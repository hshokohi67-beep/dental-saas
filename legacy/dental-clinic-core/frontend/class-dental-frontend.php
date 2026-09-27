<?php
defined('ABSPATH') || exit;

/**
 * Class Dental_Frontend
 * مدیریت پنل بیمار در فرانت‌اند
 */
class Dental_Frontend {

    public function __construct() {
        add_shortcode('dental_patient_portal', [$this, 'render_portal']);
        add_action('wp_enqueue_scripts',       [$this, 'enqueue_assets']);
        add_action('init',                     [$this, 'handle_ajax_actions']);
        add_action('template_redirect',        [$this, 'handle_print_request']);
        // ─── رفع باگ «headers already sent» در فرم شرح‌حال پرتال —
        // دقیقاً هم‌الگو با handle_print_request بالا: از template_redirect
        // (خیلی زودتر از رندر قالب سایت) اجرا می‌شه.
        add_action('template_redirect', function(){
            if (!isset($_GET['ptab']) || $_GET['ptab'] !== 'medhistory') return;
            if (!isset($_POST['dental_save_medhistory'])) return;
            $patient_id = self::get_current_patient_id();
            if (!$patient_id || !class_exists('Dental_Portal_Medical_History')) return;
            (new Dental_Portal_Medical_History($patient_id))->maybe_handle_post();
        });
    }

    public function enqueue_assets(): void {
        if (!$this->is_portal_page()) return;

        wp_enqueue_style('vazirmatn',
            'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap',
            [], null);

        wp_enqueue_style('sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
            [], '11');

        
                // ─── Dental Jalali Datepicker ────────────────────────────
        wp_enqueue_script('dental-datepicker',
            DENTAL_CORE_URL . 'assets/js/dental-datepicker.js',
            ['jquery'], DENTAL_CORE_VERSION, true);

        wp_enqueue_script('lucide',
            'https://cdn.jsdelivr.net/npm/lucide@0.408.0/dist/umd/lucide.min.js',
            [], '0.408.0', true);

        wp_enqueue_script('sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
            [], '11', true);

        wp_enqueue_script('dental-portal',
            DENTAL_CORE_URL . 'assets/js/portal.js',
            ['jquery','lucide'], DENTAL_CORE_VERSION, true);

        wp_localize_script('dental-portal', 'dentalPortal', [
            'apiBase'  => rest_url('dental/v1'),
            'nonce'    => wp_create_nonce('wp_rest'),
            'ajaxUrl'  => admin_url('admin-ajax.php'),
            'printUrl' => add_query_arg('dental_print', '1', get_permalink()),
            'currency' => get_option('dental_currency', 'تومان'),
        ]);
    }

