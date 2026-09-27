<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class Dental_Auth_Manager
 *
 * Single Source of Truth برای احراز هویت:
 * - ثبت‌نام بیمار جدید با موبایل
 * - ورود بیمار موجود
 * - مدیریت Session های وردپرس
 * - API توابع برای استفاده پلاگین دوم
 */
class Dental_Auth_Manager {

    // ─── Constants ────────────────────────────────────────────────────────────
    private const META_MOBILE        = '_dental_mobile';
    private const META_PATIENT_ID    = '_dental_patient_post_id';
    private const META_VERIFIED      = '_dental_mobile_verified';
    private const META_LAST_LOGIN    = '_dental_last_login';
    private const META_REGISTER_DATE = '_dental_registered_at';

    /**
     * مرحله ۱ — ارسال OTP (مشترک بین ثبت‌نام و ورود)
     * پلاگین دوم هم از همین endpoint استفاده می‌کند
     *
     * @param string $mobile  شماره موبایل
     * @param string $purpose login | register | booking_confirm
     * @return array
     */
    public static function send_otp( string $mobile, string $purpose = 'login' ): array {
        Dental_Dev_Logger::log( 'AUTH', "درخواست OTP", [ 'mobile' => $mobile, 'purpose' => $purpose ] );
        return Dental_OTP_Manager::request( $mobile, $purpose );
    }

    /**
     * مرحله ۲ — تأیید OTP و ورود/ثبت‌نام خودکار
     *
     * اگر بیمار وجود داشت: لاگین
     * اگر بیمار جدید بود: ثبت‌نام + لاگین
     *
     * @param string $mobile  شماره موبایل
     * @param string $otp     کد تأیید
     * @param array  $extra   اطلاعات اضافی برای ثبت‌نام: {name, national_id}
     * @return array{success: bool, action: 'login'|'register', user_id: int, token: string}
     */
    public static function verify_otp_and_authenticate(
        string $mobile,
        string $otp,
        array  $extra = []
    ): array {
        // ─── تأیید OTP ────────────────────────────────────────────────────────
        $verify_result = Dental_OTP_Manager::verify( $mobile, $otp, 'login' );

        if ( ! $verify_result['success'] ) {
            return $verify_result;
        }

        $verification_token = $verify_result['verification_token'];

        // ─── پیدا کردن یا ساختن کاربر وردپرس ────────────────────────────────
        $wp_user = self::find_user_by_mobile( $mobile );

        if ( $wp_user ) {
            // ─── کاربر موجود — ورود ──────────────────────────────────────────
            $result = self::login_existing_user( $wp_user, $mobile, $verification_token );
        } else {
            // ─── مهم: قبل از ساخت پرونده جدید، بررسی کن آیا پرونده بیماری با
            // این شماره از قبل وجود دارد (مثلاً توسط ادمین دستی ساخته شده و
            // هنوز به هیچ حساب کاربری وصل نشده). بدون این چک، دو پرونده
            // جداگانه با یک شماره موبایل ساخته می‌شود.
            $existing_patient_id = self::find_unlinked_patient_post_by_mobile( $mobile );

            if ( $existing_patient_id ) {
                // پرونده بیمار از قبل هست — به‌جای ساخت پرونده جدید، کاربر
                // بسازیم و به همین پرونده موجود وصلش کنیم.
                $result = self::claim_existing_patient( $existing_patient_id, $mobile, $extra, $verification_token );
            } else {
                $conflict_patient_id = self::find_linked_patient_post_by_mobile( $mobile );
                if ( $conflict_patient_id ) {
                    // این شماره به پرونده‌ای وصل است که از قبل به یک حساب
                    // کاربری دیگر متصل شده (تعارض واقعی) — به‌جای ساخت خاموش
                    // یک پرونده تکراری، پیام خطا برگردان.
                    Dental_Dev_Logger::log( 'ERROR', 'تعارض شماره موبایل با پرونده بیمار موجود', [
                        'mobile'                 => $mobile,
                        'conflicting_patient_id' => $conflict_patient_id,
                    ] );
                    return [
                        'success' => false,
                        'message' => 'این شماره موبایل قبلاً برای پرونده دیگری ثبت شده است. لطفاً با پشتیبانی کلینیک تماس بگیرید.',
                        'code'    => 'mobile_conflict',
                    ];
                }

                // ─── کاربر جدید — ثبت‌نام ────────────────────────────────────
                $result = self::register_new_patient( $mobile, $extra, $verification_token );
            }
        }

        return $result;
    }

