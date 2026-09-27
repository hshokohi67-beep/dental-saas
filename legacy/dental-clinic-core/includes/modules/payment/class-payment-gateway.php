<?php
defined('ABSPATH') || exit;

class Dental_Payment_Gateway {

    public function __construct() {
        add_action('init',              [$this, 'handle_callback']);
        add_action('wp_ajax_dental_online_pay',        [$this, 'initiate_payment']);
        add_action('wp_ajax_nopriv_dental_online_pay', [$this, 'initiate_payment']);
    }

    // ─── شروع پرداخت ─────────────────────────────────────────
    // رفع امنیتی: قبلاً amount مستقیم از $_GET خونده می‌شد (قابل‌دستکاری
    // کامل — کاربر می‌تونست هرمبلغی بخواد بفرسته!). الان فقط item_id
    // از کاربر می‌گیریم، مبلغ واقعی رو خودمون از دیتابیس می‌خونیم، و
    // چک می‌کنیم این قسط واقعاً مال همین بیمار لاگین‌شده باشه (IDOR).
    public function initiate_payment(): void {
        $item_id    = (int)($_GET['item_id'] ?? 0);
        $patient_id = Dental_Frontend::get_current_patient_id();

        if (!$item_id || !$patient_id) {
            wp_die('اطلاعات پرداخت نامعتبر است.');
        }

        if (!get_option('dental_online_payment_enabled', 0)) {
            wp_die('پرداخت آنلاین غیرفعال است.');
        }

        // مبلغ واقعی رو از دیتابیس می‌خونیم، نه از ورودی کاربر
        $item = Dental_Installment_Manager::get_item($item_id);
        if (!$item) {
            wp_die('قسط یافت نشد.');
        }
        // ─── چک مالکیت (IDOR) — این قسط باید مال همین بیمار باشه ────
        if ((int)$item['patient_id'] !== (int)$patient_id) {
            wp_die('این قسط متعلق به شما نیست.');
        }
        if ($item['status'] === 'paid') {
            wp_die('این قسط قبلاً پرداخت شده است.');
        }
        $amount = (float)$item['amount'] - (float)($item['paid_amount'] ?? 0);
        if ($amount <= 0) {
            wp_die('مبلغ باقی‌مانده این قسط نامعتبر است.');
        }

        // ذخیره اطلاعات پرداخت در session
        WC()->session->set('dental_pay_item', $item_id);
        WC()->session->set('dental_pay_amount', $amount);
        WC()->session->set('dental_pay_patient', $patient_id);

        $gateway  = get_option('dental_payment_gateway', 'zarinpal');
        $key      = get_option('dental_payment_gateway_key', '');
        $callback = add_query_arg('dental_payment_callback', '1', dental_get_portal_url());
        $amount_rial = (int)($amount * 10); // تبدیل تومان به ریال

        $result = $this->request_payment($gateway, $key, $amount_rial, $callback, $patient_id);

        if ($result['success']) {
            // ذخیره token + مبلغ واقعی (برای تأیید نهایی، نه یه مقدار جدید)
            update_post_meta($patient_id, '_pending_payment_token', $result['token']);
            update_post_meta($patient_id, '_pending_payment_item', $item_id);
            update_post_meta($patient_id, '_pending_payment_amount', $amount);
            // ریدایرکت به درگاه
            wp_redirect($result['redirect']);
            exit;
        } else {
            wp_die('خطا در اتصال به درگاه پرداخت: ' . esc_html($result['error']));
        }
    }

