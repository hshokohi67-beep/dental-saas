<?php
defined('ABSPATH') || exit;

class Dental_Page_Booking_Calendar {

    public function render(): void {
        global $wpdb;

        // تأیید/لغو نوبت
        if (isset($_POST['booking_action']) && check_admin_referer('dental_booking_action')) {
            $appt_id = (int)$_POST['appt_id'];
            $action  = sanitize_key($_POST['booking_action']);
            $reason  = sanitize_text_field($_POST['cancel_reason'] ?? '');
            if ($action === 'unlock') {
                Dental_Booking_Appointment::unlock_slot($appt_id);
            } else {
                Dental_Booking_Appointment::update_status($appt_id, $action, $reason);
            }
            wp_safe_redirect(add_query_arg('saved','1',$_SERVER['REQUEST_URI'])); exit;
        }

        $today_j   = Dental_Jalali::today();
        $today_g   = current_time('Y-m-d');
        // انتقال به تاریخ دلخواه از طریق تقویم شمسی
        if (!empty($_GET['jdate'])) {
            $g = Dental_Jalali::to_gregorian(sanitize_text_field($_GET['jdate']));
            if ($g) $view_date = $g;
        } else {
            $view_date = sanitize_text_field($_GET['date'] ?? $today_g);
        }
        $view_j    = Dental_Jalali::to_jalali($view_date, 'Y/m/d');
        $doctor_f  = (int)($_GET['doctor'] ?? 0);

        // ─── دندانپزشک فقط تقویم خودش را می‌بیند، نه همه پزشکان را ──
        $cu = wp_get_current_user();
        $is_doctor_only = in_array('dental_doctor', (array)$cu->roles) && !current_user_can('manage_options');
        if ($is_doctor_only) $doctor_f = $cu->ID;

        // پزشکان
        $doctors = get_users(['role__in' => ['dental_doctor'], 'fields' => ['ID','display_name']]);

        $appts = Dental_Booking_Appointment::get_day_appointments($view_date, $doctor_f);

        $prev = date('Y-m-d', strtotime($view_date . ' -1 day'));
        $next = date('Y-m-d', strtotime($view_date . ' +1 day'));

        $status_cfg = [
            'pending'   => ['#F0A500','⏳','در انتظار'],
            'blocked'   => ['#5A7080','🔒','قفل‌شده (پذیرش)'],
            'confirmed' => ['#2ECC9A','✅','تأیید شده'],
            'cancelled' => ['#E05252','❌','لغو شده'],
            'rejected'  => ['#7A96A4','🚫','رد شده'],
            'done'      => ['#1A6B8A','✔️','انجام شده'],
            'no_show'   => ['#E05252','🚷','عدم حضور'],
        ];
        ?>
        <div class="dental-admin-wrap">
            <!-- هدر -->
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
                <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;">
                    <i data-lucide="calendar" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                    تقویم نوبت‌ها
                </h1>
                <div style="display:flex;gap:8px;">
                    <button type="button" onclick="printDayReport()" class="dc-btn dc-btn-secondary">
                        <i data-lucide="printer" style="width:14px;height:14px;"></i> پرینت روزانه
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dental-booking-list&action=new')); ?>"
                       class="dc-btn dc-btn-primary">
                        <i data-lucide="plus-circle" style="width:14px;height:14px;"></i> نوبت جدید
                    </a>
                </div>
            </div>

            <?php if (isset($_GET['saved'])): ?>
            <div style="background:var(--dc-accent-light);border:1px solid var(--dc-accent);border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:13px;">✅ عملیات انجام شد.</div>
            <?php endif; ?>

            <!-- ناوبری تاریخ -->
            <div class="dc-card" style="margin-bottom:20px;">
                <div class="dc-card-body" style="padding:14px 18px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <a href="<?php echo esc_url(add_query_arg('date',$prev)); ?>"
                               class="dc-btn dc-btn-ghost dc-btn-sm">
                                <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
                            </a>
                            <div style="text-align:center;">
                                <div style="font-size:18px;font-weight:700;color:var(--dc-primary);"><?php echo esc_html($view_j); ?></div>
                                <div style="font-size:11px;color:var(--dc-neutral-500);"><?php echo esc_html($view_date); ?></div>
                            </div>
                            <a href="<?php echo esc_url(add_query_arg('date',$next)); ?>"
                               class="dc-btn dc-btn-ghost dc-btn-sm">
                                <i data-lucide="chevron-left" style="width:14px;height:14px;"></i>
                            </a>
                            <a href="<?php echo esc_url(add_query_arg('date',$today_g)); ?>"
                               class="dc-btn dc-btn-secondary dc-btn-sm">امروز</a>

                            <!-- رفتن به تاریخ دلخواه با تقویم شمسی -->
                            <form method="get" style="display:flex;gap:6px;align-items:center;">
                                <input type="hidden" name="page" value="dental-booking">
                                <input type="hidden" name="doctor" value="<?php echo (int)$doctor_f; ?>">
                                <input type="text" name="jdate" class="dc-datepicker dc-input" style="width:130px;height:34px;"
                                    placeholder="رفتن به تاریخ..." dir="ltr">
                                <button type="submit" class="dc-btn dc-btn-ghost dc-btn-sm">برو</button>
                            </form>
                        </div>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <?php if (!$is_doctor_only): ?>
                            <select onchange="window.location=this.value" class="dc-select" style="height:36px;">
                                <option value="<?php echo esc_url(add_query_arg(['date'=>$view_date,'doctor'=>0])); ?>">همه پزشکان</option>
                                <?php foreach($doctors as $d): ?>
                                <option value="<?php echo esc_url(add_query_arg(['date'=>$view_date,'doctor'=>$d->ID])); ?>"
                                    <?php selected($doctor_f,$d->ID); ?>>
                                    <?php echo esc_html($d->display_name); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php elseif($appt['status'] === 'blocked'): ?>
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('dental_booking_action'); ?>
                                <input type="hidden" name="appt_id" value="<?php echo (int)$appt['id']; ?>">
                                <button type="submit" name="booking_action" value="unlock"
                                    class="dc-btn dc-btn-secondary dc-btn-sm"
                                    onclick="return confirm('این ساعت دوباره برای رزرو آنلاین باز بشه؟')">
                                    🔓 باز کردن قفل
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- آمار روزانه -->
            <?php
            $stats = [
                'pending'   => count(array_filter($appts, fn($a)=>$a['status']==='pending')),
                'confirmed' => count(array_filter($appts, fn($a)=>$a['status']==='confirmed')),
                'done'      => count(array_filter($appts, fn($a)=>$a['status']==='done')),
                'no_show'   => count(array_filter($appts, fn($a)=>$a['status']==='no_show')),
            ];
            ?>
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
                <?php foreach([
                    ['در انتظار','pending','#F0A500','alert-circle'],
                    ['تأیید شده','confirmed','#2ECC9A','check-circle-2'],
                    ['انجام شده','done','#1A6B8A','activity'],
                    ['عدم حضور','no_show','#E05252','user-x'],
                ] as [$lbl,$key,$col,$ico]): ?>
                <div style="background:#fff;border-radius:10px;border:1px solid #EEF2F5;padding:14px;border-top:3px solid <?php echo $col; ?>;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                        <i data-lucide="<?php echo esc_attr($ico); ?>" style="width:15px;height:15px;color:<?php echo $col; ?>;"></i>
                        <span style="font-size:11px;color:#7A96A4;"><?php echo esc_html($lbl); ?></span>
                    </div>
                    <div style="font-size:24px;font-weight:700;color:<?php echo $col; ?>;"><?php echo $stats[$key]; ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- لیست نوبت‌ها -->
            <div class="dc-card">
                <div class="dc-card-header">
                    <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                        <i data-lucide="list" style="width:15px;height:15px;color:var(--dc-primary);"></i>
                        نوبت‌های <?php echo esc_html($view_j); ?>
                    </h3>
                    <span style="font-size:13px;color:var(--dc-neutral-500);"><?php echo count($appts); ?> نوبت</span>
                </div>

                <?php if (empty($appts)): ?>
                <div style="text-align:center;padding:48px;color:var(--dc-neutral-400);">
                    <i data-lucide="calendar-x" style="width:48px;height:48px;color:var(--dc-neutral-300);margin-bottom:12px;"></i>
                    <p>نوبتی برای این روز ثبت نشده</p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dental-booking-list&action=new')); ?>"
                       class="dc-btn dc-btn-primary" style="margin-top:12px;">ثبت نوبت جدید</a>
                </div>
                <?php else: ?>
                <div style="padding:0;">
                    <?php foreach ($appts as $appt):
                        [$scolor,$sicon,$slabel] = $status_cfg[$appt['status']] ?? ['#999','•',''];
                        $start = substr($appt['start_time'],0,5);
                        $end   = substr($appt['end_time'],0,5);
                        $color = $appt['service_color'] ?? '#1A6B8A';
                    ?>
                    <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;border-bottom:1px solid #F5F5F5;flex-wrap:wrap;">
                        <!-- ساعت -->
                        <div style="min-width:80px;text-align:center;">
                            <div style="font-size:16px;font-weight:700;color:var(--dc-primary);direction:ltr;"><?php echo esc_html($start); ?></div>
                            <div style="font-size:11px;color:var(--dc-neutral-400);direction:ltr;"><?php echo esc_html($end); ?></div>
                        </div>

                        <!-- رنگ خدمت -->
                        <div style="width:4px;height:48px;background:<?php echo esc_attr($color); ?>;border-radius:2px;flex-shrink:0;"></div>

                        <!-- اطلاعات -->
                        <div style="flex:1;min-width:150px;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap;">
                                <span style="font-size:14px;font-weight:700;color:var(--dc-neutral-900);">
                                    <?php echo esc_html($appt['patient_name']); ?>
                                </span>
                                <span style="background:<?php echo $scolor; ?>22;color:<?php echo $scolor; ?>;border-radius:10px;padding:2px 8px;font-size:11px;font-weight:700;">
                                    <?php echo $sicon; ?> <?php echo esc_html($slabel); ?>
                                </span>
                            </div>
                            <div style="font-size:12px;color:var(--dc-neutral-500);display:flex;gap:12px;flex-wrap:wrap;">
                                <?php if($appt['service_title']): ?>
                                <span><i data-lucide="stethoscope" style="width:11px;height:11px;vertical-align:middle;"></i> <?php echo esc_html($appt['service_title']); ?></span>
                                <?php endif; ?>
                                <span><i data-lucide="user" style="width:11px;height:11px;vertical-align:middle;"></i> <?php echo esc_html($appt['doctor_name']); ?></span>
                                <?php if($appt['patient_mobile']): ?>
                                <span style="direction:ltr;">📞 <?php echo esc_html($appt['patient_mobile']); ?></span>
                                <?php endif; ?>
                                <?php if($appt['notes']): ?>
                                <span>📝 <?php echo esc_html(mb_substr($appt['notes'],0,40)); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- عملیات -->
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            <?php if($appt['status'] === 'pending'): ?>
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('dental_booking_action'); ?>
                                <input type="hidden" name="appt_id" value="<?php echo (int)$appt['id']; ?>">
                                <button type="submit" name="booking_action" value="confirmed"
                                    class="dc-btn dc-btn-success dc-btn-sm"
                                    onclick="return confirm('این نوبت تأیید شود؟')">
                                    <i data-lucide="check" style="width:13px;height:13px;"></i> تأیید
                                </button>
                            </form>
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('dental_booking_action'); ?>
                                <input type="hidden" name="appt_id" value="<?php echo (int)$appt['id']; ?>">
                                <button type="submit" name="booking_action" value="rejected"
                                    class="dc-btn dc-btn-ghost dc-btn-sm"
                                    onclick="return confirm('این نوبت رد شود؟')">
                                    <i data-lucide="x" style="width:13px;height:13px;"></i> رد
                                </button>
                            </form>
                            <?php elseif($appt['status'] === 'confirmed'): ?>
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('dental_booking_action'); ?>
                                <input type="hidden" name="appt_id" value="<?php echo (int)$appt['id']; ?>">
                                <button type="submit" name="booking_action" value="done"
                                    class="dc-btn dc-btn-secondary dc-btn-sm">
                                    ✔️ انجام شد
                                </button>
                            </form>
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('dental_booking_action'); ?>
                                <input type="hidden" name="appt_id" value="<?php echo (int)$appt['id']; ?>">
                                <button type="submit" name="booking_action" value="no_show"
                                    class="dc-btn dc-btn-ghost dc-btn-sm"
                                    style="color:var(--dc-danger);border-color:var(--dc-danger);"
                                    onclick="return confirm('بیمار برای این نوبت حاضر نشد؟')">
                                    🚷 عدم حضور
                                </button>
                            </form>
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('dental_booking_action'); ?>
                                <input type="hidden" name="appt_id" value="<?php echo (int)$appt['id']; ?>">
                                <button type="submit" name="booking_action" value="cancelled"
                                    class="dc-btn dc-btn-ghost dc-btn-sm"
                                    onclick="return confirm('این نوبت لغو شود؟')">
                                    <i data-lucide="x" style="width:13px;height:13px;"></i> لغو
                                </button>
                            </form>
                            <?php endif; ?>
                            <a href="<?php echo esc_url(admin_url("admin.php?page=dental-patients&action=profile&id={$appt['patient_id']}")); ?>"
                               class="dc-btn dc-btn-ghost dc-btn-sm">
                                <i data-lucide="user" style="width:13px;height:13px;"></i> پرونده
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <script>
        if(typeof lucide!=="undefined")lucide.createIcons();
        function printDayReport(){
            // نکته: esc_url برای خروجی HTML طراحی شده و & را به &#038; تبدیل می‌کند —
            // داخل <script> این تبدیل باعث خراب شدن آدرس می‌شد. از esc_url_raw استفاده شد.
            var url = '<?php echo esc_js(esc_url_raw(admin_url('admin.php?page=dental-booking&print_day=1&date=' . $view_date . '&doctor=' . $doctor_f))); ?>';
            window.open(url, 'dental_print_' + Date.now());
        }
        </script>
        <?php
    }

