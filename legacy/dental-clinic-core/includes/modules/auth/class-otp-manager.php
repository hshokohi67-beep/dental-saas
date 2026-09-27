<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class Dental_OTP_Manager
 */
class Dental_OTP_Manager {

    private const TABLE_SUFFIX = 'dental_otp_codes';

    public static function create_table(): void {
        global $wpdb;
        $charset = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        $table   = $wpdb->prefix . self::TABLE_SUFFIX;
        $wpdb->query( "
            CREATE TABLE IF NOT EXISTS `{$table}` (
                `id`          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
                `mobile`      VARCHAR(15)      NOT NULL,
                `code_hash`   VARCHAR(64)      NOT NULL,
                `purpose`     VARCHAR(30)      NOT NULL DEFAULT 'login',
                `attempts`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
                `is_verified` TINYINT(1)       NOT NULL DEFAULT 0,
                `ip_address`  VARCHAR(45)      DEFAULT NULL,
                `user_agent`  VARCHAR(255)     DEFAULT NULL,
                `created_at`  DATETIME         NOT NULL,
                `expires_at`  DATETIME         NOT NULL,
                `verified_at` DATETIME         DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_mobile`         (`mobile`),
                KEY `idx_expires`        (`expires_at`),
                KEY `idx_mobile_purpose` (`mobile`, `purpose`, `is_verified`)
            ) {$charset}
        " ); // phpcs:ignore
    }

    // ─── نکته مهم معماری: دیگه اینجا کدی تولید/هش/ذخیره نمی‌کنیم —
    // سرویس FastSend ترز خودش کد رو تولید و نگه‌داری می‌کنه (نیازی به
    // شماره فرستنده هم نداره، فقط یوزرنیم/رمز). جدول محلی فقط برای
    // محدودیت نرخ درخواست (rate limit) و کول‌داون استفاده می‌شه، نه
    // برای مقایسه‌ی خودِ کد.
    public static function request( string $mobile, string $purpose = 'login' ): array {
        $mobile = self::normalize_mobile( $mobile );
        if ( ! self::is_valid_mobile( $mobile ) ) {
            return [ 'success' => false, 'message' => 'شماره موبایل وارد شده معتبر نیست.', 'code' => 'invalid_mobile' ];
        }

        $rate_check = self::check_rate_limit( $mobile );
        if ( ! $rate_check['allowed'] ) {
            return [
                'success'     => false,
                'message'     => "تعداد درخواست‌های شما بیش از حد مجاز است. لطفاً {$rate_check['retry_after_minutes']} دقیقه دیگر تلاش کنید.",
                'code'        => 'rate_limited',
                'retry_after' => $rate_check['retry_after_seconds'],
            ];
        }

        $cooldown = self::get_resend_cooldown( $mobile, $purpose );
        if ( $cooldown > 0 ) {
            return [ 'success' => false, 'message' => "لطفاً {$cooldown} ثانیه دیگر دوباره تلاش کنید.", 'code' => 'cooldown_active', 'wait_seconds' => $cooldown ];
        }

        self::invalidate_previous_codes( $mobile, $purpose );

        global $wpdb;
        $now        = current_time( 'mysql' );
        $expires_at = current_time( 'timestamp' ) + ( DENTAL_OTP_EXPIRY_MINUTES * MINUTE_IN_SECONDS );
        $expires_at = date( 'Y-m-d H:i:s', $expires_at );

        // فقط برای rate-limit/cooldown ثبت می‌شه — code_hash دیگه معنی
        // نداره (خالی می‌مونه) چون کد رو محلی نداریم.
        $inserted = $wpdb->insert(
            $wpdb->prefix . self::TABLE_SUFFIX,
            [
                'mobile'     => $mobile,
                'code_hash'  => '',
                'purpose'    => $purpose,
                'attempts'   => 0,
                'ip_address' => self::get_client_ip(),
                'user_agent' => substr( $_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255 ),
                'created_at' => $now,
                'expires_at' => $expires_at,
            ],
            [ '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s' ]
        );

        if ( ! $inserted ) {
            Dental_Dev_Logger::log( 'ERROR', 'خطا در ذخیره ردیف OTP در DB', [ 'error' => $wpdb->last_error ] );
            return [ 'success' => false, 'message' => 'خطای سیستمی. لطفاً دوباره تلاش کنید.', 'code' => 'db_error' ];
        }

        $otp_id = (int) $wpdb->insert_id;

        // ─── حالت توسعه: هیچ درخواستی به سرویس واقعی نمی‌ره، کد ثابته ──
        if ( Dental_Dev_Logger::is_dev_mode() ) {
            Dental_Dev_Logger::log( 'OTP', "حالت توسعه فعاله — کد ثابت 123456 برای {$mobile}", [ 'otp_id' => $otp_id ] );
            return [
                'success'          => true,
                'message'          => "کد تأیید به شماره {$mobile} ارسال شد.",
                'otp_id'           => $otp_id,
                'expires_in'       => DENTAL_OTP_EXPIRY_MINUTES * 60,
                'resend_available' => DENTAL_OTP_RESEND_COOLDOWN,
                'dev_otp'          => '123456',
            ];
        }

        // ─── حالت واقعی — سرویس FastSend خودش کد رو می‌سازه و می‌فرسته ──
        $dispatcher  = new Dental_SMS_Dispatcher();
        $send_result = $dispatcher->send_otp_via_gateway( $mobile );

        if ( ! $send_result['success'] ) {
            $wpdb->delete( $wpdb->prefix . self::TABLE_SUFFIX, [ 'id' => $otp_id ], [ '%d' ] );
            Dental_Dev_Logger::log( 'ERROR', 'ارسال OTP (FastSend) ناموفق', [ 'mobile' => $mobile, 'error' => $send_result['error'] ?? '' ] );
            return [ 'success' => false, 'message' => 'خطا در ارسال پیامک. لطفاً دوباره تلاش کنید.', 'code' => 'sms_failed' ];
        }

        Dental_Dev_Logger::log( 'OTP', "کد OTP (FastSend) برای {$mobile} ارسال شد", [ 'otp_id' => $otp_id, 'purpose' => $purpose ] );

        return [
            'success'          => true,
            'message'          => "کد تأیید به شماره {$mobile} ارسال شد.",
            'otp_id'           => $otp_id,
            'expires_in'       => DENTAL_OTP_EXPIRY_MINUTES * 60,
            'resend_available' => DENTAL_OTP_RESEND_COOLDOWN,
            'dev_otp'          => null,
        ];
    }

    public static function verify( string $mobile, string $otp, string $purpose = 'login' ): array {
        $mobile = self::normalize_mobile( $mobile );

        if ( ! self::is_valid_mobile( $mobile ) ) {
            return [ 'success' => false, 'message' => 'شماره موبایل نامعتبر است.', 'code' => 'invalid_mobile' ];
        }

        $otp = Dental_Jalali::to_english_digits( trim( $otp ) );
        $otp = preg_replace( '/\D/', '', $otp );

        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_SUFFIX;
        $now   = current_time( 'mysql' );

        // این ردیف فقط برای rate-limit/attempts استفاده می‌شه — خودِ
        // درستی کد رو دیگه اینجا مقایسه نمی‌کنیم.
        $record = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$table}
             WHERE mobile = %s
               AND purpose = %s
               AND is_verified = 0
               AND expires_at > %s
             ORDER BY id DESC
             LIMIT 1",
            $mobile,
            $purpose,
            $now
        ) );

        if ( ! $record ) {
            Dental_Dev_Logger::log( 'AUTH', "OTP منقضی یا یافت نشد برای {$mobile}", [ 'now' => $now ] );
            return [ 'success' => false, 'message' => 'کد تأیید منقضی شده یا وجود ندارد. لطفاً کد جدید درخواست کنید.', 'code' => 'otp_not_found' ];
        }

        if ( (int) $record->attempts >= DENTAL_OTP_MAX_ATTEMPTS ) {
            self::invalidate_previous_codes( $mobile, $purpose );
            return [ 'success' => false, 'message' => 'به دلیل تلاش‌های ناموفق متعدد، این کد باطل شد. لطفاً کد جدید دریافت کنید.', 'code' => 'max_attempts_exceeded' ];
        }

        // ─── حالت توسعه: فقط کد ثابت 123456 قبول می‌شه، بدون تماس با سرویس واقعی ──
        if ( Dental_Dev_Logger::is_dev_mode() ) {
            $is_valid = ( $otp === '123456' );
        } else {
            $dispatcher = new Dental_SMS_Dispatcher();
            $is_valid   = $dispatcher->verify_otp_via_gateway( $mobile, $otp );
        }

        if ( ! $is_valid ) {
            $wpdb->update( $table, [ 'attempts' => (int) $record->attempts + 1 ], [ 'id' => $record->id ], [ '%d' ], [ '%d' ] );
            $remaining = DENTAL_OTP_MAX_ATTEMPTS - ( (int) $record->attempts + 1 );
            return [
                'success'            => false,
                'message'            => $remaining > 0 ? "کد وارد شده اشتباه است. {$remaining} تلاش باقی مانده." : 'کد وارد شده اشتباه است.',
                'code'               => 'invalid_otp',
                'attempts_remaining' => max( 0, $remaining ),
            ];
        }

        $wpdb->update( $table, [ 'is_verified' => 1, 'verified_at' => current_time( 'mysql' ) ], [ 'id' => $record->id ], [ '%d', '%s' ], [ '%d' ] );

        $verification_token = self::generate_verification_token( $mobile, $purpose, (int) $record->id );

        return [
            'success'            => true,
            'message'            => 'کد تأیید با موفقیت تأیید شد.',
            'verification_token' => $verification_token,
            'mobile'             => $mobile,
            'purpose'            => $purpose,
        ];
    }

    public static function validate_verification_token( string $token, string $mobile, string $purpose ): bool {
        $transient_key = self::get_token_transient_key( $mobile, $purpose );
        $stored_token  = get_transient( $transient_key );
        if ( ! $stored_token || ! hash_equals( $stored_token, $token ) ) {
            return false;
        }
        delete_transient( $transient_key );
        return true;
    }

    private static function send_otp_sms( string $mobile, string $otp, string $purpose ): array {
        $purpose_labels = [ 'login' => 'ورود', 'register' => 'ثبت‌نام', 'booking_confirm' => 'تأیید نوبت' ];
        $purpose_label  = $purpose_labels[ $purpose ] ?? 'احراز هویت';
        $clinic_name    = get_option( 'dental_clinic_name', 'کلینیک دندانپزشکی' );
        $message        = "کلینیک {$clinic_name}\nکد {$purpose_label}: {$otp}\nاعتبار: " . DENTAL_OTP_EXPIRY_MINUTES . " دقیقه";

        if ( Dental_Dev_Logger::is_dev_mode() ) {
            return Dental_Dev_Logger::mock_sms_response( $mobile, $message );
        }

        $dispatcher = new Dental_SMS_Dispatcher();
        return $dispatcher->send( $mobile, $message, 'login_otp' );
    }

    // ─── رفع امنیتی: قبلاً فقط بر اساس موبایل چک می‌شد — یعنی یه ربات
    // می‌تونست با عوض‌کردن شماره (که رایگانه)، شارژ پیامکی رو تموم کنه.
    // الان آی‌پی درخواست‌دهنده رو هم جدا محدود می‌کنیم (ستون ip_address
    // از قبل توی جدول بود، فقط استفاده نمی‌شد).
    private static function check_rate_limit( string $mobile ): array {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_SUFFIX;

        $count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE mobile = %s AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            $mobile
        ) );
        $limit = DENTAL_OTP_RATE_LIMIT;
        if ( $count >= $limit ) {
            return [ 'allowed' => false, 'current_count' => $count, 'limit' => $limit, 'retry_after_seconds' => 3600, 'retry_after_minutes' => 60 ];
        }

        $ip = self::get_client_ip();
        $ip_count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE ip_address = %s AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            $ip
        ) );
        $ip_limit = 5; // یه آی‌پی حداکثر ۵ درخواست OTP در ساعت (برای هر شماره‌ای که باشه)
        if ( $ip_count >= $ip_limit ) {
            return [ 'allowed' => false, 'current_count' => $ip_count, 'limit' => $ip_limit, 'retry_after_seconds' => 3600, 'retry_after_minutes' => 60 ];
        }

        return [ 'allowed' => true, 'current_count' => $count, 'limit' => $limit ];
    }

    private static function get_resend_cooldown( string $mobile, string $purpose ): int {
        global $wpdb;
        $last_sent = $wpdb->get_var( $wpdb->prepare(
            "SELECT created_at FROM {$wpdb->prefix}" . self::TABLE_SUFFIX . " WHERE mobile = %s AND purpose = %s ORDER BY id DESC LIMIT 1",
            $mobile, $purpose
        ) );
        if ( ! $last_sent ) return 0;
        $elapsed  = current_time( 'timestamp' ) - strtotime( $last_sent );
        return max( 0, (int) ( DENTAL_OTP_RESEND_COOLDOWN - $elapsed ) );
    }

    private static function invalidate_previous_codes( string $mobile, string $purpose ): void {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . self::TABLE_SUFFIX,
            [ 'is_verified' => 1, 'attempts' => DENTAL_OTP_MAX_ATTEMPTS ],
            [ 'mobile' => $mobile, 'purpose' => $purpose, 'is_verified' => 0 ],
            [ '%d', '%d' ], [ '%s', '%s', '%d' ]
        );
    }

    private static function generate_verification_token( string $mobile, string $purpose, int $otp_id ): string {
        $token         = wp_generate_password( 48, false );
        $transient_key = self::get_token_transient_key( $mobile, $purpose );
        set_transient( $transient_key, $token, 10 * MINUTE_IN_SECONDS );
        return $token;
    }

    private static function get_token_transient_key( string $mobile, string $purpose ): string {
        return 'dental_otp_token_' . md5( $mobile . $purpose );
    }

    private static function hash_otp( string $otp ): string {
        $salt = defined( 'DENTAL_ENCRYPTION_KEY' ) ? DENTAL_ENCRYPTION_KEY : wp_salt( 'auth' );
        return hash_hmac( 'sha256', $otp, $salt );
    }

    public static function normalize_mobile( string $mobile ): string {
        $mobile = Dental_Jalali::to_english_digits( $mobile );
        $mobile = preg_replace( '/\D/', '', $mobile );
        if ( strlen( $mobile ) === 10 && $mobile[0] === '9' ) $mobile = '0' . $mobile;
        if ( str_starts_with( $mobile, '98' ) && strlen( $mobile ) === 12 ) $mobile = '0' . substr( $mobile, 2 );
        return $mobile;
    }

    public static function is_valid_mobile( string $mobile ): bool {
        return (bool) preg_match( '/^09[0-9]{9}$/', $mobile );
    }

    private static function get_client_ip(): string {
        foreach ( [ 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ] as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = trim( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) )[0] );
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) return $ip;
            }
        }
        return '0.0.0.0';
    }

    public static function cleanup_expired_codes(): int {
        global $wpdb;
        $deleted = (int) $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}dental_otp_codes WHERE expires_at < %s",
            current_time( 'mysql' )
        ) );
        return $deleted;
    }
}
