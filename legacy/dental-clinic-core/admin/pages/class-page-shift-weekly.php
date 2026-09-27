<?php
defined('ABSPATH') || exit;

class Dental_Page_Shift_Weekly {

    public function render(): void {
        $jalali_ym = sanitize_text_field($_GET['ym'] ?? Dental_Jalali::today('Y/m'));
        [$jy, $jm] = array_map('intval', explode('/', $jalali_ym));
        $days_in_month = Dental_Jalali::days_in_jalali_month($jy, $jm);

        if (isset($_POST['dental_arrange_month']) && check_admin_referer('dental_shift_arrange')) {
            $from_g = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/01',$jy,$jm));
            $to_g   = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/%02d',$jy,$jm,$days_in_month));
            Dental_Shift_Scheduler::auto_arrange_week($from_g, $to_g);
            echo '<div class="notice notice-success"><p>✅ چیدمان کل ماه انجام شد.</p></div>';
        }

        $rooms = Dental_Shift_Scheduler::get_rooms();
        $doctors = Dental_Shift_Scheduler::get_doctors();
        $assistants = Dental_Shift_Scheduler::get_assistants();

        $prev_jm = $jm - 1; $prev_jy = $jy; if ($prev_jm < 1) { $prev_jm = 12; $prev_jy--; }
        $next_jm = $jm + 1; $next_jy = $jy; if ($next_jm > 12) { $next_jm = 1; $next_jy++; }

        // تقسیم روزهای ماه به هفته (هر ۷ روز)
        $weeks = [];
        for ($s = 1; $s <= $days_in_month; $s += 7) $weeks[] = [$s, min($s+6, $days_in_month)];
        ?>
        <div class="dental-admin-wrap">
            <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&shift_view=hub')); ?>" style="font-size:12px;color:var(--dc-primary);text-decoration:none;display:inline-block;margin-bottom:10px;">← بازگشت به شیفت‌بندی</a>
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <i data-lucide="calendar-range" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                برنامه هفتگی اتاق‌ها
            </h1>

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <a href="<?php echo esc_url(add_query_arg('ym', sprintf('%d/%02d',$prev_jy,$prev_jm))); ?>" class="dc-btn dc-btn-secondary dc-btn-sm">‹ ماه قبل</a>
                    <span style="font-weight:700;font-size:14px;min-width:120px;text-align:center;"><?php echo esc_html(Dental_Jalali::month_name($jm) . ' ' . $jy); ?></span>
                    <a href="<?php echo esc_url(add_query_arg('ym', sprintf('%d/%02d',$next_jy,$next_jm))); ?>" class="dc-btn dc-btn-secondary dc-btn-sm">ماه بعد ›</a>
                </div>
                <div style="display:flex;gap:8px;">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dental-shift-rooms')); ?>" class="dc-btn dc-btn-ghost dc-btn-sm">⚙️ تنظیمات اتاق‌ها</a>
                    <form method="post" onsubmit="return confirm('چیدمان کل ماه انجام می‌شه (اتاق‌های خالی/بدون قفل بازنویسی می‌شن). ادامه بدم؟');" style="display:inline;">
                        <?php wp_nonce_field('dental_shift_arrange'); ?>
                        <button type="submit" name="dental_arrange_month" class="dc-btn dc-btn-primary dc-btn-sm">⚡ چیدمان کل ماه</button>
                    </form>
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=dental-dashboard&print_shift_week=1&ym='.rawurlencode($jalali_ym)),'dental_print_shift')); ?>" target="_blank" class="dc-btn dc-btn-secondary dc-btn-sm">🖨️ چاپ</a>
                </div>
            </div>

            <?php if (empty($doctors) || empty($assistants)): ?>
            <div class="dc-card"><div class="dc-card-body" style="text-align:center;padding:30px;color:var(--dc-neutral-400);">
                ابتدا پزشکان و دستیاران را وارد کنید و برنامه ماهانه‌شان را مشخص نمایید.
            </div></div>
            <?php else:
            foreach ($weeks as $wi => [$s, $e]): ?>
            <div class="dc-card" style="margin-bottom:20px;">
                <div class="dc-card-header">
                    <h3 class="dc-heading-4">هفته <?php echo $wi+1; ?> <span style="font-size:11px;color:var(--dc-neutral-400);font-weight:400;">(روز <?php echo $s; ?> تا <?php echo $e; ?>)</span></h3>
                    <form method="post" onsubmit="return confirm('چیدمان این هفته انجام می‌شه. ادامه بدم؟');">
                        <?php wp_nonce_field('dental_shift_arrange_week_'.$wi); ?>
                        <input type="hidden" name="week_start" value="<?php echo $s; ?>">
                        <input type="hidden" name="week_end" value="<?php echo $e; ?>">
                        <button type="submit" name="dental_arrange_week" class="dc-btn dc-btn-secondary dc-btn-sm">⚡ چیدمان این هفته</button>
                    </form>
                </div>
                <?php
                // ─── چیدمان یک هفته خاص ─────────────────────────────
                if (isset($_POST['dental_arrange_week']) && (int)($_POST['week_start']??0)===$s && check_admin_referer('dental_shift_arrange_week_'.$wi)) {
                    $from_g = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/%02d',$jy,$jm,$s));
                    $to_g   = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/%02d',$jy,$jm,$e));
                    Dental_Shift_Scheduler::auto_arrange_week($from_g, $to_g);
                    echo '<div style="padding:10px 16px;background:var(--dc-accent-light);font-size:12px;">✅ چیدمان این هفته انجام شد.</div>';
                }
                $week_from_g = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/%02d',$jy,$jm,$s));
                $week_to_g   = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/%02d',$jy,$jm,$e));
                $week_warns  = Dental_Shift_Scheduler::get_week_warnings($week_from_g, $week_to_g);
                ?>
                <?php if (!empty($week_warns)): ?>
                <div style="padding:10px 16px;background:var(--dc-danger-light);border-bottom:1px solid var(--dc-danger);font-size:11px;">
                    <div style="font-weight:700;color:var(--dc-danger);margin-bottom:4px;">⚠️ <?php echo count($week_warns); ?> هشدار این هفته:</div>
                    <?php foreach($week_warns as $w): ?><div style="padding:2px 0;"><?php echo esc_html($w); ?></div><?php endforeach; ?>
                </div>
                <?php else: ?>
                <div style="padding:8px 16px;background:var(--dc-accent-light);font-size:11px;color:var(--dc-accent-dark);">✅ هیچ هشداری برای این هفته نیست</div>
                <?php endif; ?>
                <div class="dc-table-wrap" style="overflow-x:auto;">
                    <table class="dc-table" style="font-size:10px;white-space:nowrap;">
                        <thead>
                            <tr>
                                <th rowspan="2" style="position:sticky;right:0;background:#fff;">روز</th>
                                <th colspan="<?php echo count($rooms)+2; ?>" style="text-align:center;background:var(--dc-accent-light);">🟢 شیفت صبح</th>
                                <th colspan="<?php echo count($rooms)+2; ?>" style="text-align:center;background:var(--dc-warning-light);border-right:3px solid var(--dc-neutral-300);">🟠 شیفت عصر</th>
                            </tr>
                            <tr>
                                <?php foreach(['m','e'] as $si=>$shift): ?>
                                <th style="<?php echo $shift==='e'?'border-right:3px solid var(--dc-neutral-300);':''; ?>">پذیرش</th>
                                <?php foreach($rooms as $r): ?><th>اتاق <?php echo (int)$r['room_number']; ?></th><?php endforeach; ?>
                                <th>تحویل</th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php for($d=$s; $d<=$e; $d++):
                            $g = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/%02d',$jy,$jm,$d));
                            $is_friday = date('N', strtotime($g)) == 5;
                            $day_name = Dental_Jalali::day_name($g);
                        ?>
                        <tr>
                            <td style="position:sticky;right:0;background:#fff;font-weight:700;"><?php echo esc_html($day_name).' '.$d; ?></td>
                            <?php if ($is_friday): ?>
                            <td colspan="<?php echo (count($rooms)+2)*2; ?>" style="text-align:center;background:var(--dc-neutral-50);color:var(--dc-neutral-400);">تعطیل</td>
                            <?php else:
                                foreach(['m','e'] as $shift):
                                    $recv = Dental_Shift_Scheduler::get_cell($g, $shift, 'recv');
                                    $recv_names = [];
                                    foreach (($recv['extra']['assistantIds'] ?? []) as $aid) {
                                        $u = get_userdata($aid);
                                        if ($u) $recv_names[] = esc_html($u->display_name);
                                    }
                            ?>
                            <td class="dc-shift-cell" data-date="<?php echo $g; ?>" data-shift="<?php echo $shift; ?>" data-slot="recv"
                                onclick="dcOpenCell(this,'recv')" style="cursor:pointer;<?php echo $shift==='e'?'border-right:3px solid var(--dc-neutral-300);':''; ?>">
                                <?php echo $recv_names ? implode('<br>',$recv_names) : '<span style="color:var(--dc-neutral-300);">—</span>'; ?>
                            </td>
                            <?php foreach($rooms as $r):
                                $active = $shift==='m' ? $r['active_morning'] : $r['active_evening'];
                                if (!$active) { echo '<td style="background:var(--dc-neutral-50);color:var(--dc-neutral-300);text-align:center;">غیرفعال</td>'; continue; }
                                $cell = Dental_Shift_Scheduler::get_cell($g, $shift, 'r'.$r['id']);
                                $dr = $cell['doctor_id'] ? get_userdata($cell['doctor_id']) : null;
                                $as = $cell['assistant_id'] ? get_userdata($cell['assistant_id']) : null;
                            ?>
                            <td class="dc-shift-cell" data-date="<?php echo $g; ?>" data-shift="<?php echo $shift; ?>" data-slot="r<?php echo $r['id']; ?>"
                                onclick="dcOpenCell(this,'room')" style="cursor:pointer;<?php echo $cell['is_double']?'background:#F1EFFE;':''; ?>">
                                <?php if($cell['is_double']):
                                    $dr2 = $cell['doctor2_id'] ? get_userdata($cell['doctor2_id']) : null;
                                    $as2 = $cell['assistant2_id'] ? get_userdata($cell['assistant2_id']) : null;
                                    $as2_show = $cell['single_assistant'] ? $as : $as2;
                                ?>
                                <div><?php echo $dr?esc_html($dr->display_name):'—'; ?> / <?php echo $as?esc_html($as->display_name):''; ?></div>
                                <div style="border-top:1px dashed var(--dc-neutral-300);"><?php echo $dr2?esc_html($dr2->display_name):'—'; ?> / <?php echo $as2_show?esc_html($as2_show->display_name):''; ?></div>
                                <?php else: ?>
                                <?php echo $dr ? esc_html($dr->display_name) : '<span style="color:var(--dc-neutral-300);">—</span>'; ?>
                                <?php if($as): ?><br><span style="color:var(--dc-neutral-500);"><?php echo esc_html($as->display_name); ?></span><?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <?php endforeach;
                                $hov = Dental_Shift_Scheduler::get_cell($g, $shift, 'handover');
                                $hov_u = $hov['assistant_id'] ? get_userdata($hov['assistant_id']) : null;
                            ?>
                            <td class="dc-shift-cell" data-date="<?php echo $g; ?>" data-shift="<?php echo $shift; ?>" data-slot="handover"
                                onclick="dcOpenCell(this,'handover')" style="cursor:pointer;">
                                <?php echo $hov_u ? esc_html($hov_u->display_name) : '<span style="color:var(--dc-neutral-300);">—</span>'; ?>
                            </td>
                            <?php endforeach; endif; ?>
                        </tr>
                        <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <!-- مودال ویرایش سلول -->
        <div id="dc-shift-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.75);z-index:999998;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:16px;width:100%;max-width:460px;max-height:85vh;overflow-y:auto;padding:22px;">
                <div id="dc-shift-modal-title" style="font-size:14px;font-weight:700;margin-bottom:14px;"></div>
                <div id="dc-shift-modal-body"></div>
                <div style="display:flex;gap:8px;margin-top:16px;">
                    <button onclick="dcSaveShiftCell()" class="dc-btn dc-btn-primary" style="flex:1;">ذخیره</button>
                    <button onclick="document.getElementById('dc-shift-modal').style.display='none'" class="dc-btn dc-btn-ghost">انصراف</button>
                </div>
            </div>
        </div>

        <script>
        var dcShiftNonce = '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>';
        var dcShiftAjax  = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        var dcCurrentCell = null;

        var DC_DOCTORS = <?php echo wp_json_encode(array_map(fn($d)=>['id'=>$d->ID,'name'=>$d->display_name], $doctors)); ?>;
        var DC_ASSISTANTS = <?php echo wp_json_encode(array_map(fn($a)=>['id'=>$a->ID,'name'=>$a->display_name], $assistants)); ?>;
        var DC_RECEPTION = <?php echo wp_json_encode(array_map(fn($r)=>['id'=>$r->ID,'name'=>$r->display_name], Dental_Shift_Scheduler::get_reception_staff())); ?>;
        var DC_ASSISTANTS_AND_RECEPTION = DC_ASSISTANTS.concat(DC_RECEPTION);

        function dcOpenCell(el, type){
            dcCurrentCell = el;
            var date = el.dataset.date, shift = el.dataset.shift, slot = el.dataset.slot;
            document.getElementById('dc-shift-modal-title').textContent = date + ' — ' + (shift==='m'?'صبح':'عصر') + ' — ' + (type==='recv'?'پذیرش':type==='handover'?'تحویل وسایل':slot);

            var drOpts = '<option value="">— خالی —</option>' + DC_DOCTORS.map(d=>'<option value="'+d.id+'">'+d.name+'</option>').join('');
            var asOpts = '<option value="">— خالی —</option>' + DC_ASSISTANTS.map(a=>'<option value="'+a.id+'">'+a.name+'</option>').join('');

            var body = document.getElementById('dc-shift-modal-body');
            if (type === 'room') {
                body.innerHTML = '<div class="dc-form-group"><label class="dc-label">دکتر</label><select id="dc-f-dr" class="dc-select">'+drOpts+'</select></div>'+
                    '<div class="dc-form-group"><label class="dc-label">دستیار</label><select id="dc-f-as" class="dc-select">'+asOpts+'</select></div>'+
                    '<label style="display:flex;align-items:center;gap:6px;margin-top:10px;font-size:12px;"><input type="checkbox" id="dc-f-double" onchange="dcToggleDouble(this)"> این اتاق امروز دونفره است</label>'+
                    '<div id="dc-double-fields" style="display:none;margin-top:10px;padding-top:10px;border-top:1px solid var(--dc-neutral-100);">'+
                    '<div class="dc-form-group"><label class="dc-label">دکتر (نفر دوم)</label><select id="dc-f-dr2" class="dc-select">'+drOpts+'</select></div>'+
                    '<label style="display:flex;align-items:center;gap:6px;font-size:12px;"><input type="checkbox" id="dc-f-single-as"> دستیار مشترک</label>'+
                    '<div id="dc-as2-row" class="dc-form-group" style="margin-top:8px;"><label class="dc-label">دستیار (نفر دوم)</label><select id="dc-f-as2" class="dc-select">'+asOpts+'</select></div>'+
                    '</div>';
            } else if (type === 'handover') {
                body.innerHTML = '<div class="dc-form-group"><label class="dc-label">دستیار</label><select id="dc-f-as" class="dc-select">'+asOpts+'</select></div>';
            } else if (type === 'recv') {
                body.innerHTML = '<div class="dc-form-group"><label class="dc-label">دستیارهای حاضر پذیرش</label>' +
                    DC_ASSISTANTS_AND_RECEPTION.map(a=>'<label style="display:flex;align-items:center;gap:6px;padding:3px 0;font-size:12px;"><input type="checkbox" class="dc-recv-chk" value="'+a.id+'"> '+a.name+'</label>').join('') + '</div>';
            }
            document.getElementById('dc-shift-modal').style.display = 'flex';

            // گرفتن مقدار فعلی از سرور
            var fd = new FormData();
            fd.append('action','dental_get_shift_cell');
            fd.append('_wpnonce', dcShiftNonce);
            fd.append('work_date', date); fd.append('shift', shift); fd.append('slot_key', slot);
            fetch(dcShiftAjax, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                if(!res.success) return;
                var c = res.data.cell;
                if (type === 'room') {
                    if(c.doctor_id) document.getElementById('dc-f-dr').value = c.doctor_id;
                    if(c.assistant_id) document.getElementById('dc-f-as').value = c.assistant_id;
                    if(c.is_double){
                        document.getElementById('dc-f-double').checked = true;
                        dcToggleDouble(document.getElementById('dc-f-double'));
                        if(c.doctor2_id) document.getElementById('dc-f-dr2').value = c.doctor2_id;
                        if(c.single_assistant) document.getElementById('dc-f-single-as').checked = true;
                        if(c.assistant2_id) document.getElementById('dc-f-as2').value = c.assistant2_id;
                    }
                } else if (type === 'handover') {
                    if(c.assistant_id) document.getElementById('dc-f-as').value = c.assistant_id;
                } else if (type === 'recv') {
                    (c.extra.assistantIds||[]).forEach(function(aid){
                        var cb = document.querySelector('.dc-recv-chk[value="'+aid+'"]');
                        if(cb) cb.checked = true;
                    });
                }
            });
        }
        function dcToggleDouble(el){ document.getElementById('dc-double-fields').style.display = el.checked ? 'block' : 'none'; }

