<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class Dental_Endpoint_Auth
 *
 * REST API endpoints برای احراز هویت
 * هم پلاگین اول و هم پلاگین دوم از همین endpoints استفاده می‌کنند
 *
 * Base: /wp-json/dental/v1/auth/
 */
class Dental_Endpoint_Auth {

    private const NAMESPACE = 'dental/v1';

    /**
     * ثبت routes
     */
    public function register_routes(): void {
        // ارسال OTP
        register_rest_route( self::NAMESPACE, '/auth/send-otp', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'send_otp' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'mobile'  => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'description'       => 'شماره موبایل (09xxxxxxxxx)',
                ],
                'purpose' => [
                    'required'          => false,
                    'type'              => 'string',
                    'default'           => 'login',
                    'enum'              => [ 'login', 'register', 'booking_confirm' ],
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ] );

        // تأیید OTP
        register_rest_route( self::NAMESPACE, '/auth/verify-otp', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'verify_otp' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'mobile'  => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'otp'     => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'purpose' => [
                    'required'          => false,
                    'type'              => 'string',
                    'default'           => 'login',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'name'    => [
                    'required'          => false,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ] );

        // تأیید Token (برای پلاگین دوم)
        register_rest_route( self::NAMESPACE, '/auth/validate-token', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'validate_token' ],
            'permission_callback' => [ $this, 'check_plugin_permission' ],
            'args'                => [
                'token'   => [ 'required' => true,  'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
                'mobile'  => [ 'required' => true,  'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
                'purpose' => [ 'required' => false, 'type' => 'string', 'default' => 'booking_confirm', 'sanitize_callback' => 'sanitize_text_field' ],
            ],
        ] );

        // ساخت بیمار از پلاگین دوم
        register_rest_route( self::NAMESPACE, '/auth/create-patient', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'create_patient' ],
            'permission_callback' => [ $this, 'check_plugin_permission' ],
            'args'                => [
                'mobile'  => [ 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
                'token'   => [ 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
                'name'    => [ 'required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
            ],
        ] );

        // خروج
        register_rest_route( self::NAMESPACE, '/auth/logout', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'logout' ],
            'permission_callback' => 'is_user_logged_in',
        ] );

        // وضعیت کاربر جاری
        register_rest_route( self::NAMESPACE, '/auth/me', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_current_user_info' ],
            'permission_callback' => '__return_true',
        ] );
    }

    // ─── Callbacks ────────────────────────────────────────────────────────────

    /**
     * POST /auth/send-otp
     */
    public function send_otp( WP_REST_Request $request ): WP_REST_Response {
        $mobile  = $request->get_param( 'mobile' );
        $purpose = $request->get_param( 'purpose' );

        $result = Dental_Auth_Manager::send_otp( $mobile, $purpose );

        $status = $result['success'] ? 200 : 422;

        // در Dev Mode اطلاعات اضافی برمی‌گردانیم
        if ( Dental_Dev_Logger::is_dev_mode() ) {
            $result['_dev_notice'] = 'حالت توسعه فعال است. OTP واقعی ارسال نشد.';
        }

        return new WP_REST_Response( $result, $status );
    }

    /**
     * POST /auth/verify-otp
     */
    public function verify_otp( WP_REST_Request $request ): WP_REST_Response {
        $mobile  = $request->get_param( 'mobile' );
        $otp     = $request->get_param( 'otp' );
        $purpose = $request->get_param( 'purpose' );
        $name    = $request->get_param( 'name' ) ?? '';

        if ( $purpose === 'login' || $purpose === 'register' ) {
            // ورود یا ثبت‌نام کامل
            $result = Dental_Auth_Manager::verify_otp_and_authenticate(
                $mobile,
                $otp,
                [ 'name' => $name ]
            );
        } else {
            // فقط تأیید (برای booking_confirm)
            $result = Dental_Auth_Manager::verify_otp_for_plugin( $mobile, $otp, $purpose );
        }

        $status = $result['success'] ? 200 : 422;
        return new WP_REST_Response( $result, $status );
    }

    /**
     * POST /auth/validate-token
     * برای استفاده پلاگین دوم
     */
    public function validate_token( WP_REST_Request $request ): WP_REST_Response {
        $token   = $request->get_param( 'token' );
        $mobile  = $request->get_param( 'mobile' );
        $purpose = $request->get_param( 'purpose' );

        $result = Dental_Auth_Manager::validate_token_and_get_user( $token, $mobile, $purpose );

        $status = $result['valid'] ? 200 : 401;
        return new WP_REST_Response( $result, $status );
    }

    /**
     * POST /auth/create-patient
     * برای استفاده پلاگین دوم
     */
    public function create_patient( WP_REST_Request $request ): WP_REST_Response {
        $result = Dental_Auth_Manager::create_patient_from_booking(
            $request->get_param( 'mobile' ),
            $request->get_param( 'token' ),
            [ 'name' => $request->get_param( 'name' ) ?? 'بیمار' ]
        );

        $status = $result['success'] ? 201 : 422;
        return new WP_REST_Response( $result, $status );
    }

    /**
     * POST /auth/logout
     */
    public function logout( WP_REST_Request $request ): WP_REST_Response {
        Dental_Auth_Manager::logout();
        return new WP_REST_Response( [ 'success' => true, 'message' => 'با موفقیت خارج شدید.' ], 200 );
    }

    /**
     * GET /auth/me
     */
    public function get_current_user_info( WP_REST_Request $request ): WP_REST_Response {
        if ( ! is_user_logged_in() ) {
            return new WP_REST_Response( [ 'logged_in' => false ], 200 );
        }

        $user            = wp_get_current_user();
        $patient_post_id = Dental_Auth_Manager::get_current_patient_post_id();

        return new WP_REST_Response( [
            'logged_in'       => true,
            'user_id'         => $user->ID,
            'display_name'    => $user->display_name,
            'role'            => Dental_Roles_Manager::get_dental_role(),
            'patient_post_id' => $patient_post_id,
            'wallet_balance'  => $patient_post_id
                ? (float) get_post_meta( $patient_post_id, '_wallet_balance', true )
                : 0,
        ], 200 );
    }

    /**
     * بررسی مجوز برای endpoint های مشترک با پلاگین دوم
     * با استفاده از Secret Key مشترک بین دو پلاگین
     */
    public function check_plugin_permission( WP_REST_Request $request ): bool {
        // اگر کاربر ادمین است، همیشه مجاز
        if ( current_user_can( 'manage_options' ) ) {
            return true;
        }

        // بررسی X-Dental-Plugin-Key header
        $provided_key = $request->get_header( 'X-Dental-Plugin-Key' );
        $stored_key   = get_option( 'dental_plugin_shared_key', '' );

        if ( empty( $stored_key ) ) {
            // اگر کلید تنظیم نشده، در محیط لوکال اجازه می‌دهیم
            return Dental_Dev_Logger::is_dev_mode();
        }

        return $provided_key && hash_equals( $stored_key, $provided_key );
    }
}
