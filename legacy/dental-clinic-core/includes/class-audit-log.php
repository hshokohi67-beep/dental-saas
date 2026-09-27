<?php
defined('ABSPATH') || exit;

/**
 * لاگ فعالیت مرکزی — کی، کِی، چه کاری انجام داده.
 * از هرجای پلاگین که یه اقدام حساس (مالی، تغییر داده مهم) انجام
 * می‌شه، Dental_Audit_Log::log(...) صدا زده می‌شه.
 */
class Dental_Audit_Log {

    public static function log(string $action_type, string $description, array $args = []): void {
        global $wpdb;
        $cu = wp_get_current_user();

        $wpdb->insert($wpdb->prefix . 'dental_audit_log', [
            'user_id'     => $cu->ID ?: null,
            'user_name'   => $cu->display_name ?: 'سیستم',
            'action_type' => $action_type,
            'entity_type' => $args['entity_type'] ?? null,
            'entity_id'   => $args['entity_id'] ?? null,
            'description' => $description,
            'amount'      => $args['amount'] ?? null,
            'ip_address'  => self::get_client_ip(),
            'created_at'  => current_time('mysql'),
        ], ['%d','%s','%s','%s','%d','%s','%f','%s','%s']);
    }

    private static function get_client_ip(): string {
        foreach (['HTTP_X_FORWARDED_FOR','HTTP_CLIENT_IP','REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = explode(',', $_SERVER[$key])[0];
                return trim(sanitize_text_field($ip));
            }
        }
        return '';
    }

    // ─── لیست با فیلتر — برای صفحه‌ی مدیریت لاگ ─────────────────────
    public static function get_logs(array $filters = [], int $page = 1, int $per_page = 50): array {
        global $wpdb;
        $where = ['1=1']; $params = [];

        if (!empty($filters['user_id'])) { $where[] = 'user_id=%d'; $params[] = (int)$filters['user_id']; }
        if (!empty($filters['action_type'])) { $where[] = 'action_type=%s'; $params[] = $filters['action_type']; }
        if (!empty($filters['from_date'])) { $where[] = 'created_at >= %s'; $params[] = $filters['from_date'] . ' 00:00:00'; }
        if (!empty($filters['to_date'])) { $where[] = 'created_at <= %s'; $params[] = $filters['to_date'] . ' 23:59:59'; }
        if (!empty($filters['search'])) { $where[] = 'description LIKE %s'; $params[] = '%' . $wpdb->esc_like($filters['search']) . '%'; }

        $where_sql = implode(' AND ', $where);
        $count_sql = "SELECT COUNT(*) FROM {$wpdb->prefix}dental_audit_log WHERE {$where_sql}";
        $total = (int)($params ? $wpdb->get_var($wpdb->prepare($count_sql, $params)) : $wpdb->get_var($count_sql));

        $offset = ($page - 1) * $per_page;
        $sql = "SELECT * FROM {$wpdb->prefix}dental_audit_log WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $logs = $wpdb->get_results($wpdb->prepare($sql, [...$params, $per_page, $offset]), ARRAY_A);

        return ['logs' => $logs, 'total' => $total, 'pages' => (int)ceil($total / $per_page)];
    }

    public static function get_action_labels(): array {
        return [
            'ledger_confirm'       => '✅ تأیید دفتر روزانه',
            'ledger_discount'      => '🏷️ اعمال تخفیف',
            'installment_create'   => '📋 ساخت پلن قسطی',
            'installment_pay'      => '💳 پرداخت قسط',
            'wallet_credit'        => '➕ شارژ کیف پول',
            'wallet_deduct'        => '➖ برداشت کیف پول',
            'pos_charge'           => '💳 ارسال به کارتخوان',
            'staff_added'          => '👤 افزودن پرسنل',
            'role_changed'         => '🔑 تغییر نقش کاربر',
            'settings_changed'     => '⚙️ تغییر تنظیمات',
            'patient_data_changed' => '📝 تغییر اطلاعات بیمار',
            'reception_checkin'    => '🚪 پذیرش بیمار',
        ];
    }
}
