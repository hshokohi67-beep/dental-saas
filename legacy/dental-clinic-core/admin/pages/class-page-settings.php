<?php
defined('ABSPATH') || exit;

class Dental_Page_Settings {

    private array $tabs = [
        'general'  => 'عمومی',
        'features' => 'امکانات',
        'pos'      => 'کارتخوان',
        'sms'      => 'پیامک',
        'sms_log'  => 'لاگ پیامک‌ها',
        'financial'=> 'مالی',
        'roles'    => 'دسترسی‌ها',
        'backup'   => 'بک‌آپ',
        'drugs'    => 'دیتابیس داروها',
        'pathway'  => 'قالب‌های مسیر درمان',
    ];

    public function __construct() {
        // «قالب‌های مسیر درمان» فقط وقتی ماژول مسیر درمان فعاله معنی داره —
        // دقیقاً همون شرطی که قبلاً برای منوی جدا‌ش استفاده می‌شد.
        if (!class_exists('Dental_Pathway_Manager') || !Dental_Pathway_Manager::is_enabled()) {
            unset($this->tabs['pathway']);
        }
    }

    // ─── رفع ریشه‌ای باگ «صفحه‌ی سفید بعد از ذخیره»: این منطق قبلاً
    // داخل render() بود — که وردپرس دیر (بعد از سایدبار) صداش می‌زنه،
    // پس wp_safe_redirect() داخلش همیشه با «headers already sent»
    // شکست می‌خورد. الان این متد جدا شده تا از admin_init (زودهنگام،
    // قبل از هر HTML) صدا زده بشه — قبل از اینکه وردپرس حتی یه بایت
    // خروجی بفرسته.
    public function maybe_handle_post(): void {
        $tab = sanitize_key($_GET['tab'] ?? 'general');

        if (isset($_POST['dental_save_settings'])) {
            check_admin_referer('dental_settings_' . $tab);
            $this->save($tab);
        }

        if (isset($_POST['dental_add_staff'])) {
            check_admin_referer('dental_add_staff');
            $cu = wp_get_current_user();
            if (!current_user_can('manage_options') && !in_array('dental_admin', (array)$cu->roles)) {
                wp_die('دسترسی مجاز نیست.');
            }
            $this->add_staff();
        }

        if (isset($_POST['dental_test_sms'])) {
            check_admin_referer('dental_settings_sms');
            $this->test_sms();
        }

        if (isset($_POST['dental_retry_sms'])) {
            check_admin_referer('dental_retry_sms');
            $this->retry_sms((int)$_POST['sms_id']);
        }
    }

    public function render(): void {
        $tab = sanitize_key($_GET['tab'] ?? 'general');

        $saved = isset($_GET['saved']);
        ?>
        <div class="dental-admin-wrap">
            <div style="margin-bottom:24px;">
                <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;">
                    <i data-lucide="settings" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                    تنظیمات پلاگین
                </h1>
            </div>

            <?php if ($saved): ?>
            <div style="background:var(--dc-accent-light);border:1px solid var(--dc-accent);border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:8px;">
                <i data-lucide="check-circle-2" style="width:16px;height:16px;color:var(--dc-accent-dark);"></i>
                تنظیمات ذخیره شد.
            </div>
            <?php endif; ?>

            <!-- تب‌ها -->
            <div class="dc-tabs" style="margin-bottom:20px;">
                <?php foreach($this->tabs as $t => $l):
                    $icons = ['general'=>'sliders','features'=>'toggle-right','pos'=>'credit-card','sms'=>'message-square','sms_log'=>'list','financial'=>'credit-card','roles'=>'users','backup'=>'database-backup','drugs'=>'pill','pathway'=>'route'];
                ?>
                <a href="<?php echo esc_url(admin_url("admin.php?page=dental-settings&tab={$t}")); ?>"
                   class="dc-tab <?php echo $tab===$t?'active':''; ?>">
                    <i data-lucide="<?php echo esc_attr($icons[$t]??'circle'); ?>" style="width:14px;height:14px;"></i>
                    <?php echo esc_html($l); ?>
                </a>
                <?php endforeach; ?>
            </div>

            <?php
            match($tab) {
                'features' => $this->render_features_tab(),
                'pos'      => $this->render_pos_tab(),
                'sms'      => $this->render_sms_tab(),
                'sms_log'  => $this->render_sms_log_tab(),
                'backup'   => $this->render_backup_tab(),
                'financial'=> $this->render_financial_tab(),
                'roles'    => $this->render_roles_tab(),
                'drugs'    => (new Dental_Page_Drugs())->render(),
                'pathway'  => isset($this->tabs['pathway']) ? (new Dental_Page_Pathway_Templates())->render() : $this->render_general_tab(),
                default    => $this->render_general_tab(),
            };
            ?>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }

    // ─── تب عمومی ──────────────────────────────────────────────
    private function render_general_tab(): void {
        $clinic_name  = get_option('dental_clinic_name', get_bloginfo('name'));
        $clinic_phone = get_option('dental_clinic_phone', '');
        $clinic_address = get_option('dental_clinic_address', '');
        $currency     = get_option('dental_currency', 'تومان');
        $loyalty_rate = (int)get_option('dental_loyalty_rate', 1);
        $chart_mode   = get_option('dental_chart_default_mode', 'adult');
        $dev_mode     = defined('DENTAL_DEV_MODE') && DENTAL_DEV_MODE;
        ?>
        <div class="dc-card" style="max-width:620px;">
            <div class="dc-card-header">
                <h3 class="dc-heading-4">⚙️ تنظیمات عمومی</h3>
            </div>
            <div class="dc-card-body">
                <form method="post">
                    <?php wp_nonce_field('dental_settings_general'); ?>
                    <div style="display:flex;flex-direction:column;gap:14px;">

                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">نام کلینیک</label>
                            <input type="text" name="clinic_name" class="dc-input" value="<?php echo esc_attr($clinic_name); ?>">
                        </div>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">تلفن کلینیک</label>
                            <input type="text" name="clinic_phone" class="dc-input" dir="ltr" value="<?php echo esc_attr($clinic_phone); ?>">
                        </div>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">آدرس کلینیک</label>
                            <textarea name="clinic_address" class="dc-input" rows="2"><?php echo esc_textarea($clinic_address); ?></textarea>
                            <span style="font-size:11px;color:var(--dc-neutral-500);">توی سربرگ چاپی فاکتور، نسخه، مدارک بیمه، و بسته‌ی تسویه با بیمه نشون داده می‌شه.</span>
                        </div>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">واحد پولی</label>
                            <select name="currency" class="dc-select">
                                <option value="تومان" <?php selected($currency,'تومان'); ?>>تومان</option>
                                <option value="ریال"  <?php selected($currency,'ریال'); ?>>ریال</option>
                            </select>
                        </div>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">نرخ امتیاز وفاداری</label>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="number" name="loyalty_rate" class="dc-input" style="max-width:100px;" min="0" max="10" value="<?php echo $loyalty_rate; ?>">
                                <span style="font-size:12px;color:var(--dc-neutral-600);">امتیاز به ازای هر ۱,۰۰۰ تومان پرداخت</span>
                            </div>
                        </div>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">حالت پیش‌فرض چارت</label>
                            <select name="chart_mode" class="dc-select">
                                <option value="adult" <?php selected($chart_mode,'adult'); ?>>بزرگسال</option>
                                <option value="peds"  <?php selected($chart_mode,'peds'); ?>>اطفال</option>
                            </select>
                        </div>

                        <?php if ($dev_mode): ?>
                        <div style="background:var(--dc-warning-light);border:1px solid var(--dc-accent-warm);border-radius:8px;padding:12px;font-size:13px;">
                            <i data-lucide="alert-triangle" style="width:15px;height:15px;color:var(--dc-accent-warm);vertical-align:middle;"></i>
                            <strong>حالت توسعه فعال است</strong> — OTP ثابت: <code>123456</code>
                            <br><small style="color:var(--dc-neutral-600);margin-top:4px;display:block;">برای غیرفعال‌کردن، DENTAL_DEV_MODE را در wp-config.php حذف کنید.</small>
                        </div>
                        <?php endif; ?>

                    </div>
                    <div style="margin-top:20px;">
                        <button type="submit" name="dental_save_settings" class="dc-btn dc-btn-primary">💾 ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    // ─── تب SMS ────────────────────────────────────────────────
    // ─── تب امکانات — سوئیچ روشن/خاموش برای مطب vs کلینیک ───────
    private function render_features_tab(): void {
        $all    = Dental_Features::get_all();
        $labels = Dental_Features::labels();
        ?>
        <div class="dc-card" style="max-width:640px;">
            <div class="dc-card-header">
                <h3 class="dc-heading-4">🧩 امکانات فعال</h3>
            </div>
            <div class="dc-card-body">
                <div style="background:var(--dc-primary-light);border-radius:8px;padding:12px 16px;font-size:13px;color:var(--dc-primary);margin-bottom:18px;">
                    برخی امکانات برای مطب‌های تک‌پزشکی ضروری نیستند. هرکدام را که نیاز ندارید خاموش کنید تا پیشخوان ساده‌تر شود.
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dental-setup-wizard')); ?>" style="color:var(--dc-primary);font-weight:700;">اجرای دوباره ویزارد نصب ←</a>
                </div>
                <form method="post">
                    <?php wp_nonce_field('dental_settings_features'); ?>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <?php foreach($labels as $key => [$title,$desc]): ?>
                        <label style="display:flex;align-items:flex-start;gap:12px;padding:14px;background:var(--dc-neutral-50);border-radius:10px;cursor:pointer;">
                            <input type="checkbox" name="feat_<?php echo esc_attr($key); ?>" value="1" <?php checked(!empty($all[$key])); ?>
                                style="margin-top:3px;accent-color:var(--dc-primary);width:18px;height:18px;flex-shrink:0;">
                            <div>
                                <div style="font-size:14px;font-weight:700;color:var(--dc-neutral-900);"><?php echo esc_html($title); ?></div>
                                <div style="font-size:12px;color:var(--dc-neutral-500);margin-top:2px;"><?php echo esc_html($desc); ?></div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top:18px;">
                        <button type="submit" name="dental_save_settings" class="dc-btn dc-btn-primary">💾 ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    // ─── تب کارتخوان — انتخاب دستگاه از بین بانک‌های رایج ────────
    private function render_pos_tab(): void {
        // ─── الان به‌جای یه دستگاه، لیستی از دستگاه‌ها ذخیره می‌شه —
        // برای کلینیک‌هایی که چندتا کارتخوان (مثلاً یکی پذیرش، یکی مالی) دارن
        $devices = get_option('dental_pos_devices', []);
        if (empty($devices) && get_option('dental_pos_ip')) {
            // مهاجرت خودکار از تنظیمات قدیمی تک‌دستگاهی
            $devices = [[
                'id' => 'dev_1', 'label' => 'کارتخوان اصلی',
                'provider' => get_option('dental_pos_provider','generic'),
                'ip' => get_option('dental_pos_ip',''), 'port' => get_option('dental_pos_port',''),
                'merchant_id' => get_option('dental_pos_merchant_id',''), 'terminal_id' => get_option('dental_pos_terminal_id',''),
                'enabled' => (int)get_option('dental_pos_enabled',0),
            ]];
            update_option('dental_pos_devices', $devices);
        }

        $providers = [
            'generic'   => 'عمومی / سایر (پروتکل استاندارد Socket)',
            'behpardakht'=> 'به‌پرداخت ملت',
            'saman'     => 'سامان کیش',
            'pasargad'  => 'پاسارگاد',
            'asanpardakht'=> 'آسان‌پرداخت',
            'fanava'    => 'فن‌آوا کارت',
            'novin'     => 'پرداخت نوین',
        ];
        ?>
        <div class="dc-card" style="max-width:720px;">
            <div class="dc-card-header"><h3 class="dc-heading-4">💳 دستگاه‌های کارتخوان (POS شبکه‌ای)</h3></div>
            <div class="dc-card-body">
                <div style="background:var(--dc-primary-light);border-radius:8px;padding:12px 16px;font-size:13px;color:var(--dc-primary);margin-bottom:18px;">
                    می‌تونید چندتا دستگاه اضافه کنید (مثلاً یکی برای پذیرش، یکی برای بخش مالی) — هرکدوم آی‌پی و تنظیمات جدا داره.
                </div>
                <form method="post">
                    <?php wp_nonce_field('dental_settings_pos'); ?>
                    <div id="dc-pos-devices-list" style="display:flex;flex-direction:column;gap:16px;">
                        <?php foreach($devices as $i => $d): ?>
                        <div class="dc-pos-device-row" style="border:1px solid var(--dc-neutral-100);border-radius:10px;padding:14px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                                <input type="text" name="pos_devices[<?php echo $i; ?>][label]" class="dc-input" style="max-width:220px;font-weight:700;"
                                    value="<?php echo esc_attr($d['label']); ?>" placeholder="مثلاً: کارتخوان پذیرش">
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <label style="display:flex;align-items:center;gap:5px;font-size:12px;">
                                        فعال <input type="checkbox" name="pos_devices[<?php echo $i; ?>][enabled]" value="1" <?php checked(!empty($d['enabled'])); ?> style="width:16px;height:16px;">
                                    </label>
                                    <button type="button" onclick="this.closest('.dc-pos-device-row').remove()" style="color:var(--dc-danger);background:none;border:none;cursor:pointer;font-size:12px;">🗑️ حذف</button>
                                </div>
                            </div>
                            <input type="hidden" name="pos_devices[<?php echo $i; ?>][id]" value="<?php echo esc_attr($d['id']); ?>">
                            <div class="dc-form-group" style="margin-bottom:10px;">
                                <select name="pos_devices[<?php echo $i; ?>][provider]" class="dc-select">
                                    <?php foreach($providers as $k=>$l): ?>
                                    <option value="<?php echo esc_attr($k); ?>" <?php selected($d['provider'],$k); ?>><?php echo esc_html($l); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="dc-grid dc-grid-2" style="gap:10px;">
                                <input type="text" name="pos_devices[<?php echo $i; ?>][ip]" class="dc-input" value="<?php echo esc_attr($d['ip']); ?>" placeholder="آی‌پی — 192.168.1.50" dir="ltr">
                                <input type="number" name="pos_devices[<?php echo $i; ?>][port]" class="dc-input" value="<?php echo esc_attr($d['port']); ?>" placeholder="پورت — 9001" dir="ltr">
                                <input type="text" name="pos_devices[<?php echo $i; ?>][merchant_id]" class="dc-input" value="<?php echo esc_attr($d['merchant_id']); ?>" placeholder="شماره پذیرنده" dir="ltr">
                                <input type="text" name="pos_devices[<?php echo $i; ?>][terminal_id]" class="dc-input" value="<?php echo esc_attr($d['terminal_id']); ?>" placeholder="شماره ترمینال" dir="ltr">
                            </div>
                            <button type="button" onclick="dcTestPos('<?php echo esc_js($d['id']); ?>')" class="dc-btn dc-btn-ghost dc-btn-sm" style="margin-top:10px;">🔌 تست این دستگاه</button>
                            <span class="dc-pos-test-result" data-dev="<?php echo esc_attr($d['id']); ?>" style="font-size:11px;margin-right:8px;"></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="dcAddPosDevice()" class="dc-btn dc-btn-secondary dc-btn-sm" style="margin-top:14px;">➕ افزودن دستگاه جدید</button>
                    <div style="margin-top:18px;">
                        <button type="submit" name="dental_save_settings" class="dc-btn dc-btn-primary">💾 ذخیره همه</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
        var dcPosDeviceIdx = <?php echo count($devices); ?>;
        function dcAddPosDevice(){
            var idx = dcPosDeviceIdx++;
            var wrap = document.createElement('div');
            wrap.className = 'dc-pos-device-row';
            wrap.style.cssText = 'border:1px solid var(--dc-neutral-100);border-radius:10px;padding:14px;';
            wrap.innerHTML = '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">'+
                '<input type="text" name="pos_devices['+idx+'][label]" class="dc-input" style="max-width:220px;font-weight:700;" placeholder="مثلاً: کارتخوان پذیرش">'+
                '<div style="display:flex;align-items:center;gap:10px;"><label style="display:flex;align-items:center;gap:5px;font-size:12px;">فعال <input type="checkbox" name="pos_devices['+idx+'][enabled]" value="1" checked style="width:16px;height:16px;"></label>'+
                '<button type="button" onclick="this.closest(\'.dc-pos-device-row\').remove()" style="color:var(--dc-danger);background:none;border:none;cursor:pointer;font-size:12px;">🗑️ حذف</button></div></div>'+
                '<input type="hidden" name="pos_devices['+idx+'][id]" value="dev_'+Date.now()+'">'+
                '<div class="dc-grid dc-grid-2" style="gap:10px;">'+
                '<input type="text" name="pos_devices['+idx+'][ip]" class="dc-input" placeholder="آی‌پی — 192.168.1.50" dir="ltr">'+
                '<input type="number" name="pos_devices['+idx+'][port]" class="dc-input" placeholder="پورت — 9001" dir="ltr">'+
                '<input type="text" name="pos_devices['+idx+'][merchant_id]" class="dc-input" placeholder="شماره پذیرنده" dir="ltr">'+
                '<input type="text" name="pos_devices['+idx+'][terminal_id]" class="dc-input" placeholder="شماره ترمینال" dir="ltr">'+
                '</div>';
            document.getElementById('dc-pos-devices-list').appendChild(wrap);
        }
        function dcTestPos(devId){
            var box = document.querySelector('.dc-pos-test-result[data-dev="'+devId+'"]');
            box.textContent = '⏳ در حال تست...';
            var fd = new FormData();
            fd.append('action','dental_pos_test_connection');
            fd.append('device_id', devId);
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce("dental_pos_test")); ?>');
            fetch(ajaxurl, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                box.innerHTML = res.success
                    ? '<span style="color:var(--dc-accent-dark);">✅ '+res.data.message+'</span>'
                    : '<span style="color:var(--dc-danger);">❌ '+(res.data&&res.data.message?res.data.message:'اتصال برقرار نشد')+'</span>';
            });
        }
        </script>
        <?php
    }