    /**
     * پیدا کردن پرونده بیماری که این شماره را دارد ولی هنوز به هیچ
     * حساب کاربری وصل نشده (مثلاً توسط ادمین دستی ساخته شده)
     */
    private static function find_unlinked_patient_post_by_mobile( string $mobile ): int {
        global $wpdb;
        $id = $wpdb->get_var( $wpdb->prepare(
            "SELECT p.ID FROM {$wpdb->posts} p
             JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                  AND pm.meta_key = '_patient_mobile' AND pm.meta_value = %s
             LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id
                  AND pm2.meta_key = '_patient_wp_user_id'
             WHERE p.post_type = 'dental_patient' AND p.post_status = 'publish'
               AND ( pm2.meta_value IS NULL OR pm2.meta_value = '' OR pm2.meta_value = '0' )
             LIMIT 1",
            $mobile
        ) );
        return (int) $id;
    }

    /**
     * پیدا کردن پرونده بیماری که این شماره را دارد و از قبل به یک
     * حساب کاربری وصل شده (برای تشخیص تعارض واقعی)
     */
    private static function find_linked_patient_post_by_mobile( string $mobile ): int {
        global $wpdb;
        $id = $wpdb->get_var( $wpdb->prepare(
            "SELECT p.ID FROM {$wpdb->posts} p
             JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                  AND pm.meta_key = '_patient_mobile' AND pm.meta_value = %s
             WHERE p.post_type = 'dental_patient' AND p.post_status = 'publish'
             LIMIT 1",
            $mobile
        ) );
        return (int) $id;
    }

    /**
     * اتصال کاربر جدید به یک پرونده بیمار موجود (به‌جای ساخت پرونده تازه)
     */
    private static function claim_existing_patient(
        int     $patient_post_id,
        string  $mobile,
        array   $data,
        ?string $token
    ): array {
        $username      = 'patient_' . substr( $mobile, -8 );
        $username      = self::ensure_unique_username( $username );
        $existing_post = get_post( $patient_post_id );
        $display_name  = $existing_post
            ? $existing_post->post_title
            : ( ! empty( $data['name'] ) ? sanitize_text_field( $data['name'] ) : 'بیمار' );

        $user_id = wp_insert_user( [
            'user_login'   => $username,
            'user_pass'    => wp_generate_password( 20, true, true ),
            'display_name' => $display_name,
            'role'         => 'dental_patient',
            'description'  => 'بیمار متصل‌شده به پرونده موجود از طریق موبایل',
        ] );

        if ( is_wp_error( $user_id ) ) {
            Dental_Dev_Logger::log( 'ERROR', 'خطا در ساخت کاربر برای پرونده موجود', [
                'mobile'          => $mobile,
                'patient_post_id' => $patient_post_id,
                'error'           => $user_id->get_error_message(),
            ] );
            return [ 'success' => false, 'message' => 'خطا در ثبت‌نام. لطفاً دوباره تلاش کنید.', 'code' => 'user_creation_failed' ];
        }

        update_user_meta( $user_id, self::META_MOBILE,        $mobile );
        update_user_meta( $user_id, self::META_VERIFIED,      1 );
        update_user_meta( $user_id, self::META_LAST_LOGIN,    current_time( 'mysql' ) );
        update_user_meta( $user_id, self::META_REGISTER_DATE, current_time( 'mysql' ) );
        update_user_meta( $user_id, self::META_PATIENT_ID,    $patient_post_id );

        update_post_meta( $patient_post_id, '_patient_wp_user_id', $user_id );

        // فقط اگر قبلاً مقداری نداشته، کیف‌پول/امتیاز را صفر کن — ممکن است
        // پرونده موجود از قبل داده مالی داشته باشد.
        if ( get_post_meta( $patient_post_id, '_wallet_balance', true ) === '' ) {
            update_post_meta( $patient_post_id, '_wallet_balance', 0.00 );
        }
        if ( get_post_meta( $patient_post_id, '_loyalty_points', true ) === '' ) {
            update_post_meta( $patient_post_id, '_loyalty_points', 0 );
        }

        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );

