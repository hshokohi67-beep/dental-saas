<?php
defined('ABSPATH') || exit;

/**
 * Class Dental_Installment_Manager
 * مدیریت اقساط — محاسبه، ذخیره، پیگیری پرداخت
 */
class Dental_Installment_Manager {

    /**
     * ایجاد پلان قسطی جدید
     */
    // ─── ساخت پلن قسطی — الان می‌تونه مستقیم به یه رکورد دفتر روزانه
    // وصل بشه (ledger_id) و پیش‌پرداخت رو هم واقعاً پردازش کنه (نه
    // فقط یه عدد ثبت‌شده بی‌اثر) — طبق نیازی که برای یکپارچگی کامل
    // «ثبت خدمت → دفتر روزانه → قسط‌بندی» لازم بود.
    public static function create(
        int    $patient_id,
        int    $treatment_id,
        float  $total_amount,
        float  $down_payment,
        int    $installment_count,
        string $start_date_jalali = '',
        float  $discount          = 0,
        string $notes             = '',
        string $down_payment_method = '',
        int    $ledger_id         = 0
    ): array {
        global $wpdb;

        if ($total_amount <= 0 || $installment_count < 1) {
            return ['success' => false, 'message' => 'اطلاعات وارد شده نامعتبر است.'];
        }

        // ─── اگه پیش‌پرداخت واقعی درخواست شده، اول خودِ پرداخت رو
        // پردازش کن — اگه ناموفق بود، اصلاً پلن قسطی ساخته نمی‌شه ──
        $down_payment_ref = '';
        if ($down_payment > 0 && $down_payment_method) {
            if ($down_payment_method === 'wallet') {
                $wallet_result = Dental_Patient_Wallet::deduct($patient_id, $down_payment, 'installment_downpayment', 0, 'پیش‌پرداخت پلن قسطی');
                if (!$wallet_result['success']) {
                    return ['success' => false, 'message' => $wallet_result['message']];
                }
            }
            // نقدی/کارتخوان همین‌جا فقط ثبت می‌شه (پول فیزیکی از قبل گرفته شده)
            $down_payment_ref = 'DP-' . current_time('timestamp');
        }

        $remaining        = $total_amount - $down_payment - $discount;
        $installment_amount = $installment_count > 0 ? round($remaining / $installment_count, 0) : 0;

        if (empty($start_date_jalali)) {
            $start_date_jalali = Dental_Jalali::today();
        }

        $now = current_time('mysql');

        $wpdb->insert(
            $wpdb->prefix . 'dental_installments',
            [
                'patient_id'         => $patient_id,
                'treatment_id'       => $treatment_id,
                'total_amount'       => $total_amount,
                'down_payment'       => $down_payment,
                'remaining_amount'   => $remaining,
                'installment_count'  => $installment_count,
                'installment_amount' => $installment_amount,
                'discount_amount'    => $discount,
                'status'             => 'active',
                'notes'              => $notes,
                'created_at'         => $now,
                'updated_at'         => $now,
                'created_by'         => get_current_user_id(),
                'down_payment_method'=> $down_payment_method ?: null,
                'down_payment_ref'   => $down_payment_ref ?: null,
                'ledger_id'          => $ledger_id ?: null,
            ],
            ['%d','%d','%f','%f','%f','%d','%f','%f','%s','%s','%s','%s','%d','%s','%s','%d']
        );

        if ($wpdb->last_error) {
            return ['success' => false, 'message' => 'خطا در ذخیره: ' . $wpdb->last_error];
        }

        $installment_id = (int) $wpdb->insert_id;

        // ساخت اقلام قسط با تاریخ‌های شمسی
        $due_dates = Dental_Jalali::get_installment_dates($start_date_jalali, $installment_count);
        self::create_items($installment_id, $installment_amount, $due_dates);

        Dental_Dev_Logger::log('INFO', "پلان قسطی ایجاد شد", [
            'installment_id'  => $installment_id,
            'patient_id'      => $patient_id,
            'total'           => $total_amount,
            'down_payment'    => $down_payment,
            'count'           => $installment_count,
            'each'            => $installment_amount,
        ]);

        return [
            'success'        => true,
            'installment_id' => $installment_id,
            'items_count'    => $installment_count,
            'each_amount'    => $installment_amount,
            'due_dates'      => $due_dates,
        ];
    }

    /**
     * ساخت اقلام قسط
     */
    private static function create_items(
        int   $installment_id,
        float $amount,
        array $due_dates
    ): void {
        global $wpdb;
        $table = $wpdb->prefix . 'dental_installment_items';

        foreach ($due_dates as $i => $jalali_date) {
            $gregorian = Dental_Jalali::to_gregorian($jalali_date);
            $wpdb->insert($table, [
                'installment_id'  => $installment_id,
                'item_number'     => $i + 1,
                'due_date_jalali' => $jalali_date,
                'due_date'        => $gregorian,
                'amount'          => $amount,
                'paid_amount'     => 0,
                'status'          => 'pending',
            ], ['%d','%d','%s','%s','%f','%f','%s']);
        }
    }

