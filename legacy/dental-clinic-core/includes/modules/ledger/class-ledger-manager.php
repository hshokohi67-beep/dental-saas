<?php
defined('ABSPATH') || exit;

class Dental_Ledger_Manager {

    // ─── ثبت رکورد جدید ──────────────────────────────────────
    public static function create(array $data): int {
        global $wpdb;

        $jalali = sanitize_text_field($data['entry_date_jalali'] ?? Dental_Jalali::today());
        $gregorian = Dental_Jalali::to_gregorian($jalali) ?: current_time('Y-m-d');

        $wpdb->insert($wpdb->prefix . 'dental_daily_ledger', [
            'patient_id'        => (int)($data['patient_id'] ?? 0),
            'appointment_id'    => !empty($data['appointment_id']) ? (int)$data['appointment_id'] : null,
            'doctor_id'         => !empty($data['doctor_id']) ? (int)$data['doctor_id'] : null,
            'treatment_title'   => sanitize_text_field($data['treatment_title'] ?? ''),
            'amount_charged'    => (float)($data['amount_charged'] ?? 0),
            'amount_received'   => (float)($data['amount_received'] ?? 0),
            'payment_method'    => sanitize_text_field($data['payment_method'] ?? 'cash'),
            'entry_date'        => $gregorian,
            'entry_date_jalali' => $jalali,
            'notes'             => sanitize_textarea_field($data['notes'] ?? ''),
            // نکته: رکوردهایی که خودکار از کاتالوگ خدمات ساخته می‌شن، با
            // وضعیت pending ذخیره می‌شن تا واحد مالی تأییدشون کنه — رکوردهای
            // دستی (از فرم دفتر روزانه) همچنان مستقیم confirmed هستن.
            'status'            => sanitize_key($data['status'] ?? 'confirmed'),
            'source'            => sanitize_key($data['source'] ?? 'manual'),
            'catalog_treatment_id' => !empty($data['catalog_treatment_id']) ? (int)$data['catalog_treatment_id'] : null,
            'created_by'        => get_current_user_id(),
            'created_at'        => current_time('mysql'),
            'updated_at'        => current_time('mysql'),
        ], ['%d','%d','%d','%s','%f','%f','%s','%s','%s','%s','%s','%s','%d','%d','%s','%s']);

        return (int)$wpdb->insert_id;
    }

