<?php
defined('ABSPATH') || exit;

class Dental_Insurance_Manager {

    // ═══════════════ شرکت‌های بیمه ═══════════════════════════════
    public static function get_companies(bool $active_only = false): array {
        global $wpdb;
        $where = $active_only ? 'WHERE is_active=1' : '';
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dental_insurance_companies {$where} ORDER BY name ASC", ARRAY_A);
    }

    public static function save_company(?int $id, array $data): int {
        global $wpdb;
        $row = [
            'name'                     => sanitize_text_field($data['name']),
            'type'                     => in_array($data['type']??'', ['supplementary','base']) ? $data['type'] : 'supplementary',
            'default_franchise_percent'=> (float)($data['default_franchise_percent'] ?? 20),
            'contact_info'             => sanitize_textarea_field($data['contact_info'] ?? ''),
            'is_active'                => !empty($data['is_active']) ? 1 : 0,
        ];
        if ($id) { $wpdb->update($wpdb->prefix.'dental_insurance_companies', $row, ['id'=>$id]); return $id; }
        $row['created_at'] = current_time('mysql');
        $wpdb->insert($wpdb->prefix.'dental_insurance_companies', $row);
        return (int)$wpdb->insert_id;
    }

    // ═══════════════ تعرفه هر خدمت نزد هر بیمه ═══════════════════
    public static function get_tariffs(int $insurance_id): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT t.*, c.name as catalog_name FROM {$wpdb->prefix}dental_insurance_tariffs t
             LEFT JOIN {$wpdb->prefix}dental_service_catalog c ON t.catalog_id=c.id
             WHERE t.insurance_id=%d ORDER BY c.name ASC", $insurance_id
        ), ARRAY_A);
    }

    public static function save_tariff(int $insurance_id, int $catalog_id, float $tariff, ?float $franchise, string $docs = ''): void {
        global $wpdb;
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}dental_insurance_tariffs WHERE insurance_id=%d AND catalog_id=%d", $insurance_id, $catalog_id
        ));
        $row = [
            'approved_tariff'   => (int)$tariff,
            'franchise_percent' => $franchise,
            'requires_docs'     => sanitize_text_field($docs),
            'updated_at'        => current_time('mysql'),
        ];
        if ($existing) {
            $wpdb->update($wpdb->prefix.'dental_insurance_tariffs', $row, ['id'=>$existing]);
        } else {
            $row['insurance_id'] = $insurance_id;
            $row['catalog_id'] = $catalog_id;
            $wpdb->insert($wpdb->prefix.'dental_insurance_tariffs', $row);
        }
    }

    public static function delete_tariff(int $id): void {
        global $wpdb;
        $wpdb->delete($wpdb->prefix.'dental_insurance_tariffs', ['id'=>$id]);
    }

    // ─── تعرفه یه خدمت خاص نزد یه بیمه‌ی خاص (برای محاسبه لحظه‌ای) ──
    public static function get_tariff_for(int $insurance_id, int $catalog_id): ?array {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT t.*, i.default_franchise_percent FROM {$wpdb->prefix}dental_insurance_tariffs t
             JOIN {$wpdb->prefix}dental_insurance_companies i ON t.insurance_id=i.id
             WHERE t.insurance_id=%d AND t.catalog_id=%d", $insurance_id, $catalog_id
        ), ARRAY_A);
        return $row ?: null;
    }

    // ═══════════════ محاسبه‌ی خودکار سهم بیمار/بیمه ═══════════════
    // فرمول (طبق بررسی واقعی نحوه‌ی کار بیمه‌های تکمیلی ایران):
    // مبنا = MIN(مبلغ فاکتور شده، تعرفه مصوب سندیکا)
    // سهم بیمه = مبنا × (۱ - فرانشیز٪)
    // سهم بیمار = مبلغ فاکتور - سهم بیمه
    public static function calculate(int $insurance_id, int $catalog_id, float $charged_price): array {
        $tariff_row = self::get_tariff_for($insurance_id, $catalog_id);

        if (!$tariff_row) {
            // این خدمت نزد این بیمه تعرفه‌ای نداره — یعنی کلاً پوشش نمی‌ده
            return [
                'has_tariff' => false,
                'base' => 0, 'insurance_share' => 0, 'patient_share' => $charged_price,
                'requires_docs' => [],
                'message' => 'این خدمت نزد این بیمه تعرفه‌ای ثبت نشده — احتمالاً پوشش داده نمی‌شه.',
            ];
        }

        $approved = (float)$tariff_row['approved_tariff'];
        $franchise = $tariff_row['franchise_percent'] !== null ? (float)$tariff_row['franchise_percent'] : (float)$tariff_row['default_franchise_percent'];

        $base = min($charged_price, $approved);
        $insurance_share = round($base * (1 - $franchise / 100));
        $patient_share = round($charged_price - $insurance_share);

        return [
            'has_tariff'       => true,
            'approved_tariff'  => $approved,
            'franchise_percent'=> $franchise,
            'base'             => $base,
            'insurance_share'  => $insurance_share,
            'patient_share'    => max(0, $patient_share),
            'requires_docs'    => array_filter(explode(',', $tariff_row['requires_docs'] ?? '')),
            'capped'           => $charged_price > $approved, // یعنی مبلغ فاکتور از تعرفه بیشتر بوده
        ];
    }

    // ─── لیبل فارسی مدارک لازم ────────────────────────────────────
    public static function get_doc_labels(): array {
        return [
            'xray_before'  => '📷 رادیوگرافی قبل از درمان',
            'xray_after'   => '📷 رادیوگرافی بعد از درمان',
            'initial_exam' => '📋 گواهی معاینه اولیه',
            'pos_receipt'  => '🧾 رسید کارتخوان',
        ];
    }

    // ─── تشخیص خودکار مدارک لازم بر اساس نوع خدمت (مثل تشخیص خودکار
    // رضایت‌نامه‌ی کاتالوگ) — اگه تعرفه‌ی دستی مشخص نکرده باشه، حدس بزن ──
    public static function detect_required_docs(string $service_name): array {
        $docs = [];
        $name = $service_name;
        if (mb_stripos($name, 'اندو') !== false || mb_stripos($name, 'عصب') !== false || mb_stripos($name, 'روت کانال') !== false) {
            $docs[] = 'xray_before'; $docs[] = 'xray_after';
        }
        if (mb_stripos($name, 'ایمپلنت') !== false || mb_stripos($name, 'implant') !== false) {
            $docs[] = 'xray_before'; $docs[] = 'xray_after';
        }
        if (mb_stripos($name, 'روکش') !== false || mb_stripos($name, 'جراحی') !== false) {
            $docs[] = 'xray_before'; $docs[] = 'xray_after';
        }
        if (mb_stripos($name, 'ارتودنس') !== false) {
            $docs[] = 'initial_exam';
        }
        return array_unique($docs);
    }

    // ─── وضعیت مدارک یه رکورد ثبت‌شده — برای چک‌لیست پرونده ────────
    public static function set_docs_status(int $treatment_id, string $status): void {
        global $wpdb;
        $wpdb->update($wpdb->prefix.'dental_catalog_treatments', ['insurance_docs_status'=>$status], ['id'=>$treatment_id]);
    }

    // ═══════════════ مدارک بیمه — آپلود، وضعیت، پرینت ═══════════════
    public static function get_doc_checklist(int $treatment_id): array {
        global $wpdb;
        $treatment = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_catalog_treatments WHERE id=%d", $treatment_id
        ), ARRAY_A);
        if (!$treatment) return [];

        // مدارک لازم رو از تعرفه (اگه دستی ثبت شده) یا تشخیص خودکار بگیر
        $required = [];
        if (!empty($treatment['insurance_id'])) {
            $tariff = self::get_tariff_for((int)$treatment['insurance_id'], (int)$treatment['catalog_id']);
            if ($tariff) $required = array_filter(explode(',', $tariff['requires_docs'] ?? ''));
        }
        if (empty($required)) $required = self::detect_required_docs($treatment['service_name'] ?? '');

        $existing = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_insurance_docs WHERE treatment_id=%d", $treatment_id
        ), ARRAY_A);
        $existing_map = [];
        foreach ($existing as $e) { $existing_map[$e['doc_type']] = $e; }

        $labels = self::get_doc_labels();
        $checklist = [];
        foreach ($required as $doc_type) {
            $checklist[] = [
                'doc_type'  => $doc_type,
                'label'     => $labels[$doc_type] ?? $doc_type,
                'uploaded'  => isset($existing_map[$doc_type]) && !empty($existing_map[$doc_type]['file_path']),
                'file_path' => $existing_map[$doc_type]['file_path'] ?? null,
                'doc_id'    => $existing_map[$doc_type]['id'] ?? null,
            ];
        }
        return $checklist;
    }

    // ─── آپلود یه مدرک خاص — از همون پوشه‌ی امن سیستم Imaging استفاده
    // می‌کنه، سیستم ذخیره‌سازی جدا نمی‌سازه ──────────────────────────
    public static function upload_doc(int $treatment_id, string $doc_type, array $file): array {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'message' => 'فایل نامعتبر است.'];
        }
        if (!class_exists('Dental_Imaging_Manager')) {
            return ['success' => false, 'message' => 'سیستم ذخیره‌سازی در دسترس نیست.'];
        }
        // رفع باگ احتمالی: mime_content_type() به اکستنشن PHP fileinfo
        // وابسته‌ست که روی بعضی نصب‌های لوکال (مثل Local by Flywheel با
        // بعضی تنظیمات) ممکنه فعال نباشه — که باعث می‌شد false برگرده و
        // هر فایلی (حتی درست) رد بشه. الان به پسوند فایل هم فال‌بک می‌زنیم.
        $mime = @mime_content_type($file['tmp_name']);
        if (!$mime) {
            $ext_check = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $ext_to_mime = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp','pdf'=>'application/pdf'];
            $mime = $ext_to_mime[$ext_check] ?? false;
        }
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        if (!$mime || !in_array($mime, $allowed)) {
            return ['success' => false, 'message' => 'فقط JPG/PNG/PDF مجازه.'];
        }

        $dir = Dental_Imaging_Manager::get_storage_dir();
        $ext = $mime === 'application/pdf' ? 'pdf' : (explode('/', $mime)[1] ?? 'jpg');
        $unique_name = 'insdoc_' . $treatment_id . '_' . $doc_type . '_' . wp_generate_password(10, false) . '.' . $ext;
        $dest = $dir . '/' . $unique_name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return ['success' => false, 'message' => 'خطا در ذخیره‌سازی.'];
        }

        global $wpdb;
        $existing_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}dental_insurance_docs WHERE treatment_id=%d AND doc_type=%s", $treatment_id, $doc_type
        ));
        $row = ['file_path'=>$unique_name, 'uploaded_by'=>get_current_user_id(), 'uploaded_at'=>current_time('mysql')];
        if ($existing_id) {
            $wpdb->update($wpdb->prefix.'dental_insurance_docs', $row, ['id'=>$existing_id]);
        } else {
            $row['treatment_id'] = $treatment_id;
            $row['doc_type'] = $doc_type;
            $wpdb->insert($wpdb->prefix.'dental_insurance_docs', $row);
        }

        // ─── چک کن اگه همه‌ی مدارک لازم کامل شدن، وضعیت رو خودکار
        // «complete» کن ────────────────────────────────────────────
        $checklist = self::get_doc_checklist($treatment_id);
        $all_done = !empty($checklist) && !in_array(false, array_column($checklist, 'uploaded'));
        self::set_docs_status($treatment_id, $all_done ? 'complete' : 'pending');

        return ['success' => true, 'all_complete' => $all_done];
    }

    public static function get_patient_insurance(int $patient_id): array {
        return [
            'insurance_id'   => (int)get_post_meta($patient_id, '_patient_insurance_id', true),
            'policy_number'  => get_post_meta($patient_id, '_patient_insurance_policy', true),
        ];
    }

    public static function save_patient_insurance(int $patient_id, int $insurance_id, string $policy_number): void {
        update_post_meta($patient_id, '_patient_insurance_id', $insurance_id);
        update_post_meta($patient_id, '_patient_insurance_policy', sanitize_text_field($policy_number));
    }

    // ═══════════════ تسویه دوره‌ای با بیمه (کلینیک‌های طرف‌قرارداد) ══
    // روال واقعی: کلینیک سهم بیمار رو نقد می‌گیره، سهم بیمه رو نه فوری —
    // بلکه دوره‌ای (معمولاً ماهانه) یه لیست تجمیعی می‌فرسته و بیمه بعد
    // از بررسی، یکجا واریز می‌کنه. این بخش دقیقاً همون کار رو ساده می‌کنه.
    public static function get_unsettled_treatments(int $insurance_id, string $from, string $to): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT t.*, p.post_title as patient_name, u.display_name as doctor_name
             FROM {$wpdb->prefix}dental_catalog_treatments t
             LEFT JOIN {$wpdb->posts} p ON t.patient_id=p.ID
             LEFT JOIN {$wpdb->users} u ON t.doctor_id=u.ID
             WHERE t.insurance_id=%d AND t.insurance_share > 0
               AND t.recorded_date BETWEEN %s AND %s
               AND t.id NOT IN (SELECT treatment_id FROM {$wpdb->prefix}dental_insurance_settlement_items)
             ORDER BY t.recorded_date ASC",
            $insurance_id, $from, $to
        ), ARRAY_A);
    }

    public static function create_settlement(int $insurance_id, string $from, string $to, array $treatment_ids): int {
        global $wpdb;
        if (empty($treatment_ids)) return 0;

        $placeholders = implode(',', array_fill(0, count($treatment_ids), '%d'));
        $total = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT SUM(insurance_share) FROM {$wpdb->prefix}dental_catalog_treatments WHERE id IN ({$placeholders})",
            $treatment_ids
        ));

        $wpdb->insert($wpdb->prefix.'dental_insurance_settlements', [
            'insurance_id'    => $insurance_id,
            'period_from'     => $from,
            'period_to'       => $to,
            'total_amount'    => $total,
            'treatment_count' => count($treatment_ids),
            'status'          => 'draft',
            'created_by'      => get_current_user_id(),
            'created_at'      => current_time('mysql'),
        ]);
        $settlement_id = (int)$wpdb->insert_id;

        foreach ($treatment_ids as $tid) {
            $wpdb->insert($wpdb->prefix.'dental_insurance_settlement_items', [
                'settlement_id' => $settlement_id, 'treatment_id' => (int)$tid,
            ]);
        }
        return $settlement_id;
    }

    public static function get_settlements(int $insurance_id = 0): array {
        global $wpdb;
        $where = $insurance_id ? $wpdb->prepare('WHERE s.insurance_id=%d', $insurance_id) : '';
        return $wpdb->get_results(
            "SELECT s.*, i.name as insurance_name FROM {$wpdb->prefix}dental_insurance_settlements s
             LEFT JOIN {$wpdb->prefix}dental_insurance_companies i ON s.insurance_id=i.id
             {$where} ORDER BY s.created_at DESC", ARRAY_A
        );
    }

    public static function get_settlement_details(int $settlement_id): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT t.*, p.post_title as patient_name, u.display_name as doctor_name
             FROM {$wpdb->prefix}dental_insurance_settlement_items si
             JOIN {$wpdb->prefix}dental_catalog_treatments t ON si.treatment_id=t.id
             LEFT JOIN {$wpdb->posts} p ON t.patient_id=p.ID
             LEFT JOIN {$wpdb->users} u ON t.doctor_id=u.ID
             WHERE si.settlement_id=%d ORDER BY t.recorded_date ASC", $settlement_id
        ), ARRAY_A);
    }

    public static function update_settlement_status(int $settlement_id, string $status, array $extra = []): void {
        global $wpdb;
        $row = ['status' => $status];
        $fmt = ['%s'];
        if ($status === 'submitted') { $row['submitted_at'] = current_time('mysql'); $fmt[] = '%s'; }
        if ($status === 'paid') {
            $row['paid_at'] = current_time('mysql'); $fmt[] = '%s';
            if (isset($extra['paid_amount'])) { $row['paid_amount'] = (int)$extra['paid_amount']; $fmt[] = '%d'; }
        }
        if (!empty($extra['reference_no'])) { $row['reference_no'] = sanitize_text_field($extra['reference_no']); $fmt[] = '%s'; }
        if (isset($extra['notes'])) { $row['notes'] = sanitize_textarea_field($extra['notes']); $fmt[] = '%s'; }
        $wpdb->update($wpdb->prefix.'dental_insurance_settlements', $row, ['id'=>$settlement_id], $fmt, ['%d']);
    }
}
