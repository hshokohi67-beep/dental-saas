<?php
defined('ABSPATH') || exit;

class Dental_Prescription_Manager {

    // ═══════════════ دیتابیس داروها ═══════════════════════════════
    public static function get_drugs(bool $active_only = true): array {
        global $wpdb;
        $where = $active_only ? 'WHERE is_active=1' : '';
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dental_drugs {$where} ORDER BY name ASC", ARRAY_A);
    }

    public static function search_drugs(string $q): array {
        global $wpdb;
        $like = '%' . $wpdb->esc_like($q) . '%';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_drugs WHERE is_active=1 AND (name LIKE %s OR english_name LIKE %s) ORDER BY name ASC LIMIT 20", $like, $like
        ), ARRAY_A);
    }

    public static function save_drug(?int $id, array $data): int {
        global $wpdb;
        // رفع باگ: قبلاً پیش‌فرض is_active روی «غیرفعال» بود مگر صریحاً
        // ست می‌شد — چون نه seed نه فرم «افزودن داروی جدید» اصلاً چک‌باکس
        // is_active نداشتن، هر دارویی (چه seed چه دستی) همیشه غیرفعال
        // ثبت می‌شد. الان پیش‌فرض «فعال»ه، مگر صریحاً '0' فرستاده بشه.
        $row = [
            'name'              => sanitize_text_field($data['name']),
            'english_name'      => sanitize_text_field($data['english_name'] ?? ''),
            'category'          => in_array($data['category']??'', ['antibiotic','analgesic','other']) ? $data['category'] : 'other',
            'default_dose_note' => sanitize_text_field($data['default_dose_note'] ?? ''),
            'is_active'         => array_key_exists('is_active', $data) ? (!empty($data['is_active']) ? 1 : 0) : 1,
        ];
        if ($id) { $wpdb->update($wpdb->prefix.'dental_drugs', $row, ['id'=>$id]); return $id; }
        $row['created_at'] = current_time('mysql');
        $wpdb->insert($wpdb->prefix.'dental_drugs', $row);
        return (int)$wpdb->insert_id;
    }

    public static function delete_drug(int $id): void {
        global $wpdb;
        // حذف واقعی نه — فقط غیرفعال، چون نسخه‌های قدیمی ممکنه بهش وصل باشن
        $wpdb->update($wpdb->prefix.'dental_drugs', ['is_active'=>0], ['id'=>$id]);
    }

    // ─── فعال‌سازی دوباره — قبلاً اصلاً چنین راهی وجود نداشت، فقط
    // می‌شد غیرفعال کرد و برگشتی نداشت ────────────────────────────
    public static function activate_drug(int $id): void {
        global $wpdb;
        $wpdb->update($wpdb->prefix.'dental_drugs', ['is_active'=>1], ['id'=>$id]);
    }

    public static function seed_default_drugs(): void {
        global $wpdb;
        // ─── رفع باگ مهم: قبلاً فقط به یه پرچم (option) تکیه می‌شد که
        // اگه بار اول (قبل از ساخته‌شدن جدول) اجرا می‌شد، insert ها بی‌صدا
        // شکست می‌خورد ولی پرچم «انجام شد» ست می‌شد — نتیجه: جدول برای
        // همیشه خالی می‌موند. الان مستقیم تعداد واقعی ردیف‌های جدول رو
        // چک می‌کنیم، نه یه پرچم که ممکنه دروغ بگه.
        $table = $wpdb->prefix . 'dental_drugs';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) return; // جدول هنوز نیست، صبر کن
        $existing_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM `{$table}`");
        if ($existing_count > 0) return; // از قبل دارو داریم، دوباره seed نکن

        $defaults = [
            ['name'=>'آموکسی‌سیلین ۵۰۰', 'english_name'=>'Amoxicillin 500mg', 'category'=>'antibiotic', 'default_dose_note'=>'۵۰۰ میلی‌گرم هر ۸ ساعت، ۷ تا ۱۰ روز'],
            ['name'=>'مترونیدازول ۲۵۰', 'english_name'=>'Metronidazole 250mg', 'category'=>'antibiotic', 'default_dose_note'=>'معمولاً همراه آموکسی‌سیلین — پرهیز از الکل حین مصرف'],
            ['name'=>'آموکسی‌کلاو (کو-آموکسی‌کلاو)', 'english_name'=>'Co-Amoxiclav (Amoxicillin/Clavulanate)', 'category'=>'antibiotic', 'default_dose_note'=>'ترکیب قوی‌تر، برای عفونت مقاوم'],
            ['name'=>'آزیترومایسین', 'english_name'=>'Azithromycin', 'category'=>'antibiotic', 'default_dose_note'=>'یک عدد روزانه، ۳ روز — جایگزین حساسیت پنی‌سیلین'],
            ['name'=>'کلیندامایسین', 'english_name'=>'Clindamycin', 'category'=>'antibiotic', 'default_dose_note'=>'وقتی لثه هم درگیره؛ مقاومت باکتریایی کمتر'],
            ['name'=>'سفیکسیم', 'english_name'=>'Cefixime', 'category'=>'antibiotic', 'default_dose_note'=>'۲۵۰ هر ۴ ساعت یا ۵۰۰ هر ۸ ساعت — بعد از جراحی'],
            ['name'=>'داکسی‌سایکلین', 'english_name'=>'Doxycycline', 'category'=>'antibiotic', 'default_dose_note'=>'جایگزین حساسیت به پنی‌سیلین'],
            ['name'=>'نوافن', 'english_name'=>'Novafen (Ibuprofen/Caffeine/Hyoscine)', 'category'=>'analgesic', 'default_dose_note'=>'ایبوپروفن+کافئین+هیوسین — هر ۶ تا ۸ ساعت'],
            ['name'=>'ژلوفن (ایبوپروفن)', 'english_name'=>'Ibuprofen', 'category'=>'analgesic', 'default_dose_note'=>'هر ۶ تا ۸ ساعت، حداکثر ۳ بار در روز'],
            ['name'=>'ناپروکسن', 'english_name'=>'Naproxen', 'category'=>'analgesic', 'default_dose_note'=>'ضدالتهاب با اثر طولانی‌تر'],
            ['name'=>'استامینوفن کدئین', 'english_name'=>'Acetaminophen Codeine', 'category'=>'analgesic', 'default_dose_note'=>'برای درد شدیدتر'],
            ['name'=>'مفنامیک اسید', 'english_name'=>'Mefenamic Acid', 'category'=>'analgesic', 'default_dose_note'=>'مخصوصاً بعد از جراحی'],
        ];
        foreach ($defaults as $d) { self::save_drug(null, $d); }
    }

    // ─── رفع یه‌باره‌ی داروهایی که به‌خاطر باگ قبلی (پیش‌فرض غیرفعال)
    // اشتباهاً is_active=0 ثبت شده بودن — خودشون رو خودکار فعال می‌کنه ──
    public static function fix_inactive_seed_bug(): void {
        if (get_option('dental_drugs_activation_fixed')) return;
        global $wpdb;
        $table = $wpdb->prefix . 'dental_drugs';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table) {
            $wpdb->query("UPDATE `{$table}` SET is_active=1 WHERE is_active=0");
        }
        update_option('dental_drugs_activation_fixed', 1);
    }

    // ─── رفع قطعی: ۱۲ داروی پیش‌فرض قبل از اضافه‌شدن ستون english_name
    // ثبت شده بودن — چون seed_default_drugs() فقط وقتی جدول خالیه اجرا
    // می‌شه (تا دوباره‌کاری نکنه)، این ردیف‌های موجود هیچ‌وقت مقدار
    // انگلیسی نمی‌گرفتن. این تابع مستقیم، بر اساس اسم فارسی، آپدیتشون
    // می‌کنه — فقط وقتی english_name فعلاً خالیه (کاری که دستی خودتون
    // زده باشید رو خراب نمی‌کنه).
    public static function fix_missing_english_names(): void {
        if (get_option('dental_drugs_english_fixed_v1')) return;
        global $wpdb;
        $table = $wpdb->prefix . 'dental_drugs';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) return;

        $map = [
            'آموکسی‌سیلین ۵۰۰'               => 'Amoxicillin 500mg',
            'مترونیدازول ۲۵۰'                => 'Metronidazole 250mg',
            'آموکسی‌کلاو (کو-آموکسی‌کلاو)'    => 'Co-Amoxiclav (Amoxicillin/Clavulanate)',
            'آزیترومایسین'                    => 'Azithromycin',
            'کلیندامایسین'                    => 'Clindamycin',
            'سفیکسیم'                         => 'Cefixime',
            'داکسی‌سایکلین'                   => 'Doxycycline',
            'نوافن'                           => 'Novafen (Ibuprofen/Caffeine/Hyoscine)',
            'ژلوفن (ایبوپروفن)'               => 'Ibuprofen',
            'ناپروکسن'                        => 'Naproxen',
            'استامینوفن کدئین'                => 'Acetaminophen Codeine',
            'مفنامیک اسید'                    => 'Mefenamic Acid',
        ];
        foreach ($map as $fa_name => $en_name) {
            $wpdb->query($wpdb->prepare(
                "UPDATE `{$table}` SET english_name=%s WHERE name=%s AND (english_name IS NULL OR english_name='')",
                $en_name, $fa_name
            ));
        }
        update_option('dental_drugs_english_fixed_v1', 1);
    }

    // ═══════════════ نسخه‌ها ═══════════════════════════════════════
    public static function create_prescription(array $data, array $items): int {
        global $wpdb;
        $wpdb->insert($wpdb->prefix.'dental_prescriptions', [
            'patient_id'      => (int)$data['patient_id'],
            'doctor_id'       => (int)($data['doctor_id'] ?? get_current_user_id()),
            'treatment_id'    => !empty($data['treatment_id']) ? (int)$data['treatment_id'] : null,
            'prescribed_date' => $data['prescribed_date'] ?? current_time('Y-m-d'),
            'notes'           => sanitize_textarea_field($data['notes'] ?? ''),
            'created_at'      => current_time('mysql'),
        ]);
        $prescription_id = (int)$wpdb->insert_id;

        foreach ($items as $item) {
            $drug_id = (int)($item['drug_id'] ?? 0);
            $drug_row = $drug_id ? $wpdb->get_row($wpdb->prepare("SELECT name, english_name FROM {$wpdb->prefix}dental_drugs WHERE id=%d", $drug_id), ARRAY_A) : null;
            $drug_name = $drug_row['name'] ?? sanitize_text_field($item['drug_name'] ?? '');
            $english_name = $drug_row['english_name'] ?? sanitize_text_field($item['english_name'] ?? '');
            if (!$drug_name) continue;
            $wpdb->insert($wpdb->prefix.'dental_prescription_items', [
                'prescription_id' => $prescription_id,
                'drug_id'         => $drug_id ?: null,
                'drug_name_snapshot' => $drug_name,
                'english_name_snapshot' => $english_name,
                'quantity'        => (float)($item['quantity'] ?? 1),
                'quantity_unit'   => sanitize_text_field($item['quantity_unit'] ?? 'عدد'),
                'frequency'       => sanitize_text_field($item['frequency'] ?? ''),
                'duration_days'   => (int)($item['duration_days'] ?? 1),
                'timing_note'     => sanitize_text_field($item['timing_note'] ?? ''),
                'extra_note'      => sanitize_text_field($item['extra_note'] ?? ''),
            ]);
        }
        return $prescription_id;
    }

    public static function get_patient_prescriptions(int $patient_id): array {
        global $wpdb;
        $prescriptions = $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, u.display_name as doctor_name FROM {$wpdb->prefix}dental_prescriptions p
             LEFT JOIN {$wpdb->users} u ON p.doctor_id=u.ID
             WHERE p.patient_id=%d ORDER BY p.prescribed_date DESC, p.id DESC", $patient_id
        ), ARRAY_A);
        foreach ($prescriptions as &$p) { $p['items'] = self::get_items((int)$p['id']); }
        return $prescriptions;
    }

    public static function get_items(int $prescription_id): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_prescription_items WHERE prescription_id=%d ORDER BY id ASC", $prescription_id
        ), ARRAY_A);
    }

    public static function get_prescription(int $id): ?array {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT p.*, u.display_name as doctor_name, pt.post_title as patient_name
             FROM {$wpdb->prefix}dental_prescriptions p
             LEFT JOIN {$wpdb->users} u ON p.doctor_id=u.ID
             LEFT JOIN {$wpdb->posts} pt ON p.patient_id=pt.ID
             WHERE p.id=%d", $id
        ), ARRAY_A);
        if ($row) $row['items'] = self::get_items($id);
        return $row ?: null;
    }

    // ─── فرمت عدد بدون اعشار اضافه — چون ستون DECIMAL(6,2) همیشه با ۲
    // رقم اعشار برمی‌گرده (مثلاً «۱۰.۰۰»)، این تابع تبدیلش می‌کنه به
    // «۱۰» (اگه عدد صحیحه) یا «۱.۵» (اگه واقعاً کسریه) ────────────────
    public static function format_qty($val): string {
        return rtrim(rtrim(sprintf('%.2f', (float)$val), '0'), '.');
    }

    // ─── ساخت جمله‌ی خوانای Sig از فیلدهای ساخت‌یافته ────────────────
    public static function format_sig(array $item): string {
        $parts = [
            $item['drug_name_snapshot'],
            '—',
            self::format_qty($item['quantity']) . ' ' . $item['quantity_unit'] . '،',
            $item['frequency'] . '،',
            'به مدت ' . $item['duration_days'] . ' روز',
        ];
        if (!empty($item['timing_note'])) $parts[] = '، ' . $item['timing_note'];
        if (!empty($item['extra_note'])) $parts[] = '— ' . $item['extra_note'];
        return implode(' ', $parts);
    }

    public static function get_frequency_options(): array {
        return ['هر ۶ ساعت (۴ بار در روز)','هر ۸ ساعت (۳ بار در روز)','هر ۱۲ ساعت (۲ بار در روز)','روزی یک‌بار','در صورت نیاز (PRN)'];
    }
    public static function get_timing_options(): array {
        return ['قبل از غذا','بعد از غذا','همراه غذا','فرقی ندارد'];
    }
    public static function get_unit_options(): array {
        return ['عدد','قطره','میلی‌لیتر','کپسول'];
    }
}
