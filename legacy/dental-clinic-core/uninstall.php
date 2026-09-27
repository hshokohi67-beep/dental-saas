<?php
/**
 * Uninstall Script
 * این فایل فقط زمانی اجرا می‌شود که پلاگین از وردپرس حذف شود
 */

// اگر مستقیم فراخوانی شد، خروج کن
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// بارگذاری autoloader
require_once plugin_dir_path( __FILE__ ) . 'includes/class-dental-autoloader.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-dental-installer.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/modules/rbac/class-roles-manager.php';

Dental_Autoloader::register();

// حذف کامل داده‌ها
Dental_Installer::uninstall();
