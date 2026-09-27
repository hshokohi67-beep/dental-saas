<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class Dental_Dev_Logger
 *
 * مرکز مدیریت لاگ، Mock و شبیه‌سازی سرویس‌های خارجی در حالت توسعه.
 * تمام فراخوانی‌های SMS و پرداخت از این کلاس عبور می‌کنند.
 */
class Dental_Dev_Logger {

    // ─── رنگ‌ها برای خروجی لاگ ─────────────────────────────────────────────
    private const LEVEL_COLORS = [
        'OTP'     => '🟡',
        'SMS'     => '📱',
        'PAYMENT' => '💳',
        'AUTH'    => '🔐',
        'ERROR'   => '🔴',
        'INFO'    => '🔵',
        'SUCCESS' => '🟢',
        'MOCK'    => '🧪',
    ];

    /**
     * آیا حالت توسعه فعال است؟
     */
    public static function is_dev_mode(): bool {
        return defined( 'DENTAL_DEV_MODE' ) && DENTAL_DEV_MODE === true;
    }

    /**
     * آیا Console Log فعال است؟
     */
    public static function is_console_log_enabled(): bool {
        return defined( 'DENTAL_DEV_CONSOLE_LOG' ) && DENTAL_DEV_CONSOLE_LOG === true;
    }

    /**
     * لاگ اصلی — هم در debug.log هم در صف Console Log ذخیره می‌کند
     *
     * @param string $level  سطح لاگ: OTP, SMS, PAYMENT, AUTH, ERROR, INFO
     * @param string $message پیام
     * @param array  $context داده‌های اضافی
     */
    public static function log( string $level, string $message, array $context = [] ): void {
        if ( ! self::is_dev_mode() && $level !== 'ERROR' ) {
            return;
        }

        $icon      = self::LEVEL_COLORS[ $level ] ?? '⚪';
        $timestamp = current_time( 'Y-m-d H:i:s' );
        $prefix    = "[DENTAL-{$level}]";

        // ─── WordPress debug.log ─────────────────────────────────────────────
        if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            $log_message = sprintf(
                '%s %s %s | %s',
                $prefix,
                $icon,
                $message,
                empty( $context ) ? '' : wp_json_encode( $context, JSON_UNESCAPED_UNICODE )
            );
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions
            error_log( $log_message );
        }

        // ─── ذخیره در Session برای Console Log ──────────────────────────────
        if ( self::is_console_log_enabled() ) {
            self::push_to_console_queue( [
                'level'     => $level,
                'icon'      => $icon,
                'message'   => $message,
                'context'   => $context,
                'timestamp' => $timestamp,
            ] );
        }

