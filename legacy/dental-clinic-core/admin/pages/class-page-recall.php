<?php
defined('ABSPATH') || exit;

class Dental_Page_Recall {

    public function render(): void {
        if (isset($_POST['dental_save_rule']) && check_admin_referer('dental_recall')) {
            $id = (int)($_POST['rule_id'] ?? 0);
            Dental_Recall_Manager::save_rule($id ?: null, $_POST);
            echo '<div class="notice notice-success"><p>✅ قانون ذخیره شد.</p></div>';
        }
        if (isset($_GET['delete_rule'])) {
            Dental_Recall_Manager::delete_rule((int)$_GET['delete_rule']);
            echo '<div class="notice notice-success"><p>✅ حذف شد.</p></div>';
        }
        if (isset($_POST['dental_scan_recalls']) && check_admin_referer('dental_recall')) {
            $n = Dental_Recall_Manager::scan_due_recalls();
            echo '<div class="notice notice-success"><p>✅ ' . $n . ' بیمار جدید به لیست موعدرسیده اضافه شد.</p></div>';
        }
        if (isset($_POST['dental_send_recall']) && check_admin_referer('dental_recall')) {
            $ok = Dental_Recall_Manager::send_recall_sms((int)$_POST['log_id']);
            echo $ok ? '<div class="notice notice-success"><p>✅ پیامک ارسال شد.</p></div>' : '<div class="notice notice-error"><p>❌ شماره موبایل بیمار ثبت نشده.</p></div>';
        }
        if (isset($_GET['dismiss_recall'])) {
            Dental_Recall_Manager::dismiss_recall((int)$_GET['dismiss_recall']);
        }

        $rules = Dental_Recall_Manager::get_rules();
        $due_list = Dental_Recall_Manager::get_due_list();
        $catalog_options = class_exists('Dental_Service_Catalog') && method_exists('Dental_Service_Catalog','get_leaf_treatments_flat')
            ? Dental_Service_Catalog::get_leaf_treatments_flat() : [];
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <i data-lucide="bell-ring" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                یادآوری چکاپ دوره‌ای (ریکال)
            </h1>
            <p class="dc-text-muted" style="margin-bottom:20px;">بیمارهایی که موعد چکاپ دوره‌ای‌شون رسیده رو خودکار پیدا کن و پیامک یادآور بفرست.</p>

            <div class="dc-card" style="margin-bottom:20px;">
                <div class="dc-card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                    <div style="font-size:13px;color:var(--dc-neutral-600);">این دکمه، بیمارهای موعدرسیده‌ی جدید رو پیدا و به لیست پایین اضافه می‌کنه (این کار خودکار هم هرشب انجام می‌شه).</div>
                    <form method="post"><?php wp_nonce_field('dental_recall'); ?>
                        <button type="submit" name="dental_scan_recalls" class="dc-btn dc-btn-primary dc-btn-sm">🔍 بررسی موارد جدید</button>
                    </form>
                </div>
            </div>

            <div class="dc-card" style="margin-bottom:24px;">
                <div class="dc-card-header"><h3 class="dc-heading-4">📋 بیمارهای موعدرسیده (<?php echo count($due_list); ?>)</h3></div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>بیمار</th><th>موبایل</th><th>قانون</th><th>موعد</th><th>اقدام</th></tr></thead>
                        <tbody>
                        <?php if (empty($due_list)): ?>
                        <tr><td colspan="5" style="text-align:center;padding:20px;color:var(--dc-neutral-400);">فعلاً کسی موعدش نرسیده</td></tr>
                        <?php else: foreach($due_list as $d): ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo esc_html($d['patient_name']); ?></td>
                            <td style="font-size:12px;direction:ltr;text-align:right;"><?php echo esc_html($d['mobile'] ?: '—ثبت‌نشده—'); ?></td>
                            <td style="font-size:12px;color:var(--dc-neutral-500);"><?php echo esc_html($d['rule_name']); ?></td>
                            <td style="font-size:12px;"><?php echo esc_html(Dental_Jalali::to_jalali($d['due_date'],'Y/m/d')); ?></td>
                            <td style="white-space:nowrap;">
                                <form method="post" style="display:inline;">
                                    <?php wp_nonce_field('dental_recall'); ?>
                                    <input type="hidden" name="log_id" value="<?php echo $d['id']; ?>">
                                    <button type="submit" name="dental_send_recall" class="dc-btn dc-btn-secondary dc-btn-sm">📤 ارسال پیامک</button>
                                </form>
                                <a href="<?php echo esc_url(add_query_arg('dismiss_recall',$d['id'])); ?>" style="font-size:11px;color:var(--dc-neutral-400);margin-right:6px;">نادیده بگیر</a>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dc-card" style="margin-bottom:20px;">
                <div class="dc-card-header"><h3 class="dc-heading-4">⚙️ قوانین ریکال</h3></div>
                <form method="post">
                    <?php wp_nonce_field('dental_recall'); ?>
                    <input type="hidden" name="rule_id" value="0">
                    <div class="dc-card-body" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">نام قانون</label>
                            <input type="text" name="name" class="dc-input" placeholder="مثلاً: یادآور جرم‌گیری" required>
                        </div>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">خدمت محرک (اختیاری)</label>
                            <select name="trigger_catalog_id" class="dc-select">
                                <option value="">هر خدمتی</option>
                                <?php foreach($catalog_options as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo esc_html($c['full_path'] ?? $c['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">فاصله (ماه)</label>
                            <input type="number" name="recall_months" class="dc-input" value="6" min="1">
                        </div>
                        <div class="dc-form-group" style="margin:0;grid-column:1/-1;">
                            <label class="dc-label">متن پیامک (اختیاری — {clinic_name} و {patient_name} جایگزین می‌شن)</label>
                            <textarea name="sms_template" class="dc-input" rows="2" placeholder="کلینیک {clinic_name}&#10;سلام {patient_name} عزیز، وقت چکاپ دوره‌ای شما فرارسیده..."></textarea>
                        </div>
                    </div>
                    <div class="dc-card-body" style="padding-top:0;">
                        <button type="submit" name="dental_save_rule" class="dc-btn dc-btn-primary">➕ افزودن قانون</button>
                    </div>
                </form>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>نام</th><th>خدمت محرک</th><th>فاصله</th><th>وضعیت</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach($rules as $r): ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo esc_html($r['name']); ?></td>
                            <td style="font-size:12px;color:var(--dc-neutral-500);"><?php echo $r['trigger_catalog_id'] ? 'خاص' : 'هر خدمتی'; ?></td>
                            <td><?php echo (int)$r['recall_months']; ?> ماه</td>
                            <td><?php echo $r['is_active'] ? '🟢 فعال' : '⚫ غیرفعال'; ?></td>
                            <td><a href="<?php echo esc_url(add_query_arg('delete_rule',$r['id'])); ?>" onclick="return confirm('حذف بشه؟');" style="color:var(--dc-danger);font-size:11px;">حذف</a></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }
}
