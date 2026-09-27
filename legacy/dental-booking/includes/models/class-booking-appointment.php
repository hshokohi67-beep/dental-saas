<?php
defined('ABSPATH') || exit;

class Dental_Booking_Appointment {

    // ─── دریافت اسلات‌های خالی ─────────────────────────────
    public static function get_available_slots(int $doctor_id, string $jalali_date, int $service_id = 0): array {
        global $wpdb;

        // تبدیل به میلادی
        $gregorian = Dental_Jalali::to_gregorian($jalali_date);
        $dow       = (int)date('w', strtotime($gregorian)); // 0=Sun...6=Sat

        // شیفت‌های پزشک برای این روز
        $shifts = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_shifts
             WHERE doctor_id=%d AND day_of_week=%d AND is_active=1",
            $doctor_id, $dow
        ), ARRAY_A);

        if (empty($shifts)) return [];

        // ─── هماهنگی با برنامه ماهانه شیفت‌بندی (اگه استفاده می‌شه) ──
        // نکته مهم: این چک فقط وقتی فعال می‌شه که مدیر واقعاً برای این
        // پزشک/این ماه برنامه ماهانه پر کرده باشه — وگرنه (مثلاً مطب
        // تک‌پزشک که اصلاً از این بخش استفاده نمی‌کنه) هیچ تأثیری نداره
        // و نوبت‌دهی مثل قبل کار می‌کنه.
        $shift_m_ok = true; $shift_e_ok = true;
        if (class_exists('Dental_Shift_Scheduler')) {
            [$rota_map, ] = Dental_Shift_Scheduler::get_month_rota_flat('doctor',
                date('Y-m-01', strtotime($gregorian)), date('Y-m-t', strtotime($gregorian)));
            if (!empty($rota_map[$doctor_id])) {
                // برای این پزشک این ماه برنامه ثبت شده — پس واقعاً چک کن
                $today_shift = $rota_map[$doctor_id][$gregorian] ?? '';
                $shift_m_ok = in_array($today_shift, ['m','d']);
                $shift_e_ok = in_array($today_shift, ['e','d']);
                if (!$shift_m_ok && !$shift_e_ok) return []; // این روز اصلاً کار نمی‌کنه
            }
        }

        // چک تعطیل/مسدود
        $blocked = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dental_blocked_dates
             WHERE (doctor_id=%d OR doctor_id IS NULL) AND blocked_date=%s AND is_active=1",
            $doctor_id, $gregorian
        ));
        if ($blocked > 0) return [];

        // ─── اگه نوع خدمت مشخص شده، مدت‌زمان و ظرفیت روزانه اون رو
        // به‌جای مقادیر پیش‌فرض شیفت استفاده می‌کنیم ────────────────
        $svc_duration = null;
        $svc_daily_cap = null;
        if ($service_id) {
            $svc = $wpdb->get_row($wpdb->prepare(
                "SELECT duration, daily_capacity FROM {$wpdb->prefix}dental_services WHERE id=%d", $service_id
            ), ARRAY_A);
            if ($svc) {
                $svc_duration  = (int)$svc['duration'];
                $svc_daily_cap = $svc['daily_capacity'] !== null ? (int)$svc['daily_capacity'] : null;
            }
        }

        // اگه سقف روزانه این خدمت برای این پزشک پر شده، اصلاً اسلاتی نشون نده
        if ($service_id && $svc_daily_cap !== null) {
            $today_count_this_service = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}dental_appointments
                 WHERE doctor_id=%d AND appt_date=%s AND service_id=%d AND status NOT IN ('cancelled','rejected')",
                $doctor_id, $gregorian, $service_id
            ));
            if ($today_count_this_service >= $svc_daily_cap) return [];
        }

        // نوبت‌های رزرو شده
        $booked = $wpdb->get_results($wpdb->prepare(
            "SELECT start_time, end_time, status FROM {$wpdb->prefix}dental_appointments
             WHERE doctor_id=%d AND appt_date=%s AND status NOT IN ('cancelled','rejected')",
            $doctor_id, $gregorian
        ), ARRAY_A);

        $booked_times = [];
        foreach ($booked as $b) {
            $booked_times[] = $b['start_time'];
        }

        $slots = [];
        foreach ($shifts as $shift) {
            // اگه برنامه ماهانه این پزشک/ماه پر شده، شیفت‌هایی که با
            // برنامه‌ی امروزش نمی‌خونن رو رد کن (مثلاً امروز فقط صبح
            // کار می‌کنه، پس شیفت عصر این پزشک اصلاً نوبت نشون نده)
            $is_morning_shift = (int)date('H', strtotime($shift['start_time'])) < 13;
            if ($is_morning_shift && !$shift_m_ok) continue;
            if (!$is_morning_shift && !$shift_e_ok) continue;

            // ─── فیلتر خدمات مجاز این شیفت خاص — طبق درخواست کاربر:
            // یه پزشک ممکنه شنبه فقط جراحی، یکشنبه فقط ترمیم کار کنه.
            // خالی‌بودن dental_shift_services برای این شیفت یعنی «همه
            // خدمات مجازن» (سازگاری با شیفت‌های قدیمی/بدون محدودیت) ──
            if ($service_id) {
                $shift_allowed_services = $wpdb->get_col($wpdb->prepare(
                    "SELECT service_id FROM {$wpdb->prefix}dental_shift_services WHERE shift_id=%d", $shift['id']
                ));
                if (!empty($shift_allowed_services) && !in_array($service_id, array_map('intval', $shift_allowed_services))) {
                    continue; // این شیفت این خدمت رو پشتیبانی نمی‌کنه
                }
            }

            // مدت‌زمان اسلات: اگه خدمت مشخص شده، مدت خودِ خدمت؛ وگرنه مدت پیش‌فرض شیفت
            $duration = $svc_duration ?: (int)$shift['slot_duration'];
            $max      = (int)$shift['max_patients'];
            $current  = strtotime($gregorian . ' ' . $shift['start_time']);
            $end      = strtotime($gregorian . ' ' . $shift['end_time']);

            while ($current + ($duration * 60) <= $end) {
                $time_str = date('H:i:s', $current);
                $count_booked = count(array_filter($booked_times, fn($t) => $t === $time_str));
                $available = $count_booked < $max;

                $slots[] = [
                    'time'      => date('H:i', $current),
                    'time_full' => $time_str,
                    'end_time'  => date('H:i', $current + $duration * 60),
                    'available' => $available,
                    'shift_id'  => $shift['id'],
                    'duration'  => $duration,
                ];
                $current += $duration * 60;
            }
        }
        return $slots;
    }

    // ─── ایجاد نوبت ─────────────────────────────────────────
    public static function create(
        int    $patient_id,
        int    $doctor_id,
        string $jalali_date,
        string $time,
        int    $shift_id,
        string $service_type = '',
        string $notes = '',
        int    $created_by = 0,
        bool   $auto_confirm = false
    ): array {
        global $wpdb;

        $gregorian = Dental_Jalali::to_gregorian($jalali_date);
        if (!$gregorian) return ['success' => false, 'message' => 'تاریخ نامعتبر'];

        // چک تداخل
        $conflict = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dental_appointments
             WHERE doctor_id=%d AND appt_date=%s AND start_time=%s
             AND status NOT IN ('cancelled','rejected')",
            $doctor_id, $gregorian, $time . ':00'
        ));
        if ($conflict > 0) return ['success' => false, 'message' => 'این ساعت رزرو شده است'];

        // مدت زمان از شیفت
        $shift = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_shifts WHERE id=%d", $shift_id
        ), ARRAY_A);
        $duration = $shift ? (int)$shift['slot_duration'] : 30;
        $end_time = date('H:i:s', strtotime($time . ':00') + $duration * 60);

        $status = $auto_confirm ? 'confirmed' : 'pending';

        $r = $wpdb->insert($wpdb->prefix . 'dental_appointments', [
            'patient_id'       => $patient_id,
            'doctor_id'        => $doctor_id,
            'shift_id'         => $shift_id,
            'appt_date'        => $gregorian,
            'appt_date_jalali' => $jalali_date,
            'start_time'       => $time . ':00',
            'end_time'         => $end_time,
            'duration'         => $duration,
            'service_type'     => $service_type,
            'notes'            => $notes,
            'status'           => $status,
            'confirmed_at'     => $auto_confirm ? current_time('mysql') : null,
            'confirmed_by'     => $auto_confirm ? ($created_by ?: get_current_user_id()) : null,
            'created_by'       => $created_by ?: get_current_user_id(),
            'created_at'       => current_time('mysql'),
            'updated_at'       => current_time('mysql'),
        ], ['%d','%d','%d','%s','%s','%s','%s','%d','%s','%s','%s','%s','%d','%s','%d','%s','%s']);

        if (!$r) return ['success' => false, 'message' => 'خطا در ثبت نوبت'];

        $appt_id = (int)$wpdb->insert_id;

        // SMS
        Dental_Booking_SMS::send_booking_confirm($appt_id);

        return [
            'success'  => true,
            'appt_id'  => $appt_id,
            'status'   => $status,
            'message'  => $status === 'confirmed' ? 'نوبت تأیید شد' : 'نوبت ثبت شد و در انتظار تأیید است',
        ];
    }

    // ─── قفل‌کردن یه اسلات توسط پذیرش — بدون بیمار واقعی، فقط برای
    // اینکه نوبت‌دهی آنلاین این ساعت رو نشون نده. از همون منطق تشخیص
    // تداخل create() استفاده می‌شه (status='blocked' که NOT IN
    // cancelled/rejected هست، پس خودکار اسلات رو اشغال‌شده می‌کنه) ────
    public static function lock_slot(int $doctor_id, string $jalali_date, string $time, int $shift_id, string $reason = ''): array {
        global $wpdb;
        $gregorian = Dental_Jalali::to_gregorian($jalali_date);
        if (!$gregorian) return ['success' => false, 'message' => 'تاریخ نامعتبر'];

        $conflict = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dental_appointments
             WHERE doctor_id=%d AND appt_date=%s AND start_time=%s
             AND status NOT IN ('cancelled','rejected')",
            $doctor_id, $gregorian, $time . ':00'
        ));
        if ($conflict > 0) return ['success' => false, 'message' => 'این ساعت از قبل اشغال است'];

        $shift = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}dental_shifts WHERE id=%d", $shift_id), ARRAY_A);
        $duration = $shift ? (int)$shift['slot_duration'] : 30;
        $end_time = date('H:i:s', strtotime($time . ':00') + $duration * 60);

        $r = $wpdb->insert($wpdb->prefix . 'dental_appointments', [
            'patient_id'       => 0,
            'doctor_id'        => $doctor_id,
            'shift_id'         => $shift_id,
            'appt_date'        => $gregorian,
            'appt_date_jalali' => $jalali_date,
            'start_time'       => $time . ':00',
            'end_time'         => $end_time,
            'duration'         => $duration,
            'service_type'     => 'قفل‌شده (پذیرش)',
            'notes'            => $reason ?: 'قفل‌شده توسط پذیرش — قابل رزرو آنلاین نیست',
            'status'           => 'blocked',
            'created_by'       => get_current_user_id(),
            'created_at'       => current_time('mysql'),
            'updated_at'       => current_time('mysql'),
        ]);
        if (!$r) return ['success' => false, 'message' => 'خطا در قفل‌کردن'];
        return ['success' => true, 'appt_id' => (int)$wpdb->insert_id];
    }

    public static function unlock_slot(int $appt_id): bool {
        global $wpdb;
        return (bool)$wpdb->delete($wpdb->prefix.'dental_appointments', ['id'=>$appt_id, 'status'=>'blocked']);
    }

    // ─── تغییر وضعیت ────────────────────────────────────────
    public static function update_status(int $appt_id, string $status, string $reason = ''): bool {
        global $wpdb;

        $data = ['status' => $status, 'updated_at' => current_time('mysql')];

        if ($status === 'confirmed') {
            $data['confirmed_at'] = current_time('mysql');
            $data['confirmed_by'] = get_current_user_id();
        } elseif ($status === 'cancelled') {
            $data['cancelled_at'] = current_time('mysql');
            $data['cancel_reason'] = $reason;
        }

        $r = $wpdb->update($wpdb->prefix . 'dental_appointments', $data, ['id' => $appt_id],
            array_fill(0, count($data), '%s'), ['%d']);

        if ($r !== false && in_array($status, ['confirmed', 'cancelled', 'rejected'])) {
            Dental_Booking_SMS::send_status_update($appt_id, $status);
        }

        return $r !== false;
    }

    // ─── دریافت نوبت‌های بیمار ──────────────────────────────
    public static function get_patient_appointments(int $patient_id, string $filter = 'upcoming'): array {
        global $wpdb;

        $where = $filter === 'past'
            ? "AND a.appt_date < CURDATE()"
            : ($filter === 'all' ? '' : "AND (a.appt_date > CURDATE() OR (a.appt_date = CURDATE() AND a.start_time >= CURTIME()))");

        return $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, u.display_name as doctor_name, s.title as service_title
             FROM {$wpdb->prefix}dental_appointments a
             LEFT JOIN {$wpdb->users} u ON a.doctor_id = u.ID
             LEFT JOIN {$wpdb->prefix}dental_services s ON a.service_type = s.id
             WHERE a.patient_id = %d AND a.status != 'cancelled'
             $where
             ORDER BY a.appt_date ASC, a.start_time ASC
             LIMIT 20",
            $patient_id
        ), ARRAY_A);
    }

    // ─── دریافت نوبت‌های روزانه ─────────────────────────────
    public static function get_day_appointments(string $date, int $doctor_id = 0): array {
        global $wpdb;
        $where = $doctor_id ? $wpdb->prepare("AND a.doctor_id=%d", $doctor_id) : '';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, u.display_name as doctor_name,
                    p.post_title as patient_name, pm.meta_value as patient_mobile,
                    s.title as service_title, s.color as service_color
             FROM {$wpdb->prefix}dental_appointments a
             LEFT JOIN {$wpdb->users} u ON a.doctor_id = u.ID
             LEFT JOIN {$wpdb->posts} p ON a.patient_id = p.ID
             LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_patient_mobile'
             LEFT JOIN {$wpdb->prefix}dental_services s ON a.service_type = s.id
             WHERE a.appt_date = %s AND a.status != 'cancelled' $where
             ORDER BY a.start_time ASC",
            $date
        ), ARRAY_A);
    }
}
