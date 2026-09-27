<?php
defined('ABSPATH') || exit;

/**
 * جزئیات تخصصی عصب‌کشی (Endodontic Details) — کاملاً جدا از مسیر
 * درمان عمومی؛ به یه رکورد واقعی درمان (dental_catalog_treatments)
 * وصل می‌شه، نه به ساختار مسیر. طبق همون کلید امنی که برای کل
 * ماژول مسیر درمان ساختیم.
 */
class Dental_Endodontic_Manager {

    public static function is_enabled(): bool {
        return class_exists('Dental_Features') && Dental_Features::enabled('treatment_pathway');
    }

    public static function get_obturation_options(): array {
        return ['کاندنسیشن جانبی', 'کاندنسیشن عمودی گرم', 'تک نقطه', 'ترمافیل', 'سایر'];
    }
    public static function get_restoration_options(): array {
        return ['کامپوزیت موقت', 'کاویت', 'IRM', 'گلاس‌آینومر موقت', 'بدون ترمیم موقت'];
    }

    // ─── تشخیص خودکار «این خدمت واقعاً عصب‌کشیه؟» — دقیقاً هم‌الگو با
    // تشخیص مدارک بیمه (detect_required_docs) — نه یه تصمیم بالینی
    // (که کِی لازمه)، فقط تشخیص «این دکمه اصلاً مرتبطه یا نه» تا کنار
    // فلوراید/جرم‌گیری بی‌ربط ظاهر نشه ──────────────────────────────
    public static function is_endodontic_service(string $service_name): bool {
        return mb_stripos($service_name, 'عصب') !== false
            || mb_stripos($service_name, 'اندو') !== false
            || mb_stripos($service_name, 'روت کانال') !== false
            || mb_stripos($service_name, 'RCT') !== false
            || mb_stripos($service_name, 'پالپ') !== false; // پالپوتومی/پالپکتومی اطفال
    }

    public static function get_by_treatment(int $treatment_id): ?array {
        global $wpdb;
        $detail = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_endodontic_details WHERE treatment_id=%d", $treatment_id
        ), ARRAY_A);
        if (!$detail) return null;
        $detail['canals'] = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_endodontic_canals WHERE detail_id=%d ORDER BY id ASC", $detail['id']
        ), ARRAY_A);
        return $detail;
    }

    public static function save(int $treatment_id, int $patient_id, ?int $tooth_number, array $data, array $canals): int {
        global $wpdb;

        // ─── فقط همون پزشکی که خودِ خدمت رو ثبت کرده، اجازه‌ی ثبت/اصلاح
        // جزئیات تخصصیش رو داره — مدیر هم می‌تونه (برای مواقع اصلاحی) ──
        $treatment_doctor = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT doctor_id FROM {$wpdb->prefix}dental_catalog_treatments WHERE id=%d", $treatment_id
        ));
        $cu = wp_get_current_user();
        $is_admin = current_user_can('manage_options') || in_array('dental_admin', (array)$cu->roles);
        if (!$is_admin && $treatment_doctor !== get_current_user_id()) {
            return 0; // اجازه نداره — caller باید این حالت رو مدیریت کنه
        }

        $existing_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}dental_endodontic_details WHERE treatment_id=%d", $treatment_id
        ));

        $row = [
            'patient_id'            => $patient_id,
            'tooth_number'          => $tooth_number,
            'number_of_canals'      => !empty($data['number_of_canals']) ? (int)$data['number_of_canals'] : null,
            'number_of_visits'      => !empty($data['number_of_visits']) ? (int)$data['number_of_visits'] : null,
            'irrigation'            => sanitize_text_field($data['irrigation'] ?? ''),
            'sealer'                => sanitize_text_field($data['sealer'] ?? ''),
            'obturation_method'     => sanitize_text_field($data['obturation_method'] ?? ''),
            'intracanal_medication' => sanitize_text_field($data['intracanal_medication'] ?? ''),
            'temporary_restoration' => sanitize_text_field($data['temporary_restoration'] ?? ''),
            'notes'                 => sanitize_textarea_field($data['notes'] ?? ''),
            'doctor_id'             => get_current_user_id(),
        ];

        if ($existing_id) {
            $row['updated_at'] = current_time('mysql');
            $wpdb->update($wpdb->prefix.'dental_endodontic_details', $row, ['id'=>$existing_id]);
            $detail_id = (int)$existing_id;
            // پاک‌کردن کانال‌های قبلی و درج دوباره — ساده‌تر از diff زدن
            $wpdb->delete($wpdb->prefix.'dental_endodontic_canals', ['detail_id'=>$detail_id]);
        } else {
            $row['treatment_id'] = $treatment_id;
            $row['created_at'] = current_time('mysql');
            $wpdb->insert($wpdb->prefix.'dental_endodontic_details', $row);
            $detail_id = (int)$wpdb->insert_id;
        }

        foreach ($canals as $c) {
            if (empty($c['canal_name'])) continue;
            $wpdb->insert($wpdb->prefix.'dental_endodontic_canals', [
                'detail_id'      => $detail_id,
                'canal_name'     => sanitize_text_field($c['canal_name']),
                'working_length' => !empty($c['working_length']) ? (float)$c['working_length'] : null,
                'final_file'     => sanitize_text_field($c['final_file'] ?? ''),
                'notes'          => sanitize_text_field($c['notes'] ?? ''),
            ]);
        }

        if (class_exists('Dental_Audit_Log')) {
            Dental_Audit_Log::log('patient_data_changed', "جزئیات تخصصی عصب‌کشی برای بیمار #{$patient_id} " . ($existing_id ? 'ویرایش' : 'ثبت') . ' شد',
                ['entity_type'=>'endodontic_detail', 'entity_id'=>$detail_id]);
        }
        return $detail_id;
    }
}
