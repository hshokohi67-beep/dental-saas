<?php
defined('ABSPATH') || exit;

class Dental_Page_Insurance {

    public function render(): void {
        if (isset($_POST['dental_save_insurance']) && check_admin_referer('dental_insurance')) {
            $id = (int)($_POST['insurance_id'] ?? 0);
            Dental_Insurance_Manager::save_company($id ?: null, $_POST);
            echo '<div class="notice notice-success"><p>✅ ذخیره شد.</p></div>';
        }
        if (isset($_POST['dental_save_tariff']) && check_admin_referer('dental_insurance')) {
            $catalog_id = (int)($_POST['catalog_id'] ?? 0);
            $insurance_id = (int)($_POST['tariff_insurance_id'] ?? 0);
            if ($catalog_id && $insurance_id) {
                $docs = implode(',', $_POST['docs'] ?? []);
                Dental_Insurance_Manager::save_tariff($insurance_id, $catalog_id, (float)($_POST['approved_tariff']??0), $_POST['franchise_percent']!==''?(float)$_POST['franchise_percent']:null, $docs);
                echo '<div class="notice notice-success"><p>✅ تعرفه ثبت شد.</p></div>';
            }
        }
        if (isset($_GET['delete_tariff'])) {
            Dental_Insurance_Manager::delete_tariff((int)$_GET['delete_tariff']);
        }

        $companies = Dental_Insurance_Manager::get_companies();
        $selected_id = (int)($_GET['ins_id'] ?? ($companies[0]['id'] ?? 0));
        $tariffs = $selected_id ? Dental_Insurance_Manager::get_tariffs($selected_id) : [];
        // نکته: قبلاً اینجا get_all_leaves_flat() کل برگ‌ها رو می‌گرفت
        // برای پرکردن یه <select> خام — که با کاتالوگ بزرگ هم کند بود
        // هم از صفحه می‌زد بیرون. جاش با سرچ زنده (همون AJAX کاتالوگ) عوض شد.
        $doc_labels = Dental_Insurance_Manager::get_doc_labels();
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <i data-lucide="shield-plus" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                بیمه
            </h1>
            <p class="dc-text-muted" style="margin-bottom:20px;">مدیریت شرکت‌های بیمه طرف‌قرارداد و تعرفه‌ی هر خدمت نزدشون.</p>

            <div style="background:var(--dc-primary-light);border-radius:8px;padding:12px 16px;font-size:12px;color:var(--dc-primary);margin-bottom:20px;">
                💡 تعرفه‌ی بیمه‌ها به‌صورت لحظه‌ای/API آپدیت نمی‌شه (چون چنین سرویسی برای بیمه‌های تکمیلی ایران عمومی وجود نداره) — سندیکای بیمه‌گران سالانه یه تعرفه منتشر می‌کنه که باید دستی همینجا وارد/به‌روز کنید.
            </div>

            <div style="display:grid;grid-template-columns:280px 1fr;gap:20px;">
                <div>
                    <div class="dc-card" style="margin-bottom:16px;">
                        <div class="dc-card-header"><h3 class="dc-heading-4">➕ افزودن بیمه</h3></div>
                        <form method="post">
                            <?php wp_nonce_field('dental_insurance'); ?>
                            <input type="hidden" name="insurance_id" value="0">
                            <div class="dc-card-body" style="display:flex;flex-direction:column;gap:10px;">
                                <input type="text" name="name" class="dc-input" placeholder="نام بیمه (مثلاً البرز)" required>
                                <select name="type" class="dc-select">
                                    <option value="supplementary">تکمیلی</option>
                                    <option value="base">پایه</option>
                                </select>
                                <input type="number" name="default_franchise_percent" class="dc-input" placeholder="فرانشیز پیش‌فرض٪" value="20">
                                <label style="display:flex;align-items:center;gap:6px;font-size:12px;"><input type="checkbox" name="is_active" value="1" checked> فعال</label>
                                <button type="submit" name="dental_save_insurance" class="dc-btn dc-btn-primary dc-btn-sm">افزودن</button>
                            </div>
                        </form>
                    </div>
                    <div class="dc-card">
                        <div class="dc-card-header"><h3 class="dc-heading-4">🏥 بیمه‌های ثبت‌شده</h3></div>
                        <div style="padding:0;">
                            <?php foreach($companies as $c): ?>
                            <a href="<?php echo esc_url(add_query_arg('ins_id',$c['id'])); ?>"
                               style="display:block;padding:10px 16px;text-decoration:none;color:inherit;border-bottom:1px solid var(--dc-neutral-50);<?php echo $c['id']==$selected_id?'background:var(--dc-primary-light);':''; ?>">
                                <div style="font-weight:700;font-size:13px;"><?php echo esc_html($c['name']); ?></div>
                                <div style="font-size:11px;color:var(--dc-neutral-500);"><?php echo $c['type']==='base'?'پایه':'تکمیلی'; ?> — فرانشیز پیش‌فرض <?php echo (int)$c['default_franchise_percent']; ?>٪</div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div>
                    <?php if (!$selected_id): ?>
                    <div class="dc-card"><div class="dc-card-body" style="text-align:center;padding:30px;color:var(--dc-neutral-400);">اول یه بیمه اضافه/انتخاب کنید</div></div>
                    <?php else: ?>
                    <div class="dc-card" style="margin-bottom:16px;">
                        <div class="dc-card-header"><h3 class="dc-heading-4">➕ افزودن/ویرایش تعرفه‌ی یه خدمت</h3></div>
                        <form method="post">
                            <?php wp_nonce_field('dental_insurance'); ?>
                            <input type="hidden" name="tariff_insurance_id" value="<?php echo $selected_id; ?>">
                            <div class="dc-card-body" style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                                <div class="dc-form-group" style="margin:0;grid-column:1/-1;position:relative;">
                                    <label class="dc-label">خدمت</label>
                                    <input type="text" id="dc-ins-catalog-search" class="dc-input" placeholder="🔍 اسم خدمت رو تایپ کنید..." autocomplete="off" required>
                                    <input type="hidden" name="catalog_id" id="dc-ins-catalog-id" required>
                                    <div id="dc-ins-catalog-results" style="display:none;position:absolute;top:100%;right:0;left:0;background:#fff;border:1px solid var(--dc-neutral-200);border-radius:8px;max-height:260px;overflow-y:auto;z-index:20;box-shadow:0 6px 16px rgba(0,0,0,.12);"></div>
                                </div>
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">تعرفه‌ی مصوب (تومان)</label>
                                    <input type="number" name="approved_tariff" class="dc-input" required>
                                </div>
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">فرانشیز٪ (خالی = پیش‌فرض بیمه)</label>
                                    <input type="number" name="franchise_percent" class="dc-input" placeholder="مثلاً 20">
                                </div>
                                <div class="dc-form-group" style="margin:0;grid-column:1/-1;">
                                    <label class="dc-label">مدارک لازم برای این خدمت</label>
                                    <div style="display:flex;gap:14px;flex-wrap:wrap;font-size:12px;">
                                        <?php foreach($doc_labels as $k=>$l): ?>
                                        <label><input type="checkbox" name="docs[]" value="<?php echo $k; ?>"> <?php echo esc_html($l); ?></label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="dc-card-body" style="padding-top:0;">
                                <button type="submit" name="dental_save_tariff" class="dc-btn dc-btn-primary">ثبت تعرفه</button>
                            </div>
                        </form>
                    </div>

                    <div class="dc-card">
                        <div class="dc-card-header"><h3 class="dc-heading-4">📋 تعرفه‌های ثبت‌شده (<?php echo count($tariffs); ?>)</h3></div>
                        <div class="dc-table-wrap">
                            <table class="dc-table">
                                <thead><tr><th>خدمت</th><th>تعرفه مصوب</th><th>فرانشیز</th><th>مدارک لازم</th><th></th></tr></thead>
                                <tbody>
                                <?php if (empty($tariffs)): ?>
                                <tr><td colspan="5" style="text-align:center;padding:20px;color:var(--dc-neutral-400);">تعرفه‌ای ثبت نشده</td></tr>
                                <?php else: foreach($tariffs as $t): ?>
                                <tr>
                                    <td style="font-size:12px;"><?php echo esc_html($t['catalog_name']); ?></td>
                                    <td style="font-weight:700;"><?php echo number_format($t['approved_tariff']); ?> ت</td>
                                    <td><?php echo $t['franchise_percent']!==null ? (int)$t['franchise_percent'].'٪' : '(پیش‌فرض)'; ?></td>
                                    <td style="font-size:11px;color:var(--dc-neutral-500);"><?php echo esc_html(implode('، ', array_map(fn($d)=>$doc_labels[$d]??$d, array_filter(explode(',',$t['requires_docs']??''))))) ?: '—'; ?></td>
                                    <td><a href="<?php echo esc_url(add_query_arg('delete_tariff',$t['id'])); ?>" onclick="return confirm('حذف بشه؟');" style="color:var(--dc-danger);font-size:11px;">حذف</a></td>
                                </tr>
                                <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <script>
        if(typeof lucide!=="undefined")lucide.createIcons();
        (function(){
            var searchBox = document.getElementById('dc-ins-catalog-search');
            if (!searchBox) return;
            var hiddenId = document.getElementById('dc-ins-catalog-id');
            var resultsBox = document.getElementById('dc-ins-catalog-results');
            var timer;
            searchBox.addEventListener('input', function(){
                clearTimeout(timer);
                hiddenId.value = '';
                var q = this.value.trim();
                if (q.length < 2) { resultsBox.style.display = 'none'; return; }
                timer = setTimeout(function(){
                    var fd = new FormData();
                    fd.append('action','dental_catalog_search_pricing');
                    fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
                    fd.append('q', q);
                    fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                        .then(r=>r.json()).then(function(res){
                            if(!res.success || !res.data.items.length){
                                resultsBox.innerHTML = '<div style="padding:10px 14px;font-size:12px;color:#999;">چیزی یافت نشد</div>';
                                resultsBox.style.display = 'block';
                                return;
                            }
                            resultsBox.innerHTML = res.data.items.map(function(it){
                                return '<div class="dc-ins-cat-item" data-id="'+it.id+'" data-name="'+it.name.replace(/"/g,'&quot;')+'" '+
                                    'style="padding:9px 14px;cursor:pointer;border-bottom:1px solid #F5F5F5;font-size:13px;" onmouseover="this.style.background=\'#F8FAFB\'" onmouseout="this.style.background=\'\'">'+
                                    '<b>'+it.name+'</b><div style="font-size:10px;color:#A0B4C0;">📁 '+it.full_path+'</div></div>';
                            }).join('');
                            resultsBox.style.display = 'block';
                            resultsBox.querySelectorAll('.dc-ins-cat-item').forEach(function(el){
                                el.addEventListener('click', function(){
                                    hiddenId.value = this.dataset.id;
                                    searchBox.value = this.dataset.name;
                                    resultsBox.style.display = 'none';
                                });
                            });
                        });
                }, 300);
            });
            document.addEventListener('click', function(e){
                if (!resultsBox.contains(e.target) && e.target !== searchBox) resultsBox.style.display = 'none';
            });
        })();
        </script>
        <?php
    }
}
