<?php
defined('ABSPATH') || exit;

class Dental_Page_Attendance {

    // ─── رفع همون باگ «صفحه سفید/رفرش بدون ذخیره» — از admin_init صدا زده می‌شه
    public function maybe_handle_post(): void {
        $cu = wp_get_current_user();
        $is_admin = current_user_can('manage_options') || in_array('dental_admin', (array)$cu->roles);
        $cu_id = get_current_user_id();

        if (isset($_POST['dental_request_leave']) && check_admin_referer('dental_attendance')) {
            $from_j = sanitize_text_field($_POST['date_from'] ?? '');
            $to_j   = sanitize_text_field($_POST['date_to'] ?? '');
            $from_g = Dental_Jalali::to_gregorian($from_j) ?: $from_j;
            $to_g   = Dental_Jalali::to_gregorian($to_j) ?: $to_j;
            Dental_Attendance_Manager::request_leave(
                $cu_id,
                sanitize_key($_POST['leave_type'] ?? 'daily'),
                $from_g,
                $to_g,
                !empty($_POST['hours']) ? (float)$_POST['hours'] : null,
                sanitize_textarea_field($_POST['reason'] ?? '')
            );
            wp_safe_redirect(add_query_arg(['tab'=>'my','saved'=>1])); exit;
        }
        if (isset($_POST['dental_review_leave']) && check_admin_referer('dental_attendance') && $is_admin) {
            Dental_Attendance_Manager::review_leave(
                (int)$_POST['leave_id'],
                sanitize_key($_POST['review_status']),
                $cu_id,
                sanitize_text_field($_POST['admin_note'] ?? '')
            );
            wp_safe_redirect(add_query_arg(['tab'=>'pending','saved'=>1])); exit;
        }
        if (isset($_POST['dental_save_break_limits']) && check_admin_referer('dental_attendance') && $is_admin) {
            $limits = [];
            foreach (['dental_admin','dental_doctor','dental_secretary','dental_financial','dental_assistant'] as $role) {
                $limits[$role] = [
                    'lunch' => (int)($_POST['lunch_'.$role] ?? 30),
                    'break' => (int)($_POST['break_'.$role] ?? 10),
                ];
            }
            Dental_Attendance_Manager::save_break_limits($limits);
            wp_safe_redirect(add_query_arg(['tab'=>'break_settings','saved'=>1])); exit;
        }
        if (isset($_POST['dental_save_leave_quota']) && $is_admin && check_admin_referer('dental_leave_quota')) {
            foreach ($_POST['quota'] ?? [] as $uid => $days) {
                update_user_meta((int)$uid, '_dental_leave_quota_days', max(0, (float)$days));
            }
            wp_safe_redirect(add_query_arg(['tab'=>'leave_quota','saved'=>1])); exit;
        }
    }