        do_action( 'dental_patient_registered', $user_id, $patient_post_id, $mobile );

        Dental_Dev_Logger::log( 'AUTH', 'کاربر جدید به پرونده بیمار موجود متصل شد', [
            'user_id'         => $user_id,
            'patient_post_id' => $patient_post_id,
            'mobile'          => $mobile,
        ] );

        return [
            'success'         => true,
            'action'          => 'claimed_existing',
            'user_id'         => $user_id,
            'patient_post_id' => $patient_post_id,
            'display_name'    => $display_name,
            'token'           => $token,
            'redirect_url'    => self::get_patient_portal_url(),
        ];
    }

    /**
     * تأیید OTP فقط برای بازگرداندن Token (برای پلاگین دوم)
     * بدون لاگین خودکار — فقط اعتبارسنجی شماره موبایل
     *
     * @param string $mobile  شماره موبایل
     * @param string $otp     کد تأیید
     * @param string $purpose booking_confirm | ...
     * @return array{success: bool, verification_token: string, mobile: string}
     */
    public static function verify_otp_for_plugin( string $mobile, string $otp, string $purpose ): array {
        Dental_Dev_Logger::log( 'AUTH', "تأیید OTP از پلاگین خارجی", [
            'mobile'  => $mobile,
            'purpose' => $purpose,
        ] );

        return Dental_OTP_Manager::verify( $mobile, $otp, $purpose );
    }

    /**
     * اعتبارسنجی Token (برای پلاگین دوم در گام نهایی رزرو)
     *
     * @param string $token   توکن دریافتی از مرحله verify
     * @param string $mobile  شماره موبایل
     * @param string $purpose هدف
     * @return array{valid: bool, user_id?: int, patient_post_id?: int, is_new?: bool}
     */
    public static function validate_token_and_get_user(
        string $token,
        string $mobile,
        string $purpose = 'booking_confirm'
    ): array {
        $is_valid = Dental_OTP_Manager::validate_verification_token( $token, $mobile, $purpose );

        if ( ! $is_valid ) {
            return [ 'valid' => false, 'message' => 'توکن نامعتبر یا منقضی شده است.' ];
        }

        $mobile  = Dental_OTP_Manager::normalize_mobile( $mobile );
        $wp_user = self::find_user_by_mobile( $mobile );

        if ( ! $wp_user ) {
            // بیمار هنوز ثبت‌نام نکرده — باید در پلاگین دوم ثبت‌نام شود
            return [
                'valid'   => true,
                'is_new'  => true,
                'mobile'  => $mobile,
                'user_id' => null,
            ];
        }

        return [
            'valid'           => true,
            'is_new'          => false,
            'user_id'         => $wp_user->ID,
            'mobile'          => $mobile,
            'patient_post_id' => (int) get_user_meta( $wp_user->ID, self::META_PATIENT_ID, true ),
            'display_name'    => $wp_user->display_name,
        ];
    }

    /**
     * ساخت بیمار جدید از پلاگین دوم (پس از تأیید Token)
     * این Single Source of Truth برای ساخت کاربر است
     *
     * @param string $mobile  شماره موبایل (تأیید شده)
     * @param string $token   توکن تأیید شده
     * @param array  $data    {name, national_id}
     * @return array{success: bool, user_id: int, patient_post_id: int}
     */
    public static function create_patient_from_booking(
        string $mobile,
        string $token,
        array  $data
    ): array {
        // اعتبارسنجی Token یکبار دیگر
        $is_valid = Dental_OTP_Manager::validate_verification_token( $token, $mobile, 'booking_confirm' );

        if ( ! $is_valid ) {
            Dental_Dev_Logger::log( 'ERROR', 'توکن نامعتبر در create_patient_from_booking', [
                'mobile' => $mobile,
            ] );
            return [ 'success' => false, 'message' => 'توکن نامعتبر است. لطفاً دوباره احراز هویت کنید.' ];
        }

        // چک کن شاید در این چند ثانیه کاربر ایجاد شده باشد
        $existing = self::find_user_by_mobile( $mobile );
        if ( $existing ) {
            return [
                'success'         => true,
                'user_id'         => $existing->ID,
                'patient_post_id' => (int) get_user_meta( $existing->ID, self::META_PATIENT_ID, true ),
                'action'          => 'existing',
            ];
        }

        // ─── همین‌جا هم چک شماره تکراری با پرونده موجود ─────────────────────
        $existing_patient_id = self::find_unlinked_patient_post_by_mobile( $mobile );
        if ( $existing_patient_id ) {
            return self::claim_existing_patient( $existing_patient_id, $mobile, $data, $token );
        }

        return self::register_new_patient( $mobile, $data, null );
    }

    // ─── متدهای داخلی ────────────────────────────────────────────────────────

    /**
     * لاگین کاربر موجود
     */
    private static function login_existing_user( WP_User $user, string $mobile, ?string $token ): array {
        // بروزرسانی آخرین ورود
        update_user_meta( $user->ID, self::META_LAST_LOGIN, current_time( 'mysql' ) );
        update_user_meta( $user->ID, self::META_VERIFIED, 1 );

        // ایجاد Session وردپرس
        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, true );

        $patient_post_id = (int) get_user_meta( $user->ID, self::META_PATIENT_ID, true );

        Dental_Dev_Logger::log( 'AUTH', "ورود موفق بیمار موجود", [
            'user_id'         => $user->ID,
            'mobile'          => $mobile,
            'patient_post_id' => $patient_post_id,
        ] );

        return [
            'success'         => true,
            'action'          => 'login',
            'user_id'         => $user->ID,
            'patient_post_id' => $patient_post_id,
            'display_name'    => $user->display_name,
            'token'           => $token,
            'redirect_url'    => self::get_patient_portal_url(),
        ];
    }

    /**
     * ثبت‌نام بیمار جدید
     */
    private static function register_new_patient(
        string $mobile,
        array  $data,
        ?string $token
    ): array {
        // ─── ساخت نام کاربری یکتا ─────────────────────────────────────────
        $username     = 'patient_' . substr( $mobile, -8 );
        $username     = self::ensure_unique_username( $username );
        $display_name = ! empty( $data['name'] ) ? sanitize_text_field( $data['name'] ) : 'بیمار';

        // ─── ایجاد کاربر وردپرس ──────────────────────────────────────────────
        $user_id = wp_insert_user( [
            'user_login'   => $username,
            'user_pass'    => wp_generate_password( 20, true, true ),
            'display_name' => $display_name,
            'role'         => 'dental_patient',
            'description'  => 'بیمار ثبت‌نام شده از طریق موبایل',
        ] );

        if ( is_wp_error( $user_id ) ) {
            Dental_Dev_Logger::log( 'ERROR', 'خطا در ساخت کاربر وردپرس', [
                'mobile' => $mobile,
                'error'  => $user_id->get_error_message(),
            ] );
            return [ 'success' => false, 'message' => 'خطا در ثبت‌نام. لطفاً دوباره تلاش کنید.', 'code' => 'user_creation_failed' ];
        }

        // ─── ذخیره Meta کاربر ────────────────────────────────────────────────
        update_user_meta( $user_id, self::META_MOBILE,        $mobile );
        update_user_meta( $user_id, self::META_VERIFIED,      1 );
        update_user_meta( $user_id, self::META_LAST_LOGIN,    current_time( 'mysql' ) );
        update_user_meta( $user_id, self::META_REGISTER_DATE, current_time( 'mysql' ) );

        if ( ! empty( $data['national_id'] ) ) {
            // ذخیره رمزنگاری‌شده کد ملی
            update_user_meta(
                $user_id,
                '_dental_national_id',
                self::encrypt( sanitize_text_field( $data['national_id'] ) )
            );
        }

        // ─── ساخت Custom Post Type بیمار ──────────────────────────────────────
        $patient_post_id = self::create_patient_post( $user_id, $mobile, $display_name, $data );

        if ( ! $patient_post_id ) {
            // Rollback کاربر وردپرس
            wp_delete_user( $user_id );
            return [ 'success' => false, 'message' => 'خطا در ایجاد پرونده بیمار.', 'code' => 'patient_post_failed' ];
        }

        // ─── لینک دوطرفه: user ↔ patient post ────────────────────────────────
        update_user_meta( $user_id, self::META_PATIENT_ID, $patient_post_id );
        update_post_meta( $patient_post_id, '_patient_wp_user_id', $user_id );

        // ─── ایجاد کیف پول ───────────────────────────────────────────────────
        update_post_meta( $patient_post_id, '_wallet_balance', 0.00 );
        update_post_meta( $patient_post_id, '_loyalty_points', 0 );

        // ─── ایجاد Session ────────────────────────────────────────────────────
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );

        // ─── Action Hook برای سایر ماژول‌ها ──────────────────────────────────
        do_action( 'dental_patient_registered', $user_id, $patient_post_id, $mobile );

        Dental_Dev_Logger::log( 'AUTH', "بیمار جدید ثبت‌نام شد", [
            'user_id'         => $user_id,
            'patient_post_id' => $patient_post_id,
            'mobile'          => $mobile,
            'name'            => $display_name,
        ] );

        return [
            'success'         => true,
            'action'          => 'register',
            'user_id'         => $user_id,
            'patient_post_id' => $patient_post_id,
            'display_name'    => $display_name,
            'token'           => $token,
            'redirect_url'    => self::get_patient_portal_url(),
        ];
    }

    /**
     * ساخت CPT بیمار
     */
    private static function create_patient_post(
        int    $user_id,
        string $mobile,
        string $name,
        array  $data
    ): int|false {
        $post_id = wp_insert_post( [
            'post_type'   => 'dental_patient',
            'post_title'  => $name,
            'post_status' => 'publish',
            'post_author' => 1,
            'meta_input'  => [
                '_patient_mobile'      => $mobile,
                '_patient_wp_user_id'  => $user_id,
                '_patient_name'        => $name,
                '_patient_gender'      => sanitize_text_field( $data['gender'] ?? '' ),
                '_patient_dob_jalali'  => sanitize_text_field( $data['dob'] ?? '' ),
                '_wallet_balance'      => 0.00,
                '_loyalty_points'      => 0,
                '_systemic_diseases'   => wp_json_encode( [] ),
                '_drug_allergies'      => wp_json_encode( [] ),
            ],
        ], true );

        return is_wp_error( $post_id ) ? false : (int) $post_id;
    }

    /**
     * پیدا کردن کاربر وردپرس بر اساس موبایل
     */
    public static function find_user_by_mobile( string $mobile ): WP_User|false {
        $users = get_users( [
            'meta_key'   => self::META_MOBILE,
            'meta_value' => $mobile,
            'number'     => 1,
        ] );

        return ! empty( $users ) ? $users[0] : false;
    }

    /**
     * اطمینان از یکتا بودن نام کاربری
     */
    private static function ensure_unique_username( string $base_username ): string {
        $username = $base_username;
        $counter  = 1;

        while ( username_exists( $username ) ) {
            $username = $base_username . '_' . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * آدرس پورتال بیمار
     */
    public static function get_patient_portal_url(): string {
        $page_id = get_option( 'dental_patient_portal_page_id' );
        return $page_id ? get_permalink( $page_id ) : home_url( '/portal-bimar/' );
    }

    /**
     * رمزنگاری داده حساس
     */
    // ─── رفع امنیتی حیاتی: فال‌بک قبلی فقط base64_encode بود که اصلاً
    // رمزنگاری نیست (هرکسی با یه دستور ساده می‌تونست کد ملی رو بخونه).
    // الان فال‌بک واقعی با AES-256-CBC — IV هرباره تصادفیه و همراه
    // خروجی ذخیره می‌شه (لازم برای decrypt، خودش رمز نیست).
    private static function encrypt( string $data ): string {
        if ( function_exists( 'sodium_crypto_secretbox' ) ) {
            $key   = substr( hash( 'sha256', DENTAL_ENCRYPTION_KEY . wp_salt() ), 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES );
            $nonce = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
            return 'sodium:' . base64_encode( $nonce . sodium_crypto_secretbox( $data, $nonce, $key ) );
        }
        // فال‌بک: AES-256-CBC واقعی — نه base64 خام
        $key = hash( 'sha256', DENTAL_ENCRYPTION_KEY . wp_salt(), true );
        $iv  = random_bytes( openssl_cipher_iv_length( 'aes-256-cbc' ) );
        $cipher = openssl_encrypt( $data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );
        return 'openssl:' . base64_encode( $iv . $cipher );
    }

    // ─── متد متناظر decrypt — قبلاً اصلاً وجود نداشت (چون فال‌بک واقعاً
    // رمزنگاری نبود، نیازی هم به decrypt نداشت). حالا با پیشوند تشخیص
    // می‌ده کدوم روش رمزنگاری شده و متناسبش رو باز می‌کنه.
    public static function decrypt( string $data ): string {
        if ( str_starts_with( $data, 'sodium:' ) && function_exists( 'sodium_crypto_secretbox_open' ) ) {
            $raw   = base64_decode( substr( $data, 7 ) );
            $key   = substr( hash( 'sha256', DENTAL_ENCRYPTION_KEY . wp_salt() ), 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES );
            $nonce = substr( $raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
            $cipher = substr( $raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
            $plain = sodium_crypto_secretbox_open( $cipher, $nonce, $key );
            return $plain !== false ? $plain : '';
        }
        if ( str_starts_with( $data, 'openssl:' ) ) {
            $raw = base64_decode( substr( $data, 8 ) );
            $key = hash( 'sha256', DENTAL_ENCRYPTION_KEY . wp_salt(), true );
            $iv_len = openssl_cipher_iv_length( 'aes-256-cbc' );
            $iv = substr( $raw, 0, $iv_len );
            $cipher = substr( $raw, $iv_len );
            $plain = openssl_decrypt( $cipher, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );
            return $plain !== false ? $plain : '';
        }
        // داده‌ی قدیمی sodium (قبل از این فیکس، بدون پیشوند 'sodium:') —
        // برای سازگاری با داده‌های موجود توی دیتابیس
        if ( function_exists( 'sodium_crypto_secretbox_open' ) ) {
            $raw    = base64_decode( $data, true );
            if ( $raw !== false && strlen( $raw ) > SODIUM_CRYPTO_SECRETBOX_NONCEBYTES ) {
                $key    = substr( hash( 'sha256', DENTAL_ENCRYPTION_KEY . wp_salt() ), 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES );
                $nonce  = substr( $raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
                $cipher = substr( $raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
                $plain  = @sodium_crypto_secretbox_open( $cipher, $nonce, $key );
                if ( $plain !== false ) return $plain;
            }
        }
        // داده‌ی خیلی قدیمی که با base64 خام (نسخه‌ی اول، قبل از هر
        // رمزنگاری واقعی) ذخیره شده — آخرین فال‌بک
        $decoded = base64_decode( $data, true );
        return $decoded !== false ? $decoded : '';
    }

    /**
     * خروج از سیستم
     *
     * @param int $user_id
     */
    public static function logout( int $user_id = 0 ): void {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        wp_destroy_other_sessions();
        wp_logout();

        Dental_Dev_Logger::log( 'AUTH', "خروج کاربر", [ 'user_id' => $user_id ] );
    }

    /**
     * بررسی لاگین بودن کاربر جاری به عنوان بیمار
     */
    public static function is_patient_logged_in(): bool {
        if ( ! is_user_logged_in() ) {
            return false;
        }
        return Dental_Roles_Manager::is_patient();
    }

    /**
     * دریافت patient_post_id کاربر جاری
     */
    public static function get_current_patient_post_id(): int {
        if ( ! self::is_patient_logged_in() ) {
            return 0;
        }
        return (int) get_user_meta( get_current_user_id(), self::META_PATIENT_ID, true );
    }
}