        function dcSaveShiftCell(){
            var el = dcCurrentCell;
            var date = el.dataset.date, shift = el.dataset.shift, slot = el.dataset.slot, type = el.dataset.slot==='recv'?'recv':el.dataset.slot==='handover'?'handover':'room';
            var fd = new FormData();
            fd.append('action','dental_save_shift_cell');
            fd.append('_wpnonce', dcShiftNonce);
            fd.append('work_date', date); fd.append('shift', shift); fd.append('slot_key', slot);

            if (type === 'room') {
                fd.append('doctor_id', document.getElementById('dc-f-dr').value);
                fd.append('assistant_id', document.getElementById('dc-f-as').value);
                var isDouble = document.getElementById('dc-f-double').checked;
                fd.append('is_double', isDouble?1:0);
                if(isDouble){
                    fd.append('doctor2_id', document.getElementById('dc-f-dr2').value);
                    var singleAs = document.getElementById('dc-f-single-as').checked;
                    fd.append('single_assistant', singleAs?1:0);
                    if(!singleAs) fd.append('assistant2_id', document.getElementById('dc-f-as2').value);
                }
            } else if (type === 'handover') {
                fd.append('assistant_id', document.getElementById('dc-f-as').value);
            } else if (type === 'recv') {
                var ids = Array.from(document.querySelectorAll('.dc-recv-chk:checked')).map(c=>c.value);
                fd.append('assistant_ids', JSON.stringify(ids));
            }

            fetch(dcShiftAjax, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                document.getElementById('dc-shift-modal').style.display = 'none';
                if(res.success) location.reload();
            });
        }
        if(typeof lucide!=="undefined")lucide.createIcons();
        </script>
        <?php
    }

    // ─── پرینت تمیز برنامه ماه (بدون چارچوب پیشخوان) ────────────────
    public function render_print(int $jy, int $jm): void {
        $days_in_month = Dental_Jalali::days_in_jalali_month($jy, $jm);
        $rooms = Dental_Shift_Scheduler::get_rooms();
        $weeks = [];
        for ($s = 1; $s <= $days_in_month; $s += 7) $weeks[] = [$s, min($s+6, $days_in_month)];
        ?>
        <!DOCTYPE html>
        <html lang="fa" dir="rtl">
        <head>
        <meta charset="UTF-8">
        <title>برنامه هفتگی اتاق‌ها — <?php echo Dental_Jalali::month_name($jm).' '.$jy; ?></title>
        <style>
        * { font-family: Tahoma, sans-serif; margin:0; padding:0; box-sizing:border-box; }
        body { direction:rtl; padding:16px; }
        h2 { text-align:center; margin-bottom:14px; font-size:16px; }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; font-size:9px; }
        th, td { border:1px solid #999; padding:3px 4px; text-align:center; }
        th { background:#eee; }
        .no-print { text-align:center; margin-bottom:16px; }
        @media print { .no-print{display:none;} }
        </style>
        </head>
        <body>
            <div class="no-print"><button onclick="window.print()" style="padding:10px 20px;">🖨️ چاپ</button></div>
            <h2>برنامه هفتگی اتاق‌ها — <?php echo Dental_Jalali::month_name($jm).' '.$jy; ?></h2>
            <?php foreach($weeks as $wi => [$s,$e]): ?>
            <h3 style="font-size:12px;margin:10px 0;">هفته <?php echo $wi+1; ?> (روز <?php echo $s; ?> تا <?php echo $e; ?>)</h3>
            <table>
                <thead><tr>
                    <th>روز</th>
                    <th colspan="<?php echo count($rooms)+2; ?>">صبح</th>
                    <th colspan="<?php echo count($rooms)+2; ?>">عصر</th>
                </tr>
                <tr><th></th>
                    <?php foreach(['m','e'] as $sh): ?>
                    <th>پذیرش</th>
                    <?php foreach($rooms as $r): ?><th>اتاق <?php echo (int)$r['room_number']; ?></th><?php endforeach; ?>
                    <th>تحویل</th>
                    <?php endforeach; ?>
                </tr></thead>
                <tbody>
                <?php for($d=$s;$d<=$e;$d++):
                    $g = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/%02d',$jy,$jm,$d));
                    $is_friday = date('N', strtotime($g)) == 5;
                ?>
                <tr>
                    <td><?php echo esc_html(Dental_Jalali::day_name($g)).' '.$d; ?></td>
                    <?php if ($is_friday): ?>
                    <td colspan="<?php echo (count($rooms)+2)*2; ?>">تعطیل</td>
                    <?php else: foreach(['m','e'] as $shift):
                        $recv = Dental_Shift_Scheduler::get_cell($g, $shift, 'recv');
                        $names = array_map(fn($aid)=>($u=get_userdata($aid))?$u->display_name:'', $recv['extra']['assistantIds'] ?? []);
                    ?>
                    <td><?php echo esc_html(implode('، ',array_filter($names))) ?: '—'; ?></td>
                    <?php foreach($rooms as $r):
                        $active = $shift==='m' ? $r['active_morning'] : $r['active_evening'];
                        if (!$active) { echo '<td>—</td>'; continue; }
                        $cell = Dental_Shift_Scheduler::get_cell($g, $shift, 'r'.$r['id']);
                        $dr = $cell['doctor_id'] ? get_userdata($cell['doctor_id']) : null;
                        $as = $cell['assistant_id'] ? get_userdata($cell['assistant_id']) : null;
                    ?>
                    <td><?php echo $dr?esc_html($dr->display_name):'—'; echo $as?' / '.esc_html($as->display_name):''; ?></td>
                    <?php endforeach;
                        $hov = Dental_Shift_Scheduler::get_cell($g, $shift, 'handover');
                        $hu = $hov['assistant_id'] ? get_userdata($hov['assistant_id']) : null;
                    ?>
                    <td><?php echo $hu?esc_html($hu->display_name):'—'; ?></td>
                    <?php endforeach; endif; ?>
                </tr>
                <?php endfor; ?>
                </tbody>
            </table>
            <?php endforeach; ?>
        </body>
        </html>
        <?php
    }
}
