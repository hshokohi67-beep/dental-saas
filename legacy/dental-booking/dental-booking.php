<?php
/**
 * Plugin Name: Dental Booking Wizard
 * Plugin URI:  https://example.com
 * Description: سیستم نوبت‌دهی کلینیک دندانپزشکی
 * Version:     1.0.0
 * Author:      Dental Clinic Core
 * Text Domain: dental-booking
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */
defined('ABSPATH') || exit;

define('DENTAL_BOOKING_VERSION', '1.0.0');
define('DENTAL_BOOKING_URL',     plugin_dir_url(__FILE__));
define('DENTAL_BOOKING_PATH',    plugin_dir_path(__FILE__));

require_once DENTAL_BOOKING_PATH . 'includes/class-booking-autoloader.php';

register_activation_hook(__FILE__,   ['Dental_Booking_Installer', 'run']);
register_deactivation_hook(__FILE__, ['Dental_Booking_Installer', 'deactivate']);

add_action('plugins_loaded', function(){
    if(!class_exists('Dental_Jalali')){
        add_action('admin_notices', function(){
            echo '<div class="notice notice-error"><p>پلاگین <strong>Dental Booking</strong> نیاز به پلاگین <strong>Dental Clinic Core</strong> دارد.</p></div>';
        });
        return;
    }
    new Dental_Booking_Core();
});
