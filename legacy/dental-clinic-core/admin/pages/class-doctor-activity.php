<?php
defined('ABSPATH') || exit;

/**
 * ثبت و نمایش کارکرد پزشکان — برای مدیریت:
 * هر بار پزشکی معاینه‌ای را تمام می‌کند یا درمانی را تیک می‌زند،
 * یک رکورد اینجا ثبت می‌شود تا مدیریت و بقیه پزشکان ببینند.
 */
class Dental_Doctor_Activity {

    public static function log(int $doctor_id, int $patient_id, string $type, string $description = ''): void {
        global $wpdb;
        $wpdb->insert($wpdb->prefix.'dental_doctor_activity', [
            'doctor_id'     => $doctor_id,
            'patient_id'    => $patient_id,
            'activity_type' => $type,
            'description'   => sanitize_text_field($description),
            'created_at'    => current_time('mysql'),
        ], ['%d','%d','%s','%s','%s']);
    }

    public static function get_recent(int $limit = 50, int $doctor_id = 0, string $date_from = '', string $date_to = ''): array {
        global $wpdb;
        $where = 'WHERE 1=1';
        $params = [];
        if ($doctor_id) { $where .= ' AND a.doctor_id=%d'; $params[] = $doctor_id; }
        if ($date_from) { $where .= ' AND a.created_at >= %s'; $params[] = $date_from.' 00:00:00'; }
        if ($date_to)   { $where .= ' AND a.created_at <= %s'; $params[] = $date_to.' 23:59:59'; }

        $sql = "SELECT a.*, u.display_name as doctor_name, p.post_title as patient_name
                FROM {$wpdb->prefix}dental_doctor_activity a
                LEFT JOIN {$wpdb->users} u ON a.doctor_id=u.ID
                LEFT JOIN {$wpdb->posts} p ON a.patient_id=p.ID
                $where ORDER BY a.created_at DESC LIMIT %d";
        $params[] = $limit;
        return $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
    }

    // خلاصه امروز به تفکیک پزشک (برای ویجت داشبورد)
    public static function get_today_summary(): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT a.doctor_id, u.display_name as doctor_name,
                SUM(a.activity_type='exam_done') as exams,
                SUM(a.activity_type='treatment_done') as treatments
             FROM {$wpdb->prefix}dental_doctor_activity a
             LEFT JOIN {$wpdb->users} u ON a.doctor_id=u.ID
             WHERE DATE(a.created_at)=%s
             GROUP BY a.doctor_id ORDER BY (exams+treatments) DESC",
            current_time('Y-m-d')
        ), ARRAY_A);
    }
}
