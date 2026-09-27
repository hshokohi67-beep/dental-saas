<?php
defined('ABSPATH') || exit;

class Dental_Booking_Autoloader {

    private static array $map = [
        // Core
        'Dental_Booking_Core'        => 'includes/class-booking-core.php',
        'Dental_Booking_Installer'   => 'includes/class-booking-installer.php',

        // Models
        'Dental_Booking_Doctor'      => 'includes/models/class-booking-doctor.php',
        'Dental_Booking_Shift'       => 'includes/models/class-booking-shift.php',
        'Dental_Booking_Appointment' => 'includes/models/class-booking-appointment.php',

        // Admin Pages
        'Dental_Booking_Admin'       => 'admin/class-booking-admin.php',
        'Dental_Page_Booking_Doctors'=> 'admin/pages/class-page-booking-doctors.php',
        'Dental_Page_Booking_Reports'=> 'admin/pages/class-page-booking-reports.php',
        'Dental_Page_Booking_List'   => 'admin/pages/class-page-booking-list.php',
        'Dental_Page_Booking_Calendar'=> 'admin/pages/class-page-booking-calendar.php',
        'Dental_Page_Booking_Settings'=> 'admin/pages/class-page-booking-settings.php',

        // Frontend
        'Dental_Booking_Frontend'    => 'frontend/class-booking-frontend.php',
        'Dental_Booking_Wizard'      => 'frontend/class-booking-wizard.php',

        // API
        'Dental_Booking_API'         => 'includes/api/class-booking-api.php',

        // SMS
        'Dental_Booking_SMS'         => 'includes/class-booking-sms.php',
    ];

    public static function register(): void {
        spl_autoload_register(function(string $class): void {
            if (!isset(self::$map[$class])) return;
            $file = DENTAL_BOOKING_PATH . self::$map[$class];
            if (file_exists($file)) require_once $file;
        });
    }
}

Dental_Booking_Autoloader::register();
