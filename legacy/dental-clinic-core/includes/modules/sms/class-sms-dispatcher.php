<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class Dental_SMS_Dispatcher
 *
 * دروازه مرکزی ارسال پیامک.
 * تمام ارسال‌های SMS از اینجا عبور می‌کنند.
 * در Dev Mode هیچ پیامکی واقعاً ارسال نمی‌شود.
 */
class Dental_SMS_Dispatcher {

    /** @var string نام کاربری ترز */
    private string $trez_username;

    /** @var string رمز عبور ترز */
    private string $trez_password;

    /** @var string شماره فرستنده — برای پیامک‌های عادی (یادآور، اعلان و...) */
    private string $sender;

    /** @var string فقط برای سازگاری با نصب‌های قدیمی نگه داشته شده —
     * دیگه واقعاً استفاده نمی‌شه، چون OTP از سرویس FastSend ترز میره
     * که اصلاً نیازی به شماره فرستنده نداره (فقط یوزرنیم/رمز). */
    private string $sender_otp;

    public function __construct() {
        $this->trez_username = get_option( 'dental_sms_trez_username', '' );
        $this->trez_password = get_option( 'dental_sms_trez_password', '' );
        $this->sender         = get_option( 'dental_sms_sender', '' );
        $this->sender_otp = get_option( 'dental_sms_sender_otp', '' ) ?: $this->sender;

        // ثبت هوک‌های Cron برای پردازش صف
        add_action( 'dental_process_sms_queue', [ $this, 'process_queue' ] );
    }

    /**
     * ارسال پیامک — نقطه ورودی اصلی
     *
     * @param string $mobile       شماره گیرنده
     * @param string $message      متن پیامک
     * @param string $trigger_type نوع رویداد
     * @param int    $patient_id   شناسه بیمار (اختیاری)
     * @return array{success: bool, message_id: string|null, error: string|null}
     */
    public function send(
        string $mobile,
        string $message,
        string $trigger_type = 'custom',
        int    $patient_id   = 0
    ): array {
        // ─── Mock Mode: بدون ارسال واقعی ────────────────────────────────────
        if ( Dental_Dev_Logger::is_dev_mode() ) {
            $mock = Dental_Dev_Logger::mock_sms_response( $mobile, $message );
            $this->log_to_db( $mobile, $message, $trigger_type, $patient_id, $mock );
            return $mock;
        }

        // ─── بررسی اعتبار تنظیمات ────────────────────────────────────────────
        $is_otp = $trigger_type === 'login_otp';
        $use_sender = $is_otp ? $this->sender_otp : $this->sender;
        if ( empty( $this->trez_username ) || empty( $this->trez_password ) || empty( $use_sender ) ) {
            Dental_Dev_Logger::log( 'ERROR', 'تنظیمات SMS (ترز) کامل نیست', [] );
            return [ 'success' => false, 'message_id' => null, 'error' => 'تنظیمات SMS پیکربندی نشده.' ];
        }

        // ─── ارسال واقعی از طریق سامانه ترز ─────────────────────────────────
        $result = $this->send_via_trez( $mobile, $message, $use_sender );

        // ذخیره لاگ در DB
        $this->log_to_db( $mobile, $message, $trigger_type, $patient_id, $result );

        return $result;
    }

    /**
     * افزودن به صف ارسال (برای ارسال‌های غیرفوری مثل یادآوری)
     *
     * @param string   $mobile       شماره
     * @param string   $message      متن
     * @param string   $trigger_type نوع
     * @param int      $patient_id   شناسه بیمار
     * @param int|null $scheduled_at timestamp ارسال (null = هم‌اکنون)
     */
    public function queue(
        string $mobile,
        string $message,
        string $trigger_type,
        int    $patient_id   = 0,
        ?int   $scheduled_at = null
    ): void {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'dental_sms_log',
            [
                'patient_id'   => $patient_id,
                'mobile'       => $mobile,
                'message'      => $message,
                'trigger_type' => $trigger_type,
                'gateway'      => 'trez',
                'status'       => 'queued',
                'scheduled_at' => $scheduled_at
                    ? gmdate( 'Y-m-d H:i:s', $scheduled_at )
                    : current_time( 'mysql' ),
                'created_at'   => current_time( 'mysql' ),
            ],
            [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );

        Dental_Dev_Logger::log( 'SMS', "پیامک به صف اضافه شد برای {$mobile}", [
            'trigger'      => $trigger_type,
            'scheduled_at' => $scheduled_at ? gmdate( 'Y-m-d H:i:s', $scheduled_at ) : 'هم‌اکنون',
        ] );
    }

