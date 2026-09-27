<?php
defined('ABSPATH') || exit;

class Dental_Page_Booking_Doctors {

    public function render(): void {
        global $wpdb;

        if (isset($_POST['dental_save_shift']) && check_admin_referer('dental_save_shift')) {
            $this->save_shift();
        }
        if (isset($_GET['delete_shift']) && check_admin_referer('delete_shift_'.($_GET['delete_shift']??0))) {
            $wpdb->delete($wpdb->prefix.'dental_shifts',['id'=>(int)$_GET['delete_shift']],['%d']);
            wp_safe_redirect(admin_url('admin.php?page=dental-booking-doctors&saved=1')); exit;
        }
        if (isset($_POST['dental_save_blocked']) && check_admin_referer('dental_save_blocked')) {
            $this->save_blocked_date();
        }
        // ─── آپلود عکس پروفایل پزشک — برای نمایش گرد توی ویزارد فرانت‌اند ──
        if (isset($_POST['dental_save_doctor_photo']) && check_admin_referer('dental_save_doctor_photo') && !empty($_FILES['doctor_photo']['tmp_name'])) {
            $doc_id = (int)$_POST['doctor_id'];
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            $attachment_id = media_handle_upload('doctor_photo', 0);
            if (!is_wp_error($attachment_id)) {
                update_user_meta($doc_id, '_dental_doctor_photo', $attachment_id);
            }
            wp_safe_redirect(admin_url('admin.php?page=dental-booking-doctors&doctor='.$doc_id.'&saved=1')); exit;
        }
        // ─── ذخیره‌ی خدمات مجاز یه شیفت خاص — نه کل پزشک، طبق تصحیح
        // کاربر (چون خدمات پزشک بین روزهای مختلف فرق می‌کنه) ────────
        if (isset($_POST['dental_save_shift_services']) && check_admin_referer('dental_save_shift_services')) {
            $shift_id = (int)$_POST['shift_id'];
            $wpdb->delete($wpdb->prefix.'dental_shift_services', ['shift_id'=>$shift_id]);
            foreach ($_POST['services'] ?? [] as $sid) {
                $wpdb->insert($wpdb->prefix.'dental_shift_services', ['shift_id'=>$shift_id, 'service_id'=>(int)$sid]);
            }
            wp_safe_redirect(admin_url('admin.php?page=dental-booking-doctors&doctor='.(int)$_POST['doctor_id'].'&saved=1')); exit;
        }

        $doctors  = get_users(['role__in'=>['dental_doctor'],'fields'=>['ID','display_name']]);
        $doctors_by_id = [];
        foreach ($doctors as $d) { $doctors_by_id[$d->ID] = $d; }
        $selected = (int)($_GET['doctor'] ?? ($doctors[0]->ID ?? 0));

        $shifts = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_shifts WHERE doctor_id=%d ORDER BY day_of_week,start_time",
            $selected
        ), ARRAY_A);

        $blocked = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_blocked_dates WHERE doctor_id=%d OR doctor_id IS NULL ORDER BY blocked_date DESC LIMIT 20",
            $selected
        ), ARRAY_A);

        $days = ['یکشنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنج‌شنبه','جمعه','شنبه'];

        // ─── خدمات اختصاصی این پزشک ────────────────────────────────
        // ─── لیست کلی خدمات — هرکدوم توی فرم هر شیفت به‌صورت جدا تیک می‌خورن ──
        $all_services = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dental_services WHERE is_active=1 ORDER BY sort_order", ARRAY_A);
        // نگاشت شیفت→خدمات مجازش، برای نمایش سریع توی جدول و پرکردن فرم ویرایش
        $shift_services_map = [];
        foreach ($shifts as $sh) {
            $shift_services_map[$sh['id']] = $wpdb->get_col($wpdb->prepare(
                "SELECT service_id FROM {$wpdb->prefix}dental_shift_services WHERE shift_id=%d", $sh['id']
            ));
        }
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:24px;">
                <i data-lucide="users" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                پزشکان و شیفت‌ها
            </h1>

            <?php if(isset($_GET['saved'])): ?>
            <div style="background:var(--dc-accent-light);border:1px solid var(--dc-accent);border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:13px;">✅ ذخیره شد.</div>
            <?php endif; ?>

            <!-- راهنما -->
            <div style="background:var(--dc-primary-light);border:1px solid var(--dc-primary);border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
                <i data-lucide="info" style="width:18px;height:18px;color:var(--dc-primary);flex-shrink:0;"></i>
                <span style="font-size:13px;color:var(--dc-primary);">پزشک را از این قسمت انتخاب نمایید تا بتوانید شیفت‌های کاری و روزهای تعطیل او را مدیریت کنید.</span>
            </div>

            <!-- انتخاب پزشک -->
            <div style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap;">
                <?php foreach($doctors as $d):
                    $is_sel = $selected===$d->ID;
                    $initial = mb_substr($d->display_name, 0, 1);
                    $photo_id = get_user_meta($d->ID, '_dental_doctor_photo', true);
                    $photo_url = $photo_id ? wp_get_attachment_image_url($photo_id, 'thumbnail') : '';
                ?>
                <a href="<?php echo esc_url(add_query_arg('doctor',$d->ID)); ?>"
                   style="display:flex;align-items:center;gap:10px;padding:14px 22px;border-radius:14px;text-decoration:none;
                          font-size:15px;font-weight:700;font-family:Tahoma;transition:all .2s;
                          border:2px solid <?php echo $is_sel?'var(--dc-primary)':'var(--dc-neutral-200)'; ?>;
                          background:<?php echo $is_sel?'var(--dc-primary)':'#fff'; ?>;
                          color:<?php echo $is_sel?'#fff':'var(--dc-neutral-700)'; ?>;
                          box-shadow:<?php echo $is_sel?'0 4px 14px rgba(26,107,138,.28)':'none'; ?>;">
                    <?php if ($photo_url): ?>
                    <img src="<?php echo esc_url($photo_url); ?>" style="width:38px;height:38px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid <?php echo $is_sel?'rgba(255,255,255,.5)':'#fff'; ?>;">
                    <?php else: ?>
                    <span style="width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;
                          background:<?php echo $is_sel?'rgba(255,255,255,.25)':'var(--dc-primary-light)'; ?>;
                          color:<?php echo $is_sel?'#fff':'var(--dc-primary)'; ?>;">
                        <?php echo esc_html($initial); ?>
                    </span>
                    <?php endif; ?>
                    <?php echo esc_html($d->display_name); ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- آپلود عکس پروفایل پزشک انتخاب‌شده -->
            <div style="background:#fff;border:1px dashed var(--dc-neutral-200);border-radius:10px;padding:12px 16px;margin-bottom:24px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <span style="font-size:12px;color:var(--dc-neutral-500);">📷 عکس پروفایل برای «<?php echo esc_html($doctors_by_id[$selected]->display_name ?? ''); ?>» (نمایش گرد توی نوبت‌دهی):</span>
                <form method="post" enctype="multipart/form-data" style="display:flex;align-items:center;gap:8px;">
                    <?php wp_nonce_field('dental_save_doctor_photo'); ?>
                    <input type="hidden" name="doctor_id" value="<?php echo $selected; ?>">
                    <input type="file" name="doctor_photo" accept="image/*" style="font-size:12px;">
                    <button type="submit" name="dental_save_doctor_photo" class="dc-btn dc-btn-primary dc-btn-sm">آپلود</button>
                </form>
            </div>

            <?php if (empty($doctors)): ?>
            <div style="background:var(--dc-warning-light);border:1px solid var(--dc-accent-warm);border-radius:10px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:#7a5200;">
                هیچ پزشکی یافت نشد. از بخش <strong>تنظیمات ← دسترسی‌ها</strong> نقش «دندانپزشک» را به کاربر مورد نظر اختصاص دهید.
            </div>
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

                <!-- شیفت‌های فعلی -->
                <div class="dc-card">
                    <div class="dc-card-header">
                        <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                            <i data-lucide="clock" style="width:15px;height:15px;color:var(--dc-primary);"></i>
                            شیفت‌های کاری
                        </h3>
                    </div>
                    <div class="dc-table-wrap">
                        <table class="dc-table">
                            <thead><tr><th>روز</th><th>شروع</th><th>پایان</th><th>مدت (دقیقه)</th><th>ظرفیت</th><th></th></tr></thead>
                            <tbody>
                            <?php if(empty($shifts)): ?>
                            <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--dc-neutral-400);">شیفتی تعریف نشده</td></tr>
                            <?php else: foreach($shifts as $s): ?>
                            <tr>
                                <td style="font-weight:600;"><?php echo esc_html($days[$s['day_of_week']]??''); ?></td>
                                <td style="direction:ltr;"><?php echo esc_html(substr($s['start_time'],0,5)); ?></td>
                                <td style="direction:ltr;"><?php echo esc_html(substr($s['end_time'],0,5)); ?></td>
                                <td style="text-align:center;"><?php echo (int)$s['slot_duration']; ?></td>
                                <td style="text-align:center;"><?php echo (int)$s['max_patients']; ?></td>
                                <td>
                                    <a href="javascript:void(0)" onclick="dcOpenShiftServices(<?php echo (int)$s['id']; ?>)"
                                       class="dc-btn dc-btn-ghost dc-btn-sm" title="خدمات مجاز این شیفت">
                                        <i data-lucide="tag" style="width:12px;height:12px;color:var(--dc-accent-dark);"></i>
                                    </a>
                                    <a href="javascript:void(0)" onclick="dcEditShift(<?php echo (int)$s['id']; ?>,<?php echo (int)$s['day_of_week']; ?>,'<?php echo esc_js(substr($s['start_time'],0,5)); ?>','<?php echo esc_js(substr($s['end_time'],0,5)); ?>',<?php echo (int)$s['slot_duration']; ?>,<?php echo (int)$s['max_patients']; ?>)"
                                       class="dc-btn dc-btn-ghost dc-btn-sm">
                                        <i data-lucide="pencil" style="width:12px;height:12px;color:var(--dc-primary);"></i>
                                    </a>
                                    <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(['doctor'=>$selected,'delete_shift'=>$s['id']]),'delete_shift_'.$s['id'])); ?>"
                                       class="dc-btn dc-btn-ghost dc-btn-sm"
                                       onclick="return confirm('حذف شود؟')">
                                        <i data-lucide="trash-2" style="width:12px;height:12px;color:var(--dc-danger);"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- فرم افزودن/ویرایش شیفت -->
                    <div style="padding:16px;border-top:1px solid var(--dc-neutral-100);">
                        <h4 id="dc-shift-form-title" style="font-size:13px;font-weight:700;color:var(--dc-neutral-700);margin-bottom:12px;">➕ افزودن شیفت</h4>
                        <form method="post" id="dc-shift-form">
                            <?php wp_nonce_field('dental_save_shift'); ?>
                            <input type="hidden" name="doctor_id" value="<?php echo $selected; ?>">
                            <input type="hidden" name="shift_id" id="dc-shift-id" value="0">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                <div class="dc-form-group" style="margin:0;grid-column:1/-1;">
                                    <label class="dc-label">روز هفته</label>
                                    <select name="day_of_week" id="dc-shift-day" class="dc-select">
                                        <?php foreach($days as $i=>$d): ?>
                                        <option value="<?php echo $i; ?>"><?php echo esc_html($d); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">ساعت شروع</label>
                                    <input type="time" name="start_time" id="dc-shift-start" class="dc-input" value="08:00">
                                </div>
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">ساعت پایان</label>
                                    <input type="time" name="end_time" id="dc-shift-end" class="dc-input" value="14:00">
                                </div>
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">مدت هر نوبت (دقیقه)</label>
                                    <select name="slot_duration" id="dc-shift-duration" class="dc-select">
                                        <?php foreach([15,20,30,45,60,90,120] as $m): ?>
                                        <option value="<?php echo $m; ?>" <?php selected($m,30); ?>><?php echo $m; ?> دقیقه</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">ظرفیت هم‌زمان</label>
                                    <select name="max_patients" id="dc-shift-capacity" class="dc-select">
                                        <?php for($i=1;$i<=5;$i++): ?>
                                        <option value="<?php echo $i; ?>" <?php selected($i,1); ?>><?php echo $i; ?> بیمار</option>
                                        <?php endfor; ?>
                                    </select>
                                    <span style="font-size:10px;color:var(--dc-neutral-500);display:block;margin-top:4px;line-height:1.6;">
                                        ⚠️ یعنی چند بیمار می‌تونن <b>دقیقاً توی یه تایم‌اسلات</b> نوبت بگیرن — نه کل بیماران روز.<br>
                                        برای یه پزشک تنها همیشه <b>۱</b> بذارید. فقط اگه چندتا صندلی/اتاق دارید یا دستیار هم‌زمان کار می‌کنه، بیشتر کنید.
                                    </span>
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                <button type="submit" name="dental_save_shift" id="dc-shift-submit-btn" class="dc-btn dc-btn-primary dc-btn-sm">💾 ذخیره شیفت</button>
                                <button type="button" id="dc-shift-cancel-btn" onclick="dcCancelEditShift()" class="dc-btn dc-btn-ghost dc-btn-sm" style="display:none;">انصراف از ویرایش</button>
                                <script>
                                function dcEditShift(id, day, start, end, duration, capacity){
                                    document.getElementById('dc-shift-id').value = id;
                                    document.getElementById('dc-shift-day').value = day;
                                    document.getElementById('dc-shift-start').value = start;
                                    document.getElementById('dc-shift-end').value = end;
                                    document.getElementById('dc-shift-duration').value = duration;
                                    document.getElementById('dc-shift-capacity').value = capacity;
                                    document.getElementById('dc-shift-form-title').textContent = '✏️ ویرایش شیفت';
                                    document.getElementById('dc-shift-submit-btn').textContent = '💾 ذخیره تغییرات';
                                    document.getElementById('dc-shift-cancel-btn').style.display = 'inline-flex';
                                    document.getElementById('dc-shift-form').scrollIntoView({behavior:'smooth', block:'center'});
                                }
                                function dcCancelEditShift(){
                                    document.getElementById('dc-shift-id').value = 0;
                                    document.getElementById('dc-shift-form').reset();
                                    document.getElementById('dc-shift-form-title').textContent = '➕ افزودن شیفت';
                                    document.getElementById('dc-shift-submit-btn').textContent = '💾 ذخیره شیفت';
                                    document.getElementById('dc-shift-cancel-btn').style.display = 'none';
                                }
                                </script>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- مودال تیک‌زدن خدمات هر شیفت — با کلیک روی 🏷️ کنار هر شیفت باز می‌شه -->
                <div id="dc-shift-svc-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.75);z-index:999999;align-items:center;justify-content:center;">
                    <div style="background:#fff;border-radius:16px;padding:20px;width:100%;max-width:420px;">
                        <div style="font-weight:700;font-size:14px;margin-bottom:6px;">🏷️ خدمات مجاز این شیفت</div>
                        <p style="font-size:11px;color:var(--dc-neutral-500);margin-bottom:12px;">
                            هیچی تیک نزنید = همه‌ی خدمات توی این شیفت مجازه. تیک بزنید = فقط همون‌ها.<br>
                            مثلاً برای شیفتی که پزشک فقط جراحی داره، فقط «جراحی» رو تیک بزنید.
                        </p>
                        <form method="post">
                            <?php wp_nonce_field('dental_save_shift_services'); ?>
                            <input type="hidden" name="doctor_id" value="<?php echo $selected; ?>">
                            <input type="hidden" name="shift_id" id="dc-svc-shift-id">
                            <div id="dc-svc-checkboxes" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;margin-bottom:14px;">
                                <?php foreach($all_services as $sv): ?>
                                <label style="display:flex;align-items:center;gap:6px;font-size:12px;background:var(--dc-neutral-50);padding:8px 10px;border-radius:6px;cursor:pointer;">
                                    <input type="checkbox" name="services[]" value="<?php echo $sv['id']; ?>" class="dc-svc-cb" data-svc="<?php echo $sv['id']; ?>">
                                    <span style="width:8px;height:8px;border-radius:50%;background:<?php echo esc_attr($sv['color']); ?>;flex-shrink:0;"></span>
                                    <?php echo esc_html($sv['title']); ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <div style="display:flex;gap:8px;">
                                <button type="submit" name="dental_save_shift_services" class="dc-btn dc-btn-primary dc-btn-sm" style="flex:1;">💾 ذخیره</button>
                                <button type="button" onclick="document.getElementById('dc-shift-svc-modal').style.display='none'" class="dc-btn dc-btn-ghost dc-btn-sm">انصراف</button>
                            </div>
                        </form>
                    </div>
                </div>
                <script>
                var dcShiftServicesMap = <?php echo wp_json_encode($shift_services_map); ?>;
                function dcOpenShiftServices(shiftId){
                    document.getElementById('dc-svc-shift-id').value = shiftId;
                    var allowed = dcShiftServicesMap[shiftId] || [];
                    document.querySelectorAll('.dc-svc-cb').forEach(function(cb){
                        cb.checked = allowed.indexOf(parseInt(cb.dataset.svc,10)) !== -1 || allowed.map(String).indexOf(cb.dataset.svc) !== -1;
                    });
                    document.getElementById('dc-shift-svc-modal').style.display = 'flex';
                }
                </script>

                <!-- روزهای تعطیل/مسدود -->
                <div class="dc-card">
                    <div class="dc-card-header">
                        <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                            <i data-lucide="calendar-x" style="width:15px;height:15px;color:var(--dc-danger);"></i>
                            روزهای تعطیل / مسدود
                        </h3>
                    </div>
                    <div style="padding:16px;">
                        <form method="post" style="margin-bottom:16px;">
                            <?php wp_nonce_field('dental_save_blocked'); ?>
                            <input type="hidden" name="doctor_id" value="<?php echo $selected; ?>">
                            <div class="dc-form-group" style="margin:0 0 10px;">
                                <label class="dc-label">تاریخ (شمسی)</label>
                                <input type="text" name="blocked_date_jalali" class="dc-input dc-datepicker" dir="ltr" placeholder="<?php echo esc_attr(Dental_Jalali::today()); ?>">
                            </div>
                            <div class="dc-form-group" style="margin:0 0 10px;">
                                <label class="dc-label">دلیل</label>
                                <input type="text" name="reason" class="dc-input" placeholder="مثال: تعطیل رسمی">
                            </div>
                            <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:12px;cursor:pointer;">
                                <input type="checkbox" name="all_doctors" value="1" style="accent-color:var(--dc-danger);">
                                برای همه پزشکان
                            </label>
                            <button type="submit" name="dental_save_blocked" class="dc-btn dc-btn-danger dc-btn-sm">🚫 مسدود کردن</button>
                        </form>

                        <div style="border-top:1px solid var(--dc-neutral-100);padding-top:12px;">
                            <?php if(empty($blocked)): ?>
                            <p style="text-align:center;color:var(--dc-neutral-400);font-size:13px;">روز مسدودی ثبت نشده</p>
                            <?php else: foreach($blocked as $b): ?>
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #F5F5F5;">
                                <div>
                                    <div style="font-size:13px;font-weight:600;"><?php echo esc_html(Dental_Jalali::to_jalali($b['blocked_date'],'Y/m/d')); ?></div>
                                    <div style="font-size:11px;color:var(--dc-neutral-500);"><?php echo esc_html($b['reason']??''); ?> <?php echo $b['doctor_id']?'':'(همه)'; ?></div>
                                </div>
                                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(['doctor'=>$selected,'delete_blocked'=>$b['id']]),'delete_blocked_'.$b['id'])); ?>"
                                   style="color:var(--dc-danger);text-decoration:none;font-size:12px;"
                                   onclick="return confirm('حذف شود؟')">حذف</a>
                            </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }

    private function save_shift(): void {
        global $wpdb;
        $shift_id = (int)($_POST['shift_id'] ?? 0);
        $row = [
            'doctor_id'     => (int)$_POST['doctor_id'],
            'day_of_week'   => (int)$_POST['day_of_week'],
            'start_time'    => sanitize_text_field($_POST['start_time']),
            'end_time'      => sanitize_text_field($_POST['end_time']),
            'slot_duration' => (int)$_POST['slot_duration'],
            'max_patients'  => (int)$_POST['max_patients'],
        ];
        if ($shift_id) {
            // ─── ویرایش — قبلاً این امکان اصلاً وجود نداشت، فقط
            // افزودن/حذف بود ────────────────────────────────────
            $wpdb->update($wpdb->prefix.'dental_shifts', $row, ['id'=>$shift_id], ['%d','%d','%s','%s','%d','%d'], ['%d']);
        } else {
            $row['is_active'] = 1;
            $wpdb->insert($wpdb->prefix.'dental_shifts', $row, ['%d','%d','%s','%s','%d','%d','%d']);
        }
        wp_safe_redirect(admin_url('admin.php?page=dental-booking-doctors&doctor='.(int)$_POST['doctor_id'].'&saved=1')); exit;
    }

    private function save_blocked_date(): void {
        global $wpdb;
        $jalali    = sanitize_text_field($_POST['blocked_date_jalali']??'');
        $gregorian = Dental_Jalali::to_gregorian($jalali);
        if(!$gregorian) return;
        $all = !empty($_POST['all_doctors']);
        $wpdb->insert($wpdb->prefix.'dental_blocked_dates',[
            'doctor_id'    => $all ? null : (int)$_POST['doctor_id'],
            'blocked_date' => $gregorian,
            'reason'       => sanitize_text_field($_POST['reason']??''),
            'is_active'    => 1,
        ],[$all?'%s':'%d','%s','%s','%d']);
        wp_safe_redirect(admin_url('admin.php?page=dental-booking-doctors&doctor='.(int)$_POST['doctor_id'].'&saved=1')); exit;
    }
}
