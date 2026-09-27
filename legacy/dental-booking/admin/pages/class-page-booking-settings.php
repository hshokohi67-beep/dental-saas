<?php
defined('ABSPATH') || exit;

class Dental_Page_Booking_Settings {

    // ─── متن‌های پیش‌فرض پیامک — آماده استفاده بدون نیاز به کدنویسی ──
    private function default_sms(): array {
        return [
            'dental_booking_sms_pending'   => "کلینیک {clinic_name}\nنوبت شما با موفقیت ثبت شد.\nتاریخ: {date} — ساعت: {time}\nپزشک: {doctor}\nنوبت شما پس از تأیید نهایی خواهد شد.",
            'dental_booking_sms_confirmed' => "کلینیک {clinic_name}\nنوبت شما در تاریخ {date} ساعت {time} تأیید شد.\nپزشک: {doctor}\nمنتظر حضور شما هستیم.",
            'dental_booking_sms_cancelled' => "کلینیک {clinic_name}\nنوبت شما در تاریخ {date} ساعت {time} لغو شد.\nبرای رزرو مجدد با ما تماس بگیرید.",
            'dental_booking_sms_rejected'  => "کلینیک {clinic_name}\nمتأسفانه نوبت درخواستی شما در تاریخ {date} تأیید نشد.\nلطفاً با کلینیک تماس بگیرید.",
            'dental_booking_sms_reminder'  => "کلینیک {clinic_name}\nیادآور نوبت:\nفردا {date} ساعت {time}\nپزشک: {doctor}\nلطفاً به موقع حاضر باشید.",
            'dental_booking_sms_manager'   => "نوبت جدید ثبت شد.\nبیمار: {patient_name}\nتاریخ: {date} — ساعت: {time}\nپزشک: {doctor}",
        ];
    }