    // ─── گرفتن یک رکورد خاص (برای دسترسی به catalog_treatment_id هنگام
    // ساخت پلن قسطی از دفتر روزانه) ──────────────────────────────────
    public static function get(int $id): ?array {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_daily_ledger WHERE id=%d", $id
        ), ARRAY_A);
        return $row ?: null;
    }

    // ─── تأیید یک رکورد در انتظار (توسط مسئول مالی) — الان تخفیف و
    // روش پرداخت واقعی رو هم ثبت می‌کنه، نه فقط مبلغ دریافتی خام ────
    public static function confirm(int $id, ?float $amount_received = null, float $discount = 0, string $payment_method = ''): bool {
        global $wpdb;
        $data = ['status' => 'confirmed', 'updated_at' => current_time('mysql')];
        $fmt  = ['%s','%s'];
        if ($amount_received !== null) {
            $data['amount_received'] = $amount_received;
            $fmt[] = '%f';
        }
        if ($discount > 0) {
            $data['discount_amount'] = $discount;
            $fmt[] = '%f';
        }
        if ($payment_method) {
            $data['payment_method'] = $payment_method === 'installment' ? 'installment' : $payment_method;
            $fmt[] = '%s';
        }
        return (bool)$wpdb->update($wpdb->prefix.'dental_daily_ledger', $data, ['id'=>$id], $fmt, ['%d']);
    }

    // ─── لیست رکوردهای در انتظار تأیید مالی ─────────────────────
    public static function get_pending(): array {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT l.*, p.post_title as patient_name, u.display_name as doctor_name
             FROM {$wpdb->prefix}dental_daily_ledger l
             LEFT JOIN {$wpdb->posts} p ON l.patient_id=p.ID
             LEFT JOIN {$wpdb->users} u ON l.doctor_id=u.ID
             WHERE l.status='pending' ORDER BY l.created_at ASC", ARRAY_A
        );
    }

    // ─── ویرایش رکورد ────────────────────────────────────────
    public static function update(int $id, array $data): bool {
        global $wpdb;
        $r = $wpdb->update($wpdb->prefix . 'dental_daily_ledger', [
            'treatment_title'  => sanitize_text_field($data['treatment_title'] ?? ''),
            'amount_charged'   => (float)($data['amount_charged'] ?? 0),
            'amount_received'  => (float)($data['amount_received'] ?? 0),
            'payment_method'   => sanitize_text_field($data['payment_method'] ?? 'cash'),
            'notes'            => sanitize_textarea_field($data['notes'] ?? ''),
            'updated_at'       => current_time('mysql'),
        ], ['id' => $id], ['%s','%f','%f','%s','%s','%s'], ['%d']);
        return $r !== false;
    }

    // ─── حذف رکورد ───────────────────────────────────────────
    public static function delete(int $id): bool {
        global $wpdb;
        return (bool)$wpdb->delete($wpdb->prefix . 'dental_daily_ledger', ['id' => $id], ['%d']);
    }

    // ─── دریافت رکوردهای یک روز ──────────────────────────────
    public static function get_by_date(string $gregorian_date): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT l.*, p.post_title as patient_name, u.display_name as doctor_name
             FROM {$wpdb->prefix}dental_daily_ledger l
             LEFT JOIN {$wpdb->posts} p ON l.patient_id = p.ID
             LEFT JOIN {$wpdb->users} u ON l.doctor_id = u.ID
             WHERE l.entry_date = %s
             ORDER BY l.created_at ASC",
            $gregorian_date
        ), ARRAY_A);
    }

    // ─── همه رکوردهای یه بازه (نه فقط یه روز) — برای خروجی اکسل ─────
    public static function get_by_range(string $from, string $to): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT l.*, p.post_title as patient_name, u.display_name as doctor_name
             FROM {$wpdb->prefix}dental_daily_ledger l
             LEFT JOIN {$wpdb->posts} p ON l.patient_id = p.ID
             LEFT JOIN {$wpdb->users} u ON l.doctor_id = u.ID
             WHERE l.entry_date BETWEEN %s AND %s
             ORDER BY l.entry_date ASC, l.created_at ASC",
            $from, $to
        ), ARRAY_A);
    }

    // ─── نگاشت appointment_id هایی که قبلاً ثبت شدن ─────────
    public static function get_logged_appointment_ids(string $gregorian_date): array {
        global $wpdb;
        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT appointment_id FROM {$wpdb->prefix}dental_daily_ledger
             WHERE entry_date = %s AND appointment_id IS NOT NULL",
            $gregorian_date
        ));
        return array_map('intval', $ids);
    }

    // ─── آمار یک بازه زمانی برای گزارش ───────────────────────
    public static function get_report_stats(string $from, string $to): array {
        global $wpdb;

        $overall = $wpdb->get_row($wpdb->prepare(
            "SELECT COUNT(*) as total_entries,
                    SUM(amount_charged) as total_charged,
                    SUM(amount_received) as total_received
             FROM {$wpdb->prefix}dental_daily_ledger
             WHERE entry_date BETWEEN %s AND %s",
            $from, $to
        ), ARRAY_A);

        $daily = $wpdb->get_results($wpdb->prepare(
            "SELECT entry_date, entry_date_jalali, SUM(amount_received) as total
             FROM {$wpdb->prefix}dental_daily_ledger
             WHERE entry_date BETWEEN %s AND %s
             GROUP BY entry_date ORDER BY entry_date ASC",
            $from, $to
        ), ARRAY_A);

        $by_treatment = $wpdb->get_results($wpdb->prepare(
            "SELECT treatment_title, COUNT(*) as cnt, SUM(amount_received) as total
             FROM {$wpdb->prefix}dental_daily_ledger
             WHERE entry_date BETWEEN %s AND %s
             GROUP BY treatment_title ORDER BY total DESC LIMIT 10",
            $from, $to
        ), ARRAY_A);

        $by_doctor = $wpdb->get_results($wpdb->prepare(
            "SELECT l.doctor_id, u.display_name as doctor_name, COUNT(*) as cnt, SUM(l.amount_received) as total
             FROM {$wpdb->prefix}dental_daily_ledger l
             LEFT JOIN {$wpdb->users} u ON l.doctor_id = u.ID
             WHERE l.entry_date BETWEEN %s AND %s
             GROUP BY l.doctor_id ORDER BY total DESC",
            $from, $to
        ), ARRAY_A);

        return [
            'overall'      => $overall,
            'daily'        => $daily,
            'by_treatment' => $by_treatment,
            'by_doctor'    => $by_doctor,
        ];
    }
}
