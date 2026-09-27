<?php
defined('ABSPATH') || exit;

class Dental_Page_Catalog_Pricing {

    public function render(): void {
        // ─── وارد کردن اولیه کاتالوگ (فقط یک‌بار) ────────────────────
        if (isset($_POST['dental_import_catalog']) && check_admin_referer('dental_catalog_pricing')) {
            $n = Dental_Service_Catalog::import_seed();
            echo '<div class="notice notice-success"><p>✅ ' . ($n>0 ? "{$n} خدمت وارد شد." : 'قبلاً وارد شده بود، دوباره وارد نشد.') . '</p></div>';
        }
        if (isset($_POST['dental_reset_catalog']) && check_admin_referer('dental_catalog_pricing')) {
            Dental_Service_Catalog::reset_catalog();
            echo '<div class="notice notice-warning"><p>⚠️ کاتالوگ پاک شد. می‌توانید دوباره وارد کنید.</p></div>';
        }
        // ─── ورود یه دسته‌ی جدید خاص (که بعداً به seed اضافه شده) —
        // برای وقتی کل کاتالوگ قبلاً وارد شده و import_seed دیگه کاری نمی‌کنه ──
        if (isset($_POST['dental_import_category']) && check_admin_referer('dental_catalog_pricing')) {
            $result = Dental_Service_Catalog::import_category_by_name(sanitize_text_field($_POST['category_name']));
            echo $result['success']
                ? '<div class="notice notice-success"><p>✅ دسته «' . esc_html($_POST['category_name']) . '» با ' . $result['count'] . ' آیتم اضافه شد.</p></div>'
                : '<div class="notice notice-warning"><p>⚠️ ' . esc_html($result['message']) . '</p></div>';
        }

        $imported = (bool)get_option('dental_catalog_imported');
        $total_leaves = (int)$this->count_leaves();
        $has_beauty = (bool)$GLOBALS['wpdb']->get_var($GLOBALS['wpdb']->prepare(
            "SELECT id FROM {$GLOBALS['wpdb']->prefix}dental_service_catalog WHERE parent_id IS NULL AND name=%s", 'زیبایی'
        ));
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                <i data-lucide="list-tree" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                کاتالوگ خدمات و قیمت‌گذاری
            </h1>

            <?php if ($imported && !$has_beauty): ?>
            <div class="dc-card" style="margin-bottom:20px;border-color:#8B5CF6;">
                <div class="dc-card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                    <div>
                        <div style="font-weight:700;font-size:13px;">✨ دسته‌ی جدید «زیبایی» آماده‌ی افزودنه</div>
                        <div style="font-size:12px;color:var(--dc-neutral-500);">یه دسته‌ی کاملاً مجزا (طراحی لبخند، بلیچینگ، ونیر و...) — به دسته‌های موجود شما دست نمی‌زنه.</div>
                    </div>
                    <form method="post"><?php wp_nonce_field('dental_catalog_pricing'); ?>
                        <input type="hidden" name="category_name" value="زیبایی">
                        <button type="submit" name="dental_import_category" class="dc-btn dc-btn-primary dc-btn-sm">➕ افزودن دسته «زیبایی»</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!$imported): ?>
            <div class="dc-card" style="margin-bottom:20px;border-color:var(--dc-accent-warm);">
                <div class="dc-card-body" style="text-align:center;padding:30px;">
                    <div style="font-size:36px;margin-bottom:10px;">📥</div>
                    <p style="margin-bottom:16px;">کاتالوگ خدمات هنوز وارد نشده. برای شروع، یک‌بار وارد کنید.</p>
                    <form method="post">
                        <?php wp_nonce_field('dental_catalog_pricing'); ?>
                        <button type="submit" name="dental_import_catalog" class="dc-btn dc-btn-primary">📥 وارد کردن کاتالوگ کامل</button>
                    </form>
                </div>
            </div>
            <?php else: ?>