    private function render_sms_tab(): void {
        $trez_username = get_option('dental_sms_trez_username', '');
        $trez_password = get_option('dental_sms_trez_password', '');
        $sender       = get_option('dental_sms_sender', '');
        $sender_otp   = get_option('dental_sms_sender_otp', '');
        $manager_mob  = get_option('dental_financial_manager_mobile', '');
        $reminder3    = (int)get_option('dental_reminder_days_3', 3);
        $reminder1    = (int)get_option('dental_reminder_days_1', 1);
        $overdue_en   = (int)get_option('dental_overdue_sms_enabled', 1);
        $birthday_en  = (int)get_option('dental_birthday_sms_enabled', 0);

        $templates = [
            'installment_reminder' => [
                'label'   => 'یادآور سررسید قسط',
                'default' => "کلینیک {clinic_name}\nکاربر گرامی {patient_name}،\nقسط {amount} تومان شما در تاریخ {due_date} سررسید دارد.\nلطفاً پرداخت را انجام دهید.",
                'vars'    => ['{patient_name}','{amount}','{due_date}','{clinic_name}'],
            ],
            'payment_receipt' => [
                'label'   => 'رسید پرداخت قسط',
                'default' => "کلینیک {clinic_name}\nپرداخت {amount} تومان با موفقیت ثبت شد.\nکد پیگیری: {ref_id}",
                'vars'    => ['{patient_name}','{amount}','{ref_id}','{clinic_name}'],
            ],
            'wallet_topup' => [
                'label'   => 'شارژ کیف پول',
                'default' => "کلینیک {clinic_name}\nکیف پول شما به مبلغ {amount} تومان شارژ شد.\nموجودی: {balance} تومان",
                'vars'    => ['{amount}','{balance}','{clinic_name}'],
            ],
            'booking_confirm' => [
                'label'   => 'تأیید رزرو نوبت',
                'default' => "کلینیک {clinic_name}\nنوبت شما در تاریخ {due_date} تأیید شد.\nبا تشکر",
                'vars'    => ['{patient_name}','{due_date}','{clinic_name}'],
            ],
            'overdue_installment' => [
                'label'   => 'اطلاع‌رسانی معوقه',
                'default' => "کلینیک {clinic_name}\nکاربر گرامی {patient_name}،\nقسط {amount} تومان شما از سررسید گذشته است.",
                'vars'    => ['{patient_name}','{amount}','{due_date}','{clinic_name}'],
            ],
            'birthday' => [
                'label'   => 'تبریک تولد',
                'default' => "کلینیک {clinic_name}\n{patient_name} عزیز، روز تولدتان مبارک! 🎂\nآرزوی سلامتی برای شما داریم.",
                'vars'    => ['{patient_name}','{clinic_name}'],
            ],
            'receipt_submitted' => [
                'label'   => 'ثبت فیش توسط بیمار (به مدیر)',
                'default' => "فیش پرداخت جدید ثبت شد.\nبیمار: {patient_name}\nمبلغ: {amount} تومان\nلطفاً تأیید کنید.",
                'vars'    => ['{patient_name}','{amount}'],
            ],
        ];

        global $wpdb;
        $sms_logs = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}dental_sms_log ORDER BY created_at DESC LIMIT 20",
            ARRAY_A
        );
        ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

            <!-- ستون ۱: تنظیمات -->
            <div style="display:flex;flex-direction:column;gap:16px;">

                <!-- دروازه SMS -->
                <div class="dc-card">
                    <div class="dc-card-header">
                        <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                            <i data-lucide="radio" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                            دروازه SMS
                        </h3>
                    </div>
                    <div class="dc-card-body">
                        <form method="post">
                            <?php wp_nonce_field('dental_settings_sms'); ?>
                            <div style="display:flex;flex-direction:column;gap:12px;">
                                <div style="background:var(--dc-primary-light);border-radius:8px;padding:10px 14px;font-size:12px;color:var(--dc-primary-dark);">
                                    📡 سامانه پیامک: <b>ترز (Trez SMS Panel)</b>
                                </div>
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">نام کاربری</label>
                                    <input type="text" name="sms_trez_username" class="dc-input" dir="ltr"
                                        value="<?php echo esc_attr($trez_username); ?>" placeholder="نام کاربری سامانه ترز">
                                </div>
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">رمز عبور</label>
                                    <input type="password" name="sms_trez_password" class="dc-input" dir="ltr"
                                        value="<?php echo esc_attr($trez_password); ?>" placeholder="رمز عبور سامانه ترز">
                                </div>
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">شماره اختصاصی فرستنده (پیامک‌های عادی)</label>
                                    <input type="text" name="sms_sender" class="dc-input" dir="ltr"
                                        value="<?php echo esc_attr($sender); ?>" placeholder="مثال: 983000xxxxxx">
                                    <small style="color:var(--dc-neutral-500);font-size:11px;">برای یادآور قسط، رسید، شارژ کیف‌پول و... — نه کد ورود</small>
                                </div>
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">شماره اشتراکی (مخصوص کد ورود / OTP)</label>
                                    <input type="text" name="sms_sender_otp" class="dc-input" dir="ltr"
                                        value="<?php echo esc_attr($sender_otp); ?>" placeholder="همون شماره‌ی اشتراکی قدیمی">
                                    <small style="color:var(--dc-danger);font-size:11px;">⚠️ شماره‌های اختصاصی معمولاً نمی‌تونن کد ورود (OTP) بفرستن — این باید همون شماره‌ی اشتراکی/قدیمی بمونه.</small>
                                </div>
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">موبایل مدیر مالی</label>
                                    <input type="tel" name="financial_manager_mobile" class="dc-input" dir="ltr"
                                        value="<?php echo esc_attr($manager_mob); ?>" placeholder="09xxxxxxxxx">
                                    <small style="color:var(--dc-neutral-500);font-size:11px;">برای دریافت اطلاع ثبت فیش‌ها</small>
                                </div>
                            </div>
                            <div style="margin-top:16px;display:flex;gap:8px;">
                                <button type="submit" name="dental_save_settings" class="dc-btn dc-btn-primary dc-btn-sm">💾 ذخیره</button>
                                <button type="submit" name="dental_test_sms" class="dc-btn dc-btn-secondary dc-btn-sm">
                                    <i data-lucide="send" style="width:13px;height:13px;"></i> ارسال تست
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- زمان‌بندی -->
                <div class="dc-card">
                    <div class="dc-card-header">
                        <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                            <i data-lucide="clock" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                            زمان‌بندی یادآورها
                        </h3>
                    </div>
                    <div class="dc-card-body">
                        <form method="post">
                            <?php wp_nonce_field('dental_settings_sms'); ?>
                            <div style="display:flex;flex-direction:column;gap:12px;">
                                <div style="display:flex;align-items:center;justify-content:space-between;padding:10px;background:var(--dc-neutral-50);border-radius:8px;">
                                    <label style="font-size:13px;">یادآور اول (روز قبل)</label>
                                    <input type="number" name="reminder_days_3" min="1" max="14" value="<?php echo $reminder3; ?>"
                                        style="width:60px;border:1px solid var(--dc-neutral-300);border-radius:6px;padding:4px 8px;text-align:center;font-family:Tahoma;">
                                </div>
                                <div style="display:flex;align-items:center;justify-content:space-between;padding:10px;background:var(--dc-neutral-50);border-radius:8px;">
                                    <label style="font-size:13px;">یادآور دوم (روز قبل)</label>
                                    <input type="number" name="reminder_days_1" min="0" max="7" value="<?php echo $reminder1; ?>"
                                        style="width:60px;border:1px solid var(--dc-neutral-300);border-radius:6px;padding:4px 8px;text-align:center;font-family:Tahoma;">
                                </div>
                                <label style="display:flex;align-items:center;gap:8px;padding:10px;background:var(--dc-neutral-50);border-radius:8px;cursor:pointer;">
                                    <input type="checkbox" name="overdue_sms_enabled" value="1" <?php checked($overdue_en,1); ?> style="accent-color:var(--dc-primary);width:15px;height:15px;">
                                    <span style="font-size:13px;">ارسال پیامک برای اقساط معوقه</span>
                                </label>
                                <label style="display:flex;align-items:center;gap:8px;padding:10px;background:var(--dc-neutral-50);border-radius:8px;cursor:pointer;">
                                    <input type="checkbox" name="birthday_sms_enabled" value="1" <?php checked($birthday_en,1); ?> style="accent-color:var(--dc-primary);width:15px;height:15px;">
                                    <span style="font-size:13px;">پیامک تبریک تولد</span>
                                </label>
                            </div>
                            <div style="margin-top:16px;">
                                <button type="submit" name="dental_save_settings" class="dc-btn dc-btn-primary dc-btn-sm">💾 ذخیره</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ستون ۲: تمپلیت‌ها -->
            <div class="dc-card">
                <div class="dc-card-header">
                    <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                        <i data-lucide="file-text" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                        متن پیام‌ها
                    </h3>
                </div>
                <div class="dc-card-body">
                    <form method="post">
                        <?php wp_nonce_field('dental_settings_sms'); ?>
                        <div style="display:flex;flex-direction:column;gap:16px;">
                            <?php foreach($templates as $key => $t):
                                $saved_val = get_option('dental_sms_tpl_'.$key, $t['default']);
                            ?>
                            <div style="border:1px solid var(--dc-neutral-200);border-radius:8px;overflow:hidden;">
                                <div style="background:var(--dc-neutral-50);padding:8px 12px;font-size:12px;font-weight:600;color:var(--dc-primary);border-bottom:1px solid var(--dc-neutral-200);">
                                    <?php echo esc_html($t['label']); ?>
                                </div>
                                <div style="padding:10px;">
                                    <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:6px;">
                                        <?php foreach($t['vars'] as $var): ?>
                                        <button type="button" onclick="insertVar('tpl_<?php echo esc_js($key); ?>','<?php echo esc_js($var); ?>')"
                                            style="background:var(--dc-primary-light);color:var(--dc-primary);border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;font-family:Tahoma;">
                                            <?php echo esc_html($var); ?>
                                        </button>
                                        <?php endforeach; ?>
                                    </div>
                                    <textarea id="tpl_<?php echo esc_attr($key); ?>"
                                        name="sms_tpl[<?php echo esc_attr($key); ?>]"
                                        style="width:100%;height:80px;border:1px solid var(--dc-neutral-200);border-radius:6px;padding:8px;font-family:Tahoma;font-size:12px;resize:vertical;box-sizing:border-box;direction:rtl;"><?php echo esc_textarea($saved_val); ?></textarea>
                                    <div style="text-align:left;font-size:10px;color:var(--dc-neutral-400);" id="cnt_<?php echo esc_attr($key); ?>"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="margin-top:16px;">
                            <button type="submit" name="dental_save_settings" class="dc-btn dc-btn-primary">💾 ذخیره تمپلیت‌ها</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- لاگ SMS -->
        <div class="dc-card" style="margin-top:20px;">
            <div class="dc-card-header">
                <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="list" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                    آخرین پیامک‌های ارسال‌شده
                </h3>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dental-sms-log')); ?>" class="dc-btn dc-btn-ghost dc-btn-sm">مشاهده همه</a>
            </div>
            <div class="dc-table-wrap">
                <table class="dc-table">
                    <thead><tr><th>شماره</th><th>نوع</th><th>وضعیت</th><th>دروازه</th><th>زمان</th><th>عملیات</th></tr></thead>
                    <tbody>
                    <?php if(empty($sms_logs)): ?>
                    <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--dc-neutral-400);">لاگی ثبت نشده</td></tr>
                    <?php else: foreach($sms_logs as $log):
                        $status_cfg = [
                            'sent'   => ['var(--dc-accent-dark)', 'ارسال شد'],
                            'failed' => ['var(--dc-danger)',      'خطا'],
                            'queued' => ['var(--dc-neutral-500)', 'در صف'],
                            'mock'   => ['var(--dc-accent-warm)', 'تست'],
                        ];
                        [$scolor,$slabel] = $status_cfg[$log['status']] ?? ['#999',$log['status']];
                        $dt = Dental_Jalali::to_jalali($log['created_at'],'Y/m/d H:i');
                    ?>
                    <tr>
                        <td style="font-family:monospace;font-size:12px;"><?php echo esc_html($log['mobile']); ?></td>
                        <td style="font-size:12px;"><?php echo esc_html($log['trigger_type']); ?></td>
                        <td><span style="background:<?php echo $scolor; ?>22;color:<?php echo $scolor; ?>;border-radius:10px;padding:2px 8px;font-size:11px;font-weight:700;"><?php echo esc_html($slabel); ?></span></td>
                        <td style="font-size:12px;"><?php echo esc_html($log['gateway']); ?></td>
                        <td style="font-size:11px;color:var(--dc-neutral-500);"><?php echo esc_html($dt); ?></td>
                        <td>
                            <?php if($log['status']==='failed' && ($log['retry_count']??0) < 3): ?>
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('dental_retry_sms'); ?>
                                <input type="hidden" name="sms_id" value="<?php echo (int)$log['id']; ?>">
                                <button type="submit" name="dental_retry_sms" class="dc-btn dc-btn-ghost dc-btn-sm">
                                    <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
        function insertVar(id, v) {
            var el = document.getElementById(id);
            if(!el) return;
            var s = el.selectionStart, e = el.selectionEnd;
            el.value = el.value.substring(0,s) + v + el.value.substring(e);
            el.selectionStart = el.selectionEnd = s + v.length;
            el.focus();
        }
        // شمارنده کاراکتر
        document.querySelectorAll("textarea[name^='sms_tpl']").forEach(function(ta){
            var cnt = document.getElementById("cnt_" + ta.id);
            function update(){ if(cnt) cnt.textContent = ta.value.length + " کاراکتر"; }
            ta.addEventListener("input", update);
            update();
        });
        </script>
        <?php
    }

    // ─── تب مالی ───────────────────────────────────────────────
    private function render_financial_tab(): void {
        // ─── چندکارتی — قبلاً فقط یه کارت تک بود. الان یه آرایه، که
        // توی پرتال بیمار به‌صورت چرخشی (نه لیست انتخابی) یکی نشون
        // داده می‌شه — بیمار هیچ‌وقت لیست کامل کارت‌ها رو نمی‌بینه.
        $cards = get_option('dental_cards', []);
        if (empty($cards) && get_option('dental_card_number')) {
            // ─── مهاجرت خودکار از تنظیمات قدیمی تک‌کارتی ──────────────
            $cards = [[
                'number' => get_option('dental_card_number'),
                'name'   => get_option('dental_card_name'),
                'bank'   => get_option('dental_card_bank'),
            ]];
        }
        $gateway_en   = (int)get_option('dental_online_payment_enabled', 0);
        $gateway_type = get_option('dental_payment_gateway', 'zarinpal');
        $gateway_key  = get_option('dental_payment_gateway_key', '');
        ?>
        <div class="dc-card" style="max-width:620px;">
            <div class="dc-card-header">
                <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="credit-card" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                    تنظیمات مالی
                </h3>
            </div>
            <div class="dc-card-body">
                <form method="post">
                    <?php wp_nonce_field('dental_settings_financial'); ?>
                    <div style="display:flex;flex-direction:column;gap:16px;">

                        <!-- کارت‌های بانکی — چندتایی، به‌صورت چرخشی به بیمار نشون داده می‌شن -->
                        <div style="border:1px solid var(--dc-neutral-200);border-radius:8px;overflow:hidden;">
                            <div style="background:var(--dc-primary);padding:10px 14px;color:#fff;font-size:13px;font-weight:600;">
                                💳 کارت‌های بانکی برای انتقال
                            </div>
                            <div style="padding:14px;">
                                <p style="font-size:11px;color:var(--dc-neutral-500);margin-bottom:12px;">
                                    اگه چندتا کارت اضافه کنید، توی پرتال بیمار هر بار یکی از این‌ها به‌صورت تصادفی نشون داده می‌شه — بیمار هیچ‌وقت لیست کامل رو نمی‌بینه.
                                </p>
                                <div id="dc-cards-wrap">
                                <?php foreach($cards as $i => $c): ?>
                                <div class="dc-card-row" style="display:grid;grid-template-columns:1.5fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:end;">
                                    <div><label class="dc-label" style="font-size:10px;">شماره کارت</label><input type="text" name="cards[<?php echo $i; ?>][number]" class="dc-input" dir="ltr" value="<?php echo esc_attr($c['number']); ?>" maxlength="19" style="height:34px;"></div>
                                    <div><label class="dc-label" style="font-size:10px;">نام صاحب کارت</label><input type="text" name="cards[<?php echo $i; ?>][name]" class="dc-input" value="<?php echo esc_attr($c['name']); ?>" style="height:34px;"></div>
                                    <div><label class="dc-label" style="font-size:10px;">نام بانک</label><input type="text" name="cards[<?php echo $i; ?>][bank]" class="dc-input" value="<?php echo esc_attr($c['bank']); ?>" style="height:34px;"></div>
                                    <button type="button" onclick="this.closest('.dc-card-row').remove()" style="background:none;border:none;color:var(--dc-danger);cursor:pointer;padding-bottom:8px;">✕</button>
                                </div>
                                <?php endforeach; ?>
                                </div>
                                <button type="button" onclick="dcAddCardRow()" class="dc-btn dc-btn-ghost dc-btn-sm">➕ افزودن کارت دیگر</button>
                            </div>
                        </div>
                        <script>
                        var dcCardIdx = <?php echo count($cards); ?>;
                        function dcAddCardRow(){
                            var wrap = document.getElementById('dc-cards-wrap');
                            var row = document.createElement('div');
                            row.className = 'dc-card-row';
                            row.style.cssText = 'display:grid;grid-template-columns:1.5fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:end;';
                            row.innerHTML =
                                '<div><label class="dc-label" style="font-size:10px;">شماره کارت</label><input type="text" name="cards['+dcCardIdx+'][number]" class="dc-input" dir="ltr" maxlength="19" style="height:34px;"></div>'+
                                '<div><label class="dc-label" style="font-size:10px;">نام صاحب کارت</label><input type="text" name="cards['+dcCardIdx+'][name]" class="dc-input" style="height:34px;"></div>'+
                                '<div><label class="dc-label" style="font-size:10px;">نام بانک</label><input type="text" name="cards['+dcCardIdx+'][bank]" class="dc-input" style="height:34px;"></div>'+
                                '<button type="button" onclick="this.closest(\'.dc-card-row\').remove()" style="background:none;border:none;color:var(--dc-danger);cursor:pointer;padding-bottom:8px;">✕</button>';
                            wrap.appendChild(row);
                            dcCardIdx++;
                        }
                        </script>

                        <!-- درگاه آنلاین -->
                        <div style="border:1px solid var(--dc-neutral-200);border-radius:8px;overflow:hidden;">
                            <div style="background:var(--dc-accent-light);padding:10px 14px;color:var(--dc-accent-dark);font-size:13px;font-weight:600;display:flex;align-items:center;justify-content:space-between;">
                                <span>💻 پرداخت آنلاین</span>
                                <label style="display:flex;align-items:center;gap:6px;font-weight:400;cursor:pointer;">
                                    <input type="checkbox" name="online_payment_enabled" value="1" <?php checked($gateway_en,1); ?> style="accent-color:var(--dc-accent-dark);width:15px;height:15px;">
                                    فعال
                                </label>
                            </div>
                            <div style="padding:14px;display:flex;flex-direction:column;gap:10px;">
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">درگاه پرداخت</label>
                                    <select name="payment_gateway" class="dc-select">
                                        <option value="zarinpal"  <?php selected($gateway_type,'zarinpal'); ?>>زرین‌پال</option>
                                        <option value="idpay"     <?php selected($gateway_type,'idpay'); ?>>آیدی‌پی</option>
                                        <option value="payping"   <?php selected($gateway_type,'payping'); ?>>پی‌پینگ</option>
                                        <option value="nextpay"   <?php selected($gateway_type,'nextpay'); ?>>نکست‌پی</option>
                                    </select>
                                </div>
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">کد پذیرنده / API Key</label>
                                    <input type="password" name="payment_gateway_key" class="dc-input" dir="ltr"
                                        value="<?php echo esc_attr($gateway_key); ?>" placeholder="کد دریافتی از درگاه">
                                </div>
                                <div style="background:var(--dc-neutral-50);border-radius:6px;padding:10px;font-size:12px;color:var(--dc-neutral-600);">
                                    <i data-lucide="info" style="width:13px;height:13px;vertical-align:middle;"></i>
                                    آدرس بازگشت: <code dir="ltr"><?php echo esc_html(home_url('/dental-payment-callback')); ?></code>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:16px;">
                        <button type="submit" name="dental_save_settings" class="dc-btn dc-btn-primary">💾 ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    // ─── تب دسترسی‌ها ──────────────────────────────────────────
    // ─── لاگ پیامک‌ها — قبلاً منوی جدا بود، الان زیر تنظیمات ────────
    // ─── بک‌آپ کامل دیتابیس پلاگین ──────────────────────────────────
    private function render_backup_tab(): void {
        $summary = class_exists('Dental_Backup_Manager') ? Dental_Backup_Manager::get_summary() : ['table_count'=>0,'total_rows'=>0,'details'=>[]];
        ?>
        <div class="dc-card" style="max-width:700px;">
            <div class="dc-card-header"><h3 class="dc-heading-4">💾 بک‌آپ کامل دیتابیس پلاگین</h3></div>
            <div class="dc-card-body">
                <div style="background:var(--dc-primary-light);border-radius:8px;padding:12px 16px;font-size:13px;color:var(--dc-primary);margin-bottom:18px;">
                    یه فایل SQL کامل از همه‌ی جدول‌های این پلاگین (بیمار، مالی، انبار، حضور، شیفت‌بندی، کاتالوگ و...) می‌سازه و دانلود می‌کنه.
                    این فایل شامل تصاویر/رادیوگرافی نیست، فقط داده‌های متنیه.
                </div>
                <div style="display:flex;gap:24px;margin-bottom:18px;font-size:13px;">
                    <div>📊 تعداد جدول: <b><?php echo $summary['table_count']; ?></b></div>
                    <div>📄 مجموع ردیف‌ها: <b><?php echo number_format($summary['total_rows']); ?></b></div>
                </div>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=dental-settings&tab=backup&download_backup=1'),'dental_download_backup')); ?>" class="dc-btn dc-btn-primary">
                    📥 دانلود بک‌آپ کامل الان
                </a>
                <div style="margin-top:20px;font-size:11px;color:var(--dc-neutral-400);">
                    ⚠️ توصیه: این فایل رو یه‌جای امن (نه فقط روی همین سرور) ذخیره کنید — مثلاً گوگل‌درایو یا یه هارد جدا.
                </div>
            </div>
        </div>

        <!-- ─── بکاپ خودکار روزانه — دیگه نیازی به یادآوری/کلیک دستی نیست ── -->
        <div class="dc-card" style="max-width:700px;margin-top:20px;">
            <div class="dc-card-header"><h3 class="dc-heading-4">⏰ بکاپ خودکار روزانه</h3></div>
            <div class="dc-card-body">
                <div style="background:var(--dc-primary-light);border-radius:8px;padding:12px 16px;font-size:13px;color:var(--dc-primary);margin-bottom:18px;">
                    هر شب ساعت ۳ بامداد، خودکار یه بکاپ ساخته می‌شه — نیازی به یادآوری یا کلیک دستی نیست. فقط ۱۰ بکاپ آخر نگه داشته می‌شه (قدیمی‌ترها خودکار پاک می‌شن).
                </div>
                <?php
                $last_backup = get_option('dental_last_auto_backup');
                $scheduled_backups = class_exists('Dental_Backup_Manager') ? Dental_Backup_Manager::get_scheduled_backups() : [];
                ?>
                <div style="font-size:13px;margin-bottom:14px;">
                    آخرین بکاپ خودکار: <b><?php echo $last_backup ? esc_html(Dental_Jalali::to_jalali(date('Y-m-d',strtotime($last_backup)),'Y/m/d')) . ' ساعت ' . date('H:i', strtotime($last_backup)) : 'هنوز اجرا نشده'; ?></b>
                </div>
                <?php if (empty($scheduled_backups)): ?>
                <p style="font-size:12px;color:var(--dc-neutral-400);">هنوز بکاپ خودکاری ساخته نشده — اولین‌بار امشب ساعت ۳ اجرا می‌شه.</p>
                <?php else: ?>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>فایل</th><th>تاریخ</th><th>حجم</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach($scheduled_backups as $b): ?>
                        <tr>
                            <td style="font-size:11px;direction:ltr;text-align:left;"><?php echo esc_html($b['name']); ?></td>
                            <td style="font-size:12px;"><?php echo esc_html($b['date']); ?></td>
                            <td style="font-size:12px;"><?php echo esc_html($b['size']); ?></td>
                            <td><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=dental-settings&tab=backup&download_auto_backup='.urlencode($b['name'])),'dental_download_backup')); ?>" style="font-size:11px;">دانلود</a></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function render_sms_log_tab(): void {
        global $wpdb;
        $logs = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}dental_sms_log ORDER BY created_at DESC LIMIT 100"
        );
        $badge_map = [
            'sent'   => 'dc-badge-success',
            'failed' => 'dc-badge-danger',
            'mock'   => 'dc-badge-warning',
            'queued' => 'dc-badge-neutral',
        ];
        ?>
        <div class="dc-card">
            <div class="dc-card-header"><h3 class="dc-heading-4">📱 لاگ پیامک‌ها (۱۰۰ مورد اخیر)</h3></div>
            <div class="dc-table-wrap">
                <table class="dc-table">
                    <thead><tr><th>شماره</th><th>نوع</th><th>پیام</th><th>وضعیت</th><th>دروازه</th><th>زمان</th></tr></thead>
                    <tbody>
                    <?php if(empty($logs)): ?>
                        <tr><td colspan="6" style="text-align:center;padding:32px;color:#7A96A4;">لاگی ثبت نشده</td></tr>
                    <?php else: foreach($logs as $log):
                        $badge = $badge_map[$log->status] ?? 'dc-badge-neutral';
                        $dt    = Dental_Jalali::to_jalali($log->created_at, 'Y/m/d H:i');
                    ?>
                        <tr>
                            <td style="font-family:monospace;"><?php echo esc_html($log->mobile); ?></td>
                            <td><?php echo esc_html($log->trigger_type); ?></td>
                            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;"><?php echo esc_html($log->message); ?></td>
                            <td><span class="dc-badge <?php echo esc_attr($badge); ?>"><?php echo esc_html($log->status); ?></span></td>
                            <td><?php echo esc_html($log->gateway); ?></td>
                            <td style="font-size:12px;color:#7A96A4;"><?php echo esc_html($dt); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    private function render_roles_tab(): void {
        $users = get_users(['fields'=>['ID','display_name','user_email'],'number'=>200]);
        $dental_roles = [
            'dental_admin'     => 'مدیر کلینیک',
            'dental_doctor'    => 'دندانپزشک',
            'dental_secretary' => 'منشی / پذیرش',
            'dental_financial' => 'مسئول مالی',
            'dental_assistant' => 'دستیار',
        ];

        // ─── پیام موفقیت/خطا برای افزودن پرسنل (اگه از همین تب هندل شده) ──
        if (isset($_GET['staff_added'])) {
            echo '<div style="background:var(--dc-accent-light);border:1px solid var(--dc-accent);border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:13px;">✅ کاربر جدید با موفقیت اضافه شد.</div>';
        }
        if (isset($_GET['staff_error'])) {
            echo '<div style="background:var(--dc-danger-light);border:1px solid var(--dc-danger);border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:13px;">❌ ' . esc_html(urldecode($_GET['staff_error'])) . '</div>';
        }
        ?>
        <!-- ─── افزودن پرسنل جدید ──────────────────────────────────── -->
        <div class="dc-card" style="margin-bottom:20px;">
            <div class="dc-card-header">
                <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="user-plus" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                    افزودن پرسنل جدید
                </h3>
            </div>
            <form method="post">
                <?php wp_nonce_field('dental_add_staff'); ?>
                <div class="dc-card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="dc-form-group" style="margin:0;">
                        <label class="dc-label">نام و نام‌خانوادگی</label>
                        <input type="text" name="staff_display_name" class="dc-input" required>
                    </div>
                    <div class="dc-form-group" style="margin:0;">
                        <label class="dc-label">نام کاربری (برای ورود)</label>
                        <input type="text" name="staff_username" class="dc-input" dir="ltr" required>
                    </div>
                    <div class="dc-form-group" style="margin:0;">
                        <label class="dc-label">ایمیل (اختیاری)</label>
                        <input type="email" name="staff_email" class="dc-input" dir="ltr">
                    </div>
                    <div class="dc-form-group" style="margin:0;">
                        <label class="dc-label">رمز عبور</label>
                        <input type="text" name="staff_password" class="dc-input" dir="ltr" required placeholder="حداقل ۶ کاراکتر">
                    </div>
                    <div class="dc-form-group" style="margin:0;grid-column:1/-1;">
                        <label class="dc-label">نقش‌ها (می‌تونید چند نقش هم‌زمان انتخاب کنید)</label>
                        <div style="display:flex;flex-wrap:wrap;gap:14px;">
                            <?php foreach($dental_roles as $rk=>$rl): ?>
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                                <input type="checkbox" name="staff_roles[]" value="<?php echo esc_attr($rk); ?>" style="width:16px;height:16px;">
                                <?php echo esc_html($rl); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <small style="color:var(--dc-neutral-400);">اگه بیشتر از یه نقش انتخاب کنید، کاربر موقع ورود می‌تونه انتخاب کنه با کدوم نقش وارد بشه، و بعداً هم می‌تونه از پنل خودش نقشش رو عوض کنه.</small>
                    </div>
                </div>
                <div class="dc-card-body" style="padding-top:0;">
                    <button type="submit" name="dental_add_staff" class="dc-btn dc-btn-primary">➕ افزودن پرسنل</button>
                </div>
            </form>
        </div>

        <!-- ─── لیست پرسنل و مدیریت نقش‌ها ───────────────────────── -->
        <div class="dc-card">
            <div class="dc-card-header">
                <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="users" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                    مدیریت دسترسی کاربران
                </h3>
            </div>
            <div class="dc-table-wrap">
                <table class="dc-table">
                    <thead><tr><th>کاربر</th><th>ایمیل</th><th>نقش‌های فعلی</th><th>ویرایش نقش‌ها</th></tr></thead>
                    <tbody>
                    <?php foreach($users as $u):
                        $user_obj = get_user_by('id',$u->ID);
                        $is_super = user_can($u->ID,'manage_options');
                        $current_roles = array_values(array_intersect($user_obj->roles, array_keys($dental_roles)));
                    ?>
                    <tr>
                        <td style="font-weight:500;"><?php echo esc_html($u->display_name); ?></td>
                        <td style="font-size:12px;color:var(--dc-neutral-500);"><?php echo esc_html($u->user_email); ?></td>
                        <td>
                            <?php if($is_super): ?>
                            <span style="background:var(--dc-primary-light);color:var(--dc-primary);border-radius:10px;padding:2px 10px;font-size:11px;font-weight:700;">مدیر ارشد</span>
                            <?php elseif($current_roles): foreach($current_roles as $r): ?>
                            <span style="background:var(--dc-neutral-100);color:var(--dc-neutral-600);border-radius:10px;padding:2px 8px;font-size:11px;margin-left:3px;display:inline-block;margin-bottom:3px;"><?php echo esc_html($dental_roles[$r]); ?></span>
                            <?php endforeach; else: ?>
                            <span style="color:var(--dc-neutral-400);font-size:12px;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!$is_super): ?>
                            <form method="post" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                                <?php wp_nonce_field('dental_settings_roles'); ?>
                                <input type="hidden" name="user_id" value="<?php echo (int)$u->ID; ?>">
                                <?php foreach($dental_roles as $rk=>$rl): ?>
                                <label style="display:flex;align-items:center;gap:4px;font-size:11px;">
                                    <input type="checkbox" name="new_roles[]" value="<?php echo esc_attr($rk); ?>" <?php checked(in_array($rk,$current_roles)); ?> style="width:14px;height:14px;">
                                    <?php echo esc_html($rl); ?>
                                </label>
                                <?php endforeach; ?>
                                <button type="submit" name="dental_save_settings" class="dc-btn dc-btn-secondary dc-btn-sm">اعمال</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ─── Save ───────────────────────────────────────────────────
    private function save(string $tab): void {
        switch($tab) {
            case 'general':
                update_option('dental_clinic_name',  sanitize_text_field($_POST['clinic_name']??''));
                update_option('dental_clinic_address', sanitize_textarea_field($_POST['clinic_address']??''));
                update_option('dental_clinic_phone', sanitize_text_field($_POST['clinic_phone']??''));
                update_option('dental_currency',     sanitize_text_field($_POST['currency']??'تومان'));
                update_option('dental_loyalty_rate', (int)($_POST['loyalty_rate']??1));
                update_option('dental_chart_default_mode', sanitize_key($_POST['chart_mode']??'adult'));
                break;

            case 'features':
                $keys = array_keys(Dental_Features::defaults());
                $values = [];
                foreach ($keys as $k) {
                    $values[$k] = isset($_POST['feat_'.$k]) ? 1 : 0;
                }
                update_option('dental_features', $values);
                update_option('dental_setup_done', 1);
                break;

            case 'pos':
                $clean_devices = [];
                foreach ($_POST['pos_devices'] ?? [] as $d) {
                    if (empty($d['label']) && empty($d['ip'])) continue; // ردیف خالی رو نادیده بگیر
                    $clean_devices[] = [
                        'id'          => sanitize_key($d['id'] ?? 'dev_'.wp_rand()),
                        'label'       => sanitize_text_field($d['label'] ?? ''),
                        'provider'    => sanitize_key($d['provider'] ?? 'generic'),
                        'ip'          => sanitize_text_field($d['ip'] ?? ''),
                        'port'        => (int)($d['port'] ?? 0),
                        'merchant_id' => sanitize_text_field($d['merchant_id'] ?? ''),
                        'terminal_id' => sanitize_text_field($d['terminal_id'] ?? ''),
                        'enabled'     => !empty($d['enabled']) ? 1 : 0,
                    ];
                }
                update_option('dental_pos_devices', $clean_devices);
                break;

            case 'sms':
                update_option('dental_sms_trez_username',       sanitize_text_field($_POST['sms_trez_username']??''));
                update_option('dental_sms_trez_password',       sanitize_text_field($_POST['sms_trez_password']??''));
                update_option('dental_sms_sender',              sanitize_text_field($_POST['sms_sender']??''));
                update_option('dental_sms_sender_otp',           sanitize_text_field($_POST['sms_sender_otp']??''));
                update_option('dental_financial_manager_mobile',sanitize_text_field($_POST['financial_manager_mobile']??''));
                update_option('dental_reminder_days_3',         (int)($_POST['reminder_days_3']??3));
                update_option('dental_reminder_days_1',         (int)($_POST['reminder_days_1']??1));
                update_option('dental_overdue_sms_enabled',     isset($_POST['overdue_sms_enabled'])?1:0);
                update_option('dental_birthday_sms_enabled',    isset($_POST['birthday_sms_enabled'])?1:0);
                // تمپلیت‌ها
                if (!empty($_POST['sms_tpl']) && is_array($_POST['sms_tpl'])) {
                    foreach($_POST['sms_tpl'] as $key => $val) {
                        update_option('dental_sms_tpl_'.sanitize_key($key), sanitize_textarea_field($val));
                    }
                }
                break;

            case 'financial':
                // ─── چندکارتی — پاک‌سازی و ذخیره به‌صورت آرایه ────────────
                $cards_clean = [];
                foreach ($_POST['cards'] ?? [] as $c) {
                    if (empty(trim($c['number'] ?? ''))) continue;
                    $cards_clean[] = [
                        'number' => sanitize_text_field($c['number']),
                        'name'   => sanitize_text_field($c['name'] ?? ''),
                        'bank'   => sanitize_text_field($c['bank'] ?? ''),
                    ];
                }
                update_option('dental_cards', $cards_clean);
                update_option('dental_online_payment_enabled',isset($_POST['online_payment_enabled'])?1:0);
                update_option('dental_payment_gateway',       sanitize_text_field($_POST['payment_gateway']??'zarinpal'));
                update_option('dental_payment_gateway_key',   sanitize_text_field($_POST['payment_gateway_key']??''));
                break;

            case 'roles':
                $uid = (int)($_POST['user_id']??0);
                $new_roles = array_map('sanitize_key', $_POST['new_roles'] ?? []);
                $dental_roles = ['dental_admin','dental_doctor','dental_secretary','dental_financial','dental_assistant'];
                if($uid && $uid !== get_current_user_id()) {
                    $user = new WP_User($uid);
                    foreach($dental_roles as $r) $user->remove_role($r);
                    foreach($new_roles as $r) { if(in_array($r,$dental_roles)) $user->add_role($r); }
                    // اگه فقط یه نقش داره، همون خودکار نقش فعالش می‌شه (نیازی به انتخاب نیست)
                    if (count($new_roles) === 1) {
                        update_user_meta($uid, '_dental_active_role', $new_roles[0]);
                    } else {
                        delete_user_meta($uid, '_dental_active_role'); // چندتاست، دفعه بعد لاگین انتخاب کنه
                    }
                }
                break;
        }

        wp_safe_redirect(admin_url("admin.php?page=dental-settings&tab={$tab}&saved=1"));
        exit;
    }

    // ─── افزودن پرسنل جدید — مستقیم wp_insert_user، بدون نیاز به
    // capability خام وردپرس create_users (کنترل کاملاً دست خودمونه) ──
    private function add_staff(): void {
        $display_name = sanitize_text_field($_POST['staff_display_name'] ?? '');
        $username     = sanitize_user($_POST['staff_username'] ?? '');
        $email        = sanitize_email($_POST['staff_email'] ?? '');
        $password     = $_POST['staff_password'] ?? '';
        $roles        = array_map('sanitize_key', $_POST['staff_roles'] ?? []);
        $valid_roles  = ['dental_admin','dental_doctor','dental_secretary','dental_financial','dental_assistant'];
        $roles        = array_values(array_intersect($roles, $valid_roles));

        $err = '';
        if (!$display_name || !$username || !$password) $err = 'همه فیلدهای ستاره‌دار را پر کنید.';
        elseif (strlen($password) < 6) $err = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
        elseif (username_exists($username)) $err = 'این نام کاربری قبلاً استفاده شده.';
        elseif ($email && email_exists($email)) $err = 'این ایمیل قبلاً استفاده شده.';
        elseif (empty($roles)) $err = 'حداقل یک نقش انتخاب کنید.';

        if ($err) {
            wp_safe_redirect(admin_url('admin.php?page=dental-settings&tab=roles&staff_error=' . urlencode($err)));
            exit;
        }

        $user_id = wp_insert_user([
            'user_login'   => $username,
            'user_pass'    => $password,
            'user_email'   => $email ?: $username . '@clinic.local',
            'display_name' => $display_name,
            'role'         => $roles[0], // نقش اول، بقیه رو دستی اضافه می‌کنیم
        ]);

        if (is_wp_error($user_id)) {
            wp_safe_redirect(admin_url('admin.php?page=dental-settings&tab=roles&staff_error=' . urlencode($user_id->get_error_message())));
            exit;
        }

        $user = new WP_User($user_id);
        foreach (array_slice($roles, 1) as $r) $user->add_role($r);
        if (count($roles) === 1) update_user_meta($user_id, '_dental_active_role', $roles[0]);

        wp_safe_redirect(admin_url('admin.php?page=dental-settings&tab=roles&staff_added=1'));
        exit;
    }

    private function test_sms(): void {
        $mobile = sanitize_text_field($_POST['sms_sender_test'] ?? get_option('dental_financial_manager_mobile',''));
        if($mobile && class_exists('Dental_SMS_Dispatcher')) {
            $d = new Dental_SMS_Dispatcher();
            $d->queue($mobile, 'پیامک آزمایشی از پلاگین کلینیک دندانپزشکی ✅', 'custom', 0);
        }
        wp_safe_redirect(admin_url('admin.php?page=dental-settings&tab=sms&saved=1'));
        exit;
    }

    private function retry_sms(int $id): void {
        global $wpdb;
        $log = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_sms_log WHERE id=%d", $id
        ), ARRAY_A);
        if($log && class_exists('Dental_SMS_Dispatcher')) {
            $d = new Dental_SMS_Dispatcher();
            $d->queue($log['mobile'], $log['message'], $log['trigger_type'], (int)$log['patient_id']);
        }
        wp_safe_redirect(admin_url('admin.php?page=dental-settings&tab=sms&saved=1'));
        exit;
    }
}
