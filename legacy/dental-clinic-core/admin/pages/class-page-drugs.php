<?php
defined('ABSPATH') || exit;

class Dental_Page_Drugs {

    public function render(): void {
        if (isset($_POST['dental_save_drug']) && check_admin_referer('dental_drugs')) {
            Dental_Prescription_Manager::save_drug((int)($_POST['drug_id'] ?? 0) ?: null, $_POST);
            echo '<div class="notice notice-success"><p>✅ ذخیره شد.</p></div>';
        }
        if (isset($_GET['deactivate_drug'])) {
            Dental_Prescription_Manager::delete_drug((int)$_GET['deactivate_drug']);
        }
        if (isset($_GET['activate_drug'])) {
            Dental_Prescription_Manager::activate_drug((int)$_GET['activate_drug']);
        }

        $drugs = Dental_Prescription_Manager::get_drugs(false);
        $cat_labels = ['antibiotic'=>'آنتی‌بیوتیک','analgesic'=>'مسکن/ضدالتهاب','other'=>'سایر'];
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <i data-lucide="pill" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                دیتابیس داروها
            </h1>
            <p class="dc-text-muted" style="margin-bottom:20px;">داروهای قابل‌انتخاب موقع نسخه‌نویسی — می‌تونید خودتون اضافه/ویرایش کنید.</p>

            <div class="dc-card" style="margin-bottom:24px;">
                <div class="dc-card-header"><h3 class="dc-heading-4">➕ افزودن داروی جدید</h3></div>
                <form method="post">
                    <?php wp_nonce_field('dental_drugs'); ?>
                    <input type="hidden" name="drug_id" value="0">
                    <div class="dc-card-body" style="display:grid;grid-template-columns:1.5fr 1.5fr 1fr 2fr auto;gap:10px;align-items:end;">
                        <div><label class="dc-label">نام دارو (فارسی)</label><input type="text" name="name" class="dc-input" required></div>
                        <div><label class="dc-label">نام انگلیسی/ژنریک</label><input type="text" name="english_name" class="dc-input" dir="ltr" placeholder="مثلاً: Amoxicillin 500mg"></div>
                        <div>
                            <label class="dc-label">دسته</label>
                            <select name="category" class="dc-select">
                                <option value="antibiotic">آنتی‌بیوتیک</option>
                                <option value="analgesic">مسکن/ضدالتهاب</option>
                                <option value="other">سایر</option>
                            </select>
                        </div>
                        <div><label class="dc-label">یادداشت دوز پیش‌فرض (اختیاری)</label><input type="text" name="default_dose_note" class="dc-input" placeholder="مثلاً: ۵۰۰ میلی‌گرم هر ۸ ساعت"></div>
                        <button type="submit" name="dental_save_drug" class="dc-btn dc-btn-primary">افزودن</button>
                    </div>
                </form>
            </div>

            <div class="dc-card">
                <div class="dc-card-header"><h3 class="dc-heading-4">📋 لیست داروها (<?php echo count($drugs); ?>)</h3></div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>نام فارسی</th><th>نام انگلیسی</th><th>دسته</th><th>یادداشت دوز</th><th>وضعیت</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach($drugs as $d): ?>
                        <tr style="<?php echo !$d['is_active']?'opacity:.5;':''; ?>">
                            <td style="font-weight:600;"><?php echo esc_html($d['name']); ?></td>
                            <td style="direction:ltr;text-align:left;color:var(--dc-neutral-500);"><?php echo esc_html($d['english_name']); ?></td>
                            <td><?php echo esc_html($cat_labels[$d['category']] ?? $d['category']); ?></td>
                            <td style="font-size:12px;color:var(--dc-neutral-500);"><?php echo esc_html($d['default_dose_note']); ?></td>
                            <td><?php echo $d['is_active'] ? '✅ فعال' : '⛔ غیرفعال'; ?></td>
                            <td>
                                <?php if ($d['is_active']): ?>
                                <a href="<?php echo esc_url(add_query_arg('deactivate_drug',$d['id'])); ?>" onclick="return confirm('غیرفعال بشه؟');" style="color:var(--dc-danger);font-size:11px;">غیرفعال کردن</a>
                                <?php else: ?>
                                <a href="<?php echo esc_url(add_query_arg('activate_drug',$d['id'])); ?>" style="color:var(--dc-accent-dark);font-size:11px;">✅ فعال کردن دوباره</a>
                                <?php endif; ?>
                            </td>
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