    public function render(): void {
        // نکته مهم: قبلاً از current_user_can('manage_dental') هم استفاده می‌شد،
        // ولی این مجوز به دندانپزشک هم داده شده — پس دکتر اشتباهاً همه تب‌های
        // مدیریتی (تأیید مرخصی، گزارش‌ها، تنظیم سقف زمان و...) را می‌دید.
        $cu = wp_get_current_user();
        $is_admin = current_user_can('manage_options') || in_array('dental_admin', (array)$cu->roles);
        $tab = sanitize_key($_GET['tab'] ?? ($is_admin ? 'pending' : 'my'));
        $cu_id = get_current_user_id();

        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                <i data-lucide="calendar-clock" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                حضور و غیاب و مرخصی
            </h1>

            <?php if(isset($_GET['saved'])): ?>
            <div style="background:var(--dc-accent-light);border:1px solid var(--dc-accent);border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:13px;">✅ ثبت شد.</div>
            <?php endif; ?>

            <div class="dc-tabs" style="margin-bottom:20px;">
                <a href="<?php echo esc_url(add_query_arg('tab','my')); ?>" class="dc-tab <?php echo $tab==='my'?'active':''; ?>">مرخصی من</a>
                <a href="<?php echo esc_url(add_query_arg('tab','history')); ?>" class="dc-tab <?php echo $tab==='history'?'active':''; ?>">تاریخچه حضور</a>
                <?php if ($is_admin): ?>
                <a href="<?php echo esc_url(add_query_arg('tab','pending')); ?>" class="dc-tab <?php echo $tab==='pending'?'active':''; ?>">
                    درخواست‌های در انتظار
                    <?php $pc = count(Dental_Attendance_Manager::get_pending_leaves()); if($pc): ?>
                    <span class="dc-badge dc-badge-danger" style="margin-right:4px;"><?php echo $pc; ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab','report')); ?>" class="dc-tab <?php echo $tab==='report'?'active':''; ?>">گزارش مرخصی‌ها</a>
                <a href="<?php echo esc_url(add_query_arg('tab','all_attendance')); ?>" class="dc-tab <?php echo $tab==='all_attendance'?'active':''; ?>">حضور همه کارکنان</a>
                <a href="<?php echo esc_url(add_query_arg('tab','attendance_report')); ?>" class="dc-tab <?php echo $tab==='attendance_report'?'active':''; ?>">گزارش حضور و غیاب</a>
                <a href="<?php echo esc_url(add_query_arg('tab','break_settings')); ?>" class="dc-tab <?php echo $tab==='break_settings'?'active':''; ?>">تنظیم سقف زمان</a>
                <a href="<?php echo esc_url(add_query_arg('tab','leave_quota')); ?>" class="dc-tab <?php echo $tab==='leave_quota'?'active':''; ?>">سهمیه مرخصی افراد</a>
                <a href="<?php echo esc_url(add_query_arg('tab','break_log')); ?>" class="dc-tab <?php echo $tab==='break_log'?'active':''; ?>">لاگ ناهار/استراحت</a>
                <?php endif; ?>
            </div>

            <?php
            match($tab) {
                'pending'        => $is_admin ? $this->render_pending() : $this->render_my(),
                'report'         => $is_admin ? $this->render_report() : $this->render_my(),
                'all_attendance' => $is_admin ? $this->render_all_attendance() : $this->render_my(),
                'attendance_report' => $is_admin ? $this->render_attendance_report() : $this->render_my(),
                'break_settings' => $is_admin ? $this->render_break_settings() : $this->render_my(),
                'leave_quota'    => $is_admin ? $this->render_leave_quota() : $this->render_my(),
                'break_log'      => $is_admin ? $this->render_break_log() : $this->render_my(),
                'history'        => $this->render_history($cu_id),
                default          => $this->render_my($cu_id),
            };
            ?>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }

    // ─── تب «مرخصی من» — ثبت درخواست + تاریخچه شخصی ─────────────
    private function render_my(): void {
        $cu_id   = get_current_user_id();
        $balance = Dental_Attendance_Manager::get_leave_balance($cu_id);
        $leaves  = Dental_Attendance_Manager::get_my_leaves($cu_id);
        $status_cfg = ['pending'=>['#F0A500','در انتظار'],'approved'=>['#2ECC9A','تأیید شده'],'rejected'=>['#E05252','رد شده']];
        ?>
        <div style="display:grid;grid-template-columns:1fr 1.4fr;gap:20px;align-items:start;">
            <div>
                <!-- موجودی مرخصی -->
                <div class="dc-card" style="margin-bottom:16px;">
                    <div class="dc-card-header"><h3 class="dc-heading-4">📊 موجودی مرخصی امسال</h3></div>
                    <div class="dc-card-body" style="display:flex;gap:14px;text-align:center;">
                        <div style="flex:1;">
                            <div style="font-size:22px;font-weight:700;color:var(--dc-primary);"><?php echo $balance['quota']; ?></div>
                            <div style="font-size:11px;color:var(--dc-neutral-500);">سهمیه (روز)</div>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:22px;font-weight:700;color:var(--dc-accent-warm);"><?php echo $balance['used']; ?></div>
                            <div style="font-size:11px;color:var(--dc-neutral-500);">استفاده‌شده</div>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:22px;font-weight:700;color:var(--dc-accent-dark);"><?php echo $balance['remaining']; ?></div>
                            <div style="font-size:11px;color:var(--dc-neutral-500);">باقی‌مانده</div>
                        </div>
                    </div>
                </div>

                <!-- فرم ثبت مرخصی -->
                <div class="dc-card">
                    <div class="dc-card-header"><h3 class="dc-heading-4">📝 ثبت درخواست مرخصی</h3></div>
                    <div class="dc-card-body">
                        <form method="post">
                            <?php wp_nonce_field('dental_attendance'); ?>
                            <div style="display:flex;flex-direction:column;gap:12px;">
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">نوع مرخصی</label>
                                    <select name="leave_type" id="lv-type" class="dc-select" onchange="lvToggle()">
                                        <option value="daily">روزانه</option>
                                        <option value="hourly">ساعتی</option>
                                    </select>
                                </div>
                                <div class="dc-grid dc-grid-2" style="gap:10px;">
                                    <div class="dc-form-group" style="margin:0;">
                                        <label class="dc-label">از تاریخ (شمسی)</label>
                                        <input type="text" name="date_from" class="dc-input dc-datepicker" required dir="ltr">
                                    </div>
                                    <div class="dc-form-group" style="margin:0;" id="lv-to-wrap">
                                        <label class="dc-label">تا تاریخ (شمسی)</label>
                                        <input type="text" name="date_to" class="dc-input dc-datepicker" required dir="ltr">
                                    </div>
                                </div>
                                <div class="dc-form-group" style="margin:0;display:none;" id="lv-hours-wrap">
                                    <label class="dc-label">تعداد ساعت</label>
                                    <input type="number" name="hours" class="dc-input" min="1" max="8" step="0.5">
                                </div>
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">دلیل (اختیاری)</label>
                                    <textarea name="reason" class="dc-textarea" placeholder="توضیح کوتاه..."></textarea>
                                </div>
                            </div>
                            <div style="margin-top:14px;">
                                <button type="submit" name="dental_request_leave" class="dc-btn dc-btn-primary">📤 ثبت درخواست</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- تاریخچه شخصی -->
            <div class="dc-card">
                <div class="dc-card-header"><h3 class="dc-heading-4">📜 تاریخچه مرخصی‌های من</h3></div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>نوع</th><th>از</th><th>تا</th><th>تعداد</th><th>وضعیت</th><th>یادداشت مدیر</th></tr></thead>
                        <tbody>
                        <?php if(empty($leaves)): ?>
                        <tr><td colspan="6" style="text-align:center;padding:28px;color:var(--dc-neutral-400);">درخواستی ثبت نشده</td></tr>
                        <?php else: foreach($leaves as $l):
                            [$c,$l_label] = $status_cfg[$l['status']] ?? ['#999',$l['status']];
                        ?>
                        <tr>
                            <td><?php echo $l['leave_type']==='hourly'?'ساعتی':'روزانه'; ?></td>
                            <td><?php echo esc_html(Dental_Jalali::to_jalali($l['date_from'],'Y/m/d')); ?></td>
                            <td><?php echo esc_html(Dental_Jalali::to_jalali($l['date_to'],'Y/m/d')); ?></td>
                            <td><?php echo $l['leave_type']==='hourly' ? $l['hours'].' ساعت' : $l['days_count'].' روز'; ?></td>
                            <td><span style="background:<?php echo $c; ?>22;color:<?php echo $c; ?>;border-radius:10px;padding:2px 10px;font-size:11px;font-weight:700;"><?php echo esc_html($l_label); ?></span></td>
                            <td style="font-size:12px;color:var(--dc-neutral-500);"><?php echo esc_html($l['admin_note']?:'—'); ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script>
        function lvToggle(){
            var isHourly = document.getElementById('lv-type').value === 'hourly';
            document.getElementById('lv-hours-wrap').style.display = isHourly ? 'block' : 'none';
        }
        </script>
        <?php
    }