    public function render(): void {
        if(isset($_POST['dental_save_booking_settings']) && check_admin_referer('dental_booking_settings')){
            $this->save();
        }

        global $wpdb;
        $auto_confirm = (int)get_option('dental_booking_auto_confirm',0);
        $reminder_days= (int)get_option('dental_booking_reminder_days',1);
        $max_advance  = (int)get_option('dental_booking_max_advance_days',30);
        $min_advance  = (int)get_option('dental_booking_min_advance_hours',2);
        $services     = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dental_services WHERE is_active=1 ORDER BY sort_order",ARRAY_A);

        $sms_fields = [
            'dental_booking_sms_pending'  => 'پس از ثبت (در انتظار تأیید)',
            'dental_booking_sms_confirmed'=> 'پس از تأیید نوبت',
            'dental_booking_sms_cancelled'=> 'پس از لغو نوبت',
            'dental_booking_sms_rejected' => 'پس از رد نوبت',
            'dental_booking_sms_reminder' => 'یادآور قبل از نوبت',
            'dental_booking_sms_manager'  => 'اطلاع به مدیر (نوبت جدید)',
        ];
        $defaults = $this->default_sms();
        $vars = '<code>{patient_name}</code> <code>{date}</code> <code>{time}</code> <code>{doctor}</code> <code>{clinic_name}</code>';
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:24px;">
                <i data-lucide="settings" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                تنظیمات نوبت‌دهی
            </h1>

            <?php if(isset($_GET['saved'])): ?>
            <div style="background:var(--dc-accent-light);border:1px solid var(--dc-accent);border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:13px;">✅ ذخیره شد.</div>
            <?php endif; ?>

            <form method="post">
                <?php wp_nonce_field('dental_booking_settings'); ?>
                <input type="hidden" name="deleted_services" id="deleted-services-input" value="">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

                    <!-- تنظیمات عمومی -->
                    <div class="dc-card">
                        <div class="dc-card-header"><h3 class="dc-heading-4">⚙️ تنظیمات عمومی</h3></div>
                        <div class="dc-card-body" style="display:flex;flex-direction:column;gap:14px;">
                            <label style="display:flex;align-items:center;justify-content:space-between;padding:10px;background:var(--dc-neutral-50);border-radius:8px;cursor:pointer;">
                                <span style="font-size:13px;">تأیید خودکار نوبت‌ها</span>
                                <input type="checkbox" name="auto_confirm" value="1" <?php checked($auto_confirm,1); ?>
                                    style="accent-color:var(--dc-primary);width:16px;height:16px;">
                            </label>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">ارسال یادآور (روز قبل)</label>
                                <input type="number" name="reminder_days" class="dc-input" min="0" max="7" value="<?php echo $reminder_days; ?>">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">حداکثر پیش‌رزرو (روز)</label>
                                <input type="number" name="max_advance_days" class="dc-input" min="1" max="365" value="<?php echo $max_advance; ?>">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">حداقل فاصله تا نوبت (ساعت)</label>
                                <input type="number" name="min_advance_hours" class="dc-input" min="0" max="48" value="<?php echo $min_advance; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- خدمات -->
                    <div class="dc-card">
                        <div class="dc-card-header">
                            <h3 class="dc-heading-4">🦷 خدمات قابل رزرو</h3>
                            <button type="button" onclick="addService()" class="dc-btn dc-btn-secondary dc-btn-sm">+ افزودن</button>
                        </div>
                        <div class="dc-card-body">
                            <div style="display:flex;align-items:center;gap:8px;padding:0 8px;margin-bottom:6px;font-size:11px;color:var(--dc-neutral-500);">
                                <span style="width:30px;flex-shrink:0;"></span>
                                <span style="flex:1;">نام خدمت</span>
                                <span style="width:70px;">مدت (دقیقه)</span>
                                <span style="width:80px;">ظرفیت روزانه</span>
                                <span style="width:18px;"></span>
                            </div>
                            <div id="services-list" style="display:flex;flex-direction:column;gap:8px;">
                                <?php foreach($services as $s): ?>
                                <div style="display:flex;align-items:center;gap:8px;padding:8px;background:var(--dc-neutral-50);border-radius:8px;" class="svc-row" data-real-id="<?php echo (int)$s['id']; ?>">
                                    <input type="hidden" name="services[<?php echo $s['id']; ?>][id]" value="<?php echo $s['id']; ?>">
                                    <input type="color" name="services[<?php echo $s['id']; ?>][color]" value="<?php echo esc_attr($s['color']); ?>"
                                        style="width:30px;height:30px;border:none;padding:0;cursor:pointer;border-radius:4px;flex-shrink:0;">
                                    <input type="text" name="services[<?php echo $s['id']; ?>][title]" value="<?php echo esc_attr($s['title']); ?>"
                                        class="dc-input" style="flex:1;">
                                    <input type="number" name="services[<?php echo $s['id']; ?>][duration]" value="<?php echo $s['duration']; ?>"
                                        class="dc-input" style="width:70px;" min="5" max="300" placeholder="دقیقه" title="مدت‌زمان (دقیقه)">
                                    <input type="number" name="services[<?php echo $s['id']; ?>][daily_capacity]" value="<?php echo esc_attr($s['daily_capacity'] ?? ''); ?>"
                                        class="dc-input" style="width:80px;" min="0" placeholder="نامحدود" title="ظرفیت روزانه (به‌ازای هر پزشک) — خالی = نامحدود">
                                    <button type="button" onclick="removeService(this)"
                                        style="background:none;border:none;color:var(--dc-danger);cursor:pointer;font-size:18px;flex-shrink:0;">×</button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- پیامک‌ها -->
                    <div class="dc-card" style="grid-column:1/-1;">
                        <div class="dc-card-header"><h3 class="dc-heading-4">📱 متن پیامک‌ها</h3></div>
                        <div class="dc-card-body">
                            <div style="font-size:12px;color:var(--dc-neutral-500);margin-bottom:14px;">متغیرها (با کلیک درج می‌شوند): <?php echo $vars; ?></div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                                <?php foreach($sms_fields as $opt=>$label):
                                    $val = get_option($opt, '');
                                    if ($val === '') $val = $defaults[$opt] ?? '';
                                ?>
                                <div>
                                    <label class="dc-label"><?php echo esc_html($label); ?></label>
                                    <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:6px;">
                                        <?php foreach(['{patient_name}','{date}','{time}','{doctor}','{clinic_name}'] as $var): ?>
                                        <button type="button" onclick="insertVar('tpl_<?php echo esc_js($opt); ?>','<?php echo esc_js($var); ?>')"
                                            style="background:var(--dc-primary-light);color:var(--dc-primary);border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;font-family:Tahoma;">
                                            <?php echo esc_html($var); ?>
                                        </button>
                                        <?php endforeach; ?>
                                    </div>
                                    <textarea id="tpl_<?php echo esc_attr($opt); ?>" name="<?php echo esc_attr($opt); ?>"
                                        style="width:100%;height:90px;border:1px solid var(--dc-neutral-200);border-radius:8px;padding:8px;font-family:Tahoma;font-size:12px;resize:vertical;box-sizing:border-box;direction:rtl;"><?php
                                        echo esc_textarea($val);
                                    ?></textarea>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top:20px;">
                    <button type="submit" name="dental_save_booking_settings" class="dc-btn dc-btn-primary">💾 ذخیره تنظیمات</button>
                </div>
            </form>
        </div>

        <script>
        var _svcId = Date.now();
        var _deletedIds = [];

        function addService(){
            _svcId++;
            var html = '<div style="display:flex;align-items:center;gap:8px;padding:8px;background:var(--dc-neutral-50);border-radius:8px;" class="svc-row" data-real-id="">' +
                '<input type="color" name="services[new_'+_svcId+'][color]" value="#1A6B8A" style="width:30px;height:30px;border:none;padding:0;cursor:pointer;border-radius:4px;flex-shrink:0;">' +
                '<input type="text" name="services[new_'+_svcId+'][title]" class="dc-input" style="flex:1;" placeholder="نام خدمت">' +
                '<input type="number" name="services[new_'+_svcId+'][duration]" class="dc-input" style="width:70px;" min="5" max="300" value="30" placeholder="دقیقه">' +
                '<input type="number" name="services[new_'+_svcId+'][daily_capacity]" class="dc-input" style="width:80px;" min="0" placeholder="نامحدود">' +
                '<button type="button" onclick="removeService(this)" style="background:none;border:none;color:var(--dc-danger);cursor:pointer;font-size:18px;flex-shrink:0;">×</button>' +
                '</div>';
            document.getElementById('services-list').insertAdjacentHTML('beforeend',html);
        }

        // رفع باگ: حذف واقعاً در دیتابیس هم اعمال شود
        function removeService(btn){
            var row = btn.closest('.svc-row');
            var realId = row.getAttribute('data-real-id');
            if(realId){
                _deletedIds.push(realId);
                document.getElementById('deleted-services-input').value = _deletedIds.join(',');
            }
            row.remove();
        }

        function insertVar(id, v){
            var el = document.getElementById(id);
            if(!el) return;
            var s = el.selectionStart, e = el.selectionEnd;
            el.value = el.value.substring(0,s) + v + el.value.substring(e);
            el.focus();
        }

        if(typeof lucide!=="undefined") lucide.createIcons();
        </script>
        <?php
    }

