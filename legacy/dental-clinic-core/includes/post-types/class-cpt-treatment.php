<?php
defined('ABSPATH') || exit;

class Dental_CPT_Treatment {

    const POST_TYPE = 'dental_treatment';

    public function __construct() {
        add_action('init', [$this, 'register']);
    }

    public function register(): void {
        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name'          => 'درمان‌ها',
                'singular_name' => 'درمان',
            ],
            'public'       => false,
            'show_ui'      => false,
            'show_in_menu' => false,
            'show_in_rest' => false,
            'supports'     => ['title'],
        ]);
    }

    /**
     * ثبت یک درمان جدید برای بیمار
     */
    public static function create(
        int    $patient_id,
        int    $doctor_id,
        array  $tooth_numbers,
        string $tooth_type,
        string $service_code,
        string $service_label,
        float  $cost           = 0,
        string $status         = 'planned',
        string $notes          = ''
    ): int|false {
        $post_id = wp_insert_post([
            'post_type'   => self::POST_TYPE,
            'post_title'  => $service_label . ' — دندان ' . implode(',', $tooth_numbers),
            'post_status' => 'publish',
            'post_author' => $doctor_id,
        ]);

        if (is_wp_error($post_id)) return false;

        $today = Dental_Jalali::today();

        update_post_meta($post_id, '_treatment_patient_id',    $patient_id);
        update_post_meta($post_id, '_treatment_doctor_id',     $doctor_id);
        update_post_meta($post_id, '_tooth_numbers',           wp_json_encode($tooth_numbers));
        update_post_meta($post_id, '_tooth_type',              $tooth_type);
        update_post_meta($post_id, '_service_code',            $service_code);
        update_post_meta($post_id, '_service_label',           $service_label);
        update_post_meta($post_id, '_treatment_cost',          $cost);
        update_post_meta($post_id, '_treatment_status',        $status);
        update_post_meta($post_id, '_treatment_date_jalali',   $today);
        update_post_meta($post_id, '_treatment_notes',         $notes);
        update_post_meta($post_id, '_xray_attachment_ids',     wp_json_encode([]));

        return $post_id;
    }

    /**
     * دریافت درمان‌های یک بیمار
     */
    public static function get_by_patient(int $patient_id, string $status = ''): array {
        $meta_query = [['key' => '_treatment_patient_id', 'value' => $patient_id]];

        if ($status) {
            $meta_query[] = ['key' => '_treatment_status', 'value' => $status];
        }

        return get_posts([
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => $meta_query,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
    }

    /**
     * وضعیت‌های ممکن یک درمان
     */
    public static function get_statuses(): array {
        return [
            'planned'     => 'برنامه‌ریزی شده',
            'in_progress' => 'در حال انجام',
            'done'        => 'انجام شده',
            'cancelled'   => 'لغو شده',
        ];
    }
}