    // ─── تب «تاریخچه حضور» — ورود/خروج شخصی ─────────────────────
    private function render_history(int $user_id): void {
        $from = date('Y-m-d', strtotime('-30 days'));
        $to   = current_time('Y-m-d');
        $history = Dental_Attendance_Manager::get_history($user_id, $from, $to);
        $status = Dental_Attendance_Manager::get_today_status($user_id);
        $open_break = Dental_Attendance_Manager::get_open_break($user_id);
        ?>
        <!-- ورود/خروج امروز -->
        <div class="dc-card" style="margin-bottom:20px;max-width:520px;">
            <div class="dc-card-header"><h3 class="dc-heading-4">⏱️ حضور امروز</h3></div>
            <div class="dc-card-body">
                <div style="display:flex;gap:8px;margin-bottom:10px;">
                    <?php if (!$status['clocked_in']): ?>
                    <button type="button" onclick="dcClockAction('clock_in')" class="dc-btn dc-btn-primary" style="flex:1;">🟢 ورود</button>
                    <?php else: ?>
                    <button type="button" onclick="dcClockAction('clock_out')" class="dc-btn dc-btn-danger" style="flex:1;">🔴 خروج</button>
                    <?php endif; ?>
                    <?php if ($status['clocked_in']): ?>
                        <?php if (!$open_break): ?>
                        <button type="button" onclick="dcBreakAction('start_break','lunch')" class="dc-btn dc-btn-secondary">🍽️ ناهار</button>
                        <button type="button" onclick="dcBreakAction('start_break','break')" class="dc-btn dc-btn-secondary">☕ استراحت</button>
                        <?php else: ?>
                        <button type="button" onclick="dcBreakAction('end_break')" class="dc-btn dc-btn-success">↩️ بازگشت از <?php echo $open_break['break_type']==='lunch'?'ناهار':'استراحت'; ?></button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php if ($status['attendance']): ?>
                <div style="font-size:12px;color:var(--dc-neutral-600);">
                    ورود: <?php echo esc_html(date('H:i', strtotime($status['attendance']['clock_in']))); ?>
                    <?php if($status['attendance']['clock_out']): ?> — خروج: <?php echo esc_html(date('H:i', strtotime($status['attendance']['clock_out']))); ?><?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- تاریخچه ۳۰ روز اخیر -->
        <div class="dc-card">
            <div class="dc-card-header"><h3 class="dc-heading-4">📅 تاریخچه ۳۰ روز اخیر</h3></div>
            <div class="dc-table-wrap">
                <table class="dc-table">
                    <thead><tr><th>تاریخ</th><th>ورود</th><th>خروج</th><th>مدت حضور</th></tr></thead>
                    <tbody>
                    <?php if(empty($history)): ?>
                    <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--dc-neutral-400);">رکوردی نیست</td></tr>
                    <?php else: foreach($history as $h): ?>
                    <tr>
                        <td><?php echo esc_html(Dental_Jalali::to_jalali($h['work_date'],'Y/m/d')); ?></td>
                        <td style="direction:ltr;"><?php echo esc_html(date('H:i',strtotime($h['clock_in']))); ?></td>
                        <td style="direction:ltr;"><?php echo $h['clock_out']?esc_html(date('H:i',strtotime($h['clock_out']))):'—'; ?></td>
                        <td><?php echo $h['total_minutes'] ? floor($h['total_minutes']/60).'س '.($h['total_minutes']%60).'د' : '—'; ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
        function dcClockAction(action){
            var fd = new FormData();
            fd.append('action','dental_'+action);
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce("dental_attendance_ajax")); ?>');
            fetch(ajaxurl, {method:'POST', body:fd}).then(()=>location.reload());
        }
        function dcBreakAction(action, type){
            var fd = new FormData();
            fd.append('action','dental_'+action);
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce("dental_attendance_ajax")); ?>');
            if(type) fd.append('break_type', type);
            fetch(ajaxurl, {method:'POST', body:fd}).then(()=>location.reload());
        }
        </script>
        <?php
    }

    // ─── تب مدیریتی: درخواست‌های در انتظار (فقط ادمین) ──────────
    private function render_pending(): void {
        $pending = Dental_Attendance_Manager::get_pending_leaves();
        ?>
        <div class="dc-card">
            <div class="dc-card-header"><h3 class="dc-heading-4">⏳ درخواست‌های در انتظار تأیید</h3></div>
            <div style="padding:0;">
                <?php if(empty($pending)): ?>
                <p style="text-align:center;color:var(--dc-neutral-400);padding:32px;">درخواست در انتظاری نیست</p>
                <?php else: foreach($pending as $l): ?>
                <div style="padding:16px 20px;border-bottom:1px solid var(--dc-neutral-100);">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                        <div>
                            <div style="font-weight:700;font-size:14px;"><?php echo esc_html($l['display_name']); ?></div>
                            <div style="font-size:12px;color:var(--dc-neutral-500);">
                                <?php echo $l['leave_type']==='hourly'?'ساعتی':'روزانه'; ?> —
                                <?php echo esc_html(Dental_Jalali::to_jalali($l['date_from'],'Y/m/d')); ?> تا <?php echo esc_html(Dental_Jalali::to_jalali($l['date_to'],'Y/m/d')); ?>
                                (<?php echo $l['leave_type']==='hourly' ? $l['hours'].' ساعت' : $l['days_count'].' روز'; ?>)
                            </div>
                            <?php if($l['reason']): ?><div style="font-size:12px;color:var(--dc-neutral-600);margin-top:4px;">💬 <?php echo esc_html($l['reason']); ?></div><?php endif; ?>
                        </div>
                        <form method="post" style="display:flex;gap:6px;align-items:center;">
                            <?php wp_nonce_field('dental_attendance'); ?>
                            <input type="hidden" name="leave_id" value="<?php echo $l['id']; ?>">
                            <input type="text" name="admin_note" placeholder="یادداشت (اختیاری)" class="dc-input" style="width:150px;height:34px;">
                            <button type="submit" name="dental_review_leave" onclick="this.form.review_status.value='approved'" class="dc-btn dc-btn-success dc-btn-sm">✅ تأیید</button>
                            <button type="submit" name="dental_review_leave" onclick="this.form.review_status.value='rejected'" class="dc-btn dc-btn-ghost dc-btn-sm" style="color:var(--dc-danger);">❌ رد</button>
                            <input type="hidden" name="review_status" value="approved">
                        </form>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        <?php
    }

    // ─── گزارش کامل مرخصی‌ها (فقط ادمین) ────────────────────────
    private function render_report(): void {
        $filter = sanitize_key($_GET['status'] ?? 'all');
        $all = Dental_Attendance_Manager::get_all_leaves($filter);
        $status_cfg = ['pending'=>['#F0A500','در انتظار'],'approved'=>['#2ECC9A','تأیید شده'],'rejected'=>['#E05252','رد شده']];
        ?>
        <div style="display:flex;gap:6px;margin-bottom:14px;">
            <?php foreach(['all'=>'همه','pending'=>'در انتظار','approved'=>'تأیید شده','rejected'=>'رد شده'] as $k=>$l): ?>
            <a href="<?php echo esc_url(add_query_arg(['tab'=>'report','status'=>$k])); ?>"
               style="padding:6px 14px;border-radius:20px;font-size:12px;text-decoration:none;font-family:Tahoma;
                      background:<?php echo $filter===$k?'var(--dc-primary)':'var(--dc-neutral-100)'; ?>;
                      color:<?php echo $filter===$k?'#fff':'var(--dc-neutral-700)'; ?>;"><?php echo esc_html($l); ?></a>
            <?php endforeach; ?>
        </div>
        <div class="dc-card">
            <div class="dc-table-wrap">
                <table class="dc-table">
                    <thead><tr><th>کارمند</th><th>نوع</th><th>از</th><th>تا</th><th>تعداد</th><th>وضعیت</th><th>بررسی‌کننده</th></tr></thead>
                    <tbody>
                    <?php if(empty($all)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:28px;color:var(--dc-neutral-400);">داده‌ای نیست</td></tr>
                    <?php else: foreach($all as $l): [$c,$l_label]=$status_cfg[$l['status']]??['#999',$l['status']]; ?>
                    <tr>
                        <td style="font-weight:600;"><?php echo esc_html($l['display_name']); ?></td>
                        <td><?php echo $l['leave_type']==='hourly'?'ساعتی':'روزانه'; ?></td>
                        <td><?php echo esc_html(Dental_Jalali::to_jalali($l['date_from'],'Y/m/d')); ?></td>
                        <td><?php echo esc_html(Dental_Jalali::to_jalali($l['date_to'],'Y/m/d')); ?></td>
                        <td><?php echo $l['leave_type']==='hourly' ? $l['hours'].' ساعت' : $l['days_count'].' روز'; ?></td>
                        <td><span style="background:<?php echo $c; ?>22;color:<?php echo $c; ?>;border-radius:10px;padding:2px 10px;font-size:11px;font-weight:700;"><?php echo esc_html($l_label); ?></span></td>
                        <td style="font-size:12px;color:var(--dc-neutral-500);"><?php echo esc_html($l['reviewer_name']?:'—'); ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ─── حضور همه کارکنان امروز (فقط ادمین) ──────────────────────
    private function render_all_attendance(): void {
        $rows = Dental_Attendance_Manager::get_all_today();
        ?>
        <div class="dc-card">
            <div class="dc-card-header"><h3 class="dc-heading-4">👥 حضور امروز همه کارکنان</h3></div>
            <div class="dc-table-wrap">
                <table class="dc-table">
                    <thead><tr><th>کارمند</th><th>ورود</th><th>خروج</th><th>مدت حضور</th><th>وضعیت</th></tr></thead>
                    <tbody>
                    <?php if(empty($rows)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:28px;color:var(--dc-neutral-400);">هنوز کسی ورود نزده</td></tr>
                    <?php else: foreach($rows as $r): ?>
                    <tr>
                        <td style="font-weight:600;"><?php echo esc_html($r['display_name']); ?></td>
                        <td style="direction:ltr;"><?php echo esc_html(date('H:i',strtotime($r['clock_in']))); ?></td>
                        <td style="direction:ltr;"><?php echo $r['clock_out']?esc_html(date('H:i',strtotime($r['clock_out']))):'—'; ?></td>
                        <td><?php echo $r['total_minutes'] ? floor($r['total_minutes']/60).'س '.($r['total_minutes']%60).'د' : 'در حال کار'; ?></td>
                        <td><?php echo $r['clock_out'] ? '<span style="color:var(--dc-neutral-500);">خارج شده</span>' : '<span style="color:var(--dc-accent-dark);">🟢 حاضر</span>'; ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ─── گزارش حضور و غیاب با بازه تاریخ دلخواه (فقط ادمین) ──────
    private function render_attendance_report(): void {
        $from_j = sanitize_text_field($_GET['from'] ?? Dental_Jalali::add_days(Dental_Jalali::today(), -30));
        $to_j   = sanitize_text_field($_GET['to']   ?? Dental_Jalali::today());
        $from_g = Dental_Jalali::to_gregorian($from_j) ?: date('Y-m-d', strtotime('-30 days'));
        $to_g   = Dental_Jalali::to_gregorian($to_j)   ?: current_time('Y-m-d');

        $staff_filter = (int)($_GET['staff'] ?? 0);
        $rows = Dental_Attendance_Manager::get_range_report($from_g, $to_g, $staff_filter);
        $staff_users = get_users(['role__in'=>['dental_admin','dental_doctor','dental_secretary','dental_financial','dental_assistant'],'fields'=>['ID','display_name']]);

        $total_minutes = array_sum(array_column($rows, 'total_minutes'));
        ?>
        <div class="dc-card" style="margin-bottom:16px;">
            <div class="dc-card-body" style="padding:16px;">
                <form method="get" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
                    <input type="hidden" name="page" value="dental-attendance">
                    <input type="hidden" name="tab" value="attendance_report">
                    <div class="dc-form-group" style="margin:0;">
                        <label class="dc-label">از تاریخ</label>
                        <input type="text" name="from" class="dc-input dc-datepicker" value="<?php echo esc_attr($from_j); ?>" dir="ltr" style="width:130px;">
                    </div>
                    <div class="dc-form-group" style="margin:0;">
                        <label class="dc-label">تا تاریخ</label>
                        <input type="text" name="to" class="dc-input dc-datepicker" value="<?php echo esc_attr($to_j); ?>" dir="ltr" style="width:130px;">
                    </div>
                    <div class="dc-form-group" style="margin:0;">
                        <label class="dc-label">کارمند</label>
                        <select name="staff" class="dc-select" style="width:150px;">
                            <option value="0">همه</option>
                            <?php foreach($staff_users as $su): ?>
                            <option value="<?php echo $su->ID; ?>" <?php selected($staff_filter,$su->ID); ?>><?php echo esc_html($su->display_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="dc-btn dc-btn-primary">🔍 اعمال فیلتر</button>
                </form>
            </div>
        </div>

        <div class="dc-card">
            <div class="dc-card-header">
                <h3 class="dc-heading-4">📊 گزارش حضور (<?php echo count($rows); ?> رکورد — مجموع <?php echo floor($total_minutes/60); ?> ساعت)</h3>
            </div>
            <div class="dc-table-wrap">
                <table class="dc-table">
                    <thead><tr><th>تاریخ</th><th>کارمند</th><th>ورود</th><th>خروج</th><th>مدت حضور</th></tr></thead>
                    <tbody>
                    <?php if(empty($rows)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:28px;color:var(--dc-neutral-400);">رکوردی در این بازه نیست</td></tr>
                    <?php else: foreach($rows as $r): ?>
                    <tr>
                        <td><?php echo esc_html(Dental_Jalali::to_jalali($r['work_date'],'Y/m/d')); ?></td>
                        <td style="font-weight:600;"><?php echo esc_html($r['display_name']); ?></td>
                        <td style="direction:ltr;"><?php echo esc_html(date('H:i',strtotime($r['clock_in']))); ?></td>
                        <td style="direction:ltr;"><?php echo $r['clock_out']?esc_html(date('H:i',strtotime($r['clock_out']))):'—'; ?></td>
                        <td><?php echo $r['total_minutes'] ? floor($r['total_minutes']/60).'س '.($r['total_minutes']%60).'د' : 'در حال کار'; ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ─── تنظیم سقف زمان ناهار/استراحت به تفکیک نقش (فقط ادمین) ──────
    private function render_break_settings(): void {
        $limits = Dental_Attendance_Manager::get_break_limits();
        $role_labels = [
            'dental_admin'     => 'مدیر کلینیک',
            'dental_doctor'    => 'دندانپزشک',
            'dental_secretary' => 'منشی / پذیرش',
            'dental_financial' => 'مسئول مالی',
            'dental_assistant' => 'دستیار',
        ];
        ?>
        <div class="dc-card" style="max-width:600px;">
            <div class="dc-card-header"><h3 class="dc-heading-4">⏱️ سقف زمانی ناهار و استراحت هر گروه</h3></div>
            <div class="dc-card-body">
                <p style="font-size:12px;color:var(--dc-neutral-500);margin-bottom:16px;">
                    اگه کسی بیشتر از این زمان‌ها روی ناهار/استراحت بمونه، توی ویجت «لیست پرسنل امروز» با رنگ قرمز و علامت هشدار مشخص می‌شه.
                </p>
                <form method="post">
                    <?php wp_nonce_field('dental_attendance'); ?>
                    <table class="dc-table">
                        <thead><tr><th>نقش</th><th>ناهار (دقیقه)</th><th>استراحت (دقیقه)</th></tr></thead>
                        <tbody>
                        <?php foreach($role_labels as $slug=>$label): ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo esc_html($label); ?></td>
                            <td><input type="number" name="lunch_<?php echo esc_attr($slug); ?>" class="dc-input" min="0" max="180" value="<?php echo (int)($limits[$slug]['lunch']??30); ?>" style="width:80px;"></td>
                            <td><input type="number" name="break_<?php echo esc_attr($slug); ?>" class="dc-input" min="0" max="120" value="<?php echo (int)($limits[$slug]['break']??10); ?>" style="width:80px;"></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="margin-top:16px;">
                        <button type="submit" name="dental_save_break_limits" class="dc-btn dc-btn-primary">💾 ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    // ─── سهمیه مرخصی سالانه قابل‌تنظیم برای هر نفر جدا ────────────
    private function render_leave_quota(): void {
        $users = get_users(['role__in'=>['dental_admin','dental_doctor','dental_secretary','dental_financial','dental_assistant'], 'fields'=>['ID','display_name']]);
        $default_quota = (float)get_option('dental_default_leave_quota', 20);
        ?>
        <div class="dc-card" style="max-width:600px;">
            <div class="dc-card-header"><h3 class="dc-heading-4">📅 سهمیه مرخصی سالانه هر نفر (روز)</h3></div>
            <div class="dc-card-body">
                <p style="font-size:12px;color:var(--dc-neutral-500);margin-bottom:16px;">
                    پیش‌فرض همه <?php echo $default_quota; ?> روزه — برای هرکسی که سهمیه‌ی متفاوتی داره (مثلاً کمتر)، عدد رو عوض کنید.
                </p>
                <form method="post">
                    <?php wp_nonce_field('dental_leave_quota'); ?>
                    <table class="dc-table">
                        <thead><tr><th>نام</th><th>سهمیه امسال (روز)</th><th>مصرف‌شده</th><th>باقی‌مانده</th></tr></thead>
                        <tbody>
                        <?php foreach($users as $u):
                            $bal = Dental_Attendance_Manager::get_leave_balance($u->ID);
                        ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo esc_html($u->display_name); ?></td>
                            <td><input type="number" step="0.5" name="quota[<?php echo $u->ID; ?>]" class="dc-input" value="<?php echo $bal['quota']; ?>" style="width:80px;"></td>
                            <td style="font-size:12px;color:var(--dc-neutral-500);"><?php echo $bal['used']; ?> روز</td>
                            <td style="font-size:12px;font-weight:700;color:<?php echo $bal['remaining']<3?'var(--dc-danger)':'var(--dc-accent-dark)'; ?>;"><?php echo $bal['remaining']; ?> روز</td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="margin-top:16px;">
                        <button type="submit" name="dental_save_leave_quota" class="dc-btn dc-btn-primary">💾 ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    // ─── لاگ کامل استفاده از ناهار/استراحت — چه‌کسی کِی چقدر ────────
    private function render_break_log(): void {
        global $wpdb;
        $logs = $wpdb->get_results(
            "SELECT b.*, u.display_name FROM {$wpdb->prefix}dental_breaks b
             LEFT JOIN {$wpdb->users} u ON b.user_id=u.ID
             ORDER BY b.start_time DESC LIMIT 100", ARRAY_A
        );
        ?>
        <div class="dc-card">
            <div class="dc-card-header"><h3 class="dc-heading-4">🍽️ لاگ استفاده از ناهار و استراحت (۱۰۰ مورد اخیر)</h3></div>
            <div class="dc-table-wrap">
                <table class="dc-table">
                    <thead><tr><th>تاریخ</th><th>نام</th><th>نوع</th><th>شروع</th><th>پایان</th><th>مدت</th></tr></thead>
                    <tbody>
                    <?php if (empty($logs)): ?>
                    <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--dc-neutral-400);">لاگی ثبت نشده</td></tr>
                    <?php else: foreach($logs as $l):
                        $mins = $l['end_time'] ? round((strtotime($l['end_time'])-strtotime($l['start_time']))/60) : null;
                    ?>
                    <tr>
                        <td style="font-size:12px;"><?php echo esc_html(Dental_Jalali::to_jalali($l['work_date'],'Y/m/d')); ?></td>
                        <td style="font-weight:600;"><?php echo esc_html($l['display_name']); ?></td>
                        <td><?php echo $l['break_type']==='lunch' ? '🍽️ ناهار' : '🛋️ استراحت'; ?></td>
                        <td style="font-size:12px;"><?php echo esc_html(date('H:i', strtotime($l['start_time']))); ?></td>
                        <td style="font-size:12px;"><?php echo $l['end_time'] ? esc_html(date('H:i', strtotime($l['end_time']))) : '<span style="color:var(--dc-accent-dark);">— هنوز بازه</span>'; ?></td>
                        <td style="font-weight:700;"><?php echo $mins!==null ? $mins.' دقیقه' : '—'; ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
}