    /**
     * پرداخت یک قسط
     */
    // ─── رفع امنیتی: قبلاً paid_amount توی PHP جمع زده می‌شد (race
    // condition) و چک نمی‌شد که مبلغ پرداختی از باقی‌مانده‌ی بدهی
    // بیشتر نباشه (امکان اضافه‌پرداخت/سوءاستفاده). الان هم آپدیت اتمیکه
    // هم قبلش چک سقف انجام می‌شه.
    public static function pay_item(
        int    $item_id,
        float  $amount,
        string $method   = 'cash',
        string $ref      = '',
        bool   $from_wallet = false
    ): array {
        global $wpdb;

        $item = $wpdb->get_row($wpdb->prepare(
            "SELECT i.*, inst.patient_id FROM {$wpdb->prefix}dental_installment_items i
             JOIN {$wpdb->prefix}dental_installments inst ON i.installment_id = inst.id
             WHERE i.id = %d",
            $item_id
        ));

        if (!$item) {
            return ['success' => false, 'message' => 'قسط یافت نشد.'];
        }

        if ($item->status === 'paid') {
            return ['success' => false, 'message' => 'این قسط قبلاً پرداخت شده است.'];
        }

        $patient_id = (int) $item->patient_id;
        $remaining  = (float)$item->amount - (float)$item->paid_amount;

        // ─── چک اضافه‌پرداخت — مبلغ درخواستی نباید از باقی‌مانده‌ی
        // بدهی همین قسط بیشتر باشه ─────────────────────────────────
        if ($amount > $remaining + 0.01) { // ۰.۰۱ برای گرد شدن اعشار
            return ['success' => false, 'message' => "مبلغ پرداختی از باقی‌مانده‌ی این قسط (" . number_format($remaining) . " تومان) بیشتر است."];
        }

        // پرداخت از کیف پول
        if ($from_wallet) {
            $wallet_result = Dental_Patient_Wallet::deduct(
                $patient_id,
                $amount,
                'installment_pay',
                $item_id,
                "پرداخت قسط شماره {$item->item_number}"
            );
            if (!$wallet_result['success']) {
                return $wallet_result;
            }
            $method = 'wallet';
        }

        // ─── آپدیت اتمیک مبلغ — این بخش بحرانیه، باید حتماً توی خودِ SQL
        // انجام بشه (نه PHP) تا race condition نداشته باشه. وضعیت
        // (paid/partial) رو عمداً توی همین کوئری محاسبه نمی‌کنیم، چون
        // رفتار MySQL برای ارجاع دوباره به ستونی که توی همون UPDATE
        // عوض شده، همیشه قابل‌پیش‌بینی نیست — اینجوری مطمئن‌تره.
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}dental_installment_items
             SET paid_amount = paid_amount + %f,
                 paid_date = %s, payment_method = %s, transaction_ref = %s
             WHERE id = %d AND status != 'paid' AND (paid_amount + %f) <= (amount + 0.01)",
            $amount, current_time('mysql'), $method, $ref, $item_id, $amount
        ));

        if ($wpdb->rows_affected === 0) {
            // یا قسط بین‌این‌حین توسط یه درخواست دیگه paid شده، یا
            // مبلغ باعث عبور از سقف می‌شده — در هر دو حالت، عملیات لغو شد
            if ($from_wallet) {
                // پول برداشته‌شده از کیف پول رو برگردون چون پرداخت قسط انجام نشد
                Dental_Patient_Wallet::credit($patient_id, $amount, 'refund', $item_id, 'بازگشت وجه — قسط قابل پرداخت نبود');
            }
            return ['success' => false, 'message' => 'این قسط توسط یک درخواست هم‌زمان دیگر پردازش شد یا مبلغ نامعتبر است.'];
        }

        // مبلغ به‌درستی و اتمیک آپدیت شد؛ حالا مقدار تازه رو می‌خونیم و
        // وضعیت (paid/partial) رو براساسش تنظیم می‌کنیم — این بخش دیگه
        // بحرانی نیست (صرفاً نمایشیه، خودِ پول قبلاً امن آپدیت شده)
        $fresh = $wpdb->get_row($wpdb->prepare(
            "SELECT paid_amount, amount FROM {$wpdb->prefix}dental_installment_items WHERE id=%d", $item_id
        ));
        $new_paid = (float)($fresh->paid_amount ?? 0);
        $status   = $new_paid >= (float)($fresh->amount ?? 0) ? 'paid' : 'partial';
        $wpdb->update($wpdb->prefix.'dental_installment_items', ['status'=>$status], ['id'=>$item_id], ['%s'], ['%d']);

        // بروزرسانی وضعیت کلی قسط
        self::update_installment_status((int)$item->installment_id);

        // ارسال SMS رسید
        if ($status === 'paid') {
            self::send_payment_sms($patient_id, $amount, $item->item_number);
        }

        return [
            'success'    => true,
            'message'    => 'پرداخت با موفقیت ثبت شد.',
            'new_status' => $status,
            'paid_amount'=> $new_paid,
        ];
    }

    /**
     * بروزرسانی وضعیت کلی پلان قسطی
     */
    private static function update_installment_status(int $installment_id): void {
        global $wpdb;

        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT status FROM {$wpdb->prefix}dental_installment_items WHERE installment_id = %d",
            $installment_id
        ));

        $all_paid    = true;
        $any_overdue = false;

        foreach ($items as $item) {
            if ($item->status !== 'paid') $all_paid = false;
            if ($item->status === 'overdue') $any_overdue = true;
        }

        $new_status = $all_paid ? 'completed' : ($any_overdue ? 'overdue' : 'active');

        $wpdb->update(
            $wpdb->prefix . 'dental_installments',
            ['status' => $new_status, 'updated_at' => current_time('mysql')],
            ['id' => $installment_id],
            ['%s','%s'],
            ['%d']
        );
    }

    /**
     * بررسی اقساط سررسیدشده (اجرا از Cron)
     */
    public static function check_overdue(): int {
        global $wpdb;

        $today = current_time('Y-m-d');

        $updated = $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}dental_installment_items
             SET status = 'overdue'
             WHERE status = 'pending'
               AND due_date < %s",
            $today
        ));

        if ($updated > 0) {
            // بروزرسانی وضعیت پلان‌های مرتبط
            $wpdb->query(
                "UPDATE {$wpdb->prefix}dental_installments i
                 SET status = 'overdue', updated_at = NOW()
                 WHERE EXISTS (
                     SELECT 1 FROM {$wpdb->prefix}dental_installment_items ii
                     WHERE ii.installment_id = i.id AND ii.status = 'overdue'
                 ) AND i.status = 'active'"
            );

            Dental_Dev_Logger::log('INFO', "{$updated} قسط سررسیدشده علامت‌گذاری شد");
        }

        return (int) $updated;
    }

    /**
     * دریافت اقساط یک بیمار
     */
    public static function get_patient_installments(int $patient_id): array {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT i.*,
                    (SELECT COUNT(*) FROM {$wpdb->prefix}dental_installment_items ii WHERE ii.installment_id = i.id) as total_items,
                    (SELECT COUNT(*) FROM {$wpdb->prefix}dental_installment_items ii WHERE ii.installment_id = i.id AND ii.status = 'paid') as paid_items,
                    (SELECT SUM(ii.paid_amount) FROM {$wpdb->prefix}dental_installment_items ii WHERE ii.installment_id = i.id) as total_paid
             FROM {$wpdb->prefix}dental_installments i
             WHERE i.patient_id = %d
             ORDER BY i.created_at DESC",
            $patient_id
        ), ARRAY_A);
    }

    /**
     * دریافت اقلام یک پلان قسطی
     */
    // ─── گرفتن یک قسط خاص، همراه با patient_id (برای چک مالکیت IDOR
    // توی درگاه پرداخت) — قبلاً فقط get_items جمعی وجود داشت ─────────
    public static function get_item(int $item_id): ?array {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT i.*, inst.patient_id FROM {$wpdb->prefix}dental_installment_items i
             JOIN {$wpdb->prefix}dental_installments inst ON i.installment_id = inst.id
             WHERE i.id = %d", $item_id
        ), ARRAY_A);
        return $row ?: null;
    }

    public static function get_items(int $installment_id): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_installment_items
             WHERE installment_id = %d ORDER BY item_number ASC",
            $installment_id
        ), ARRAY_A);
    }

    /**
     * محاسبه‌گر قسط (بدون ذخیره)
     */
    public static function calculate(
        float $total,
        float $down,
        int   $count,
        float $discount = 0
    ): array {
        $remaining = $total - $down - $discount;
        $each      = $count > 0 ? round($remaining / $count) : 0;

        return [
            'total_amount'       => $total,
            'down_payment'       => $down,
            'discount'           => $discount,
            'remaining'          => $remaining,
            'installment_count'  => $count,
            'installment_amount' => $each,
            'total_with_installments' => $down + ($each * $count),
        ];
    }

    private static function send_payment_sms(int $patient_id, float $amount, int $item_number): void {
        $mobile = get_post_meta($patient_id, '_patient_mobile', true);
        if (!$mobile) return;

        $message = Dental_SMS_Dispatcher::get_template('payment_receipt', [
            'amount' => number_format($amount),
            'number' => $item_number,
            'ref_id' => date('YmdHis'),
        ]);

        $dispatcher = new Dental_SMS_Dispatcher();
        $dispatcher->queue($mobile, $message, 'payment_receipt', $patient_id);
    }
}
