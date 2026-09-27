<?php
defined('ABSPATH') || exit;

class Dental_Booking_SMS {

    // ─── تأیید نوبت ────────────────────────────────────────
    public static function send_booking_confirm(int $appt_id): void {
        global $wpdb;
        $appt = $wpdb->get_row($wpdb->prepare(
            "SELECT a.*, p.post_title as patient_name, pm.meta_value as mobile, u.display_name as doctor_name
             FROM {$wpdb->prefix}dental_appointments a
             JOIN {$wpdb->posts} p ON a.patient_id=p.ID
             JOIN {$wpdb->postmeta} pm ON p.ID=pm.post_id AND pm.meta_key='_patient_mobile'
             JOIN {$wpdb->users} u ON a.doctor_id=u.ID
             WHERE a.id=%d", $appt_id
        ), ARRAY_A);

        if (!$appt || empty($appt['mobile'])) return;

        $clinic = get_option('dental_clinic_name', 'کلینیک');
        $auto   = (int)get_option('dental_booking_auto_confirm', 0);

        if ($auto) {
            $tpl = get_option('dental_booking_sms_confirm',
                "کلینیک {clinic_name}\nنوبت شما تأیید شد.\nتاریخ: {date}\nساعت: {time}\nپزشک: {doctor}\nبا تشکر");
        } else {
            $tpl = get_option('dental_booking_sms_pending',
                "کلینیک {clinic_name}\nنوبت شما با موفقیت ثبت شد.\nتاریخ: {date} — ساعت: {time}\nپزشک: {doctor}\nنوبت شما پس از تأیید نهایی خواهد شد.");
        }

        $msg = str_replace(
            ['{clinic_name}','{patient_name}','{date}','{time}','{doctor}'],
            [$clinic, $appt['patient_name'], $appt['appt_date_jalali'],
             substr($appt['start_time'],0,5), $appt['doctor_name']],
            $tpl
        );

        self::queue($appt['mobile'], $msg, 'booking_confirm', (int)$appt['patient_id']);

        // SMS به مدیر
        $manager = get_option('dental_financial_manager_mobile', '');
        if ($manager) {
            $mgr_tpl = get_option('dental_booking_sms_manager',
                "نوبت جدید ثبت شد.\nبیمار: {patient_name}\nتاریخ: {date} — ساعت: {time}\nپزشک: {doctor}");
            $mgr_msg = str_replace(
                ['{patient_name}','{date}','{time}','{doctor}'],
                [$appt['patient_name'], $appt['appt_date_jalali'], substr($appt['start_time'],0,5), $appt['doctor_name']],
                $mgr_tpl
            );
            self::queue($manager, $mgr_msg, 'booking_notify_admin', 0);
        }
    }

    // ─── آپدیت وضعیت ────────────────────────────────────────
    public static function send_status_update(int $appt_id, string $status): void {
        global $wpdb;
        $appt = $wpdb->get_row($wpdb->prepare(
            "SELECT a.*, p.post_title as patient_name, pm.meta_value as mobile, u.display_name as doctor_name
             FROM {$wpdb->prefix}dental_appointments a
             JOIN {$wpdb->posts} p ON a.patient_id=p.ID
             JOIN {$wpdb->postmeta} pm ON p.ID=pm.post_id AND pm.meta_key='_patient_mobile'
             JOIN {$wpdb->users} u ON a.doctor_id=u.ID
             WHERE a.id=%d", $appt_id
        ), ARRAY_A);

        if (!$appt || empty($appt['mobile'])) return;

        $clinic = get_option('dental_clinic_name', 'کلینیک');

        $tpls = [
            'confirmed' => get_option('dental_booking_sms_confirmed',
                "کلینیک {clinic_name}\nنوبت شما در تاریخ {date} ساعت {time} تأیید شد.\nپزشک: {doctor}\nمنتظر حضور شما هستیم."),
            'cancelled' => get_option('dental_booking_sms_cancelled',
                "کلینیک {clinic_name}\nنوبت شما در تاریخ {date} ساعت {time} لغو شد.\nبرای رزرو مجدد با ما تماس بگیرید."),
            'rejected'  => get_option('dental_booking_sms_rejected',
                "کلینیک {clinic_name}\nمتأسفانه نوبت درخواستی شما در تاریخ {date} تأیید نشد.\nلطفاً با کلینیک تماس بگیرید."),
        ];

        if (!isset($tpls[$status])) return;

        $msg = str_replace(
            ['{clinic_name}','{patient_name}','{date}','{time}','{doctor}'],
            [$clinic, $appt['patient_name'], $appt['appt_date_jalali'],
             substr($appt['start_time'],0,5), $appt['doctor_name']],
            $tpls[$status]
        );
        self::queue($appt['mobile'], $msg, 'booking_' . $status, (int)$appt['patient_id']);
    }

    // ─── یادآور ─────────────────────────────────────────────
    public static function send_reminders(): void {
        global $wpdb;
        $days = (int)get_option('dental_booking_reminder_days', 1);
        $date = date('Y-m-d', strtotime("+{$days} days"));

        $appts = $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, p.post_title as patient_name, pm.meta_value as mobile, u.display_name as doctor_name
             FROM {$wpdb->prefix}dental_appointments a
             JOIN {$wpdb->posts} p ON a.patient_id=p.ID
             JOIN {$wpdb->postmeta} pm ON p.ID=pm.post_id AND pm.meta_key='_patient_mobile'
             JOIN {$wpdb->users} u ON a.doctor_id=u.ID
             WHERE a.appt_date=%s AND a.status='confirmed' AND a.reminder_sent=0",
            $date
        ), ARRAY_A);

        $clinic = get_option('dental_clinic_name','کلینیک');
        $tpl = get_option('dental_booking_sms_reminder',
            "کلینیک {clinic_name}\nیادآور نوبت:\nفردا {date} ساعت {time}\nپزشک: {doctor}\nلطفاً به موقع حاضر باشید.");

        foreach ($appts as $a) {
            if (empty($a['mobile'])) continue;
            $msg = str_replace(
                ['{clinic_name}','{patient_name}','{date}','{time}','{doctor}'],
                [$clinic, $a['patient_name'], $a['appt_date_jalali'], substr($a['start_time'],0,5), $a['doctor_name']],
                $tpl
            );
            self::queue($a['mobile'], $msg, 'booking_reminder', (int)$a['patient_id']);
            $wpdb->update($wpdb->prefix.'dental_appointments',
                ['reminder_sent'=>1], ['id'=>$a['id']], ['%d'], ['%d']);
        }
    }

    // ─── صف SMS ──────────────────────────────────────────────
    public static function queue(string $mobile, string $msg, string $trigger, int $patient_id): void {
        // اگه پلاگین ۱ فعاله از صف اون استفاده کن
        if (class_exists('Dental_SMS_Cron')) {
            global $wpdb;
            $wpdb->insert($wpdb->prefix.'dental_sms_log',[
                'patient_id'   => $patient_id,
                'mobile'       => $mobile,
                'message'      => $msg,
                'trigger_type' => $trigger,
                'gateway'      => get_option('dental_sms_gateway','trez'),
                'status'       => 'queued',
                'created_at'   => current_time('mysql'),
            ],['%d','%s','%s','%s','%s','%s','%s']);
        }
    }
}
