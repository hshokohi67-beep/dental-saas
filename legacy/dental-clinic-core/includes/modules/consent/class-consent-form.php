<?php
defined('ABSPATH') || exit;

/**
 * Class Dental_Consent_Form
 * مدیریت رضایت‌نامه‌های دیجیتال
 */
class Dental_Consent_Form {

    // انواع درمان‌هایی که نیاز به رضایت‌نامه دارن
    public static function get_consent_required_treatments(): array {
        return [
            'rct'          => 'عصب‌کشی (RCT)',
            'surgical_ext' => 'کشیدن جراحی دندان',
            'implant'      => 'ایمپلنت',
            'crown'        => 'روکش دندان',
            'veneer'       => 'لامینیت',
            'bridge_abutment' => 'بریج',
            'apicoectomy'  => 'آپیکوستومی',
            'pulpotomy'    => 'پالپوتومی',
            'pulpectomy'   => 'پالپکتومی',
        ];
    }

    public static function get_template(string $treatment_code): string {
        $saved = get_option('dental_consent_tpl_' . $treatment_code, '');
        if ($saved) return $saved;

        $defaults = [
            'rct' => "رضایت‌نامه درمان ریشه (عصب‌کشی)\n\nاینجانب {patient_name} با آگاهی کامل از روش درمانی عصب‌کشی دندان {tooth_info} رضایت خود را اعلام می‌نمایم.\n\nموارد توضیح داده شده:\n• احتمال نیاز به جلسات متعدد\n• احتمال درد و تورم پس از درمان\n• نیاز به روکش پس از اتمام درمان\n• احتمال شکست درمان در موارد نادر\n\nپزشک معالج: {doctor_name}\nتاریخ: {date}",
            'surgical_ext' => "رضایت‌نامه جراحی کشیدن دندان\n\nاینجانب {patient_name} با آگاهی کامل از عمل جراحی کشیدن دندان {tooth_info} رضایت خود را اعلام می‌نمایم.\n\nموارد توضیح داده شده:\n• احتمال درد و تورم پس از جراحی\n• رعایت دستورالعمل‌های بعد از جراحی\n• احتمال کبودی در ناحیه عمل\n• محدودیت غذایی پس از جراحی\n\nپزشک معالج: {doctor_name}\nتاریخ: {date}",
            'implant' => "رضایت‌نامه کاشت ایمپلنت\n\nاینجانب {patient_name} با آگاهی کامل از مراحل کاشت ایمپلنت در ناحیه {tooth_info} رضایت خود را اعلام می‌نمایم.\n\nموارد توضیح داده شده:\n• مراحل چندگانه درمان\n• دوره استئواینتگریشن (۳ تا ۶ ماه)\n• رعایت بهداشت دهان و دندان\n• احتمال شکست در موارد نادر\n• هزینه‌های مراقبتی آینده\n\nپزشک معالج: {doctor_name}\nتاریخ: {date}",
            'crown' => "رضایت‌نامه روکش دندان\n\nاینجانب {patient_name} با آگاهی کامل از درمان روکش دندان {tooth_info} رضایت خود را اعلام می‌نمایم.\n\nموارد توضیح داده شده:\n• تراشیدن بخشی از دندان طبیعی\n• استفاده از روکش موقت تا آماده شدن روکش اصلی\n• احتمال حساسیت موقت\n• نیاز به مراقبت‌های منظم\n\nپزشک معالج: {doctor_name}\nتاریخ: {date}",
        ];

        return $defaults[$treatment_code]
            ?? "رضایت‌نامه درمان\n\nاینجانب {patient_name} با آگاهی کامل از درمان {treatment_name} در ناحیه {tooth_info} رضایت خود را اعلام می‌نمایم.\n\nپزشک معالج: {doctor_name}\nتاریخ: {date}";
    }

    public static function get_pending_consents(int $patient_id): array {
        global $wpdb;

        $conditions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_tooth_conditions
             WHERE patient_id = %d AND is_active = 1",
            $patient_id
        ), ARRAY_A);

        $consent_required = self::get_consent_required_treatments();
        $done_map = get_post_meta($patient_id, '_chart_done_map', true) ?: [];
        $pending  = [];

        foreach($conditions as $row) {
            $treatments = json_decode($row['tooth_surface'] ?: '[]', true) ?: [];
            $key = $row['tooth_number'] . '_' . $row['tooth_type'];

            foreach($treatments as $code) {
                if (!isset($consent_required[$code])) continue;
                $dk = $key . '_' . $code;

                // چک کن رضایت‌نامه قبلاً امضا شده؟
                $signed = get_post_meta($patient_id, '_consent_signed_' . $dk, true);
                if ($signed) continue;

                $pending[] = [
                    'key'            => $dk,
                    'treatment_code' => $code,
                    'treatment_name' => $consent_required[$code],
                    'tooth_number'   => $row['tooth_number'],
                    'tooth_type'     => $row['tooth_type'],
                    'row'            => $row,
                ];
            }
        }

        return $pending;
    }

    public static function get_signed_consents(int $patient_id): array {
        $meta   = get_post_meta($patient_id);
        $signed = [];
        foreach($meta as $key => $vals) {
            if (strpos($key, '_consent_signed_') === 0) {
                $data = maybe_unserialize($vals[0]);
                if (is_array($data)) $signed[$key] = $data;
            }
        }
        return $signed;
    }

    public static function save_consent(int $patient_id, string $key, string $signature_data, int $doctor_id = 0): bool {
        if (empty($signature_data) || strpos($signature_data, 'data:image') !== 0) return false;

        // آپلود امضا به Media Library
        $upload_dir = wp_upload_dir();
        $filename   = 'consent-' . $patient_id . '-' . sanitize_key($key) . '-' . time() . '.png';
        $filepath   = $upload_dir['path'] . '/' . $filename;

        $img_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signature_data));
        if (!$img_data) return false;

        file_put_contents($filepath, $img_data);

        $attach_id = wp_insert_attachment([
            'post_mime_type' => 'image/png',
            'post_title'     => 'امضای رضایت‌نامه — ' . $key,
            'post_status'    => 'private',
        ], $filepath);

        update_post_meta($patient_id, '_consent_signed_' . $key, [
            'signed_at'    => current_time('mysql'),
            'signed_jalali'=> Dental_Jalali::today(),
            'doctor_id'    => $doctor_id ?: get_current_user_id(),
            'doctor_name'  => get_userdata($doctor_id ?: get_current_user_id())->display_name ?? '',
            'attach_id'    => $attach_id,
            'ip'           => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        return true;
    }
}
