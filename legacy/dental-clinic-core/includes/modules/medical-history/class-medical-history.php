<?php
defined('ABSPATH') || exit;

/**
 * Class Dental_Medical_History
 * مدیریت تاریخچه پزشکی، رادیوگرافی، و گالری تصاویر بیمار
 */
class Dental_Medical_History {

    /**
     * ذخیره تاریخچه پزشکی کامل
     */
    public static function save(int $patient_id, array $data): void {
        // بیماری‌های سیستمیک
        if (isset($data['diseases'])) {
            update_post_meta($patient_id, '_systemic_diseases',
                wp_json_encode(array_map('sanitize_text_field', $data['diseases']))
            );
        }

        // آلرژی‌ها
        if (isset($data['allergies'])) {
            update_post_meta($patient_id, '_drug_allergies',
                sanitize_text_field($data['allergies'])
            );
        }

        // داروهای مصرفی
        if (isset($data['medications'])) {
            update_post_meta($patient_id, '_current_medications',
                sanitize_textarea_field($data['medications'])
            );
        }

        // یادداشت پزشکی
        if (isset($data['medical_notes'])) {
            update_post_meta($patient_id, '_medical_notes',
                sanitize_textarea_field($data['medical_notes'])
            );
        }

        // تاریخ آخرین بروزرسانی
        update_post_meta($patient_id, '_medical_history_updated', current_time('mysql'));
        update_post_meta($patient_id, '_medical_history_updated_by', get_current_user_id());
    }

    // ─── نسخه‌ی «خودِ بیمار پر و امضا کرد» — همون فیلدها + امضا،
    // برای اینکه از پرتال بیمار (نه فقط پرسنل) قابل ثبت باشه ──────────
    public static function save_patient_signed(int $patient_id, array $data, string $signature_data_url): bool {
        if (empty($signature_data_url)) return false;
        self::save($patient_id, $data);
        update_post_meta($patient_id, '_medical_history_signature', $signature_data_url);
        update_post_meta($patient_id, '_medical_history_signed_at', current_time('mysql'));
        update_post_meta($patient_id, '_medical_history_signed_by_patient', 1);
        return true;
    }

    public static function is_signed_by_patient(int $patient_id): bool {
        return (bool)get_post_meta($patient_id, '_medical_history_signed_by_patient', true);
    }

    public static function get_signature(int $patient_id): array {
        return [
            'signature' => get_post_meta($patient_id, '_medical_history_signature', true),
            'signed_at' => get_post_meta($patient_id, '_medical_history_signed_at', true),
        ];
    }

    /**
     * دریافت تاریخچه پزشکی
     */
    public static function get(int $patient_id): array {
        return [
            'diseases'    => json_decode(get_post_meta($patient_id, '_systemic_diseases', true) ?: '[]', true) ?: [],
            'allergies'   => get_post_meta($patient_id, '_drug_allergies', true),
            'medications' => get_post_meta($patient_id, '_current_medications', true),
            'notes'       => get_post_meta($patient_id, '_medical_notes', true),
            'updated_at'  => get_post_meta($patient_id, '_medical_history_updated', true),
            'updated_by'  => (int)get_post_meta($patient_id, '_medical_history_updated_by', true),
        ];
    }

    /**
     * اضافه کردن تصویر/رادیوگرافی به پرونده بیمار
     *
     * @param int    $patient_id  شناسه بیمار
     * @param int    $attachment_id  شناسه Media در وردپرس
     * @param string $type        نوع: xray_pa | xray_panoramic | xray_cbct | photo_intra | photo_extra
     * @param string $tooth_ref   شماره دندان مرتبط (اختیاری)
     * @param string $notes       توضیحات
     */
    public static function add_image(
        int    $patient_id,
        int    $attachment_id,
        string $type       = 'xray_pa',
        string $tooth_ref  = '',
        string $notes      = ''
    ): bool {
        // بررسی وجود فایل
        if (!wp_attachment_is_image($attachment_id) && get_post_mime_type($attachment_id) !== 'application/pdf') {
            return false;
        }

        // اتصال فایل به بیمار
        wp_update_post(['ID' => $attachment_id, 'post_parent' => $patient_id]);

        // ذخیره meta اضافه
        update_post_meta($attachment_id, '_dental_image_type',      $type);
        update_post_meta($attachment_id, '_dental_patient_id',      $patient_id);
        update_post_meta($attachment_id, '_dental_tooth_ref',       $tooth_ref);
        update_post_meta($attachment_id, '_dental_image_notes',     $notes);
        update_post_meta($attachment_id, '_dental_image_date',      Dental_Jalali::today());
        update_post_meta($attachment_id, '_dental_uploaded_by',     get_current_user_id());

        // اضافه به لیست تصاویر بیمار
        $images = self::get_image_ids($patient_id);
        if (!in_array($attachment_id, $images)) {
            $images[] = $attachment_id;
            update_post_meta($patient_id, '_dental_images', wp_json_encode($images));
        }

        return true;
    }

