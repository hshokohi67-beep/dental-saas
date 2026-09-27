<?php
defined('ABSPATH') || exit;

class Dental_Page_Patients {

    public function maybe_handle_post(): void {
        if (isset($_POST['dental_save_patient'])) {
            check_admin_referer('dental_save_patient');
            $this->save_patient();
        }
        if (isset($_GET['trash']) && check_admin_referer('dental_trash_patient_' . $_GET['trash'])) {
            if (!current_user_can('manage_options')) wp_die('این عملیات فقط برای مدیر کلینیک مجاز است.');
            wp_trash_post((int)$_GET['trash']);
            wp_safe_redirect(remove_query_arg(['trash','_wpnonce'], add_query_arg('trashed','1')));
            exit;
        }
        if (isset($_GET['restore']) && check_admin_referer('dental_restore_patient_' . $_GET['restore'])) {
            if (!current_user_can('manage_options')) wp_die('این عملیات فقط برای مدیر کلینیک مجاز است.');
            wp_untrash_post((int)$_GET['restore']);
            wp_safe_redirect(remove_query_arg(['restore','_wpnonce']));
            exit;
        }
        if (isset($_GET['delete_forever']) && check_admin_referer('dental_delete_patient_' . $_GET['delete_forever'])) {
            if (!current_user_can('manage_options')) wp_die('این عملیات فقط برای مدیر کلینیک مجاز است.');
            wp_delete_post((int)$_GET['delete_forever'], true);
            wp_safe_redirect(remove_query_arg(['delete_forever','_wpnonce'], add_query_arg('deleted','1')));
            exit;
        }
    }

    public function render(): void {
        $view      = sanitize_key($_GET['view'] ?? 'active');
        $search    = sanitize_text_field($_GET['s'] ?? '');
        $patients  = $this->get_patients($search, $view);
        $add_new   = isset($_GET['action']) && $_GET['action'] === 'new';
        $saved     = isset($_GET['saved']);
        $is_admin  = current_user_can('manage_options');
        ?>
        <div class="dental-admin-wrap">

            <div class="dc-flex dc-items-center dc-justify-between" style="margin-bottom:24px;">
                <div>
                    <h1 class="dc-heading-2">👥 کاربران</h1>
                    <p class="dc-text-muted">مدیریت پرونده‌های کاربران کلینیک</p>
                </div>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dental-patients&action=new')); ?>"
                   class="dc-btn dc-btn-primary">
                    ➕ کاربر جدید
                </a>
            </div>

            <?php if ($saved): ?>
            <div class="dc-card" style="background:var(--dc-success-light);border-color:var(--dc-accent);margin-bottom:20px;">
                <div class="dc-card-body" style="padding:12px 20px;">✅ کاربر با موفقیت ذخیره شد.</div>
            </div>
            <?php endif; ?>
            <?php if (isset($_GET['trashed'])): ?>
            <div class="dc-card" style="background:var(--dc-warning-light);border-color:var(--dc-accent-warm);margin-bottom:20px;">
                <div class="dc-card-body" style="padding:12px 20px;">🗑️ پرونده به زباله‌دان منتقل شد. <a href="<?php echo esc_url(add_query_arg('view','trash')); ?>">مشاهده زباله‌دان</a></div>
            </div>
            <?php endif; ?>
            <?php if (isset($_GET['deleted'])): ?>
            <div class="dc-card" style="background:var(--dc-danger-light);border-color:var(--dc-danger);margin-bottom:20px;">
                <div class="dc-card-body" style="padding:12px 20px;">✅ پرونده برای همیشه حذف شد.</div>
            </div>
            <?php endif; ?>

            <?php if ($add_new): ?>
                <?php $this->render_add_form(); ?>
            <?php else: ?>
                <?php if ($is_admin): ?>
                <div style="display:flex;gap:6px;margin-bottom:14px;">
                    <a href="<?php echo esc_url(remove_query_arg(['view','trashed','deleted'])); ?>"
                       style="padding:6px 14px;border-radius:20px;font-size:12px;text-decoration:none;font-family:Tahoma;
                              background:<?php echo $view==='active'?'var(--dc-primary)':'var(--dc-neutral-100)'; ?>;
                              color:<?php echo $view==='active'?'#fff':'var(--dc-neutral-700)'; ?>;">فعال</a>
                    <a href="<?php echo esc_url(add_query_arg('view','trash')); ?>"
                       style="padding:6px 14px;border-radius:20px;font-size:12px;text-decoration:none;font-family:Tahoma;
                              background:<?php echo $view==='trash'?'var(--dc-danger)':'var(--dc-neutral-100)'; ?>;
                              color:<?php echo $view==='trash'?'#fff':'var(--dc-neutral-700)'; ?>;">🗑️ زباله‌دان</a>
                </div>
                <?php endif; ?>
                <?php $this->render_list($patients, $search, $view, $is_admin); ?>
            <?php endif; ?>

        </div>
        <?php
    }

