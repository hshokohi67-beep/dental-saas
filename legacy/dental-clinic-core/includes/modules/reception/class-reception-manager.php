<?php
defined('ABSPATH') || exit;

class Dental_Reception_Manager {

    // ─── پذیرش بیمار (با نوبت قبلی یا بدون نوبت) ────────────────
    // ─── ساخت بیمار جدید (یا برگرداندن پرونده‌ی موجود اگه موبایل تکراری
    // بود) + حساب کاربری وردپرس — این منطق قبلاً فقط توی پذیرش بود،
    // الان مشترک شد تا نوبت‌دهی هم بتونه ازش استفاده کنه بدون دوباره‌کاری ──
    public static function create_or_get_patient(string $name, string $mobile, string $national_id = '', int $insurance_id = 0, string $insurance_policy = ''): array {
        if (!$name || !$mobile) {
            return ['success' => false, 'message' => 'نام و موبایل هر دو اجباری هستن.'];
        }
        // اگر این موبایل از قبل پرونده‌ای داشت، دوباره نساز
        $existing = get_posts(['post_type'=>'dental_patient','posts_per_page'=>1,'post_status'=>'publish',
            'meta_key'=>'_patient_mobile','meta_value'=>$mobile,'fields'=>'ids']);
        if (!empty($existing)) {
            return ['success' => true, 'patient_id' => $existing[0], 'is_new' => false];
        }

        $patient_id = wp_insert_post([
            'post_type'   => 'dental_patient',
            'post_title'  => $name,
            'post_status' => 'publish',
        ]);
        if (!$patient_id || is_wp_error($patient_id)) {
            return ['success' => false, 'message' => 'خطا در ساخت پرونده.'];
        }
        update_post_meta($patient_id, '_patient_mobile', $mobile);

        $national_id_clean = preg_replace('/[^0-9]/', '', $national_id);
        if ($national_id_clean) {
            update_post_meta($patient_id, '_patient_national_id', substr($national_id_clean, 0, 10));
        }

        if ($insurance_id && class_exists('Dental_Insurance_Manager')) {
            Dental_Insurance_Manager::save_patient_insurance($patient_id, $insurance_id, sanitize_text_field($insurance_policy));
        }

        // ساخت حساب کاربری وردپرس واقعی برای بیمار (برای ورود بعدی با OTP)
        $existing_user = get_users(['meta_key'=>'_dental_mobile','meta_value'=>$mobile,'number'=>1]);
        if (empty($existing_user)) {
            $user_id = wp_insert_user([
                'user_login' => 'patient_' . $mobile,
                'user_pass'  => wp_generate_password(20),
                'display_name' => $name,
                'role'       => 'dental_patient',
            ]);
            if ($user_id && !is_wp_error($user_id)) {
                update_user_meta($user_id, '_dental_mobile', $mobile);
                update_user_meta($user_id, '_dental_patient_post_id', $patient_id);
                update_post_meta($patient_id, '_patient_wp_user_id', $user_id);
            }
        } else {
            update_post_meta($patient_id, '_patient_wp_user_id', $existing_user[0]->ID);
            update_user_meta($existing_user[0]->ID, '_dental_patient_post_id', $patient_id);
        }

        return ['success' => true, 'patient_id' => $patient_id, 'is_new' => true];
    }

    public static function check_in(int $patient_id, int $doctor_id, string $reason = '', int $appointment_id = 0): int {
        global $wpdb;

        $today_j = Dental_Jalali::today();
        $today_g = current_time('Y-m-d');

        $wpdb->insert($wpdb->prefix . 'dental_reception_queue', [
            'patient_id'        => $patient_id,
            'doctor_id'         => $doctor_id,
            'appointment_id'    => $appointment_id ?: null,
            'reason'            => sanitize_text_field($reason),
            'status'            => 'waiting',
            'queue_date'        => $today_g,
            'queue_date_jalali' => $today_j,
            'checked_in_at'     => current_time('mysql'),
            'created_by'        => get_current_user_id(),
        ], ['%d','%d','%d','%s','%s','%s','%s','%s','%d']);

        return (int)$wpdb->insert_id;
    }

    // ─── تغییر وضعیت (شروع معاینه / پایان / لغو) ────────────────
    public static function update_status(int $id, string $status): bool {
        global $wpdb;
        $data = ['status' => $status];
        $fmt  = ['%s'];

        if ($status === 'in_progress') {
            $data['started_at'] = current_time('mysql');
            $fmt[] = '%s';
        } elseif ($status === 'done') {
            $data['finished_at'] = current_time('mysql');
            $fmt[] = '%s';
        }

        $result = (bool)$wpdb->update($wpdb->prefix . 'dental_reception_queue', $data, ['id' => $id], $fmt, ['%d']);

        // ثبت در «کارکرد پزشکان» تا مدیریت ببیند این پزشک معاینه‌ای را تمام کرده
        if ($result && $status === 'done' && class_exists('Dental_Doctor_Activity')) {
            $row = $wpdb->get_row($wpdb->prepare(
                "SELECT doctor_id, patient_id FROM {$wpdb->prefix}dental_reception_queue WHERE id=%d", $id
            ), ARRAY_A);
            if ($row) {
                Dental_Doctor_Activity::log((int)$row['doctor_id'], (int)$row['patient_id'], 'exam_done', 'معاینه/ویزیت تکمیل شد');
            }
        }

        return $result;
    }