    // ─── پرینت روزانه نوبت‌های یک روز ───────────────────────────
    public function render_print_day(string $date, int $doctor_id = 0): void {
        $appts  = Dental_Booking_Appointment::get_day_appointments($date, $doctor_id);
        $date_j = Dental_Jalali::to_jalali($date, 'Y/m/d');
        $clinic = get_option('dental_clinic_name', get_bloginfo('name'));
        $phone  = get_option('dental_clinic_phone', '');
        $today  = Dental_Jalali::today('Y/m/d');

        $doctor_name = '';
        if ($doctor_id) {
            $u = get_userdata($doctor_id);
            $doctor_name = $u ? $u->display_name : '';
        }

        $status_cfg = [
            'pending'   => 'در انتظار',
            'confirmed' => 'تأیید شده',
            'done'      => 'انجام شده',
            'rejected'  => 'رد شده',
        ];
        ?>
        <!DOCTYPE html>
        <html lang="fa" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>گزارش روزانه نوبت‌ها — <?php echo esc_html($date_j); ?></title>
            <style>
            * { margin:0;padding:0;box-sizing:border-box; }
            body { font-family:Tahoma,Arial,sans-serif;direction:rtl;font-size:13px;color:#1A2733;background:#fff; }
            .page { width:210mm;min-height:297mm;padding:15mm 18mm;margin:0 auto; }
            .header { display:flex;align-items:center;justify-content:space-between;padding-bottom:12px;border-bottom:2px solid #1A6B8A;margin-bottom:16px; }
            .clinic-name { font-size:19px;font-weight:700;color:#1A6B8A; }
            .meta { font-size:12px;color:#5A7080;margin-top:4px; }
            .doc-title { font-size:15px;font-weight:700;text-align:center;margin-bottom:16px;background:#F0F6F9;padding:8px;border-radius:8px; }
            table { width:100%;border-collapse:collapse;font-size:12px; }
            th { background:#1A6B8A;color:#fff;padding:8px 10px;text-align:right;font-size:11px; }
            td { padding:8px 10px;border-bottom:1px solid #EEF2F5; }
            tr:nth-child(even) td { background:#F8FAFB; }
            .footer { margin-top:24px;padding-top:10px;border-top:1px solid #EEF2F5;font-size:11px;color:#A0B4C0;display:flex;justify-content:space-between; }
            @media print { .no-print{display:none!important;} body{print-color-adjust:exact;-webkit-print-color-adjust:exact;} }
            </style>
        </head>
        <body>
            <div class="no-print" style="position:fixed;top:16px;left:16px;z-index:9999;display:flex;gap:8px;">
                <button onclick="window.print()" style="background:#1A6B8A;color:#fff;border:none;border-radius:8px;padding:10px 20px;font-family:Tahoma;font-size:14px;cursor:pointer;">🖨️ پرینت</button>
                <button onclick="window.close()" style="background:#F0F4F6;color:#5A7080;border:1px solid #C8D4DC;border-radius:8px;padding:10px 20px;font-family:Tahoma;font-size:14px;cursor:pointer;">بستن</button>
            </div>
            <div class="page">
                <div class="header">
                    <div>
                        <div class="clinic-name">🦷 <?php echo esc_html($clinic); ?></div>
                        <?php if($phone): ?><div class="meta">📞 <?php echo esc_html($phone); ?></div><?php endif; ?>
                    </div>
                    <div style="text-align:left;">
                        <div style="font-size:13px;font-weight:700;">گزارش روزانه نوبت‌ها</div>
                        <div class="meta">تاریخ چاپ: <?php echo esc_html($today); ?></div>
                    </div>
                </div>

                <div class="doc-title">
                    نوبت‌های تاریخ <?php echo esc_html($date_j); ?>
                    <?php if($doctor_name): ?> — دکتر <?php echo esc_html($doctor_name); ?><?php endif; ?>
                    — مجموع: <?php echo count($appts); ?> نوبت
                </div>

                <table>
                    <thead><tr><th>ساعت</th><th>بیمار</th><th>موبایل</th><th>خدمت</th><th>پزشک</th><th>وضعیت</th></tr></thead>
                    <tbody>
                    <?php if(empty($appts)): ?>
                    <tr><td colspan="6" style="text-align:center;padding:24px;color:#A0B4C0;">نوبتی ثبت نشده</td></tr>
                    <?php else: foreach($appts as $a): ?>
                    <tr>
                        <td style="direction:ltr;font-weight:700;"><?php echo esc_html(substr($a['start_time'],0,5)); ?></td>
                        <td><?php echo esc_html($a['patient_name']); ?></td>
                        <td style="direction:ltr;"><?php echo esc_html($a['patient_mobile']??''); ?></td>
                        <td><?php echo esc_html($a['service_title']??'—'); ?></td>
                        <td><?php echo esc_html($a['doctor_name']); ?></td>
                        <td><?php echo esc_html($status_cfg[$a['status']]??$a['status']); ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>

                <div class="footer">
                    <div><?php echo esc_html($clinic); ?></div>
                    <div>صفحه ۱ از ۱</div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}
