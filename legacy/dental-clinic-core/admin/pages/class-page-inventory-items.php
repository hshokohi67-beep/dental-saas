<?php
defined('ABSPATH') || exit;

class Dental_Page_Inventory_Items {

    public function render(): void {
        if (isset($_POST['dental_save_item']) && check_admin_referer('dental_inventory_item')) {
            $id = (int)($_POST['item_id'] ?? 0);
            Dental_Inventory_Manager::save_item($id ?: null, $_POST);
            echo '<div class="notice notice-success"><p>✅ ذخیره شد.</p></div>';
        }
        if (isset($_POST['dental_add_category']) && check_admin_referer('dental_inventory_item')) {
            Dental_Inventory_Manager::save_category($_POST['new_category'] ?? '');
        }
        if (isset($_GET['deactivate'])) {
            Dental_Inventory_Manager::deactivate_item((int)$_GET['deactivate']);
            echo '<div class="notice notice-success"><p>✅ کالا غیرفعال شد.</p></div>';
        }
        if (isset($_POST['dental_bulk_import']) && check_admin_referer('dental_inventory_item')) {
            $result = Dental_Inventory_Manager::bulk_import_from_seed();
            echo '<div class="notice notice-success"><p>✅ ' . (int)$result['imported'] . ' کالای جدید وارد شد' . (($result['skipped']??0) ? ' (' . (int)$result['skipped'] . ' مورد تکراری رد شد)' : '') . '.</p></div>';
        }

        $categories = Dental_Inventory_Manager::get_categories();
        $cat_names  = array_column($categories, 'name', 'id');

        // ─── فیلترها — از GET؛ برای ۱۱۴۵ ردیف خیلی سریع‌تر از فیلتر
        // جاوااسکریپتی روی کل لیست (که همه رو یه‌جا رندر می‌کرد) ────
        $filter_cat = (int)($_GET['fcat'] ?? 0);
        $filter_q   = sanitize_text_field($_GET['fq'] ?? '');
        $page       = max(1, (int)($_GET['pg'] ?? 1));
        $per_page   = 50;

        $result = Dental_Inventory_Manager::get_items_paged($page, $per_page, $filter_cat, $filter_q);
        $items = $result['items']; $total = $result['total']; $total_pages = max(1, $result['pages']);
        ?>
        <div class="dental-admin-wrap">
            <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&inv_view=hub')); ?>" style="font-size:12px;color:var(--dc-primary);text-decoration:none;display:inline-block;margin-bottom:10px;">← بازگشت به انبار</a>
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <i data-lucide="package" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                کالاهای انبار
            </h1>

            <?php if (class_exists('Dental_Inventory_Seed_Data')): ?>
            <div class="dc-card" style="margin-bottom:20px;border:1px dashed var(--dc-primary);">
                <div class="dc-card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                    <div>
                        <div style="font-weight:700;font-size:13px;margin-bottom:3px;">📥 ورود دسته‌جمعی</div>
                        <div style="font-size:12px;color:var(--dc-neutral-500);">کالاهای جدید (که قبلاً ثبت نشدن) اضافه می‌شن — تکراری‌ها رد می‌شن.</div>
                    </div>
                    <form method="post" onsubmit="return confirm('ادامه بدم؟');">
                        <?php wp_nonce_field('dental_inventory_item'); ?>
                        <button type="submit" name="dental_bulk_import" class="dc-btn dc-btn-primary dc-btn-sm">وارد کردن کالاهای جدید</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- افزودن کالا جدید -->
            <details style="margin-bottom:16px;">
                <summary style="cursor:pointer;font-size:13px;font-weight:700;color:var(--dc-primary);padding:8px 0;">➕ افزودن کالای جدید (کلیک کنید)</summary>
                <div class="dc-card" style="margin-top:8px;">
                    <form method="post">
                        <?php wp_nonce_field('dental_inventory_item'); ?>
                        <input type="hidden" name="item_id" value="0">
                        <div class="dc-card-body" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">نام کالا</label>
                                <input type="text" name="name" class="dc-input" required>
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">دسته‌بندی</label>
                                <select name="category_id" class="dc-select">
                                    <?php foreach($categories as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo esc_html($c['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">واحد</label>
                                <input type="text" name="unit" class="dc-input" value="عدد">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">حداقل موجودی هشدار</label>
                                <input type="number" step="0.01" name="min_stock" class="dc-input" value="0">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">قیمت واحد (تومان)</label>
                                <input type="number" name="unit_cost" class="dc-input" value="0">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">تأمین‌کننده (اختیاری)</label>
                                <input type="text" name="supplier_name" class="dc-input">
                            </div>
                        </div>
                        <div class="dc-card-body" style="padding-top:0;">
                            <button type="submit" name="dental_save_item" class="dc-btn dc-btn-primary">افزودن کالا</button>
                        </div>
                    </form>
                </div>
            </details>

            <details style="margin-bottom:20px;">
                <summary style="cursor:pointer;font-size:12px;color:var(--dc-neutral-500);">+ افزودن دسته‌بندی جدید</summary>
                <form method="post" style="display:flex;gap:8px;margin-top:8px;max-width:400px;">
                    <?php wp_nonce_field('dental_inventory_item'); ?>
                    <input type="text" name="new_category" class="dc-input" placeholder="نام دسته‌بندی" required>
                    <button type="submit" name="dental_add_category" class="dc-btn dc-btn-secondary dc-btn-sm">افزودن</button>
                </form>
            </details>

            <!-- فیلتر دسته + جستجو -->
            <div class="dc-card" style="margin-bottom:0;border-radius:12px 12px 0 0;">
                <div class="dc-card-body" style="padding:14px 16px;">
                    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                        <input type="hidden" name="page" value="dental-dashboard">
                        <input type="hidden" name="inv_view" value="items">
                        <select name="fcat" class="dc-select" style="max-width:220px;" onchange="this.form.submit()">
                            <option value="0">همه دسته‌ها</option>
                            <?php foreach($categories as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php selected($filter_cat, $c['id']); ?>><?php echo esc_html($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="fq" class="dc-input" value="<?php echo esc_attr($filter_q); ?>" placeholder="🔍 جستجوی نام کالا..." style="max-width:240px;">
                        <button type="submit" class="dc-btn dc-btn-primary dc-btn-sm">اعمال فیلتر</button>
                        <?php if($filter_cat || $filter_q): ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&inv_view=items')); ?>" class="dc-btn dc-btn-ghost dc-btn-sm">پاک‌کردن فیلتر</a>
                        <?php endif; ?>
                        <span style="margin-right:auto;font-size:12px;color:var(--dc-neutral-500);"><?php echo number_format($total); ?> کالا یافت شد</span>
                    </form>
                </div>
            </div>

            <!-- لیست کالاها -->
            <div class="dc-card" style="border-radius:0;margin-bottom:0;">
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>نام</th><th>دسته</th><th>موجودی</th><th>حداقل</th><th>واحد</th><th>قیمت واحد</th><th>انقضا</th><th style="width:90px;"></th></tr></thead>
                        <tbody id="dc-items-tbody">
                        <?php if (empty($items)): ?>
                        <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--dc-neutral-400);">کالایی یافت نشد</td></tr>
                        <?php else: foreach($items as $it):
                            $low = $it['min_stock'] > 0 && $it['current_stock'] <= $it['min_stock'];
                            $expiring = $it['nearest_expiry'] && strtotime($it['nearest_expiry']) <= strtotime('+30 days');
                        ?>
                        <tr class="dc-item-row" data-id="<?php echo $it['id']; ?>" style="<?php echo $low?'background:var(--dc-danger-light);':''; ?>">
                            <td class="dc-cell-name" style="font-weight:600;"><?php echo esc_html($it['name']); ?></td>
                            <td class="dc-cell-cat" data-val="<?php echo (int)$it['category_id']; ?>" style="font-size:12px;color:var(--dc-neutral-500);"><?php echo esc_html($cat_names[$it['category_id']] ?? '—'); ?></td>
                            <td class="dc-cell-stock" style="font-weight:700;<?php echo $low?'color:var(--dc-danger);':''; ?>"><?php echo number_format($it['current_stock'],1); ?></td>
                            <td class="dc-cell-min" style="font-size:12px;color:var(--dc-neutral-500);"><?php echo number_format($it['min_stock'],1); ?></td>
                            <td class="dc-cell-unit" style="font-size:12px;"><?php echo esc_html($it['unit']); ?></td>
                            <td class="dc-cell-cost" style="font-size:12px;"><?php echo number_format($it['unit_cost']); ?> ت</td>
                            <td style="font-size:11px;<?php echo $expiring?'color:var(--dc-danger);font-weight:700;':''; ?>">
                                <?php echo $it['nearest_expiry'] ? esc_html(Dental_Jalali::to_jalali($it['nearest_expiry'],'Y/m/d')) : '—'; ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="#" onclick="dcEditRow(<?php echo $it['id']; ?>);return false;" style="font-size:11px;margin-left:8px;">✏️ ویرایش</a>
                                <a href="<?php echo esc_url(add_query_arg('deactivate',$it['id'])); ?>" onclick="return confirm('غیرفعال بشه؟');" style="color:var(--dc-danger);font-size:11px;">حذف</a>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_pages > 1): ?>
                <div style="padding:14px 16px;display:flex;gap:6px;align-items:center;justify-content:center;flex-wrap:wrap;border-top:1px solid var(--dc-neutral-100);">
                    <?php
                    $base_args = array_filter(['page'=>'dental-dashboard','inv_view'=>'items','fcat'=>$filter_cat?:null,'fq'=>$filter_q?:null], fn($v)=>$v!==null);
                    for ($p = 1; $p <= $total_pages; $p++):
                        if ($p > 3 && $p < $total_pages - 2 && abs($p - $page) > 2) { if ($p == 4) echo '<span style="color:var(--dc-neutral-300);">…</span>'; continue; }
                    ?>
                    <a href="<?php echo esc_url(add_query_arg(array_merge($base_args,['pg'=>$p]), admin_url('admin.php'))); ?>"
                       class="dc-btn <?php echo $p==$page?'dc-btn-primary':'dc-btn-ghost'; ?> dc-btn-sm" style="min-width:32px;text-align:center;"><?php echo $p; ?></a>
                    <?php endfor; ?>
                    <span style="font-size:11px;color:var(--dc-neutral-400);margin-right:10px;">صفحه <?php echo $page; ?> از <?php echo $total_pages; ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- مودال ویرایش سریع -->
        <div id="dc-edit-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.8);z-index:999999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:16px;padding:22px;width:100%;max-width:420px;">
                <div style="font-weight:700;font-size:14px;margin-bottom:14px;">✏️ ویرایش کالا</div>
                <div class="dc-form-group" style="margin-bottom:10px;">
                    <label class="dc-label">نام</label>
                    <input type="text" id="dc-ed-name" class="dc-input">
                </div>
                <div class="dc-grid dc-grid-2" style="gap:10px;margin-bottom:10px;">
                    <div class="dc-form-group" style="margin:0;"><label class="dc-label">دسته</label>
                        <select id="dc-ed-cat" class="dc-select">
                            <?php foreach($categories as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo esc_html($c['name']); ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="dc-form-group" style="margin:0;"><label class="dc-label">واحد</label><input type="text" id="dc-ed-unit" class="dc-input"></div>
                </div>
                <div class="dc-grid dc-grid-2" style="gap:10px;margin-bottom:10px;">
                    <div class="dc-form-group" style="margin:0;"><label class="dc-label">موجودی فعلی</label><input type="number" step="0.01" id="dc-ed-stock" class="dc-input"></div>
                    <div class="dc-form-group" style="margin:0;"><label class="dc-label">حداقل هشدار</label><input type="number" step="0.01" id="dc-ed-min" class="dc-input"></div>
                </div>
                <div class="dc-form-group" style="margin-bottom:14px;"><label class="dc-label">قیمت واحد</label><input type="number" id="dc-ed-cost" class="dc-input"></div>
                <div style="display:flex;gap:8px;">
                    <button onclick="dcSaveEdit()" class="dc-btn dc-btn-primary" style="flex:1;">ذخیره</button>
                    <button onclick="document.getElementById('dc-edit-modal').style.display='none'" class="dc-btn dc-btn-ghost">انصراف</button>
                </div>
            </div>
        </div>

        <script>
        if(typeof lucide!=="undefined")lucide.createIcons();
        var dcEdNonce = '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>';
        var dcEdAjax  = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        var dcEdCurrentId = 0;

        function dcEditRow(id){
            var row = document.querySelector('.dc-item-row[data-id="'+id+'"]');
            if(!row) return;
            dcEdCurrentId = id;
            document.getElementById('dc-ed-name').value = row.querySelector('.dc-cell-name').textContent.trim();
            document.getElementById('dc-ed-cat').value = row.querySelector('.dc-cell-cat').dataset.val;
            document.getElementById('dc-ed-unit').value = row.querySelector('.dc-cell-unit').textContent.trim();
            document.getElementById('dc-ed-stock').value = parseFloat(row.querySelector('.dc-cell-stock').textContent) || 0;
            document.getElementById('dc-ed-min').value = parseFloat(row.querySelector('.dc-cell-min').textContent) || 0;
            document.getElementById('dc-ed-cost').value = parseFloat(row.querySelector('.dc-cell-cost').textContent.replace(/[^\d.]/g,'')) || 0;
            document.getElementById('dc-edit-modal').style.display = 'flex';
        }
        function dcSaveEdit(){
            var fd = new FormData();
            fd.append('action','dental_inventory_quick_update');
            fd.append('_wpnonce', dcEdNonce);
            fd.append('item_id', dcEdCurrentId);
            fd.append('name', document.getElementById('dc-ed-name').value);
            fd.append('category_id', document.getElementById('dc-ed-cat').value);
            fd.append('unit', document.getElementById('dc-ed-unit').value);
            fd.append('current_stock', document.getElementById('dc-ed-stock').value);
            fd.append('min_stock', document.getElementById('dc-ed-min').value);
            fd.append('unit_cost', document.getElementById('dc-ed-cost').value);
            fetch(dcEdAjax, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                document.getElementById('dc-edit-modal').style.display = 'none';
                if(res.success) location.reload();
                else alert('خطا در ذخیره');
            });
        }
        </script>
        <?php
    }
}
