<?php
defined('ABSPATH') || exit;

class Dental_Page_Inventory_Log {

    public function render(): void {
        $msg = '';
        if (isset($_POST['dental_inv_transaction']) && check_admin_referer('dental_inventory_tx')) {
            $item_id = (int)($_POST['item_id'] ?? 0);
            $type    = sanitize_key($_POST['type'] ?? 'in');
            $qty     = (float)($_POST['quantity'] ?? 0);
            $extra = [
                'batch_expiry' => !empty($_POST['batch_expiry']) ? Dental_Jalali::to_gregorian(sanitize_text_field($_POST['batch_expiry'])) : null,
                'unit_cost'    => !empty($_POST['unit_cost']) ? (int)$_POST['unit_cost'] : null,
                'note'         => sanitize_text_field($_POST['note'] ?? ''),
            ];
            $result = Dental_Inventory_Manager::add_transaction($item_id, $type, $qty, $extra);
            $msg = $result['success'] ? '✅ ثبت شد.' : '❌ ' . $result['message'];
        }

        $items = Dental_Inventory_Manager::get_items();
        $transactions = Dental_Inventory_Manager::get_transactions();
        ?>
        <div class="dental-admin-wrap">
            <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&inv_view=hub')); ?>" style="font-size:12px;color:var(--dc-primary);text-decoration:none;display:inline-block;margin-bottom:10px;">← بازگشت به انبار</a>
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <i data-lucide="arrow-left-right" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                ثبت ورود/خروج کالا
            </h1>
            <?php if ($msg): ?>
            <div style="background:var(--dc-accent-light);border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:13px;"><?php echo esc_html($msg); ?></div>
            <?php endif; ?>

            <div class="dc-card" style="margin-bottom:20px;">
                <div class="dc-card-header"><h3 class="dc-heading-4">ثبت تراکنش جدید</h3></div>
                <form method="post">
                    <?php wp_nonce_field('dental_inventory_tx'); ?>
                    <div class="dc-card-body" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">کالا</label>
                            <select name="item_id" class="dc-select" required>
                                <option value="">— انتخاب کنید —</option>
                                <?php foreach($items as $it): ?>
                                <option value="<?php echo $it['id']; ?>">
                                    <?php echo esc_html($it['name']); ?> (موجودی: <?php echo number_format($it['current_stock'],1); ?> <?php echo esc_html($it['unit']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">نوع</label>
                            <select name="type" class="dc-select">
                                <option value="in">➕ ورود (خرید)</option>
                                <option value="out">➖ خروج (مصرف)</option>
                            </select>
                        </div>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">مقدار</label>
                            <input type="number" step="0.01" name="quantity" class="dc-input" required>
                        </div>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">تاریخ انقضا (فقط برای ورود، اختیاری)</label>
                            <input type="text" name="batch_expiry" class="dc-input dc-datepicker" placeholder="۱۴۰۵/۰۶/۰۱">
                        </div>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">قیمت واحد این محموله (اختیاری)</label>
                            <input type="number" name="unit_cost" class="dc-input">
                        </div>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">یادداشت</label>
                            <input type="text" name="note" class="dc-input">
                        </div>
                    </div>
                    <div class="dc-card-body" style="padding-top:0;">
                        <button type="submit" name="dental_inv_transaction" class="dc-btn dc-btn-primary">ثبت تراکنش</button>
                    </div>
                </form>
            </div>

            <div class="dc-card">
                <div class="dc-card-header"><h3 class="dc-heading-4">📋 تاریخچه تراکنش‌ها (۵۰ مورد اخیر)</h3></div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>تاریخ</th><th>کالا</th><th>نوع</th><th>مقدار</th><th>ثبت‌کننده</th><th>یادداشت</th></tr></thead>
                        <tbody>
                        <?php if (empty($transactions)): ?>
                        <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--dc-neutral-400);">تراکنشی ثبت نشده</td></tr>
                        <?php else: foreach($transactions as $t): ?>
                        <tr>
                            <td style="font-size:12px;"><?php echo esc_html(Dental_Jalali::to_jalali(date('Y-m-d',strtotime($t['created_at'])),'Y/m/d')); ?></td>
                            <td style="font-weight:600;"><?php echo esc_html($t['item_name']); ?></td>
                            <td><?php echo $t['type']==='in' ? '<span style="color:var(--dc-accent-dark);">➕ ورود</span>' : '<span style="color:var(--dc-danger);">➖ خروج</span>'; ?></td>
                            <td><?php echo number_format($t['quantity'],1); ?> <?php echo esc_html($t['unit']); ?></td>
                            <td style="font-size:12px;color:var(--dc-neutral-500);"><?php echo esc_html($t['recorded_by_name']); ?></td>
                            <td style="font-size:12px;color:var(--dc-neutral-500);"><?php echo esc_html($t['note']); ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }
}
