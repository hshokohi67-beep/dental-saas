<?php
defined('ABSPATH') || exit;

class Dental_Page_Monthly_Rota {

    private string $person_type; // 'doctor' یا 'assistant'

    public function __construct(string $person_type = 'doctor') {
        $this->person_type = $person_type;
    }

    public function render(): void {
        $jalali_ym = sanitize_text_field($_GET['ym'] ?? Dental_Jalali::today('Y/m'));
        [$jy, $jm] = array_map('intval', explode('/', $jalali_ym));
        $days_in_month = Dental_Jalali::days_in_jalali_month($jy, $jm);

        $people = $this->person_type === 'doctor'
            ? Dental_Shift_Scheduler::get_doctors()
            : Dental_Shift_Scheduler::get_assistants();

        // بازه میلادی این ماه شمسی برای کوئری یه‌جا
        $from_g = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/01', $jy, $jm));
        $to_g   = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/%02d', $jy, $jm, $days_in_month));
        [$rota, $locks] = Dental_Shift_Scheduler::get_month_rota_flat($this->person_type, $from_g, $to_g);

        // ─── چیدمان خودکار دستیارها (فقط برای این نوع اجرا می‌شه) ────
        if ($this->person_type === 'assistant' && isset($_POST['dental_auto_schedule_as']) && check_admin_referer('dental_shift_auto')) {
            $result = Dental_Shift_Scheduler::auto_schedule_assistants($jy, $jm);
            if ($result['ok']) {
                echo '<div class="notice notice-success"><p>✅ چیدمان انجام شد.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>❌ ' . esc_html($result['message']) . '</p></div>';
            }
            [$rota, $locks] = Dental_Shift_Scheduler::get_month_rota_flat($this->person_type, $from_g, $to_g);
        }

        $labels = ['m'=>['ص','صبح','var(--dc-accent-dark)'], 'e'=>['ع','عصر','var(--dc-accent-warm)'],
                   'l'=>['م','مرخصی','var(--dc-danger)'], 'd'=>['د','دوشیفتی','#8B5CF6']];
        $title = $this->person_type === 'doctor' ? 'برنامه ماهانه دکترها' : 'برنامه ماهانه دستیارها';
        $page_slug = $this->person_type === 'doctor' ? 'dental-monthly-doctors' : 'dental-monthly-assistants';

        // ماه قبل/بعد برای دکمه‌های ناوبری
        $prev_jm = $jm - 1; $prev_jy = $jy; if ($prev_jm < 1) { $prev_jm = 12; $prev_jy--; }
        $next_jm = $jm + 1; $next_jy = $jy; if ($next_jm > 12) { $next_jm = 1; $next_jy++; }
        ?>
        <div class="dental-admin-wrap">
            <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&shift_view=hub')); ?>" style="font-size:12px;color:var(--dc-primary);text-decoration:none;display:inline-block;margin-bottom:10px;">← بازگشت به شیفت‌بندی</a>
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <i data-lucide="calendar-days" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                <?php echo esc_html($title); ?>
            </h1>

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <a href="<?php echo esc_url(add_query_arg('ym', sprintf('%d/%02d',$prev_jy,$prev_jm))); ?>" class="dc-btn dc-btn-secondary dc-btn-sm">‹ ماه قبل</a>
                    <span style="font-weight:700;font-size:14px;min-width:120px;text-align:center;"><?php echo esc_html(Dental_Jalali::month_name($jm) . ' ' . $jy); ?></span>
                    <a href="<?php echo esc_url(add_query_arg('ym', sprintf('%d/%02d',$next_jy,$next_jm))); ?>" class="dc-btn dc-btn-secondary dc-btn-sm">ماه بعد ›</a>
                </div>
                <div style="font-size:11px;color:var(--dc-neutral-500);display:flex;align-items:center;gap:10px;">
                    کلیک روی هر خونه: خالی ← صبح ← عصر ← مرخصی ← خالی
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=dental-dashboard&print_rota=1&rota_type='.$this->person_type.'&ym='.rawurlencode($jalali_ym)),'dental_print_rota')); ?>" target="_blank" class="dc-btn dc-btn-secondary dc-btn-sm">🖨️ چاپ</a>
                </div>
                <?php if ($this->person_type === 'assistant'): ?>
                <div style="display:flex;gap:8px;">
                    <button type="button" id="dc-lock-mode-btn" onclick="dcToggleLockMode()" class="dc-btn dc-btn-secondary dc-btn-sm">
                        🔓 حالت قفل
                    </button>
                    <form method="post" onsubmit="return confirm('چیدمان فعلی (به‌جز روزهای قفل‌شده و مرخصی) پاک و دوباره چیده می‌شه. ادامه بدم؟');" style="display:inline;">
                        <?php wp_nonce_field('dental_shift_auto'); ?>
                        <button type="submit" name="dental_auto_schedule_as" class="dc-btn dc-btn-primary dc-btn-sm">⚡ چیدمان خودکار</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <?php if (empty($people)): ?>
            <div class="dc-card"><div class="dc-card-body" style="text-align:center;padding:30px;color:var(--dc-neutral-400);">
                هنوز <?php echo $this->person_type==='doctor'?'پزشکی':'دستیاری'; ?> ثبت نشده.
            </div></div>
            <?php else: ?>

            <div class="dc-card">
                <div class="dc-table-wrap" style="overflow-x:auto;">
                    <table class="dc-table" style="font-size:11px;">
                        <thead><tr>
                            <th style="position:sticky;right:0;background:#fff;min-width:130px;">نام</th>
                            <?php for($d=1;$d<=$days_in_month;$d++):
                                $g = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/%02d',$jy,$jm,$d));
                                $is_friday = date('N', strtotime($g)) == 5;
                            ?>
                            <th style="text-align:center;min-width:26px;<?php echo $is_friday?'color:var(--dc-danger);':''; ?>"><?php echo $d; ?></th>
                            <?php endfor; ?>
                            <th style="color:var(--dc-accent-dark);">ص</th>
                            <th style="color:var(--dc-accent-warm);">ع</th>
                            <th style="color:var(--dc-danger);">م</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach($people as $p):
                            $counts = ['m'=>0,'e'=>0,'l'=>0,'d'=>0];
                        ?>
                        <tr>
                            <td style="position:sticky;right:0;background:#fff;font-weight:700;white-space:nowrap;">
                                <?php echo esc_html($p->display_name); ?>
                            </td>
                            <?php for($d=1;$d<=$days_in_month;$d++):
                                $g = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/%02d',$jy,$jm,$d));
                                $is_friday = date('N', strtotime($g)) == 5;
                                $val = $rota[$p->ID][$g] ?? '';
                                $is_locked = !empty($locks[$p->ID][$g]);
                                if ($val) $counts[$val] = ($counts[$val] ?? 0) + 1;
                                [$short, $full, $color] = $labels[$val] ?? ['', '', ''];
                            ?>
                            <td class="dc-rota-cell" data-person="<?php echo $p->ID; ?>" data-date="<?php echo esc_attr($g); ?>"
                                style="text-align:center;position:relative;cursor:<?php echo $is_friday?'default':'pointer'; ?>;<?php echo $is_friday?'background:var(--dc-neutral-50);':''; ?><?php echo $is_locked?'box-shadow:inset 0 0 0 2px var(--dc-accent-warm);':''; ?>font-weight:700;color:<?php echo $color; ?>;"
                                <?php if(!$is_friday): ?>onclick="dcCycleRota(this)"<?php endif; ?>>
                                <span class="dc-cell-txt"><?php echo $is_friday ? 'ت' : esc_html($short); ?></span><?php if($is_locked): ?><span style="position:absolute;top:0;left:1px;font-size:7px;">🔒</span><?php endif; ?>
                            </td>
                            <?php endfor; ?>
                            <td style="text-align:center;font-weight:700;color:var(--dc-accent-dark);" class="dc-count-m"><?php echo $counts['m']; ?></td>
                            <td style="text-align:center;font-weight:700;color:var(--dc-accent-warm);" class="dc-count-e"><?php echo $counts['e']; ?></td>
                            <td style="text-align:center;font-weight:700;color:var(--dc-danger);" class="dc-count-l"><?php echo $counts['l']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <script>
        var dcRotaNonce = '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>';
        var dcRotaAjax  = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        var dcRotaLabels = {'':['',''], 'm':['ص','var(--dc-accent-dark)'], 'e':['ع','var(--dc-accent-warm)'], 'l':['م','var(--dc-danger)'], 'd':['د','#8B5CF6']};
        var dcLockMode = false;
        function dcToggleLockMode(){
            dcLockMode = !dcLockMode;
            var btn = document.getElementById('dc-lock-mode-btn');
            btn.textContent = dcLockMode ? '🔒 حالت قفل: فعال (کلیک=قفل/بازکردن)' : '🔓 حالت قفل';
            btn.className = 'dc-btn dc-btn-sm ' + (dcLockMode ? 'dc-btn-primary' : 'dc-btn-secondary');
        }
        function dcCycleRota(cell){
            var personId = cell.dataset.person, date = cell.dataset.date;
            var fd = new FormData();
            if (dcLockMode) {
                fd.append('action','dental_toggle_rota_lock');
                fd.append('_wpnonce', dcRotaNonce);
                fd.append('person_id', personId);
                fd.append('person_type', '<?php echo esc_js($this->person_type); ?>');
                fd.append('work_date', date);
                fetch(dcRotaAjax, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                    if(!res.success) return;
                    // تغییر نمای بصری قفل بدون رفرش کامل صفحه
                    var existingLock = cell.querySelector('span');
                    if (res.data.locked) {
                        cell.style.boxShadow = 'inset 0 0 0 2px var(--dc-accent-warm)';
                        if (!existingLock) { var s=document.createElement('span'); s.style.cssText='position:absolute;top:0;left:1px;font-size:7px;'; s.textContent='🔒'; cell.style.position='relative'; cell.appendChild(s); }
                    } else {
                        cell.style.boxShadow = 'none';
                        if (existingLock) existingLock.remove();
                    }
                });
                return;
            }
            fd.append('action','dental_cycle_rota');
            fd.append('_wpnonce', dcRotaNonce);
            fd.append('person_id', personId);
            fd.append('person_type', '<?php echo esc_js($this->person_type); ?>');
            fd.append('work_date', date);
            cell.style.opacity = '0.4';
            fetch(dcRotaAjax, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                cell.style.opacity = '1';
                if(!res.success) return;
                var val = res.data.value;
                var lbl = dcRotaLabels[val] || ['',''];
                cell.querySelector('.dc-cell-txt').textContent = lbl[0];
                cell.style.color = lbl[1];
                // به‌روزرسانی شمارنده‌های همون ردیف
                var row = cell.closest('tr');
                var allCells = row.querySelectorAll('.dc-rota-cell');
                var counts = {m:0,e:0,l:0,d:0};
                allCells.forEach(function(c){
                    var t = c.querySelector('.dc-cell-txt').textContent.trim();
                    if(t==='ص') counts.m++;
                    else if(t==='ع') counts.e++;
                    else if(t==='م') counts.l++;
                    else if(t==='د') counts.d++;
                });
                row.querySelector('.dc-count-m').textContent = counts.m;
                row.querySelector('.dc-count-e').textContent = counts.e;
                row.querySelector('.dc-count-l').textContent = counts.l;
            });
        }
        if(typeof lucide!=="undefined")lucide.createIcons();
        </script>
        <?php
    }

    // ─── پرینت تمیز برنامه ماهانه (بدون چارچوب پیشخوان) ──────────────
    public function render_print(int $jy, int $jm): void {
        $days_in_month = Dental_Jalali::days_in_jalali_month($jy, $jm);
        $people = $this->person_type === 'doctor' ? Dental_Shift_Scheduler::get_doctors() : Dental_Shift_Scheduler::get_assistants();
        $from_g = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/01',$jy,$jm));
        $to_g   = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/%02d',$jy,$jm,$days_in_month));
        [$rota, $locks] = Dental_Shift_Scheduler::get_month_rota_flat($this->person_type, $from_g, $to_g);
        $labels = ['m'=>'ص','e'=>'ع','l'=>'م','d'=>'د'];
        $title = $this->person_type === 'doctor' ? 'برنامه ماهانه دکترها' : 'برنامه ماهانه دستیارها';
        ?>
        <!DOCTYPE html>
        <html lang="fa" dir="rtl">
        <head>
        <meta charset="UTF-8">
        <title><?php echo esc_html($title.' — '.Dental_Jalali::month_name($jm).' '.$jy); ?></title>
        <style>
        * { font-family: Tahoma, sans-serif; margin:0; padding:0; box-sizing:border-box; }
        body { direction:rtl; padding:16px; }
        h2 { text-align:center; margin-bottom:14px; font-size:16px; }
        table { width:100%; border-collapse:collapse; font-size:10px; }
        th, td { border:1px solid #999; padding:4px 3px; text-align:center; }
        th { background:#eee; }
        .no-print { text-align:center; margin-bottom:16px; }
        @media print { .no-print{display:none;} }
        </style>
        </head>
        <body>
            <div class="no-print"><button onclick="window.print()" style="padding:10px 20px;">🖨️ چاپ</button></div>
            <h2><?php echo esc_html($title.' — '.Dental_Jalali::month_name($jm).' '.$jy); ?></h2>
            <table>
                <thead><tr>
                    <th>نام</th>
                    <?php for($d=1;$d<=$days_in_month;$d++): ?><th><?php echo $d; ?></th><?php endfor; ?>
                    <th>ص</th><th>ع</th><th>م</th>
                </tr></thead>
                <tbody>
                <?php foreach($people as $p):
                    $counts = ['m'=>0,'e'=>0,'l'=>0,'d'=>0];
                ?>
                <tr>
                    <td style="font-weight:700;white-space:nowrap;"><?php echo esc_html($p->display_name); ?></td>
                    <?php for($d=1;$d<=$days_in_month;$d++):
                        $g = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/%02d',$jy,$jm,$d));
                        $is_friday = date('N', strtotime($g)) == 5;
                        $val = $rota[$p->ID][$g] ?? '';
                        if ($val) $counts[$val]++;
                    ?>
                    <td><?php echo $is_friday ? 'ت' : esc_html($labels[$val] ?? ''); ?></td>
                    <?php endfor; ?>
                    <td><?php echo $counts['m']; ?></td>
                    <td><?php echo $counts['e']; ?></td>
                    <td><?php echo $counts['l']; ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </body>
        </html>
        <?php
    }
}
