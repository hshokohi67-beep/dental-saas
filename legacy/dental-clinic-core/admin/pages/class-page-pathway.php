<?php
defined('ABSPATH') || exit;

class Dental_Page_Pathway {

    private int $patient_id;

    public function __construct(int $patient_id) {
        $this->patient_id = $patient_id;
    }

    public function render(): void {
        $templates = Dental_Pathway_Manager::get_templates(true);
        $pathways  = Dental_Pathway_Manager::get_patient_pathways($this->patient_id);
        $status_labels = Dental_Pathway_Manager::get_status_labels();
        $pathway_status_labels = Dental_Pathway_Manager::get_pathway_status_labels();
        ?>
        <div style="margin-bottom:16px;">
            <button type="button" class="dc-btn dc-btn-primary dc-btn-sm" onclick="dcOpenPathwayModal()">➕ شروع مسیر درمان جدید</button>
        </div>

        <?php if (empty($pathways)): ?>
        <p style="text-align:center;color:var(--dc-neutral-400);padding:30px;">هیچ مسیر درمانی برای این بیمار ثبت نشده.</p>
        <?php else: foreach($pathways as $pw): ?>
        <div class="dc-card" style="margin-bottom:16px;">
            <div class="dc-card-header">
                <h3 class="dc-heading-4" style="font-size:13px;">
                    <?php echo $pw['tooth_number'] ? '🦷 دندان '.esc_html(Dental_Service_Catalog::describe_tooth_number((int)$pw['tooth_number'])).' — ' : ''; ?>
                    <?php echo esc_html($pw['title']); ?>
                </h3>
                <span style="font-size:11px;color:var(--dc-neutral-500);">
                    <?php echo esc_html($pathway_status_labels[$pw['status']] ?? $pw['status']); ?> —
                    <?php echo $pw['remaining'] > 0 ? $pw['remaining'].' مرحله باقی‌مانده' : 'کامل شده'; ?>
                </span>
            </div>
            <div style="padding:16px;">
                <?php foreach($pw['steps'] as $idx => $step):
                    $sl = $status_labels[$step['status']] ?? ['—','#999'];
                    $blocked = Dental_Pathway_Manager::is_step_blocked($step);
                ?>
                <div style="display:flex;align-items:flex-start;gap:10px;padding-right:14px;margin-right:9px;<?php echo $idx < count($pw['steps'])-1 ? 'border-right:2px solid #EEF2F5;' : ''; ?>">
                    <div style="width:18px;height:18px;border-radius:50%;background:<?php echo $sl[1]; ?>;margin-right:-24px;flex-shrink:0;margin-top:2px;box-shadow:0 0 0 3px #fff;"></div>
                    <div style="flex:1;padding:8px 0;">
                        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
                            <b style="font-size:13px;"><?php echo esc_html($step['title']); ?></b>
                            <span style="font-size:11px;color:<?php echo $sl[1]; ?>;font-weight:700;"><?php echo esc_html($sl[0]); ?><?php echo $blocked && $step['status']!=='completed' ? ' 🔒' : ''; ?></span>
                        </div>
                        <?php if ($step['scheduled_date']): ?><div style="font-size:11px;color:var(--dc-neutral-500);">📅 <?php echo esc_html(Dental_Jalali::to_jalali($step['scheduled_date'],'Y/m/d')); ?></div><?php endif; ?>
                        <?php if ($step['completed_date']): ?><div style="font-size:11px;color:var(--dc-accent-dark);">✅ انجام‌شده در <?php echo esc_html(Dental_Jalali::to_jalali($step['completed_date'],'Y/m/d')); ?></div><?php endif; ?>
                        <?php if ($step['notes']): ?><div style="font-size:11px;color:var(--dc-neutral-400);"><?php echo esc_html($step['notes']); ?></div><?php endif; ?>

                        <?php if (!in_array($step['status'], ['completed','skipped','cancelled']) && $pw['status'] === 'active'): ?>
                        <div style="display:flex;gap:6px;margin-top:6px;align-items:center;flex-wrap:wrap;">
                            <button type="button" class="dc-btn dc-btn-secondary dc-btn-sm" style="padding:3px 10px;font-size:11px;"
                                onclick="dcCompletePathwayStep(<?php echo (int)$step['id']; ?>, <?php echo (int)($step['catalog_id'] ?? 0); ?>, '<?php echo esc_js($step['title']); ?>')">✅ تکمیل</button>
                            <button type="button" class="dc-btn dc-btn-ghost dc-btn-sm" style="padding:3px 10px;font-size:11px;"
                                onclick="dcUpdatePathwayStep(<?php echo (int)$step['id']; ?>,'skipped')">⏭️ رد کردن</button>
                            <?php if (empty($step['catalog_id'])): ?>
                            <span style="font-size:10px;color:var(--dc-neutral-400);">💡 موقع تکمیل، خدمت مرتبط از کاتالوگ انتخاب می‌شه</span>
                            <?php endif; ?>
                        </div>
                        <?php elseif (in_array($step['status'], ['skipped','cancelled']) && $pw['status'] === 'active'): ?>
                        <div style="margin-top:6px;">
                            <button type="button" class="dc-btn dc-btn-ghost dc-btn-sm" style="padding:3px 10px;font-size:11px;color:var(--dc-primary);"
                                onclick="dcUpdatePathwayStep(<?php echo (int)$step['id']; ?>,'pending')">↩️ بازگردانی (قابل‌اصلاح)</button>
                        </div>
                        <?php elseif ($step['status'] === 'completed' && $pw['status'] === 'active'): ?>
                        <div style="margin-top:6px;">
                            <button type="button" class="dc-btn dc-btn-ghost dc-btn-sm" style="padding:3px 10px;font-size:11px;color:var(--dc-neutral-400);"
                                onclick="if(confirm('این مرحله از حالت «تکمیل‌شده» به «در انتظار» برگرده؟')) dcUpdatePathwayStep(<?php echo (int)$step['id']; ?>,'pending')">↩️ برگردوندن از تکمیل‌شده</button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; endif; ?>

        <!-- مودال شروع مسیر جدید -->
        <div id="dc-pathway-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.8);z-index:999999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:16px;padding:22px;width:100%;max-width:460px;">
                <div style="font-weight:700;font-size:14px;margin-bottom:14px;">➕ شروع مسیر درمان جدید</div>
                <div class="dc-form-group" style="margin-bottom:14px;">
                    <label class="dc-label">قالب مسیر</label>
                    <select id="dc-pathway-template" class="dc-select">
                        <option value="">— انتخاب کنید —</option>
                        <?php foreach($templates as $t): ?>
                        <option value="<?php echo $t['id']; ?>"><?php echo esc_html($t['name']); ?> (<?php echo count($t['steps']); ?> مرحله)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="dc-form-group" style="margin-bottom:14px;">
                    <label class="dc-label">دندان مرتبط (اختیاری)</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <select id="dc-pathway-quad" class="dc-select">
                            <option value="">— بدون دندان خاص —</option>
                            <option value="1">بالا راست</option>
                            <option value="2">بالا چپ</option>
                            <option value="3">پایین چپ</option>
                            <option value="4">پایین راست</option>
                        </select>
                        <select id="dc-pathway-pos" class="dc-select">
                            <option value="">شماره (۱ تا ۸)</option>
                            <?php for($i=1;$i<=8;$i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="button" onclick="dcCreatePathway()" class="dc-btn dc-btn-primary" style="flex:1;">✅ شروع مسیر</button>
                    <button type="button" onclick="document.getElementById('dc-pathway-modal').style.display='none'" class="dc-btn dc-btn-ghost">انصراف</button>
                </div>
                <p style="font-size:11px;color:var(--dc-neutral-400);margin-top:10px;">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dental-settings&tab=pathway')); ?>" target="_blank">مدیریت/ساخت قالب‌های جدید ←</a>
                </p>
            </div>
        </div>

        <script>
        function dcOpenPathwayModal(){ document.getElementById('dc-pathway-modal').style.display = 'flex'; }
        function dcCreatePathway(){
            var templateId = document.getElementById('dc-pathway-template').value;
            if (!templateId) { alert('یه قالب انتخاب کنید.'); return; }
            var fd = new FormData();
            fd.append('action','dental_create_pathway');
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
            fd.append('patient_id', <?php echo (int)$this->patient_id; ?>);
            fd.append('template_id', templateId);
            // ─── ترکیب کوادرانت+موقعیت به همون فرمت کدگذاری فعلی
            // پلاگین (کوادرانت×۱۰+موقعیت) — دقیقاً هماهنگ با چارت خودمون
            var quad = document.getElementById('dc-pathway-quad').value;
            var pos  = document.getElementById('dc-pathway-pos').value;
            var toothNumber = (quad && pos) ? (parseInt(quad)*10 + parseInt(pos)) : '';
            fd.append('tooth_number', toothNumber);
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                .then(r=>r.json()).then(function(res){
                    if (res.success) location.reload();
                    else alert('خطا: ' + (res.data && res.data.message ? res.data.message : 'ناموفق'));
                });
        }
        function dcUpdatePathwayStep(stepId, status, override){
            var fd = new FormData();
            fd.append('action','dental_update_pathway_step');
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
            fd.append('step_id', stepId);
            fd.append('status', status);
            if (override) fd.append('override', '1');
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                .then(r=>r.json()).then(function(res){
                    if (res.success) { location.reload(); return; }
                    if (res.data && res.data.blocked) {
                        if (confirm('⚠️ ' + res.data.message + '\n\nمی‌خواید با وجود این، Override کنید؟ (این در لاگ فعالیت ثبت می‌شه)')) {
                            dcUpdatePathwayStep(stepId, status, true);
                        }
                    } else {
                        alert('خطا: ' + (res.data && res.data.message ? res.data.message : 'ناموفق'));
                    }
                });
        }
        function dcCompletePathwayStep(stepId, catalogId, stepTitle){
            if (catalogId && catalogId > 0) {
                // ─── قبلاً به یه خدمت کاتالوگ وصل شده — مستقیم با همون
                // خدمت تکمیل و خودکار توی دفتر روزانه ثبت می‌شه ────────
                dcSendCompleteWithCatalog(stepId, catalogId);
            } else {
                // ─── هنوز به کاتالوگ وصل نیست — مینی‌مودال باز می‌شه و
                // خودکار با اسم خودِ مرحله سرچ می‌کنه (دم‌دست‌تر — بدون
                // نیاز به تایپ دستی) ────────────────────────────────
                dcOpenPathwayCatalogPicker(stepId, stepTitle);
            }
        }
        function dcSendCompleteWithCatalog(stepId, catalogId){
            var fd = new FormData();
            fd.append('action','dental_complete_pathway_step_catalog');
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
            fd.append('step_id', stepId);
            fd.append('catalog_id', catalogId);
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                .then(r=>r.json()).then(function(res){
                    if (res.success) { location.reload(); return; }
                    alert('خطا: ' + (res.data && res.data.message ? res.data.message : 'ناموفق'));
                });
        }

        // ─── مینی‌مودال سرچ کاتالوگ — دقیقاً همون AJAX سرچ زنده‌ای که
        // جای دیگه‌ی پلاگین استفاده می‌شه، سیستم موازی نساختیم. برای
        // دم‌دست‌تر شدن (طبق بازخورد)، موقع باز شدن خودکار با اسم خودِ
        // مرحله سرچ می‌زنه — دیگه لازم نیست دستی تایپ کنید ─────────────
        var dcPathwayCompletingStepId = null;
        function dcOpenPathwayCatalogPicker(stepId, stepTitle){
            dcPathwayCompletingStepId = stepId;
            document.getElementById('dc-pathway-catalog-modal').style.display = 'flex';
            // پاک‌سازی اختصارات انگلیسی داخل پرانتز — مثلاً «عصب‌کشی
            // (RCT)» می‌شه «عصب‌کشی»، چون کاتالوگ معمولاً فارسی خالصه
            var cleanTitle = (stepTitle || '').replace(/\s*\([^)]*\)\s*/g, '').trim();
            var box = document.getElementById('dc-pathway-catalog-search');
            box.value = cleanTitle;
            document.getElementById('dc-pathway-catalog-results').innerHTML = '<div style="padding:8px;font-size:12px;color:#999;">در حال جستجو...</div>';
            if (cleanTitle) dcTriggerPathwayCatalogSearch(cleanTitle);
            else document.getElementById('dc-pathway-catalog-results').innerHTML = '';
            setTimeout(function(){ box.focus(); box.select(); }, 100);
        }
        function dcTriggerPathwayCatalogSearch(q){
            var results = document.getElementById('dc-pathway-catalog-results');
            if (q.length < 2) { results.innerHTML = ''; return; }
            var fd = new FormData();
            fd.append('action','dental_catalog_search_pricing');
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
            fd.append('q', q);
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                .then(r=>r.json()).then(function(res){
                    if (!res.success || !res.data.items.length) {
                        results.innerHTML = '<div style="padding:10px;font-size:12px;color:#999;">چیزی پیدا نشد — یه اسم دیگه امتحان کنید (مثلاً کوتاه‌تر)</div>';
                        return;
                    }
                    results.innerHTML = res.data.items.map(function(it){
                        return '<div class="dc-pw-cat-opt" data-id="'+it.id+'" style="padding:10px 12px;cursor:pointer;border-bottom:1px solid #f5f5f5;font-size:13px;border-radius:6px;" onmouseover="this.style.background=\'#F0F6F9\'" onmouseout="this.style.background=\'\'">'+
                            '<b>'+it.name+'</b><div style="font-size:10px;color:#999;">📁 '+it.full_path+'</div></div>';
                    }).join('');
                    results.querySelectorAll('.dc-pw-cat-opt').forEach(function(el){
                        el.addEventListener('click', function(){
                            document.getElementById('dc-pathway-catalog-modal').style.display = 'none';
                            dcSendCompleteWithCatalog(dcPathwayCompletingStepId, this.dataset.id);
                        });
                    });
                });
        }
        document.addEventListener('DOMContentLoaded', function(){
            var box = document.getElementById('dc-pathway-catalog-search');
            if (!box) return;
            var timer;
            box.addEventListener('input', function(){
                clearTimeout(timer);
                var q = this.value.trim();
                timer = setTimeout(function(){ dcTriggerPathwayCatalogSearch(q); }, 300);
            });
        });
        </script>

        <!-- مینی‌مودال انتخاب خدمت کاتالوگ برای اتصال مرحله -->
        <div id="dc-pathway-catalog-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.8);z-index:9999999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:16px;padding:20px;width:100%;max-width:440px;">
                <div style="font-weight:700;font-size:13px;margin-bottom:4px;">🔍 این مرحله به کدوم خدمت کاتالوگ مربوطه؟</div>
                <div style="font-size:11px;color:var(--dc-neutral-500);margin-bottom:10px;">نتایج بر اساس اسم خودِ مرحله خودکار اومدن — اگه درست نبود، پایین رو ویرایش کنید</div>
                <input type="text" id="dc-pathway-catalog-search" class="dc-input" placeholder="نام خدمت..." style="margin-bottom:8px;">
                <div id="dc-pathway-catalog-results" style="max-height:260px;overflow-y:auto;"></div>
                <button type="button" onclick="document.getElementById('dc-pathway-catalog-modal').style.display='none'" class="dc-btn dc-btn-ghost dc-btn-sm" style="width:100%;margin-top:10px;">انصراف</button>
            </div>
        </div>
        <?php
    }
}
