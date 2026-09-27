<?php
/**
 * Plugin Name:       سیستم مدیریت کلینیک دندانپزشکی
 * Plugin URI:        https://your-domain.com/dental-clinic-core
 * Description:       سیستم جامع مدیریت پرونده الکترونیک، مالی، و بیماران کلینیک دندانپزشکی
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Your Name
 * License:           GPL v2 or later
 * Text Domain:       dental-clinic-core
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

// ─── Constants ────────────────────────────────────────────────────────────────
define( 'DENTAL_CORE_VERSION',  '1.0.0' );
define( 'DENTAL_CORE_FILE',     __FILE__ );
define( 'DENTAL_CORE_PATH',     plugin_dir_path( __FILE__ ) );
define( 'DENTAL_CORE_URL',      plugin_dir_url( __FILE__ ) );
define( 'DENTAL_CORE_BASENAME', plugin_basename( __FILE__ ) );
define( 'DENTAL_CORE_DB_VER',   '1.0.0' );

// ─── Config (Dev Mode و تنظیمات محیط) ────────────────────────────────────────
$config_file = DENTAL_CORE_PATH . 'dental-config.php';
if ( file_exists( $config_file ) ) {
    require_once $config_file;
}

// ─── Autoloader ───────────────────────────────────────────────────────────────
require_once DENTAL_CORE_PATH . 'includes/class-dental-autoloader.php';
Dental_Autoloader::register();

// ─── Activation / Deactivation ───────────────────────────────────────────────
register_activation_hook(__FILE__, ['Dental_Installer', 'run']);
register_deactivation_hook( __FILE__, [ 'Dental_Installer', 'deactivate' ] );

// ─── Shortcode — مستقیم register می‌شود، بدون وابستگی به Core ───────────────
// این تضمین می‌کند حتی اگر بخشی از سیستم مشکل داشت، فرم لاگین کار کند
add_action( 'init', function () {
    add_shortcode( 'dental_otp_login', function ( $atts ) {
        $atts      = shortcode_atts( [ 'redirect' => '' ], $atts ?? [], 'dental_otp_login' );
        $view_file = DENTAL_CORE_PATH . 'frontend/views/otp-login-form.php';

        if ( ! file_exists( $view_file ) ) {
            return current_user_can( 'manage_options' )
                ? '<p style="color:red;font-family:Tahoma;direction:rtl;">⚠️ فایل فرم لاگین پیدا نشد: ' . esc_html( $view_file ) . '</p>'
                : '';
        }

        ob_start();
        include $view_file;
        return (string) ob_get_clean();
    } );


}, 5 ); // priority 5 — قبل از همه چیز

// ─── Boot اصلی پلاگین ────────────────────────────────────────────────────────
add_action( 'plugins_loaded', function () {
    Dental_Core::get_instance();
}, 10 );