    private function save(): void {
        global $wpdb;
        update_option('dental_booking_auto_confirm',     isset($_POST['auto_confirm'])?1:0);
        update_option('dental_booking_reminder_days',    (int)$_POST['reminder_days']);
        update_option('dental_booking_max_advance_days', (int)$_POST['max_advance_days']);
        update_option('dental_booking_min_advance_hours',(int)$_POST['min_advance_hours']);

        // ─── حذف خدمات (soft delete) — رفع باگ برگشتن خدمات حذف‌شده ──
        if (!empty($_POST['deleted_services'])) {
            $ids = array_filter(array_map('intval', explode(',', $_POST['deleted_services'])));
            foreach ($ids as $id) {
                $wpdb->update($wpdb->prefix.'dental_services', ['is_active'=>0], ['id'=>$id], ['%d'], ['%d']);
            }
        }

        // ذخیره خدمات باقی‌مانده
        if(!empty($_POST['services'])){
            foreach($_POST['services'] as $id=>$s){
                $title    = sanitize_text_field($s['title']??'');
                $duration = (int)($s['duration']??30);
                $color    = sanitize_hex_color($s['color']??'#1A6B8A');
                $daily_cap = ($s['daily_capacity']??'') !== '' ? (int)$s['daily_capacity'] : null;
                if(!$title) continue;
                if(strpos((string)$id,'new_')===0){
                    $wpdb->insert($wpdb->prefix.'dental_services',
                        ['title'=>$title,'duration'=>$duration,'daily_capacity'=>$daily_cap,'color'=>$color,'is_active'=>1,'sort_order'=>0],
                        ['%s','%d','%d','%s','%d','%d']);
                } else {
                    $wpdb->update($wpdb->prefix.'dental_services',
                        ['title'=>$title,'duration'=>$duration,'daily_capacity'=>$daily_cap,'color'=>$color],
                        ['id'=>(int)$id],['%s','%d','%d','%s'],['%d']);
                }
            }
        }

        // پیامک‌ها
        $sms_opts = ['dental_booking_sms_pending','dental_booking_sms_confirmed','dental_booking_sms_cancelled','dental_booking_sms_rejected','dental_booking_sms_reminder','dental_booking_sms_manager'];
        foreach($sms_opts as $opt){
            if(isset($_POST[$opt])) update_option($opt, sanitize_textarea_field($_POST[$opt]));
        }

        wp_safe_redirect(admin_url('admin.php?page=dental-booking-settings&saved=1')); exit;
    }
}
