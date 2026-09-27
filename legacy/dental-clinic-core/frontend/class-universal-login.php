<?php
defined('ABSPATH') || exit;

/**
 * صفحه ورود همگانی — یک صفحه برای همه نقش‌ها
 * (مدیر، دکتر، منشی، مسئول مالی، دستیار با نام‌کاربری/رمز — بیمار با موبایل/OTP)
 */
class Dental_Universal_Login {

    public function __construct() {
        add_shortcode('dental_universal_login', [$this, 'render']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('logout_url', [$this, 'filter_logout_url'], 10, 2);
        add_filter('login_url',  [$this, 'filter_login_url'], 10, 2);
        add_action('login_init', [$this, 'redirect_default_login_page']);
        // نکته مهم: همین فیلتر توی کلاس Dental_Admin هم هست، ولی اون کلاس
        // احتمالاً فقط توی پیشخوان (is_admin) لود می‌شه — این‌جا (که حتماً
        // توی frontend هم لود می‌شه، چون شورتکد صفحه‌ی ورود همینجاست)
        // یه نسخه‌ی مستقل می‌ذاریم تا نوار وردپرس توی صفحات frontend هم
        // (نه فقط پیشخوان) قطعاً مخفی بمونه.
        add_filter('show_admin_bar', '__return_false');
    }

    private function get_login_page_url(): string {
        // نکته: get_page_by_path به‌صورت پیش‌فرض فقط صفحات published رو
        // پیدا می‌کنه — اگه بعد از زباله‌دان/بازگردوندن، وضعیت صفحه چیز
        // دیگه‌ای شده باشه (draft و...)، این چک صریح مطمئن‌تره.
        $page = get_page_by_path('login-page', OBJECT, 'page');
        if (!$page || $page->post_status !== 'publish') return '';
        $url = get_permalink($page);
        return $url ?: '';
    }

    // ─── همه جای وردپرس که «خروج» ساخته می‌شود، بعدش به همین صفحه برگردد ──
    public function filter_logout_url(string $logout_url, string $redirect): string {
        $target = $this->get_login_page_url();
        if (empty($redirect) && $target) {
            $logout_url = add_query_arg('redirect_to', urlencode($target), $logout_url);
        }
        return $logout_url;
    }

    // ─── هر جای وردپرس بخواهد به صفحه ورود اشاره کند (مثلاً هنگام نیاز به احراز هویت) ──
    // اگه صفحه سفارشی پیدا نشد، آدرس اصلی وردپرس رو دست‌نخورده برگردون —
    // وگرنه یه URL خالی/خراب می‌سازیم که خودش باعث مشکلات دیگه می‌شه.
    public function filter_login_url(string $login_url, string $redirect): string {
        $url = $this->get_login_page_url();
        if (!$url) return $login_url;
        if ($redirect) $url = add_query_arg('redirect_to', urlencode($redirect), $url);
        return $url;
    }

    // ─── اگر کسی مستقیم wp-login.php را باز کند، به صفحه سفارشی هدایت شود ──
    // (به‌جز خودِ عملیات خروج/فراموشی رمز که باید در wp-login.php واقعاً پردازش شود)
    // نکته مهم (رفع باگ حلقه‌ی بی‌نهایت): اگه صفحه‌ی سفارشی واقعاً پیدا
    // نشه (مثلاً بعد از زباله‌دان‌رفتن/بازگردوندن، اسلاگش خراب شده)، به‌جای
    // ریدایرکت به یه URL خراب که دوباره برمی‌گرده اینجا، اصلاً کاری نکن و
    // بذار خودِ wp-login.php استاندارد وردپرس نمایش داده بشه — یه راه فرار
    // همیشگی برای وقتی صفحه سفارشی خرابه.
    public function redirect_default_login_page(): void {
        $action = $_GET['action'] ?? '';
        if (in_array($action, ['logout','lostpassword','rp','resetpass','register'], true)) return;

        // پارامتر فرار — اگه کسی گیر کرد، با ?dental_bypass=1 می‌تونه
        // مستقیم به wp-login.php استاندارد وردپرس دسترسی داشته باشه
        if (isset($_GET['dental_bypass'])) return;

        $target = $this->get_login_page_url();
        // اگه صفحه سفارشی پیدا نشد (یعنی get_login_page_url() به فالبک
        // home_url() افتاده)، یا اگه به هر دلیلی همون آدرس wp-login.php رو
        // برگردوند (که یعنی یه‌جای دیگه گیر کرده)، ریدایرکت نکن —
        // بذار wp-login.php استاندارد کار خودشو بکنه، وگرنه حلقه می‌شه.
        if (!$target || strpos($target, 'wp-login.php') !== false || $target === home_url('/')) {
            return;
        }

        wp_safe_redirect($target);
        exit;
    }

    public function enqueue_assets(): void {
        if (!$this->is_login_page()) return;
        wp_enqueue_style('vazirmatn',
            'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap',
            [], null);
    }

    private function is_login_page(): bool {
        global $post;
        return is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'dental_universal_login');
    }

