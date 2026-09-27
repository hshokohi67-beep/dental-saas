<?php
defined('ABSPATH') || exit;

class Dental_Page_Booking_List {

    public function render(): void {
        global $wpdb;

        $action = sanitize_key($_GET['action'] ?? 'list');

        if ($action === 'new') {
            $this->render_new_form();
            return;
        }

        // فیلترها
        $filter_status = sanitize_key($_GET['status'] ?? 'all');
        $filter_doctor = (int)($_GET['doctor'] ?? 0);
        $search        = sanitize_text_field($_GET['s'] ?? '');

        // ─── دندانپزشک فقط نوبت‌های خودش را می‌بیند ─────────────────
        $cu = wp_get_current_user();
        $is_doctor_only = in_array('dental_doctor', (array)$cu->roles) && !current_user_can('manage_options');
        if ($is_doctor_only) $filter_doctor = $cu->ID;

        $where = "WHERE 1=1";
        $params = [];

        if ($filter_status !== 'all') {
            $where .= " AND a.status=%s";
            $params[] = $filter_status;
        }
        if ($filter_doctor) {
            $where .= " AND a.doctor_id=%d";
            $params[] = $filter_doctor;
        }
        if ($search) {
            $where .= " AND p.post_title LIKE %s";
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }

        $sql = "SELECT a.*, p.post_title as patient_name, pm.meta_value as patient_mobile,
                       u.display_name as doctor_name, s.title as service_title, s.color as service_color
                FROM {$wpdb->prefix}dental_appointments a
                LEFT JOIN {$wpdb->posts} p ON a.patient_id=p.ID
                LEFT JOIN {$wpdb->postmeta} pm ON p.ID=pm.post_id AND pm.meta_key='_patient_mobile'
                LEFT JOIN {$wpdb->users} u ON a.doctor_id=u.ID
                LEFT JOIN {$wpdb->prefix}dental_services s ON a.service_type=s.id
                $where
                ORDER BY a.appt_date DESC, a.start_time DESC LIMIT 100";

        $appts = empty($params)
            ? $wpdb->get_results($sql, ARRAY_A)
            : $wpdb->get_results($wpdb->prepare($sql, ...$params), ARRAY_A);

        $doctors = get_users(['role__in'=>['dental_doctor'],'fields'=>['ID','display_name']]);

        $status_cfg = [
            'pending'   => ['#F0A500','در انتظار'],
            'confirmed' => ['#2ECC9A','تأیید شده'],
            'cancelled' => ['#E05252','لغو شده'],
            'rejected'  => ['#7A96A4','رد شده'],
            'done'      => ['#1A6B8A','انجام شده'],
            'no_show'   => ['#E05252','عدم حضور'],
        ];
        ?>
        <div class="dental-admin-wrap">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
                <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;">
                    <i data-lucide="list" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                    لیست نوبت‌ها
                </h1>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dental-booking-list&action=new')); ?>"
                   class="dc-btn dc-btn-primary">
                    <i data-lucide="plus-circle" style="width:14px;height:14px;"></i> نوبت جدید
                </a>
            </div>

            <!-- فیلتر -->
            <div class="dc-card" style="margin-bottom:16px;">
                <div class="dc-card-body" style="padding:14px 18px;">
                    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                        <input type="hidden" name="page" value="dental-booking-list">
                        <input type="text" name="s" value="<?php echo esc_attr($search); ?>"
                            placeholder="جستجو با نام بیمار..." class="dc-input" style="max-width:220px;">
                        <select name="status" class="dc-select">
                            <option value="all" <?php selected($filter_status,'all'); ?>>همه وضعیت‌ها</option>
                            <?php foreach($status_cfg as $s=>[$c,$l]): ?>
                            <option value="<?php echo esc_attr($s); ?>" <?php selected($filter_status,$s); ?>><?php echo esc_html($l); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!$is_doctor_only): ?>
                        <select name="doctor" class="dc-select">
                            <option value="0">همه پزشکان</option>
                            <?php foreach($doctors as $d): ?>
                            <option value="<?php echo $d->ID; ?>" <?php selected($filter_doctor,$d->ID); ?>><?php echo esc_html($d->display_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php else: ?>
                        <input type="hidden" name="doctor" value="<?php echo (int)$cu->ID; ?>">
                        <?php endif; ?>
                        <button type="submit" class="dc-btn dc-btn-secondary">
                            <i data-lucide="search" style="width:14px;height:14px;"></i> جستجو
                        </button>
                    </form>
                </div>
            </div>

            <!-- جدول -->
            <div class="dc-card">
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead>
                            <tr><th>بیمار</th><th>تاریخ</th><th>ساعت</th><th>پزشک</th><th>خدمت</th><th>وضعیت</th><th>عملیات</th></tr>
                        </thead>
                        <tbody>
                        <?php if(empty($appts)): ?>
                        <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--dc-neutral-400);">نوبتی یافت نشد</td></tr>
                        <?php else: foreach($appts as $a):
                            [$scolor,$slabel] = $status_cfg[$a['status']] ?? ['#999',''];
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight:600;"><?php echo esc_html($a['patient_name']); ?></div>
                                <div style="font-size:11px;color:var(--dc-neutral-500);direction:ltr;"><?php echo esc_html($a['patient_mobile']??''); ?></div>
                            </td>
                            <td><?php echo esc_html($a['appt_date_jalali']); ?></td>
                            <td style="direction:ltr;font-weight:600;"><?php echo esc_html(substr($a['start_time'],0,5)); ?></td>
                            <td><?php echo esc_html($a['doctor_name']); ?></td>
                            <td>
                                <?php if($a['service_title']): ?>
                                <span style="background:<?php echo esc_attr($a['service_color']??'#1A6B8A'); ?>22;color:<?php echo esc_attr($a['service_color']??'#1A6B8A'); ?>;border-radius:8px;padding:2px 8px;font-size:11px;">
                                    <?php echo esc_html($a['service_title']); ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="background:<?php echo $scolor; ?>22;color:<?php echo $scolor; ?>;border-radius:10px;padding:2px 10px;font-size:11px;font-weight:700;">
                                    <?php echo esc_html($slabel); ?>
                                </span>
                                <?php if(in_array($a['status'],['cancelled','rejected']) && !empty($a['cancel_reason'])): ?>
                                <div style="font-size:10px;color:var(--dc-neutral-500);margin-top:3px;max-width:160px;" title="<?php echo esc_attr($a['cancel_reason']); ?>">
                                    💬 <?php echo esc_html(mb_substr($a['cancel_reason'],0,28)); ?><?php echo mb_strlen($a['cancel_reason'])>28?'…':''; ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;">
                                    <a href="<?php echo esc_url(admin_url("admin.php?page=dental-booking&date={$a['appt_date']}")); ?>"
                                       class="dc-btn dc-btn-ghost dc-btn-sm">تقویم</a>
                                    <a href="<?php echo esc_url(admin_url("admin.php?page=dental-patients&action=profile&id={$a['patient_id']}")); ?>"
                                       class="dc-btn dc-btn-ghost dc-btn-sm">پرونده</a>
                                </div>
                            </td>
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

