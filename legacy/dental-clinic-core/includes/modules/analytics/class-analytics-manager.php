<?php
defined('ABSPATH') || exit;

class Dental_Analytics_Manager {

    // ─── کارت‌های خلاصه بالای صفحه (این ماه vs ماه قبل) ────────────
    public static function get_kpi_summary(): array {
        global $wpdb;
        $this_month_start = date('Y-m-01');
        $last_month_start = date('Y-m-01', strtotime('-1 month'));
        $last_month_end    = date('Y-m-t', strtotime('-1 month'));

        // ─── رفع باگ: قبلاً مستقیم `status='confirmed'` روی خودِ
        // dental_catalog_treatments چک می‌شد — ستونی که اصلاً روی این
        // جدول وجود نداره (نه توی CREATE TABLE، نه هیچ‌جا INSERT/UPDATE
        // می‌شه). نتیجه: هر کوئری با خطای دیتابیس شکست می‌خورد و
        // COALESCE همیشه ۰ برمی‌گردوند — «درآمد این ماه» همیشه صفر بود.
        // وضعیت واقعی مالی («تأیید شده»/«در انتظار») توی dental_daily_ledger
        // نگه‌داری می‌شه؛ دقیقاً همون الگویی که Dental_Service_Catalog::
        // get_doctor_summary() هم برای همین منظور استفاده می‌کنه.
        $revenue_this = (float)$wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(l.amount_charged),0)
             FROM {$wpdb->prefix}dental_catalog_treatments t
             JOIN {$wpdb->prefix}dental_daily_ledger l ON l.catalog_treatment_id=t.id
             WHERE l.status='confirmed' AND t.recorded_date>=%s",
            $this_month_start
        ));
        $revenue_last = (float)$wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(l.amount_charged),0)
             FROM {$wpdb->prefix}dental_catalog_treatments t
             JOIN {$wpdb->prefix}dental_daily_ledger l ON l.catalog_treatment_id=t.id
             WHERE l.status='confirmed' AND t.recorded_date BETWEEN %s AND %s",
            $last_month_start, $last_month_end
        ));

        $patients_this = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT patient_id) FROM {$wpdb->prefix}dental_catalog_treatments WHERE recorded_date>=%s", $this_month_start
        ));
        $patients_last = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT patient_id) FROM {$wpdb->prefix}dental_catalog_treatments WHERE recorded_date BETWEEN %s AND %s",
            $last_month_start, $last_month_end
        ));

        $visits_this = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dental_catalog_treatments WHERE recorded_date>=%s", $this_month_start
        ));
        $avg_visit_this = $visits_this > 0 ? $revenue_this / $visits_this : 0;

        // نرخ عدم‌حضور (اگه پلاگین نوبت‌دهی فعال باشه)
        $no_show_rate = null;
        if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}dental_appointments'")) {
            $total_appts = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}dental_appointments WHERE appt_date>=%s AND status IN ('done','no_show')", $this_month_start
            ));
            $no_shows = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}dental_appointments WHERE appt_date>=%s AND status='no_show'", $this_month_start
            ));
            $no_show_rate = $total_appts > 0 ? round(($no_shows / $total_appts) * 100, 1) : 0;
        }

        return [
            'revenue_this' => $revenue_this, 'revenue_change' => self::pct_change($revenue_last, $revenue_this),
            'patients_this' => $patients_this, 'patients_change' => self::pct_change($patients_last, $patients_this),
            'avg_visit' => round($avg_visit_this),
            'no_show_rate' => $no_show_rate,
        ];
    }

    private static function pct_change(float $old, float $new): ?float {
        if ($old == 0) return $new > 0 ? 100 : 0;
        return round((($new - $old) / $old) * 100, 1);
    }

    // ─── روند درآمد ماهانه (N ماه اخیر) ──────────────────────────
    public static function get_revenue_trend(int $months = 6): array {
        global $wpdb;
        $out = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $from = date('Y-m-01', strtotime("-{$i} months"));
            $to   = date('Y-m-t', strtotime("-{$i} months"));
            $sum = (float)$wpdb->get_var($wpdb->prepare(
                "SELECT COALESCE(SUM(l.amount_charged),0)
                 FROM {$wpdb->prefix}dental_catalog_treatments t
                 JOIN {$wpdb->prefix}dental_daily_ledger l ON l.catalog_treatment_id=t.id
                 WHERE l.status='confirmed' AND t.recorded_date BETWEEN %s AND %s",
                $from, $to
            ));
            $out[] = ['label' => Dental_Jalali::to_jalali($from, 'F'), 'value' => $sum];
        }
        return $out;
    }

    // ─── روند تعداد ویزیت/بیمار ماهانه ─────────────────────────
    public static function get_visits_trend(int $months = 6): array {
        global $wpdb;
        $out = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $from = date('Y-m-01', strtotime("-{$i} months"));
            $to   = date('Y-m-t', strtotime("-{$i} months"));
            $visits = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}dental_catalog_treatments WHERE recorded_date BETWEEN %s AND %s", $from, $to
            ));
            $patients = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(DISTINCT patient_id) FROM {$wpdb->prefix}dental_catalog_treatments WHERE recorded_date BETWEEN %s AND %s", $from, $to
            ));
            $out[] = ['label' => Dental_Jalali::to_jalali($from, 'F'), 'visits' => $visits, 'patients' => $patients];
        }
        return $out;
    }

    // ─── سهم درآمد هر پزشک (۳۰ روز اخیر) ─────────────────────────
    public static function get_revenue_by_doctor(int $days = 30): array {
        global $wpdb;
        $from = date('Y-m-d', strtotime("-{$days} days"));
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT u.display_name as doctor, COALESCE(SUM(l.amount_charged),0) as revenue, COUNT(*) as visits
             FROM {$wpdb->prefix}dental_catalog_treatments t
             JOIN {$wpdb->prefix}dental_daily_ledger l ON l.catalog_treatment_id=t.id
             LEFT JOIN {$wpdb->users} u ON t.doctor_id = u.ID
             WHERE l.status='confirmed' AND t.recorded_date >= %s
             GROUP BY t.doctor_id ORDER BY revenue DESC", $from
        ), ARRAY_A);
        return $rows;
    }

    // ─── پرمصرف‌ترین خدمات (۳۰ روز اخیر) ─────────────────────────
    public static function get_top_treatments(int $limit = 8, int $days = 30): array {
        global $wpdb;
        $from = date('Y-m-d', strtotime("-{$days} days"));
        return $wpdb->get_results($wpdb->prepare(
            "SELECT service_name, COUNT(*) as cnt, COALESCE(SUM(price),0) as revenue
             FROM {$wpdb->prefix}dental_catalog_treatments
             WHERE recorded_date >= %s
             GROUP BY service_name ORDER BY cnt DESC LIMIT %d", $from, $limit
        ), ARRAY_A);
    }

    // ─── بیمار جدید vs مراجعه‌کننده قدیمی (۳۰ روز اخیر) ────────────
    public static function get_new_vs_returning(int $days = 30): array {
        global $wpdb;
        $from = date('Y-m-d', strtotime("-{$days} days"));
        $patient_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT patient_id FROM {$wpdb->prefix}dental_catalog_treatments WHERE recorded_date >= %s", $from
        ));
        $new = 0; $returning = 0;
        foreach ($patient_ids as $pid) {
            $first_visit = $wpdb->get_var($wpdb->prepare(
                "SELECT MIN(recorded_date) FROM {$wpdb->prefix}dental_catalog_treatments WHERE patient_id=%d", $pid
            ));
            if ($first_visit >= $from) $new++; else $returning++;
        }
        return ['new' => $new, 'returning' => $returning];
    }

    // ─── وضعیت نوبت‌ها (اگه پلاگین نوبت‌دهی فعال باشه) ────────────
    public static function get_appointment_status_breakdown(int $days = 30): array {
        global $wpdb;
        if (!$wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}dental_appointments'")) return [];
        $from = date('Y-m-d', strtotime("-{$days} days"));
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT status, COUNT(*) as cnt FROM {$wpdb->prefix}dental_appointments WHERE appt_date >= %s GROUP BY status", $from
        ), ARRAY_A);
        return $rows;
    }
}
