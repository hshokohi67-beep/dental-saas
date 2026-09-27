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

    // ─── تبدیل کلید خام (مثل 38_permanent_consult_endo) به برچسب خوانا فارسی ──
    // نکته: کد ()code می‌تواند خودش زیرخط داشته باشد (مثل consult_endo)،
    // پس با explode با محدودیت ۳ تکه پارس می‌شود تا کد درست جدا شود.
    public static function format_item_key(string $item_key): string {
        $labels = [
            'composite'=>'ترمیم کامپوزیت','amalgam'=>'ترمیم آمالگام','rct'=>'عصب‌کشی (RCT)',
            'pulpotomy'=>'پالپوتومی','pulpectomy'=>'پالپکتومی','buildup'=>'بیلدآپ',
            'crown'=>'روکش','veneer'=>'لامینیت / ونیر','inlay_onlay'=>'انله / آنله',
            'extraction'=>'کشیدن ساده','surgical_ext'=>'کشیدن جراحی','implant'=>'ایمپلنت',
            'apicoectomy'=>'آپیکوستومی','bridge_abutment'=>'بریج (پایه)','retainer_fix'=>'ریتینر فیکس',
            'xray_pa'=>'عکس PA','cbct'=>'CBCT تک دندان',
            'consult_perio'=>'مشاوره پریو','consult_endo'=>'مشاوره اندو','consult_surgeon'=>'مشاوره جراح',
            'consult_prosth'=>'مشاوره پروتز','consult_resto'=>'مشاوره متخصص ترمیم','consult_peds'=>'مشاوره اطفال',
            'bwx'=>'عکس بایت‌وینگ (BWX)','scaling_half'=>'جرم‌گیری نیم‌فک','root_planing'=>'روت پلنینگ / کورتاژ',
            'flap_surgery'=>'فلاپ جراحی','consult_perio_h'=>'مشاوره پریو نیم‌فک',
            'panoramic'=>'عکس پانورامیک','scaling_full'=>'جرم‌گیری کل دهان','brushing'=>'بروساژ',
            'fissure_seal'=>'فیشورسیلانت','fluoride'=>'فلوراید','bleaching'=>'بلیچینگ',
            'ortho'=>'ارتودنسی','consult_ortho'=>'مشاوره ارتودنسی','study_model'=>'مدل مطالعه',
        ];
        $q_names = [1=>'بالا راست',2=>'بالا چپ',3=>'پایین چپ',4=>'پایین راست'];

        // ─── کلیدهای «نیم‌فک»/«کل دهان» فرمت متفاوتی دارند ────────
        if (strpos($item_key, 'half_') === 0) {
            $half_names = ['half_q1'=>'بالا راست','half_q2'=>'بالا چپ','half_q3'=>'پایین چپ','half_q4'=>'پایین راست'];
            return 'خدمات نیم‌فک ' . ($half_names[$item_key] ?? '');
        }
        if ($item_key === 'fullarch') return 'خدمات کل دهان';

        $parts = explode('_', $item_key, 3);
        if (count($parts) < 3 || !is_numeric($parts[0])) return $item_key;
        [$tooth_number, , $code] = $parts;
        $n = (int)$tooth_number % 10;
        $q = (int)((int)$tooth_number / 10);
        $label = $labels[$code] ?? $code;
        return $label . ' — دندان ' . $n . ' (' . ($q_names[$q] ?? '') . ')';
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

    // ─── ترجمه کلید خام (مثل «38_permanent_consult_endo») به برچسب فارسی خوانا ──
    // این کلید از ترکیب شماره‌دندان + نوع‌دندان + کد‌درمان ساخته می‌شود.
    public static function describe_item_key(string $item_key): string {
        $labels = [
            'composite'=>'ترمیم کامپوزیت','amalgam'=>'ترمیم آمالگام','rct'=>'عصب‌کشی (RCT)',
            'pulpotomy'=>'پالپوتومی','pulpectomy'=>'پالپکتومی','buildup'=>'بیلدآپ',
            'crown'=>'روکش','veneer'=>'لامینیت / ونیر','inlay_onlay'=>'انله / آنله',
            'extraction'=>'کشیدن ساده','surgical_ext'=>'کشیدن جراحی','implant'=>'ایمپلنت',
            'apicoectomy'=>'آپیکوستومی','bridge_abutment'=>'بریج (پایه)','retainer_fix'=>'ریتینر فیکس',
            'xray_pa'=>'عکس PA','cbct'=>'CBCT تک دندان',
            'consult_perio'=>'مشاوره پریو','consult_endo'=>'مشاوره اندو','consult_surgeon'=>'مشاوره جراح',
            'consult_prosth'=>'مشاوره پروتز','consult_resto'=>'مشاوره متخصص ترمیم','consult_peds'=>'مشاوره اطفال',
            'bwx'=>'عکس بایت‌وینگ (BWX)','scaling_half'=>'جرم‌گیری نیم‌فک','root_planing'=>'روت پلنینگ / کورتاژ',
            'flap_surgery'=>'فلاپ جراحی','consult_perio_h'=>'مشاوره پریو نیم‌فک',
            'panoramic'=>'عکس پانورامیک','scaling_full'=>'جرم‌گیری کل دهان','brushing'=>'بروساژ',
            'fissure_seal'=>'فیشورسیلانت','fluoride'=>'فلوراید','bleaching'=>'بلیچینگ',
            'ortho'=>'ارتودنسی','consult_ortho'=>'مشاوره ارتودنسی','study_model'=>'مدل مطالعه',
        ];
        $q_names = [1=>'بالا راست',2=>'بالا چپ',3=>'پایین چپ',4=>'پایین راست'];

        // فرمت: {شماره‌دندان}_{permanent|primary}_{کد}
        if (preg_match('/^(\d+)_(permanent|primary)_(.+)$/', $item_key, $m)) {
            $tooth_number = (int)$m[1];
            $code = $m[3];
            $q = (int)($tooth_number/10); $n = $tooth_number%10;
            return ($labels[$code] ?? $code) . ' — دندان ' . $n . ' (' . ($q_names[$q] ?? '') . ')';
        }
        // فرمت‌های نیم‌فک/کل‌دهان یا هر چیز ناشناخته دیگر
        return $labels[$item_key] ?? $item_key;
    }
}
