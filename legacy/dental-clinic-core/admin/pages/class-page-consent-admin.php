<?php
defined('ABSPATH') || exit;

class Dental_Page_Consent_Admin {

    public function render(): void {
        if (isset($_POST['dental_save_consent_tpl'])) {
            check_admin_referer('dental_consent_settings');
            foreach (Dental_Consent_Form::get_consent_required_treatments() as $code => $name) {
                if (isset($_POST['consent_tpl'][$code])) {
                    update_option('dental_consent_tpl_' . $code, sanitize_textarea_field($_POST['consent_tpl'][$code]));
                }
            }
            echo '<div class="notice notice-success"><p>تمپلیت‌ها ذخیره شد.</p></div>';
        }

        $treatments = Dental_Consent_Form::get_consent_required_treatments();
        global $wpdb;

        // آمار رضایت‌نامه‌های امضاشده
        $signed_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '_consent_signed_%'"
        );
        ?>
        <div class="dental-admin-wrap">
            <div style="margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div>
                    <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;">
                        <i data-lucide="file-signature" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                        رضایت‌نامه‌های دیجیتال
                    </h1>
                    <p class="dc-text-muted">مدیریت متن فرم‌های رضایت و مشاهده وضعیت امضاها</p>
                </div>
                <div style="background:var(--dc-primary-light);border-radius:10px;padding:12px 20px;text-align:center;">
                    <div style="font-size:24px;font-weight:700;color:var(--dc-primary);"><?php echo (int)$signed_count; ?></div>
                    <div style="font-size:12px;color:var(--dc-neutral-600);">رضایت‌نامه امضاشده</div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

                <!-- تمپلیت‌ها -->
                <div class="dc-card">
                    <div class="dc-card-header">
                        <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                            <i data-lucide="edit-3" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                            متن رضایت‌نامه‌ها
                        </h3>
                    </div>
                    <div class="dc-card-body">
                        <form method="post">
                            <?php wp_nonce_field('dental_consent_settings'); ?>
                            <div style="display:flex;flex-direction:column;gap:16px;">
                                <?php foreach($treatments as $code => $name): ?>
                                <div style="border:1px solid var(--dc-neutral-200);border-radius:8px;overflow:hidden;">
                                    <div style="background:var(--dc-primary-light);padding:8px 14px;font-size:12px;font-weight:700;color:var(--dc-primary);border-bottom:1px solid var(--dc-neutral-200);">
                                        <?php echo esc_html($name); ?>
                                    </div>
                                    <div style="padding:10px;">
                                        <div style="font-size:11px;color:var(--dc-neutral-500);margin-bottom:6px;">
                                            متغیرها: <code>{patient_name}</code> <code>{tooth_info}</code> <code>{treatment_name}</code> <code>{doctor_name}</code> <code>{date}</code>
                                        </div>
                                        <textarea name="consent_tpl[<?php echo esc_attr($code); ?>]"
                                            style="width:100%;height:120px;border:1px solid var(--dc-neutral-200);border-radius:6px;padding:8px;font-family:Tahoma;font-size:12px;resize:vertical;box-sizing:border-box;direction:rtl;"><?php
                                            echo esc_textarea(Dental_Consent_Form::get_template($code));
                                        ?></textarea>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div style="margin-top:16px;">
                                <button type="submit" name="dental_save_consent_tpl" class="dc-btn dc-btn-primary">💾 ذخیره تمپلیت‌ها</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- لیست رضایت‌نامه‌های اخیر -->
                <div class="dc-card">
                    <div class="dc-card-header">
                        <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                            <i data-lucide="list" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                            رضایت‌نامه‌های اخیر
                        </h3>
                    </div>
                    <div class="dc-table-wrap">
                        <table class="dc-table">
                            <thead>
                                <tr><th>بیمار</th><th>نوع درمان</th><th>تاریخ</th><th>عملیات</th></tr>
                            </thead>
                            <tbody>
                            <?php
                            $rows = $wpdb->get_results(
                                "SELECT p.ID, p.post_title, pm.meta_key, pm.meta_value
                                 FROM {$wpdb->prefix}postmeta pm
                                 JOIN {$wpdb->prefix}posts p ON pm.post_id = p.ID
                                 WHERE pm.meta_key LIKE '_consent_signed_%'
                                 AND p.post_type = 'dental_patient'
                                 ORDER BY pm.meta_id DESC LIMIT 30",
                                ARRAY_A
                            );
                            if(empty($rows)):
                            ?>
                            <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--dc-neutral-400);">رضایت‌نامه‌ای ثبت نشده</td></tr>
                            <?php else: foreach($rows as $row):
                                $data  = maybe_unserialize($row['meta_value']);
                                $key   = str_replace('_consent_signed_','',$row['meta_key']);
                                $parts = explode('_',$key);
                                $code  = end($parts);
                                $tx    = $treatments[$code] ?? $code;
                                $date  = is_array($data) ? ($data['signed_jalali']??'') : '';
                            ?>
                            <tr>
                                <td style="font-weight:500;">
                                    <a href="<?php echo esc_url(admin_url("admin.php?page=dental-patients&action=profile&id={$row['ID']}")); ?>"
                                       style="color:var(--dc-primary);text-decoration:none;"><?php echo esc_html($row['post_title']); ?></a>
                                </td>
                                <td style="font-size:12px;"><?php echo esc_html($tx); ?></td>
                                <td style="font-size:12px;color:var(--dc-neutral-500);"><?php echo esc_html($date); ?></td>
                                <td>
                                    <?php if(is_array($data) && !empty($data['attach_id'])): ?>
                                    <a href="<?php echo esc_url(add_query_arg(['dental_print_consent'=>$key,'dental_print'=>'consent','patient_id'=>$row['ID']],
                                        dental_get_portal_url())); ?>"
                                       target="_blank" class="dc-btn dc-btn-ghost dc-btn-sm">
                                        <i data-lucide="printer" style="width:12px;height:12px;"></i> پرینت
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }
}
