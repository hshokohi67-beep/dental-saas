<?php
defined('ABSPATH') || exit;

/**
 * صفحه تاریخچه پزشکی — رندر داخل تب Medical در پروفایل بیمار
 */
class Dental_Page_Medical {

    private int $patient_id;

    public function __construct(int $patient_id) {
        $this->patient_id = $patient_id;
    }

    // ─── رفع همون باگ «صفحه رفرش می‌شه ولی چیزی ذخیره نمی‌شه» — منطق
    // ذخیره از render() (که دیر اجرا می‌شه) جدا شد، تا از admin_init
    // (زودهنگام) صدا زده بشه.
    public function maybe_handle_post(): void {
        $can_edit = current_user_can('manage_options') || in_array('dental_admin', (array)wp_get_current_user()->roles) || in_array('dental_doctor', (array)wp_get_current_user()->roles);
        if (!$can_edit || !isset($_POST['dental_save_medical'])) return;
        check_admin_referer('dental_medical_' . $this->patient_id);
        Dental_Medical_History::save($this->patient_id, [
            'diseases'    => array_map('sanitize_text_field', $_POST['diseases'] ?? []),
            'allergies'   => sanitize_text_field($_POST['allergies'] ?? ''),
            'medications' => sanitize_textarea_field($_POST['medications'] ?? ''),
            'medical_notes' => sanitize_textarea_field($_POST['medical_notes'] ?? ''),
        ]);
        wp_safe_redirect(add_query_arg(['tab' => 'medical', 'saved' => '1'],
            admin_url("admin.php?page=dental-patients&action=profile&id={$this->patient_id}")));
        exit;
    }