    // ─── صف کل امروز (برای صفحه پذیرش) ───────────────────────────
    public static function get_today_queue(): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT q.*, p.post_title as patient_name, pm.meta_value as patient_mobile,
                    u.display_name as doctor_name
             FROM {$wpdb->prefix}dental_reception_queue q
             LEFT JOIN {$wpdb->posts} p ON q.patient_id = p.ID
             LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_patient_mobile'
             LEFT JOIN {$wpdb->users} u ON q.doctor_id = u.ID
             WHERE q.queue_date = %s AND q.status != 'cancelled'
             ORDER BY q.checked_in_at ASC",
            current_time('Y-m-d')
        ), ARRAY_A);
    }

    // ─── صف فقط یک پزشک خاص (برای ویجت داشبورد) ──────────────────
    public static function get_doctor_queue(int $doctor_id): array {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT q.*, p.post_title as patient_name
             FROM {$wpdb->prefix}dental_reception_queue q
             LEFT JOIN {$wpdb->posts} p ON q.patient_id = p.ID
             WHERE q.queue_date = %s AND q.doctor_id = %d
             AND q.status IN ('waiting','in_progress')
             ORDER BY q.checked_in_at ASC",
            current_time('Y-m-d'), $doctor_id
        ), ARRAY_A);

        // اطلاعات پزشکی/هشدارها برای هر بیمار
        foreach ($rows as &$r) {
            $r['allergy']     = get_post_meta($r['patient_id'], '_drug_allergies', true);
            $r['diseases']    = json_decode(get_post_meta($r['patient_id'], '_systemic_diseases', true) ?: '[]', true) ?: [];
            $r['appt_time']   = null;
            if (!empty($r['appointment_id']) && class_exists('Dental_Booking_Appointment')) {
                global $wpdb;
                $r['appt_time'] = $wpdb->get_var($wpdb->prepare(
                    "SELECT start_time FROM {$wpdb->prefix}dental_appointments WHERE id=%d", $r['appointment_id']
                ));
            }
        }
        return $rows;
    }

    // ─── اقدام‌شده‌های امروز همین پزشک (برای بخش «اقدام شده» در داشبورد) ──
    public static function get_doctor_done_today(int $doctor_id): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT q.*, p.post_title as patient_name
             FROM {$wpdb->prefix}dental_reception_queue q
             LEFT JOIN {$wpdb->posts} p ON q.patient_id = p.ID
             WHERE q.queue_date = %s AND q.doctor_id = %d AND q.status = 'done'
             ORDER BY q.finished_at DESC LIMIT 15",
            current_time('Y-m-d'), $doctor_id
        ), ARRAY_A);
    }

    // ─── نسخه عمومی: لیست انتظار برای هر تاریخ دلخواه (نه فقط امروز) ──
    // برای صفحه پزشک که باید بتواند تاریخچه هر روز را ببیند.
    public static function get_queue_for_date(string $date, int $doctor_id = 0): array {
        global $wpdb;
        $where = $doctor_id ? $wpdb->prepare("AND q.doctor_id=%d", $doctor_id) : '';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT q.*, p.post_title as patient_name
             FROM {$wpdb->prefix}dental_reception_queue q
             LEFT JOIN {$wpdb->posts} p ON q.patient_id = p.ID
             WHERE q.queue_date = %s AND q.status IN ('waiting','in_progress') $where
             ORDER BY q.checked_in_at ASC",
            $date
        ), ARRAY_A);
    }

    // ─── نسخه عمومی: اقدام‌شده‌های هر تاریخ دلخواه ─────────────────
    public static function get_done_for_date(string $date, int $doctor_id = 0): array {
        global $wpdb;
        $where = $doctor_id ? $wpdb->prepare("AND q.doctor_id=%d", $doctor_id) : '';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT q.*, p.post_title as patient_name
             FROM {$wpdb->prefix}dental_reception_queue q
             LEFT JOIN {$wpdb->posts} p ON q.patient_id = p.ID
             WHERE q.queue_date = %s AND q.status = 'done' $where
             ORDER BY q.finished_at DESC",
            $date
        ), ARRAY_A);
    }

    // ─── تعداد پذیرش‌هایی که خودِ همین کاربر امروز ثبت کرده (برای منشی) ──
    public static function get_my_checkins_today_count(int $user_id): int {
        global $wpdb;
        return (int)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dental_reception_queue
             WHERE queue_date=%s AND created_by=%d", current_time('Y-m-d'), $user_id
        ));
    }

    // ─── بررسی نوبت امروز بیمار (برای پیشنهاد خودکار در فرم پذیرش) ──
    public static function find_today_appointment(int $patient_id): ?array {
        if (!class_exists('Dental_Booking_Appointment')) return null;
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT id, doctor_id, start_time FROM {$wpdb->prefix}dental_appointments
             WHERE patient_id=%d AND appt_date=%s AND status IN ('confirmed','pending')
             ORDER BY start_time ASC LIMIT 1",
            $patient_id, current_time('Y-m-d')
        ), ARRAY_A);
        return $row ?: null;
    }
}