    private function is_portal_page(): bool {
        global $post;
        return is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'dental_patient_portal');
    }

    public function handle_print_request(): void {
        if (!isset($_GET['dental_print'])) return;
        if (!is_user_logged_in()) { wp_safe_redirect(wp_login_url()); exit; }

        // ─── پرینت رضایت‌نامه ─────────────────────────────────────
        if ($_GET['dental_print'] === 'consent') {
            $key = sanitize_key($_GET['dental_print_consent'] ?? '');
            $requested_patient_id = (int)($_GET['patient_id'] ?? 0);

            $cu_roles = (array)wp_get_current_user()->roles;
            $is_staff = current_user_can('manage_options')
                || in_array('dental_admin', $cu_roles) || in_array('dental_doctor', $cu_roles)
                || in_array('dental_secretary', $cu_roles) || in_array('dental_financial', $cu_roles);

            if ($is_staff && $requested_patient_id) {
                // پرسنل کلینیک — اجازه پرینت پرونده هر بیماری را دارند
                $patient_id = $requested_patient_id;
            } else {
                // بیمار — فقط پرونده خودش
                $patient_id = $this->get_current_patient_id();
            }

            if (!$patient_id || !$key) wp_die('پرونده یافت نشد.');

            Dental_Consent_Print::render($patient_id, $key);
            exit;
        }

        // ─── پرینت خلاصه پرونده ───────────────────────────────────
        $patient_id = $this->get_current_patient_id();
        if (!$patient_id) wp_die('پرونده‌ای یافت نشد.');

        (new Dental_Portal_Print($patient_id))->render();
        exit;
    }

    public function handle_ajax_actions(): void {
        if (!isset($_POST['dental_action'])) return;
        if (!is_user_logged_in()) wp_send_json_error('احراز هویت نشده');

        $action     = sanitize_key($_POST['dental_action']);
        $patient_id = $this->get_current_patient_id();
        if (!$patient_id) wp_send_json_error('پرونده یافت نشد');

        match($action) {
            'upload_receipt'  => $this->handle_receipt_upload($patient_id),
            'update_profile'  => $this->handle_profile_update($patient_id),
            default           => wp_send_json_error('عملیات نامعتبر'),
        };
    }

    public function render_portal(): string {
        if (!is_user_logged_in()) {
            return $this->render_login_required();
        }

        $patient_id = $this->get_current_patient_id();

        // اگه ادمین باشه و پورتال رو میبینه — اولین بیمار رو نشون بده
        if (!$patient_id && current_user_can('manage_options')) {
            $posts = get_posts([
                'post_type'      => 'dental_patient',
                'post_status'    => 'publish',
                'numberposts'    => 1,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'fields'         => 'ids',
            ]);
            $patient_id = !empty($posts) ? (int)$posts[0] : 0;
        }

        if (!$patient_id) {
            return $this->render_not_patient();
        }

        $tab = sanitize_key($_GET['ptab'] ?? 'dashboard');

        ob_start();
        ?>
        <div id="dental-portal" style="direction:rtl;font-family:Vazirmatn,Tahoma,sans-serif;max-width:1100px;margin:0 auto;padding:20px 16px;">

            <?php $this->render_portal_header($patient_id); ?>

            <!-- تب‌های پنل -->
            <div style="display:flex;gap:4px;border-bottom:2px solid #EEF2F5;margin-bottom:24px;overflow-x:auto;">
                <?php
                $tabs = [
                    'dashboard' => ['layout-dashboard', 'داشبورد'],
                    'record'    => ['file-heart',        'پرونده'],
                    'financial' => ['credit-card',       'مالی'],
                    'wallet'    => ['wallet',            'کیف پول'],
                    'booking'   => ['calendar',          'نوبت‌ها'],
                'consent'   => ['file-signature',    'رضایت‌نامه'],
                'medhistory'=> ['heart-pulse',        'شرح‌حال'],
                'profile'   => ['user',              'پروفایل'],
                ];
                foreach($tabs as $t => [$icon, $label]):
                    $active = $tab === $t;
                    $url    = add_query_arg('ptab', $t, get_permalink());
                ?>
                <a href="<?php echo esc_url($url); ?>"
                   style="display:flex;align-items:center;gap:6px;padding:10px 16px;text-decoration:none;font-size:13px;font-weight:500;white-space:nowrap;border-bottom:2px solid <?php echo $active?'#1A6B8A':'transparent'; ?>;margin-bottom:-2px;color:<?php echo $active?'#1A6B8A':'#5A7080'; ?>;transition:all .2s;">
                    <i data-lucide="<?php echo esc_attr($icon); ?>" style="width:15px;height:15px;"></i>
                    <?php echo esc_html($label); ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- محتوای تب -->
            <?php
            match($tab) {
                'record'    => (new Dental_Portal_Record($patient_id))->render(),
                'financial' => (new Dental_Portal_Financial($patient_id))->render(),
                'wallet'    => (new Dental_Portal_Wallet($patient_id))->render(),
                'booking'   => class_exists('Dental_Booking_Wizard')
                                ? (new Dental_Booking_Wizard($patient_id, false))->render()
                                : print('<div style="direction:rtl;text-align:center;padding:40px;color:#7A96A4;font-family:Vazirmatn,Tahoma,sans-serif;">پلاگین نوبت‌دهی فعال نیست.</div>'),
                'consent'   => class_exists('Dental_Portal_Consent')
                                ? (new Dental_Portal_Consent($patient_id))->render()
                                : print('<div style="direction:rtl;text-align:center;padding:40px;color:#7A96A4;font-family:Vazirmatn,Tahoma,sans-serif;">این بخش در دسترس نیست.</div>'),
                'medhistory'=> class_exists('Dental_Portal_Medical_History')
                                ? (new Dental_Portal_Medical_History($patient_id))->render()
                                : print('<div style="direction:rtl;text-align:center;padding:40px;color:#7A96A4;font-family:Vazirmatn,Tahoma,sans-serif;">این بخش در دسترس نیست.</div>'),
                'profile'   => (new Dental_Portal_Profile($patient_id))->render(),
                default     => (new Dental_Portal_Dashboard($patient_id))->render(),
            };
            ?>

        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
        return ob_get_clean();
    }

    private function render_portal_header(int $patient_id): void {
        $patient  = get_post($patient_id);
        $wallet   = Dental_Patient_Wallet::get_balance($patient_id);
        $points   = (int)get_post_meta($patient_id, '_loyalty_points', true);
        $clinic   = get_option('dental_clinic_name', get_bloginfo('name'));
        $ini      = mb_substr($patient->post_title ?? '', 0, 1);
        ?>
        <div style="background:linear-gradient(135deg,#1A6B8A,#0F4D66);border-radius:16px;padding:20px 24px;margin-bottom:20px;color:#fff;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div style="display:flex;align-items:center;gap:14px;">
                    <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;flex-shrink:0;">
                        <?php echo esc_html($ini); ?>
                    </div>
                    <div>
                        <div style="font-size:18px;font-weight:700;"><?php echo esc_html($patient->post_title ?? ''); ?></div>
                        <div style="opacity:.7;font-size:12px;margin-top:2px;"><?php echo esc_html($clinic); ?></div>
                    </div>
                </div>
                <div style="display:flex;gap:16px;flex-wrap:wrap;">
                    <div style="text-align:center;">
                        <div style="font-size:18px;font-weight:700;"><?php echo number_format($wallet); ?></div>
                        <div style="font-size:11px;opacity:.7;">کیف پول (ت)</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:18px;font-weight:700;"><?php echo number_format($points); ?></div>
                        <div style="font-size:11px;opacity:.7;">امتیاز</div>
                    </div>
                    <a href="<?php echo esc_url(add_query_arg('dental_print','1',get_permalink())); ?>"
                       target="_blank"
                       style="display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);color:#fff;padding:8px 14px;border-radius:8px;text-decoration:none;font-size:12px;">
                        <i data-lucide="printer" style="width:14px;height:14px;"></i>
                        پرینت پرونده
                    </a>
                    <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>"
                       style="display:flex;align-items:center;gap:6px;background:rgba(224,82,82,.25);border:1px solid rgba(224,82,82,.4);color:#fff;padding:8px 14px;border-radius:8px;text-decoration:none;font-size:12px;">
                        <i data-lucide="log-out" style="width:14px;height:14px;"></i>
                        خروج
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_login_required(): string {
        $login_page = get_page_by_path('dental-login');
        $login_url  = $login_page
            ? add_query_arg('redirect_to', urlencode(get_permalink()), get_permalink($login_page))
            : wp_login_url(get_permalink());
        return '<div style="direction:rtl;text-align:center;padding:48px 20px;font-family:Vazirmatn,Tahoma,sans-serif;">
            <div style="font-size:48px;margin-bottom:16px;">🔒</div>
            <h2 style="color:#1A2733;margin-bottom:8px;">ورود به پنل کاربری</h2>
            <p style="color:#5A7080;margin-bottom:20px;">برای مشاهده پرونده خود ابتدا وارد شوید.</p>
            <a href="' . esc_url($login_url) . '" style="background:#1A6B8A;color:#fff;padding:10px 24px;border-radius:8px;text-decoration:none;font-size:14px;">ورود / ثبت‌نام</a>
        </div>';
    }

    private function render_not_patient(): string {
        return '<div style="direction:rtl;text-align:center;padding:48px 20px;font-family:Vazirmatn,Tahoma,sans-serif;">
            <div style="font-size:48px;margin-bottom:16px;">🦷</div>
            <h2 style="color:#1A2733;">پرونده‌ای یافت نشد</h2>
            <p style="color:#5A7080;">حساب کاربری شما به هیچ پرونده‌ای متصل نیست.</p>
        </div>';
    }

    public static function get_current_patient_id(): int {
        $user_id = get_current_user_id();
        if (!$user_id) return 0;

        $posts = get_posts([
            'post_type'   => 'dental_patient',
            'post_status' => 'publish',
            'meta_query'  => [['key'=>'_patient_wp_user_id','value'=>$user_id]],
            'numberposts' => 1,
            'fields'      => 'ids',
        ]);

        return !empty($posts) ? (int)$posts[0] : 0;
    }

    private function handle_receipt_upload(int $patient_id): void {
        check_ajax_referer('dental_portal_nonce', 'nonce');

        if (empty($_FILES['receipt'])) {
            wp_send_json_error('فایلی ارسال نشده');
        }

        $item_id = (int)($_POST['item_id'] ?? 0);
        $notes   = sanitize_text_field($_POST['notes'] ?? '');

        // ─── تأیید مالکیت: این قسط واقعاً متعلق به همین بیمار است؟ ──
        // بدون این چک، هر بیمار لاگین‌شده می‌توانست با تغییر item_id
        // برای قسط بیمار دیگری فیش ثبت کند (IDOR).
        global $wpdb;
        $owner_patient_id = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT i.patient_id FROM {$wpdb->prefix}dental_installment_items ii
             JOIN {$wpdb->prefix}dental_installments i ON ii.installment_id = i.id
             WHERE ii.id = %d",
            $item_id
        ));
        if (!$item_id || $owner_patient_id !== $patient_id) {
            wp_send_json_error('دسترسی به این قسط مجاز نیست.');
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attachment_id = media_handle_upload('receipt', $patient_id);

        if (is_wp_error($attachment_id)) {
            wp_send_json_error($attachment_id->get_error_message());
        }

        global $wpdb;
        // ذخیره receipt روی آیتم قسط
        $wpdb->update(
            $wpdb->prefix . 'dental_installment_items',
            ['receipt_image_id' => $attachment_id, 'notes' => $notes, 'status' => 'partial'],
            ['id' => $item_id],
            ['%d','%s','%s'], ['%d']
        );

        // SMS به مدیر مالی
        $manager_mobile = get_option('dental_financial_manager_mobile', '');
        if ($manager_mobile && class_exists('Dental_SMS_Dispatcher')) {
            $patient = get_post($patient_id);
            $tpl = get_option('dental_sms_tpl_receipt_submitted',
                "فیش پرداخت جدید ثبت شد.\nکاربر: {patient_name}\nلطفاً تأیید کنید."
            );
            $msg = str_replace(
                ['{patient_name}'],
                [$patient->post_title ?? ''],
                $tpl
            );
            (new Dental_SMS_Dispatcher())->queue($manager_mobile, $msg, 'custom', $patient_id);
        }

        wp_send_json_success(['message' => 'فیش با موفقیت ثبت شد. منتظر تأیید باشید.']);
    }

    private function handle_profile_update(int $patient_id): void {
        check_ajax_referer('dental_portal_nonce', 'nonce');

        $name  = sanitize_text_field($_POST['name']  ?? '');
        $phone = sanitize_text_field($_POST['phone']  ?? '');
        $national_id = preg_replace('/[^0-9]/', '', $_POST['national_id'] ?? '');

        if ($name) wp_update_post(['ID' => $patient_id, 'post_title' => $name]);
        if ($phone) update_post_meta($patient_id, '_patient_mobile', $phone);
        if ($national_id) update_post_meta($patient_id, '_patient_national_id', substr($national_id, 0, 10));

        wp_send_json_success(['message' => 'اطلاعات ذخیره شد.']);
    }
}