    /**
     * پردازش صف SMS (اجرا توسط Cron)
     */
    public function process_queue(): void {
        global $wpdb;

        $pending = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_sms_log
             WHERE status IN ('queued', 'failed')
               AND retry_count < 3
               AND (scheduled_at IS NULL OR scheduled_at <= %s)
             ORDER BY scheduled_at ASC
             LIMIT 20",
            current_time( 'mysql' )
        ) );

        if ( empty( $pending ) ) {
            return;
        }

        Dental_Dev_Logger::log( 'SMS', count( $pending ) . ' پیامک در صف برای ارسال' );

        foreach ( $pending as $item ) {
            // علامت‌گذاری به عنوان در حال ارسال
            $wpdb->update(
                $wpdb->prefix . 'dental_sms_log',
                [ 'status' => 'pending' ],
                [ 'id' => $item->id ],
                [ '%s' ],
                [ '%d' ]
            );

            $result = $this->send(
                $item->mobile,
                $item->message,
                $item->trigger_type,
                (int) $item->patient_id
            );

            // بروزرسانی وضعیت
            $wpdb->update(
                $wpdb->prefix . 'dental_sms_log',
                [
                    'status'         => $result['success'] ? 'sent' : 'failed',
                    'gateway_msg_id' => $result['message_id'] ?? null,
                    'error_message'  => $result['error'] ?? null,
                    'sent_at'        => $result['success'] ? current_time( 'mysql' ) : null,
                    'retry_count'    => $item->retry_count + ( $result['success'] ? 0 : 1 ),
                ],
                [ 'id' => $item->id ],
                [ '%s', '%s', '%s', '%s', '%d' ],
                [ '%d' ]
            );

            // تأخیر کوچک بین ارسال‌ها
            if ( ! Dental_Dev_Logger::is_dev_mode() ) {
                usleep( 300000 ); // 300ms
            }
        }
    }

    // ─── دروازه پیامک: ترز (Trez SMS Panel) ─────────────────────────────────

    /**
     * ارسال از طریق سامانه ترز (SOAP / trezsmswebservice.asmx)
     * نکته مهم: نام پارامترها عیناً همون چیزیه که سرور ترز انتظار داره —
     * شامل تایپوهای خودِ سرویس (Passwod به‌جای Password، SenderNumebr
     * به‌جای SenderNumber) — این‌ها اشتباه ما نیستن، اشتباه خودِ WSDL ترزه
     * و باید عیناً همین‌طور فرستاده بشن وگرنه درخواست رد می‌شه.
     */
    private function send_via_trez( string $mobile, string $message, string $sender ): array {
        if ( ! class_exists( 'SoapClient' ) ) {
            return [ 'success' => false, 'message_id' => null, 'error' => 'افزونه PHP SOAP روی سرور فعال نیست.' ];
        }

        try {
            $client = new SoapClient( 'http://smspanel.trez.ir/trezsmswebservice.asmx?WSDL', [
                'encoding' => 'UTF-8',
                'connection_timeout' => 15,
                'exceptions' => true,
            ] );

            $params = [
                'Username'         => $this->trez_username,
                'Passwod'          => $this->trez_password, // تایپوی خودِ سرویس ترز — عمدی است
                'SenderNumebr'     => $sender,                // تایپوی خودِ سرویس ترز — عمدی است
                'MessageBody'      => $message,
                'ReciptionNumbers' => $mobile,
                'Class'            => '1',
                'UserMessageId'    => (string) wp_rand( 100, 999999 ),
            ];

            $response = $client->SendOneMessage( $params );
            $status   = (string) ( $response->SendOneMessageResult ?? '' );

            return $this->parse_trez_status( $status );

        } catch ( \SoapFault $e ) {
            return [ 'success' => false, 'message_id' => null, 'error' => 'خطای اتصال به سامانه ترز: ' . $e->getMessage() ];
        }
    }

    /**
     * تفسیر کد وضعیت بازگشتی از ترز — مطابق مستندات:
     * بیشتر از ۱۰۰۰ = موفق (خودِ عدد شناسه پیامکه)، بقیه کدهای خطا.
     */
    private function parse_trez_status( string $status ): array {
        $code = (int) $status;

        if ( $code > 1000 ) {
            return [ 'success' => true, 'message_id' => $status, 'error' => null, 'gateway' => 'trez' ];
        }

        $errors = [
            0 => 'خطا در ارسال',
            2 => 'ارسال موفق بدون ذخیره پیام در سایت', // این وضعیت هم عملاً ارسال موفقه
            3 => 'خطا در ارسال',
            4 => 'اعتبار حساب پیامک ناکافی است',
            5 => 'طول پیام از حد مجاز بیشتر است',
            6 => 'اطلاعات کاربری دستکاری شده است',
            7 => 'تعداد گیرندگان بیش از حد مجاز است',
            8 => 'نام کاربری یا رمز عبور ترز نادرست است',
        ];

        // کد ۲ در عمل یعنی ارسال شده، فقط ذخیره نشده — پس موفق حساب می‌شه
        if ( $code === 2 ) {
            return [ 'success' => true, 'message_id' => $status, 'error' => null, 'gateway' => 'trez' ];
        }

        return [
            'success'    => false,
            'message_id' => null,
            'error'      => $errors[ $code ] ?? "کد خطای نامشخص ترز: {$status}",
        ];
    }

    /**
     * ذخیره لاگ ارسال در DB
     */
    private function log_to_db(
        string $mobile,
        string $message,
        string $trigger_type,
        int    $patient_id,
        array  $result
    ): void {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'dental_sms_log',
            [
                'patient_id'     => $patient_id,
                'mobile'         => $mobile,
                'message'        => $message,
                'trigger_type'   => $trigger_type,
                'gateway'        => Dental_Dev_Logger::is_dev_mode() ? 'mock' : 'trez',
                'gateway_msg_id' => $result['message_id'] ?? null,
                'status'         => $result['success'] ? 'sent' : 'failed',
                'error_message'  => $result['error'] ?? null,
                'sent_at'        => $result['success'] ? current_time( 'mysql' ) : null,
                'created_at'     => current_time( 'mysql' ),
            ],
            [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );
    }

    /**
     * قالب‌های پیامک آماده
     */
    public static function get_template( string $type, array $vars = [] ): string {
        $clinic = get_option( 'dental_clinic_name', 'کلینیک دندانپزشکی' );

        $templates = [
            'appointment_reminder' => "کلینیک {$clinic}\nیادآوری نوبت:\nتاریخ: {date}\nساعت: {time}\nدکتر: {doctor}\nلغو نوبت: {cancel_url}",
            'booking_confirm'      => "کلینیک {$clinic}\nنوبت شما ثبت شد ✓\nتاریخ: {date} ساعت {time}\nدکتر: {doctor}\nخدمت: {service}",
            'overdue_installment'  => "کلینیک {$clinic}\nقسط شماره {number} شما به مبلغ {amount} تومان سررسید شده.\nلطفاً هرچه زودتر پرداخت فرمایید.",
            'wallet_topup'         => "کلینیک {$clinic}\nمبلغ {amount} تومان به کیف پول شما افزوده شد.\nموجودی جدید: {balance} تومان",
            'payment_receipt'      => "کلینیک {$clinic}\nپرداخت {amount} تومان تأیید شد ✓\nکد پیگیری: {ref_id}",
        ];

        $template = $templates[ $type ] ?? '{message}';

        foreach ( $vars as $key => $value ) {
            $template = str_replace( '{' . $key . '}', (string) $value, $template );
        }

        return $template;
    }

    // ═══════════════════════════════════════════════════════════════
    // سرویس اختصاصی کد فعال‌سازی (FastSend) — کاملاً جدا از سرویس اصلی
    // پیامک. طبق تأیید خودِ کاربر و نمونه‌کد پلاگین CRM، این سرویس فقط
    // با یوزرنیم/رمز کار می‌کنه، اصلاً به شماره فرستنده نیاز نداره —
    // و مهم‌تر: کد رو خودش (سمت سرور ترز) تولید و نگه‌داری می‌کنه؛
    // ما هیچ کد یا هش کدی رو محلی ذخیره نمی‌کنیم، فقط شماره رو
    // می‌فرستیم و بعداً همون شماره+کدی که کاربر وارد کرد رو برای تأیید
    // به همین سرویس برمی‌گردونیم.
    // ═══════════════════════════════════════════════════════════════
    const OTP_WSDL_URL = 'http://smspanel.trez.ir/fastsend.asmx?WSDL';

    /**
     * ارسال کد فعال‌سازی. موفق بود true برمی‌گردونه.
     */
    public function send_otp_via_gateway( string $mobile ): array {
        if ( empty( $this->trez_username ) || empty( $this->trez_password ) ) {
            return [ 'success' => false, 'error' => 'یوزرنیم/رمز پیامک تنظیم نشده.' ];
        }
        if ( ! class_exists( 'SoapClient' ) ) {
            return [ 'success' => false, 'error' => 'افزونه PHP SOAP روی سرور فعال نیست.' ];
        }

        $mobile = preg_replace( '/[^0-9]/', '', $mobile );

        try {
            $client = new SoapClient( self::OTP_WSDL_URL, [
                'encoding' => 'UTF-8', 'exceptions' => true, 'connection_timeout' => 15,
            ] );

            $result = $client->AutoSendCode( [
                'Username'        => $this->trez_username,
                'Password'        => $this->trez_password,
                'ReciptionNumber' => $mobile,
                'Footer'          => '',
            ] );

            $ret = isset( $result->AutoSendCodeResult ) ? (string) $result->AutoSendCodeResult : '';

            // طبق مستندات این سرویس، عددی بزرگ‌تر از ۲۰۰۰ یعنی ارسال موفق
            if ( $ret !== '' && (float) $ret > 2000 ) {
                $this->log_otp_activity( $mobile, 'ارسال کد ورود (FastSend)', 'sent', 'کد بازگشتی: ' . $ret );
                return [ 'success' => true, 'message_id' => $ret ];
            }
            $this->log_otp_activity( $mobile, 'ارسال کد ورود (FastSend)', 'failed', 'کد بازگشتی: ' . $ret );
            return [ 'success' => false, 'error' => 'کد بازگشتی سرویس: ' . $ret ];

        } catch ( SoapFault $e ) {
            $this->log_otp_activity( $mobile, 'ارسال کد ورود (FastSend)', 'error', 'خطای SOAP: ' . $e->getMessage() );
            return [ 'success' => false, 'error' => 'خطای SOAP: ' . $e->getMessage() ];
        }
    }

    // ─── ثبت لاگ فعالیت OTP توی همون جدول لاگ پیامک عمومی — چون
    // متدهای جدید FastSend (برخلاف send() قدیمی) مستقیم SOAP صدا
    // می‌زدن و از queue()/log_sms() رد نمی‌شدن، لاگشون گم شده بود ────
    private function log_otp_activity( string $mobile, string $label, string $status, string $note = '' ): void {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'dental_sms_log',
            [
                'patient_id'   => 0,
                'mobile'       => $mobile,
                'message'      => $label . ( $note ? " — {$note}" : '' ),
                'trigger_type' => 'login_otp',
                'gateway'      => 'trez_fastsend',
                'status'       => $status,
                'scheduled_at' => current_time( 'mysql' ),
                'sent_at'      => current_time( 'mysql' ),
                'created_at'   => current_time( 'mysql' ),
            ]
        );
    }

    /**
     * بررسی صحت کدی که کاربر وارد کرده، مستقیماً روی شماره‌تلفن — ما
     * خودمون هیچ کدی نگه نمی‌داریم، این سرویس مرجع نهاییه.
     */
    public function verify_otp_via_gateway( string $mobile, string $code ): bool {
        if ( empty( $this->trez_username ) || empty( $this->trez_password ) || ! class_exists( 'SoapClient' ) ) {
            return false;
        }

        $mobile = preg_replace( '/[^0-9]/', '', $mobile );

        try {
            $client = new SoapClient( self::OTP_WSDL_URL, [
                'encoding' => 'UTF-8', 'exceptions' => true, 'connection_timeout' => 15,
            ] );

            $result = $client->CheckSendCode( [
                'Username'        => $this->trez_username,
                'Password'        => $this->trez_password,
                'ReciptionNumber' => $mobile,
                'Code'            => $code,
            ] );

            $check = $result->CheckSendCodeResult ?? null;
            $is_valid = $check === true || $check === 'true' || $check === '1' || $check === 1;
            $this->log_otp_activity( $mobile, 'بررسی کد ورود', $is_valid ? 'sent' : 'failed', $is_valid ? 'کد صحیح بود' : 'کد نادرست بود' );
            return $is_valid;

        } catch ( SoapFault $e ) {
            Dental_Dev_Logger::log( 'ERROR', 'خطای بررسی OTP (FastSend): ' . $e->getMessage(), [] );
            return false;
        }
    }
}