    // ─── ثبت نوبت جدید (POST) — باید روی admin_init (زودهنگام) اجرا بشه،
    // نه داخل render_new_form() که وردپرس دیر (بعد از سایدبار/هدر) صداش
    // می‌زنه. قبلاً wp_safe_redirect() اینجا همیشه با «headers already
    // sent» شکست می‌خورد و بعد از هر ثبت (حتی موفق)، صفحه نصفه/سفید
    // می‌موند — دقیقاً همون باگی که توی بقیه‌ی صفحات این پروژه رفع شده
    // بود ولی اینجا جا مونده بود.
    public function maybe_handle_post(): void {
        if (!isset($_POST['dental_book_new']) || !check_admin_referer('dental_book_new')) return;

        $patient_id  = (int)($_POST['patient_id'] ?? 0);
        $booking_error = '';
        // باید همینجا، قبل از چک اجباری‌بودن بیمار، خونده بشه — قبلاً
        // پایین‌تر (بعد از این بلوک) خونده می‌شد، پس حالت «فقط قفل کن»
        // (که عمداً بیمار نداره) همیشه با خطای «بیمار انتخاب کنید»
        // رد می‌شد و اصلاً امکان قفل‌کردن ساعت وجود نداشت.
        $lock_only   = !empty($_POST['lock_only']);

        // ─── بیمار کاملاً جدید — طبق درخواست کاربر، دقیقاً هم‌الگو
        // با پذیرش (متد مشترک، بدون دوباره‌کاری منطق) ────────────
        $new_name   = sanitize_text_field($_POST['new_patient_name'] ?? '');
        $new_mobile = sanitize_text_field($_POST['new_patient_mobile'] ?? '');
        if (!$lock_only && !$patient_id && $new_name) {
            if (!$new_mobile) {
                $booking_error = 'برای بیمار جدید، شماره موبایل اجباریه.';
            } else {
                $result = Dental_Reception_Manager::create_or_get_patient($new_name, $new_mobile);
                if ($result['success']) { $patient_id = $result['patient_id']; }
                else { $booking_error = $result['message']; }
            }
        }

        if (!$lock_only && !$patient_id && !$booking_error) {
            $booking_error = 'یه بیمار انتخاب کنید یا اطلاعات بیمار جدید رو وارد کنید.';
        }

        if (!$booking_error) {
            $doctor_id   = (int)$_POST['doctor_id'];
            $date_j      = sanitize_text_field($_POST['appt_date_jalali']);
            $time        = sanitize_text_field($_POST['start_time']);
            $shift_id    = (int)$_POST['shift_id'];
            $service     = (int)$_POST['service_type'];
            $notes       = sanitize_textarea_field($_POST['notes']??'');
            $auto        = (int)get_option('dental_booking_auto_confirm',0);

            // ─── قفل‌کردن (بدون بیمار واقعی) — طبق درخواست کاربر، پذیرش
            // بتونه یه ساعت رو از رزرو آنلاین خارج کنه بدون ثبت نوبت واقعی ──
            if ($lock_only) {
                $result = Dental_Booking_Appointment::lock_slot($doctor_id, $date_j, $time, $shift_id, $notes);
            } else {
                $result = Dental_Booking_Appointment::create(
                    $patient_id,$doctor_id,$date_j,$time,$shift_id,(string)$service,$notes,0,(bool)$auto
                );
            }

            if ($result['success']) {
                wp_safe_redirect(admin_url('admin.php?page=dental-booking&date='.Dental_Jalali::to_gregorian($date_j).'&saved=1'));
                exit;
            }
            $booking_error = $result['message'];
        }

        wp_safe_redirect(admin_url('admin.php?page=dental-booking-list&action=new&book_error='.urlencode($booking_error)));
        exit;
    }

