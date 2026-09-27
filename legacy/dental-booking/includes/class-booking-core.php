<?php
defined('ABSPATH') || exit;

class Dental_Booking_Core {

    public function __construct() {
        new Dental_Booking_Admin();
        new Dental_Booking_Frontend();
        new Dental_Booking_API();
        new Dental_Booking_SMS();
        add_action('dental_booking_reminder_cron', ['Dental_Booking_SMS', 'send_reminders']);
    }
}