    // ─── callback از درگاه ────────────────────────────────────
    public function handle_callback(): void {
        if (!isset($_GET['dental_payment_callback'])) return;

        $gateway    = get_option('dental_payment_gateway', 'zarinpal');
        $key        = get_option('dental_payment_gateway_key', '');
        $patient_id = Dental_Frontend::get_current_patient_id();

        if (!$patient_id) {
            wp_safe_redirect(home_url('/portal-bimar/?ptab=financial&pay_error=1'));
            exit;
        }

        $token    = get_post_meta($patient_id, '_pending_payment_token', true);
        $item_id  = (int)get_post_meta($patient_id, '_pending_payment_item', true);
        // ─── رفع امنیتی: مبلغ واقعی همون چیزیه که خودمون موقع initiate
        // ذخیره کردیم (نه صفر، نه چیزی که کاربر بفرسته) — همینو هم برای
        // تأیید نزد درگاه هم برای ثبت قسط استفاده می‌کنیم.
        $amount   = (float)get_post_meta($patient_id, '_pending_payment_amount', true);
        $amount_rial = (int)($amount * 10);

        if (!$item_id || $amount <= 0) {
            wp_safe_redirect(home_url('/portal-bimar/?ptab=financial&pay_error=1'));
            exit;
        }

        $result = $this->verify_payment($gateway, $key, $token, $_GET, $amount_rial);

        if ($result['success']) {
            // ثبت پرداخت — مبلغ واقعی، نه صفر
            Dental_Installment_Manager::pay_item($item_id, $amount, 'online', $result['ref_id'], false);

            // SMS رسید
            $item = Dental_Installment_Manager::get_item($item_id);
            if ($item) {
                $mobile = get_post_meta($patient_id, '_patient_mobile', true);
                $patient = get_post($patient_id);
                if ($mobile) {
                    $tpl = get_option('dental_sms_tpl_payment_receipt',
                        "پرداخت {amount} تومان با موفقیت انجام شد.\nکد پیگیری: {ref_id}"
                    );
                    $msg = str_replace(
                        ['{amount}', '{ref_id}', '{patient_name}', '{clinic_name}'],
                        [number_format($item['amount']), $result['ref_id'], $patient->post_title??'', get_option('dental_clinic_name','')],
                        $tpl
                    );
                    (new Dental_SMS_Cron())->queue_sms_public($mobile, $msg, 'payment_receipt', $patient_id);
                }
            }

            // پاک کردن token
            delete_post_meta($patient_id, '_pending_payment_token');
            delete_post_meta($patient_id, '_pending_payment_item');
            delete_post_meta($patient_id, '_pending_payment_amount');

            wp_safe_redirect(home_url('/portal-bimar/?ptab=financial&pay_success=1&ref=' . urlencode($result['ref_id'])));
            exit;
        } else {
            wp_safe_redirect(home_url('/portal-bimar/?ptab=financial&pay_error=1&msg=' . urlencode($result['error'])));
            exit;
        }
    }

    // ─── درخواست پرداخت ──────────────────────────────────────
    private function request_payment(string $gw, string $key, int $amount, string $callback, int $patient_id): array {
        $patient = get_post($patient_id);
        $mobile  = get_post_meta($patient_id, '_patient_mobile', true);
        $clinic  = get_option('dental_clinic_name', 'کلینیک دندانپزشکی');

        switch($gw) {
            case 'zarinpal':
                $res = wp_remote_post('https://api.zarinpal.com/pg/v4/payment/request.json', [
                    'body'    => json_encode([
                        'merchant_id'  => $key,
                        'amount'       => $amount,
                        'description'  => 'پرداخت قسط — ' . $clinic,
                        'callback_url' => $callback,
                        'metadata'     => ['mobile'=>$mobile,'email'=>''],
                    ]),
                    'headers' => ['Content-Type'=>'application/json'],
                    'timeout' => 15,
                ]);
                if (is_wp_error($res)) return ['success'=>false,'error'=>$res->get_error_message()];
                $body = json_decode(wp_remote_retrieve_body($res), true);
                if (($body['data']['code']??-1) === 100) {
                    return ['success'=>true,'token'=>$body['data']['authority'],'redirect'=>'https://www.zarinpal.com/pg/StartPay/'.$body['data']['authority']];
                }
                return ['success'=>false,'error'=>$body['errors']['message']??'خطای زرین‌پال'];

            case 'idpay':
                $res = wp_remote_post('https://api.idpay.ir/v1.1/payment', [
                    'body'    => json_encode(['order_id'=>uniqid('dc_'),'amount'=>$amount,'name'=>$patient->post_title??'','phone'=>$mobile,'callback'=>$callback]),
                    'headers' => ['Content-Type'=>'application/json','X-API-KEY'=>$key,'X-SANDBOX'=>'0'],
                    'timeout' => 15,
                ]);
                if (is_wp_error($res)) return ['success'=>false,'error'=>$res->get_error_message()];
                $body = json_decode(wp_remote_retrieve_body($res), true);
                if (!empty($body['link'])) {
                    return ['success'=>true,'token'=>$body['id'],'redirect'=>$body['link']];
                }
                return ['success'=>false,'error'=>$body['error_message']??'خطای IDPay'];

            case 'nextpay':
                $res = wp_remote_post('https://nextpay.org/nx/gateway/token', [
                    'body'    => ['api_key'=>$key,'amount'=>$amount,'order_id'=>uniqid('dc_'),'callback_uri'=>$callback],
                    'timeout' => 15,
                ]);
                if (is_wp_error($res)) return ['success'=>false,'error'=>$res->get_error_message()];
                $body = json_decode(wp_remote_retrieve_body($res), true);
                if (($body['code']??-1) === -1) {
                    return ['success'=>true,'token'=>$body['trans_id'],'redirect'=>'https://nextpay.org/nx/gateway/payment/'.$body['trans_id']];
                }
                return ['success'=>false,'error'=>'خطای NextPay: '.$body['code']];
        }
        return ['success'=>false,'error'=>'درگاه نامشخص'];
    }