    // ─── مسیر فرود بعد از ورود، بر اساس نقش ─────────────────────
    // ─── لیست نقش‌های دندانی یک کاربر (برای تشخیص چندنقشی‌بودن) ────
    private function get_dental_roles(\WP_User $user): array {
        $valid = ['dental_admin','dental_doctor','dental_secretary','dental_financial','dental_assistant'];
        return array_values(array_intersect((array)$user->roles, $valid));
    }

    private function role_label(string $role): string {
        $labels = [
            'dental_admin'=>'مدیر کلینیک','dental_doctor'=>'دندانپزشک','dental_secretary'=>'منشی / پذیرش',
            'dental_financial'=>'مسئول مالی','dental_assistant'=>'دستیار',
        ];
        return $labels[$role] ?? $role;
    }

    // ─── مسیر فرود بر اساس یک نقش مشخص (نه لزوماً همه نقش‌های کاربر) ──
    private function get_redirect_for_role(string $role): string {
        return match($role) {
            'dental_secretary' => admin_url('admin.php?page=dental-reception'),
            'dental_financial' => admin_url('admin.php?page=dental-financial'),
            default             => admin_url('admin.php?page=dental-dashboard'),
        };
    }

    private function get_redirect_for(\WP_User $user): string {
        $roles = (array)$user->roles;
        if ($user->has_cap('manage_options'))        return admin_url('admin.php?page=dental-dashboard');
        if (in_array('dental_secretary', $roles))    return admin_url('admin.php?page=dental-reception');
        if (in_array('dental_financial', $roles))    return admin_url('admin.php?page=dental-financial');
        if (in_array('dental_doctor', $roles))       return admin_url('admin.php?page=dental-dashboard');
        if (in_array('dental_assistant', $roles))    return admin_url('admin.php?page=dental-dashboard');
        if (in_array('dental_patient', $roles)) {
            $url = dental_get_portal_url();
            return $url ?: home_url('/');
        }
        return admin_url();
    }

