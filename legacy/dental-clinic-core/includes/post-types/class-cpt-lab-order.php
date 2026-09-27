<?php
defined('ABSPATH') || exit;

class Dental_CPT_Lab_Order {

    const POST_TYPE = 'dental_lab_order';

    public function __construct() {
        add_action('init', [$this, 'register']);
    }

    public function register(): void {
        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name'          => 'سفارش‌های لب',
                'singular_name' => 'سفارش لب',
            ],
            'public'       => false,
            'show_ui'      => false,
            'show_in_menu' => false,
            'supports'     => ['title'],
        ]);
    }

    /**
     * ثبت سفارش لب جدید
     */
    public static function create(
        int    $patient_id,
        int    $treatment_id,
        int    $doctor_id,
        string $lab_name,
        string $work_type,
        string $shade         = '',
        string $delivery_jalali = '',
        string $notes         = ''
    ): int|false {
        $post_id = wp_insert_post([
            'post_type'   => self::POST_TYPE,
            'post_title'  => $work_type . ' — ' . $lab_name,
            'post_status' => 'publish',
            'post_author' => $doctor_id,
        ]);

        if (is_wp_error($post_id)) return false;

        update_post_meta($post_id, '_lab_patient_id',        $patient_id);
        update_post_meta($post_id, '_lab_treatment_id',      $treatment_id);
        update_post_meta($post_id, '_lab_doctor_id',         $doctor_id);
        update_post_meta($post_id, '_lab_name',              $lab_name);
        update_post_meta($post_id, '_lab_work_type',         $work_type);
        update_post_meta($post_id, '_lab_shade',             $shade);
        update_post_meta($post_id, '_lab_delivery_jalali',   $delivery_jalali);
        update_post_meta($post_id, '_lab_sent_date_jalali',  Dental_Jalali::today());
        update_post_meta($post_id, '_lab_status',            'sent');
        update_post_meta($post_id, '_lab_notes',             $notes);

        return $post_id;
    }

    /**
     * به‌روزرسانی وضعیت سفارش
     */
    public static function update_status(int $post_id, string $status, string $received_date = ''): void {
        update_post_meta($post_id, '_lab_status', $status);
        if ($status === 'received' && $received_date) {
            update_post_meta($post_id, '_lab_received_date_jalali', $received_date);
        }
    }

    /**
     * دریافت سفارش‌های لب یک بیمار
     */
    public static function get_by_patient(int $patient_id): array {
        return get_posts([
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => [['key' => '_lab_patient_id', 'value' => $patient_id]],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
    }

    public static function get_statuses(): array {
        return [
            'sent'     => 'ارسال به لب',
            'received' => 'دریافت از لب',
            'placed'   => 'نصب شده',
            'redo'     => 'نیاز به اصلاح',
        ];
    }
}