        // ─── ذخیره در جدول لاگ داخلی ───────────────────────────────────────
        if ( defined( 'DENTAL_LOG_API_CALLS' ) && DENTAL_LOG_API_CALLS ) {
            self::persist_log( $level, $message, $context );
        }
    }

    /**
     * اضافه کردن لاگ به صف Console (ذخیره در transient)
     */
    private static function push_to_console_queue( array $entry ): void {
        $queue   = get_transient( 'dental_console_log_queue' ) ?: [];
        $queue[] = $entry;

        // حداکثر ۵۰ آیتم در صف نگه می‌داریم
        if ( count( $queue ) > 50 ) {
            $queue = array_slice( $queue, -50 );
        }

        set_transient( 'dental_console_log_queue', $queue, MINUTE_IN_SECONDS * 5 );
    }

    /**
     * دریافت صف Console Log و پاک کردن آن
     */
    public static function flush_console_queue(): array {
        $queue = get_transient( 'dental_console_log_queue' ) ?: [];
        delete_transient( 'dental_console_log_queue' );
        return $queue;
    }

    /**
     * چاپ Console Log به عنوان اسکریپت JS در footer
     * این متد را در admin_footer یا wp_footer قلاب کنید
     */
    public static function render_console_logs(): void {
        if ( ! self::is_dev_mode() || ! self::is_console_log_enabled() ) {
            return;
        }

        $queue = self::flush_console_queue();
        if ( empty( $queue ) ) {
            return;
        }

        echo "<script>\n/* 🧪 Dental Clinic Dev Mode — Console Logs */\n";
        echo "console.groupCollapsed('🦷 Dental Clinic Dev Logs (" . count( $queue ) . " entries)');\n";

        foreach ( $queue as $entry ) {
            $level   = esc_js( $entry['level'] );
            $icon    = esc_js( $entry['icon'] );
            $message = esc_js( $entry['message'] );
            $time    = esc_js( $entry['timestamp'] );
            $context = wp_json_encode( $entry['context'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );

            $console_method = in_array( $entry['level'], [ 'ERROR' ], true ) ? 'error' : 'log';
            if ( in_array( $entry['level'], [ 'OTP', 'MOCK' ], true ) ) {
                $console_method = 'warn';
            }

            echo "console.{$console_method}('{$icon} [{$level}] {$time} | {$message}', {$context});\n";
        }

        echo "console.groupEnd();\n";
        echo "</script>\n";
    }

    /**
     * ذخیره لاگ در آپشن وردپرس (برای نمایش در پنل ادمین)
     */
    private static function persist_log( string $level, string $message, array $context ): void {
        $logs   = get_option( 'dental_dev_logs', [] );
        $logs[] = [
            'level'     => $level,
            'message'   => $message,
            'context'   => $context,
            'timestamp' => current_time( 'mysql' ),
            'user_id'   => get_current_user_id(),
        ];

        // فقط ۱۰۰ لاگ آخر را نگه می‌داریم
        if ( count( $logs ) > 100 ) {
            $logs = array_slice( $logs, -100 );
        }

        update_option( 'dental_dev_logs', $logs, false );
    }

    /**
     * دریافت لاگ‌های ذخیره‌شده (برای نمایش در پنل ادمین)
     */
    public static function get_persisted_logs( int $limit = 50 ): array {
        $logs = get_option( 'dental_dev_logs', [] );
        return array_slice( array_reverse( $logs ), 0, $limit );
    }

    /**
     * پاک کردن همه لاگ‌ها
     */
    public static function clear_logs(): void {
        delete_option( 'dental_dev_logs' );
        delete_transient( 'dental_console_log_queue' );
    }

    /**
     * نمایش نوار هشدار Dev Mode در بالای پنل ادمین
     */
    public static function render_dev_mode_banner(): void {
        if ( ! self::is_dev_mode() ) {
            return;
        }
        ?>
        <div id="dental-dev-banner" style="
            background: linear-gradient(90deg, #F0A500, #E8890A);
            color: #1A2733;
            padding: 8px 20px;
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            font-size: 13px;
            font-weight: 700;
            direction: rtl;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid #c47800;
            position: sticky;
            top: 32px;
            z-index: 9999;
        ">
            <span style="font-size:18px;">🧪</span>
            <span>حالت توسعه (Dev Mode) فعال است — پیامک‌های OTP واقعاً ارسال نمی‌شوند.</span>
            <span style="background:rgba(0,0,0,0.15); padding:2px 10px; border-radius:20px; font-size:11px;">
                کد OTP ثابت: <?php echo esc_html( DENTAL_DEV_OTP_CODE ); ?>
            </span>
            <span style="margin-right:auto;">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=dental-settings&tab=dev' ) ); ?>"
                   style="color:#1A2733; text-decoration:underline; font-size:12px;">
                   مشاهده لاگ‌ها ←
                </a>
            </span>
        </div>
        <?php
    }

    // ─── Mock Responses ──────────────────────────────────────────────────────

    /**
     * پاسخ Mock برای ارسال SMS
     *
     * @param string $mobile شماره موبایل
     * @param string $message متن پیامک
     * @return array{success: bool, message_id: string, status: string}
     */
    public static function mock_sms_response( string $mobile, string $message ): array {
        $mock_id = 'MOCK_' . strtoupper( wp_generate_password( 8, false ) );

        self::log( 'SMS', "Mock SMS ارسال شد به {$mobile}", [
            'mock_id' => $mock_id,
            'message' => $message,
            'mobile'  => $mobile,
        ] );

        return [
            'success'    => true,
            'message_id' => $mock_id,
            'status'     => 'mock_sent',
            'gateway'    => 'mock',
            'sent_at'    => current_time( 'mysql' ),
        ];
    }

    /**
     * پاسخ Mock برای تأیید پرداخت
     *
     * @param string $ref_id  شناسه مرجع
     * @param float  $amount  مبلغ
     * @return array{success: bool, transaction_id: string, status: string}
     */
    public static function mock_payment_verify( string $ref_id, float $amount ): array {
        $mock_tx = 'TX_MOCK_' . time();

        self::log( 'PAYMENT', "Mock Payment تأیید شد", [
            'ref_id'         => $ref_id,
            'amount'         => $amount,
            'transaction_id' => $mock_tx,
        ] );

        return [
            'success'        => true,
            'transaction_id' => $mock_tx,
            'status'         => 'mock_verified',
            'amount'         => $amount,
            'verified_at'    => current_time( 'mysql' ),
        ];
    }

    /**
     * تولید کد OTP — در Dev Mode از کد ثابت استفاده می‌کند
     *
     * @param int $length طول کد
     * @return string
     */
    public static function generate_otp( int $length = 6 ): string {
        if ( self::is_dev_mode() && defined( 'DENTAL_DEV_OTP_CODE' ) ) {
            $otp = DENTAL_DEV_OTP_CODE;
            self::log( 'OTP', "کد OTP ثابت استفاده شد (Dev Mode)", [ 'otp' => $otp ] );
            return $otp;
        }

        // تولید واقعی: cryptographically secure
        $digits = '';
        for ( $i = 0; $i < $length; $i++ ) {
            $digits .= random_int( 0, 9 );
        }

        return $digits;
    }
}