            <div class="dc-card" style="margin-bottom:20px;">
                <div class="dc-card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;">
                    <div style="display:flex;gap:20px;">
                        <div style="text-align:center;">
                            <div style="font-size:22px;font-weight:700;color:var(--dc-primary);"><?php echo number_format($total_leaves); ?></div>
                            <div style="font-size:11px;color:var(--dc-neutral-500);">کل خدمات قابل انتخاب</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-size:22px;font-weight:700;color:var(--dc-accent-dark);"><?php echo number_format($this->count_priced()); ?></div>
                            <div style="font-size:11px;color:var(--dc-neutral-500);">قیمت‌گذاری‌شده</div>
                        </div>
                    </div>
                    <form method="post" onsubmit="return confirm('کل کاتالوگ و قیمت‌ها پاک می‌شود. مطمئنید؟');">
                        <?php wp_nonce_field('dental_catalog_pricing'); ?>
                        <button type="submit" name="dental_reset_catalog" class="dc-btn dc-btn-ghost dc-btn-sm" style="color:var(--dc-danger);">🗑️ پاک‌کردن و وارد کردن دوباره</button>
                    </form>
                </div>
            </div>

            <div class="dc-card">
                <div class="dc-card-header">
                    <h3 class="dc-heading-4">💰 قیمت‌گذاری خدمات</h3>
                    <div style="display:flex;gap:8px;">
                        <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(['export_csv'=>1]),'dental_export_catalog')); ?>" class="dc-btn dc-btn-secondary dc-btn-sm">
                            <i data-lucide="file-down" style="width:13px;height:13px;"></i> خروجی اکسل
                        </a>
                    </div>
                </div>
                <div class="dc-card-body" style="padding:16px;">
                    <div style="display:flex;gap:10px;align-items:center;margin-bottom:14px;">
                        <input type="text" id="dc-price-search" class="dc-input" placeholder="جستجو (مسیر کامل هم نشون داده می‌شه)..." style="flex:1;">
                        <button type="button" id="dc-price-search-clear" class="dc-btn dc-btn-ghost dc-btn-sm" style="display:none;">✕ پاک‌کردن جستجو</button>
                    </div>
                    <!-- مسیر فعلی — برای اینکه همیشه معلوم باشه دقیقاً کجای درخت هستید -->
                    <div id="dc-price-breadcrumb" style="font-size:12px;color:var(--dc-neutral-500);margin-bottom:10px;padding:8px 12px;background:var(--dc-neutral-50);border-radius:6px;">
                        📍 دندانپزشکی
                    </div>
                    <button id="dc-price-back" onclick="dcPriceGoBack()" class="dc-btn dc-btn-ghost dc-btn-sm" style="display:none;margin-bottom:10px;">← بازگشت</button>
                </div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>خدمت / دسته</th><th style="width:150px;">قیمت کل (تومان)</th><th style="width:150px;">سهم بیمار (تومان)</th><th style="width:150px;">محدوده دندان</th><th style="width:90px;">رضایت‌نامه</th><th style="width:80px;">وضعیت</th></tr></thead>
                        <tbody id="dc-pricing-rows">
                            <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--dc-neutral-400);">در حال بارگذاری...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <script>
        var dcCatalogNonce = '<?php echo esc_js(wp_create_nonce("dental_catalog_price")); ?>';
        var dcPriceAjax    = '<?php echo esc_js(admin_url("admin-ajax.php")); ?>';
        var dcPriceWpNonce = '<?php echo esc_js(wp_create_nonce("dental_workspace")); ?>';
        var dcPriceStack = [];

        // ─── مرور زیرشاخه‌ای — همیشه مشخصه کدوم شاخه‌ایم، ابهامی نیست ──
        var DC_FILTER_OPTIONS = [
            ['all','کل دندان‌ها (نیاز به انتخاب یک دندان دلخواه)'],
            ['whole_mouth','کل دهان (بدون نیاز به انتخاب دندان)'],
            ['anterior','فقط قدامی (۱-۳)'],
            ['posterior','فقط خلفی (۳-۸)'],
            ['premolar','فقط پره‌مولر (۴-۵)'],
            ['molar','فقط مولر (۶-۸)'],
            ['upper_jaw','کل فک بالا'],
            ['lower_jaw','کل فک پایین'],
            ['half_ur','نیم‌فک بالا راست'],
            ['half_ul','نیم‌فک بالا چپ'],
            ['half_ll','نیم‌فک پایین چپ'],
            ['half_lr','نیم‌فک پایین راست'],
        ];
        function dcBuildFilterSelect(id, current){
            var html = '<select class="dc-select dc-filter-select" data-id="'+id+'" style="font-size:11px;height:32px;">';
            DC_FILTER_OPTIONS.forEach(function(opt){
                html += '<option value="'+opt[0]+'"'+(opt[0]===current?' selected':'')+'>'+opt[1]+'</option>';
            });
            html += '</select>';
            return html;
        }
        function dcBuildPriceRow(c){
            return '<tr data-id="'+c.id+'">'+
                '<td style="font-size:12px;">'+(c.full_path ? '<span style="color:#5A7080;font-size:11px;">'+c.full_path+'</span>' : '✓ '+c.name)+'</td>'+
                '<td><input type="number" class="dc-input dc-price-input" data-id="'+c.id+'" data-field="price" value="'+(c.price||0)+'" style="width:130px;" dir="ltr"></td>'+
                '<td><input type="number" class="dc-input dc-price-input" data-id="'+c.id+'" data-field="patient_share" value="'+(c.patient_share||'')+'" placeholder="= کل مبلغ" style="width:130px;" dir="ltr"></td>'+
                '<td>'+dcBuildFilterSelect(c.id, c.tooth_filter||'all')+'</td>'+
                '<td style="text-align:center;"><input type="checkbox" class="dc-consent-checkbox" data-id="'+c.id+'" '+(c.requires_consent==1?'checked':'')+' style="width:18px;height:18px;accent-color:var(--dc-danger);"></td>'+
                '<td><span class="dc-save-status" data-id="'+c.id+'" style="font-size:11px;color:#A0B4C0;">'+(c.price>0?'✅':'—')+'</span></td>'+
            '</tr>';
        }
        function dcPriceBrowse(parentId, label){
            var tbody = document.getElementById('dc-pricing-rows');
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:#A0B4C0;">در حال بارگذاری...</td></tr>';
            document.getElementById('dc-price-back').style.display = dcPriceStack.length > 0 ? 'inline-block' : 'none';

            fetch(dcPriceAjax + '?action=dental_catalog_browse&parent_id=' + (parentId===null?'':parentId) + '&_wpnonce=' + dcPriceWpNonce)
            .then(r=>r.json()).then(function(res){
                if(!res.success) return;
                var crumbs = (res.data.path||[]).map(function(p){return p.name;});
                document.getElementById('dc-price-breadcrumb').textContent = '📍 دندانپزشکی' + (crumbs.length ? ' > ' + crumbs.join(' > ') : '');
                dcRenderPriceRows(res.data.children);
            });
            dcPriceStack.push({id: parentId, name: label});
        }
        function dcPriceGoBack(){
            dcPriceStack.pop();
            var prev = dcPriceStack.pop();
            dcPriceBrowse(prev ? prev.id : null, prev ? prev.name : 'دندانپزشکی');
        }
        function dcRenderPriceRows(items){
            var tbody = document.getElementById('dc-pricing-rows');
            if(!items.length){ tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:#A0B4C0;">زیرمجموعه‌ای ندارد</td></tr>'; return; }
            tbody.innerHTML = items.map(function(c){
                if(!c.is_leaf){
                    return '<tr style="cursor:pointer;" onclick="dcPriceBrowse('+c.id+',\''+c.name.replace(/'/g,"\\'")+'\')" onmouseover="this.style.background=\'#F0F6F9\'" onmouseout="this.style.background=\'\'">'+
                        '<td colspan="6" style="font-size:13px;font-weight:600;">📁 '+c.name+' ›</td></tr>';
                }
                return dcBuildPriceRow(c);
            }).join('');
            dcBindPriceInputs();
            // ─── اگه از سرچ اومدیم اینجا، ردیف موردنظر رو هایلایت و اسکرول کن ──
            if (dcPriceHighlightId) {
                var targetRow = document.querySelector('tr[data-id="'+dcPriceHighlightId+'"]');
                if (targetRow) {
                    targetRow.style.transition = 'background .3s';
                    targetRow.style.background = '#FFF3CD';
                    targetRow.scrollIntoView({behavior:'smooth', block:'center'});
                    setTimeout(function(){ targetRow.style.background = ''; }, 2500);
                }
                dcPriceHighlightId = null;
            }
        }
        function dcBindPriceInputs(){
            document.querySelectorAll('.dc-price-input').forEach(function(inp){
                var timer;
                inp.addEventListener('input', function(){
                    clearTimeout(timer);
                    var id = this.dataset.id, statusEl = document.querySelector('.dc-save-status[data-id="'+id+'"]');
                    statusEl.textContent = '⏳';
                    timer = setTimeout(function(){
                        var row = document.querySelector('tr[data-id="'+id+'"]');
                        var priceInput = row.querySelector('[data-field="price"]');
                        var shareInput = row.querySelector('[data-field="patient_share"]');
                        var fd = new FormData();
                        fd.append('action','dental_save_catalog_price');
                        fd.append('_wpnonce', dcCatalogNonce);
                        fd.append('catalog_id', id);
                        fd.append('price', priceInput.value);
                        fd.append('patient_share', shareInput.value);
                        fetch(ajaxurl, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                            statusEl.textContent = res.success ? '✅' : '❌';
                        });
                    }, 600);
                });
            });
            document.querySelectorAll('.dc-consent-checkbox').forEach(function(cb){
                cb.addEventListener('change', function(){
                    var id = this.dataset.id;
                    var fd = new FormData();
                    fd.append('action','dental_save_catalog_consent');
                    fd.append('_wpnonce', dcCatalogNonce);
                    fd.append('catalog_id', id);
                    fd.append('requires', this.checked?1:0);
                    fetch(ajaxurl, {method:'POST', body:fd});
                });
            });
            document.querySelectorAll('.dc-filter-select').forEach(function(sel){
                sel.addEventListener('change', function(){
                    var id = this.dataset.id;
                    var fd = new FormData();
                    fd.append('action','dental_save_catalog_filter');
                    fd.append('_wpnonce', dcCatalogNonce);
                    fd.append('catalog_id', id);
                    fd.append('tooth_filter', this.value);
                    fetch(ajaxurl, {method:'POST', body:fd});
                });
            });
        }

        // ─── جستجو — با مسیر کامل، تا ابهام اطفال/بزرگسال/قدامی/خلفی نداشته باشه ──
        var dcPriceSearchTimer;
        document.getElementById('dc-price-search').addEventListener('input', function(){
            clearTimeout(dcPriceSearchTimer);
            var q = this.value.trim();
            document.getElementById('dc-price-search-clear').style.display = q ? 'inline-block' : 'none';
            if(!q){ dcPriceBrowse(null, 'دندانپزشکی'); dcPriceStack = []; return; }
            dcPriceSearchTimer = setTimeout(function(){
                var fd = new FormData();
                fd.append('action','dental_catalog_search_pricing');
                fd.append('_wpnonce', dcPriceWpNonce);
                fd.append('q', q);
                fetch(dcPriceAjax, {method:'POST', body:fd})
                .then(r=>r.json()).then(function(res){
                    if(!res.success) return;
                    document.getElementById('dc-price-breadcrumb').textContent = '🔍 نتایج جستجو برای: ' + q + ' — روی هرکدوم بزنید تا شاخه‌ش باز بشه';
                    // برخلاف قبل، دکمه‌ی بازگشت رو مخفی نمی‌کنیم — فقط
                    // رفتارش رو موقتاً عوض می‌کنیم تا از حالت سرچ خارج بشه
                    var backBtn = document.getElementById('dc-price-back');
                    backBtn.style.display = 'inline-block';
                    backBtn.onclick = function(){
                        document.getElementById('dc-price-search').value = '';
                        document.getElementById('dc-price-search-clear').style.display = 'none';
                        backBtn.onclick = dcPriceGoBack; // برگردوندن رفتار عادی
                        dcPriceBrowse(dcPriceStack.length ? dcPriceStack[dcPriceStack.length-1].id : null, dcPriceStack.length ? dcPriceStack[dcPriceStack.length-1].name : 'دندانپزشکی');
                    };
                    var tbody = document.getElementById('dc-pricing-rows');
                    var items = res.data.items;
                    if(!items.length){ tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:#A0B4C0;">چیزی یافت نشد</td></tr>'; return; }
                    // ─── به‌جای نمایش تخت، هر نتیجه قابل‌کلیکه — با زدنش
                    // مستقیم می‌ره تو همون شاخه‌ی واقعی (باز شدن شاخه) و
                    // خودِ آیتم رو هایلایت می‌کنه — دقیقاً چیزی که خواسته شده ──
                    tbody.innerHTML = items.map(function(it){
                        return '<tr style="cursor:pointer;" onclick="dcJumpToResult('+it.id+','+(it.parent_id||'null')+',\''+it.full_path.replace(/'/g,"\\'")+'\')" onmouseover="this.style.background=\'#F0F6F9\'" onmouseout="this.style.background=\'\'">'+
                            '<td colspan="6" style="font-size:13px;">'+
                            '<b>'+it.name+'</b><br><span style="font-size:11px;color:#A0B4C0;">📁 '+it.full_path+'</span>'+
                            '</td></tr>';
                    }).join('');
                });
            }, 400);
        });
        // ─── با کلیک روی نتیجه‌ی سرچ، وارد شاخه‌ی واقعی اون آیتم می‌شیم
        // (باز شدن شاخه) و خودِ ردیف رو هایلایت می‌کنیم تا سریع پیدا بشه ──
        function dcJumpToResult(itemId, parentId, fullPath){
            document.getElementById('dc-price-search').value = '';
            document.getElementById('dc-price-search-clear').style.display = 'none';
            dcPriceStack = [];
            var pathParts = fullPath.split(' > ');
            var parentLabel = pathParts.length > 1 ? pathParts[pathParts.length-2] : 'دندانپزشکی';
            dcPriceHighlightId = itemId;
            dcPriceBrowse(parentId, parentLabel);
        }
        var dcPriceHighlightId = null;
        document.getElementById('dc-price-search-clear').addEventListener('click', function(){
            document.getElementById('dc-price-search').value = '';
            this.style.display = 'none';
            dcPriceStack = [];
            dcPriceBrowse(null, 'دندانپزشکی');
        });

        // شروع از ریشه
        dcPriceBrowse(null, 'دندانپزشکی');
        if(typeof lucide!=="undefined")lucide.createIcons();
        </script>
        <?php
    }

    private function count_leaves(): int {
        global $wpdb;
        return (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dental_service_catalog WHERE is_leaf=1 AND is_active=1");
    }
    private function count_priced(): int {
        global $wpdb;
        return (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dental_service_pricing WHERE price > 0");
    }

    // ─── خروجی CSV کامل کاتالوگ + قیمت‌ها ────────────────────────
    public function export_csv(): void {
        $leaves = Dental_Service_Catalog::get_all_leaves_flat();

        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=catalog-khadamat.csv');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['مسیر کامل خدمت','قیمت کل (تومان)','سهم بیمار (تومان)']);
        foreach ($leaves as $lf) {
            fputcsv($out, [
                $lf['full_path'] ?? $lf['name'],
                $lf['price'] ?? 0,
                $lf['patient_share'] ?? '',
            ]);
        }
        fclose($out);
    }
}