    /**
     * دریافت لیست ID تصاویر
     */
    public static function get_image_ids(int $patient_id): array {
        $raw = get_post_meta($patient_id, '_dental_images', true);
        return json_decode($raw ?: '[]', true) ?: [];
    }

    /**
     * دریافت تصاویر با جزئیات کامل
     */
    public static function get_images(int $patient_id, string $type = ''): array {
        $ids    = self::get_image_ids($patient_id);
        $result = [];

        foreach ($ids as $id) {
            $post = get_post($id);
            if (!$post) continue;

            $img_type = get_post_meta($id, '_dental_image_type', true);
            if ($type && $img_type !== $type) continue;

            $result[] = [
                'id'          => $id,
                'url'         => wp_get_attachment_url($id),
                'thumb'       => wp_get_attachment_image_url($id, 'thumbnail'),
                'medium'      => wp_get_attachment_image_url($id, 'medium'),
                'type'        => $img_type,
                'type_label'  => self::get_type_label($img_type),
                'tooth_ref'   => get_post_meta($id, '_dental_tooth_ref', true),
                'notes'       => get_post_meta($id, '_dental_image_notes', true),
                'date_jalali' => get_post_meta($id, '_dental_image_date', true),
                'uploaded_by' => (int)get_post_meta($id, '_dental_uploaded_by', true),
                'mime_type'   => get_post_mime_type($id),
                'filename'    => basename(get_attached_file($id)),
            ];
        }

        // مرتب‌سازی از جدید به قدیم
        usort($result, fn($a,$b) => strcmp($b['date_jalali'], $a['date_jalali']));

        return $result;
    }

    /**
     * حذف تصویر
     */
    public static function remove_image(int $patient_id, int $attachment_id): bool {
        $ids = self::get_image_ids($patient_id);
        $ids = array_values(array_filter($ids, fn($id) => $id !== $attachment_id));
        update_post_meta($patient_id, '_dental_images', wp_json_encode($ids));
        wp_delete_attachment($attachment_id, true);
        return true;
    }

    /**
     * انواع تصاویر
     */
    public static function get_image_types(): array {
        return [
            'xray_pa'        => 'رادیوگرافی PA',
            'xray_bwx'       => 'رادیوگرافی BWX',
            'xray_panoramic' => 'رادیوگرافی پانورامیک',
            'xray_cbct'      => 'CBCT',
            'photo_intra'    => 'عکس داخل دهانی',
            'photo_extra'    => 'عکس خارج دهانی',
            'photo_smile'    => 'عکس لبخند',
            'consent'        => 'فرم رضایت',
            'other'          => 'سایر',
        ];
    }

    public static function get_type_label(string $type): string {
        return self::get_image_types()[$type] ?? $type;
    }

    /**
     * بیماری‌های سیستمیک تعریف‌شده
     */
    public static function get_disease_list(): array {
        return [
            'diabetes'       => ['label' => 'دیابت',               'alert' => 'کنترل قند خون قبل از درمان'],
            'hypertension'   => ['label' => 'فشار خون بالا',        'alert' => 'بررسی فشار قبل از تزریق'],
            'heart'          => ['label' => 'بیماری قلبی',          'alert' => 'مشاوره قلب قبل از جراحی'],
            'bleeding'       => ['label' => 'اختلال انعقاد خون',   'alert' => 'احتیاط در کشیدن دندان'],
            'asthma'         => ['label' => 'آسم',                  'alert' => 'اسپری برونکودیلاتور آماده باشد'],
            'kidney'         => ['label' => 'بیماری کلیوی',         'alert' => 'دوز داروها را تنظیم کنید'],
            'liver'          => ['label' => 'بیماری کبدی',          'alert' => 'احتیاط در تجویز آنتی‌بیوتیک'],
            'thyroid'        => ['label' => 'بیماری تیروئید',       'alert' => ''],
            'pregnancy'      => ['label' => 'بارداری',              'alert' => 'از رادیوگرافی و داروهای غیرضروری بپرهیزید'],
            'osteoporosis'   => ['label' => 'پوکی استخوان',         'alert' => 'بیس‌فسفونات → ONJ risk'],
            'hiv'            => ['label' => 'HIV/AIDS',             'alert' => 'پروتکل کنترل عفونت'],
            'hepatitis'      => ['label' => 'هپاتیت',              'alert' => 'پروتکل کنترل عفونت'],
        ];
    }
}
