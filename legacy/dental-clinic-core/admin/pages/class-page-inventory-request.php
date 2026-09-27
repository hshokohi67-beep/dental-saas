<?php
defined('ABSPATH') || exit;

class Dental_Page_Inventory_Request {

    public function render(): void {
        $cu = wp_get_current_user();
        $is_manager = current_user_can('manage_options') || in_array('dental_admin',(array)$cu->roles) || in_array('dental_financial',(array)$cu->roles);

        if ($is_manager && isset($_GET['export_csv']) && wp_verify_nonce($_GET['_wpnonce'] ?? '', 'dental_export_inv_requests')) {
            Dental_Inventory_Manager::export_requests_csv();
        }

        // ─── ثبت سبد — همه‌ی آیتم‌های انتخاب‌شده با یه دکمه ─────────
        if (isset($_POST['dental_submit_cart']) && check_admin_referer('dental_inv_request')) {
            $cart = json_decode(stripslashes($_POST['cart_data'] ?? '[]'), true) ?: [];
            $count = Dental_Inventory_Manager::create_requests_batch($cart, $cu->ID);
            echo '<div class="notice notice-success"><p>✅ ' . $count . ' مورد درخواست ثبت شد.</p></div>';
        }
        if ($is_manager && isset($_POST['dental_resolve_request']) && check_admin_referer('dental_inv_resolve')) {
            Dental_Inventory_Manager::resolve_request((int)$_POST['request_id'], sanitize_key($_POST['resolve_status']), $cu->ID);
            echo '<div class="notice notice-success"><p>✅ ثبت شد.</p></div>';
        }

        $categories = Dental_Inventory_Manager::get_categories();
        ?>
        <div class="dental-admin-wrap">
            <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&inv_view=hub')); ?>" style="font-size:12px;color:var(--dc-primary);text-decoration:none;display:inline-block;margin-bottom:10px;">← بازگشت به انبار</a>
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <i data-lucide="bell-plus" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                درخواست کالا
            </h1>

            <div class="dc-card" style="margin-bottom:24px;">
                <div class="dc-card-header"><h3 class="dc-heading-4">📢 کالاهایی که کم/تموم شده رو انتخاب کنید</h3></div>
                <div class="dc-card-body">
                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
                        <select id="dc-req-cat" class="dc-select" style="max-width:220px;">
                            <option value="0">همه دسته‌ها</option>
                            <?php foreach($categories as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo esc_html($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" id="dc-req-search" class="dc-input" placeholder="اسم کالا رو بنویسید و Enter بزنید (یا خالی بذارید و فقط دسته انتخاب کنید)" style="flex:1;min-width:220px;" autocomplete="off">
                        <button type="button" onclick="dcDoSearch()" class="dc-btn dc-btn-primary dc-btn-sm">جستجو</button>
                    </div>
                    <div id="dc-req-results" style="max-height:320px;overflow-y:auto;border:1px solid var(--dc-neutral-100);border-radius:8px;display:none;"></div>
                    <div id="dc-req-hint" style="font-size:12px;color:var(--dc-neutral-400);padding:10px 0;">یه دسته انتخاب کنید یا اسم کالا رو بنویسید تا نتایج نشون داده بشه.</div>
                </div>
            </div>

            <div class="dc-card" id="dc-cart-card" style="display:none;margin-bottom:24px;border:2px solid var(--dc-primary);">
                <div class="dc-card-header"><h3 class="dc-heading-4">🧺 کالاهای انتخاب‌شده (<span id="dc-cart-count">0</span>)</h3></div>
                <div class="dc-card-body">
                    <div id="dc-cart-list" style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;"></div>
                    <form method="post" onsubmit="return dcPrepareSubmit();">
                        <?php wp_nonce_field('dental_inv_request'); ?>
                        <input type="hidden" name="cart_data" id="dc-cart-data">
                        <button type="submit" name="dental_submit_cart" class="dc-btn dc-btn-primary">📢 ثبت نهایی همه (<span id="dc-cart-count2">0</span> مورد)</button>
                    </form>
                </div>
            </div>

            <?php
            // ─── درخواست‌های خودِ من — همه‌ی کاربران (نه فقط مدیر) اینو
            // می‌بینن، تا مطمئن بشن درخواستشون واقعاً ثبت شده ──────────
            $my_requests = Dental_Inventory_Manager::get_my_requests($cu->ID, 15);
            if (!empty($my_requests)):
            ?>
            <div class="dc-card" style="margin-bottom:24px;">
                <div class="dc-card-header"><h3 class="dc-heading-4">📋 درخواست‌های من</h3></div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>کالا</th><th>توضیح</th><th>وضعیت</th><th>تاریخ</th></tr></thead>
                        <tbody>
                        <?php foreach($my_requests as $r):
                            $st = ['pending'=>['در انتظار بررسی','var(--dc-accent-warm)'],'fulfilled'=>['✅ تأمین شد','var(--dc-accent-dark)'],'rejected'=>['رد شد','var(--dc-danger)']][$r['status']];
                        ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo esc_html($r['item_name']); ?></td>
                            <td style="font-size:12px;color:var(--dc-neutral-500);"><?php echo esc_html($r['note'] ?: '—'); ?></td>
                            <td><span style="color:<?php echo $st[1]; ?>;font-weight:700;font-size:12px;"><?php echo $st[0]; ?></span></td>
                            <td style="font-size:11px;color:var(--dc-neutral-400);"><?php echo esc_html(Dental_Jalali::to_jalali(date('Y-m-d',strtotime($r['created_at'])),'Y/m/d')); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($is_manager):
                $pending = Dental_Inventory_Manager::get_requests('pending');
                $resolved = Dental_Inventory_Manager::get_requests('all', 30);
            ?>
            <div style="background:var(--dc-primary-light);border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:12px;color:var(--dc-primary);">
                💡 هر درخواست دقیقاً مشخص می‌کنه <b>کی</b> چه <b>کالایی</b> رو با چه <b>تاریخی</b> خواسته — همه‌شون همین‌جا (بخش «درخواست‌های در انتظار» و «تاریخچه» پایین) قابل‌مشاهده و بررسی هستن.
            </div>
            <div class="dc-card" style="margin-bottom:20px;">
                <div class="dc-card-header">
                    <h3 class="dc-heading-4">⏳ درخواست‌های در انتظار (<?php echo count($pending); ?>)</h3>
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=dental-dashboard&inv_view=request&export_csv=1'),'dental_export_inv_requests')); ?>" class="dc-btn dc-btn-secondary dc-btn-sm">
                        <i data-lucide="file-down" style="width:13px;height:13px;"></i> خروجی اکسل (کل تاریخچه)
                    </a>
                </div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>کالا</th><th>درخواست‌دهنده</th><th>توضیح</th><th>تاریخ</th><th>اقدام</th></tr></thead>
                        <tbody>
                        <?php if (empty($pending)): ?>
                        <tr><td colspan="5" style="text-align:center;padding:20px;color:var(--dc-neutral-400);">درخواستی در انتظار نیست</td></tr>
                        <?php else: foreach($pending as $r): ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo esc_html($r['item_name']); ?></td>
                            <td style="font-size:12px;"><?php echo esc_html($r['requester_name']); ?></td>
                            <td style="font-size:12px;color:var(--dc-neutral-500);"><?php echo esc_html($r['note'] ?: '—'); ?></td>
                            <td style="font-size:11px;color:var(--dc-neutral-400);"><?php echo esc_html(Dental_Jalali::to_jalali(date('Y-m-d',strtotime($r['created_at'])),'Y/m/d')); ?></td>
                            <td>
                                <form method="post" style="display:flex;gap:6px;">
                                    <?php wp_nonce_field('dental_inv_resolve'); ?>
                                    <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                                    <button type="submit" name="dental_resolve_request" onclick="this.form.resolve_status.value='fulfilled'" class="dc-btn dc-btn-secondary dc-btn-sm">✅ تأمین شد</button>
                                    <button type="submit" name="dental_resolve_request" onclick="this.form.resolve_status.value='rejected'" class="dc-btn dc-btn-ghost dc-btn-sm">✕</button>
                                    <input type="hidden" name="resolve_status" value="">
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dc-card">
                <div class="dc-card-header"><h3 class="dc-heading-4">📋 تاریخچه درخواست‌ها</h3></div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>کالا</th><th>درخواست‌دهنده</th><th>وضعیت</th><th>تاریخ</th></tr></thead>
                        <tbody>
                        <?php foreach($resolved as $r):
                            $st_label = ['pending'=>['در انتظار','var(--dc-accent-warm)'],'fulfilled'=>['تأمین شد','var(--dc-accent-dark)'],'rejected'=>['رد شد','var(--dc-danger)']][$r['status']];
                        ?>
                        <tr>
                            <td><?php echo esc_html($r['item_name']); ?></td>
                            <td style="font-size:12px;"><?php echo esc_html($r['requester_name']); ?></td>
                            <td><span style="color:<?php echo $st_label[1]; ?>;font-weight:700;font-size:12px;"><?php echo $st_label[0]; ?></span></td>
                            <td style="font-size:11px;color:var(--dc-neutral-400);"><?php echo esc_html(Dental_Jalali::to_jalali(date('Y-m-d',strtotime($r['created_at'])),'Y/m/d')); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <script>
        var dcReqNonce = '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>';
        var dcReqAjax  = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        var dcCart = {};

        function dcDoSearch(){
            var q = document.getElementById('dc-req-search').value.trim();
            var cat = document.getElementById('dc-req-cat').value;
            if (!q && cat === '0') {
                document.getElementById('dc-req-hint').style.display = 'block';
                document.getElementById('dc-req-results').style.display = 'none';
                return;
            }
            var fd = new FormData();
            fd.append('action','dental_search_inventory_items_cat');
            fd.append('_wpnonce', dcReqNonce);
            fd.append('q', q);
            fd.append('cat', cat);
            fetch(dcReqAjax, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                var box = document.getElementById('dc-req-results');
                document.getElementById('dc-req-hint').style.display = 'none';
                if(!res.success || !res.data.items.length){
                    box.innerHTML = '<div style="padding:14px;text-align:center;font-size:12px;color:#999;">چیزی یافت نشد</div>';
                    box.style.display = 'block';
                    return;
                }
                box.innerHTML = res.data.items.map(function(it){
                    var inCart = dcCart[it.id];
                    return '<div style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-bottom:1px solid #F5F5F5;">'+
                        '<div style="flex:1;font-size:13px;"><b>'+it.name+'</b><span style="color:#999;font-size:11px;"> — موجودی: '+it.stock+' '+it.unit+'</span></div>'+
                        '<input type="text" id="dc-qty-'+it.id+'" placeholder="تعداد (اختیاری)" style="width:110px;padding:6px 8px;border:1px solid #ddd;border-radius:6px;font-size:12px;font-family:inherit;" value="'+(inCart?inCart.qty:'')+'">'+
                        '<button type="button" onclick="dcAddToCart('+it.id+',\'' + it.name.replace(/'/g,"\\'") + '\',\'' + it.unit + '\',\'' + it.stock + '\')" class="dc-btn '+(inCart?'dc-btn-secondary':'dc-btn-primary')+' dc-btn-sm">'+(inCart?'✓ اضافه‌شده':'+ افزودن')+'</button>'+
                        '</div>';
                }).join('');
                box.style.display = 'block';
            });
        }
        document.getElementById('dc-req-search').addEventListener('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); dcDoSearch(); } });
        document.getElementById('dc-req-cat').addEventListener('change', dcDoSearch);

        function dcAddToCart(id, name, unit, stock){
            var qtyInput = document.getElementById('dc-qty-'+id);
            dcCart[id] = { name: name, unit: unit, stock: stock, qty: qtyInput.value.trim() };
            dcRenderCart();
            dcDoSearch();
        }
        function dcRemoveFromCart(id){
            delete dcCart[id];
            dcRenderCart();
        }
        function dcRenderCart(){
            var ids = Object.keys(dcCart);
            var card = document.getElementById('dc-cart-card');
            if (ids.length === 0) { card.style.display = 'none'; return; }
            card.style.display = 'block';
            document.getElementById('dc-cart-count').textContent = ids.length;
            document.getElementById('dc-cart-count2').textContent = ids.length;
            document.getElementById('dc-cart-list').innerHTML = ids.map(function(id){
                var it = dcCart[id];
                return '<div style="display:flex;align-items:center;gap:10px;background:var(--dc-neutral-50);border-radius:8px;padding:8px 12px;">'+
                    '<div style="flex:1;font-size:13px;">'+it.name+(it.qty?' — <b>'+it.qty+' '+it.unit+'</b>':'')+'</div>'+
                    '<span onclick="dcRemoveFromCart('+id+')" style="cursor:pointer;color:var(--dc-danger);font-size:12px;">✕ حذف</span>'+
                    '</div>';
            }).join('');
        }
        function dcPrepareSubmit(){
            var arr = Object.keys(dcCart).map(function(id){ return { item_id: id, qty: dcCart[id].qty }; });
            document.getElementById('dc-cart-data').value = JSON.stringify(arr);
            return true;
        }
        if(typeof lucide!=="undefined")lucide.createIcons();
        </script>
        <?php
    }
}
