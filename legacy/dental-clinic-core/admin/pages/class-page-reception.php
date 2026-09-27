<?php
defined('ABSPATH') || exit;

class Dental_Page_Reception {

    public function maybe_handle_post(): void {
        if (isset($_POST['dental_checkin']) && check_admin_referer('dental_checkin')) {
            $this->do_checkin();
        }
        if (isset($_GET['quick_checkin']) && check_admin_referer('dental_quick_checkin_' . $_GET['quick_checkin'])) {
            $appt_id = (int)$_GET['quick_checkin'];
            if (class_exists('Dental_Booking_Appointment')) {
                global $wpdb;
                $appt = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}dental_appointments WHERE id=%d", $appt_id
                ), ARRAY_A);
                if ($appt) {
                    Dental_Reception_Manager::check_in((int)$appt['patient_id'], (int)$appt['doctor_id'], 'نوبت رزروشده', $appt_id);
                    if (class_exists('Dental_Audit_Log')) {
                        $p_name = get_the_title((int)$appt['patient_id']);
                        Dental_Audit_Log::log('reception_checkin', "پذیرش بیمار «{$p_name}» (از روی نوبت رزروشده)",
                            ['entity_type'=>'patient','entity_id'=>(int)$appt['patient_id']]);
                    }
                }
            }
            wp_safe_redirect(remove_query_arg(['quick_checkin','_wpnonce'])); exit;
        }
        if (isset($_GET['status']) && isset($_GET['qid']) && check_admin_referer('dental_queue_status_' . $_GET['qid'])) {
            $qid        = (int)$_GET['qid'];
            $new_status = sanitize_key($_GET['status']);
            Dental_Reception_Manager::update_status($qid, $new_status);
            if ($new_status === 'done' && !empty($_GET['goto_ledger'])) {
                wp_safe_redirect(admin_url('admin.php?page=dental-financial&section=ledger&tab=daily&open_patient=' . (int)$_GET['goto_ledger']));
                exit;
            }
            wp_safe_redirect(remove_query_arg(['status','qid','_wpnonce','goto_ledger'])); exit;
        }
    }

    public function render(): void {
        // پرینت لیست روزانه — قبل از رندر معمولی
        if (isset($_GET['print_queue'])) {
            $this->render_print();
            return;
        }

        $queue     = Dental_Reception_Manager::get_today_queue();
        $patients  = get_posts(['post_type'=>'dental_patient','posts_per_page'=>-1,'post_status'=>'publish','orderby'=>'title','order'=>'ASC']);
        $doctors   = get_users(['role__in'=>['dental_doctor'],'fields'=>['ID','display_name']]);
        $today_j   = Dental_Jalali::today();

        // ─── نوبت‌های از‌قبل‌رزروشده امروز که هنوز پذیرش (چک‌این) نشدن ──
        // این دقیقاً همون نوبتی‌ست که خودِ بیمار از پنل کاربری‌اش گرفته
        // یا ادمین/منشی از پلاگین نوبت‌دهی برایش ثبت کرده.
        $booked_today = [];
        if (class_exists('Dental_Booking_Appointment')) {
            $already_checked_in_appt_ids = array_filter(array_column($queue, 'appointment_id'));
            $today_appts = Dental_Booking_Appointment::get_day_appointments(current_time('Y-m-d'));
            foreach ($today_appts as $a) {
                if (in_array($a['status'], ['confirmed','pending']) && !in_array((int)$a['id'], $already_checked_in_appt_ids)) {
                    $booked_today[] = $a;
                }
            }
        }

        $status_cfg = [
            'waiting'     => ['#F0A500','⏳','در انتظار'],
            'in_progress' => ['#1A6B8A','🩺','در حال معاینه'],
            'done'        => ['#2ECC9A','✅','انجام شد'],
        ];
        ?>
        <div class="dental-admin-wrap">
            <?php if (isset($_GET['error']) && $_GET['error']==='mobile_required'): ?>
            <div style="background:var(--dc-danger-light);border:1px solid var(--dc-danger);color:var(--dc-danger);border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:13px;">
                ⚠️ برای پذیرش بیمار کاملاً جدید، وارد کردن شماره موبایل اجباری است (برای ساخت حساب کاربری بیمار).
            </div>
            <?php endif; ?>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
                <div>
                    <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;">
                        <i data-lucide="user-check" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                        پذیرش امروز
                    </h1>
                    <p class="dc-text-muted"><?php echo esc_html($today_j); ?></p>
                </div>
                <div style="display:flex;gap:8px;">
                    <?php if (class_exists('Dental_Inventory_Manager')): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&inv_view=request')); ?>" class="dc-btn dc-btn-ghost">📢 درخواست کالا</a>
                    <?php endif; ?>
                    <button type="button" onclick="printQueue()" class="dc-btn dc-btn-secondary">
                        <i data-lucide="printer" style="width:14px;height:14px;"></i> پرینت لیست
                    </button>
                </div>
            </div>

            <?php if (class_exists('Dental_Shift_Scheduler')):
                $doctors_today = Dental_Shift_Scheduler::get_doctors();
                $room_lines = [];
                foreach ($doctors_today as $d) {
                    $r = Dental_Shift_Scheduler::get_doctor_room_today($d->ID);
                    if ($r) $room_lines[] = esc_html($d->display_name) . ' — اتاق ' . (int)$r['room_number'];
                }
                if (!empty($room_lines)):
            ?>
            <div style="background:var(--dc-primary-light);border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:12px;color:var(--dc-primary);display:flex;flex-wrap:wrap;gap:14px;">
                🚪 <b>اتاق پزشکان امروز:</b>
                <?php echo implode(' — ', $room_lines); ?>
            </div>
            <?php endif; endif; ?>

            <!-- جستجوی سریع پرونده — نمایش لحظه‌ای بدون رفرش صفحه -->
            <div style="display:grid;grid-template-columns:340px 1fr;gap:16px;margin-bottom:20px;align-items:start;">
                <div class="dc-card">
                    <div class="dc-card-header"><h3 class="dc-heading-4">🔍 جستجوی سریع پرونده</h3></div>
                    <div class="dc-card-body">
                        <input type="text" id="dc-quick-search" class="dc-input" placeholder="نام یا موبایل بیمار را تایپ کنید..." style="margin-bottom:10px;" oninput="dcQuickSearch(this.value)">
                        <div id="dc-quick-search-results" style="display:flex;flex-direction:column;gap:4px;max-height:400px;overflow-y:auto;"></div>
                    </div>
                </div>
                <div class="dc-card" style="min-height:200px;">
                    <div id="dc-quick-view-panel" style="display:flex;align-items:center;justify-content:center;height:200px;color:var(--dc-neutral-400);font-size:13px;">
                        یک بیمار را از لیست کناری انتخاب کنید تا اطلاعاتش اینجا نمایش داده شود
                    </div>
                </div>
            </div>

            <!-- نوبت‌های از‌قبل‌رزروشده امروز — پذیرش تک‌کلیکی -->
            <?php if (!empty($booked_today)): ?>
            <div class="dc-card" style="margin-bottom:20px;border-top:3px solid var(--dc-accent-dark);">
                <div class="dc-card-header">
                    <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                        <i data-lucide="calendar-check" style="width:16px;height:16px;color:var(--dc-accent-dark);"></i>
                        نوبت‌های رزروشده امروز که هنوز نیامده‌اند
                        <span class="dc-badge dc-badge-success"><?php echo count($booked_today); ?> نفر</span>
                    </h3>
                </div>
                <div style="padding:0;">
                    <?php foreach ($booked_today as $a): ?>
                    <div style="display:flex;align-items:center;gap:14px;padding:12px 20px;border-bottom:1px solid var(--dc-neutral-100);flex-wrap:wrap;">
                        <div style="min-width:50px;font-weight:700;color:var(--dc-primary);direction:ltr;"><?php echo esc_html(substr($a['start_time'],0,5)); ?></div>
                        <div style="flex:1;min-width:160px;">
                            <div style="font-size:13px;font-weight:600;"><?php echo esc_html($a['patient_name']); ?></div>
                            <div style="font-size:11px;color:var(--dc-neutral-500);"><?php echo esc_html($a['service_title']??'—'); ?> — دکتر <?php echo esc_html($a['doctor_name']); ?></div>
                        </div>
                        <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('quick_checkin',$a['id']),'dental_quick_checkin_'.$a['id'])); ?>"
                           class="dc-btn dc-btn-success dc-btn-sm">
                            <i data-lucide="check" style="width:12px;height:12px;"></i> بیمار رسید — پذیرش
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- فرم پذیرش سریع -->
            <div class="dc-card" style="margin-bottom:20px;">
                <div class="dc-card-header">
                    <h3 class="dc-heading-4">➕ پذیرش بیمار جدید (بدون نوبت قبلی یا جستجوی دستی)</h3>
                    <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;">
                        <input type="checkbox" id="rc-is-new-patient" onchange="rcToggleNewPatient(this.checked)">
                        بیمار کاملاً جدید است (پرونده ندارد)
                    </label>
                </div>
                <div class="dc-card-body">
                    <form method="post">
                        <?php wp_nonce_field('dental_checkin'); ?>
                        <div style="display:grid;grid-template-columns:2fr 1.5fr 2fr auto;gap:10px;align-items:end;">
                            <div class="dc-form-group" style="margin:0;" id="rc-existing-patient-wrap">
                                <label class="dc-label">بیمار</label>
                                <select name="patient_id" id="rc-patient" class="dc-select" onchange="checkTodayAppt(this.value)">
                                    <option value="">جستجو و انتخاب بیمار...</option>
                                    <?php foreach($patients as $p): ?>
                                    <option value="<?php echo $p->ID; ?>"><?php echo esc_html($p->post_title); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="dc-form-group" style="margin:0;display:none;" id="rc-new-patient-wrap">
                                <label class="dc-label">نام و نام‌خانوادگی بیمار جدید</label>
                                <input type="text" name="new_patient_name" id="rc-new-name" class="dc-input" placeholder="نام کامل...">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">پزشک</label>
                                <select name="doctor_id" id="rc-doctor" class="dc-select" required>
                                    <option value="">انتخاب پزشک...</option>
                                    <?php foreach($doctors as $d): ?>
                                    <option value="<?php echo $d->ID; ?>"><?php echo esc_html($d->display_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label" id="rc-reason-label">دلیل مراجعه (اختیاری)</label>
                                <input type="text" name="reason" id="rc-reason-input" class="dc-input" placeholder="مثال: درد دندان، چکاپ...">
                                <input type="hidden" name="appointment_id" id="rc-appt-id" value="">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">&nbsp;</label>
                                <button type="submit" name="dental_checkin" class="dc-btn dc-btn-primary" style="height:42px;width:100%;flex-shrink:0;white-space:nowrap;">
                                    <i data-lucide="check" style="width:14px;height:14px;"></i> پذیرش
                                </button>
                            </div>
                        </div>
                        <!-- فیلد موبایل بیمار جدید — ردیف دوم، فقط وقتی «بیمار جدید» فعاله -->
                        <div id="rc-new-mobile-wrap" style="display:none;margin-top:10px;gap:10px;">
                            <div style="flex:1;max-width:260px;">
                                <label class="dc-label">شماره موبایل (اجباری — برای ساخت حساب کاربری بیمار)</label>
                                <input type="text" name="new_patient_mobile" id="rc-new-mobile" class="dc-input" placeholder="09xxxxxxxxx" dir="ltr">
                            </div>
                            <div style="max-width:140px;">
                                <label class="dc-label">کد ملی (اختیاری)</label>
                                <input type="text" name="new_patient_national_id" class="dc-input" placeholder="۱۰ رقم" dir="ltr" maxlength="10">
                            </div>
                        </div>
                        <?php if (class_exists('Dental_Insurance_Manager')):
                            $ins_companies_rc = Dental_Insurance_Manager::get_companies(true);
                        ?>
                        <div id="rc-new-insurance-wrap" style="display:none;margin-top:10px;gap:10px;flex-wrap:wrap;">
                            <div style="max-width:200px;">
                                <label class="dc-label">بیمه (اختیاری)</label>
                                <select name="new_patient_insurance_id" class="dc-select">
                                    <option value="0">بدون بیمه</option>
                                    <?php foreach($ins_companies_rc as $ic): ?>
                                    <option value="<?php echo $ic['id']; ?>"><?php echo esc_html($ic['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="max-width:200px;">
                                <label class="dc-label">شماره بیمه‌نامه (اختیاری)</label>
                                <input type="text" name="new_patient_insurance_policy" class="dc-input">
                            </div>
                        </div>
                        <?php endif; ?>
                        <div id="rc-appt-hint" style="display:none;margin-top:10px;background:var(--dc-accent-light);border-radius:8px;padding:8px 12px;font-size:12px;color:var(--dc-accent-dark);"></div>
                    </form>
                </div>
            </div>

            <!-- صف امروز -->
            <div class="dc-card">
                <div class="dc-card-header">
                    <h3 class="dc-heading-4">📋 صف امروز (<?php echo count($queue); ?> نفر)</h3>
                </div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>ساعت پذیرش</th><th>بیمار</th><th>موبایل</th><th>پزشک</th><th>دلیل مراجعه</th><th>وضعیت</th><th>عملیات</th></tr></thead>
                        <tbody>
                        <?php if(empty($queue)): ?>
                        <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--dc-neutral-400);">هنوز بیماری پذیرش نشده</td></tr>
                        <?php else: foreach($queue as $q):
                            [$scolor,$sicon,$slabel] = $status_cfg[$q['status']] ?? ['#999','•',''];
                            $wait_min = $q['status']==='waiting' ? round((time()-strtotime($q['checked_in_at']))/60) : null;
                        ?>
                        <tr>
                            <td style="direction:ltr;font-weight:600;"><?php echo esc_html(date('H:i',strtotime($q['checked_in_at']))); ?></td>
                            <td style="font-weight:600;"><?php echo esc_html($q['patient_name']); ?></td>
                            <td style="direction:ltr;font-size:12px;"><?php echo esc_html($q['patient_mobile']??''); ?></td>
                            <td><?php echo esc_html($q['doctor_name']); ?></td>
                            <td style="font-size:12px;color:var(--dc-neutral-600);"><?php echo esc_html($q['reason']?:'—'); ?></td>
                            <td>
                                <span style="background:<?php echo $scolor; ?>22;color:<?php echo $scolor; ?>;border-radius:10px;padding:2px 10px;font-size:11px;font-weight:700;">
                                    <?php echo $sicon; ?> <?php echo esc_html($slabel); ?>
                                </span>
                                <?php if($wait_min!==null): ?>
                                <div style="font-size:10px;color:var(--dc-neutral-400);margin-top:2px;"><?php echo $wait_min; ?> دقیقه در انتظار</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($q['status']!=='done'): ?>
                                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(['qid'=>$q['id'],'status'=>'cancelled']),'dental_queue_status_'.$q['id'])); ?>"
                                   class="dc-btn dc-btn-ghost dc-btn-sm" onclick="return confirm('این بیمار از صف حذف شود؟')">
                                    <i data-lucide="x" style="width:12px;height:12px;color:var(--dc-danger);"></i>
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo esc_url(admin_url("admin.php?page=dental-patients&action=profile&id={$q['patient_id']}")); ?>"
                                   class="dc-btn dc-btn-ghost dc-btn-sm">پرونده</a>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script>
        function rcToggleNewPatient(isNew){
            document.getElementById('rc-existing-patient-wrap').style.display = isNew ? 'none' : 'block';
            document.getElementById('rc-new-patient-wrap').style.display     = isNew ? 'block' : 'none';
            document.getElementById('rc-new-mobile-wrap').style.display     = isNew ? 'flex' : 'none';
            var insWrap = document.getElementById('rc-new-insurance-wrap');
            if (insWrap) insWrap.style.display = isNew ? 'flex' : 'none';
            document.getElementById('rc-patient').required   = !isNew;
            document.getElementById('rc-new-name').required  = isNew;
            document.getElementById('rc-new-mobile').required= isNew; // چون باید کاربر وردپرس هم ساخته بشه
            document.getElementById('rc-appt-hint').style.display = 'none';
        }
        function checkTodayAppt(patientId){
            var hint = document.getElementById('rc-appt-hint');
            var apptIdInput = document.getElementById('rc-appt-id');
            var doctorSel = document.getElementById('rc-doctor');
            apptIdInput.value = '';
            if(!patientId){ hint.style.display='none'; return; }

            fetch(ajaxurl + '?action=dental_check_today_appt&patient_id=' + patientId + '&_wpnonce=<?php echo wp_create_nonce("dental_check_appt"); ?>')
            .then(r=>r.json()).then(function(d){
                if(d.success && d.data){
                    hint.style.display='block';
                    hint.innerHTML = '✅ این بیمار امروز ساعت <b>'+d.data.time+'</b> نوبت رزروشده دارد. پزشک به‌صورت خودکار انتخاب شد.';
                    apptIdInput.value = d.data.appointment_id;
                    doctorSel.value = d.data.doctor_id;
                } else {
                    hint.style.display='block';
                    hint.innerHTML = 'ℹ️ این بیمار نوبت ازقبل‌رزروشده‌ای برای امروز ندارد (پذیرش به‌عنوان مراجعه بدون نوبت ثبت می‌شود).';
                }
            });
        }
        function printQueue(){
            var base = '<?php echo esc_js(admin_url("admin.php")); ?>';
            var url = base + '?page=dental-reception' + String.fromCharCode(38) + 'print_queue=1';
            // نکته مهم: اسم ثابت '_blank' باعث می‌شد بعضی مرورگرها همون
            // کانتکست/تب قبلی رو دوباره استفاده کنن و لحظه اول محتوای
            // کهنه نشون بدن تا ناوبری کامل بشه (که با رفرش دستی حل می‌شد).
            // با یه اسم یکتا برای هر کلیک، همیشه واقعاً یه تب تازه باز می‌شه.
            window.open(url, 'dental_print_' + Date.now());
        }

        var dcRcNonce = '<?php echo esc_js(wp_create_nonce("dental_workspace")); ?>';
        var dcRcAjax  = '<?php echo esc_js(admin_url("admin-ajax.php")); ?>';
        var dcSearchTimer = null;

        function dcQuickSearch(q){
            clearTimeout(dcSearchTimer);
            var box = document.getElementById('dc-quick-search-results');
            if(q.trim().length < 2){ box.innerHTML=''; return; }
            dcSearchTimer = setTimeout(function(){
                fetch(dcRcAjax + '?action=dental_search_patients&q=' + encodeURIComponent(q) + '&_wpnonce=' + dcRcNonce)
                .then(r=>r.json()).then(function(res){
                    if(!res.success || !res.data.length){ box.innerHTML = '<p style="font-size:12px;color:#A0B4C0;padding:8px;">نتیجه‌ای یافت نشد</p>'; return; }
                    box.innerHTML = res.data.map(function(p){
                        return '<div onclick="dcLoadQuickView('+p.ID+')" style="padding:9px 12px;border-radius:8px;cursor:pointer;font-size:13px;border:1px solid var(--dc-neutral-100);" onmouseover="this.style.background=\'var(--dc-primary-light)\'" onmouseout="this.style.background=\'#fff\'">'+
                            '<b>'+p.post_title+'</b> <span style="color:#A0B4C0;font-size:11px;">'+(p.mobile||'')+'</span></div>';
                    }).join('');
                });
            }, 350);
        }

        function dcLoadQuickView(patientId){
            var panel = document.getElementById('dc-quick-view-panel');
            panel.style.height = 'auto';
            panel.innerHTML = '<div style="padding:40px;text-align:center;color:#A0B4C0;">در حال بارگذاری...</div>';
            fetch(dcRcAjax + '?action=dental_quick_patient_view&patient_id=' + patientId + '&_wpnonce=' + dcRcNonce)
            .then(r=>r.json()).then(function(res){
                if(res.success) panel.innerHTML = res.data.html;
                else panel.innerHTML = '<p style="padding:20px;color:#E05252;">خطا در بارگذاری اطلاعات</p>';
            });
        }

        if(typeof lucide!=="undefined") lucide.createIcons();
        </script>
        <?php Dental_Admin::render_quick_view_assets(); ?>
        <script>if(typeof lucide!=="undefined") lucide.createIcons();</script>
        <?php
    }

    private function do_checkin(): void {
        $patient_id = (int)($_POST['patient_id'] ?? 0);
        $doctor_id  = (int)($_POST['doctor_id'] ?? 0);
        $reason     = sanitize_text_field($_POST['reason'] ?? '');
        $appt_id    = (int)($_POST['appointment_id'] ?? 0);

        // ─── بیمار کاملاً جدید — از متد مشترک استفاده می‌شه (هم‌الگو با نوبت‌دهی) ──
        $new_name   = sanitize_text_field($_POST['new_patient_name'] ?? '');
        $new_mobile = sanitize_text_field($_POST['new_patient_mobile'] ?? '');
        if (!$patient_id && $new_name) {
            if (!$new_mobile) {
                wp_safe_redirect(admin_url('admin.php?page=dental-reception&error=mobile_required')); exit;
            }
            $result = Dental_Reception_Manager::create_or_get_patient(
                $new_name, $new_mobile,
                $_POST['new_patient_national_id'] ?? '',
                (int)($_POST['new_patient_insurance_id'] ?? 0),
                $_POST['new_patient_insurance_policy'] ?? ''
            );
            if ($result['success']) {
                $patient_id = $result['patient_id'];
            }
        }

        if ($patient_id && $doctor_id) {
            Dental_Reception_Manager::check_in($patient_id, $doctor_id, $reason, $appt_id);

            if (class_exists('Dental_Audit_Log')) {
                $p_name = get_the_title($patient_id);
                $doc_name = get_userdata($doctor_id) ? get_userdata($doctor_id)->display_name : '';
                Dental_Audit_Log::log('reception_checkin', "پذیرش بیمار «{$p_name}» نزد دکتر {$doc_name}" . ($reason ? " — علت: {$reason}" : ''),
                    ['entity_type'=>'patient','entity_id'=>$patient_id]);
            }

            // ─── اتصال به پلاگین نوبت‌دهی — رفع باگ مهم: تا الان، وقتی
            // بیماری مستقیم (بدون نوبت قبلی آنلاین) توی پذیرش قبول
            // می‌شد، هیچ رکوردی توی جدول نوبت‌دهی ساخته نمی‌شد؛ در
            // نتیجه تقویم/آمار پلاگین نوبت‌دهی این ویزیت‌ها رو اصلاً
            // نمی‌دید. الان اگه این پذیرش «بدون نوبت قبلی» بوده
            // ($appt_id خالیه)، یه رکورد نوبت با وضعیت «انجام‌شده»
            // خودکار براش ساخته می‌شه — همون لحظه، همون پزشک.
            if (!$appt_id && class_exists('Dental_Booking_Appointment')) {
                global $wpdb;
                $now = current_time('mysql');
                $wpdb->insert($wpdb->prefix.'dental_appointments', [
                    'patient_id'       => $patient_id,
                    'doctor_id'        => $doctor_id,
                    'shift_id'         => 0,
                    'appt_date'        => current_time('Y-m-d'),
                    'appt_date_jalali' => Dental_Jalali::today(),
                    'start_time'       => current_time('H:i:s'),
                    'end_time'         => date('H:i:s', strtotime(current_time('H:i:s')) + 1800),
                    'duration'         => 30,
                    'service_type'     => $reason ?: 'پذیرش مستقیم',
                    'notes'            => 'ثبت خودکار از پذیرش (بدون نوبت آنلاین قبلی)',
                    // نکته: مستقیم status=done درج می‌شه (نه از طریق
                    // Dental_Booking_Appointment::create) چون اون تابع
                    // خودکار پیامک «نوبتتون تأیید شد» می‌فرسته — که برای
                    // بیماری که همین الان فیزیکی حاضره، اضافه/گیج‌کننده‌ست
                    'status'           => 'done',
                    'confirmed_at'     => $now,
                    'confirmed_by'     => get_current_user_id(),
                    'created_by'       => get_current_user_id(),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ], ['%d','%d','%d','%s','%s','%s','%s','%d','%s','%s','%s','%s','%d','%d','%s','%s']);
            }
        }
        wp_safe_redirect(admin_url('admin.php?page=dental-reception&saved=1'));
        exit;
    }

    // ─── پرینت لیست صف امروز ─────────────────────────────────────
    public function render_print(): void {
        $queue  = Dental_Reception_Manager::get_today_queue();
        $clinic = get_option('dental_clinic_name', get_bloginfo('name'));
        $today_j = Dental_Jalali::today();
        $status_labels = ['waiting'=>'در انتظار','in_progress'=>'در حال معاینه','done'=>'انجام شد'];
        ?>
        <!DOCTYPE html>
        <html lang="fa" dir="rtl">
        <head>
        <meta charset="UTF-8">
        <title>لیست پذیرش — <?php echo esc_html($today_j); ?></title>
        <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:Tahoma,Arial,sans-serif;direction:rtl;font-size:13px;color:#1A2733;}
        .page{width:210mm;padding:15mm 18mm;margin:0 auto;}
        .header{display:flex;justify-content:space-between;border-bottom:2px solid #1A6B8A;padding-bottom:10px;margin-bottom:16px;}
        .clinic{font-size:18px;font-weight:700;color:#1A6B8A;}
        table{width:100%;border-collapse:collapse;font-size:12px;}
        th{background:#1A6B8A;color:#fff;padding:8px;text-align:right;}
        td{padding:7px 8px;border-bottom:1px solid #EEF2F5;}
        @media print{.no-print{display:none!important;}}
        </style>
        </head>
        <body>
            <div class="no-print" style="position:fixed;top:16px;left:16px;">
                <button onclick="window.print()" style="background:#1A6B8A;color:#fff;border:none;border-radius:8px;padding:10px 20px;font-family:Tahoma;cursor:pointer;">🖨️ پرینت</button>
            </div>
            <div class="page">
                <div class="header">
                    <div class="clinic">🦷 <?php echo esc_html($clinic); ?></div>
                    <div>لیست پذیرش — <?php echo esc_html($today_j); ?></div>
                </div>
                <table>
                    <thead><tr><th>ساعت</th><th>بیمار</th><th>پزشک</th><th>دلیل مراجعه</th><th>وضعیت</th></tr></thead>
                    <tbody>
                    <?php foreach($queue as $q): ?>
                    <tr>
                        <td style="direction:ltr;"><?php echo esc_html(date('H:i',strtotime($q['checked_in_at']))); ?></td>
                        <td><?php echo esc_html($q['patient_name']); ?></td>
                        <td><?php echo esc_html($q['doctor_name']); ?></td>
                        <td><?php echo esc_html($q['reason']?:'—'); ?></td>
                        <td><?php echo esc_html($status_labels[$q['status']]??$q['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </body>
        </html>
        <?php
    }
}
