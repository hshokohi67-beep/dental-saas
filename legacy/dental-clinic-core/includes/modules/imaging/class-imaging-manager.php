<?php
defined('ABSPATH') || exit;

/**
 * سیستم Imaging — فاز ۱ (بدون DICOM واقعی، معماری آماده برای فاز ۲).
 * فایل‌ها خارج از پوشه‌ی عمومی uploads وردپرس ذخیره می‌شن (نه Media
 * Library عادی) تا از طریق URL مستقیم و حدس‌زدنی قابل‌دسترسی نباشن —
 * فقط از طریق همین کلاس (با چک دسترسی) استریم می‌شن.
 */
class Dental_Imaging_Manager {

    public static function get_modality_labels(): array {
        return [
            'periapical'       => 'پری‌اپیکال (PA)',
            'bitewing'         => 'بایت‌وینگ',
            'panoramic'        => 'پانورامیک (OPG)',
            'cephalometric'    => 'سفالومتری',
            'cbct'             => 'CBCT',
            'intraoral_photo'  => 'عکس داخل دهانی',
            'extraoral_photo'  => 'عکس بیرون دهانی',
            'clinical_photo'   => 'عکس بالینی',
            'other'            => 'سایر',
        ];
    }

    // ─── مسیر امن ذخیره‌سازی — خارج از /uploads/ عمومی ────────────
    public static function get_storage_dir(): string {
        $upload = wp_upload_dir();
        $dir = trailingslashit($upload['basedir']) . 'dental-imaging-private';
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
            // جلوگیری از دسترسی مستقیم به فایل‌ها از طریق URL
            file_put_contents($dir . '/.htaccess', "Deny from all\n");
            file_put_contents($dir . '/index.php', "<?php // silence\n");
        }
        return $dir;
    }

    // ═══════════════ Study ════════════════════════════════════════
    public static function create_study(array $data): int {
        global $wpdb;
        $wpdb->insert($wpdb->prefix.'dental_imaging_studies', [
            'patient_id'     => (int)$data['patient_id'],
            'doctor_id'      => !empty($data['doctor_id']) ? (int)$data['doctor_id'] : null,
            'appointment_id' => !empty($data['appointment_id']) ? (int)$data['appointment_id'] : null,
            'treatment_id'   => !empty($data['treatment_id']) ? (int)$data['treatment_id'] : null,
            'study_date'     => $data['study_date'] ?? current_time('Y-m-d'),
            'modality'       => sanitize_key($data['modality'] ?? 'other'),
            'description'    => sanitize_text_field($data['description'] ?? ''),
            'created_by'     => get_current_user_id(),
            'created_at'     => current_time('mysql'),
        ]);
        return (int)$wpdb->insert_id;
    }

    public static function get_patient_studies(int $patient_id): array {
        global $wpdb;
        $studies = $wpdb->get_results($wpdb->prepare(
            "SELECT s.*, u.display_name as doctor_name
             FROM {$wpdb->prefix}dental_imaging_studies s
             LEFT JOIN {$wpdb->users} u ON s.doctor_id = u.ID
             WHERE s.patient_id=%d ORDER BY s.study_date DESC, s.id DESC", $patient_id
        ), ARRAY_A);
        foreach ($studies as &$s) {
            $s['images'] = self::get_study_images((int)$s['id']);
        }
        return $studies;
    }

    // ─── تصاویر مرتبط با یه دندان خاص (برای اتصال به چارت دندان) ────
    public static function get_tooth_images(int $patient_id, int $tooth_number): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT i.*, s.modality, s.study_date, s.description as study_description
             FROM {$wpdb->prefix}dental_imaging_images i
             JOIN {$wpdb->prefix}dental_imaging_studies s ON i.study_id=s.id
             WHERE s.patient_id=%d AND i.tooth_number=%d
             ORDER BY s.study_date DESC", $patient_id, $tooth_number
        ), ARRAY_A);
    }

    // ═══════════════ Image ════════════════════════════════════════
    public static function add_image(int $study_id, array $file, array $meta = []): array {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'message' => 'فایل نامعتبر است.'];
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $file_type = wp_check_filetype($file['name']);
        // همون رفع باگ mime_content_type — فال‌بک به پسوند اگه fileinfo فعال نبود
        $mime = @mime_content_type($file['tmp_name']);
        if (!$mime) {
            $ext_to_mime = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp'];
            $mime = $ext_to_mime[strtolower($file_type['ext'] ?: '')] ?? false;
        }
        if (!$mime || !in_array($mime, $allowed)) {
            return ['success' => false, 'message' => 'فقط JPG/PNG/WEBP مجازه (DICOM در فاز بعدی اضافه می‌شه).'];
        }

        $dir = self::get_storage_dir();
        $ext = $file_type['ext'] ?: 'jpg';
        $unique_name = 'img_' . $study_id . '_' . wp_generate_password(12, false) . '.' . $ext;
        $dest = $dir . '/' . $unique_name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return ['success' => false, 'message' => 'خطا در ذخیره‌سازی فایل.'];
        }

        // ─── ساخت thumbnail (برای Lazy Loading — لیست‌ها فقط thumbnail
        // می‌گیرن، سایز اصلی فقط موقع باز کردن Viewer لود می‌شه) ────
        $thumb_name = 'thumb_' . $unique_name;
        $thumb_path = $dir . '/' . $thumb_name;
        self::make_thumbnail($dest, $thumb_path, $mime);

        [$width, $height] = @getimagesize($dest) ?: [null, null];

        global $wpdb;
        $wpdb->insert($wpdb->prefix.'dental_imaging_images', [
            'study_id'       => $study_id,
            'file_path'      => $unique_name,
            'thumbnail_path' => file_exists($thumb_path) ? $thumb_name : null,
            'file_type'      => $ext,
            'is_dicom'       => 0,
            'tooth_number'   => !empty($meta['tooth_number']) ? (int)$meta['tooth_number'] : null,
            'before_after'   => in_array($meta['before_after'] ?? '', ['before','after']) ? $meta['before_after'] : null,
            'width'          => $width,
            'height'         => $height,
            'uploaded_by'    => get_current_user_id(),
            'created_at'     => current_time('mysql'),
        ]);

        return ['success' => true, 'image_id' => (int)$wpdb->insert_id];
    }

    private static function make_thumbnail(string $src, string $dest, string $mime): void {
        if (!function_exists('imagecreatetruecolor')) return; // GD نصب نیست، صرف‌نظر کن (بدون خطا)
        $max_w = 300;
        [$w, $h] = @getimagesize($src) ?: [0, 0];
        if (!$w || !$h) return;

        $img = match($mime) {
            'image/jpeg' => @imagecreatefromjpeg($src),
            'image/png'  => @imagecreatefrompng($src),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($src) : false,
            default => false,
        };
        if (!$img) return;

        $ratio = $max_w / $w;
        $new_w = $max_w;
        $new_h = (int)($h * $ratio);
        $thumb = imagecreatetruecolor($new_w, $new_h);
        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
        imagejpeg($thumb, $dest, 82);
        imagedestroy($img);
        imagedestroy($thumb);
    }

    public static function get_study_images(int $study_id): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_imaging_images WHERE study_id=%d ORDER BY id ASC", $study_id
        ), ARRAY_A);
    }

    public static function get_image(int $image_id): ?array {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT i.*, s.patient_id FROM {$wpdb->prefix}dental_imaging_images i
             JOIN {$wpdb->prefix}dental_imaging_studies s ON i.study_id=s.id
             WHERE i.id=%d", $image_id
        ), ARRAY_A);
        return $row ?: null;
    }

    public static function delete_image(int $image_id): void {
        $img = self::get_image($image_id);
        if (!$img) return;
        $dir = self::get_storage_dir();
        @unlink($dir . '/' . $img['file_path']);
        if ($img['thumbnail_path']) @unlink($dir . '/' . $img['thumbnail_path']);
        global $wpdb;
        $wpdb->delete($wpdb->prefix.'dental_imaging_images', ['id'=>$image_id]);
        $wpdb->delete($wpdb->prefix.'dental_imaging_annotations', ['image_id'=>$image_id]);
        $wpdb->delete($wpdb->prefix.'dental_imaging_measurements', ['image_id'=>$image_id]);
    }

    // ─── استریم امن فایل — فقط بعد از چک دسترسی صدا زده می‌شه ──────
    public static function stream_file(string $filename): void {
        $dir = self::get_storage_dir();
        $path = realpath($dir . '/' . basename($filename));
        if (!$path || strpos($path, realpath($dir)) !== 0 || !file_exists($path)) {
            wp_die('فایل یافت نشد.', 404);
        }
        $mime = mime_content_type($path) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    // ═══════════════ Annotation ═══════════════════════════════════
    public static function save_annotation(int $image_id, string $type, array $data, string $color = '#E05252'): int {
        global $wpdb;
        $wpdb->insert($wpdb->prefix.'dental_imaging_annotations', [
            'image_id'   => $image_id,
            'type'       => sanitize_key($type),
            'data'       => wp_json_encode($data),
            'color'      => sanitize_text_field($color),
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql'),
        ]);
        return (int)$wpdb->insert_id;
    }

    public static function get_annotations(int $image_id): array {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_imaging_annotations WHERE image_id=%d ORDER BY id ASC", $image_id
        ), ARRAY_A);
        foreach ($rows as &$r) { $r['data'] = json_decode($r['data'], true); }
        return $rows;
    }

    public static function delete_annotation(int $id): void {
        global $wpdb;
        $wpdb->delete($wpdb->prefix.'dental_imaging_annotations', ['id'=>$id]);
    }

    // ═══════════════ Measurement ══════════════════════════════════
    // نکته مهم: چون فاز ۱ DICOM واقعی نداره (پس Pixel Spacing واقعی
    // هم نداریم)، هیچ‌وقت واحد میلی‌متر جعلی نمی‌سازیم — فقط px نسبی.
    public static function save_measurement(int $image_id, string $type, array $points, float $value): int {
        global $wpdb;
        $wpdb->insert($wpdb->prefix.'dental_imaging_measurements', [
            'image_id'   => $image_id,
            'type'       => sanitize_key($type),
            'points'     => wp_json_encode($points),
            'value'      => $value,
            'unit'       => 'px',
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql'),
        ]);
        return (int)$wpdb->insert_id;
    }

    public static function get_measurements(int $image_id): array {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_imaging_measurements WHERE image_id=%d ORDER BY id ASC", $image_id
        ), ARRAY_A);
        foreach ($rows as &$r) { $r['points'] = json_decode($r['points'], true); }
        return $rows;
    }
}