    private function render_list(array $patients, string $search, string $view = 'active', bool $is_admin = false): void {
        ?>
        <!-- جستجو -->
        <div class="dc-card" style="margin-bottom:20px;">
            <div class="dc-card-body" style="padding:16px 20px;">
                <form method="get" style="display:flex;gap:10px;align-items:center;">
                    <input type="hidden" name="page" value="dental-patients">
                    <input type="hidden" name="view" value="<?php echo esc_attr($view); ?>">
                    <input type="text" name="s" value="<?php echo esc_attr($search); ?>"
                        placeholder="جستجو با نام یا موبایل..."
                        class="dc-input" style="max-width:300px;">
                    <button type="submit" class="dc-btn dc-btn-secondary">🔍 جستجو</button>
                    <?php if ($search): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dental-patients&view=' . $view)); ?>"
                       class="dc-btn dc-btn-ghost">✕ پاک کردن</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- لیست -->
        <div class="dc-card">
            <div class="dc-card-header">
                <h3 class="dc-heading-4"><?php echo $view==='trash' ? 'زباله‌دان' : 'لیست کاربران'; ?>
                    <span class="dc-badge dc-badge-neutral" style="margin-right:8px;"><?php echo count($patients); ?> مورد</span>
                </h3>
            </div>
            <div class="dc-table-wrap">
                <table class="dc-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام کاربر</th>
                            <th>موبایل</th>
                            <th>کیف پول</th>
                            <th>تاریخ ثبت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($patients)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;padding:40px;color:var(--dc-neutral-500);">
                                <?php echo $view==='trash' ? '🗑️ زباله‌دان خالی است.' : ($search ? '🔍 نتیجه‌ای یافت نشد.' : '👤 هنوز کاربری ثبت نشده.'); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($patients as $i => $p):
                            $mobile  = get_post_meta($p->ID, '_patient_mobile', true);
                            $wallet  = (float) get_post_meta($p->ID, '_wallet_balance', true);
                            $date    = Dental_Jalali::to_jalali($p->post_date, 'Y/m/d');
                            $initial = mb_substr($p->post_title, 0, 1);
                            $profile_url = admin_url('admin.php?page=dental-patients&action=profile&id=' . $p->ID);
                        ?>
                        <tr<?php echo $view!=='trash' ? ' style="cursor:pointer;" onclick="window.location=\''.esc_url($profile_url).'\'"' : ''; ?>>
                            <td style="color:var(--dc-neutral-500);font-size:12px;"><?php echo $i + 1; ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--dc-primary),var(--dc-primary-dark));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;flex-shrink:0;">
                                        <?php echo esc_html($initial); ?>
                                    </div>
                                    <span style="font-weight:500;"><?php echo esc_html($p->post_title); ?></span>
                                </div>
                            </td>
                            <td style="direction:ltr;text-align:right;font-family:monospace;">
                                <?php echo esc_html($mobile ?: '—'); ?>
                            </td>
                            <td>
                                <?php if ($wallet > 0): ?>
                                <span class="dc-badge dc-badge-success">
                                    <?php echo number_format($wallet); ?> ت
                                </span>
                                <?php else: ?>
                                <span style="color:var(--dc-neutral-400);">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px;color:var(--dc-neutral-500);"><?php echo esc_html($date); ?></td>
                            <td onclick="event.stopPropagation()">
                                <div style="display:flex;gap:4px;">
                                <?php if ($view === 'trash'): ?>
                                    <?php if ($is_admin): ?>
                                    <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('restore',$p->ID),'dental_restore_patient_'.$p->ID)); ?>"
                                       class="dc-btn dc-btn-secondary dc-btn-sm">♻️ بازگردانی</a>
                                    <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('delete_forever',$p->ID),'dental_delete_patient_'.$p->ID)); ?>"
                                       class="dc-btn dc-btn-ghost dc-btn-sm" style="color:var(--dc-danger);"
                                       onclick="return confirm('این پرونده برای همیشه و بدون بازگشت حذف شود؟')">🗑️ حذف کامل</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="<?php echo esc_url($profile_url); ?>" class="dc-btn dc-btn-secondary dc-btn-sm">
                                        📋 پرونده
                                    </a>
                                    <?php if ($is_admin): ?>
                                    <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('trash',$p->ID),'dental_trash_patient_'.$p->ID)); ?>"
                                       class="dc-btn dc-btn-ghost dc-btn-sm" style="color:var(--dc-danger);"
                                       onclick="return confirm('این پرونده به زباله‌دان منتقل شود؟ (قابل بازگردانی است)')">🗑️</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    private function render_add_form(): void {
        ?>
        <div class="dc-card" style="max-width:600px;">
            <div class="dc-card-header">
                <h3 class="dc-heading-4">➕ ثبت کاربر جدید</h3>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dental-patients')); ?>"
                   class="dc-btn dc-btn-ghost dc-btn-sm">← بازگشت</a>
            </div>
            <div class="dc-card-body">
                <form method="post">
                    <?php wp_nonce_field('dental_save_patient'); ?>
                    <div class="dc-grid dc-grid-2" style="gap:16px;">
                        <div class="dc-form-group">
                            <label class="dc-label">نام و نام خانوادگی <span style="color:red">*</span></label>
                            <input type="text" name="patient_name" class="dc-input" required placeholder="مثال: علی احمدی">
                        </div>
                        <div class="dc-form-group">
                            <label class="dc-label">شماره موبایل <span style="color:red">*</span></label>
                            <input type="tel" name="patient_mobile" class="dc-input" required placeholder="09xxxxxxxxx" dir="ltr">
                        </div>
                        <div class="dc-form-group">
                            <label class="dc-label">جنسیت</label>
                            <select name="patient_gender" class="dc-select">
                                <option value="">انتخاب کنید</option>
                                <option value="male">مرد</option>
                                <option value="female">زن</option>
                            </select>
                        </div>
                        <div class="dc-form-group">
                            <label class="dc-label">تاریخ تولد (شمسی)</label>
                            <input type="text" name="patient_dob" class="dc-input dc-datepicker" placeholder="1370/01/01" dir="ltr">
                        </div>
                        <div class="dc-form-group" style="grid-column:1/-1;">
                            <label class="dc-label">آلرژی به دارو</label>
                            <input type="text" name="patient_allergy" class="dc-input" placeholder="مثال: پنی‌سیلین، آمپی‌سیلین">
                        </div>
                        <div class="dc-form-group" style="grid-column:1/-1;">
                            <label class="dc-label">توضیحات</label>
                            <textarea name="patient_notes" class="dc-textarea" placeholder="یادداشت‌های اولیه..."></textarea>
                        </div>
                    </div>
                    <button type="submit" name="dental_save_patient" class="dc-btn dc-btn-primary">
                        💾 ثبت کاربر
                    </button>
                </form>
            </div>
        </div>
        <?php
    }

    private function save_patient(): void {
        $name    = sanitize_text_field($_POST['patient_name']   ?? '');
        $mobile  = sanitize_text_field($_POST['patient_mobile'] ?? '');
        $gender  = sanitize_text_field($_POST['patient_gender'] ?? '');
        $dob     = sanitize_text_field($_POST['patient_dob']    ?? '');
        $allergy = sanitize_text_field($_POST['patient_allergy'] ?? '');
        $notes   = sanitize_textarea_field($_POST['patient_notes'] ?? '');

        if (empty($name) || empty($mobile)) return;

        // نرمال‌سازی موبایل
        if (class_exists('Dental_OTP_Manager')) {
            $mobile = Dental_OTP_Manager::normalize_mobile($mobile);
        }

        $post_id = wp_insert_post([
            'post_type'   => Dental_CPT_Patient::POST_TYPE,
            'post_title'  => $name,
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ]);

        if (is_wp_error($post_id)) return;

        update_post_meta($post_id, '_patient_mobile',    $mobile);
        update_post_meta($post_id, '_patient_name',      $name);
        update_post_meta($post_id, '_patient_gender',    $gender);
        update_post_meta($post_id, '_patient_dob_jalali', $dob);
        update_post_meta($post_id, '_drug_allergies',    $allergy);
        update_post_meta($post_id, '_patient_notes',     $notes);
        update_post_meta($post_id, '_wallet_balance',    0);
        update_post_meta($post_id, '_loyalty_points',    0);
        update_post_meta($post_id, '_systemic_diseases', wp_json_encode([]));

        wp_safe_redirect(admin_url('admin.php?page=dental-patients&saved=1'));
        exit;
    }

    private function get_patients(string $search = '', string $view = 'active'): array {
        $args = [
            'post_type'      => Dental_CPT_Patient::POST_TYPE,
            'posts_per_page' => 100,
            'post_status'    => $view === 'trash' ? 'trash' : 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ];

        if ($search) {
            $args['s'] = $search;
        }

        // ─── دندانپزشک فقط بیمارهای خودش را می‌بیند، نه کل کلینیک را ──
        $cu = wp_get_current_user();
        $is_doctor_only = in_array('dental_doctor', (array)$cu->roles) && !current_user_can('manage_options');
        if ($is_doctor_only) {
            global $wpdb;
            $ids = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT patient_id FROM {$wpdb->prefix}dental_reception_queue WHERE doctor_id=%d", $cu->ID
            ));
            // جدول نوبت‌دهی فقط اگر آن پلاگین فعال باشد وجود دارد
            if (class_exists('Dental_Booking_Appointment')) {
                $appt_ids = $wpdb->get_col($wpdb->prepare(
                    "SELECT DISTINCT patient_id FROM {$wpdb->prefix}dental_appointments WHERE doctor_id=%d", $cu->ID
                ));
                $ids = array_merge($ids, $appt_ids);
            }
            $ids = array_unique(array_map('intval', $ids));
            if (empty($ids)) return []; // اگه هنوز هیچ بیماری نداشته، لیست خالی برگردد نه همه بیمارها
            $args['post__in'] = $ids;
        }

        return get_posts($args) ?: [];
    }
}