    public function render(): void {
        $can_edit = current_user_can('manage_options') || in_array('dental_admin', (array)wp_get_current_user()->roles) || in_array('dental_doctor', (array)wp_get_current_user()->roles);
        $can_view = $can_edit || in_array('dental_secretary', (array)wp_get_current_user()->roles) || in_array('dental_assistant', (array)wp_get_current_user()->roles);

        if (!$can_view) {
            echo '<div style="padding:40px;text-align:center;color:var(--dc-neutral-500);">⛔ شما مجاز به مشاهده تاریخچه پزشکی نیستید.</div>';
            return;
        }

        $history  = Dental_Medical_History::get($this->patient_id);
        $images   = Dental_Medical_History::get_images($this->patient_id);
        $diseases = Dental_Medical_History::get_disease_list();
        $saved    = isset($_GET['saved']);

        // نکته: مدیریت آپلود/حذف تصویر از اینجا حذف شد — الان توسط
        // سیستم جدید «تصویربرداری» (تب مجزا توی پرونده بیمار) انجام
        // می‌شه که خیلی کامل‌تره (Study/Series، Annotation، Viewer و...)

        if ($saved) {
            echo '<div style="background:#E8FAF4;border:1px solid #2ECC9A;border-radius:8px;padding:10px 16px;margin-bottom:14px;font-size:13px;">✅ ذخیره شد.</div>';
        }

        // هشدارهای پزشکی مهم
        $active_diseases = $history['diseases'] ?? [];
        $alerts = array_filter(array_map(fn($k) => isset($diseases[$k]) && $diseases[$k]['alert'] ? $diseases[$k] : null, $active_diseases));
        if (!empty($alerts)): ?>
        <div style="background:#FEF6E4;border:1px solid #F0A500;border-radius:10px;padding:14px 16px;margin-bottom:16px;">
            <div style="font-size:13px;font-weight:700;color:#7a5200;margin-bottom:8px;">⚠️ هشدارهای پزشکی</div>
            <?php foreach($active_diseases as $key):
                if (!isset($diseases[$key]) || !$diseases[$key]['alert']) continue; ?>
            <div style="font-size:12px;color:#7a5200;margin-bottom:4px;">
                🔸 <strong><?php echo esc_html($diseases[$key]['label']); ?>:</strong>
                <?php echo esc_html($diseases[$key]['alert']); ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif;

        // ─── فرم تاریخچه ──────────────────────────────────────
        ?>
        <div class="dc-grid dc-grid-2" style="gap:20px;align-items:start;">

            <!-- ستون ۱: بیماری‌ها -->
            <div class="dc-card">
                <div class="dc-card-header">
                    <h3 class="dc-heading-4">📋 تاریخچه پزشکی</h3>
                    <?php if ($history['updated_at']): ?>
                    <span style="font-size:11px;color:var(--dc-neutral-500);">
                        آخرین بروزرسانی: <?php echo esc_html(Dental_Jalali::to_jalali($history['updated_at'], 'Y/m/d')); ?>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="dc-card-body">
                    <?php if ($can_edit): ?>
                    <form method="post">
                        <?php wp_nonce_field('dental_medical_' . $this->patient_id); ?>

                        <div class="dc-form-group">
                            <label class="dc-label">آلرژی به دارو</label>
                            <input type="text" name="allergies" class="dc-input"
                                value="<?php echo esc_attr($history['allergies']); ?>"
                                placeholder="مثال: پنی‌سیلین، ایبوپروفن">
                        </div>

                        <div class="dc-form-group">
                            <label class="dc-label">داروهای مصرفی</label>
                            <textarea name="medications" class="dc-textarea" style="min-height:70px;"
                                placeholder="داروهایی که بیمار مصرف می‌کند..."><?php echo esc_textarea($history['medications']); ?></textarea>
                        </div>

                        <div class="dc-form-group">
                            <label class="dc-label" style="margin-bottom:10px;">بیماری‌های سیستمیک</label>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                                <?php foreach($diseases as $key => $d):
                                    $checked = in_array($key, $active_diseases);
                                ?>
                                <label style="display:flex;align-items:center;gap:6px;padding:6px 10px;border-radius:7px;border:1px solid <?php echo $checked?'#F0A500':'var(--dc-neutral-200)'; ?>;background:<?php echo $checked?'#FEF6E4':'#fff'; ?>;cursor:pointer;font-size:12px;transition:all .15s;">
                                    <input type="checkbox" name="diseases[]" value="<?php echo esc_attr($key); ?>"
                                        <?php checked($checked); ?> style="accent-color:#F0A500;width:14px;height:14px;">
                                    <?php echo esc_html($d['label']); ?>
                                    <?php if ($d['alert']): ?>
                                    <span title="<?php echo esc_attr($d['alert']); ?>" style="color:#F0A500;cursor:help;margin-right:auto;">⚠️</span>
                                    <?php endif; ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="dc-form-group">
                            <label class="dc-label">یادداشت پزشکی</label>
                            <textarea name="medical_notes" class="dc-textarea" style="min-height:80px;"
                                placeholder="یادداشت‌های مهم پزشکی..."><?php echo esc_textarea($history['notes']); ?></textarea>
                        </div>

                        <button type="submit" name="dental_save_medical" class="dc-btn dc-btn-primary">💾 ذخیره</button>
                    </form>
                    <?php else: ?>
                    <!-- نمایش فقط‌خواندنی -->
                    <div style="display:flex;flex-direction:column;gap:10px;font-size:13px;">
                        <?php if ($history['allergies']): ?>
                        <div><span style="color:#7A96A4;">آلرژی:</span> <strong><?php echo esc_html($history['allergies']); ?></strong></div>
                        <?php endif; ?>
                        <?php if ($history['medications']): ?>
                        <div><span style="color:#7A96A4;">داروها:</span> <?php echo esc_html($history['medications']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($active_diseases)): ?>
                        <div>
                            <span style="color:#7A96A4;display:block;margin-bottom:6px;">بیماری‌ها:</span>
                            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                <?php foreach($active_diseases as $key):
                                    if (!isset($diseases[$key])) continue; ?>
                                <span style="background:#FEF6E4;border:1px solid #F0A500;border-radius:6px;padding:3px 10px;font-size:12px;">
                                    <?php echo esc_html($diseases[$key]['label']); ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * پردازش آپلود فایل
     */
    private function handle_upload(): void {
        if (empty($_FILES['dental_image']['name'])) return;

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attachment_id = media_handle_upload('dental_image', $this->patient_id);

        if (is_wp_error($attachment_id)) {
            Dental_Dev_Logger::log('ERROR', 'خطا در آپلود تصویر', [
                'error' => $attachment_id->get_error_message()
            ]);
            return;
        }

        Dental_Medical_History::add_image(
            $this->patient_id,
            $attachment_id,
            sanitize_text_field($_POST['image_type'] ?? 'other'),
            sanitize_text_field($_POST['tooth_ref']   ?? ''),
            sanitize_text_field($_POST['image_notes'] ?? '')
        );

        wp_safe_redirect(add_query_arg(['tab' => 'medical', 'saved' => '1'],
            admin_url("admin.php?page=dental-patients&action=profile&id={$this->patient_id}")));
        exit;
    }
}
