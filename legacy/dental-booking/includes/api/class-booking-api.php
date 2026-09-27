<?php
defined('ABSPATH') || exit;

class Dental_Booking_API {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void {
        $ns = 'dental-booking/v1';

        register_rest_route($ns, '/slots', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_slots'],
            'permission_callback' => '__return_true',
            'args' => [
                'doctor_id' => ['required'=>true,'type'=>'integer'],
                'date'      => ['required'=>true,'type'=>'string'],
            ]
        ]);

        register_rest_route($ns, '/book', [
            'methods'             => 'POST',
            'callback'            => [$this, 'create_booking'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

        register_rest_route($ns, '/cancel/(?P<id>\d+)', [
            'methods'             => 'POST',
            'callback'            => [$this, 'cancel_booking'],
            'permission_callback' => fn() => is_user_logged_in(),
        ]);

        register_rest_route($ns, '/doctors', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_doctors'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route($ns, '/services', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_services'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function get_slots(\WP_REST_Request $req): \WP_REST_Response {
        $doctor_id = (int)$req->get_param('doctor_id');
        $date      = sanitize_text_field($req->get_param('date'));
        $slots     = Dental_Booking_Appointment::get_available_slots($doctor_id, $date);
        return rest_ensure_response(['success'=>true,'data'=>['slots'=>$slots,'date'=>$date]]);
    }

    public function create_booking(\WP_REST_Request $req): \WP_REST_Response {
        $patient_id = Dental_Frontend::get_current_patient_id();
        if (!$patient_id) return new \WP_REST_Response(['success'=>false,'message'=>'پرونده بیمار یافت نشد'],403);

        $auto   = (bool)get_option('dental_booking_auto_confirm',0);
        $result = Dental_Booking_Appointment::create(
            $patient_id,
            (int)$req->get_param('doctor_id'),
            sanitize_text_field($req->get_param('date')),
            sanitize_text_field($req->get_param('time')),
            (int)$req->get_param('shift_id'),
            sanitize_text_field($req->get_param('service_type') ?? ''),
            sanitize_textarea_field($req->get_param('notes') ?? ''),
            0, $auto
        );
        return rest_ensure_response($result);
    }

    public function cancel_booking(\WP_REST_Request $req): \WP_REST_Response {
        global $wpdb;
        $appt_id    = (int)$req->get_param('id');
        $patient_id = Dental_Frontend::get_current_patient_id();

        $appt = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_appointments WHERE id=%d AND patient_id=%d",
            $appt_id, $patient_id
        ), ARRAY_A);

        if (!$appt) return new \WP_REST_Response(['success'=>false,'message'=>'نوبت یافت نشد'],404);
        if (in_array($appt['status'],['cancelled','done'])) return new \WP_REST_Response(['success'=>false,'message'=>'امکان لغو وجود ندارد'],400);

        Dental_Booking_Appointment::update_status($appt_id,'cancelled',sanitize_text_field($req->get_param('reason')??''));
        return rest_ensure_response(['success'=>true,'message'=>'نوبت لغو شد']);
    }

    public function get_doctors(): \WP_REST_Response {
        // رفع باگ: 'administrator' اشتباهاً توی لیست بود — باعث می‌شد
        // ادمین اصلی وردپرس هم به‌عنوان یه «پزشک» توی نوبت‌دهی آنلاین
        // بیماران نمایش داده بشه، در صورتی که ادمین یه پزشک درمانگر نیست.
        $doctors = get_users(['role__in'=>['dental_doctor'],'fields'=>['ID','display_name']]);
        return rest_ensure_response(['success'=>true,'data'=>array_map(fn($d)=>['id'=>$d->ID,'name'=>$d->display_name],$doctors)]);
    }

    public function get_services(): \WP_REST_Response {
        global $wpdb;
        $services = $wpdb->get_results("SELECT id,title,duration,color FROM {$wpdb->prefix}dental_services WHERE is_active=1 ORDER BY sort_order",ARRAY_A);
        return rest_ensure_response(['success'=>true,'data'=>$services]);
    }
}