    // ─── تأیید پرداخت ────────────────────────────────────────
    // رفع امنیتی: مبلغ ۰ به درگاه پاس داده می‌شد (باعث می‌شد تأیید یا
    // اشتباه انجام بشه یا اصلاً معتبر نباشه) — الان amount_rial واقعی
    // (که خودمون موقع initiate_payment محاسبه و ذخیره کردیم) پاس داده می‌شه.
    private function verify_payment(string $gw, string $key, string $token, array $get, int $amount_rial): array {
        switch($gw) {
            case 'zarinpal':
                $authority = $get['Authority'] ?? $token;
                $status    = $get['Status'] ?? '';
                if ($status !== 'OK') return ['success'=>false,'error'=>'پرداخت توسط کاربر لغو شد'];
                $res = wp_remote_post('https://api.zarinpal.com/pg/v4/payment/verify.json', [
                    'body'    => json_encode(['merchant_id'=>$key,'amount'=>$amount_rial,'authority'=>$authority]),
                    'headers' => ['Content-Type'=>'application/json'],
                    'timeout' => 15,
                ]);
                if (is_wp_error($res)) return ['success'=>false,'error'=>$res->get_error_message()];
                $body = json_decode(wp_remote_retrieve_body($res), true);
                if (in_array($body['data']['code']??-1, [100,101])) {
                    return ['success'=>true,'ref_id'=>(string)($body['data']['ref_id']??$authority)];
                }
                return ['success'=>false,'error'=>'تأیید زرین‌پال ناموفق'];

            case 'idpay':
                $res = wp_remote_post('https://api.idpay.ir/v1.1/payment/verify', [
                    'body'    => json_encode(['id'=>$get['id']??'','order_id'=>$get['order_id']??'']),
                    'headers' => ['Content-Type'=>'application/json','X-API-KEY'=>$key,'X-SANDBOX'=>'0'],
                    'timeout' => 15,
                ]);
                if (is_wp_error($res)) return ['success'=>false,'error'=>$res->get_error_message()];
                $body = json_decode(wp_remote_retrieve_body($res), true);
                if (($body['status']??0) === 100) {
                    // ─── چک اضافی مبلغ — IDPay مبلغ واقعی رو توی پاسخ هم برمی‌گردونه ──
                    if ((int)($body['amount'] ?? 0) !== $amount_rial) {
                        return ['success'=>false,'error'=>'عدم تطابق مبلغ پرداختی با مبلغ قسط.'];
                    }
                    return ['success'=>true,'ref_id'=>(string)($body['track_id']??'')];
                }
                return ['success'=>false,'error'=>'تأیید IDPay ناموفق'];

            case 'nextpay':
                $res = wp_remote_post('https://nextpay.org/nx/gateway/verify', [
                    'body'    => ['api_key'=>$key,'trans_id'=>$get['trans_id']??$token,'amount'=>$amount_rial],
                    'timeout' => 15,
                ]);
                if (is_wp_error($res)) return ['success'=>false,'error'=>$res->get_error_message()];
                $body = json_decode(wp_remote_retrieve_body($res), true);
                if (($body['code']??-1) === 0) {
                    return ['success'=>true,'ref_id'=>(string)($body['Shaparak_Ref_Id']??$token)];
                }
                return ['success'=>false,'error'=>'تأیید NextPay ناموفق'];
        }
        return ['success'=>false,'error'=>'درگاه نامشخص'];
    }
}