    // ─── صفحه انتخاب نقش — وقتی کاربر چند نقش داره ────────────────
    private function render_role_picker(\WP_User $user, array $roles): string {
        $clinic = get_option('dental_clinic_name', get_bloginfo('name'));
        ob_start();
        ?>
        <div style="position:fixed;inset:0;background:#0F2A38;display:flex;align-items:center;justify-content:center;font-family:Vazirmatn,Tahoma,sans-serif;direction:rtl;z-index:99999;">
            <div style="background:#fff;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.35);width:100%;max-width:400px;overflow:hidden;">
                <div style="background:linear-gradient(135deg,#1A6B8A,#0F4D66);padding:26px 24px;text-align:center;color:#fff;">
                    <div style="font-size:15px;font-weight:800;">سلام <?php echo esc_html($user->display_name); ?> 👋</div>
                    <div style="font-size:12px;opacity:.8;margin-top:4px;">شما چند نقش دارید — با کدوم وارد بشید؟</div>
                </div>
                <form method="post" style="padding:22px 20px;display:flex;flex-direction:column;gap:10px;">
                    <?php wp_nonce_field('dental_choose_role'); ?>
                    <?php foreach($roles as $r): ?>
                    <button type="submit" name="dental_choose_role" value="1" onclick="this.form.chosen_role.value='<?php echo esc_js($r); ?>'"
                        style="width:100%;text-align:right;background:#F8FAFB;border:1.5px solid #EEF2F5;border-radius:12px;padding:14px 16px;font-family:inherit;font-size:14px;font-weight:700;color:#1A2733;cursor:pointer;">
                        <?php echo esc_html($this->role_label($r)); ?>
                    </button>
                    <?php endforeach; ?>
                    <input type="hidden" name="chosen_role" value="">
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render(): string {
        // ─── پردازش انتخاب نقش (وقتی کاربر چندنقشی، بعد از ورود انتخاب می‌کنه) ──
        // نکته مهم: از wp_verify_nonce مستقیم استفاده می‌کنیم، نه
        // check_admin_referer — چون این صفحه یه صفحه معمولی سایت (frontend)
        // است، نه صفحه‌ی پیشخوان، و check_admin_referer برای پیشخوان
        // طراحی شده و می‌تونه با ریفرر یه صفحه‌ی frontend ناسازگار باشه
        // و باعث خطای «پیوند منقضی شده» بشه با اینکه واقعاً منقضی نشده.
        if (isset($_POST['dental_choose_role']) && is_user_logged_in()
            && wp_verify_nonce($_POST['_wpnonce'] ?? '', 'dental_choose_role')) {
            $u = wp_get_current_user();
            $chosen = sanitize_key($_POST['chosen_role'] ?? '');
            $valid_roles = $this->get_dental_roles($u);
            if (in_array($chosen, $valid_roles)) {
                update_user_meta($u->ID, '_dental_active_role', $chosen);
                wp_safe_redirect($this->get_redirect_for_role($chosen));
                exit;
            }
        }

        // ─── پردازش ورود کارمندان (نام‌کاربری/رمز) ──────────────
        $staff_error = '';
        if (isset($_POST['dental_staff_login'])
            && wp_verify_nonce($_POST['_wpnonce'] ?? '', 'dental_staff_login')) {

            $login_username = sanitize_text_field($_POST['staff_user'] ?? '');
            // ─── محدودیت تلاش ورود غلط — جلوگیری از حمله‌ی Brute-Force.
            // بر اساس ترکیب یوزرنیم+IP (نه فقط IP، چون پشت NAT کلینیک
            // ممکنه چندتا کاربر IP یکسان داشته باشن؛ نه فقط یوزرنیم، چون
            // نباید بشه با امتحان‌کردن یوزرنیم‌های مختلف دورش زد).
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $lock_key = 'dental_login_lock_' . md5($login_username . '|' . $ip);
            $attempts_key = 'dental_login_attempts_' . md5($login_username . '|' . $ip);

            if (get_transient($lock_key)) {
                $remaining = ceil((get_option('_transient_timeout_' . $lock_key) - time()) / 60);
                $staff_error = "به‌خاطر تلاش‌های ناموفق زیاد، حساب موقتاً قفل شده. لطفاً حدود {$remaining} دقیقه‌ی دیگه دوباره امتحان کنید.";
            } else {
                $creds = [
                    'user_login'    => $login_username,
                    'user_password' => $_POST['staff_pass'] ?? '',
                    'remember'      => true,
                ];
                $user = wp_signon($creds, is_ssl());
                if (is_wp_error($user)) {
                    $attempts = (int)get_transient($attempts_key) + 1;
                    set_transient($attempts_key, $attempts, 15 * MINUTE_IN_SECONDS);
                    if ($attempts >= 5) {
                        set_transient($lock_key, 1, 15 * MINUTE_IN_SECONDS);
                        delete_transient($attempts_key);
                        $staff_error = 'به‌خاطر ۵ تلاش ناموفق، حساب برای ۱۵ دقیقه قفل شد.';
                        if (class_exists('Dental_Audit_Log')) {
                            Dental_Audit_Log::log('settings_changed', "قفل موقت ورود — تلاش‌های ناموفق زیاد برای «{$login_username}» از IP {$ip}", ['entity_type'=>'security']);
                        }
                    } else {
                        $staff_error = 'نام کاربری یا رمز عبور اشتباه است. (' . (5-$attempts) . ' تلاش باقی‌مانده تا قفل موقت)';
                    }
                } else {
                    delete_transient($attempts_key); // ورود موفق — شمارنده صفر بشه
                // نکته مهم: wp_signon کوکی ورود رو برای درخواست بعدی مرورگر
                // تنظیم می‌کنه، ولی «کاربر جاری» PHP رو توی همین درخواست
                // خودکار آپدیت نمی‌کنه — بدون این خط، هر nonce ای که همین‌جا
                // بسازیم (مثل فرم انتخاب نقش) برای کاربر «مهمان» ساخته می‌شه
                // و توی درخواست بعدی (که واقعاً لاگین شدیم) نامعتبر می‌شه.
                wp_set_current_user($user->ID);

                $dental_roles = $this->get_dental_roles($user);
                if (count($dental_roles) > 1) {
                    // چندنقشی — به‌جای ریدایرکت مستقیم، صفحه انتخاب نقش نشون بده
                    return $this->render_role_picker($user, $dental_roles);
                }
                wp_safe_redirect($this->get_redirect_for($user));
                exit;
                    }
                }
            }

        // اگر از قبل لاگین است، مستقیم به مقصد خودش هدایت شود
        if (is_user_logged_in()) {
            $u = wp_get_current_user();
            $dental_roles = $this->get_dental_roles($u);
            $active = get_user_meta($u->ID, '_dental_active_role', true);
            if (count($dental_roles) > 1 && !$active) {
                return $this->render_role_picker($u, $dental_roles);
            }
            $go = $this->get_redirect_for($u);
            return '<div style="direction:rtl;text-align:center;padding:60px 20px;font-family:Vazirmatn,Tahoma,sans-serif;">
                <div style="font-size:44px;margin-bottom:14px;">✅</div>
                <div style="font-size:16px;color:#1A2733;margin-bottom:18px;">شما با حساب <b>'.esc_html($u->display_name).'</b> وارد شده‌اید.</div>
                <a href="'.esc_url($go).'" style="background:#1A6B8A;color:#fff;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:700;">ورود به پنل ←</a>
                <div style="margin-top:14px;"><a href="'.esc_url(wp_logout_url(get_permalink())).'" style="color:#E05252;font-size:12px;text-decoration:none;">خروج و ورود با حساب دیگر</a></div>
            </div>';
        }

        $clinic = get_option('dental_clinic_name', get_bloginfo('name'));

        ob_start();
        ?>
        <style>
        body:has(#dental-universal-login) h1.wp-block-post-title,
        body:has(#dental-universal-login) .entry-title,
        body:has(#dental-universal-login) header.wp-block-template-part,
        body:has(#dental-universal-login) footer.wp-block-template-part { display:none !important; }
        body:has(#dental-universal-login) { background:#5B8FBF; }

        #dental-universal-login{
            position:fixed;inset:0;width:100vw;height:100vh;z-index:99999;
            overflow-y:auto;box-sizing:border-box;
            display:flex;align-items:center;justify-content:center;
            font-family:Vazirmatn,Tahoma,sans-serif;direction:rtl;
            background:linear-gradient(135deg,#6B9FCF 0%,#4A7BAA 100%);
            padding:30px 16px;
        }
        .dul-blob{position:absolute;border-radius:50%;background:rgba(255,255,255,.12);z-index:1;}
        .dul-blob.b1{width:140px;height:140px;top:6%;right:10%;}
        .dul-blob.b2{width:90px;height:90px;bottom:8%;left:8%;}

        /* ─── کارت اصلی — طرح دوبخشی طبق تصویر مرجع: یه سمت عکس/برندینگ،
        یه سمت فرم. کاملاً responsive — زیر ۸۰۰px تک‌ستونه می‌شه ────────── */
        .dul-card{
            position:relative;z-index:2;background:#fff;border-radius:26px;
            box-shadow:0 30px 70px rgba(15,42,56,.35);
            width:100%;max-width:960px;min-height:560px;overflow:hidden;
            display:flex;
        }
        @media (max-width:800px){ .dul-card{ flex-direction:column;min-height:auto; } }

        /* ─── پنل عکس/برندینگ — پس‌زمینه پیش‌فرض یه گرادیان دندان‌پزشکی‌ه؛
        کافیه یه عکس دلخواه رو با background-image جایگزین کنید ──────── */
        .dul-visual{
            flex:0 0 44%;position:relative;overflow:hidden;
            background:linear-gradient(160deg,#1A6B8A 0%,#0F4D66 60%,#0A3547 100%);
            display:flex;flex-direction:column;justify-content:space-between;
            padding:36px 30px;color:#fff;
            /* برای گذاشتن عکس دلخواه، این خط رو با آدرس عکس عوض کنید: */
            /* background-image:url('آدرس-عکس-شما.jpg');background-size:cover;background-position:center; */
        }
        @media (max-width:800px){ .dul-visual{ min-height:200px;flex:none; } }
        .dul-visual:before{
            content:'';position:absolute;inset:0;
            background:radial-gradient(circle at 25% 20%,rgba(46,204,154,.25),transparent 55%),
                       radial-gradient(circle at 80% 85%,rgba(26,107,138,.4),transparent 50%);
        }
        .dul-visual-brand{position:relative;z-index:2;}
        .dul-visual-brand .dul-logo{font-size:26px;font-weight:800;display:flex;align-items:center;gap:8px;}
        .dul-visual-brand p{font-size:12.5px;opacity:.8;margin-top:8px;line-height:1.9;max-width:260px;}
        .dul-visual-deco{position:relative;z-index:2;display:flex;align-items:center;gap:10px;opacity:.75;font-size:11px;}
        .dul-tooth-icon{font-size:34px;opacity:.9;}

        /* ─── پنل فرم ─────────────────────────────────────────────── */
        .dul-form-panel{flex:1;padding:44px 40px;display:flex;flex-direction:column;justify-content:center;}
        @media (max-width:800px){ .dul-form-panel{ padding:32px 24px; } }
        .dul-welcome-icon{width:50px;height:50px;background:linear-gradient(135deg,#1A6B8A,#0F4D66);
            border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:16px;}
        .dul-form-panel h1{font-size:26px;font-weight:800;color:#1A2733;margin:0 0 4px;}
        .dul-form-panel .dul-sub{font-size:13px;color:#8CA0AB;margin-bottom:24px;}

        .dul-tabs{display:flex;gap:8px;margin-bottom:22px;background:#F3F6F8;border-radius:12px;padding:4px;}
        .dul-tab{flex:1;text-align:center;padding:10px;font-size:12.5px;font-weight:700;color:#8CA0AB;cursor:pointer;
            border-radius:9px;transition:all .2s;}
        .dul-tab.active{color:#fff;background:linear-gradient(135deg,#1A6B8A,#0F4D66);box-shadow:0 4px 12px rgba(26,107,138,.3);}

        .dul-pane{}
        .dul-label{font-size:11.5px;font-weight:700;color:#5A7080;display:block;margin-bottom:6px;}
        .dul-input-wrap{position:relative;margin-bottom:16px;}
        .dul-input{width:100%;padding:13px 14px 13px 14px;border:1.5px solid #E5ECF0;border-radius:12px;
            font-family:Vazirmatn,Tahoma,sans-serif;font-size:14px;box-sizing:border-box;background:#FBFCFD;
            transition:border-color .2s;}
        .dul-input:focus{outline:none;border-color:#1A6B8A;background:#fff;}
        .dul-btn{width:100%;background:linear-gradient(135deg,#1A6B8A,#0F4D66);color:#fff;border:none;border-radius:12px;
            padding:14px;font-size:14.5px;font-weight:700;cursor:pointer;font-family:inherit;
            box-shadow:0 8px 20px rgba(26,107,138,.28);transition:transform .15s;}
        .dul-btn:hover{transform:translateY(-1px);}
        .dul-error{background:#FDEAEA;border:1px solid #E05252;color:#E05252;border-radius:10px;padding:10px 14px;
            font-size:12px;margin-bottom:16px;}
        </style>

        <div id="dental-universal-login">
            <div class="dul-blob b1"></div>
            <div class="dul-blob b2"></div>

            <div class="dul-card">
                <!-- پنل عکس/برندینگ — برای گذاشتن عکس دلخواه، کلاس dul-visual
                     رو توی CSS بالا با background-image خودتون جایگزین کنید -->
                <div class="dul-visual">
                    <div class="dul-visual-brand">
                        <div class="dul-logo">🦷 <?php echo esc_html($clinic); ?></div>
                        <p>مدیریت هوشمند و یکپارچه‌ی کلینیک دندان‌پزشکی — از پذیرش تا درمان، همه‌جا در دسترس شما.</p>
                    </div>
                    <div class="dul-visual-deco">
                        <span class="dul-tooth-icon">🦷</span>
                        <span>سامانه‌ی مدیریت کلینیک</span>
                    </div>
                </div>

                <!-- پنل فرم -->
                <div class="dul-form-panel">
                    <div class="dul-welcome-icon">🔐</div>
                    <h1>خوش آمدید</h1>
                    <div class="dul-sub">لطفاً بخش مربوط به خودتان را انتخاب کنید</div>

                    <div class="dul-tabs">
                        <div class="dul-tab active" id="dul-tab-staff" onclick="dulSwitch('staff')">👤 کارمندان</div>
                        <div class="dul-tab" id="dul-tab-patient" onclick="dulSwitch('patient')">🙋 بیماران</div>
                    </div>

                    <!-- ورود کارمندان -->
                    <div class="dul-pane" id="dul-pane-staff">
                        <?php if ($staff_error): ?>
                        <div class="dul-error">⚠️ <?php echo esc_html($staff_error); ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <?php wp_nonce_field('dental_staff_login'); ?>
                            <label class="dul-label">نام کاربری یا ایمیل</label>
                            <input type="text" name="staff_user" class="dul-input" required autocomplete="username">
                            <label class="dul-label">رمز عبور</label>
                            <input type="password" name="staff_pass" class="dul-input" required autocomplete="current-password">
                            <button type="submit" name="dental_staff_login" class="dul-btn">ورود</button>
                        </form>
                        <div style="text-align:center;margin-top:14px;">
                            <a href="<?php echo esc_url(wp_lostpassword_url(get_permalink())); ?>" style="font-size:11.5px;color:#A0B4C0;text-decoration:none;">رمز عبور را فراموش کرده‌اید؟</a>
                        </div>
                    </div>

                    <!-- ورود بیماران (OTP) -->
                    <div class="dul-pane" id="dul-pane-patient" style="display:none;">
                        <?php
                        // نکته مهم (رفع قطعی مشکل اسکرول/هدر تکراری): چون این
                        // فرم اصلاً برای اجرای مستقل طراحی شده، خروجیش رو با
                        // output buffering می‌گیریم، بعد یه CSS override
                        // مشخص و «بعد از» خودِ استایل اصلی تزریق می‌کنیم —
                        // این‌جوری دیگه به ترتیب/رقابت کَسکید CSS وابسته نیست.
                        $atts = ['redirect' => ''];
                        $view_file = DENTAL_CORE_PATH . 'frontend/views/otp-login-form.php';
                        if (file_exists($view_file)) {
                            ob_start();
                            include $view_file;
                            $otp_html = ob_get_clean();

                            // استخراج $uid واقعی همین اجرا (چون رندوم هر بار فرق داره)
                            if (preg_match('/id="(dc\d+)-wrap"/', $otp_html, $m)) {
                                $real_uid = $m[1];
                                echo $otp_html;
                                echo '<style>
                                    #' . $real_uid . '-wrap{margin:0 !important;padding:0 !important;max-width:100% !important;}
                                    .' . $real_uid . '-card{box-shadow:none !important;border-radius:0 !important;margin:0 !important;}
                                    .' . $real_uid . '-header{display:none !important;}
                                    .' . $real_uid . '-body{padding:0 !important;}
                                </style>';
                            } else {
                                echo $otp_html; // فال‌بک — اگه الگو پیدا نشد، حداقل خودِ فرم رو نشون بده
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align:center;margin-top:16px;font-size:11px;color:rgba(255,255,255,.6);font-family:Vazirmatn,Tahoma,sans-serif;">
            سامانه مدیریت دندان‌پزشکی <strong>Dentoma</strong> — توسعه‌یافته توسط
            <a href="https://shokohiurl.ir" target="_blank" rel="noopener" style="color:#fff;">shokohiurl.ir</a>
        </div>

        <script>
        function dulSwitch(which){
            document.getElementById('dul-pane-staff').style.display   = which==='staff'   ? 'block' : 'none';
            document.getElementById('dul-pane-patient').style.display = which==='patient' ? 'block' : 'none';
            document.getElementById('dul-tab-staff').classList.toggle('active', which==='staff');
            document.getElementById('dul-tab-patient').classList.toggle('active', which==='patient');
        }
        </script>
        <?php
        return ob_get_clean();
    }
}
