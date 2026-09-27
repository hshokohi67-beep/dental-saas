<?php
defined('ABSPATH') || exit;

class Dental_Recall_Manager {

    public static function get_rules(bool $active_only = false): array {
        global $wpdb;
        $where = $active_only ? 'WHERE is_active=1' : '';
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dental_recall_rules {$where} ORDER BY id DESC", ARRAY_A);
    }

    public static function save_rule(?int $id, array $data): int {
        global $wpdb;
        $row = [
            'name'               => sanitize_text_field($data['name']),
            'trigger_catalog_id' => !empty($data['trigger_catalog_id']) ? (int)$data['trigger_catalog_id'] : null,
            'recall_months'      => max(1, (int)($data['recall_months'] ?? 6)),
            'sms_template'       => sanitize_textarea_field($data['sms_template'] ?? ''),
            'is_active'          => !empty($data['is_active']) ? 1 : 0,
        ];
        if ($id) { $wpdb->update($wpdb->prefix.'dental_recall_rules', $row, ['id'=>$id]); return $id; }
        $row['created_at'] = current_time('mysql');
        $wpdb->insert($wpdb->prefix.'dental_recall_rules', $row);
        return (int)$wpdb->insert_id;
    }

    public static function delete_rule(int $id): void {
        global $wpdb;
        $wpdb->delete($wpdb->prefix.'dental_recall_rules', ['id'=>$id]);
    }

    // ─── محاسبه بیمارهای موعدشون رسیده — برای هر قانون فعال، آخرین
    // باری که خدمت مرتبط (یا هر خدمتی) رو انجام دادن رو پیدا می‌کنه و
    // اگه از موعدش گذشته و اخیراً یادآوری نگرفتن، به لیست اضافه می‌کنه ──
    public static function scan_due_recalls(): int {
        global $wpdb;
        $rules = self::get_rules(true);
        $added = 0;

        foreach ($rules as $rule) {
            $where_service = $rule['trigger_catalog_id']
                ? $wpdb->prepare('AND catalog_id=%d', $rule['trigger_catalog_id']) : '';

            // آخرین تاریخ انجام این خدمت برای هر بیمار
            $rows = $wpdb->get_results(
                "SELECT patient_id, MAX(recorded_date) as last_date
                 FROM {$wpdb->prefix}dental_catalog_treatments
                 WHERE is_done=1 {$where_service}
                 GROUP BY patient_id", ARRAY_A
            );

            foreach ($rows as $r) {
                $due_date = date('Y-m-d', strtotime("{$r['last_date']} +{$rule['recall_months']} months"));
                if ($due_date > current_time('Y-m-d')) continue; // هنوز موعدش نرسیده

                // اگه همین بیمار/قانون از قبل توی لاگ هست (هرچه وضعیتی)، دوباره اضافه نکن
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}dental_recall_log WHERE patient_id=%d AND rule_id=%d AND due_date=%s",
                    $r['patient_id'], $rule['id'], $due_date
                ));
                if ($exists) continue;

                $wpdb->insert($wpdb->prefix.'dental_recall_log', [
                    'patient_id' => $r['patient_id'], 'rule_id' => $rule['id'],
                    'due_date' => $due_date, 'status' => 'due', 'created_at' => current_time('mysql'),
                ], ['%d','%d','%s','%s','%s']);
                $added++;
            }
        }
        return $added;
    }

    public static function get_due_list(int $limit = 100): array {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT l.*, p.post_title as patient_name, pm.meta_value as mobile, r.name as rule_name, r.sms_template
             FROM {$wpdb->prefix}dental_recall_log l
             LEFT JOIN {$wpdb->posts} p ON l.patient_id = p.ID
             LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_patient_mobile'
             LEFT JOIN {$wpdb->prefix}dental_recall_rules r ON l.rule_id = r.id
             WHERE l.status='due' ORDER BY l.due_date ASC LIMIT {$limit}", ARRAY_A
        );
    }

    public static function count_due(): int {
        global $wpdb;
        return (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dental_recall_log WHERE status='due'");
    }

    public static function send_recall_sms(int $log_id): bool {
        global $wpdb;
        $log = $wpdb->get_row($wpdb->prepare(
            "SELECT l.*, p.post_title as patient_name, pm.meta_value as mobile, r.sms_template
             FROM {$wpdb->prefix}dental_recall_log l
             LEFT JOIN {$wpdb->posts} p ON l.patient_id = p.ID
             LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_patient_mobile'
             LEFT JOIN {$wpdb->prefix}dental_recall_rules r ON l.rule_id = r.id
             WHERE l.id=%d", $log_id
        ), ARRAY_A);
        if (!$log || empty($log['mobile'])) return false;

        $clinic = get_option('dental_clinic_name', 'کلینیک');
        $tpl = $log['sms_template'] ?: "کلینیک {clinic_name}\nسلام {patient_name} عزیز، وقت چکاپ دوره‌ای شما فرارسیده. برای رزرو نوبت تماس بگیرید.";
        $msg = str_replace(['{clinic_name}','{patient_name}'], [$clinic, $log['patient_name']], $tpl);

        $wpdb->insert($wpdb->prefix.'dental_sms_log', [
            'patient_id' => $log['patient_id'], 'mobile' => $log['mobile'], 'message' => $msg,
            'trigger_type' => 'recall', 'gateway' => get_option('dental_sms_gateway','trez'),
            'status' => 'queued', 'created_at' => current_time('mysql'),
        ], ['%d','%s','%s','%s','%s','%s','%s']);

        $wpdb->update($wpdb->prefix.'dental_recall_log', ['status'=>'sent','sms_sent_at'=>current_time('mysql')], ['id'=>$log_id]);
        return true;
    }

    public static function dismiss_recall(int $log_id): void {
        global $wpdb;
        $wpdb->update($wpdb->prefix.'dental_recall_log', ['status'=>'dismissed'], ['id'=>$log_id]);
    }
}