    private function render_new_form(): void {
        global $wpdb;
        $error = isset($_GET['book_error']) ? sanitize_text_field(wp_unslash($_GET['book_error'])) : '';

        $patients = get_posts(['post_type'=>'dental_patient','posts_per_page'=>-1,'post_status'=>'publish','orderby'=>'title','order'=>'ASC']);
        $doctors  = get_users(['role__in'=>['dental_doctor'],'fields'=>['ID','display_name']]);
        $services = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dental_services WHERE is_active=1 ORDER BY sort_order", ARRAY_A);
        ?>
        <div class="dental-admin-wrap">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
                <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;">
                    <i data-lucide="calendar-plus" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                    ثبت نوبت جدید
                </h1>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dental-booking-list')); ?>" class="dc-btn dc-btn-ghost">← بازگشت</a>
            </div>

            <?php if(!empty($error)): ?>
            <div style="background:var(--dc-danger-light);border:1px solid var(--dc-danger);border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:13px;color:var(--dc-danger);">
                ❌ <?php echo esc_html($error); ?>
            </div>
            <?php endif; ?>

            <div class="dc-card" style="max-width:680px;">
                <div class="dc-card-body">
                    <form method="post">
                        <?php wp_nonce_field('dental_book_new'); ?>
                        <div class="dc-grid dc-grid-2" style="gap:14px;">
                            <div class="dc-form-group" style="grid-column:1/-1;">
                                <label style="display:flex;align-items:center;gap:8px;background:var(--dc-warning-light);padding:10px 14px;border-radius:8px;cursor:pointer;font-size:13px;">
                                    <input type="checkbox" name="lock_only" id="bk-lock-only" value="1" onchange="dcToggleLockMode(this.checked)">
                                    🔒 فقط این ساعت رو قفل کن (بدون بیمار واقعی) — از رزرو آنلاین خارج می‌شه
                                </label>
                            </div>
                            <div class="dc-form-group" id="bk-patient-field">
                                <label style="display:flex;align-items:center;gap:8px;margin-bottom:8px;font-size:12px;cursor:pointer;">
                                    <input type="checkbox" id="bk-new-patient-toggle" onchange="dcToggleNewPatient(this.checked)">
                                    ➕ بیمار جدید (هنوز پرونده نداره)
                                </label>
                                <div id="bk-existing-patient-wrap">
                                    <label class="dc-label">بیمار <span style="color:red">*</span></label>
                                    <select name="patient_id" class="dc-select">
                                        <option value="">انتخاب بیمار...</option>
                                        <?php foreach($patients as $p): ?>
                                        <option value="<?php echo $p->ID; ?>"><?php echo esc_html($p->post_title); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div id="bk-new-patient-wrap" style="display:none;gap:8px;grid-template-columns:1fr 1fr;">
                                    <div><label class="dc-label" style="font-size:10px;">نام کامل بیمار</label><input type="text" name="new_patient_name" class="dc-input"></div>
                                    <div><label class="dc-label" style="font-size:10px;">موبایل</label><input type="text" name="new_patient_mobile" class="dc-input" dir="ltr" placeholder="09xxxxxxxxx"></div>
                                </div>
                            </div>
                            <div class="dc-form-group">
                                <label class="dc-label">پزشک <span style="color:red">*</span></label>
                                <select name="doctor_id" id="bk-doctor" class="dc-select" required onchange="loadSlots()">
                                    <option value="">انتخاب پزشک...</option>
                                    <?php foreach($doctors as $d): ?>
                                    <option value="<?php echo $d->ID; ?>"><?php echo esc_html($d->display_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="dc-form-group">
                                <label class="dc-label">تاریخ (شمسی) <span style="color:red">*</span></label>
                                <input type="text" name="appt_date_jalali" id="bk-date" class="dc-input dc-datepicker" dir="ltr"
                                    placeholder="<?php echo esc_attr(Dental_Jalali::today()); ?>"
                                    oninput="loadSlots()" required>
                            </div>
                            <div class="dc-form-group">
                                <label class="dc-label">ساعت <span style="color:red">*</span></label>
                                <select name="start_time" id="bk-time" class="dc-select" required>
                                    <option value="">ابتدا پزشک و تاریخ انتخاب کنید</option>
                                </select>
                                <input type="hidden" name="shift_id" id="bk-shift">
                            </div>
                            <div class="dc-form-group">
                                <label class="dc-label">نوع خدمت</label>
                                <select name="service_type" class="dc-select">
                                    <option value="">انتخاب خدمت...</option>
                                    <?php foreach($services as $s): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo esc_html($s['title']); ?> (<?php echo $s['duration']; ?> دقیقه)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="dc-form-group" style="grid-column:1/-1;">
                                <label class="dc-label">یادداشت</label>
                                <textarea name="notes" class="dc-textarea" placeholder="توضیحات اختیاری..."></textarea>
                            </div>
                        </div>
                        <div style="margin-top:16px;">
                            <button type="submit" name="dental_book_new" class="dc-btn dc-btn-primary">
                                <i data-lucide="calendar-check" style="width:15px;height:15px;"></i> ثبت نوبت
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        function dcToggleNewPatient(isNew){
            document.getElementById('bk-existing-patient-wrap').style.display = isNew ? 'none' : '';
            document.getElementById('bk-new-patient-wrap').style.display = isNew ? 'grid' : 'none';
        }
        function dcToggleLockMode(isLocked){
            document.getElementById('bk-patient-field').style.display = isLocked ? 'none' : '';
        }
        function loadSlots(){
            var doc  = document.getElementById("bk-doctor").value;
            var date = document.getElementById("bk-date").value;
            var time = document.getElementById("bk-time");
            var shift= document.getElementById("bk-shift");
            if(!doc||!date||date.length<8){time.innerHTML='<option value="">ابتدا پزشک و تاریخ انتخاب کنید</option>';return;}
            time.innerHTML='<option value="">در حال بارگذاری...</option>';
            fetch(dentalBooking.apiBase+"/slots?doctor_id="+doc+"&date="+encodeURIComponent(date),{
                headers:{"X-WP-Nonce":dentalBooking.nonce}
            }).then(r=>r.json()).then(function(d){
                if(d.success&&d.data.slots.length){
                    time.innerHTML = d.data.slots.map(function(s){
                        return '<option value="'+s.time+'" data-shift="'+s.shift_id+'" '+(s.available?'':'disabled')+'>'+
                            s.time+(s.available?'':' (پر)')+'</option>';
                    }).join('');
                    time.addEventListener('change',function(){
                        var opt = this.options[this.selectedIndex];
                        if(shift) shift.value = opt.dataset.shift||'';
                    });
                    // trigger برای shift اولیه
                    var e = new Event('change'); time.dispatchEvent(e);
                } else {
                    time.innerHTML='<option value="">اسلوتی موجود نیست</option>';
                }
            });
        }
        if(typeof lucide!=="undefined") lucide.createIcons();
        </script>
        <?php
    }
}
