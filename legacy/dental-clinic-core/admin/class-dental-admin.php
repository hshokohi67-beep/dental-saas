<?php
defined('ABSPATH') || exit;

class Dental_Admin {

    public function __construct() {
        add_action('admin_menu',            [$this, 'register_menus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_bar_menu',        [$this, 'add_admin_bar_item'], 100);
        add_action('admin_footer',          [$this, 'render_dev_logs']);
        add_action('admin_notices',         [$this, 'maybe_show_dev_banner']);
        add_action('wp_ajax_dental_check_today_appt', [$this, 'ajax_check_today_appt']);
        add_action('wp_ajax_dental_toggle_task',      [$this, 'ajax_toggle_task']);
        add_action('wp_ajax_dental_assign_task',       [$this, 'ajax_assign_task']);
        add_action('wp_ajax_dental_send_message',      [$this, 'ajax_send_message']);
        add_action('wp_ajax_dental_mark_message_read', [$this, 'ajax_mark_message_read']);
        add_action('wp_ajax_dental_delete_message', [$this, 'ajax_delete_message']);
        add_action('wp_ajax_dental_search_inventory_items', [$this, 'ajax_search_inventory_items']);
        add_action('wp_ajax_dental_search_inventory_items_cat', [$this, 'ajax_search_inventory_items_cat']);
        add_action('wp_ajax_dental_dismiss_changelog', [$this, 'ajax_dismiss_changelog']);
        add_action('wp_ajax_dental_save_patient_insurance', [$this, 'ajax_save_patient_insurance']);
        add_action('admin_init', [$this, 'maybe_stream_imaging_file']);
        add_action('admin_init', function(){ if (class_exists('Dental_Prescription_Manager')) Dental_Prescription_Manager::seed_default_drugs(); });
        add_action('admin_init', function(){ if (class_exists('Dental_Prescription_Manager')) Dental_Prescription_Manager::fix_inactive_seed_bug(); });
        add_action('admin_init', function(){ if (class_exists('Dental_Prescription_Manager')) Dental_Prescription_Manager::fix_missing_english_names(); });
        add_action('dental_auto_backup_cron', function(){ if (class_exists('Dental_Backup_Manager')) Dental_Backup_Manager::run_scheduled_backup(); });
        add_action('admin_init', function(){ if (class_exists('Dental_Pathway_Manager')) Dental_Pathway_Manager::seed_default_templates(); });
        add_action('wp_ajax_dental_create_pathway', [$this, 'ajax_create_pathway']);
        add_action('wp_ajax_dental_update_pathway_step', [$this, 'ajax_update_pathway_step']);
        add_action('wp_ajax_dental_complete_pathway_step_catalog', [$this, 'ajax_complete_pathway_step_with_catalog']);
        add_action('wp_ajax_dental_save_endodontic_details', [$this, 'ajax_save_endodontic_details']);
        add_action('wp_ajax_dental_get_endodontic_details', [$this, 'ajax_get_endodontic_details']);
        add_action('wp_ajax_dental_save_prescription', [$this, 'ajax_save_prescription']);
        add_action('admin_init', function(){
            if (get_option('dental_backup_cron_healed')) return;
            if (class_exists('Dental_Installer')) Dental_Installer::schedule_cron();
            update_option('dental_backup_cron_healed', 1);
        });
        // ─── رفع ریشه‌ای باگ «صفحه‌ی سفید بعد از ذخیره‌ی تنظیمات» —
        // منطق ذخیره باید زودتر از رندر صفحه (که سایدبار رو دیر بعدش
        // می‌فرسته) اجرا بشه، وگرنه wp_safe_redirect() شکست می‌خوره.
        add_action('admin_init', function(){
            if (!isset($_GET['page']) || $_GET['page'] !== 'dental-settings') return;
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
            if (class_exists('Dental_Page_Settings')) {
                (new Dental_Page_Settings())->maybe_handle_post();
            }
        });
        // ─── همون رفع، برای دفتر روزانه هم — کاربر خودش تأیید کرد که
        // این مشکل «هرجایی ثبت باشه» تکرار می‌شه، پس همون الگو رو
        // پیشگیرانه اینجا هم پیاده می‌کنیم.
        add_action('admin_init', function(){
            if (!isset($_GET['page'])) return;
            // «دفتر روزانه» دیگه صفحه‌ی جدا نیست، بخش «مالی؟section=ledger»
            // شده — نه dental-ledger رو حذف کردیم (اگه لینک قدیمی‌ای مونده
            // باشه بشکنه)، هم این مسیر جدید رو اضافه کردیم.
            $is_ledger_section = $_GET['page'] === 'dental-financial' && ($_GET['section'] ?? '') === 'ledger';
            if ($_GET['page'] !== 'dental-ledger' && !$is_ledger_section) return;
            if (class_exists('Dental_Page_Ledger')) {
                (new Dental_Page_Ledger())->maybe_handle_post();
            }
        });
        // ─── همون رفع، برای شرح‌حال بیمار (تب پزشکی) — چون این صفحه
        // زیرمجموعه‌ی پروفایل بیماره، از page=dental-patients + وجود
        // فیلد POST مخصوصش تشخیص می‌دیم ────────────────────────────
        add_action('admin_init', function(){
            if (!isset($_GET['page']) || $_GET['page'] !== 'dental-patients') return;
            if (!isset($_POST['dental_save_medical'])) return;
            $patient_id = (int)($_GET['id'] ?? 0);
            if (!$patient_id || !class_exists('Dental_Page_Medical')) return;
            (new Dental_Page_Medical($patient_id))->maybe_handle_post();
        });
        // ─── همون رفع، برای بقیه‌ی صفحاتی که همون الگوی مشکل‌دار رو
        // داشتن (طبق گزارش اصلی باگ) ──────────────────────────────────
        add_action('admin_init', function(){
            if (!isset($_GET['page'])) return;
            $page = $_GET['page'];
            if ($page === 'dental-attendance' && class_exists('Dental_Page_Attendance')) {
                (new Dental_Page_Attendance())->maybe_handle_post();
            }
            if ($page === 'dental-financial' && class_exists('Dental_Page_Financial')) {
                (new Dental_Page_Financial())->maybe_handle_post();
            }
            if ($page === 'dental-reception' && class_exists('Dental_Page_Reception')) {
                (new Dental_Page_Reception())->maybe_handle_post();
            }
            if ($page === 'dental-patients' && class_exists('Dental_Page_Patients')
                && (isset($_POST['dental_save_patient']) || isset($_GET['trash']) || isset($_GET['restore']) || isset($_GET['delete_forever']))) {
                (new Dental_Page_Patients())->maybe_handle_post();
            }
            if ($page === 'dental-patients' && class_exists('Dental_Page_Lab')
                && (isset($_POST['dental_save_lab']) || isset($_POST['dental_update_lab_status']))) {
                $patient_id = (int)($_GET['id'] ?? 0);
                if ($patient_id) (new Dental_Page_Lab($patient_id))->maybe_handle_post();
            }
        });
        // ─── دانلود بکاپ (دستی + خودکار) — طبق همون درسی که از باگ
        // «صفحه‌ی سفید بعد از ذخیره» گرفتیم، header() باید قبل از هر
        // خروجی HTML باشه، پس روی admin_init (زودهنگام)، نه داخل render() ──
        add_action('admin_init', function(){
            if (!isset($_GET['page']) || $_GET['page'] !== 'dental-settings') return;
            if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'dental_download_backup')) return;
            if (!current_user_can('manage_options') && !in_array('dental_admin', (array)wp_get_current_user()->roles)) {
                wp_die('این بخش فقط برای مدیر کلینیک قابل‌دسترسه.');
            }
            if (isset($_GET['download_backup']) && class_exists('Dental_Backup_Manager')) {
                Dental_Backup_Manager::stream_backup();
            }
            if (isset($_GET['download_auto_backup']) && class_exists('Dental_Backup_Manager')) {
                $filename = sanitize_file_name($_GET['download_auto_backup']);
                $dir = Dental_Backup_Manager::get_backup_dir();
                $path = realpath($dir . '/' . $filename);
                if ($path && strpos($path, realpath($dir)) === 0 && file_exists($path)) {
                    header('Content-Type: application/sql; charset=utf-8');
                    header('Content-Disposition: attachment; filename=' . $filename);
                    header('Pragma: no-cache');
                    readfile($path);
                    exit;
                }
                wp_die('فایل یافت نشد.');
            }
        });
        add_action('wp_ajax_dental_imaging_upload', [$this, 'ajax_imaging_upload']);
        add_action('wp_ajax_dental_imaging_save_annotation', [$this, 'ajax_imaging_save_annotation']);
        add_action('wp_ajax_dental_imaging_delete_annotation', [$this, 'ajax_imaging_delete_annotation']);
        add_action('wp_ajax_dental_imaging_save_measurement', [$this, 'ajax_imaging_save_measurement']);
        add_action('wp_ajax_dental_imaging_delete_image', [$this, 'ajax_imaging_delete_image']);
        add_action('wp_ajax_dental_imaging_get_meta', [$this, 'ajax_imaging_get_meta']);
        add_action('wp_ajax_dental_insurance_upload_doc', [$this, 'ajax_insurance_upload_doc']);
        add_action('wp_ajax_dental_search_drugs', [$this, 'ajax_search_drugs']);
        // ─── تغییر مهم: به‌جای AJAX (که بعدش نیاز به یه ریدایرکت/تب‌باز‌کردن
        // جاوااسکریپتی جدا داشت — همون منبع باگ «تب جدید محتوای صفحه‌ی
        // قبلی رو نشون می‌ده»)، از admin_post استفاده می‌کنیم: فرم واقعی
        // HTML مستقیم اینجا POST می‌شه، و همون‌جا (بدون ریدایرکت) خروجی
        // پرینت رو برمی‌گردونیم — چون فرم target="_blank" داره، خودِ
        // مرورگر (نه جاوااسکریپت) این‌کارو صد‌درصد قابل‌اعتماد مدیریت می‌کنه.
        add_action('admin_post_dental_save_and_print_rx', [$this, 'handle_save_and_print_rx']);
        add_action('admin_init', [$this, 'maybe_print_prescription']);
        add_action('wp_ajax_dental_get_insurance_checklist', [$this, 'ajax_get_insurance_checklist']);
        add_action('admin_init', [$this, 'maybe_print_insurance_docs']);
        add_action('admin_init', [$this, 'maybe_print_settlement_bundle']);
        add_action('wp_ajax_dental_doctor_report_details', [$this, 'ajax_doctor_report_details']);
        add_action('wp_ajax_dental_inventory_quick_update', [$this, 'ajax_inventory_quick_update']);
        add_action('wp_ajax_dental_cycle_rota', [$this, 'ajax_cycle_rota']);
        add_action('wp_ajax_dental_toggle_rota_lock', [$this, 'ajax_toggle_rota_lock']);
        add_action('wp_ajax_dental_get_shift_cell', [$this, 'ajax_get_shift_cell']);
        add_action('wp_ajax_dental_save_shift_cell', [$this, 'ajax_save_shift_cell']);
        add_action('wp_ajax_dental_switch_role', [$this, 'ajax_switch_role']);
        add_action('wp_ajax_dental_get_inventory_items', [$this, 'ajax_get_inventory_items']);
        add_action('wp_ajax_dental_log_consumption', [$this, 'ajax_log_consumption']);
        add_action('dental_daily_cron', [$this, 'cleanup_old_messages']);
        add_action('dental_daily_cron', [$this, 'scan_recalls_daily']);
        add_action('wp_ajax_dental_set_status',        [$this, 'ajax_set_status']);
        add_action('wp_ajax_dental_refresh_workspace', [$this, 'ajax_refresh_workspace']);
        add_action('wp_ajax_dental_quick_patient_view',[$this, 'ajax_quick_patient_view']);
        add_action('wp_ajax_dental_toggle_chart_item', [$this, 'ajax_toggle_chart_item']);
        add_action('wp_ajax_dental_upload_gallery_image', [$this, 'ajax_upload_gallery_image']);
        add_action('wp_ajax_dental_search_patients',   [$this, 'ajax_search_patients']);
        add_action('wp_ajax_dental_clock_in',  [$this, 'ajax_clock_in']);
        add_action('wp_ajax_dental_clock_out', [$this, 'ajax_clock_out']);
        add_action('wp_ajax_dental_start_break', [$this, 'ajax_start_break']);
        add_action('wp_ajax_dental_end_break',   [$this, 'ajax_end_break']);
        add_action('wp_ajax_dental_pos_test_connection', [$this, 'ajax_pos_test_connection']);
        add_action('wp_ajax_dental_pos_charge',  [$this, 'ajax_pos_charge']);
        add_action('wp_ajax_dental_save_catalog_price', [$this, 'ajax_save_catalog_price']);
        add_action('wp_ajax_dental_save_catalog_consent', [$this, 'ajax_save_catalog_consent']);
        add_action('wp_ajax_dental_catalog_search_pricing', [$this, 'ajax_catalog_search_pricing']);
        add_action('wp_ajax_dental_save_catalog_filter', [$this, 'ajax_save_catalog_filter']);
        add_action('wp_ajax_dental_catalog_browse', [$this, 'ajax_catalog_browse']);
        add_action('wp_ajax_dental_catalog_full_tree', [$this, 'ajax_catalog_full_tree']);
        add_action('wp_ajax_dental_catalog_record_treatment', [$this, 'ajax_catalog_record_treatment']);
        add_action('wp_ajax_dental_catalog_toggle_treatment', [$this, 'ajax_catalog_toggle_treatment']);
        add_action('wp_ajax_dental_catalog_delete_treatment', [$this, 'ajax_catalog_delete_treatment']);
        add_action('wp_ajax_dental_get_patient_summary', [$this, 'ajax_get_patient_summary']);
        add_action('admin_init', [$this, 'touch_online']);
        add_action('wp_login', [$this, 'on_login_auto_clock_in'], 10, 2);
        add_action('admin_init', [$this, 'maybe_auto_clock_out']);
        add_action('admin_init', [$this, 'maybe_self_heal_columns']);
        add_action('admin_init', [$this, 'maybe_self_heal_tables']);
        add_action('admin_init', [$this, 'maybe_self_heal_roles']);
        add_action('admin_init', [$this, 'maybe_redirect_to_wizard']);
        add_action('admin_init', [$this, 'maybe_print_reception_queue']);
        add_action('admin_init', [$this, 'maybe_export_csv']);
        add_action('admin_init', [$this, 'maybe_print_invoice']);
        add_action('admin_init', [$this, 'maybe_print_shift_week']);
        add_action('admin_init', [$this, 'maybe_print_rota']);
        add_action('admin_init', [$this, 'maybe_print_performance']);
        add_action('admin_menu', [$this, 'hide_wp_native_menus'], 999);
        add_action('admin_footer', [$this, 'render_sidebar_branding']);
        add_filter('admin_footer_text', [$this, 'render_page_footer_credit']);
        // نکته: قبلاً اینجا از فیلتر show_admin_bar استفاده می‌شد که با
        // وجود چندین تلاش (حتی ثبت روی after_setup_theme)، بازم قابل‌اعتماد
        // نبود. الان با admin_head + CSS مستقیم روی #wpadminbar جایگزین
        // شده — قطعی‌تره و فقط دقیقاً روی صفحات این پلاگین اعمال می‌شه.
        add_action('admin_head', [$this, 'hide_admin_bar_on_dental_pages']);
        add_action('admin_head', [$this, 'hide_wp_residual_ui']);
        add_action('admin_init', [$this, 'restrict_to_own_pages']);
    }

    // ─── مخفی‌کردن منوهای پیش‌فرض وردپرس برای کارکنان کلینیک ─────
    // فقط ادمین واقعی وردپرس (manage_options) رابط کامل وردپرس را می‌بیند.
    // نقش‌های ما (مدیر کلینیک، دکتر، منشی، مالی، دستیار) فقط منوی
    // اختصاصی کلینیک/نوبت‌دهی را می‌بینند، نه ابزارهای فنی وردپرس.
    public function hide_wp_native_menus(): void {
        if (current_user_can('manage_options')) return;
        remove_menu_page('index.php');
        remove_menu_page('edit.php');
        remove_menu_page('upload.php');
        remove_menu_page('edit-comments.php');
        remove_menu_page('themes.php');
        remove_menu_page('plugins.php');
        remove_menu_page('users.php');
        remove_menu_page('tools.php');
        remove_menu_page('options-general.php');
        // نکته: حتی بعد از حذف users.php، خودِ وردپرس یک آیتم «پروفایل من»
        // به‌صورت جداگانه اضافه می‌کند که باید این را هم صریحاً حذف کرد.
        remove_menu_page('profile.php');
    }

    // ─── مخفی‌کردن قطعیِ نوار بالای وردپرس — فقط توی صفحات این پلاگین ──
    // چرا نه show_admin_bar: اون فیلتر با اینکه از نظر تئوری باید کار
    // کنه، عملاً (احتمالاً به‌خاطر ترتیب لود پلاگین‌ها/تم یا یه پلاگین
    // دیگه که دوباره trueاش می‌کنه) قابل‌اعتماد نبود. راه‌حل قطعی این‌جا:
    // مستقیم روی admin_head، فقط وقتی صفحه‌ی جاری مال ماست، با CSS
    // خودِ عنصر #wpadminbar رو مخفی و فضای خالی بالای صفحه رو صفر می‌کنیم.
    public function hide_admin_bar_on_dental_pages(): void {
        if (!$this->is_dental_page()) return;
        ?>
        <style>
            #wpadminbar { display: none !important; }
            html.wp-toolbar { padding-top: 0 !important; }
            body.admin-bar { margin-top: 0 !important; }
        </style>
        <?php
    }

    // ─── تشخیص «آیا صفحه‌ی جاری مال پلاگین ماست؟» — یه‌جا، برای
    // استفاده‌ی مشترک توی همه‌ی جاهایی که نیاز به این تشخیص هست ────
    private function is_dental_page(): bool {
        $page = sanitize_key($_GET['page'] ?? '');
        if (strpos($page, 'dental') === 0) return true;
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        return $screen && strpos($screen->id, 'dental') !== false;
    }

    // ─── مخفی‌کردن بقایای ظاهری وردپرس که با remove_menu_page پاک نمی‌شن ──
    // (دکمه «جمع‌کردن فهرست» که در پایین سایدبار همیشه وردپرس رندرش می‌کند)
    public function hide_wp_residual_ui(): void {
        if (current_user_can('manage_options')) return;
        echo '<style>
            #collapse-menu, #collapse-button, .folded #collapse-menu,
            #wp-admin-bar-my-account, #footer-thankyou, #footer-upgrade,
            #screen-meta-links, .update-nag, .notice.notice-success.is-dismissible + .updated { display:none !important; }
            #wpwrap { min-height: 100vh; }
        </style>';
    }

    // ─── برند «دنتوما» زیر منوی کناری — فقط توی صفحات خودِ پلاگین ────
    public function render_sidebar_branding(): void {
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'dental') === false) return;
        ?>
        <style>
        #dc-sidebar-brand {
            position: sticky; bottom: 0; padding: 14px 10px;
            border-top: 1px solid rgba(255,255,255,.08);
            text-align: center; background: inherit;
        }
        #dc-sidebar-brand a { text-decoration: none; display: block; }
        #dc-sidebar-brand .dc-brand-name { font-size: 12px; font-weight: 700; color: #fff; opacity: .85; }
        #dc-sidebar-brand .dc-brand-sub { font-size: 10px; color: #fff; opacity: .5; margin-top: 2px; }
        </style>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            var menu = document.getElementById('adminmenu');
            if (!menu || document.getElementById('dc-sidebar-brand')) return;
            var el = document.createElement('li');
            el.id = 'dc-sidebar-brand';
            el.innerHTML = '<a href="https://shokohiurl.ir" target="_blank" rel="noopener">' +
                '<div class="dc-brand-name">🦷 سامانه مدیریت دندان‌پزشکی Dentoma</div>' +
                '<div class="dc-brand-sub">توسعه‌یافته توسط shokohiurl.ir</div>' +
                '</a>';
            menu.parentNode.appendChild(el);
        });
        </script>
        <?php
    }

    // ─── اعتبار توسعه‌دهنده پایین هر صفحه از پلاگین (جایگزین متن پیش‌فرض
    // «Thank you for creating with WordPress» — فقط توی صفحات خودمون) ──
    public function render_page_footer_credit(string $text): string {
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'dental') === false) return $text;
        return 'سامانه مدیریت دندان‌پزشکی <strong>Dentoma</strong> — توسعه‌یافته توسط <a href="https://shokohiurl.ir" target="_blank" rel="noopener">shokohiurl.ir</a>';
    }

    // ─── قفل واقعی سمت سرور — نه فقط مخفی‌کردن ظاهری ──────────────
    // اگر کسی مستقیم آدرس یک صفحه وردپرسی (نه صفحات خودمان) را در
    // مرورگر تایپ کند، به داشبورد خودمان هدایت می‌شود. فرض بر این
    // است که هیچ کاربری (به‌جز مدیر ارشد واقعی) نباید اصلاً بداند
    // زیرساخت این سامانه وردپرس است.
    public function restrict_to_own_pages(): void {
        if (current_user_can('manage_options')) return;

        global $pagenow;
        if ($pagenow === 'admin-ajax.php') return; // برای عملکرد AJAX پلاگین لازم است

        if ($pagenow === 'admin.php') {
            $page = sanitize_key($_GET['page'] ?? '');
            if (strpos($page, 'dental-') === 0) return; // صفحات خودمان مجازند
        }

        // هرچیز دیگری (index.php, profile.php, edit.php, upload.php, options.php و...) ممنوع
        wp_safe_redirect(admin_url('admin.php?page=dental-dashboard'));
        exit;
    }

    // ─── پرینت لیست پذیرش — باید قبل از رندر chrome پیشخوان اجرا شود ──
    // (همون الگوی باگی که قبلاً برای پرینت روزانه تقویم نوبت‌دهی پیدا و
    // رفع کردیم؛ اینجا هم همین مشکل بود و تا الان رفع نشده بود.)
    public function maybe_print_reception_queue(): void {
        if (!isset($_GET['page']) || $_GET['page'] !== 'dental-reception') return;
        if (!isset($_GET['print_queue'])) return;
        // رفع «باید رفرش بشه تا پرینت بیاد» — هوک‌های admin_init دیگه‌ی این
        // پلاگین ممکنه قبل از این یه خروجی کوچیک (حتی یه Warning) تولید
        // کرده باشن؛ این خط بافر رو پاک می‌کنه تا پاسخ از صفر شروع بشه.
        while (ob_get_level() > 0) { ob_end_clean(); }
        // نکته: قبلاً فقط dental_manage_reception چک می‌شد که پزشک رو رد
        // می‌کرد — الان manage_dental (که پزشک و مدیر کلینیک دارن) هم اضافه شد.
        $allowed = current_user_can('manage_options')
            || current_user_can('dental_manage_reception')
            || current_user_can('manage_dental');
        if (!$allowed) wp_die('دسترسی مجاز نیست.');
        (new Dental_Page_Reception())->render_print();
        exit;
    }

    // ─── خروجی CSV گزارش‌ها — باید قبل از رندر chrome پیشخوان اجرا شود ──
    public function maybe_export_csv(): void {
        if (!isset($_GET['page']) || !isset($_GET['export_csv'])) return;

        // «گزارش درآمد و عملکرد» دیگه صفحه‌ی جدا نیست، بخش
        // «گزارش‌گیری؟section=performance» شده.
        $is_perf_report = $_GET['page'] === 'dental-performance-report'
            || ($_GET['page'] === 'dental-reports' && ($_GET['section'] ?? '') === 'performance');
        if ($is_perf_report) {
            $cu_roles_csv = wp_get_current_user()->roles;
            $allowed = current_user_can('manage_options') || in_array('dental_admin',$cu_roles_csv) || in_array('dental_doctor',$cu_roles_csv);
            if (!$allowed) wp_die('دسترسی مجاز نیست.');
            check_admin_referer('dental_export_perf');
            (new Dental_Page_Performance_Report())->export_csv();
            exit;
        }
        if ($_GET['page'] === 'dental-catalog-pricing') {
            if (!current_user_can('manage_options')) wp_die('دسترسی مجاز نیست.');
            check_admin_referer('dental_export_catalog');
            (new Dental_Page_Catalog_Pricing())->export_csv();
            exit;
        }
    }

    // ─── چاپ فاکتور — باید قبل از رندر chrome پیشخوان اجرا شود ──────
    public function maybe_print_invoice(): void {
        if (!isset($_GET['page']) || $_GET['page'] !== 'dental-dashboard' || !isset($_GET['print_invoice'])) return;
        while (ob_get_level() > 0) { ob_end_clean(); } // رفع «باید رفرش بشه تا پرینت بیاد»
        $cu = wp_get_current_user();
        $roles = (array)$cu->roles;
        $allowed = current_user_can('manage_options')
            || in_array('dental_admin', $roles)
            || in_array('dental_doctor', $roles)
            || in_array('dental_financial', $roles)
            || in_array('dental_secretary', $roles);
        if (!$allowed) {
            wp_die('دسترسی مجاز نیست.');
        }
        (new Dental_Page_Invoice_Print())->render();
        exit;
    }

    // نکته: از همون صفحه دشبورد اثبات‌شده استفاده می‌کنه (نه صفحه مجزا)
    // دقیقاً طبق درسی که از باگ فاکتور گرفتیم.
    public function maybe_print_shift_week(): void {
        if (!isset($_GET['page']) || $_GET['page'] !== 'dental-dashboard' || !isset($_GET['print_shift_week'])) return;
        while (ob_get_level() > 0) { ob_end_clean(); } // رفع «باید رفرش بشه تا پرینت بیاد»
        if (!$this->user_has_role(['dental_admin'])) wp_die('این بخش فقط برای مدیر کلینیک قابل‌دسترسه.');
        $ym = sanitize_text_field($_GET['ym'] ?? Dental_Jalali::today('Y/m'));
        [$jy, $jm] = array_map('intval', explode('/', $ym));
        (new Dental_Page_Shift_Weekly())->render_print($jy, $jm);
        exit;
    }

    public function maybe_print_rota(): void {
        if (!isset($_GET['page']) || $_GET['page'] !== 'dental-dashboard' || !isset($_GET['print_rota'])) return;
        while (ob_get_level() > 0) { ob_end_clean(); } // رفع «باید رفرش بشه تا پرینت بیاد»
        if (!$this->user_has_role(['dental_admin'])) wp_die('این بخش فقط برای مدیر کلینیک قابل‌دسترسه.');
        $ym = sanitize_text_field($_GET['ym'] ?? Dental_Jalali::today('Y/m'));
        [$jy, $jm] = array_map('intval', explode('/', $ym));
        $type = sanitize_key($_GET['rota_type'] ?? 'doctor');
        (new Dental_Page_Monthly_Rota($type))->render_print($jy, $jm);
        exit;
    }

    // ─── پرینت گزارش درآمد و عملکرد — مدیر و دکتر هر دو دسترسی دارن
    // (همون نقشی که خودِ صفحه‌ی گزارش هم بهشون اجازه ورود می‌ده) ──────
    public function maybe_print_performance(): void {
        if (!isset($_GET['page']) || $_GET['page'] !== 'dental-dashboard' || !isset($_GET['print_perf'])) return;
        while (ob_get_level() > 0) { ob_end_clean(); } // رفع «باید رفرش بشه تا پرینت بیاد»
        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'dental_print_perf')) wp_die('لینک نامعتبر است.');
        if (!$this->user_has_role(['dental_admin','dental_doctor'])) wp_die('دسترسی مجاز نیست.');
        $from = sanitize_text_field($_GET['from'] ?? '');
        $to   = sanitize_text_field($_GET['to'] ?? '');
        $doctor = (int)($_GET['doctor'] ?? 0);
        (new Dental_Page_Performance_Report())->render_print($from, $to, $doctor);
        exit;
    }

    // ─── ثبت آخرین بازدید برای نمایش «آنلاین» ────────────────────
    public function touch_online(): void {
        if (is_user_logged_in() && class_exists('Dental_Attendance_Manager')) {
            Dental_Attendance_Manager::touch_last_seen(get_current_user_id());
        }
    }

    // ─── هر ورود موفق به سایت = اولین ورود پنل در روز، خودکار ثبت حضور ──
    public function on_login_auto_clock_in(string $user_login, \WP_User $user): void {
        if (!class_exists('Dental_Attendance_Manager')) return;
        $dental_roles = ['dental_admin','dental_doctor','dental_secretary','dental_financial','dental_assistant'];
        if (empty(array_intersect((array)$user->roles, $dental_roles))) return; // فقط کارکنان کلینیک
        Dental_Attendance_Manager::maybe_auto_clock_in($user->ID);
    }

    // ─── چک دوره‌ای خروج خودکار استنتاجی — سبک، فقط گاه‌به‌گاه اجرا می‌شه ──
    public function maybe_auto_clock_out(): void {
        if (!class_exists('Dental_Attendance_Manager')) return;
        // برای جلوگیری از اجرای این کوئری روی هر لود صفحه، فقط هر ۵ دقیقه یه‌بار
        if (get_transient('dental_auto_clockout_check')) return;
        set_transient('dental_auto_clockout_check', 1, 5 * MINUTE_IN_SECONDS);
        Dental_Attendance_Manager::maybe_auto_clock_out_inactive();
    }

    // ─── خودترمیمی ستون‌های جامانده — یک‌بار خودکار، بدون نیاز به
    // غیرفعال/فعال دستی پلاگین (چون dbDelta گاهی قابل‌اعتماد نیست) ──
    public function maybe_self_heal_columns(): void {
        // v8: ستون is_locked جدول dental_monthly_rota هم اضافه شد — بدون
        // بالا بردن این شماره، سایت‌هایی که v7 رو قبلاً اجرا کرده بودن
        // هیچ‌وقت ALTER TABLE جدید رو نمی‌گرفتن.
        if (get_option('dental_columns_healed_v8')) return;
        if (class_exists('Dental_Installer')) {
            Dental_Installer::fix_missing_columns();
        }
        update_option('dental_columns_healed_v8', 1);
    }

    // ─── خودترمیمی جدول‌های کاملاً جامانده (نه فقط ستون تک‌تک) —
    // مشکلی که باعث شد «درخواست کالا» با وجود پیام موفقیت، هیچ‌جا ثبت
    // نشه: جدول dental_inventory_requests هیچ‌وقت واقعاً ساخته نشده بود
    // چون بعد از اضافه‌شدنش به کد، پلاگین دوباره فعال‌سازی نشده بود.
    // dbDelta امن‌ه برای اجرای مکرر — فقط جدول/ستون جامانده رو می‌سازه.
    public function maybe_self_heal_tables(): void {
        if (get_option('dental_tables_healed_v3')) return;
        if (class_exists('Dental_Installer')) {
            Dental_Installer::create_tables();
        }
        update_option('dental_tables_healed_v3', 1);
    }

    // ─── خودترمیمی نقش‌ها — رفع مشکل مهم: capability «dental_manage_all»
    // که به هیچ نقشی داده نشده بود و باعث «شما اجازه دسترسی ندارید» روی
    // چندین صفحه (دفتر روزانه، رضایت‌نامه‌ها، کاتالوگ، شیفت‌بندی و...) می‌شد ──
    public function maybe_self_heal_roles(): void {
        // v2: کپبیلیتی‌های جمع مالی به نقش dental_financial اضافه شد —
        // بدون بالابردن این شماره، نصب‌هایی که v1 قبلاً اجرا شده بود
        // هیچ‌وقت این کپبیلیتی‌های جدید رو نمی‌گرفتن.
        if (get_option('dental_roles_healed_v2')) return;
        if (class_exists('Dental_Installer')) {
            Dental_Installer::create_roles();
        }
        update_option('dental_roles_healed_v2', 1);
    }

    public function ajax_clock_in(): void {
        check_ajax_referer('dental_attendance_ajax');
        Dental_Attendance_Manager::clock_in(get_current_user_id());
        wp_send_json_success();
    }
    public function ajax_clock_out(): void {
        check_ajax_referer('dental_attendance_ajax');
        Dental_Attendance_Manager::clock_out(get_current_user_id());
        wp_send_json_success();
    }
    public function ajax_start_break(): void {
        check_ajax_referer('dental_attendance_ajax');
        Dental_Attendance_Manager::start_break(get_current_user_id(), sanitize_key($_POST['break_type'] ?? 'break'));
        wp_send_json_success();
    }
    public function ajax_end_break(): void {
        check_ajax_referer('dental_attendance_ajax');
        Dental_Attendance_Manager::end_break(get_current_user_id());
        wp_send_json_success();
    }

    public function ajax_pos_test_connection(): void {
        check_ajax_referer('dental_pos_test');
        if (!current_user_can('manage_options')) wp_send_json_error(['message'=>'دسترسی مجاز نیست']);
        $device_id = sanitize_key($_POST['device_id'] ?? '');
        $result = (new Dental_POS_Gateway($device_id))->test_connection();
        $result['success'] ? wp_send_json_success($result) : wp_send_json_error($result);
    }

    public function ajax_pos_charge(): void {
        check_ajax_referer('dental_workspace');
        // nonce «dental_workspace» به هر ۵ نقش پرسنل داده می‌شه، پس به‌تنهایی
        // کافی نیست — بدون این چک، حتی دستیار هم می‌تونست کارتخوان رو شارژ کنه.
        if (!$this->user_has_role(['dental_admin','dental_financial','dental_secretary'])) {
            wp_send_json_error(['message'=>'دسترسی مجاز نیست']);
        }
        $amount     = (float)($_POST['amount'] ?? 0);
        $patient_id = (int)($_POST['patient_id'] ?? 0);
        $device_id  = sanitize_key($_POST['device_id'] ?? '');
        if ($amount <= 0) wp_send_json_error(['message'=>'مبلغ نامعتبر است']);
        $result = (new Dental_POS_Gateway($device_id))->send_payment_request($amount, $patient_id);
        $result['success'] ? wp_send_json_success($result) : wp_send_json_error($result);
    }

    public function ajax_save_catalog_price(): void {
        check_ajax_referer('dental_catalog_price');
        if (!$this->user_has_role(['dental_admin'])) wp_send_json_error();
        $catalog_id = (int)($_POST['catalog_id'] ?? 0);
        $price      = (int)($_POST['price'] ?? 0);
        $patient_share = isset($_POST['patient_share']) && $_POST['patient_share'] !== '' ? (int)$_POST['patient_share'] : null;
        if (!$catalog_id) wp_send_json_error();
        Dental_Service_Catalog::set_price($catalog_id, $price, $patient_share);
        delete_transient('dental_catalog_full_tree_cache');
        wp_send_json_success();
    }

    public function ajax_save_catalog_consent(): void {
        check_ajax_referer('dental_catalog_price');
        if (!$this->user_has_role(['dental_admin'])) wp_send_json_error();
        $catalog_id = (int)($_POST['catalog_id'] ?? 0);
        $requires   = !empty($_POST['requires']);
        if (!$catalog_id) wp_send_json_error();
        Dental_Service_Catalog::set_requires_consent($catalog_id, $requires);
        wp_send_json_success();
    }

    public function ajax_catalog_search_pricing(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_doctor','dental_financial','dental_secretary'])) wp_send_json_error();
        $q = sanitize_text_field($_POST['q'] ?? '');
        if (mb_strlen($q) < 2) wp_send_json_success(['items'=>[]]);
        $items = Dental_Service_Catalog::search_leaves($q, 100);
        foreach ($items as &$it) {
            $it['id'] = (int)$it['id'];
            $it['price'] = (int)($it['price'] ?? 0);
            $it['patient_share'] = isset($it['patient_share']) ? (int)$it['patient_share'] : '';
            $it['requires_consent'] = (int)($it['requires_consent'] ?? 0);
        }
        unset($it);
        wp_send_json_success(['items' => $items]);
    }

    public function ajax_save_catalog_filter(): void {
        check_ajax_referer('dental_catalog_price');
        if (!$this->user_has_role(['dental_admin'])) wp_send_json_error();
        $catalog_id = (int)($_POST['catalog_id'] ?? 0);
        $filter     = sanitize_key($_POST['tooth_filter'] ?? 'all');
        if (!$catalog_id) wp_send_json_error();
        global $wpdb;
        $wpdb->update($wpdb->prefix.'dental_service_catalog', ['tooth_filter'=>$filter], ['id'=>$catalog_id]);
        wp_send_json_success();
    }

    // ─── مرور یک لایه از درخت کاتالوگ (برای مودال انتخاب درمان) ────
    // ─── کل درخت یکجا — با کش transient ۱۲ساعته چون کاتالوگ به‌ندرت
    // تغییر می‌کنه؛ این‌جوری حتی همون یه درخواست اولیه هم فوق‌سریعه ──
    public function ajax_catalog_full_tree(): void {
        check_ajax_referer('dental_workspace');
        $cached = get_transient('dental_catalog_full_tree_cache');
        if ($cached !== false) { wp_send_json_success(['tree' => $cached]); return; }
        $tree = class_exists('Dental_Service_Catalog') ? Dental_Service_Catalog::get_full_tree() : [];
        set_transient('dental_catalog_full_tree_cache', $tree, 12 * HOUR_IN_SECONDS);
        wp_send_json_success(['tree' => $tree]);
    }

    public function ajax_catalog_browse(): void {
        check_ajax_referer('dental_workspace');
        $parent_id = isset($_GET['parent_id']) && $_GET['parent_id'] !== '' ? (int)$_GET['parent_id'] : null;
        $children  = Dental_Service_Catalog::get_children($parent_id);
        $path      = $parent_id ? Dental_Service_Catalog::get_path($parent_id) : [];

        // ─── رفع باگ مهم: $wpdb همه‌چیز رو به‌صورت رشته برمی‌گردونه —
        // رشته "0" توی جاوااسکریپت truthy حساب می‌شه (فقط "" فالسیه)!
        // پس is_leaf رو صریح به int/bool واقعی تبدیل می‌کنیم تا سمت
        // جاوااسکریپت درست تشخیص بده برگه یا هنوز زیرشاخه داره.
        foreach ($children as &$c) {
            $c['id']       = (int)$c['id'];
            $c['is_leaf']  = (int)$c['is_leaf'];
            $c['price']    = isset($c['price']) ? (int)$c['price'] : 0;
        }
        unset($c);

        wp_send_json_success(['children' => $children, 'path' => $path]);
    }

    // ─── ثبت درمان انتخاب‌شده از کاتالوگ برای دندان مشخص ───────────
    public function ajax_catalog_record_treatment(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_doctor','dental_secretary'])) wp_send_json_error(['message'=>'دسترسی مجاز نیست.']);
        $patient_id  = (int)($_POST['patient_id'] ?? 0);
        $catalog_id  = (int)($_POST['catalog_id'] ?? 0);
        $tooth       = isset($_POST['tooth_number']) && $_POST['tooth_number'] !== '' ? (int)$_POST['tooth_number'] : null;
        if (!$patient_id || !$catalog_id) wp_send_json_error(['message'=>'اطلاعات ناقص است']);

        $result = Dental_Service_Catalog::record_treatment($patient_id, $tooth, $catalog_id, get_current_user_id());
        if (!$result['id']) wp_send_json_error(['message'=>'ثبت انجام نشد']);
        wp_send_json_success(['id'=>$result['id'], 'requires_consent'=>$result['requires_consent']]);
    }

    // ─── حذف یک درمان ثبت‌شده از کاتالوگ (فقط اگه مالی تأییدش نکرده) ──
    public function ajax_catalog_delete_treatment(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_doctor','dental_secretary'])) wp_send_json_error(['message'=>'دسترسی مجاز نیست.']);
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) wp_send_json_error();
        $ok = Dental_Service_Catalog::delete_treatment($id);
        if (!$ok) wp_send_json_error(['message'=>'این رکورد قبلاً توسط مالی تأیید شده و قابل حذف نیست. برای اصلاح، با واحد مالی هماهنگ کنید.']);
        wp_send_json_success();
    }

    // ─── خلاصه پرونده — چارت + تاریخچه کامل با هاور روی هر دندون ───
    public function ajax_get_patient_summary(): void {
        check_ajax_referer('dental_workspace');
        nocache_headers();
        $patient_id = (int)($_GET['patient_id'] ?? 0);
        if (!$patient_id) wp_send_json_error();
        ob_start();
        $this->render_patient_summary($patient_id);
        $html = ob_get_clean();
        wp_send_json_success(['html' => $html]);
    }

    private function render_patient_summary(int $patient_id): void {
        $history = Dental_Service_Catalog::get_patient_full_history($patient_id);
        $teeth_with_history = array_keys($history['teeth']);

        // ─── چارت ساده و فقط-نمایشی — همون شماره‌گذاری چارت اصلی (۱ کنار خط وسط، ۸ بیرون) ──
        $q_names = [1=>'بالا راست',2=>'بالا چپ',3=>'پایین چپ',4=>'پایین راست'];
        ?>
        <div style="padding:16px;">
            <div style="font-size:14px;font-weight:700;margin-bottom:12px;">📋 خلاصه پرونده — تاریخچه کامل درمان</div>

            <!-- چارت فشرده و فقط‌نمایشی -->
            <div style="background:var(--dc-neutral-50);border-radius:10px;padding:16px;margin-bottom:16px;">
                <?php
                $rows_def = [
                    ['q_right'=>2,'q_left'=>1,'dir_right'=>'desc'], // ردیف بالا: کوادرانت۲ راست(نزولی۸بیرون۱وسط)، کوادرانت۱ چپ(صعودی)
                    ['q_right'=>3,'q_left'=>4,'dir_right'=>'desc'],
                ];
                foreach ($rows_def as $ri => $rd):
                    $right_nums = $rd['dir_right']==='desc' ? range(8,1) : range(1,8);
                    $left_nums  = $rd['dir_right']==='desc' ? range(1,8) : range(8,1);
                ?>
                <div style="display:flex;gap:4px;justify-content:center;margin-bottom:<?php echo $ri===0?'10px':'0'; ?>;">
                    <?php foreach($right_nums as $n):
                        $tn = $rd['q_right']*10 + $n;
                        $has = in_array($tn, $teeth_with_history);
                    ?>
                    <div class="dc-summary-tooth" data-tooth="<?php echo $tn; ?>"
                        style="width:30px;height:30px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;cursor:<?php echo $has?'pointer':'default'; ?>;
                        background:<?php echo $has?'var(--dc-primary)':'#fff'; ?>;color:<?php echo $has?'#fff':'var(--dc-neutral-400)'; ?>;border:1px solid var(--dc-neutral-200);">
                        <?php echo $n; ?>
                    </div>
                    <?php endforeach; ?>
                    <div style="width:1px;background:var(--dc-neutral-300);margin:0 4px;"></div>
                    <?php foreach($left_nums as $n):
                        $tn = $rd['q_left']*10 + $n;
                        $has = in_array($tn, $teeth_with_history);
                    ?>
                    <div class="dc-summary-tooth" data-tooth="<?php echo $tn; ?>"
                        style="width:30px;height:30px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;cursor:<?php echo $has?'pointer':'default'; ?>;
                        background:<?php echo $has?'var(--dc-primary)':'#fff'; ?>;color:<?php echo $has?'#fff':'var(--dc-neutral-400)'; ?>;border:1px solid var(--dc-neutral-200);">
                        <?php echo $n; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
                <div style="text-align:center;font-size:10px;color:var(--dc-neutral-400);margin-top:8px;">دندان‌های آبی‌رنگ سابقه درمان دارند — روی شماره‌شان بروید</div>
            </div>

            <!-- تاریخچه متنی کامل -->
            <div id="dc-summary-list">
                <?php if (!empty($history['whole_mouth'])): ?>
                <div style="margin-bottom:14px;">
                    <div style="font-size:12px;font-weight:700;color:var(--dc-primary);margin-bottom:6px;">🦷 کل دهان:</div>
                    <?php foreach($history['whole_mouth'] as $e): ?>
                    <div style="font-size:12px;color:var(--dc-neutral-700);padding:4px 0;border-bottom:1px solid var(--dc-neutral-50);">
                        <?php echo esc_html($e['text']); ?> — <?php echo esc_html(Dental_Jalali::to_jalali(date('Y-m-d',strtotime($e['date'])),'Y/m/d')); ?> — ارائه‌دهنده: <?php echo esc_html($e['doctor']); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (empty($history['teeth']) && empty($history['whole_mouth'])): ?>
                <p style="text-align:center;color:var(--dc-neutral-400);padding:20px;">هنوز تاریخچه‌ای ثبت نشده</p>
                <?php else: foreach($history['teeth'] as $tn => $entries):
                    $q = (int)($tn/10); $n = $tn%10;
                ?>
                <div class="dc-summary-tooth-section" data-tooth="<?php echo $tn; ?>" style="margin-bottom:14px;">
                    <div style="font-size:12px;font-weight:700;color:var(--dc-primary);margin-bottom:6px;">🦷 دندان <?php echo $n; ?> فک <?php echo esc_html($q_names[$q]??''); ?>:</div>
                    <?php foreach($entries as $e): ?>
                    <div style="font-size:12px;color:var(--dc-neutral-700);padding:4px 0;border-bottom:1px solid var(--dc-neutral-50);">
                        <?php echo esc_html($e['text']); ?> — <?php echo esc_html(Dental_Jalali::to_jalali(date('Y-m-d',strtotime($e['date'])),'Y/m/d')); ?> — ارائه‌دهنده: <?php echo esc_html($e['doctor']); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <script>
        (function(){
            // داده تاریخچه هر دندان برای هاور — تولید در همین لحظه رندر
            var dcSummaryData = <?php
                $js_data = [];
                foreach ($history['teeth'] as $tn => $entries) {
                    $lines = array_map(function($e) {
                        return esc_js($e['text']) . ' — ' . esc_js(Dental_Jalali::to_jalali(date('Y-m-d',strtotime($e['date'])),'Y/m/d')) . ' — ' . esc_js($e['doctor']);
                    }, $entries);
                    $js_data[$tn] = $lines;
                }
                echo wp_json_encode($js_data);
            ?>;
            document.querySelectorAll('.dc-summary-tooth').forEach(function(el){
                var tn = el.dataset.tooth;
                if(!dcSummaryData[tn]) return;
                el.addEventListener('mouseenter', function(e){
                    dcShowToothTooltip(el, dcSummaryData[tn]);
                });
                el.addEventListener('mouseleave', function(){
                    dcHideToothTooltip();
                });
                el.addEventListener('click', function(){
                    var section = document.querySelector('.dc-summary-tooth-section[data-tooth="'+tn+'"]');
                    if(section) section.scrollIntoView({behavior:'smooth', block:'center'});
                });
            });
        })();
        </script>

        <?php if (class_exists('Dental_Prescription_Manager')):
            $rx_list = Dental_Prescription_Manager::get_patient_prescriptions($patient_id);
        ?>
        <div style="margin-top:20px;">
            <div style="font-size:13px;font-weight:700;margin-bottom:8px;">💊 نسخه‌های ثبت‌شده</div>
            <?php if (empty($rx_list)): ?>
            <p style="font-size:12px;color:var(--dc-neutral-400);">نسخه‌ای ثبت نشده</p>
            <?php else: foreach($rx_list as $rx): ?>
            <div style="border:1px solid var(--dc-neutral-100);border-radius:8px;padding:10px 12px;margin-bottom:8px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <b style="font-size:12px;"><?php echo esc_html(Dental_Jalali::to_jalali($rx['prescribed_date'],'Y/m/d')); ?> — <?php echo esc_html($rx['doctor_name']); ?></b>
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=dental-dashboard&print_prescription=1&rx_id='.$rx['id']),'dental_print_rx')); ?>" target="_blank" style="font-size:11px;">🖨️ پرینت</a>
                </div>
                <div style="font-size:11px;color:var(--dc-neutral-500);margin-top:4px;">
                    <?php echo esc_html(implode('، ', array_map(fn($i)=>$i['drug_name_snapshot'], $rx['items']))); ?>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <?php endif; ?>
        <?php
    }

    public function ajax_catalog_toggle_treatment(): void {
        check_ajax_referer('dental_workspace');
        $id   = (int)($_POST['id'] ?? 0);
        $done = !empty($_POST['done']);
        if (!$id) wp_send_json_error();
        Dental_Service_Catalog::toggle_treatment_done($id, $done, get_current_user_id());
        wp_send_json_success();
    }

    // ─── ریدایرکت به ویزارد نصب — فقط یک‌بار، بعد از فعال‌سازی اول ──
    public function maybe_redirect_to_wizard(): void {
        if (!get_transient('dental_activation_redirect')) return;
        delete_transient('dental_activation_redirect');
        if (wp_doing_ajax() || isset($_GET['activate-multi'])) return;
        wp_safe_redirect(admin_url('admin.php?page=dental-dashboard&wizard_view=1'));
        exit;
    }

    public function register_menus(): void {
        // ─── راه‌حل ریشه‌ای و همیشگی: دیگه هیچ منویی به یه capability
        // رشته‌ای که ممکنه به نقشی داده نشده باشه تکیه نمی‌کنه (همون چیزی
        // که کلی مشکل درست کرد: dental_manage_all, manage_dental و...).
        // همه منوها با همین یه capability (که مطمئنیم به هر ۵ نقش کارمند
        // داده شده) ثبت می‌شن — کنترل واقعیِ «کی واقعاً حق دیدن محتوا رو
        // داره» داخل خودِ هر صفحه با چک مستقیم نقش انجام می‌شه، نه اینجا.
        $staff_cap = 'dental_manage_reception';
        $cap = $staff_cap; // دیگه فرقی با staff_cap نداره — عمداً

        add_menu_page(
            'کلینیک دندانپزشکی', '🦷 کلینیک', $staff_cap, 'dental-dashboard',
            [$this, 'render_dashboard'],
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M12 2C8.5 2 6 4.5 6 7c0 1.5.6 2.8 1.5 3.8L6 20h2l1-5h6l1 5h2l-1.5-9.2C17.4 9.8 18 8.5 18 7c0-2.5-2.5-5-6-5z"/></svg>'),
            30
        );

        $submenus = [
            ['dental-dashboard', 'داشبورد',       $staff_cap, [$this, 'render_dashboard']],
        ];
        if (Dental_Features::enabled('reception')) {
            $submenus[] = ['dental-reception', 'پذیرش امروز', $staff_cap, function(){ (new Dental_Page_Reception())->render(); }];
            $submenus[] = ['dental-doctor-desk', 'پرونده روزانه (پزشک)', $staff_cap, function(){ (new Dental_Page_Doctor_Desk())->render(); }];
        }
        $submenus[] = ['dental-patients',  'بیماران',        $staff_cap, [$this, 'render_patients']];
        // نکته: «رضایت‌نامه‌ها» دیگه منوی جدا نداره — به‌عنوان یکی از
        // تب‌های «گزارش‌گیری» ادغام شد (پایین‌تر همین متد).
        // نکته: لاگ پیامک‌ها دیگه منوی جدا نداره — از تب «تنظیمات ← لاگ پیامک» قابل‌دسترسه
        $submenus[] = ['dental-attendance','حضور و مرخصی',  $staff_cap, function(){ (new Dental_Page_Attendance())->render(); }];
        // نکته: «کارکرد پزشکان» دیگه منوی جدا نداره — تب زیر «گزارش‌گیری».
        // نکته: منوی «لاگ فعالیت» رو جدا، با الگوی امن، پایین‌تر ثبت می‌کنیم
        // (لینک مستقیم، نه callback با ریدایرکت — که باعث صفحه سفید می‌شد)
        $submenus[] = ['dental-recall','یادآوری چکاپ (ریکال)', $this->menu_cap(['dental_admin','dental_secretary']), function(){
            (new Dental_Page_Recall())->render();
        }];
        $submenus[] = ['dental-catalog-pricing','کاتالوگ و قیمت‌گذاری', $this->menu_cap(['dental_admin']), function(){
            (new Dental_Page_Catalog_Pricing())->render();
        }];
        // نکته: «دیتابیس داروها» و «قالب‌های مسیر درمان» دیگه منوی جدا
        // ندارن — تب‌های جدید زیر «تنظیمات».
        $submenus[] = ['dental-insurance','بیمه', $this->menu_cap(['dental_admin','dental_financial','dental_secretary']), [$this, 'render_insurance_hub']];
        // نکته: «تسویه با بیمه» دیگه منوی جدا نداره — تب زیر «بیمه».
        // ─── ماژول شیفت‌بندی — همه زیرصفحه‌هاش توی یه شاخه جمع شدن ────
        // فقط «شیفت‌بندی» توی منوی کناری دیده می‌شه؛ بقیه از همون صفحه
        // هاب قابل‌دسترسی‌ان (منوی کناری دیگه دراز نمی‌شه).
        // ─── ماژول شیفت‌بندی — لینک مستقیم استاندارد وردپرسی به صفحه
        // داشبورد با پارامتر، بدون callback/ریدایرکت دستی (که باعث
        // «صفحه سفید» می‌شد چون هدرها قبلش فرستاده شده بودن) ─────────
        // نکته: شیفت‌بندی و انبار عمداً اینجا ثبت نمی‌شن — باید بعد از
        // داشبورد (توی حلقه پایین) ثبت بشن وگرنه بالاتر از داشبورد میان.
        // ─── مالی — قبلاً ۳ زیرمنوی پشت‌سرهم با پیشوند «💰 مالی:» بود؛
        // الان یه آیتم واحده و «مالی و اقساط»/«دفتر روزانه» تب داخلی‌ان.
        $submenus[] = ['dental-financial', 'مالی', $staff_cap, [$this, 'render_financial']];

        // ─── گزارش‌گیری — آمار، گزارش درآمد، کارکرد پزشکان، و
        // رضایت‌نامه‌ها همه یه‌جا، به‌صورت تب (هرکدوم دسترسی خودش رو
        // داخل render_reports_hub() نگه می‌داره، دقیقاً مثل قبل). ────
        $submenus[] = ['dental-reports', 'گزارش‌گیری', $this->menu_cap(['dental_admin','dental_doctor','dental_secretary']), [$this, 'render_reports_hub']];

        $submenus[] = ['dental-settings',  'تنظیمات',        $this->menu_cap(['dental_admin']), [$this, 'render_settings']];

        // صفحه ویزارد نصب — به‌جای صفحه/زیرمنوی جدا (که مشکل مرموز
        // دسترسی داشت)، حالا هم از الگوی امن dental-dashboard استفاده
        // می‌کنه، دقیقاً مثل فاکتور/شیفت‌بندی/انبار.
        add_submenu_page('dental-dashboard', 'راه‌اندازی اولیه', 'راه‌اندازی اولیه', $staff_cap,
            'admin.php?page=dental-dashboard&wizard_view=1');
        remove_submenu_page('dental-dashboard', 'admin.php?page=dental-dashboard&wizard_view=1');

        foreach ($submenus as $m) {
            add_submenu_page('dental-dashboard', $m[1], $m[1], $m[2], $m[0], $m[3]);
        }

        // شیفت‌بندی و انبار — عمداً بعد از داشبورد ثبت می‌شن تا توی
        // سایدبار زیرِ داشبورد بیان، نه بالاش.
        add_submenu_page('dental-dashboard', 'شیفت‌بندی', 'شیفت‌بندی', $this->menu_cap(['dental_admin']), 'admin.php?page=dental-dashboard&shift_view=hub');
        add_submenu_page('dental-dashboard', 'انبار', 'انبار', $this->menu_cap(['dental_admin','dental_financial']), 'admin.php?page=dental-dashboard&inv_view=hub');
        add_submenu_page('dental-dashboard', 'لاگ فعالیت', 'لاگ فعالیت', $this->menu_cap(['dental_admin']), 'admin.php?page=dental-dashboard&audit_view=1');
        // نکته: دکمه‌ی جداگانه‌ی «مالی» حذف شد — چون الان ۳ زیرمنوی
        // مالی خودشون پشت‌سرهم و با پیشوند مشترک، گروه‌شده دیده می‌شن.
        // نکته: فاکتور دیگه یه صفحه/زیرمنوی جدا نیست — از همون صفحه
        // داشبورد (که ۱۰۰٪ برای همه نقش‌ها کار می‌کنه) با پارامتر
        // print_invoice=1 استفاده می‌کنه، دقیقاً مثل الگوی پرینت پذیرش.
    }

    public function enqueue_assets(string $hook): void {
        if (strpos($hook, 'dental') === false) return;

        $page = sanitize_key($_GET['page'] ?? '');
        $tab  = sanitize_key($_GET['tab']  ?? '');

        // ─── فونت Vazirmatn ───────────────────────────────────────
        wp_enqueue_style('vazirmatn',
            'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap',
            [], null);

        // ─── CSS اصلی پلاگین ─────────────────────────────────────
        wp_enqueue_style('dental-admin-rtl',
            DENTAL_CORE_URL . 'assets/css/admin-rtl.css',
            ['vazirmatn'], DENTAL_CORE_VERSION);

        // ─── Lucide Icons (آیکون‌های حرفه‌ای) ──────────────────────
        wp_enqueue_script('lucide',
            'https://cdn.jsdelivr.net/npm/lucide@0.408.0/dist/umd/lucide.min.js',
            [], '0.408.0', true);

        // ─── SweetAlert2 (Toast و Confirm) ───────────────────────
        wp_enqueue_style('sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
            [], '11');
        wp_enqueue_script('sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
            [], '11', true);

        // ─── Chart.js — فقط صفحه مالی و داشبورد ─────────────────
        if ($page === 'dental-financial' || $page === 'dental-dashboard') {
            wp_enqueue_script('chartjs',
                'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
                [], '4.4.0', true);
        }

        // ─── Persian Datepicker — روی همه صفحات dental ───────────
                // ─── Dental Jalali Datepicker ────────────────────────────
        wp_enqueue_script('dental-datepicker',
            DENTAL_CORE_URL . 'assets/js/dental-datepicker.js',
            ['jquery'], DENTAL_CORE_VERSION, true);

        // ─── چارت دندانی — فقط تب chart ─────────────────────────
        if ($tab === 'chart') {
            // رفع مشکل کش مرورگر: قبلاً این فایل با DENTAL_CORE_VERSION
            // (که یه شماره‌ی ثابته و هر آپدیت کد عوض نمی‌شد) لود می‌شد —
            // یعنی حتی وقتی خودِ فایل روی سرور عوض می‌شد، چون آدرسش
            // (?ver=x) دقیقاً همون می‌موند، مرورگر نسخه‌ی قدیمیِ کش‌شده
            // رو نشون می‌داد. الان از زمان واقعی تغییر فایل استفاده
            // می‌کنیم — هر ذخیره‌ی جدید، خودکار کش رو باطل می‌کنه.
            $chart_js_path = DENTAL_CORE_PATH . 'assets/js/admin/dental-chart.js';
            $chart_js_ver  = file_exists($chart_js_path) ? filemtime($chart_js_path) : DENTAL_CORE_VERSION;
            wp_enqueue_script('dental-chart',
                DENTAL_CORE_URL . 'assets/js/admin/dental-chart.js',
                [], $chart_js_ver, true);
        }

        // ─── Config global ────────────────────────────────────────
        wp_add_inline_script('jquery', sprintf(
            'window.dentalConfig=%s;',
            wp_json_encode([
                'apiBase'  => rest_url('dental/v1'),
                'nonce'    => wp_create_nonce('wp_rest'),
                'devMode'  => Dental_Dev_Logger::is_dev_mode(),
                'ajaxUrl'  => admin_url('admin-ajax.php'),
                'currency' => get_option('dental_currency', 'تومان'),
                'version'  => DENTAL_CORE_VERSION,
            ])
        ));

        // ─── Init SweetAlert2 Toast ──────────────────────────────
        wp_add_inline_script('sweetalert2', '
window.DentalToast = {
    success: function(msg) {
        Swal.fire({toast:true,position:"top-end",icon:"success",title:msg,showConfirmButton:false,timer:3000,timerProgressBar:true,didOpen:function(t){t.addEventListener("mouseenter",Swal.stopTimer);t.addEventListener("mouseleave",Swal.resumeTimer);}});
    },
    error: function(msg) {
        Swal.fire({toast:true,position:"top-end",icon:"error",title:msg,showConfirmButton:false,timer:4000});
    },
    confirm: function(msg, cb) {
        Swal.fire({title:msg,icon:"warning",showCancelButton:true,confirmButtonText:"بله",cancelButtonText:"انصراف",confirmButtonColor:"#E05252",reverseButtons:true}).then(function(r){if(r.isConfirmed)cb();});
    }
};
        ');

        // ─── Init Lucide ─────────────────────────────────────────
        wp_add_inline_script('lucide', 'document.addEventListener("DOMContentLoaded",function(){if(typeof lucide!=="undefined")lucide.createIcons();});');
    }

    public function add_admin_bar_item(WP_Admin_Bar $bar): void {
        if (!current_user_can('manage_options')) return;
        if (Dental_Dev_Logger::is_dev_mode()) {
            $bar->add_node([
                'id'    => 'dental-dev-mode',
                'title' => '🧪 Dental Dev',
                'href'  => admin_url('admin.php?page=dental-settings&tab=dev'),
            ]);
        }
    }

    public function render_dev_logs(): void {
        if (class_exists('Dental_Dev_Logger')) Dental_Dev_Logger::render_console_logs();
    }

    public function maybe_show_dev_banner(): void {
        if (strpos($_SERVER['REQUEST_URI'] ?? '', 'dental') === false) return;
        if (class_exists('Dental_Dev_Logger')) Dental_Dev_Logger::render_dev_mode_banner();
    }

    // ─── Pages ───────────────────────────────────────────────────
    public function render_dashboard(): void {
        // ─── ماژول شیفت‌بندی — داخل همون رندر عادی این صفحه انجام می‌شه
        // (نه با میان‌بر زودهنگام admin_init) تا استایل و اسکریپت‌های
        // پیشخوان به‌درستی لود بشن؛ میان‌بر زودتر باعث «صفحه سفید بدون
        // استایل» می‌شد چون فرصت لود CSS/JS پیشخوان رو نمی‌داد.
        if (isset($_GET['wizard_view'])) {
            (new Dental_Setup_Wizard())->render();
            return;
        }

        if (isset($_GET['shift_view'])) {
            if (!$this->user_has_role(['dental_admin'])) {
                wp_die('این بخش فقط برای مدیر کلینیک قابل‌دسترسه.');
            }

            $view = sanitize_key($_GET['shift_view']);
            switch ($view) {
                case 'hub':                 (new Dental_Page_Shift_Hub())->render(); return;
                case 'rooms':                (new Dental_Page_Shift_Rooms())->render(); return;
                case 'monthly-doctors':      (new Dental_Page_Monthly_Rota('doctor'))->render(); return;
                case 'monthly-assistants':   (new Dental_Page_Monthly_Rota('assistant'))->render(); return;
                case 'weekly':               (new Dental_Page_Shift_Weekly())->render(); return;
            }
        }

        // ─── درخواست/درخواست کالا — همه نقش‌ها دسترسی دارن (نه فقط مدیر) ──
        if (isset($_GET['audit_view'])) {
            if (!$this->user_has_role(['dental_admin'])) {
                wp_die('این بخش فقط برای مدیر کلینیک قابل‌دسترسه.');
            }
            (new Dental_Page_Audit_Log())->render();
            return;
        }

        if (isset($_GET['inv_view']) && $_GET['inv_view'] === 'request') {
            (new Dental_Page_Inventory_Request())->render();
            return;
        }

        // ─── ماژول انبار — فقط مدیر کلینیک و مسئول مالی مدیریتش می‌کنن ──
        if (isset($_GET['inv_view'])) {
            if (!$this->user_has_role(['dental_admin','dental_financial'])) {
                wp_die('این بخش فقط برای مدیر کلینیک و مسئول مالی قابل‌دسترسه.');
            }
            $view = sanitize_key($_GET['inv_view']);
            switch ($view) {
                case 'hub':   (new Dental_Page_Inventory_Hub())->render(); return;
                case 'items': (new Dental_Page_Inventory_Items())->render(); return;
                case 'log':   (new Dental_Page_Inventory_Log())->render(); return;
            }
        }

        // ─── پیام‌های داخلی — همه کارکنان دسترسی دارن (نه فقط مدیر) ────
        if (isset($_GET['msg_view'])) {
            (new Dental_Page_Messages())->render();
            return;
        }

        $patient_count = wp_count_posts('dental_patient')->publish ?? 0;
        global $wpdb;
        $overdue = (int)$wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dental_installment_items WHERE status='overdue'"
        );

        // ─── تشخیص نقش برای تعیین محتوای کارت‌های بالای داشبورد ──
        $cu = wp_get_current_user();
        $is_admin_role     = current_user_can('manage_options') || in_array('dental_admin', (array)$cu->roles);
        $is_financial_role = in_array('dental_financial', (array)$cu->roles);
        $is_doctor_role    = in_array('dental_doctor', (array)$cu->roles) && !$is_admin_role;
        $is_secretary_role = (in_array('dental_secretary', (array)$cu->roles) || in_array('dental_assistant', (array)$cu->roles)) && !$is_admin_role;
        ?>
        <div class="dental-admin-wrap">
            <?php if (class_exists('Dental_Changelog') && Dental_Changelog::user_needs_to_see($cu->ID)):
                $cl = Dental_Changelog::get_log();
                $latest = Dental_Changelog::latest_version();
                $entry = $cl[$latest];
            ?>
            <div id="dc-changelog-modal" style="position:fixed;inset:0;background:rgba(15,42,56,.8);z-index:999999;display:flex;align-items:center;justify-content:center;">
                <div style="background:#fff;border-radius:18px;max-width:440px;width:100%;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.3);">
                    <div style="background:linear-gradient(135deg,#1A6B8A,#0F4D66);padding:24px;color:#fff;text-align:center;">
                        <div style="font-size:28px;margin-bottom:6px;">🎉</div>
                        <div style="font-size:15px;font-weight:800;">چی جدید اومده؟</div>
                        <div style="font-size:11px;opacity:.8;margin-top:3px;">نسخه <?php echo esc_html($latest); ?> — <?php echo esc_html($entry['date']); ?></div>
                    </div>
                    <div style="padding:22px 24px;">
                        <div style="font-weight:700;font-size:13px;margin-bottom:12px;color:var(--dc-primary);"><?php echo esc_html($entry['title']); ?></div>
                        <ul style="margin:0;padding-right:18px;display:flex;flex-direction:column;gap:8px;">
                            <?php foreach($entry['items'] as $item): ?>
                            <li style="font-size:13px;color:var(--dc-neutral-700);line-height:1.6;"><?php echo esc_html($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button onclick="dcDismissChangelog()" class="dc-btn dc-btn-primary" style="width:100%;margin-top:20px;">متوجه شدم ✓</button>
                    </div>
                </div>
            </div>
            <script>
            function dcDismissChangelog(){
                var fd = new FormData();
                fd.append('action','dental_dismiss_changelog');
                fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce("dental_workspace")); ?>');
                fetch('<?php echo esc_js(admin_url("admin-ajax.php")); ?>', {method:'POST', body:fd});
                document.getElementById('dc-changelog-modal').remove();
            }
            </script>
            <?php endif; ?>
            <?php
            $att_status_hdr = class_exists('Dental_Attendance_Manager') ? Dental_Attendance_Manager::get_today_status($cu->ID) : ['clocked_in'=>true];
            $open_brk_hdr   = class_exists('Dental_Attendance_Manager') ? Dental_Attendance_Manager::get_open_break($cu->ID) : null;
            ?>
            <div class="dc-dashboard-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div>
                    <div class="dc-dashboard-greeting">
                        سلام <?php echo esc_html($cu->display_name); ?> 👋
                        <?php
                        $my_dental_roles = array_values(array_intersect((array)$cu->roles, ['dental_admin','dental_doctor','dental_secretary','dental_financial','dental_assistant']));
                        if (count($my_dental_roles) > 1):
                            $role_labels = ['dental_admin'=>'مدیر کلینیک','dental_doctor'=>'دندانپزشک','dental_secretary'=>'منشی / پذیرش','dental_financial'=>'مسئول مالی','dental_assistant'=>'دستیار'];
                            $active_role = get_user_meta($cu->ID, '_dental_active_role', true) ?: $my_dental_roles[0];
                        ?>
                        <select id="dc-role-switcher" onchange="dcSwitchRole(this.value)" style="font-size:11px;border:1px solid var(--dc-neutral-200);border-radius:8px;padding:3px 8px;font-family:inherit;background:#fff;color:var(--dc-primary);cursor:pointer;">
                            <?php foreach($my_dental_roles as $r): ?>
                            <option value="<?php echo esc_attr($r); ?>" <?php selected($active_role,$r); ?>><?php echo esc_html($role_labels[$r]); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                    </div>
                    <div class="dc-dashboard-date">
                        <?php echo esc_html(Dental_Jalali::today('l، d F Y')); ?>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <?php if (!$att_status_hdr['clocked_in']): ?>
                    <div style="text-align:left;">
                        <div style="font-size:11px;color:#fff;font-weight:700;margin-bottom:4px;animation:dcBlinkText 1.4s infinite;">
                            ⚠️ حتماً ورود خود را ثبت نمایید
                        </div>
                        <button type="button" onclick="dcClockQuick('clock_in')" id="dc-hdr-clock-btn"
                            style="background:#fff;color:var(--dc-primary);border:none;border-radius:24px;padding:10px 22px;font-weight:700;font-size:13px;cursor:pointer;box-shadow:0 3px 10px rgba(0,0,0,.15);animation:dcBlinkBtn 1.4s infinite;">
                            🟢 ثبت ورود
                        </button>
                    </div>
                    <?php else: ?>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <?php if($open_brk_hdr):
                            $break_label_hdr = $open_brk_hdr['break_type']==='lunch' ? 'ناهار' : 'استراحت';
                        ?>
                        <span style="font-size:11px;background:rgba(255,255,255,.2);color:#fff;border-radius:14px;padding:6px 12px;">
                            <?php echo $break_label_hdr; ?> — از «وضعیت من و همکاران» پایین بازگردید
                        </span>
                        <?php endif; ?>
                        <button type="button" onclick="dcClockOutAndLogout()"
                            style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.4);border-radius:20px;padding:8px 16px;font-size:12px;cursor:pointer;">🔴 ثبت خروج</button>
                    </div>
                    <?php endif; ?>
                    <a href="<?php echo esc_url(wp_logout_url(home_url('/login-page/'))); ?>" title="خروج از پنل"
                        style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.4);border-radius:20px;padding:8px 16px;display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:12px;font-weight:700;gap:4px;">
                        خروج از پنل
                    </a>
                </div>
            </div>
            <style>
            @keyframes dcBlinkBtn { 0%,100%{opacity:1;transform:scale(1);} 50%{opacity:.75;transform:scale(1.04);} }
            @keyframes dcBlinkText { 0%,100%{opacity:1;} 50%{opacity:.4;} }
            @keyframes dcToastIn { from{opacity:0;transform:translateY(-10px);} to{opacity:1;transform:translateY(0);} }
            </style>
            <script>
            function dcSwitchRole(role){
                var fd = new FormData();
                fd.append('action','dental_switch_role');
                fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce("dental_workspace")); ?>');
                fd.append('role', role);
                fetch('<?php echo esc_js(admin_url("admin-ajax.php")); ?>', {method:'POST', body:fd})
                    .then(r=>r.json()).then(function(res){
                        if(res.success && res.data.redirect) window.location.href = res.data.redirect;
                    });
            }
            // وقتی «ثبت خروج» می‌زنه، هم حضورش ثبت می‌شه هم از پنل خارج می‌شه
            function dcClockOutAndLogout(){
                dcClockQuick('clock_out', function(){
                    window.location.href = '<?php echo esc_js(wp_logout_url(home_url('/login-page/'))); ?>';
                });
            }
            </script>

            <?php if ($this->user_has_role(['dental_admin','dental_financial']) && class_exists('Dental_Inventory_Manager')):
                $inv_alerts = Dental_Inventory_Manager::get_alert_counts();
                $pending_reqs = Dental_Inventory_Manager::count_pending_requests();
                if ($inv_alerts['low_stock'] > 0 || $inv_alerts['expiring'] > 0 || $pending_reqs > 0):
            ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&inv_view=hub')); ?>" style="text-decoration:none;">
                <div style="background:var(--dc-danger-light);border:1px solid var(--dc-danger);border-radius:10px;padding:12px 18px;margin-bottom:16px;font-size:13px;color:var(--dc-danger);display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    📦 <b>هشدار انبار:</b>
                    <?php if($inv_alerts['low_stock']>0): ?><?php echo $inv_alerts['low_stock']; ?> کالا کسری موجودی<?php endif; ?>
                    <?php if($inv_alerts['low_stock']>0 && $inv_alerts['expiring']>0): ?> — <?php endif; ?>
                    <?php if($inv_alerts['expiring']>0): ?><?php echo $inv_alerts['expiring']; ?> کالا نزدیک به انقضا<?php endif; ?>
                    <?php if($pending_reqs>0): ?> — 📢 <?php echo $pending_reqs; ?> درخواست کالا در انتظار بررسی<?php endif; ?>
                    <span style="margin-right:auto;font-size:11px;">مشاهده ←</span>
                </div>
            </a>
            <?php endif; endif; ?>

            <?php if (class_exists('Dental_Inventory_Manager')): ?>
            <div style="text-align:left;margin-bottom:16px;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&inv_view=request')); ?>" class="dc-btn dc-btn-ghost dc-btn-sm">
                    📢 درخواست کالا
                </a>
            </div>
            <?php endif; ?>

            <?php if ($this->user_has_role(['dental_admin']) && class_exists('Dental_Shift_Scheduler')):
                $rota_alerts = Dental_Shift_Scheduler::get_rota_mismatch_alerts();
                if (!empty($rota_alerts)):
            ?>
            <div style="background:var(--dc-warning-light);border:1px solid var(--dc-accent-warm);border-radius:10px;padding:12px 18px;margin-bottom:16px;font-size:13px;color:#8a6200;">
                ⏰ <b>هشدار حضور:</b> طبق برنامه باید امروز کار می‌کردن ولی هنوز ورود نزدن —
                <?php echo esc_html(implode('، ', array_map(fn($a)=>"{$a['name']} ({$a['type']} — شیفت {$a['shift']})", $rota_alerts))); ?>
            </div>
            <?php endif; endif; ?>

            <div class="dc-grid dc-grid-4" style="gap:16px;margin-bottom:24px;" id="dc-role-stats">
                <?php
                if ($is_doctor_role && class_exists('Dental_Reception_Manager')):
                    // ─── داشبورد پزشک ───────────────────────────────
                    $today_g = current_time('Y-m-d');
                    $patients_today = count(Dental_Reception_Manager::get_done_for_date($today_g, $cu->ID));
                    $rev_today = $rev_month = 0;
                    if (class_exists('Dental_Ledger_Manager')) {
                        $rev_today = (float)$wpdb->get_var($wpdb->prepare(
                            "SELECT SUM(amount_received) FROM {$wpdb->prefix}dental_daily_ledger WHERE doctor_id=%d AND entry_date=%s",
                            $cu->ID, $today_g
                        ));
                        $rev_month = (float)$wpdb->get_var($wpdb->prepare(
                            "SELECT SUM(amount_received) FROM {$wpdb->prefix}dental_daily_ledger WHERE doctor_id=%d AND entry_date>=%s",
                            $cu->ID, date('Y-m-01')
                        ));
                    }
                    $unread_msg  = Dental_Workspace_Manager::unread_count($cu->ID);
                    $tasks_today = Dental_Workspace_Manager::get_my_tasks_today_count($cu->ID);
                    $stats = [
                        ['icon'=>'🦷','value'=>number_format($patients_today),'label'=>'بیماران امروز شما','class'=>'','link'=>admin_url('admin.php?page=dental-doctor-desk')],
                        ['icon'=>'💰','value'=>$this->format_currency($rev_today).'<div style="font-size:11px;color:var(--dc-neutral-400);margin-top:2px;">این ماه: '.$this->format_currency($rev_month).'</div>','label'=>'دریافتی امروز','class'=>'dc-stat-success','link'=>admin_url('admin.php?page=dental-financial&section=ledger')],
                        ['icon'=>'💬','value'=>number_format($unread_msg),'label'=>'پیام‌های شما','class'=>$unread_msg>0?'dc-stat-danger':'','link'=>'#dc-workspace-widgets'],
                        ['icon'=>'✅','value'=>number_format($tasks_today),'label'=>'چک‌لیست امروز شما','class'=>$tasks_today>0?'dc-stat-danger':'','link'=>'#dc-workspace-widgets'],
                    ];

                elseif ($is_secretary_role && class_exists('Dental_Reception_Manager')):
                    // ─── داشبورد منشی/پذیرش/دستیار ──────────────────
                    $unread_msg   = Dental_Workspace_Manager::unread_count($cu->ID);
                    $tasks_today  = Dental_Workspace_Manager::get_my_tasks_today_count($cu->ID);
                    $checkins     = Dental_Reception_Manager::get_my_checkins_today_count($cu->ID);
                    $pending_leave= class_exists('Dental_Attendance_Manager') ? count(array_filter(Dental_Attendance_Manager::get_my_leaves($cu->ID), fn($l)=>$l['status']==='pending')) : 0;
                    $stats = [
                        ['icon'=>'💬','value'=>number_format($unread_msg),'label'=>'پیام‌های من','class'=>$unread_msg>0?'dc-stat-danger':'','link'=>'#dc-workspace-widgets'],
                        ['icon'=>'✅','value'=>number_format($tasks_today),'label'=>'چک‌لیست امروز من','class'=>$tasks_today>0?'dc-stat-danger':'','link'=>'#dc-workspace-widgets'],
                        ['icon'=>'📋','value'=>number_format($checkins),'label'=>'پذیرش‌های امروز من','class'=>'','link'=>admin_url('admin.php?page=dental-reception')],
                        ['icon'=>'🌴','value'=>number_format($pending_leave),'label'=>'مرخصی در انتظار من','class'=>$pending_leave>0?'dc-stat-danger':'','link'=>admin_url('admin.php?page=dental-attendance')],
                    ];

                else:
                    // ─── داشبورد مدیریت / مسئول مالی (پیش‌فرض) ──────
                    $stats = [
                        ['icon'=>'👥','value'=>number_format($patient_count),'label'=>'بیمار','class'=>'','link'=>admin_url('admin.php?page=dental-patients')],
                        ['icon'=>'💰','value'=>$this->format_currency($this->get_total_received()),'label'=>'دریافتی اقساط این ماه','sub'=>$this->get_pending_receipts_count() > 0 ? $this->get_pending_receipts_count().' فیش در انتظار تأیید' : '','class'=>'dc-stat-success','link'=>admin_url('admin.php?page=dental-financial')],
                        ['icon'=>'⚠️','value'=>number_format($overdue),'label'=>'قسط معوقه','class'=>$overdue>0?'dc-stat-danger':'','link'=>admin_url('admin.php?page=dental-financial')],
                        ['icon'=>'📱','value'=>$this->get_sms_count_today(),'label'=>'پیامک امروز','class'=>'','link'=>admin_url('admin.php?page=dental-sms-log')],
                    ];
                endif;

                foreach($stats as $s):
                ?>
                <a href="<?php echo esc_url($s['link']); ?>" style="text-decoration:none;">
                    <div class="dc-stat-card <?php echo esc_attr($s['class']); ?>">
                        <div class="dc-stat-icon"><?php echo $s['icon']; ?></div>
                        <div class="dc-stat-value"><?php echo $s['value']; ?></div>
                        <div class="dc-stat-label"><?php echo esc_html($s['label']); ?></div>
                        <?php if (!empty($s['sub'])): ?>
                        <div style="font-size:10px;color:var(--dc-accent-warm);font-weight:700;margin-top:2px;">⚠️ <?php echo esc_html($s['sub']); ?></div>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- لیست پرسنل امروز — کارتی، فقط مدیریت -->
            <?php if ($is_admin_role && class_exists('Dental_Attendance_Manager')):
                $staff_cards = Dental_Attendance_Manager::get_today_staff_cards();
                $status_ui = [
                    'present' => ['حاضر','var(--dc-accent-dark)','var(--dc-accent-light)'],
                    'left'    => ['خارج شده','#B8860B','#FDF3D9'],
                    'absent'  => ['غایب','var(--dc-danger)','var(--dc-danger-light)'],
                    'lunch'   => ['در حال ناهار','#8B5CF6','#F1EBFC'],
                    'break'   => ['در حال استراحت','#F0A500','#FEF6E4'],
                ];
            ?>
            <div class="dc-card" style="margin-bottom:24px;">
                <div class="dc-card-header">
                    <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                        <i data-lucide="users" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                        لیست پرسنل امروز
                    </h3>
                    <div style="display:flex;gap:6px;">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=dental-attendance&tab=break_settings')); ?>" class="dc-btn dc-btn-ghost dc-btn-sm">
                            ⏱️ تنظیم سقف زمان
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=dental-attendance&tab=attendance_report')); ?>" class="dc-btn dc-btn-ghost dc-btn-sm">
                            <i data-lucide="file-text" style="width:13px;height:13px;"></i> گزارش حضور و غیاب
                        </a>
                    </div>
                </div>
                <div class="dc-card-body" style="padding:16px;">
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:14px;">
                        <?php foreach($staff_cards as $sc):
                            [$s_label,$s_color,$s_bg] = $status_ui[$sc['status']];
                            $time_label = $sc['status']==='present' ? 'ورود' : ($sc['status']==='left' ? 'خروج' : '');
                            $bn = $sc['break_note'];
                        ?>
                        <div style="border:1px solid <?php echo $bn&&$bn['over']?'var(--dc-danger)':'var(--dc-neutral-100)'; ?>;border-radius:12px;padding:16px 12px;text-align:center;">
                            <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--dc-primary),var(--dc-primary-dark));
                                color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:20px;margin:0 auto 10px;">
                                <?php echo esc_html(mb_substr($sc['name'],0,1)); ?>
                            </div>
                            <div style="font-size:13px;font-weight:700;color:var(--dc-neutral-900);margin-bottom:2px;"><?php echo esc_html($sc['name']); ?></div>
                            <div style="font-size:11px;color:var(--dc-neutral-500);margin-bottom:10px;"><?php echo esc_html($sc['role']); ?></div>
                            <div style="background:<?php echo $s_bg; ?>;color:<?php echo $s_color; ?>;border-radius:8px;padding:4px 10px;font-size:12px;font-weight:700;margin-bottom:8px;">
                                <?php echo esc_html($s_label); ?>
                            </div>
                            <div style="font-size:11px;color:<?php echo $bn&&$bn['over']?'var(--dc-danger)':'var(--dc-neutral-500)'; ?>;display:flex;align-items:center;justify-content:center;gap:4px;">
                                <i data-lucide="clock" style="width:12px;height:12px;"></i>
                                <?php if($bn): ?>
                                    <?php echo $bn['elapsed']; ?>/<?php echo $bn['limit']; ?> دقیقه<?php echo $bn['over']?' ⚠️':''; ?>
                                <?php elseif($sc['time']): ?>
                                    <?php echo esc_html($time_label); ?> <?php echo esc_html(date('H:i', strtotime($sc['time']))); ?>
                                <?php else: ?>—<?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($is_admin_role):
                // ─── درخواست‌های در انتظار من (مرخصی + نوبت) ─────────
                $pending_leaves_c = class_exists('Dental_Attendance_Manager') ? count(Dental_Attendance_Manager::get_pending_leaves()) : 0;
                $pending_appts_c  = 0;
                if (class_exists('Dental_Booking_Appointment')) {
                    $pending_appts_c = (int)$wpdb->get_var(
                        "SELECT COUNT(*) FROM {$wpdb->prefix}dental_appointments WHERE status='pending'"
                    );
                }
                $total_pending = $pending_leaves_c + $pending_appts_c;

                // ─── عملکرد پزشکان امروز ──────────────────────────────
                $doctors_today = get_users(['role__in'=>['dental_doctor'],'fields'=>['ID','display_name']]);
                $doctor_perf = [];
                foreach ($doctors_today as $doc) {
                    $today_g = current_time('Y-m-d');
                    $seen = class_exists('Dental_Reception_Manager') ? count(Dental_Reception_Manager::get_done_for_date($today_g, $doc->ID)) : 0;
                    $rev  = (float)$wpdb->get_var($wpdb->prepare(
                        "SELECT SUM(amount_received) FROM {$wpdb->prefix}dental_daily_ledger WHERE doctor_id=%d AND entry_date=%s",
                        $doc->ID, $today_g
                    ));
                    $no_show = 0;
                    if (class_exists('Dental_Booking_Appointment')) {
                        $no_show = (int)$wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}dental_appointments WHERE doctor_id=%d AND appt_date=%s AND status='no_show'",
                            $doc->ID, $today_g
                        ));
                    }
                    $doctor_perf[] = ['name'=>$doc->display_name,'seen'=>$seen,'revenue'=>$rev,'no_show'=>$no_show];
                }
            ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">

                <!-- درخواست‌های در انتظار من -->
                <div class="dc-card" style="border-top:3px solid var(--dc-accent-warm);">
                    <div class="dc-card-header">
                        <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                            🔔 درخواست‌های در انتظار من
                            <?php if($total_pending>0): ?><span class="dc-badge dc-badge-danger"><?php echo $total_pending; ?></span><?php endif; ?>
                        </h3>
                    </div>
                    <div style="padding:0;">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=dental-attendance&tab=pending')); ?>"
                           style="display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid var(--dc-neutral-100);text-decoration:none;color:inherit;">
                            <span style="font-size:18px;">🌴</span>
                            <span style="flex:1;font-size:13px;">درخواست‌های مرخصی در انتظار تأیید</span>
                            <span class="dc-badge <?php echo $pending_leaves_c>0?'dc-badge-danger':'dc-badge-neutral'; ?>"><?php echo $pending_leaves_c; ?></span>
                        </a>
                        <?php if (class_exists('Dental_Booking_Appointment')): ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=dental-booking-list&status=pending')); ?>"
                           style="display:flex;align-items:center;gap:10px;padding:14px 18px;text-decoration:none;color:inherit;">
                            <span style="font-size:18px;">📅</span>
                            <span style="flex:1;font-size:13px;">نوبت‌های در انتظار تأیید</span>
                            <span class="dc-badge <?php echo $pending_appts_c>0?'dc-badge-danger':'dc-badge-neutral'; ?>"><?php echo $pending_appts_c; ?></span>
                        </a>
                        <?php endif; ?>
                        <?php if ($total_pending===0): ?>
                        <p style="text-align:center;color:var(--dc-neutral-400);font-size:12px;padding:16px;">چیزی در انتظار تصمیم شما نیست 👍</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- عملکرد پزشکان امروز -->
                <div class="dc-card" style="border-top:3px solid var(--dc-primary);">
                    <div class="dc-card-header">
                        <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">📊 عملکرد پزشکان امروز</h3>
                    </div>
                    <div class="dc-table-wrap">
                        <table class="dc-table">
                            <thead><tr><th>پزشک</th><th>بیمار دیده‌شده</th><th>درآمد</th><th>عدم حضور</th></tr></thead>
                            <tbody>
                            <?php if (empty($doctor_perf)): ?>
                            <tr><td colspan="4" style="text-align:center;padding:20px;color:var(--dc-neutral-400);">پزشکی ثبت نشده</td></tr>
                            <?php else: foreach($doctor_perf as $dp): ?>
                            <tr>
                                <td style="font-weight:600;"><?php echo esc_html($dp['name']); ?></td>
                                <td style="text-align:center;"><?php echo number_format($dp['seen']); ?></td>
                                <td style="color:var(--dc-accent-dark);font-weight:600;"><?php echo number_format($dp['revenue']); ?></td>
                                <td style="text-align:center;color:<?php echo $dp['no_show']>0?'var(--dc-danger)':'var(--dc-neutral-400)'; ?>;"><?php echo $dp['no_show']; ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- اقساط معوقه اخیر -->
            <?php if ($overdue > 0): ?>
            <div class="dc-card" style="border-color:var(--dc-danger);">
                <div class="dc-card-header" style="background:var(--dc-danger-light);">
                    <h3 class="dc-heading-4" style="color:var(--dc-danger);">⚠️ اقساط معوقه</h3>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dental-financial')); ?>"
                       class="dc-btn dc-btn-danger dc-btn-sm">مشاهده همه</a>
                </div>
                <div class="dc-card-body" style="padding:0;">
                    <?php
                    $overdue_items = $wpdb->get_results(
                        "SELECT ii.*, i.patient_id, p.post_title as patient_name
                         FROM {$wpdb->prefix}dental_installment_items ii
                         JOIN {$wpdb->prefix}dental_installments i ON ii.installment_id = i.id
                         LEFT JOIN {$wpdb->posts} p ON i.patient_id = p.ID
                         WHERE ii.status = 'overdue'
                         ORDER BY ii.due_date ASC LIMIT 5"
                    );
                    foreach($overdue_items as $oi):
                        $days_overdue = (int)((time() - strtotime($oi->due_date)) / DAY_IN_SECONDS);
                    ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--dc-neutral-100);">
                        <div>
                            <div style="font-size:13px;font-weight:600;"><?php echo esc_html($oi->patient_name); ?></div>
                            <div style="font-size:11px;color:var(--dc-danger);"><?php echo $days_overdue; ?> روز تأخیر | سررسید: <?php echo esc_html($oi->due_date_jalali); ?></div>
                        </div>
                        <div style="font-weight:700;color:var(--dc-danger);"><?php echo number_format($oi->amount); ?> ت</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:1.3fr 1fr;gap:16px;align-items:start;margin-top:20px;">

                <div style="display:flex;flex-direction:column;gap:16px;">
                    <!-- نوبت‌های امروز (اتصال به پلاگین نوبت‌دهی) -->
                    <div class="dc-card">
                        <div class="dc-card-header">
                            <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                                <i data-lucide="calendar-clock" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                                نوبت‌های امروز
                            </h3>
                            <?php if (class_exists('Dental_Booking_Appointment')): ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=dental-booking')); ?>" class="dc-btn dc-btn-ghost dc-btn-sm">تقویم کامل</a>
                            <?php endif; ?>
                        </div>
                        <?php if (!class_exists('Dental_Booking_Appointment')): ?>
                        <div class="dc-card-body"><p style="font-size:12px;color:var(--dc-neutral-500);">برای نمایش نوبت‌های امروز، پلاگین «نوبت‌دهی» را فعال کنید.</p></div>
                        <?php else:
                            $today_appts = Dental_Booking_Appointment::get_day_appointments(current_time('Y-m-d'));
                            $status_map = ['pending'=>['⏳','#F0A500'],'confirmed'=>['✅','#2ECC9A'],'done'=>['✔️','#1A6B8A'],'no_show'=>['🚷','#E05252']];
                            // ─── گروه‌بندی بر اساس پزشک — به‌جای یه لیست تخت،
                            // هر پزشک یه ردیف مجزا با تعداد نوبت‌هاش داره،
                            // با کلیک (AJAX) لیست کامل بیمارها/نوع درمانش باز می‌شه ──
                            $by_doctor_today = [];
                            foreach ($today_appts as $a) {
                                $did = (int)$a['doctor_id'];
                                if (!isset($by_doctor_today[$did])) $by_doctor_today[$did] = ['name'=>$a['doctor_name'], 'items'=>[]];
                                $by_doctor_today[$did]['items'][] = $a;
                            }
                        ?>
                        <?php if (empty($by_doctor_today)): ?>
                        <div class="dc-card-body"><p style="font-size:13px;color:var(--dc-neutral-400);text-align:center;padding:12px 0;">نوبتی برای امروز ثبت نشده</p></div>
                        <?php else: ?>
                        <div style="padding:0;max-height:320px;overflow-y:auto;">
                            <?php foreach($by_doctor_today as $did => $grp): ?>
                            <div style="border-bottom:1px solid #F5F5F5;">
                                <div style="display:flex;align-items:center;gap:10px;padding:11px 18px;cursor:pointer;" onclick="dcToggleDoctorToday(<?php echo $did; ?>,this)">
                                    <span style="font-size:14px;">👨‍⚕️</span>
                                    <span style="flex:1;font-size:13px;font-weight:700;"><?php echo esc_html($grp['name'] ?: '—'); ?></span>
                                    <span class="dc-badge" style="background:var(--dc-primary-light);color:var(--dc-primary);"><?php echo count($grp['items']); ?> نوبت</span>
                                    <span style="font-size:10px;color:var(--dc-neutral-400);">▾</span>
                                </div>
                                <div class="dc-today-doctor-detail" data-doctor="<?php echo $did; ?>" style="display:none;background:var(--dc-neutral-50);">
                                    <?php foreach($grp['items'] as $a): [$ic,$col]=$status_map[$a['status']]??['•','#999']; ?>
                                    <div style="display:flex;align-items:center;gap:10px;padding:8px 18px 8px 30px;border-top:1px solid #EEF2F5;">
                                        <span style="min-width:44px;font-weight:700;color:var(--dc-primary);direction:ltr;font-size:12px;"><?php echo esc_html(substr($a['start_time'],0,5)); ?></span>
                                        <span style="flex:1;font-size:12px;">
                                            <?php echo esc_html($a['patient_name']); ?>
                                            <?php if(!empty($a['service_title'])): ?><span style="color:var(--dc-neutral-500);"> — <?php echo esc_html($a['service_title']); ?></span><?php endif; ?>
                                        </span>
                                        <span style="font-size:13px;"><?php echo $ic; ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <script>
                        function dcToggleDoctorToday(did, trigger){
                            var row = document.querySelector('.dc-today-doctor-detail[data-doctor="'+did+'"]');
                            if (!row) return;
                            var arrow = trigger.querySelector('span:last-child');
                            if (row.style.display === 'block') { row.style.display = 'none'; arrow.textContent = '▾'; }
                            else { row.style.display = 'block'; arrow.textContent = '▴'; }
                        }
                        </script>
                        <?php endif; endif; ?>
                    </div>

                    <!-- درمان‌های در انتظار ادامه — فقط وقتی مسیر درمان فعاله -->
                    <?php if (class_exists('Dental_Pathway_Manager') && Dental_Pathway_Manager::is_enabled()):
                        // ─── دکتر فقط بیمارهای خودش رو می‌بینه (نه بیمارهای
                        // دکترهای دیگه)، مدیر/مالی/منشی همه رو — دقیقاً هماهنگ
                        // با همون منطق «نوبت‌های امروز» کنارش ────────────────
                        $pw_doctor_filter = $is_doctor_role ? $cu->ID : 0;
                        $pending_continuations = Dental_Pathway_Manager::get_pending_continuations(15, $pw_doctor_filter);
                        $status_labels_pw = Dental_Pathway_Manager::get_status_labels();
                    ?>
                    <div class="dc-card" style="margin-top:20px;">
                        <div class="dc-card-header">
                            <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                                <i data-lucide="route" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                                🛤️ درمان‌های در انتظار ادامه
                            </h3>
                            <span style="font-size:11px;color:var(--dc-neutral-500);"><?php echo count($pending_continuations); ?> مورد</span>
                        </div>
                        <?php if (empty($pending_continuations)): ?>
                        <div class="dc-card-body"><p style="font-size:13px;color:var(--dc-neutral-400);text-align:center;padding:12px 0;">همه‌ی مسیرهای درمان به‌روزن 👍</p></div>
                        <?php else: ?>
                        <div class="dc-table-wrap" style="max-height:320px;overflow-y:auto;">
                            <table class="dc-table">
                                <thead><tr><th>بیمار</th><th>دندان</th><th>درمان فعلی</th><th>مرحله‌ی بعدی</th><?php if(!$is_doctor_role): ?><th>پزشک</th><?php endif; ?><th>وضعیت</th></tr></thead>
                                <tbody>
                                <?php foreach($pending_continuations as $row):
                                    $sl = $status_labels_pw[$row['next_status']] ?? ['—','#999'];
                                    $tooth_label = $row['tooth_number'] ? Dental_Service_Catalog::describe_tooth_number($row['tooth_number']) : '—';
                                    $profile_url = admin_url("admin.php?page=dental-patients&action=profile&id={$row['patient_id']}&tab=pathway");
                                ?>
                                <tr style="cursor:pointer;" onclick="window.location.href='<?php echo esc_url($profile_url); ?>'">
                                    <td style="font-weight:600;font-size:12px;"><?php echo esc_html($row['patient_name']); ?></td>
                                    <td style="font-size:11px;"><?php echo esc_html($tooth_label); ?></td>
                                    <td style="font-size:12px;"><?php echo esc_html($row['pathway_title']); ?></td>
                                    <td style="font-size:12px;font-weight:600;color:var(--dc-primary);"><?php echo esc_html($row['next_step']); ?></td>
                                    <?php if(!$is_doctor_role): ?><td style="font-size:11px;color:var(--dc-neutral-500);"><?php echo esc_html($row['doctor_name']); ?></td><?php endif; ?>
                                    <td style="font-size:11px;color:<?php echo $sl[1]; ?>;font-weight:700;"><?php echo esc_html($sl[0]); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($is_admin_role): ?>
                    <div class="dc-card">
                        <div class="dc-card-header">
                            <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                                <i data-lucide="trending-up" style="width:16px;height:16px;color:var(--dc-accent-dark);"></i>
                                درآمد ۶ ماه اخیر
                            </h3>
                        </div>
                        <div class="dc-card-body" style="padding:16px;">
                            <?php
                            $monthly = $wpdb->get_results(
                                "SELECT DATE_FORMAT(paid_date,'%Y-%m') as ym, SUM(paid_amount) as total
                                 FROM {$wpdb->prefix}dental_installment_items
                                 WHERE status='paid' AND paid_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                                 GROUP BY ym ORDER BY ym ASC", ARRAY_A
                            );
                            if (empty($monthly)): ?>
                            <p style="text-align:center;color:var(--dc-neutral-400);font-size:13px;padding:20px 0;">داده‌ای برای نمایش نیست</p>
                            <?php else:
                                $m_labels = array_map(fn($m)=>Dental_Jalali::to_jalali($m['ym'].'-01','Y/m'), $monthly);
                                $m_data   = array_map(fn($m)=>(float)$m['total'], $monthly);
                            ?>
                            <canvas id="dc-dash-revenue-chart" height="90"></canvas>
                            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
                            <script>
                            document.addEventListener('DOMContentLoaded', function(){
                                var ctx = document.getElementById('dc-dash-revenue-chart');
                                if(!ctx || typeof Chart==='undefined') return;
                                new Chart(ctx, {
                                    type:'bar',
                                    data:{ labels:<?php echo wp_json_encode($m_labels); ?>,
                                        datasets:[{label:'درآمد',data:<?php echo wp_json_encode($m_data); ?>,backgroundColor:'rgba(26,107,138,0.75)',borderRadius:6}] },
                                    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}
                                });
                            });
                            </script>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div style="display:flex;flex-direction:column;gap:16px;" id="dc-workspace-widgets">
                    <?php if (class_exists('Dental_Workspace_Manager')):
                        $cu_id = get_current_user_id();
                        $statuses = Dental_Workspace_Manager::statuses();
                        $all_status = Dental_Workspace_Manager::get_all_staff_status();
                        $my_tasks = Dental_Workspace_Manager::get_my_tasks($cu_id);
                        $my_msgs  = Dental_Workspace_Manager::get_my_messages($cu_id, 8);
                        $unread   = Dental_Workspace_Manager::unread_count($cu_id);
                        $staff_users = get_users(['role__in'=>['dental_admin','dental_doctor','dental_secretary','dental_financial','dental_assistant','administrator'],'fields'=>['ID','display_name']]);
                    ?>

                    <?php if (class_exists('Dental_Attendance_Manager')):
                        $att_status = Dental_Attendance_Manager::get_today_status($cu_id_ws = get_current_user_id());
                        $open_brk   = Dental_Attendance_Manager::get_open_break($cu_id_ws);
                        // نکته: کارت جداگانه «حضور و خروج» و تایمر استراحت از اینجا
                        // حذف شدن چون به هدر بالای صفحه منتقل شدن (کنار «سلام...»).
                    ?>
                    <?php endif; ?>

                    <!-- وضعیت حضور -->
                    <div class="dc-card">
                        <div class="dc-card-header" style="flex-wrap:wrap;gap:8px;">
                            <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">🟢 وضعیت من و همکاران</h3>
                            <?php if (class_exists('Dental_Attendance_Manager') && $att_status_hdr['clocked_in']): ?>
                            <div style="display:flex;gap:6px;align-items:center;">
                                <?php if (!$open_brk_hdr): ?>
                                <button type="button" onclick="dcBreakQuick('start_break','lunch')" class="dc-btn dc-btn-secondary dc-btn-sm">🍽️ شروع ناهار</button>
                                <button type="button" onclick="dcBreakQuick('start_break','break')" class="dc-btn dc-btn-secondary dc-btn-sm">🛋️ شروع استراحت</button>
                                <?php else: ?>
                                <span id="dc-break-timer" style="font-size:11px;font-weight:700;background:var(--dc-primary-light);color:var(--dc-primary);padding:5px 10px;border-radius:12px;"></span>
                                <button type="button" onclick="dcBreakQuick('end_break')" class="dc-btn dc-btn-primary dc-btn-sm">↩️ بازگشت به کار</button>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="dc-card-body" style="padding:14px 16px;">
                            <?php
                            // ─── خلاصه‌ی امروز خودم — چقدر ناهار/استراحت مصرف کردم ──
                            if (class_exists('Dental_Attendance_Manager')):
                                $my_limits = Dental_Attendance_Manager::get_break_limits();
                                $my_role_sum = (array)$cu->roles;
                                $my_role_sum = $my_role_sum[0] ?? '';
                                $my_lunch_used = Dental_Attendance_Manager::get_today_break_total_minutes($cu_id, 'lunch');
                                $my_break_used = Dental_Attendance_Manager::get_today_break_total_minutes($cu_id, 'break');
                                $my_lunch_limit = $my_limits[$my_role_sum]['lunch'] ?? 30;
                                $my_break_limit = $my_limits[$my_role_sum]['break'] ?? 10;
                            ?>
                            <div style="background:var(--dc-neutral-50);border-radius:8px;padding:10px 12px;margin-bottom:12px;font-size:11px;display:flex;gap:16px;">
                                <div>🍽️ ناهار امروز: <b style="color:<?php echo $my_lunch_used>$my_lunch_limit?'var(--dc-danger)':'var(--dc-neutral-700)'; ?>;"><?php echo $my_lunch_used; ?></b> / <?php echo $my_lunch_limit; ?> دقیقه</div>
                                <div>🛋️ استراحت امروز: <b style="color:<?php echo $my_break_used>$my_break_limit?'var(--dc-danger)':'var(--dc-neutral-700)'; ?>;"><?php echo $my_break_used; ?></b> / <?php echo $my_break_limit; ?> دقیقه</div>
                            </div>
                            <?php
                                // ─── هشدار هماهنگی — چندنفر همین الان رفتن، که همه با هم نرن ──
                                $others_away = array_filter($all_status, fn($s) => in_array($s['status'], ['lunch','break']) && $s['id'] != $cu_id);
                                if (!empty($others_away)):
                            ?>
                            <div style="background:var(--dc-warning-light);border-radius:8px;padding:8px 12px;margin-bottom:12px;font-size:11px;color:#8a6200;display:flex;align-items:center;gap:6px;">
                                ⚠️ الان <b><?php echo count($others_away); ?> نفر</b> بیرونن (<?php echo esc_html(implode('، ', array_map(fn($s)=>$s['name'], $others_away))); ?>) — لطفاً هماهنگ باشید.
                            </div>
                            <?php endif; endif; ?>

                            <div id="dc-status-list" style="display:flex;flex-direction:column;gap:6px;max-height:220px;overflow-y:auto;">
                                <?php foreach($all_status as $s):
                                    [$icon,$label,$color] = $statuses[$s['status']] ?? ['⚪','—','#999'];
                                    $time_str = $s['time'] ? date('H:i', strtotime($s['time'])) : '';
                                ?>
                                <div style="display:flex;align-items:center;gap:8px;font-size:12px;padding:5px 0;border-bottom:1px solid var(--dc-neutral-50);">
                                    <span style="position:relative;">
                                        <?php echo $icon; ?>
                                        <?php if($s['is_online']): ?><span style="position:absolute;bottom:-2px;left:-2px;width:7px;height:7px;background:#2ECC9A;border-radius:50%;border:1.5px solid #fff;"></span><?php endif; ?>
                                    </span>
                                    <span style="flex:1;font-weight:<?php echo $s['id']==$cu_id?'700':'400'; ?>;"><?php echo esc_html($s['name']); ?><?php echo $s['id']==$cu_id?' (شما)':''; ?></span>
                                    <span style="color:<?php echo $color; ?>;font-size:11px;font-weight:600;"><?php echo esc_html($label); ?></span>
                                    <?php if($time_str): ?><span style="color:var(--dc-neutral-400);font-size:10px;min-width:32px;text-align:left;"><?php echo esc_html($time_str); ?></span><?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div style="margin-top:10px;font-size:10px;color:var(--dc-neutral-400);">
                                🟢 نقطه‌ی سبز = همین الان آنلاینه.
                            </div>
                        </div>
                    </div>

                    <?php if (class_exists('Dental_Attendance_Manager') && $open_brk_hdr):
                        $limits_w = Dental_Attendance_Manager::get_break_limits();
                        $my_role_w = ((array)$cu->roles)[0] ?? '';
                        $limit_min_w = $limits_w[$my_role_w][$open_brk_hdr['break_type']] ?? 30;
                        $prior_minutes_w = 0;
                        $all_today_breaks_w = Dental_Attendance_Manager::get_today_breaks($cu->ID);
                        foreach ($all_today_breaks_w as $b) {
                            if ($b['break_type'] === $open_brk_hdr['break_type'] && $b['id'] != $open_brk_hdr['id'] && $b['end_time']) {
                                $prior_minutes_w += round((strtotime($b['end_time']) - strtotime($b['start_time'])) / 60);
                            }
                        }
                    ?>
                    <script>
                    (function(){
                        var startTime = new Date('<?php echo esc_js(str_replace(' ','T',$open_brk_hdr['start_time'])); ?>').getTime();
                        var limitMin = <?php echo (int)$limit_min_w; ?>;
                        var priorMin = <?php echo (int)$prior_minutes_w; ?>;
                        var alerted = false;
                        function dcUpdateBreakTimer(){
                            var sessionMin = Math.floor((Date.now() - startTime) / 60000);
                            var totalMin = priorMin + sessionMin;
                            var el = document.getElementById('dc-break-timer');
                            if (el) {
                                el.textContent = '⏱️ ' + totalMin + ' از ' + limitMin + ' دقیقه';
                                el.style.color = totalMin > limitMin ? 'var(--dc-danger)' : 'var(--dc-primary)';
                            }
                            if (!alerted && totalMin > limitMin) {
                                alerted = true;
                                alert('⏰ زمان <?php echo $open_brk_hdr["break_type"]==="lunch"?"ناهار":"استراحت"; ?> شما (' + limitMin + ' دقیقه) امروز به پایان رسیده — لطفاً «بازگشت به کار» را بزنید.');
                            }
                        }
                        dcUpdateBreakTimer();
                        setInterval(dcUpdateBreakTimer, 10000);
                    })();
                    </script>
                    <?php endif; ?>

                    <!-- چک‌لیست کارها -->
                    <div class="dc-card">
                        <div class="dc-card-header">
                            <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">✅ چک‌لیست کارهای من</h3>
                            <button type="button" onclick="document.getElementById('dc-assign-task-box').style.display='flex'" class="dc-btn dc-btn-ghost dc-btn-sm">+ محول‌کردن</button>
                        </div>
                        <div class="dc-card-body" style="padding:0;">
                            <div id="dc-assign-task-box" style="display:none;padding:12px 16px;gap:8px;border-bottom:1px solid var(--dc-neutral-100);flex-wrap:wrap;">
                                <input type="text" id="dc-new-task-title" placeholder="عنوان کار..." class="dc-input" style="flex:1;min-width:140px;">
                                <input type="text" id="dc-new-task-date" placeholder="تاریخ (اختیاری = هر زمان)" class="dc-input dc-datepicker" style="width:140px;" dir="ltr">
                                <select id="dc-new-task-user" class="dc-select" style="width:120px;">
                                    <?php foreach($staff_users as $su): ?>
                                    <option value="<?php echo $su->ID; ?>"><?php echo esc_html($su->display_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" onclick="dcAssignTask()" class="dc-btn dc-btn-primary dc-btn-sm">ثبت</button>
                            </div>
                            <div id="dc-tasks-list">
                                <?php if (empty($my_tasks)): ?>
                                <p style="text-align:center;color:var(--dc-neutral-400);font-size:12px;padding:20px;">کاری برای شما ثبت نشده</p>
                                <?php else: foreach($my_tasks as $t): ?>
                                <label style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid var(--dc-neutral-50);cursor:pointer;">
                                    <input type="checkbox" onchange="dcToggleTask(<?php echo $t['id']; ?>,this.checked)" style="width:16px;height:16px;accent-color:var(--dc-primary);">
                                    <div style="flex:1;">
                                        <div style="font-size:13px;"><?php echo esc_html($t['title']); ?></div>
                                        <div style="font-size:10px;color:var(--dc-neutral-400);">از طرف <?php echo esc_html($t['assigned_by_name']); ?></div>
                                    </div>
                                </label>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- پیام‌های داخلی — پیش‌نمایش، صفحه کامل جداست -->
                    <div class="dc-card">
                        <div class="dc-card-header">
                            <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                                💬 پیام‌ها <?php if($unread>0): ?><span class="dc-badge dc-badge-danger"><?php echo $unread; ?></span><?php endif; ?>
                            </h3>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&msg_view=1')); ?>" class="dc-btn dc-btn-ghost dc-btn-sm">باز کردن ←</a>
                        </div>
                        <div class="dc-card-body" style="padding:0;">
                            <div id="dc-messages-list" style="max-height:200px;overflow-y:auto;">
                                <?php if (empty($my_msgs)): ?>
                                <p style="text-align:center;color:var(--dc-neutral-400);font-size:12px;padding:20px;">پیامی نیست</p>
                                <?php else: foreach(array_slice($my_msgs,0,5) as $m):
                                    $is_mine_sent = $m['from_user_id']==$cu_id;
                                ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&msg_view=1&with='.($is_mine_sent?$m['to_user_id']:$m['from_user_id']))); ?>"
                                   class="dc-msg-row" data-msg-id="<?php echo (int)$m['id']; ?>" data-unread="<?php echo (!$m['is_read']&&!$is_mine_sent)?'1':'0'; ?>"
                                   style="display:block;text-decoration:none;color:inherit;padding:9px 16px;border-bottom:1px solid var(--dc-neutral-50);<?php echo !$m['is_read']&&!$is_mine_sent?'background:var(--dc-primary-light);':''; ?>">
                                    <div style="font-size:11px;font-weight:700;color:var(--dc-primary);"><?php echo esc_html($m['from_name']); ?><?php echo $m['to_user_id']==0?' → 📢 همه':''; ?></div>
                                    <div style="font-size:12px;color:var(--dc-neutral-700);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo esc_html($m['message']); ?></div>
                                    <div style="font-size:10px;color:var(--dc-neutral-400);display:flex;align-items:center;gap:4px;">
                                        <?php echo esc_html(human_time_diff(strtotime($m['created_at']), current_time('timestamp'))); ?> پیش
                                        <?php if($is_mine_sent): ?>
                                        <span style="color:<?php echo $m['is_read']?'var(--dc-primary)':'var(--dc-neutral-300)'; ?>;"><?php echo $m['is_read']?'✓✓':'✓'; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- تولد امروز -->
                    <?php
                    $today_md = '/' . explode('/', $today_j = Dental_Jalali::today())[1] . '/' . explode('/', $today_j)[2];
                    $bday_patients = get_posts(['post_type'=>'dental_patient','posts_per_page'=>-1,'post_status'=>'publish','meta_key'=>'_patient_dob_jalali']);
                    $birthdays_today = [];
                    foreach($bday_patients as $bp) {
                        $dob = get_post_meta($bp->ID,'_patient_dob_jalali',true);
                        $dp  = explode('/', $dob);
                        if (count($dp) === 3 && ('/'.$dp[1].'/'.$dp[2]) === $today_md) $birthdays_today[] = $bp;
                    }
                    if (!empty($birthdays_today)):
                    ?>
                    <div class="dc-card" style="border-top:3px solid #8B5CF6;">
                        <div class="dc-card-header">
                            <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">🎂 تولد امروز</h3>
                        </div>
                        <div style="padding:0;">
                            <?php foreach($birthdays_today as $bp): ?>
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 18px;border-bottom:1px solid #F5F5F5;">
                                <span style="font-size:13px;font-weight:600;"><?php echo esc_html($bp->post_title); ?></span>
                                <a href="<?php echo esc_url(admin_url("admin.php?page=dental-patients&action=profile&id={$bp->ID}")); ?>" class="dc-btn dc-btn-ghost dc-btn-sm">پرونده</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- فعالیت‌های اخیر — فقط مسئول مالی و مدیریت (چون شامل اطلاعات پرداخت است) -->
                    <?php if ($is_admin_role || $is_financial_role): ?>
                    <div class="dc-card">
                        <div class="dc-card-header">
                            <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                                <i data-lucide="activity" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                                فعالیت‌های اخیر
                            </h3>
                        </div>
                        <?php
                        $activities = [];

                        $recent_patients = get_posts(['post_type'=>'dental_patient','posts_per_page'=>3,'post_status'=>'publish','orderby'=>'date','order'=>'DESC']);
                        foreach($recent_patients as $rp) {
                            $activities[] = ['time'=>strtotime($rp->post_date),'icon'=>'👤','text'=>'بیمار جدید: '.$rp->post_title,'link'=>admin_url("admin.php?page=dental-patients&action=profile&id={$rp->ID}")];
                        }

                        $recent_payments = $wpdb->get_results(
                            "SELECT ii.paid_date, ii.amount, p.post_title as patient_name, i.patient_id
                             FROM {$wpdb->prefix}dental_installment_items ii
                             JOIN {$wpdb->prefix}dental_installments i ON ii.installment_id=i.id
                             LEFT JOIN {$wpdb->posts} p ON i.patient_id=p.ID
                             WHERE ii.status='paid' AND ii.paid_date IS NOT NULL
                             ORDER BY ii.paid_date DESC LIMIT 3", ARRAY_A
                        );
                        foreach($recent_payments as $rpay) {
                            $activities[] = ['time'=>strtotime($rpay['paid_date']),'icon'=>'💰','text'=>'پرداخت '.number_format($rpay['amount']).' ت — '.$rpay['patient_name'],'link'=>admin_url("admin.php?page=dental-patients&action=profile&id={$rpay['patient_id']}")];
                        }

                        // آخرین رضایت‌نامه‌های امضاشده
                        if (class_exists('Dental_Consent_Form')) {
                            $consent_rows = $wpdb->get_results(
                                "SELECT p.ID, p.post_title, pm.meta_value, pm.meta_id
                                 FROM {$wpdb->prefix}postmeta pm
                                 JOIN {$wpdb->prefix}posts p ON pm.post_id=p.ID
                                 WHERE pm.meta_key LIKE '_consent_signed_%'
                                 ORDER BY pm.meta_id DESC LIMIT 3", ARRAY_A
                            );
                            foreach($consent_rows as $cr) {
                                $data = maybe_unserialize($cr['meta_value']);
                                if (!is_array($data)) continue;
                                $activities[] = ['time'=>strtotime($data['signed_at']??'now'),'icon'=>'📝','text'=>'رضایت‌نامه امضا شد — '.$cr['post_title'],'link'=>admin_url("admin.php?page=dental-patients&action=profile&id={$cr['ID']}")];
                            }
                        }

                        usort($activities, fn($a,$b)=>$b['time']<=>$a['time']);
                        $activities = array_slice($activities, 0, 6);
                        ?>
                        <div style="padding:0;">
                        <?php if (empty($activities)): ?>
                        <p style="text-align:center;color:var(--dc-neutral-400);font-size:13px;padding:20px;">فعالیتی ثبت نشده</p>
                        <?php else: foreach($activities as $act): ?>
                        <a href="<?php echo esc_url($act['link']); ?>" style="display:flex;align-items:center;gap:10px;padding:10px 18px;border-bottom:1px solid #F5F5F5;text-decoration:none;color:inherit;">
                            <span style="font-size:16px;"><?php echo $act['icon']; ?></span>
                            <span style="flex:1;font-size:12px;color:var(--dc-neutral-700);"><?php echo esc_html($act['text']); ?></span>
                            <span style="font-size:10px;color:var(--dc-neutral-400);"><?php echo esc_html(human_time_diff($act['time'], time())); ?></span>
                        </a>
                        <?php endforeach; endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <script>
        var dcWpNonce   = '<?php echo esc_js(wp_create_nonce("dental_workspace")); ?>';
        var dcAjaxUrl   = '<?php echo esc_js(admin_url("admin-ajax.php")); ?>';

        // ─── رفرش زنده وضعیت/چک‌لیست/پیام‌ها — بدون رفرش کامل صفحه ──
        var dcPrevUnreadCount = null;
        function dcRefreshWorkspace(){
            var fd = new FormData();
            fd.append('action','dental_refresh_workspace');
            fd.append('_wpnonce', dcWpNonce);
            fetch(dcAjaxUrl, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                if(!res.success) return;
                var st = document.getElementById('dc-status-list');   if(st) st.innerHTML = res.data.status_html;
                var tk = document.getElementById('dc-tasks-list');    if(tk) tk.innerHTML = res.data.tasks_html;
                var ms = document.getElementById('dc-messages-list'); if(ms) ms.innerHTML = res.data.msgs_html;

                // ─── هشدار پیام جدید — اگه تعداد نخونده‌ها از دفعه قبل بیشتر شده ──
                var unreadRows = ms ? ms.querySelectorAll('.dc-msg-row[data-unread="1"]') : [];
                var unreadCount = unreadRows.length;
                if (dcPrevUnreadCount !== null && unreadCount > dcPrevUnreadCount) {
                    dcNotifyNewMessage();
                }
                dcPrevUnreadCount = unreadCount;

                // ─── علامت‌گذاری خودکار خوانده‌شده — چون همین الان دیدشون ──
                unreadRows.forEach(function(row){
                    var fd2 = new FormData();
                    fd2.append('action','dental_mark_message_read');
                    fd2.append('_wpnonce', dcWpNonce);
                    fd2.append('message_id', row.dataset.msgId);
                    fetch(dcAjaxUrl, {method:'POST', body:fd2});
                });
            });
        }
        function dcNotifyNewMessage(){
            // یه هشدار قابل‌توجه ولی غیرمزاحم — گوشه بالا سمت راست
            var toast = document.createElement('div');
            toast.textContent = '📩 پیام جدید دریافت شد';
            toast.style.cssText = 'position:fixed;top:20px;right:20px;background:var(--dc-primary);color:#fff;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:700;z-index:999999;box-shadow:0 4px 14px rgba(0,0,0,.2);animation:dcToastIn .3s;';
            document.body.appendChild(toast);
            setTimeout(function(){ toast.remove(); }, 4000);
            // صدای کوتاه هشدار (اگه مرورگر اجازه بده)
            try {
                var ctx = new (window.AudioContext||window.webkitAudioContext)();
                var o = ctx.createOscillator(); var g = ctx.createGain();
                o.connect(g); g.connect(ctx.destination);
                o.frequency.value = 800; g.gain.value = 0.1;
                o.start(); setTimeout(function(){ o.stop(); }, 150);
            } catch(e){}
        }
        // هر ۱۵ ثانیه یک‌بار، بدون نیاز به رفرش دستی صفحه
        // اجرای فوری یک‌بار (نه فقط بعد از ۱۵ ثانیه اول) تا تیک خوانده‌شده و
        // علامت‌گذاری پیام‌ها بدون معطلی انجام بشه
        dcRefreshWorkspace();
        setInterval(dcRefreshWorkspace, 15000);

        // نکته: دکمه‌های دستی «تنظیم وضعیت» حذف شدن — وضعیت الان کاملاً
        // خودکار از سیستم واقعی حضور/ناهار/استراحت میاد.
        function dcToggleTask(id, done){
            var fd = new FormData();
            fd.append('action','dental_toggle_task');
            fd.append('_wpnonce', dcWpNonce);
            fd.append('task_id', id);
            fd.append('done', done ? 1 : 0);
            fetch(dcAjaxUrl, {method:'POST', body:fd}).then(dcRefreshWorkspace);
        }
        function dcAssignTask(){
            var title = document.getElementById('dc-new-task-title').value.trim();
            var to    = document.getElementById('dc-new-task-user').value;
            var due   = document.getElementById('dc-new-task-date') ? document.getElementById('dc-new-task-date').value : '';
            if(!title){ alert('عنوان کار را وارد کنید.'); return; }
            if(!to){ alert('یک نفر را برای محول‌کردن انتخاب کنید.'); return; }
            var fd = new FormData();
            fd.append('action','dental_assign_task');
            fd.append('_wpnonce', dcWpNonce);
            fd.append('title', title);
            fd.append('assigned_to', to);
            fd.append('due_date_jalali', due);
            fetch(dcAjaxUrl, {method:'POST', body:fd}).then(function(r){ return r.json(); }).then(function(res){
                if(res && res.success){
                    document.getElementById('dc-new-task-title').value = '';
                    document.getElementById('dc-new-task-date').value = '';
                    dcRefreshWorkspace();
                    // نکته: اگه کار به یکی دیگه محول شده باشه (نه خودتون)،
                    // توی چک‌لیست خودتون نشون داده نمی‌شه — طبیعیه، چون
                    // مال اون کاربره، نه شما.
                } else {
                    alert('❌ ثبت نشد. لطفاً دوباره تلاش کنید.');
                }
            }).catch(function(){
                alert('❌ خطا در ارتباط با سرور.');
            });
        }
        function dcSendMessage(){
            var to   = document.getElementById('dc-msg-to').value;
            var text = document.getElementById('dc-msg-text').value.trim();
            if(!text) return;
            var fd = new FormData();
            fd.append('action','dental_send_message');
            fd.append('_wpnonce', dcWpNonce);
            fd.append('to_user_id', to);
            fd.append('message', text);
            fetch(dcAjaxUrl, {method:'POST', body:fd}).then(function(){
                document.getElementById('dc-msg-text').value = '';
                dcRefreshWorkspace();
            });
        }

        var dcAttNonce = '<?php echo esc_js(wp_create_nonce("dental_attendance_ajax")); ?>';
        function dcClockQuick(action, cb){
            var fd = new FormData();
            fd.append('action','dental_'+action);
            fd.append('_wpnonce', dcAttNonce);
            fetch(dcAjaxUrl, {method:'POST', body:fd}).then(function(){
                if (typeof cb === 'function') cb(); else location.reload();
            });
        }
        function dcBreakQuick(action, type){
            var fd = new FormData();
            fd.append('action','dental_'+action);
            fd.append('_wpnonce', dcAttNonce);
            if(type) fd.append('break_type', type);
            fetch(dcAjaxUrl, {method:'POST', body:fd}).then(()=>location.reload());
        }
        </script>
        <?php
    }

    public function render_patients(): void {
        $action = sanitize_key($_GET['action'] ?? '');
        if ($action === 'profile') {
            (new Dental_Page_Patient_Profile())->render();
        } else {
            (new Dental_Page_Patients())->render();
        }
    }

    // ─── چک مطمئن نقش — همیشه بر اساس آرایه نقش‌ها، نه یه capability
    // رشته‌ای که ممکنه جا بیفته. این تابع جایگزین همه چک‌های قبلی شد ──
    private function user_has_role(array $allowed_roles): bool {
        if (current_user_can('manage_options')) return true;
        $cu = wp_get_current_user();
        return !empty(array_intersect((array)$cu->roles, $allowed_roles));
    }

    // ─── برای ثبت منو: اگه کاربر نقش مجاز رو داشته باشه 'read' (که همه
    // کاربران لاگین‌شده دارن) برمی‌گردونه، وگرنه 'do_not_allow' — یعنی
    // اون منو اصلاً توی سایدبار این کاربر نشون داده نمی‌شه. این با تابع
    // user_has_role فرق داره: اون برای «داخل صفحه رد کردن» بود، این برای
    // «از اول توی منو نشون ندادن» — که همون چیزیه که واقعاً لازمه.
    private function menu_cap(array $allowed_roles): string {
        return $this->user_has_role($allowed_roles) ? 'read' : 'do_not_allow';
    }

    // ─── نوار تب‌های بالای هاب‌های ترکیبی (مالی/بیمه/گزارش‌گیری) — یه
    // .dental-admin-wrap سبک‌وزن (min-height:0) که فقط عنوان+تب‌ها رو
    // نشون می‌ده؛ محتوای خودِ تب با render() اصلی هر صفحه (بدون هیچ
    // تغییری) زیرش میاد. قانون CSS «.dental-admin-wrap .dental-admin-wrap»
    // جلوی تکرار padding/min-height:100vh رو می‌گیره.
    private function render_hub_header(string $title, string $icon, array $tabs, string $page_slug, string $active_tab): void {
        ?>
        <div class="dental-admin-wrap" style="min-height:0;padding-bottom:0;">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <i data-lucide="<?php echo esc_attr($icon); ?>" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                <?php echo esc_html($title); ?>
            </h1>
            <?php if (count($tabs) > 1): ?>
            <div class="dc-tabs" style="margin-bottom:0;">
                <?php foreach ($tabs as $key => $t): ?>
                <a href="<?php echo esc_url(admin_url("admin.php?page={$page_slug}&section={$key}")); ?>"
                   class="dc-tab <?php echo $active_tab === $key ? 'active' : ''; ?>"><?php echo esc_html($t[0]); ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function render_financial(): void {
        // نکته: پارامتر این هاب عمداً "section" است، نه "tab" — چون
        // «دفتر روزانه» خودش داخلی‌اش از "tab" برای سه زیرتب خودش
        // (روزانه/در انتظار/گزارش‌ها) استفاده می‌کنه؛ اگه اینجا هم از
        // "tab" استفاده می‌شد، این دو با هم تداخل می‌کردن.
        $tabs = [
            'installments' => ['مالی و اقساط', ['dental_admin','dental_financial','dental_secretary','dental_doctor']],
        ];
        if (Dental_Features::enabled('ledger') && class_exists('Dental_Page_Ledger')) {
            $tabs['ledger'] = ['دفتر روزانه', ['dental_admin','dental_financial','dental_secretary']];
        }
        $allowed = array_filter($tabs, fn($t) => $this->user_has_role($t[1]));
        if (empty($allowed)) {
            wp_die('این صفحه فقط برای مدیر کلینیک، مسئول مالی، منشی، و پزشک قابل‌دسترسه.');
        }
        $section = sanitize_key($_GET['section'] ?? '');
        if (!isset($allowed[$section])) $section = array_key_first($allowed);

        $this->render_hub_header('مالی', 'credit-card', $allowed, 'dental-financial', $section);

        if ($section === 'ledger') {
            (new Dental_Page_Ledger())->render();
        } else {
            (new Dental_Page_Financial())->render();
        }
    }

    public function render_insurance_hub(): void {
        $tabs = [
            'policies'   => ['بیمه‌ها و تعرفه‌ها', ['dental_admin','dental_financial','dental_secretary']],
            'settlement' => ['تسویه با بیمه', ['dental_admin','dental_financial']],
        ];
        $allowed = array_filter($tabs, fn($t) => $this->user_has_role($t[1]));
        if (empty($allowed)) {
            wp_die('این صفحه فقط برای مدیر کلینیک، مسئول مالی، و منشی قابل‌دسترسه.');
        }
        $section = sanitize_key($_GET['section'] ?? '');
        if (!isset($allowed[$section])) $section = array_key_first($allowed);

        $this->render_hub_header('بیمه', 'shield-plus', $allowed, 'dental-insurance', $section);

        if ($section === 'settlement') {
            (new Dental_Page_Insurance_Settlement())->render();
        } else {
            (new Dental_Page_Insurance())->render();
        }
    }

    public function render_reports_hub(): void {
        $tabs = [
            'analytics'   => ['آمار و عملکرد (نموداری)', ['dental_admin']],
            // نکته: قبلاً از current_user_can('manage_dental') استفاده می‌شد
            // که احتمالاً به منشی/پذیرش هم داده شده بود — این گزارش درآمدیه
            // و فقط باید مدیر و پزشک (گزارش خودشون) ببینن.
            'performance' => ['گزارش درآمد و عملکرد', ['dental_admin','dental_doctor']],
            'doctors'     => ['کارکرد پزشکان', ['dental_admin']],
        ];
        if (Dental_Features::enabled('consent')) {
            $tabs['consent'] = ['رضایت‌نامه‌ها', ['dental_admin','dental_doctor','dental_secretary']];
        }
        $allowed = array_filter($tabs, fn($t) => $this->user_has_role($t[1]));
        if (empty($allowed)) {
            wp_die('این صفحه فقط برای مدیر کلینیک، پزشک، و منشی قابل‌دسترسه.');
        }
        $section = sanitize_key($_GET['section'] ?? '');
        if (!isset($allowed[$section])) $section = array_key_first($allowed);

        $this->render_hub_header('گزارش‌گیری', 'bar-chart-2', $allowed, 'dental-reports', $section);

        switch ($section) {
            case 'performance': (new Dental_Page_Performance_Report())->render(); break;
            case 'doctors':     (new Dental_Page_Doctor_Activity())->render(); break;
            case 'consent':     (new Dental_Page_Consent_Admin())->render(); break;
            default:            (new Dental_Page_Analytics())->render();
        }
    }

    public function render_sms_log(): void {
        if (!$this->user_has_role(['dental_admin'])) {
            wp_die('این بخش فقط برای مدیر کلینیک قابل‌دسترسه.');
        }
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
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="margin-bottom:24px;">📱 لاگ پیامک‌ها</h1>
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

    public function render_settings(): void {
        if (!$this->user_has_role(['dental_admin'])) {
            wp_die('این بخش فقط برای مدیر کلینیک قابل‌دسترسه.');
        }
        (new Dental_Page_Settings())->render();
    }

    // ─── چک‌لیست: تیک زدن/برداشتن ─────────────────────────────
    public function ajax_toggle_task(): void {
        check_ajax_referer('dental_workspace');
        $id   = (int)($_POST['task_id'] ?? 0);
        $done = !empty($_POST['done']);
        if ($id) Dental_Workspace_Manager::toggle_task($id, $done);
        wp_send_json_success();
    }

    // ─── چک‌لیست: محول‌کردن کار جدید (فقط مدیر) ────────────────
    public function ajax_assign_task(): void {
        check_ajax_referer('dental_workspace');
        // رفع باگ: کامنت بالا می‌گفت «فقط مدیر» ولی هیچ‌جا واقعاً چک نمی‌شد —
        // هر پرسنلی می‌تونست به هر کسی کار محول کنه.
        if (!$this->user_has_role(['dental_admin'])) wp_send_json_error(['message'=>'دسترسی مجاز نیست.']);
        $title = sanitize_text_field($_POST['title'] ?? '');
        $to    = (int)($_POST['assigned_to'] ?? 0);
        $due_j = sanitize_text_field($_POST['due_date_jalali'] ?? '');
        $due_g = $due_j ? (Dental_Jalali::to_gregorian($due_j) ?: '') : '';
        if (!$title || !$to) wp_send_json_error();
        Dental_Workspace_Manager::assign_task($title, $to, get_current_user_id(), $due_g);
        wp_send_json_success();
    }

    // ─── ارسال پیام داخلی ────────────────────────────────────
    public function ajax_send_message(): void {
        check_ajax_referer('dental_workspace');
        $to  = (int)($_POST['to_user_id'] ?? 0);
        $msg = sanitize_textarea_field($_POST['message'] ?? '');
        if (!$msg) wp_send_json_error();
        Dental_Workspace_Manager::send_message(get_current_user_id(), $to, $msg);
        wp_send_json_success();
    }

    public function ajax_mark_message_read(): void {
        check_ajax_referer('dental_workspace');
        $id = (int)($_POST['message_id'] ?? 0);
        if ($id) Dental_Workspace_Manager::mark_read($id, get_current_user_id());
        wp_send_json_success();
    }

    public function ajax_delete_message(): void {
        check_ajax_referer('dental_workspace');
        $id = (int)($_POST['message_id'] ?? 0);
        if (!$id) wp_send_json_error();
        $ok = Dental_Workspace_Manager::delete_message($id, get_current_user_id());
        if (!$ok) wp_send_json_error(['message' => 'فقط فرستنده می‌تونه پیامش رو حذف کنه.']);
        wp_send_json_success();
    }

    public function ajax_search_inventory_items(): void {
        check_ajax_referer('dental_workspace');
        if (!class_exists('Dental_Inventory_Manager')) wp_send_json_error();
        $q = sanitize_text_field($_POST['q'] ?? '');
        if (mb_strlen($q) < 2) wp_send_json_success(['items'=>[]]);
        $items = Dental_Inventory_Manager::search_items($q);
        $out = array_map(fn($it) => [
            'id' => (int)$it['id'], 'name' => $it['name'],
            'stock' => number_format((float)$it['current_stock'],1), 'unit' => $it['unit'],
        ], $items);
        wp_send_json_success(['items' => $out]);
    }

    public function ajax_search_inventory_items_cat(): void {
        check_ajax_referer('dental_workspace');
        if (!class_exists('Dental_Inventory_Manager')) wp_send_json_error();
        $q   = sanitize_text_field($_POST['q'] ?? '');
        $cat = (int)($_POST['cat'] ?? 0);
        if ($q === '' && !$cat) wp_send_json_success(['items'=>[]]);
        $items = Dental_Inventory_Manager::search_items_with_category($q, $cat);
        $out = array_map(fn($it) => [
            'id' => (int)$it['id'], 'name' => $it['name'],
            'stock' => number_format((float)$it['current_stock'],1), 'unit' => $it['unit'],
        ], $items);
        wp_send_json_success(['items' => $out]);
    }

    public function ajax_dismiss_changelog(): void {
        check_ajax_referer('dental_workspace');
        if (class_exists('Dental_Changelog')) Dental_Changelog::mark_seen(get_current_user_id());
        wp_send_json_success();
    }

    public function ajax_save_patient_insurance(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_financial','dental_secretary'])) wp_send_json_error(['message'=>'دسترسی مجاز نیست.']);
        if (!class_exists('Dental_Insurance_Manager')) wp_send_json_error();
        $patient_id = (int)($_POST['patient_id'] ?? 0);
        $insurance_id = (int)($_POST['insurance_id'] ?? 0);
        $policy = sanitize_text_field($_POST['policy_number'] ?? '');
        if (!$patient_id) wp_send_json_error();
        Dental_Insurance_Manager::save_patient_insurance($patient_id, $insurance_id, $policy);
        wp_send_json_success();
    }

    // ═══════════════ سیستم Imaging — استریم امن + آپلود + Annotation/Measurement ══
    // نکته امنیتی مهم: تصاویر پزشکی از یه مسیر خارج از uploads عمومی
    // (با .htaccess مسدود) استریم می‌شن، فقط بعد از چک اینکه کاربر
    // واقعاً به داده‌ی بالینی دسترسی داره — نه صرفاً یه لینک عمومی.
    public function maybe_stream_imaging_file(): void {
        if (!isset($_GET['dental_imaging_file'])) return;
        if (!is_user_logged_in()) wp_die('دسترسی مجاز نیست.', 403);
        // منشی به داده‌ی بالینی دسترسی نداره (همون قانونی که برای چارت/پزشکی هست)
        $cu = wp_get_current_user();
        if (in_array('dental_secretary', (array)$cu->roles) && !current_user_can('manage_options')) {
            wp_die('دسترسی مجاز نیست.', 403);
        }
        if (!$this->user_has_role(['dental_admin','dental_doctor','dental_assistant'])) {
            wp_die('دسترسی مجاز نیست.', 403);
        }
        Dental_Imaging_Manager::stream_file(sanitize_file_name($_GET['dental_imaging_file']));
    }

    public function ajax_imaging_upload(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_doctor','dental_assistant'])) wp_send_json_error(['message'=>'دسترسی مجاز نیست.']);

        $patient_id = (int)($_POST['patient_id'] ?? 0);
        if (!$patient_id || empty($_FILES['file'])) wp_send_json_error(['message'=>'اطلاعات ناقص است.']);

        $study_id = (int)($_POST['study_id'] ?? 0);
        if (!$study_id) {
            $study_id = Dental_Imaging_Manager::create_study([
                'patient_id'  => $patient_id,
                'doctor_id'   => (int)($_POST['doctor_id'] ?? get_current_user_id()),
                'study_date'  => Dental_Jalali::to_gregorian(sanitize_text_field($_POST['study_date'] ?? '')) ?: current_time('Y-m-d'),
                'modality'    => sanitize_key($_POST['modality'] ?? 'other'),
                'description' => sanitize_text_field($_POST['description'] ?? ''),
                'treatment_id'=> (int)($_POST['treatment_id'] ?? 0),
            ]);
        }

        $result = Dental_Imaging_Manager::add_image($study_id, $_FILES['file'], [
            'tooth_number'  => $_POST['tooth_number'] ?? null,
            'before_after'  => $_POST['before_after'] ?? null,
        ]);

        if (!$result['success']) wp_send_json_error($result);
        if (class_exists('Dental_Audit_Log')) {
            Dental_Audit_Log::log('patient_data_changed', "تصویر جدید (Imaging) برای بیمار #{$patient_id} آپلود شد", ['entity_type'=>'patient','entity_id'=>$patient_id]);
        }
        wp_send_json_success(['study_id' => $study_id, 'image_id' => $result['image_id']]);
    }

    public function ajax_imaging_save_annotation(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_doctor'])) wp_send_json_error();
        $image_id = (int)($_POST['image_id'] ?? 0);
        $type = sanitize_key($_POST['type'] ?? '');
        $data = json_decode(stripslashes($_POST['data'] ?? '{}'), true) ?: [];
        $color = sanitize_text_field($_POST['color'] ?? '#E05252');
        if (!$image_id || !$type) wp_send_json_error();
        $id = Dental_Imaging_Manager::save_annotation($image_id, $type, $data, $color);
        wp_send_json_success(['id' => $id]);
    }

    public function ajax_imaging_delete_annotation(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_doctor'])) wp_send_json_error();
        Dental_Imaging_Manager::delete_annotation((int)($_POST['id'] ?? 0));
        wp_send_json_success();
    }

    public function ajax_imaging_save_measurement(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_doctor'])) wp_send_json_error();
        $image_id = (int)($_POST['image_id'] ?? 0);
        $type = sanitize_key($_POST['type'] ?? '');
        $points = json_decode(stripslashes($_POST['points'] ?? '[]'), true) ?: [];
        $value = (float)($_POST['value'] ?? 0);
        if (!$image_id || !$type) wp_send_json_error();
        $id = Dental_Imaging_Manager::save_measurement($image_id, $type, $points, $value);
        wp_send_json_success(['id' => $id]);
    }

    public function ajax_imaging_delete_image(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_doctor'])) wp_send_json_error();
        Dental_Imaging_Manager::delete_image((int)($_POST['id'] ?? 0));
        wp_send_json_success();
    }

    public function ajax_imaging_get_meta(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_doctor','dental_assistant'])) wp_send_json_error();
        $image_id = (int)($_GET['image_id'] ?? 0);
        $img = Dental_Imaging_Manager::get_image($image_id);
        if (!$img) wp_send_json_error();
        $study = $GLOBALS['wpdb']->get_row($GLOBALS['wpdb']->prepare(
            "SELECT modality, study_date FROM {$GLOBALS['wpdb']->prefix}dental_imaging_studies WHERE id=%d", $img['study_id']
        ), ARRAY_A);
        wp_send_json_success([
            'file_path'   => $img['file_path'],
            'tooth_number'=> $img['tooth_number'],
            'modality'    => $study['modality'] ?? '',
            'study_date'  => $study['study_date'] ?? '',
            'annotations' => Dental_Imaging_Manager::get_annotations($image_id),
        ]);
    }

    public function ajax_insurance_upload_doc(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_doctor','dental_secretary','dental_financial'])) wp_send_json_error(['message'=>'دسترسی مجاز نیست.']);
        $treatment_id = (int)($_POST['treatment_id'] ?? 0);
        $doc_type = sanitize_key($_POST['doc_type'] ?? '');
        if (!$treatment_id || !$doc_type || empty($_FILES['file'])) wp_send_json_error(['message'=>'اطلاعات ناقص است.']);
        $result = Dental_Insurance_Manager::upload_doc($treatment_id, $doc_type, $_FILES['file']);
        if (!$result['success']) wp_send_json_error($result);
        if (class_exists('Dental_Audit_Log')) {
            Dental_Audit_Log::log('patient_data_changed', "مدرک بیمه‌ای «{$doc_type}» برای رکورد درمان #{$treatment_id} آپلود شد",
                ['entity_type'=>'treatment','entity_id'=>$treatment_id]);
        }
        wp_send_json_success($result);
    }

    public function ajax_search_drugs(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_doctor'])) wp_send_json_error();
        $q = sanitize_text_field($_POST['q'] ?? '');
        wp_send_json_success(['drugs' => Dental_Prescription_Manager::search_drugs($q)]);
    }

    public function ajax_create_pathway(): void {
        check_ajax_referer('dental_workspace');
        if (!class_exists('Dental_Pathway_Manager') || !Dental_Pathway_Manager::is_enabled()) wp_send_json_error(['message'=>'این قابلیت فعال نیست.']);
        if (!$this->user_has_role(['dental_admin','dental_doctor'])) wp_send_json_error(['message'=>'دسترسی مجاز نیست.']);
        $patient_id = (int)($_POST['patient_id'] ?? 0);
        $template_id = (int)($_POST['template_id'] ?? 0);
        $tooth = !empty($_POST['tooth_number']) ? (int)$_POST['tooth_number'] : null;
        if (!$patient_id || !$template_id) wp_send_json_error(['message'=>'اطلاعات ناقص است.']);
        $pathway_id = Dental_Pathway_Manager::create_pathway_from_template($template_id, $patient_id, $tooth, get_current_user_id());
        if (!$pathway_id) wp_send_json_error(['message'=>'قالب یافت نشد.']);
        wp_send_json_success(['pathway_id' => $pathway_id]);
    }

    public function ajax_update_pathway_step(): void {
        check_ajax_referer('dental_workspace');
        if (!class_exists('Dental_Pathway_Manager') || !Dental_Pathway_Manager::is_enabled()) wp_send_json_error(['message'=>'این قابلیت فعال نیست.']);
        if (!$this->user_has_role(['dental_admin','dental_doctor'])) wp_send_json_error(['message'=>'دسترسی مجاز نیست.']);
        $step_id = (int)($_POST['step_id'] ?? 0);
        $status = sanitize_key($_POST['status'] ?? '');
        $override = !empty($_POST['override']);
        if (!$step_id || !$status) wp_send_json_error(['message'=>'اطلاعات ناقص است.']);
        $result = Dental_Pathway_Manager::update_step_status($step_id, $status, ['override' => $override]);
        if (!$result['success']) { wp_send_json_error($result); return; }
        wp_send_json_success();
    }

    // ─── تکمیل مرحله همراه با اتصال به کاتالوگ — حلقه‌ی جاافتاده‌ای
    // که وصلش کردیم: اینجا خودکار record_treatment() صدا زده می‌شه،
    // یعنی رکورد «در انتظار تأیید مالی» هم خودکار توی دفتر روزانه ساخته می‌شه ──
    public function ajax_complete_pathway_step_with_catalog(): void {
        check_ajax_referer('dental_workspace');
        if (!class_exists('Dental_Pathway_Manager') || !Dental_Pathway_Manager::is_enabled()) wp_send_json_error(['message'=>'این قابلیت فعال نیست.']);
        if (!$this->user_has_role(['dental_admin','dental_doctor'])) wp_send_json_error(['message'=>'دسترسی مجاز نیست.']);
        $step_id = (int)($_POST['step_id'] ?? 0);
        $catalog_id = (int)($_POST['catalog_id'] ?? 0);
        if (!$step_id || !$catalog_id) wp_send_json_error(['message'=>'اطلاعات ناقص است.']);
        $result = Dental_Pathway_Manager::complete_step_with_catalog($step_id, $catalog_id, get_current_user_id());
        if (!$result['success']) { wp_send_json_error($result); return; }
        wp_send_json_success($result);
    }

    public function ajax_save_endodontic_details(): void {
        check_ajax_referer('dental_workspace');
        if (!class_exists('Dental_Endodontic_Manager') || !Dental_Endodontic_Manager::is_enabled()) wp_send_json_error(['message'=>'این قابلیت فعال نیست.']);
        if (!$this->user_has_role(['dental_admin','dental_doctor'])) wp_send_json_error(['message'=>'دسترسی مجاز نیست.']);
        $treatment_id = (int)($_POST['treatment_id'] ?? 0);
        $patient_id = (int)($_POST['patient_id'] ?? 0);
        $tooth = !empty($_POST['tooth_number']) ? (int)$_POST['tooth_number'] : null;
        if (!$treatment_id || !$patient_id) wp_send_json_error(['message'=>'اطلاعات ناقص است.']);
        $canals = $_POST['canals'] ?? [];
        $result = Dental_Endodontic_Manager::save($treatment_id, $patient_id, $tooth, $_POST, is_array($canals) ? $canals : []);
        if (!$result) wp_send_json_error(['message'=>'فقط پزشکی که خودش این خدمت رو انجام داده (یا مدیر کلینیک) می‌تونه جزئیاتش رو ثبت/اصلاح کنه.']);
        wp_send_json_success();
    }

    public function ajax_get_endodontic_details(): void {
        check_ajax_referer('dental_workspace');
        if (!class_exists('Dental_Endodontic_Manager') || !Dental_Endodontic_Manager::is_enabled()) wp_send_json_error();
        // رفع باگ: نسخه‌ی «ذخیره» همین جزئیات (ajax_save_endodontic_details)
        // درست محدود به admin/doctor بود، ولی این نسخه‌ی «خواندن» هیچ چکی نداشت.
        if (!$this->user_has_role(['dental_admin','dental_doctor'])) wp_send_json_error(['message'=>'دسترسی مجاز نیست.']);
        $treatment_id = (int)($_POST['treatment_id'] ?? 0);
        wp_send_json_success(['detail' => Dental_Endodontic_Manager::get_by_treatment($treatment_id)]);
    }

    // ─── ذخیره‌ی نسخه — برگشتیم به همون الگوی AJAX ساده و مطمئنی که
    // همه‌جای پلاگین جواب داده (نه admin-post.php که مشکل‌ساز شد).
    // خروجیش فقط یه رشته‌آدرس GET برای پرینته — همون مکانیزم اثبات‌شده‌ای
    // که پرینت‌مجدد نسخه‌های قدیمی توی خلاصه‌ی پرونده ازش استفاده می‌کنه ──
    public function ajax_save_prescription(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_doctor'])) wp_send_json_error(['message'=>'دسترسی مجاز نیست.']);
        $patient_id = (int)($_POST['patient_id'] ?? 0);
        $raw_items = $_POST['items'] ?? [];
        $items = [];
        foreach ($raw_items as $it) {
            if (empty($it['drug_name']) && empty($it['drug_id'])) continue;
            $items[] = [
                'drug_id'       => (int)($it['drug_id'] ?? 0),
                'drug_name'     => sanitize_text_field($it['drug_name'] ?? ''),
                'quantity'      => (float)($it['quantity'] ?? 1),
                'quantity_unit' => sanitize_text_field($it['quantity_unit'] ?? 'عدد'),
                'duration_days' => (int)($it['duration_days'] ?? 1),
                'frequency'     => sanitize_text_field($it['frequency'] ?? ''),
                'timing_note'   => sanitize_text_field($it['timing_note'] ?? ''),
                'extra_note'    => sanitize_text_field($it['extra_note'] ?? ''),
            ];
        }
        if (!$patient_id || empty($items)) wp_send_json_error(['message'=>'حداقل یه دارو انتخاب کنید.']);

        $rx_id = Dental_Prescription_Manager::create_prescription([
            'patient_id'      => $patient_id,
            'doctor_id'       => get_current_user_id(),
            'prescribed_date' => current_time('Y-m-d'),
            'notes'           => sanitize_textarea_field($_POST['notes'] ?? ''),
        ], $items);

        if (!$rx_id) wp_send_json_error(['message'=>'ذخیره ناموفق بود.']);

        if (class_exists('Dental_Audit_Log')) {
            Dental_Audit_Log::log('patient_data_changed', "نسخه‌ی جدید برای بیمار #{$patient_id} ثبت شد (" . count($items) . " دارو)",
                ['entity_type'=>'patient','entity_id'=>$patient_id]);
        }

        $print_url = wp_nonce_url(admin_url('admin.php?page=dental-dashboard&print_prescription=1&rx_id='.$rx_id), 'dental_print_rx');
        wp_send_json_success(['rx_id' => $rx_id, 'print_url' => $print_url]);
    }

    public function handle_save_and_print_rx(): void {
        // ─── رفع باگ «باید رفرش بشه تا فاکتور بیاد»: چون admin-post.php
        // هم مثل بقیه از admin_init رد می‌شه، ممکنه هوک‌های دیگه‌ی این
        // پلاگین (seed دارو، دانلود بکاپ و...) قبل از این تابع اجرا شده
        // باشن و یه خروجی کوچیک (حتی یه Warning وردپرسی) تولید کرده
        // باشن — که باعث «headers already sent» می‌شد. این خط هر بافر
        // باقی‌مونده رو پاک می‌کنه تا این پاسخ از صفر و تمیز شروع بشه.
        while (ob_get_level() > 0) { ob_end_clean(); }
        check_admin_referer('dental_save_rx');
        if (!$this->user_has_role(['dental_admin','dental_doctor'])) wp_die('دسترسی مجاز نیست.');
        $patient_id = (int)($_POST['patient_id'] ?? 0);
        $raw_items = $_POST['items'] ?? [];
        $items = [];
        foreach ($raw_items as $it) {
            if (empty($it['drug_name']) && empty($it['drug_id'])) continue;
            $items[] = [
                'drug_id'       => (int)($it['drug_id'] ?? 0),
                'drug_name'     => sanitize_text_field($it['drug_name'] ?? ''),
                'quantity'      => (float)($it['quantity'] ?? 1),
                'quantity_unit' => sanitize_text_field($it['quantity_unit'] ?? 'عدد'),
                'duration_days' => (int)($it['duration_days'] ?? 1),
                'frequency'     => sanitize_text_field($it['frequency'] ?? ''),
                'timing_note'   => sanitize_text_field($it['timing_note'] ?? ''),
                'extra_note'    => sanitize_text_field($it['extra_note'] ?? ''),
            ];
        }
        if (!$patient_id || empty($items)) wp_die('اطلاعات ناقص است — حداقل یه دارو انتخاب کنید.');

        $rx_id = Dental_Prescription_Manager::create_prescription([
            'patient_id'      => $patient_id,
            'doctor_id'       => get_current_user_id(),
            'prescribed_date' => current_time('Y-m-d'),
            'notes'           => sanitize_textarea_field($_POST['notes'] ?? ''),
        ], $items);

        if (class_exists('Dental_Audit_Log')) {
            Dental_Audit_Log::log('patient_data_changed', "نسخه‌ی جدید برای بیمار #{$patient_id} ثبت شد (" . count($items) . " دارو)",
                ['entity_type'=>'patient','entity_id'=>$patient_id]);
        }

        // ─── مستقیم همین‌جا، توی همین درخواست، خروجی پرینت رو نشون
        // می‌دیم — نه ریدایرکت، نه AJAX جدا، نه window.open() جاوااسکریپتی.
        // چون خودِ فرم HTML اصلی target="_blank" داشت، این پاسخ مستقیم
        // توی همون تب جدید (که مرورگر خودش، نه JS، باز کرده) نمایش
        // داده می‌شه — قابل‌اعتمادترین روش ممکن.
        $rx = Dental_Prescription_Manager::get_prescription($rx_id);
        $this->render_prescription_print($rx);
        exit;
    }

    public function maybe_print_prescription(): void {
        if (!isset($_GET['page']) || $_GET['page'] !== 'dental-dashboard' || !isset($_GET['print_prescription'])) return;
        while (ob_get_level() > 0) { ob_end_clean(); } // همون رفع «باید رفرش بشه»
        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'dental_print_rx')) wp_die('لینک نامعتبر است.');
        if (!$this->user_has_role(['dental_admin','dental_doctor','dental_secretary'])) wp_die('دسترسی مجاز نیست.');
        $rx_id = (int)($_GET['rx_id'] ?? 0);
        $rx = Dental_Prescription_Manager::get_prescription($rx_id);
        if (!$rx) wp_die('نسخه یافت نشد.');
        $this->render_prescription_print($rx);
        exit;
    }

    private function render_prescription_print(array $rx): void {
        $clinic_name = get_option('dental_clinic_name', get_bloginfo('name'));
        $clinic_address = get_option('dental_clinic_address', '');
        $national_id = get_post_meta((int)$rx['patient_id'], '_patient_national_id', true);
        ?>
        <!DOCTYPE html>
        <html dir="rtl" lang="fa">
        <head>
        <meta charset="UTF-8">
        <title>نسخه — <?php echo esc_html($rx['patient_name']); ?></title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap');
            body { font-family:'Vazirmatn',Tahoma,sans-serif; padding:30px; color:#1A2733; }
            h1 { font-size:18px; border-bottom:2px solid #1A6B8A; padding-bottom:10px; display:flex; justify-content:space-between; align-items:center; }
            .box { background:#F8FAFB; border-radius:8px; padding:14px 18px; margin-bottom:20px; }
            .row { display:flex; justify-content:space-between; padding:4px 0; font-size:13px; }
            table.rx { width:100%; border-collapse:collapse; margin-bottom:24px; }
            table.rx th, table.rx td { border:1px solid #DDE5EB; padding:10px; text-align:right; font-size:13px; }
            table.rx th { background:#F0F6F9; }
            .sign-area { display:flex; justify-content:space-between; margin-top:60px; }
            .sign-box { text-align:center; width:220px; }
            .sign-line { border-top:1px solid #999; padding-top:8px; margin-top:60px; font-size:12px; color:#666; }
            @media print { .no-print { display:none; } }
        </style>
        </head>
        <body onload="window.print()">
            <h1>
                <span>🦷 <?php echo esc_html($clinic_name); ?></span>
                <span style="font-size:13px;font-weight:400;color:#7A96A4;">نسخه‌ی دارویی</span>
            </h1>
            <?php if ($clinic_address): ?><div style="font-size:11px;color:#A0B4C0;margin-top:-8px;margin-bottom:14px;"><?php echo esc_html($clinic_address); ?></div><?php endif; ?>
            <div class="box">
                <div class="row"><span>نام بیمار</span><b><?php echo esc_html($rx['patient_name']); ?></b></div>
                <div class="row"><span>کد ملی</span><b style="direction:ltr;display:inline-block;"><?php echo esc_html($national_id ?: '—'); ?></b></div>
                <div class="row"><span>پزشک معالج</span><b><?php echo esc_html($rx['doctor_name']); ?></b></div>
                <div class="row"><span>تاریخ</span><b><?php echo esc_html(Dental_Jalali::to_jalali($rx['prescribed_date'],'Y/m/d')); ?></b></div>
            </div>

            <table class="rx">
                <thead><tr><th>#</th><th>دارو</th><th>تعداد</th><th>دفعات مصرف</th><th>مدت</th><th>زمان مصرف</th><th>توضیح</th></tr></thead>
                <tbody>
                <?php foreach($rx['items'] as $i => $item): ?>
                <tr>
                    <td><?php echo $i+1; ?></td>
                    <td style="font-weight:700;">
                        <?php echo esc_html($item['drug_name_snapshot']); ?>
                        <?php if (!empty($item['english_name_snapshot'])): ?>
                        <div style="font-size:11px;font-weight:400;color:#7A96A4;direction:ltr;text-align:right;"><?php echo esc_html($item['english_name_snapshot']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html(Dental_Prescription_Manager::format_qty($item['quantity']) . ' ' . $item['quantity_unit']); ?></td>
                    <td><?php echo esc_html($item['frequency']); ?></td>
                    <td><?php echo esc_html($item['duration_days']); ?> روز</td>
                    <td><?php echo esc_html($item['timing_note']); ?></td>
                    <td style="font-size:11px;color:#7A96A4;"><?php echo esc_html($item['extra_note']); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($rx['notes']): ?>
            <div class="box"><b>توضیح کلی:</b> <?php echo esc_html($rx['notes']); ?></div>
            <?php endif; ?>

            <div class="sign-area">
                <div class="sign-box"></div>
                <div class="sign-box">
                    <div class="sign-line">مهر و امضای پزشک</div>
                </div>
            </div>

            <div class="no-print" style="margin-top:20px;">
                <button onclick="window.print()">🖨️ چاپ</button>
            </div>
        </body>
        </html>
        <?php
    }


    public function ajax_get_insurance_checklist(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_doctor','dental_secretary','dental_financial'])) wp_send_json_error();
        $treatment_id = (int)($_POST['treatment_id'] ?? 0);
        if (!$treatment_id) wp_send_json_error();
        wp_send_json_success(['checklist' => Dental_Insurance_Manager::get_doc_checklist($treatment_id)]);
    }

    // ─── چاپ/PDF بسته‌ی مدارک بیمه — فاکتور + همه‌ی مدارک آپلودشده،
    // آماده برای دست بیمار/بیمه ─────────────────────────────────────
    public function maybe_print_insurance_docs(): void {
        if (!isset($_GET['page']) || $_GET['page'] !== 'dental-dashboard' || !isset($_GET['print_insurance_docs'])) return;
        while (ob_get_level() > 0) { ob_end_clean(); } // رفع «باید رفرش بشه تا پرینت بیاد»
        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'dental_print_ins_docs')) wp_die('لینک نامعتبر است.');
        if (!$this->user_has_role(['dental_admin','dental_doctor','dental_secretary','dental_financial'])) wp_die('دسترسی مجاز نیست.');
        $treatment_id = (int)($_GET['treatment_id'] ?? 0);
        if (!$treatment_id) wp_die('رکورد نامعتبر.');
        $this->render_insurance_docs_print($treatment_id);
        exit;
    }

    private function render_insurance_docs_print(int $treatment_id): void {
        global $wpdb;
        $t = $wpdb->get_row($wpdb->prepare(
            "SELECT t.*, p.post_title as patient_name, u.display_name as doctor_name, i.name as insurance_name
             FROM {$wpdb->prefix}dental_catalog_treatments t
             LEFT JOIN {$wpdb->posts} p ON t.patient_id=p.ID
             LEFT JOIN {$wpdb->users} u ON t.doctor_id=u.ID
             LEFT JOIN {$wpdb->prefix}dental_insurance_companies i ON t.insurance_id=i.id
             WHERE t.id=%d", $treatment_id
        ), ARRAY_A);
        if (!$t) wp_die('یافت نشد.');
        $checklist = Dental_Insurance_Manager::get_doc_checklist($treatment_id);
        $national_id = get_post_meta((int)$t['patient_id'], '_patient_national_id', true);
        $clinic_name = get_option('dental_clinic_name', get_bloginfo('name'));
        $clinic_address = get_option('dental_clinic_address', '');
        ?>
        <!DOCTYPE html>
        <html dir="rtl" lang="fa">
        <head>
        <meta charset="UTF-8">
        <title>مدارک بیمه — <?php echo esc_html($t['patient_name']); ?></title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap');
            body { font-family:'Vazirmatn',Tahoma,sans-serif; padding:30px; color:#1A2733; }
            h1 { font-size:18px; border-bottom:2px solid #1A6B8A; padding-bottom:10px; }
            .box { background:#F8FAFB; border-radius:8px; padding:14px 18px; margin-bottom:16px; }
            .row { display:flex; justify-content:space-between; padding:4px 0; font-size:13px; }
            .doc-item { border:1px solid #DDE5EB; border-radius:8px; padding:12px; margin-bottom:12px; page-break-inside:avoid; }
            .doc-item img { max-width:100%; max-height:400px; display:block; margin-top:8px; border-radius:6px; }
            .missing { color:#E05252; font-weight:700; }
            .present { color:#2ECC9A; font-weight:700; }
            @media print { .no-print { display:none; } }
        </style>
        </head>
        <body onload="window.print()">
            <h1>🦷 <?php echo esc_html($clinic_name); ?> — مدارک ارسالی به بیمه</h1>
            <?php if ($clinic_address): ?><div style="font-size:11px;color:#A0B4C0;margin-bottom:14px;"><?php echo esc_html($clinic_address); ?></div><?php endif; ?>
            <div class="box">
                <div class="row"><span>نام بیمار</span><b><?php echo esc_html($t['patient_name']); ?></b></div>
                <div class="row"><span>کد ملی</span><b style="direction:ltr;display:inline-block;"><?php echo esc_html($national_id ?: '—'); ?></b></div>
                <div class="row"><span>خدمت</span><b><?php echo esc_html($t['service_name']); ?></b></div>
                <?php if ($t['tooth_number']): ?><div class="row"><span>دندان</span><b><?php echo esc_html(Dental_Service_Catalog::describe_tooth_number((int)$t['tooth_number'])); ?></b></div><?php endif; ?>
                <div class="row"><span>پزشک معالج</span><b><?php echo esc_html($t['doctor_name']); ?></b></div>
                <div class="row"><span>بیمه</span><b><?php echo esc_html($t['insurance_name'] ?: '—'); ?></b></div>
                <div class="row"><span>تاریخ</span><b><?php echo esc_html(Dental_Jalali::to_jalali($t['recorded_date'],'Y/m/d')); ?></b></div>
            </div>

            <h2 style="font-size:14px;">📋 چک‌لیست مدارک</h2>
            <?php foreach($checklist as $doc): ?>
            <div class="doc-item">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <b><?php echo esc_html($doc['label']); ?></b>
                    <span class="<?php echo $doc['uploaded']?'present':'missing'; ?>"><?php echo $doc['uploaded']?'✅ ضمیمه شد':'❌ ناقص'; ?></span>
                </div>
                <?php if ($doc['uploaded'] && $doc['file_path']):
                    $ext = pathinfo($doc['file_path'], PATHINFO_EXTENSION);
                    $file_url = add_query_arg('dental_imaging_file', $doc['file_path'], admin_url());
                    if (in_array(strtolower($ext), ['jpg','jpeg','png','webp'])):
                ?>
                <img src="<?php echo esc_url($file_url); ?>">
                <?php else: ?>
                <a href="<?php echo esc_url($file_url); ?>" target="_blank">📎 مشاهده فایل PDF</a>
                <?php endif; endif; ?>
            </div>
            <?php endforeach; ?>

            <div class="no-print" style="margin-top:20px;">
                <button onclick="window.print()">🖨️ چاپ</button>
            </div>
        </body>
        </html>
        <?php
    }

    // ─── بسته‌ی کامل یه درخواست تسویه — لیست همه‌ی بیمارهای اون دوره
    // + مدارک هرکدوم، همه یکجا، آماده برای ارسال/تحویل به کارشناس بیمه ──
    public function maybe_print_settlement_bundle(): void {
        if (!isset($_GET['page']) || $_GET['page'] !== 'dental-dashboard' || !isset($_GET['print_settlement_bundle'])) return;
        while (ob_get_level() > 0) { ob_end_clean(); } // رفع «باید رفرش بشه تا پرینت بیاد»
        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'dental_print_settlement_bundle')) wp_die('لینک نامعتبر است.');
        if (!$this->user_has_role(['dental_admin','dental_financial'])) wp_die('دسترسی مجاز نیست.');
        $settlement_id = (int)($_GET['settlement_id'] ?? 0);
        if (!$settlement_id) wp_die('رکورد نامعتبر.');
        $this->render_settlement_bundle_print($settlement_id);
        exit;
    }

    private function render_settlement_bundle_print(int $settlement_id): void {
        global $wpdb;
        $s = $wpdb->get_row($wpdb->prepare(
            "SELECT s.*, i.name as insurance_name FROM {$wpdb->prefix}dental_insurance_settlements s
             LEFT JOIN {$wpdb->prefix}dental_insurance_companies i ON s.insurance_id=i.id WHERE s.id=%d", $settlement_id
        ), ARRAY_A);
        if (!$s) wp_die('یافت نشد.');
        $items = Dental_Insurance_Manager::get_settlement_details($settlement_id);
        $clinic_name = get_option('dental_clinic_name', get_bloginfo('name'));
        $clinic_address = get_option('dental_clinic_address', '');
        ?>
        <!DOCTYPE html>
        <html dir="rtl" lang="fa">
        <head>
        <meta charset="UTF-8">
        <title>بسته تسویه #<?php echo $settlement_id; ?> — <?php echo esc_html($s['insurance_name']); ?></title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap');
            body { font-family:'Vazirmatn',Tahoma,sans-serif; padding:30px; color:#1A2733; }
            h1 { font-size:18px; border-bottom:2px solid #1A6B8A; padding-bottom:10px; }
            .box { background:#F8FAFB; border-radius:8px; padding:14px 18px; margin-bottom:20px; }
            .row { display:flex; justify-content:space-between; padding:4px 0; font-size:13px; }
            table.list { width:100%; border-collapse:collapse; margin-bottom:24px; font-size:12px; }
            table.list th, table.list td { border:1px solid #DDE5EB; padding:8px; text-align:right; }
            table.list th { background:#F0F6F9; }
            .patient-section { border:2px solid #1A6B8A; border-radius:10px; padding:16px; margin-bottom:20px; page-break-inside:avoid; page-break-before:always; }
            .doc-item { border:1px solid #DDE5EB; border-radius:8px; padding:10px; margin-bottom:10px; page-break-inside:avoid; }
            .doc-item img { max-width:100%; max-height:380px; display:block; margin-top:8px; border-radius:6px; }
            .missing { color:#E05252; font-weight:700; }
            .present { color:#2ECC9A; font-weight:700; }
            @media print { .no-print { display:none; } }
        </style>
        </head>
        <body onload="window.print()">
            <h1>🦷 <?php echo esc_html($clinic_name); ?> — بسته‌ی تسویه با بیمه</h1>
            <?php if ($clinic_address): ?><div style="font-size:11px;color:#A0B4C0;margin-bottom:14px;"><?php echo esc_html($clinic_address); ?></div><?php endif; ?>
            <div class="box">
                <div class="row"><span>بیمه</span><b><?php echo esc_html($s['insurance_name']); ?></b></div>
                <div class="row"><span>بازه</span><b><?php echo esc_html(Dental_Jalali::to_jalali($s['period_from'],'Y/m/d')); ?> تا <?php echo esc_html(Dental_Jalali::to_jalali($s['period_to'],'Y/m/d')); ?></b></div>
                <div class="row"><span>تعداد خدمات</span><b><?php echo (int)$s['treatment_count']; ?></b></div>
                <div class="row"><span>مبلغ کل درخواستی</span><b><?php echo number_format($s['total_amount']); ?> تومان</b></div>
            </div>

            <h2 style="font-size:14px;">📋 لیست خلاصه</h2>
            <table class="list">
                <thead><tr><th>#</th><th>بیمار</th><th>خدمت</th><th>پزشک</th><th>تاریخ</th><th>سهم بیمه</th></tr></thead>
                <tbody>
                <?php foreach($items as $idx => $t): ?>
                <tr>
                    <td><?php echo $idx+1; ?></td>
                    <td><?php echo esc_html($t['patient_name']); ?></td>
                    <td><?php echo esc_html($t['service_name']); ?></td>
                    <td><?php echo esc_html($t['doctor_name']); ?></td>
                    <td><?php echo esc_html(Dental_Jalali::to_jalali($t['recorded_date'],'Y/m/d')); ?></td>
                    <td><?php echo number_format($t['insurance_share']); ?> ت</td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <h2 style="font-size:14px;">📎 مدارک تفصیلی هر بیمار</h2>
            <?php foreach($items as $idx => $t):
                $checklist = Dental_Insurance_Manager::get_doc_checklist((int)$t['id']);
                $national_id = get_post_meta((int)$t['patient_id'], '_patient_national_id', true);
            ?>
            <div class="patient-section">
                <div style="font-weight:700;font-size:14px;margin-bottom:8px;color:#1A6B8A;">
                    <?php echo $idx+1; ?>. <?php echo esc_html($t['patient_name']); ?>
                    <span style="font-size:11px;color:#7A96A4;font-weight:400;"> — کد ملی: <?php echo esc_html($national_id ?: '—'); ?></span>
                </div>
                <div style="font-size:12px;color:#5A7080;margin-bottom:10px;">
                    <?php echo esc_html($t['service_name']); ?>
                    <?php if($t['tooth_number']): ?> — دندان <?php echo esc_html(Dental_Service_Catalog::describe_tooth_number((int)$t['tooth_number'])); ?><?php endif; ?>
                    — <?php echo esc_html(Dental_Jalali::to_jalali($t['recorded_date'],'Y/m/d')); ?>
                </div>
                <?php if (empty($checklist)): ?>
                <p style="font-size:12px;color:#A0B4C0;">مدرک خاصی برای این خدمت لازم نبوده</p>
                <?php else: foreach($checklist as $doc): ?>
                <div class="doc-item">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <b style="font-size:12px;"><?php echo esc_html($doc['label']); ?></b>
                        <span class="<?php echo $doc['uploaded']?'present':'missing'; ?>" style="font-size:11px;"><?php echo $doc['uploaded']?'✅':'❌ ناقص'; ?></span>
                    </div>
                    <?php if ($doc['uploaded'] && $doc['file_path']):
                        $ext = pathinfo($doc['file_path'], PATHINFO_EXTENSION);
                        $file_url = add_query_arg('dental_imaging_file', $doc['file_path'], admin_url());
                        if (in_array(strtolower($ext), ['jpg','jpeg','png','webp'])):
                    ?>
                    <img src="<?php echo esc_url($file_url); ?>">
                    <?php else: ?>
                    <a href="<?php echo esc_url($file_url); ?>" target="_blank" style="font-size:11px;">📎 مشاهده فایل PDF</a>
                    <?php endif; endif; ?>
                </div>
                <?php endforeach; endif; ?>
            </div>
            <?php endforeach; ?>

            <div class="no-print" style="margin-top:20px;">
                <button onclick="window.print()">🖨️ چاپ / ذخیره PDF</button>
            </div>
        </body>
        </html>
        <?php
    }

    // ─── جزئیات خدمات یه پزشک خاص، توی یه بازه — برای نمایش AJAX (بدون
    // رفرش صفحه) وقتی روی اسم پزشک توی گزارش‌ها کلیک می‌شه ────────────
    public function ajax_doctor_report_details(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_financial','dental_secretary'])) wp_send_json_error();
        $doctor_id = (int)($_POST['doctor_id'] ?? 0);
        $from = sanitize_text_field($_POST['from'] ?? date('Y-m-d', strtotime('-30 days')));
        $to   = sanitize_text_field($_POST['to'] ?? current_time('Y-m-d'));
        if (!$doctor_id || !class_exists('Dental_Service_Catalog')) wp_send_json_error();

        $details = Dental_Service_Catalog::get_performance_report($from, $to, $doctor_id);
        ob_start();
        if (empty($details)) {
            echo '<p style="text-align:center;color:var(--dc-neutral-400);padding:16px;">خدمتی در این بازه ثبت نشده</p>';
        } else {
            echo '<table class="dc-table"><thead><tr><th>تاریخ</th><th>بیمار</th><th>خدمت</th><th>دندان</th><th>مبلغ</th><th>تخفیف</th><th>وضعیت</th></tr></thead><tbody>';
            foreach ($details as $d) {
                $status_label = $d['ledger_status'] === 'confirmed' ? '✅ تأییدشده' : '⏳ در انتظار';
                $discount = (float)($d['ledger_discount'] ?? 0);
                printf(
                    '<tr><td style="font-size:11px;">%s</td><td style="font-size:12px;font-weight:600;">%s</td><td style="font-size:12px;">%s</td><td style="font-size:11px;">%s</td><td style="font-weight:700;">%s</td><td style="color:%s;">%s</td><td style="font-size:11px;">%s</td></tr>',
                    esc_html(Dental_Jalali::to_jalali($d['recorded_date'],'Y/m/d')),
                    esc_html($d['patient_name']),
                    esc_html($d['service_name']),
                    esc_html(Dental_Service_Catalog::describe_tooth_number($d['tooth_number']?:null)),
                    number_format($d['price']),
                    $discount>0?'var(--dc-danger)':'var(--dc-neutral-400)',
                    $discount>0?'−'.number_format($discount):'—',
                    esc_html($status_label)
                );
            }
            echo '</tbody></table>';
        }
        $html = ob_get_clean();
        wp_send_json_success(['html' => $html]);
    }

    public function ajax_inventory_quick_update(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin','dental_financial'])) wp_send_json_error();
        if (!class_exists('Dental_Inventory_Manager')) wp_send_json_error();
        $id = (int)($_POST['item_id'] ?? 0);
        if (!$id) wp_send_json_error();
        $ok = Dental_Inventory_Manager::quick_update($id, [
            'name'          => $_POST['name'] ?? '',
            'category_id'   => $_POST['category_id'] ?? 0,
            'unit'          => $_POST['unit'] ?? '',
            'current_stock' => $_POST['current_stock'] ?? 0,
            'min_stock'     => $_POST['min_stock'] ?? 0,
            'unit_cost'     => $_POST['unit_cost'] ?? 0,
        ]);
        $ok ? wp_send_json_success() : wp_send_json_error();
    }

    public function ajax_cycle_rota(): void {
        check_ajax_referer('dental_workspace');
        // برنامه ماهانه شیفت («شیفت‌بندی» توی منو) فقط برای مدیر کلینیکه —
        // این AJAX همون تغییر رو می‌ده، پس باید همون سطح دسترسی رو داشته باشه.
        if (!$this->user_has_role(['dental_admin'])) wp_send_json_error(['message'=>'دسترسی مجاز نیست.']);
        $person_id   = (int)($_POST['person_id'] ?? 0);
        $person_type = sanitize_key($_POST['person_type'] ?? 'doctor');
        $work_date   = sanitize_text_field($_POST['work_date'] ?? '');
        if (!$person_id || !$work_date) wp_send_json_error();
        $new_value = Dental_Shift_Scheduler::cycle_rota_day($person_id, $person_type, $work_date);
        wp_send_json_success(['value' => $new_value]);
    }

    public function ajax_toggle_rota_lock(): void {
        check_ajax_referer('dental_workspace');
        if (!$this->user_has_role(['dental_admin'])) wp_send_json_error(['message'=>'دسترسی مجاز نیست.']);
        $person_id   = (int)($_POST['person_id'] ?? 0);
        $person_type = sanitize_key($_POST['person_type'] ?? 'assistant');
        $work_date   = sanitize_text_field($_POST['work_date'] ?? '');
        if (!$person_id || !$work_date) wp_send_json_error();
        $locked = Dental_Shift_Scheduler::toggle_rota_lock($person_id, $person_type, $work_date);
        wp_send_json_success(['locked' => $locked]);
    }

    public function ajax_get_shift_cell(): void {
        check_ajax_referer('dental_workspace');
        $work_date = sanitize_text_field($_POST['work_date'] ?? '');
        $shift     = sanitize_key($_POST['shift'] ?? '');
        $slot_key  = sanitize_text_field($_POST['slot_key'] ?? '');
        if (!$work_date || !$shift || !$slot_key) wp_send_json_error();
        $cell = Dental_Shift_Scheduler::get_cell($work_date, $shift, $slot_key);
        wp_send_json_success(['cell' => $cell]);
    }

    public function ajax_switch_role(): void {
        check_ajax_referer('dental_workspace');
        $role = sanitize_key($_POST['role'] ?? '');
        $cu = wp_get_current_user();
        $valid_roles = ['dental_admin','dental_doctor','dental_secretary','dental_financial','dental_assistant'];
        if (!in_array($role, $valid_roles) || !in_array($role, (array)$cu->roles)) wp_send_json_error();

        update_user_meta($cu->ID, '_dental_active_role', $role);

        $redirect = match($role) {
            'dental_secretary' => admin_url('admin.php?page=dental-reception'),
            'dental_financial' => admin_url('admin.php?page=dental-financial'),
            default             => admin_url('admin.php?page=dental-dashboard'),
        };
        wp_send_json_success(['redirect' => $redirect]);
    }

    public function ajax_get_inventory_items(): void {
        check_ajax_referer('dental_workspace');
        if (!class_exists('Dental_Inventory_Manager')) wp_send_json_error();
        $items = Dental_Inventory_Manager::get_items();
        $out = array_map(fn($it) => [
            'id'    => (int)$it['id'],
            'name'  => $it['name'],
            'stock' => number_format((float)$it['current_stock'], 1),
            'unit'  => $it['unit'],
        ], $items);
        wp_send_json_success(['items' => $out]);
    }

    // ─── ثبت مصرف کالا توسط پزشک — همه نقش‌ها اجازه دارن (دکتر باید
    // بتونه سریع ثبت کنه، محدودیت نداره چون اطلاعات حساس نیست) ──────
    public function ajax_log_consumption(): void {
        check_ajax_referer('dental_workspace');
        if (!class_exists('Dental_Inventory_Manager')) wp_send_json_error();
        $item_id = (int)($_POST['item_id'] ?? 0);
        $qty     = (float)($_POST['quantity'] ?? 0);
        $patient_id = (int)($_POST['patient_id'] ?? 0);
        if (!$item_id || $qty <= 0) wp_send_json_error(['message' => 'اطلاعات ناقص است.']);

        $result = Dental_Inventory_Manager::add_transaction($item_id, 'out', $qty, [
            'patient_id' => $patient_id ?: null,
            'note'       => $patient_id ? 'مصرف حین درمان — بیمار #' . $patient_id : '',
        ]);
        if (!$result['success']) wp_send_json_error(['message' => $result['message']]);
        wp_send_json_success();
    }

    public function ajax_save_shift_cell(): void {
        check_ajax_referer('dental_workspace');
        $work_date = sanitize_text_field($_POST['work_date'] ?? '');
        $shift     = sanitize_key($_POST['shift'] ?? '');
        $slot_key  = sanitize_text_field($_POST['slot_key'] ?? '');
        if (!$work_date || !$shift || !$slot_key) wp_send_json_error();

        $data = [
            'doctor_id'        => $_POST['doctor_id'] ?? null,
            'assistant_id'     => $_POST['assistant_id'] ?? null,
            'is_double'        => $_POST['is_double'] ?? 0,
            'doctor2_id'       => $_POST['doctor2_id'] ?? null,
            'assistant2_id'    => $_POST['assistant2_id'] ?? null,
            'single_assistant' => $_POST['single_assistant'] ?? 0,
        ];
        if ($slot_key === 'recv') {
            $ids = json_decode(stripslashes($_POST['assistant_ids'] ?? '[]'), true) ?: [];
            $data['extra'] = ['assistantIds' => array_map('intval', $ids), 'radiologyId' => ''];
        }
        Dental_Shift_Scheduler::save_cell($work_date, $shift, $slot_key, $data);
        wp_send_json_success();
    }

    // ─── پاک‌سازی روزانه پیام‌های قدیمی‌تر از ۱ روز (با کرون ساعت ۸ صبح) ──
    public function cleanup_old_messages(): void {
        if (class_exists('Dental_Workspace_Manager')) {
            Dental_Workspace_Manager::cleanup_old_messages();
        }
    }

    public function scan_recalls_daily(): void {
        if (class_exists('Dental_Recall_Manager')) {
            Dental_Recall_Manager::scan_due_recalls();
        }
    }

    // ─── تنظیم وضعیت حضور من ──────────────────────────────────
    public function ajax_set_status(): void {
        check_ajax_referer('dental_workspace');
        $status = sanitize_key($_POST['status'] ?? 'available');
        $note   = sanitize_text_field($_POST['note'] ?? '');
        Dental_Workspace_Manager::set_my_status(get_current_user_id(), $status, $note);
        wp_send_json_success();
    }

    // ─── رفرش زنده وضعیت/چک‌لیست/پیام‌ها بدون رفرش صفحه ─────────
    public function ajax_refresh_workspace(): void {
        check_ajax_referer('dental_workspace');
        $cu_id = get_current_user_id();
        $statuses   = Dental_Workspace_Manager::statuses();
        $all_status = Dental_Workspace_Manager::get_all_staff_status();
        $my_tasks   = Dental_Workspace_Manager::get_my_tasks($cu_id);
        $my_msgs    = Dental_Workspace_Manager::get_my_messages($cu_id, 8);
        $unread     = Dental_Workspace_Manager::unread_count($cu_id);

        ob_start();
        foreach($all_status as $s):
            [$icon,$label,$color] = $statuses[$s['status']] ?? ['⚪','—','#999'];
            $time_str = $s['time'] ? date('H:i', strtotime($s['time'])) : '';
        ?>
        <div style="display:flex;align-items:center;gap:8px;font-size:12px;padding:5px 0;border-bottom:1px solid var(--dc-neutral-50);">
            <span style="position:relative;">
                <?php echo $icon; ?>
                <?php if($s['is_online']): ?><span style="position:absolute;bottom:-2px;left:-2px;width:7px;height:7px;background:#2ECC9A;border-radius:50%;border:1.5px solid #fff;"></span><?php endif; ?>
            </span>
            <span style="flex:1;font-weight:<?php echo $s['id']==$cu_id?'700':'400'; ?>;"><?php echo esc_html($s['name']); ?><?php echo $s['id']==$cu_id?' (شما)':''; ?></span>
            <span style="color:<?php echo $color; ?>;font-size:11px;font-weight:600;"><?php echo esc_html($label); ?></span>
            <?php if($time_str): ?><span style="color:var(--dc-neutral-400);font-size:10px;min-width:32px;text-align:left;"><?php echo esc_html($time_str); ?></span><?php endif; ?>
        </div>
        <?php endforeach;
        $status_html = ob_get_clean();

        ob_start();
        if (empty($my_tasks)): ?>
        <p style="text-align:center;color:var(--dc-neutral-400);font-size:12px;padding:20px;">کاری برای شما ثبت نشده</p>
        <?php else: foreach($my_tasks as $t): ?>
        <label style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid var(--dc-neutral-50);cursor:pointer;">
            <input type="checkbox" <?php checked($t['is_done'],1); ?> onchange="dcToggleTask(<?php echo $t['id']; ?>,this.checked)" style="width:16px;height:16px;accent-color:var(--dc-primary);">
            <div style="flex:1;">
                <div style="font-size:13px;<?php echo $t['is_done']?'text-decoration:line-through;color:var(--dc-neutral-400);':''; ?>"><?php echo esc_html($t['title']); ?></div>
                <div style="font-size:10px;color:var(--dc-neutral-400);">از طرف <?php echo esc_html($t['assigned_by_name']); ?><?php echo $t['due_date']?' — '.esc_html(Dental_Jalali::to_jalali($t['due_date'],'Y/m/d')):''; ?></div>
            </div>
        </label>
        <?php endforeach; endif;
        $tasks_html = ob_get_clean();

        ob_start();
        if (empty($my_msgs)): ?>
        <p style="text-align:center;color:var(--dc-neutral-400);font-size:12px;padding:20px;">پیامی نیست</p>
        <?php else: foreach($my_msgs as $m):
            $is_mine_sent = $m['from_user_id']==$cu_id;
        ?>
        <div class="dc-msg-row" data-msg-id="<?php echo (int)$m['id']; ?>" data-unread="<?php echo (!$m['is_read']&&!$is_mine_sent)?'1':'0'; ?>"
            style="padding:9px 16px;border-bottom:1px solid var(--dc-neutral-50);<?php echo !$m['is_read']&&!$is_mine_sent?'background:var(--dc-primary-light);':''; ?>">
            <div style="font-size:11px;font-weight:700;color:var(--dc-primary);"><?php echo esc_html($m['from_name']); ?><?php echo $m['to_user_id']==0?' → 📢 همه':''; ?></div>
            <div style="font-size:12px;color:var(--dc-neutral-700);"><?php echo esc_html($m['message']); ?></div>
            <div style="font-size:10px;color:var(--dc-neutral-400);display:flex;align-items:center;gap:4px;">
                <?php echo esc_html(human_time_diff(strtotime($m['created_at']), current_time('timestamp'))); ?> پیش
                <?php if($is_mine_sent): ?>
                <span style="color:<?php echo $m['is_read']?'var(--dc-primary)':'var(--dc-neutral-300)'; ?>;"><?php echo $m['is_read']?'✓✓':'✓'; ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; endif;
        $msgs_html = ob_get_clean();

        wp_send_json_success([
            'status_html' => $status_html,
            'tasks_html'  => $tasks_html,
            'msgs_html'   => $msgs_html,
            'unread'      => $unread,
            'tasks_count' => count(array_filter($my_tasks, fn($t)=>!$t['is_done'])),
        ]);
    }

    // ─── جستجوی سریع بیمار (برای پذیرش) ───────────────────────
    public function ajax_search_patients(): void {
        check_ajax_referer('dental_workspace');
        $q = sanitize_text_field($_GET['q'] ?? '');
        if (mb_strlen($q) < 2) wp_send_json_success([]);

        global $wpdb;
        $like = '%' . $wpdb->esc_like($q) . '%';
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT p.ID, p.post_title, pm.meta_value as mobile
             FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} pm ON p.ID=pm.post_id AND pm.meta_key='_patient_mobile'
             WHERE p.post_type='dental_patient' AND p.post_status='publish'
             AND (p.post_title LIKE %s OR pm.meta_value LIKE %s)
             LIMIT 10", $like, $like
        ), ARRAY_A);
        wp_send_json_success($rows);
    }

    // ─── پرونده سریع بیمار — برگرداندن HTML آماده (AJAX) ──────
    public function ajax_quick_patient_view(): void {
        check_ajax_referer('dental_workspace');
        nocache_headers(); // جلوگیری از کش شدن این پاسخ (احتمالاً علت تأخیر ۱ روزه)
        $patient_id = (int)($_GET['patient_id'] ?? 0);
        $show_chart = !empty($_GET['show_chart']); // پذیرش این را ارسال نمی‌کند، پزشک بله
        if (!$patient_id) wp_send_json_error();
        ob_start();
        $this->render_quick_patient_view($patient_id, $show_chart);
        $html = ob_get_clean();
        wp_send_json_success(['html' => $html]);
    }

    // ─── تیک‌زدن/برداشتن آیتم طرح درمان مستقیم از همین پنل سریع ──
    public function ajax_toggle_chart_item(): void {
        check_ajax_referer('dental_workspace');
        $patient_id = (int)($_POST['patient_id'] ?? 0);
        $item_key   = sanitize_text_field($_POST['item_key'] ?? '');
        $done       = !empty($_POST['done']);
        if (!$patient_id || !$item_key) wp_send_json_error();

        // نکته: فرمت این آرایه باید دقیقاً با toggle_done() در
        // class-endpoint-chart.php یکی باشد، چون هر دو یک postmeta
        // مشترک (_chart_done_map) را می‌خوانند/می‌نویسند.
        $done_map = get_post_meta($patient_id, '_chart_done_map', true) ?: [];
        if (!is_array($done_map)) $done_map = [];

        if ($done) {
            $uid = get_current_user_id();
            $done_map[$item_key] = [
                'done'        => 1,
                'doctor_id'   => $uid,
                'doctor_name' => get_userdata($uid) ? get_userdata($uid)->display_name : '',
                'done_at'     => current_time('mysql'),
            ];
        } else {
            unset($done_map[$item_key]);
        }

        update_post_meta($patient_id, '_chart_done_map', $done_map);

        // ثبت در «کارکرد پزشکان» برای دیده‌شدن توسط مدیریت
        if ($done && class_exists('Dental_Doctor_Activity')) {
            Dental_Doctor_Activity::log(get_current_user_id(), $patient_id, 'treatment_done', Dental_Doctor_Activity::format_item_key($item_key));
        }

        wp_send_json_success();
    }

    // ─── آپلود عکس به گالری بیمار ──────────────────────────────
    public function ajax_upload_gallery_image(): void {
        check_ajax_referer('dental_workspace');
        $patient_id = (int)($_POST['patient_id'] ?? 0);
        if (!$patient_id || empty($_FILES['image'])) wp_send_json_error(['message'=>'فایلی ارسال نشده']);

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attach_id = media_handle_upload('image', 0);
        if (is_wp_error($attach_id)) wp_send_json_error(['message'=>$attach_id->get_error_message()]);

        $ids = get_post_meta($patient_id, '_patient_gallery_ids', true) ?: [];
        $ids[] = (int)$attach_id;
        update_post_meta($patient_id, '_patient_gallery_ids', $ids);
        wp_send_json_success(['url' => wp_get_attachment_image_url($attach_id, 'thumbnail'), 'full' => wp_get_attachment_image_url($attach_id, 'large')]);
    }

    // ─── رندر HTML پرونده سریع — هدر + هشدار + تب‌های واقعی (نه لینک خروج از صفحه) ──
    private function render_quick_patient_view(int $patient_id, bool $show_chart_btn = false): void {
        $patient = get_post($patient_id);
        if (!$patient) { echo '<p style="padding:20px;color:var(--dc-neutral-400);">بیمار یافت نشد.</p>'; return; }

        $f         = fn($k) => get_post_meta($patient_id, $k, true);
        $mobile    = $f('_patient_mobile');
        $dob       = $f('_patient_dob_jalali');
        $gender    = $f('_patient_gender') === 'male' ? 'مرد' : ($f('_patient_gender') === 'female' ? 'زن' : '—');
        $allergy   = $f('_drug_allergies');
        $diseases  = json_decode($f('_systemic_diseases') ?: '[]', true) ?: [];
        $disease_list = class_exists('Dental_Medical_History') ? Dental_Medical_History::get_disease_list() : [];

        global $wpdb;
        $conditions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_tooth_conditions WHERE patient_id=%d AND is_active=1", $patient_id
        ), ARRAY_A);
        $done_map = get_post_meta($patient_id, '_chart_done_map', true) ?: [];

        // ─── لیست کامل و دقیق — عیناً از TOOTH_TX / HALFARCH_SVC / FULLARCH_SVC
        // در dental-chart.js گرفته شده تا هیچ کدی خام (untranslated) نمونه.
        $tx_labels = [
            // TOOTH_TX — کد دندان
            'composite'=>'ترمیم کامپوزیت','amalgam'=>'ترمیم آمالگام','rct'=>'عصب‌کشی (RCT)',
            'pulpotomy'=>'پالپوتومی','pulpectomy'=>'پالپکتومی','buildup'=>'بیلدآپ',
            'crown'=>'روکش','veneer'=>'لامینیت / ونیر','inlay_onlay'=>'انله / آنله',
            'extraction'=>'کشیدن ساده','surgical_ext'=>'کشیدن جراحی','implant'=>'ایمپلنت',
            'apicoectomy'=>'آپیکوستومی','bridge_abutment'=>'بریج (پایه)','retainer_fix'=>'ریتینر فیکس',
            'xray_pa'=>'عکس PA','cbct'=>'CBCT تک دندان',
            'consult_perio'=>'مشاوره پریو','consult_endo'=>'مشاوره اندو','consult_surgeon'=>'مشاوره جراح',
            'consult_prosth'=>'مشاوره پروتز','consult_resto'=>'مشاوره متخصص ترمیم','consult_peds'=>'مشاوره اطفال',
            // HALFARCH_SVC — خدمات نیم‌فک
            'bwx'=>'عکس بایت‌وینگ (BWX)','scaling_half'=>'جرم‌گیری نیم‌فک','root_planing'=>'روت پلنینگ / کورتاژ',
            'flap_surgery'=>'فلاپ جراحی','consult_perio_h'=>'مشاوره پریو نیم‌فک',
            // FULLARCH_SVC — خدمات کل دهان
            'panoramic'=>'عکس پانورامیک','scaling_full'=>'جرم‌گیری کل دهان','brushing'=>'بروساژ',
            'fissure_seal'=>'فیشورسیلانت','fluoride'=>'فلوراید','bleaching'=>'بلیچینگ',
            'ortho'=>'ارتودنسی','consult_ortho'=>'مشاوره ارتودنسی','study_model'=>'مدل مطالعه',
        ];
        $q_names = [1=>'بالا راست',2=>'بالا چپ',3=>'پایین چپ',4=>'پایین راست'];

        $plan_rows = [];
        foreach ($conditions as $row) {
            $tx = json_decode($row['tooth_surface'] ?: '[]', true) ?: [];
            $key = $row['tooth_number'] . '_' . $row['tooth_type'];
            $note = $row['notes'] ?? '';
            $exam_doctor_name = $row['recorded_by'] ? (get_userdata($row['recorded_by'])->display_name ?? '') : '';

            // ─── تشخیص نیم‌فک/کل‌دهان — این‌ها دندان واقعی نیستن، محاسبه‌ی
            // کوادرانت/موقعیت (تقسیم بر ۱۰) براشون بی‌معنیه و همون چیزی
            // بود که «دندان ۹()» و «دندان ۹۹()» غلط تولید می‌کرد.
            $is_special = in_array($row['tooth_type'], ['halfarch','fullarch']);
            if ($is_special) {
                $half_names_local = [1=>'نیم‌فک بالا راست',2=>'نیم‌فک بالا چپ',3=>'نیم‌فک پایین چپ',4=>'نیم‌فک پایین راست'];
                $location_label = $row['tooth_type'] === 'halfarch'
                    ? ($half_names_local[(int)$row['tooth_number']] ?? 'نیم‌فک')
                    : 'کل دهان';
            } else {
                $q = (int)($row['tooth_number']/10); $n = $row['tooth_number']%10;
                $location_label = 'دندان '.$n.' ('.($q_names[$q]??'').')';
            }

            foreach ($tx as $code) {
                $dk = $key.'_'.$code;
                $done_info = $done_map[$dk] ?? [];
                $plan_rows[] = [
                    'key'   => $dk,
                    'title' => ($tx_labels[$code]??$code).' — '.$location_label,
                    'note'  => $note,
                    'done'  => !empty($done_info['done']),
                    // نکته: هم دکتری که معاینه/تشخیص را ثبت کرده و هم دکتری که واقعاً
                    // درمان را انجام داده (تیک زده) نمایش داده می‌شود — برای مدیریت و بقیه پزشکان
                    'exam_doctor' => $exam_doctor_name,
                    'done_doctor' => $done_info['doctor_name'] ?? '',
                    'done_at'     => $done_info['done_at'] ?? '',
                ];
            }
        }

        // ─── رضایت‌نامه‌های امضاشده — دقیقاً منطبق با class-page-consent-admin.php ──
        $consents = [];
        $consent_treatments = class_exists('Dental_Consent_Form') ? Dental_Consent_Form::get_consent_required_treatments() : [];
        foreach (get_post_meta($patient_id) as $mk => $mv) {
            if (strpos($mk, '_consent_signed_') === 0) {
                $data = maybe_unserialize($mv[0] ?? '');
                if (is_array($data)) {
                    $key   = str_replace('_consent_signed_', '', $mk);
                    $parts = explode('_', $key);
                    $code  = end($parts);
                    $consents[] = [
                        'key'       => $key,
                        'title'     => $consent_treatments[$code] ?? $code,
                        'signed_at' => $data['signed_jalali'] ?? '',
                        'attach_id' => $data['attach_id'] ?? 0,
                    ];
                }
            }
        }

        // ─── کارهای لابراتوار — دقیقاً منطبق با class-page-lab.php / Dental_CPT_Lab_Order ──
        $lab_orders = [];
        if (class_exists('Dental_CPT_Lab_Order')) {
            $lab_posts = Dental_CPT_Lab_Order::get_by_patient($patient_id);
            $lab_statuses = Dental_CPT_Lab_Order::get_statuses();
            foreach ($lab_posts as $lp) {
                $status = get_post_meta($lp->ID, '_lab_status', true);
                $lab_orders[] = [
                    'title'    => $lp->post_title,
                    'lab_name' => get_post_meta($lp->ID, '_lab_name', true),
                    'work'     => get_post_meta($lp->ID, '_lab_work_type', true),
                    'delivery' => get_post_meta($lp->ID, '_lab_delivery_jalali', true),
                    'status'   => $lab_statuses[$status] ?? ($status ?: 'در انتظار'),
                ];
            }
        }
        $lab_count     = count($lab_orders);
        $consent_count = count($consents);

        // ─── تعداد مسیرهای درمان فعال (ناتمام) — همون الگوی نشان
        // عددی لابراتوار، برای دکمه‌ی «مسیر درمان» توی پنل سریع ────
        $pathway_active_count = 0;
        if (class_exists('Dental_Pathway_Manager') && Dental_Pathway_Manager::is_enabled()) {
            $pw_list = Dental_Pathway_Manager::get_patient_pathways($patient_id);
            $pathway_active_count = count(array_filter($pw_list, fn($p) => $p['status'] === 'active'));
        }

        // ─── گالری تصاویر ─────────────────────────────────────────
        $gallery_ids = get_post_meta($patient_id, '_patient_gallery_ids', true) ?: [];

        $profile_url = admin_url("admin.php?page=dental-patients&action=profile&id={$patient_id}");
        $uid = 'qv' . $patient_id;
        ?>
        <div style="padding:18px;">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
                <div style="width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,var(--dc-primary),var(--dc-primary-dark));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;flex-shrink:0;">
                    <?php echo esc_html(mb_substr($patient->post_title,0,1)); ?>
                </div>
                <div style="flex:1;">
                    <div style="font-size:16px;font-weight:700;color:var(--dc-neutral-900);"><?php echo esc_html($patient->post_title); ?></div>
                    <div style="font-size:12px;color:var(--dc-neutral-500);display:flex;gap:10px;flex-wrap:wrap;">
                        <span>📱 <?php echo esc_html($mobile?:'—'); ?></span>
                        <span>🎂 <?php echo esc_html($dob?:'—'); ?></span>
                        <span>⚧ <?php echo esc_html($gender); ?></span>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <a href="<?php echo esc_url($profile_url); ?>" class="dc-btn dc-btn-secondary dc-btn-sm">پرونده کامل ←</a>
                    <button type="button" onclick="dcOpenPatientSummary(<?php echo $patient_id; ?>)" class="dc-btn dc-btn-secondary dc-btn-sm">📋 خلاصه پرونده</button>
                    <a href="<?php echo esc_url(admin_url("admin.php?page=dental-dashboard&print_invoice=1&patient_id={$patient_id}")); ?>" target="_blank" class="dc-btn dc-btn-ghost dc-btn-sm">🧾 فاکتور کامل</a>
                    <a href="<?php echo esc_url(admin_url("admin.php?page=dental-dashboard&print_invoice=1&patient_id={$patient_id}&date=".current_time('Y-m-d'))); ?>" target="_blank" class="dc-btn dc-btn-ghost dc-btn-sm">🧾 فاکتور فقط امروز</a>
                </div>
            </div>

            <!-- نوار هشدار پزشکی -->
            <div class="dc-alert-bar <?php echo ($allergy||!empty($diseases))?'has-alerts':''; ?>">
                <div class="dc-alert-bar-title">⚠️ اخطارهای پزشکی</div>
                <?php if ($allergy || !empty($diseases)): ?>
                <div>
                    <?php if ($allergy): ?><span class="dc-alert-chip allergy">حساسیت: <?php echo esc_html($allergy); ?></span><?php endif; ?>
                    <?php foreach ($diseases as $dk): if(!isset($disease_list[$dk])) continue; ?>
                    <span class="dc-alert-chip disease"><?php echo esc_html($disease_list[$dk]['label']); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div style="font-size:11px;color:var(--dc-neutral-400);">موردی ثبت نشده</div>
                <?php endif; ?>
            </div>

            <!-- تب‌های واقعی — همه محتوا همین‌جا لود شده، فقط نمایش/مخفی می‌شود -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(75px,1fr));gap:8px;margin-bottom:16px;">
                <?php if ($show_chart_btn): ?>
                <a href="<?php echo esc_url($profile_url.'&tab=chart'); ?>" class="dc-quick-action-btn primary">
                    🦷<span>ثبت طرح درمان</span>
                </a>
                <button type="button" class="dc-quick-action-btn primary" onclick="dcOpenCatalogPicker(<?php echo $patient_id; ?>)">
                    📋<span>ثبت از کاتالوگ خدمات</span>
                </button>
                <?php if (class_exists('Dental_Inventory_Manager')): ?>
                <button type="button" class="dc-quick-action-btn" onclick="dcOpenConsumptionModal(<?php echo $patient_id; ?>)">
                    📦<span>ثبت مصرف کالا</span>
                </button>
                <?php endif; ?>
                <?php if (class_exists('Dental_Prescription_Manager')): ?>
                <button type="button" class="dc-quick-action-btn" onclick="dcOpenPrescriptionModal(<?php echo $patient_id; ?>)">
                    💊<span>نسخه‌نویسی</span>
                </button>
                <?php endif; ?>
                <?php if (class_exists('Dental_Pathway_Manager') && Dental_Pathway_Manager::is_enabled()): ?>
                <a href="<?php echo esc_url(admin_url("admin.php?page=dental-patients&action=profile&id={$patient_id}&tab=pathway")); ?>" class="dc-quick-action-btn" style="text-decoration:none;position:relative;">
                    🛤️<span>مسیر درمان</span>
                    <?php if($pathway_active_count): ?><span class="dc-qa-badge"><?php echo $pathway_active_count; ?></span><?php endif; ?>
                </a>
                <?php endif; ?>
                <?php endif; ?>
                <button type="button" class="dc-quick-action-btn <?php echo $uid; ?>-tabbtn active" onclick="dcQvTab('<?php echo $uid; ?>','plan',this)">
                    📋<span>طرح درمان</span>
                </button>
                <button type="button" class="dc-quick-action-btn <?php echo $uid; ?>-tabbtn" onclick="dcQvTab('<?php echo $uid; ?>','consent',this)">
                    📝<span>رضایت‌نامه‌ها</span>
                    <?php if($consent_count): ?><span class="dc-qa-badge"><?php echo $consent_count; ?></span><?php endif; ?>
                </button>
                <button type="button" class="dc-quick-action-btn <?php echo $uid; ?>-tabbtn" onclick="dcQvTab('<?php echo $uid; ?>','lab',this)">
                    🔬<span>لابراتوار</span>
                    <?php if($lab_count): ?><span class="dc-qa-badge"><?php echo $lab_count; ?></span><?php endif; ?>
                </button>
                <button type="button" class="dc-quick-action-btn <?php echo $uid; ?>-tabbtn" onclick="dcQvTab('<?php echo $uid; ?>','gallery',this)">
                    🖼️<span>گالری تصاویر</span>
                    <?php if(!empty($gallery_ids)): ?><span class="dc-qa-badge"><?php echo count($gallery_ids); ?></span><?php endif; ?>
                </button>
            </div>

            <!-- پنل: طرح درمان (چک‌لیست تعاملی + توضیحات) -->
            <div class="<?php echo $uid; ?>-panel" data-tab="plan">
                <?php if (class_exists('Dental_Insurance_Manager')):
                    $p_ins = Dental_Insurance_Manager::get_patient_insurance($patient_id);
                    $ins_companies = Dental_Insurance_Manager::get_companies(true);
                ?>
                <div style="background:var(--dc-primary-light);border-radius:8px;padding:10px 12px;margin-bottom:14px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <span style="font-size:12px;font-weight:700;color:var(--dc-primary);">🛡️ بیمه:</span>
                    <select id="dc-patient-ins" data-patient="<?php echo $patient_id; ?>" class="dc-select" style="max-width:160px;font-size:12px;">
                        <option value="0">بدون بیمه</option>
                        <?php foreach($ins_companies as $ic): ?>
                        <option value="<?php echo $ic['id']; ?>" <?php selected($p_ins['insurance_id'],$ic['id']); ?>><?php echo esc_html($ic['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="dc-patient-ins-policy" value="<?php echo esc_attr($p_ins['policy_number']); ?>" placeholder="شماره بیمه‌نامه" class="dc-input" style="max-width:140px;font-size:12px;height:30px;">
                    <button type="button" onclick="dcSavePatientInsurance()" class="dc-btn dc-btn-secondary dc-btn-sm">ذخیره</button>
                    <span id="dc-ins-saved-msg" style="font-size:11px;color:var(--dc-accent-dark);display:none;">✓ ذخیره شد</span>
                </div>
                <script>
                function dcSavePatientInsurance(){
                    var fd = new FormData();
                    fd.append('action','dental_save_patient_insurance');
                    fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
                    fd.append('patient_id', document.getElementById('dc-patient-ins').dataset.patient);
                    fd.append('insurance_id', document.getElementById('dc-patient-ins').value);
                    fd.append('policy_number', document.getElementById('dc-patient-ins-policy').value);
                    fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd}).then(function(){
                        var msg = document.getElementById('dc-ins-saved-msg');
                        msg.style.display = 'inline'; setTimeout(function(){msg.style.display='none';}, 2000);
                    });
                }
                </script>
                <?php endif; ?>
                <?php
                $catalog_treatments = class_exists('Dental_Service_Catalog') ? Dental_Service_Catalog::get_patient_treatments($patient_id) : [];
                if (!empty($catalog_treatments)):
                ?>
                <div style="font-size:12px;font-weight:700;color:var(--dc-neutral-700);margin-bottom:8px;">💎 خدمات ثبت‌شده از کاتالوگ (همیشه انجام‌شده — بدون نیاز به تیک جدا)</div>
                <?php foreach($catalog_treatments as $ct):
                    $node_info = Dental_Service_Catalog::get_node((int)$ct['catalog_id']);
                    $needs_consent = !empty($node_info['requires_consent']);
                ?>
                <div class="dc-task-item" style="padding:8px 4px;">
                    <span style="color:var(--dc-accent-dark);font-size:16px;flex-shrink:0;">✅</span>
                    <div style="flex:1;">
                        <div class="dc-task-title">
                            <?php echo esc_html($ct['service_name']); ?><?php echo $ct['tooth_number']?' — '.esc_html(Dental_Service_Catalog::describe_tooth_number((int)$ct['tooth_number'])):''; ?>
                            <?php if($needs_consent): ?><span style="color:var(--dc-danger);font-size:11px;">⚠️ نیاز به رضایت‌نامه</span><?php endif; ?>
                        </div>
                        <div class="dc-task-meta">
                            💰 <?php echo number_format($ct['price']); ?> تومان
                            <?php if (!empty($ct['insurance_share'])): ?>
                            <span style="color:var(--dc-primary);">— 🛡️ بیمه: <?php echo number_format($ct['insurance_share']); ?> / سهم بیمار: <?php echo number_format($ct['patient_share']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($ct['insurance_docs_status']) && $ct['insurance_docs_status']==='pending'): ?>
                            <span onclick="dcOpenInsuranceDocs(<?php echo (int)$ct['id']; ?>)" style="color:var(--dc-danger);font-weight:700;cursor:pointer;text-decoration:underline;"> ⚠️ مدارک بیمه ناقص (کلیک برای تکمیل)</span>
                            <?php elseif (!empty($ct['insurance_docs_status']) && $ct['insurance_docs_status']==='complete'): ?>
                            <span style="color:var(--dc-accent-dark);font-weight:700;"> ✅ مدارک بیمه کامل</span>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=dental-dashboard&print_insurance_docs=1&treatment_id='.$ct['id']),'dental_print_ins_docs')); ?>" target="_blank" style="font-size:11px;margin-right:6px;">🖨️ چاپ برای بیمه</a>
                            <?php endif; ?>
                            — انجام‌شده توسط <?php echo esc_html($ct['doctor_name']); ?> در <?php echo esc_html(Dental_Jalali::to_jalali(date('Y-m-d',strtotime($ct['created_at'])),'Y/m/d')); ?>
                            <?php if (class_exists('Dental_Endodontic_Manager') && Dental_Endodontic_Manager::is_enabled() && Dental_Endodontic_Manager::is_endodontic_service($ct['service_name'])):
                                $endo_detail = Dental_Endodontic_Manager::get_by_treatment((int)$ct['id']);
                            ?>
                            <?php if ($endo_detail): ?>
                            <span onclick="dcShowEndoSummary(<?php echo (int)$ct['id']; ?>,<?php echo $patient_id; ?>,<?php echo (int)($ct['tooth_number']??0); ?>)" style="cursor:pointer;text-decoration:underline;color:var(--dc-accent-dark);"> — ✅ جزئیات عصب‌کشی ثبت شده (کلیک برای مشاهده)</span>
                            <?php else: ?>
                            <span onclick="dcOpenEndoModal(<?php echo (int)$ct['id']; ?>,<?php echo $patient_id; ?>,<?php echo (int)($ct['tooth_number']??0); ?>)" style="cursor:pointer;text-decoration:underline;color:var(--dc-neutral-400);"> — 📝 ثبت جزئیات تخصصی عصب‌کشی</span>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button type="button" onclick="dcDeleteCatalogTreatment(<?php echo (int)$ct['id']; ?>,<?php echo $patient_id; ?>)"
                        title="حذف (اگه هنوز مالی تأیید نکرده)" style="background:none;border:none;color:var(--dc-danger);cursor:pointer;font-size:14px;padding:2px 6px;">🗑️</button>
                </div>
                <?php endforeach; ?>
                <div style="border-top:1px solid var(--dc-neutral-100);margin:12px 0;"></div>
                <?php endif; ?>

                <div style="font-size:12px;font-weight:700;color:var(--dc-neutral-700);margin-bottom:8px;">
                    📋 طرح درمان (<?php echo count(array_filter($plan_rows,fn($r)=>$r['done'])); ?>/<?php echo count($plan_rows); ?> انجام‌شده)
                </div>
                <?php if (empty($plan_rows)): ?>
                <p style="font-size:12px;color:var(--dc-neutral-400);">طرح درمانی ثبت نشده</p>
                <?php else: foreach ($plan_rows as $r): ?>
                <label class="dc-task-item" style="padding:8px 4px;">
                    <input type="checkbox" <?php checked($r['done']); ?> onchange="dcToggleChartItem(<?php echo $patient_id; ?>,'<?php echo esc_js($r['key']); ?>',this.checked,this)">
                    <div style="flex:1;">
                        <div class="dc-task-title <?php echo $r['done']?'done':''; ?>"><?php echo esc_html($r['title']); ?></div>
                        <?php if($r['note']): ?><div class="dc-task-meta">💬 <?php echo esc_html($r['note']); ?></div><?php endif; ?>
                    </div>
                </label>
                <?php endforeach; endif; ?>
            </div>

            <!-- پنل: رضایت‌نامه‌ها -->
            <div class="<?php echo $uid; ?>-panel" data-tab="consent" style="display:none;">
                <?php if (empty($consents)): ?>
                <p style="font-size:12px;color:var(--dc-neutral-400);">رضایت‌نامه‌ای امضا نشده</p>
                <?php else: foreach($consents as $c): ?>
                <div class="dc-queue-row">
                    <span>📝</span>
                    <div style="flex:1;">
                        <div style="font-size:13px;font-weight:600;"><?php echo esc_html($c['title']); ?></div>
                        <div style="font-size:10px;color:var(--dc-neutral-400);"><?php echo esc_html($c['signed_at']); ?></div>
                    </div>
                    <?php if (!empty($c['attach_id'])): ?>
                    <a href="<?php echo esc_url(add_query_arg(['dental_print'=>'consent','dental_print_consent'=>$c['key'],'patient_id'=>$patient_id], dental_get_portal_url())); ?>"
                       target="_blank" class="dc-btn dc-btn-ghost dc-btn-sm">پرینت</a>
                    <?php endif; ?>
                </div>
                <?php endforeach; endif; ?>
            </div>

            <!-- پنل: لابراتوار -->
            <div class="<?php echo $uid; ?>-panel" data-tab="lab" style="display:none;">
                <?php if (empty($lab_orders)): ?>
                <p style="font-size:12px;color:var(--dc-neutral-400);">کاری برای لابراتوار ثبت نشده</p>
                <?php else: foreach($lab_orders as $lo): ?>
                <div class="dc-queue-row">
                    <span>🔬</span>
                    <div style="flex:1;">
                        <div style="font-size:13px;font-weight:600;"><?php echo esc_html($lo['title']); ?></div>
                        <div style="font-size:10px;color:var(--dc-neutral-400);">🏢 <?php echo esc_html($lo['lab_name']); ?> — تحویل: <?php echo esc_html($lo['delivery']); ?></div>
                    </div>
                    <span class="dc-badge dc-badge-neutral"><?php echo esc_html($lo['status']); ?></span>
                </div>
                <?php endforeach; endif; ?>
            </div>

            <!-- پنل: تصویربرداری (وصل به سیستم واقعی Imaging، نه گالری قدیمی) -->
            <div class="<?php echo $uid; ?>-panel" data-tab="gallery" style="display:none;">
                <?php
                $imaging_studies = class_exists('Dental_Imaging_Manager') ? Dental_Imaging_Manager::get_patient_studies($patient_id) : [];
                $imaging_images = [];
                foreach ($imaging_studies as $st) { foreach ($st['images'] as $im) { $imaging_images[] = $im; } }
                $profile_link = admin_url("admin.php?page=dental-patients&action=profile&id={$patient_id}&tab=imaging");
                ?>
                <div style="margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;">
                    <a href="<?php echo esc_url($profile_link); ?>" class="dc-btn dc-btn-primary dc-btn-sm">📤 مدیریت کامل تصاویر (آپلود/Viewer)</a>
                    <span style="font-size:11px;color:var(--dc-neutral-500);"><?php echo count($imaging_images); ?> تصویر</span>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:8px;">
                    <?php if (empty($imaging_images)): ?>
                    <p style="font-size:12px;color:var(--dc-neutral-400);grid-column:1/-1;">تصویری ثبت نشده — از دکمه‌ی بالا آپلود کنید</p>
                    <?php else: foreach($imaging_images as $img):
                        $thumb_url = add_query_arg('dental_imaging_file', $img['thumbnail_path'] ?: $img['file_path'], admin_url());
                    ?>
                    <a href="<?php echo esc_url($profile_link); ?>" style="display:block;">
                        <img src="<?php echo esc_url($thumb_url); ?>" loading="lazy"
                             style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;border:1px solid var(--dc-neutral-200);">
                    </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>

        </div>
        <?php
    }

    // ─── دارایی‌های مشترک پنل سریع (Lightbox + JS) — باید در سطح خودِ
    // صفحه (نه داخل پاسخ AJAX) صدا زده شود، چون <script> داخل innerHTML
    // هیچ‌وقت توسط مرورگر اجرا نمی‌شود. همین چیز باعث می‌شد تب‌ها و
    // چک‌لیست و گالری از داخل پنل AJAX کار نکنند.
    public static function render_quick_view_assets(): void {
        ?>
        <!-- Lightbox ساده برای زوم/چرخش تصاویر -->
        <div id="dc-lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:999999;align-items:center;justify-content:center;flex-direction:column;">
            <img id="dc-lightbox-img" style="max-width:90vw;max-height:80vh;transition:transform .2s;">
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button onclick="dcLightboxRotate(-90)" class="dc-btn dc-btn-secondary">↺ چرخش</button>
                <button onclick="dcLightboxZoom(1.2)" class="dc-btn dc-btn-secondary">🔍 بزرگ‌نمایی</button>
                <button onclick="dcLightboxZoom(0.8)" class="dc-btn dc-btn-secondary">🔎 کوچک‌نمایی</button>
                <button onclick="document.getElementById('dc-lightbox').style.display='none'" class="dc-btn dc-btn-danger">✕ بستن</button>
            </div>
        </div>

        <!-- مودال انتخاب درمان از کاتالوگ خدمات -->
        <!-- مودال نسخه‌نویسی -->
        <div id="dc-rx-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.8);z-index:999999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:16px;padding:22px;width:100%;max-width:520px;max-height:88vh;overflow-y:auto;">
                <div style="font-weight:700;font-size:14px;margin-bottom:14px;">💊 نسخه‌نویسی جدید</div>
                <!-- ─── تغییر مهم: به‌جای AJAX + window.open() جاوااسکریپتی
                     (که رفتارش توی مرورگرهای مختلف غیرقابل‌اعتماد بود —
                     گاهی محتوای صفحه‌ی قبلی رو نشون می‌داد)، از یه فرم
                     HTML واقعی با target="_blank" استفاده می‌کنیم. این
                     یعنی خودِ مرورگر (نه جاوااسکریپت) این navigation رو
                     مدیریت می‌کنه — ۱۰۰٪ استاندارد و قابل‌اعتماد. ────── -->
                <form method="post" id="dc-rx-form" onsubmit="return false;">
                    <?php wp_nonce_field('dental_workspace'); ?>
                    <input type="hidden" name="patient_id" id="dc-rx-patient-id">
                    <div id="dc-rx-items"></div>
                    <button type="button" onclick="dcAddRxItem()" class="dc-btn dc-btn-ghost dc-btn-sm" style="width:100%;margin:10px 0;">➕ افزودن داروی دیگر</button>
                    <div class="dc-form-group" style="margin-bottom:14px;">
                        <label class="dc-label">توضیح کلی نسخه (اختیاری)</label>
                        <textarea name="notes" id="dc-rx-notes" class="dc-input" rows="2"></textarea>
                    </div>
                    <div id="dc-rx-print-link-wrap" style="display:none;background:var(--dc-primary-light);border-radius:8px;padding:10px 14px;margin-bottom:12px;text-align:center;">
                        ✅ نسخه ذخیره شد — <a id="dc-rx-print-link" href="#" target="_blank" style="font-weight:700;color:var(--dc-primary);">برای پرینت اینجا کلیک کنید ←</a>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button type="button" id="dc-rx-submit-btn" onclick="dcSaveRxAjax()" class="dc-btn dc-btn-primary" style="flex:1;">✅ ثبت نسخه</button>
                        <button type="button" onclick="document.getElementById('dc-rx-modal').style.display='none'" class="dc-btn dc-btn-ghost">انصراف</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
        var dcRxItemCount = 0;
        var dcRxDrugCache = null;
        function dcOpenPrescriptionModal(patientId){
            document.getElementById('dc-rx-patient-id').value = patientId;
            document.getElementById('dc-rx-items').innerHTML = '';
            document.getElementById('dc-rx-notes').value = '';
            document.getElementById('dc-rx-print-link-wrap').style.display = 'none';
            document.getElementById('dc-rx-submit-btn').style.display = 'inline-block';
            dcRxItemCount = 0;
            dcAddRxItem();
            document.getElementById('dc-rx-modal').style.display = 'flex';
        }
        // ─── ذخیره‌ی نسخه — AJAX ساده، بدون هیچ تلاش خودکار برای باز‌کردن
        // تب (که بارها مشکل‌ساز شده بود). بعد از ذخیره‌ی موفق، فقط یه
        // لینک واقعی نشون می‌دیم که خودتون دستی کلیک می‌کنید — چون
        // کلیک واقعی کاربر همیشه ۱۰۰٪ قابل‌اعتماده، برخلاف window.open خودکار.
        function dcSaveRxAjax(){
            var items = [];
            document.querySelectorAll('#dc-rx-items > div').forEach(function(row){
                var name = row.querySelector('.dc-rx-drug-search').value;
                if (!name) return;
                items.push({
                    drug_id: row.querySelector('.dc-rx-drug-id').value,
                    drug_name: name,
                    quantity: row.querySelector('.dc-rx-qty').value,
                    quantity_unit: row.querySelector('.dc-rx-unit').value,
                    duration_days: row.querySelector('.dc-rx-duration').value,
                    frequency: row.querySelector('.dc-rx-freq').value,
                    timing_note: row.querySelector('.dc-rx-timing').value,
                    extra_note: row.querySelector('.dc-rx-extra').value,
                });
            });
            if (!items.length) { alert('حداقل یه دارو انتخاب کنید.'); return; }
            var fd = new FormData();
            fd.append('action','dental_save_prescription');
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
            fd.append('patient_id', document.getElementById('dc-rx-patient-id').value);
            fd.append('notes', document.getElementById('dc-rx-notes').value);
            items.forEach(function(it, i){
                Object.keys(it).forEach(function(k){ fd.append('items['+i+']['+k+']', it[k]); });
            });
            document.getElementById('dc-rx-submit-btn').disabled = true;
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                .then(function(r){ return r.text(); })
                .then(function(text){
                    var res;
                    try { res = JSON.parse(text); }
                    catch(e){ alert('خطای غیرمنتظره از سرور:\n' + text.substring(0,300)); document.getElementById('dc-rx-submit-btn').disabled = false; return; }
                    if (res.success) {
                        document.getElementById('dc-rx-submit-btn').style.display = 'none';
                        var link = document.getElementById('dc-rx-print-link');
                        link.href = res.data.print_url;
                        document.getElementById('dc-rx-print-link-wrap').style.display = 'block';
                    } else {
                        alert('خطا: ' + (res.data && res.data.message ? res.data.message : 'ذخیره نشد'));
                        document.getElementById('dc-rx-submit-btn').disabled = false;
                    }
                })
                .catch(function(err){ alert('خطای شبکه: ' + err.message); document.getElementById('dc-rx-submit-btn').disabled = false; });
        }
        function dcAddRxItem(){
            var idx = dcRxItemCount++;
            var wrap = document.createElement('div');
            wrap.style.cssText = 'border:1px solid var(--dc-neutral-200);border-radius:10px;padding:12px;margin-bottom:10px;position:relative;';
            wrap.innerHTML =
                '<div style="position:relative;margin-bottom:8px;">'+
                '<input type="text" class="dc-input dc-rx-drug-search" placeholder="🔍 نام دارو..." data-idx="'+idx+'">'+
                '<input type="hidden" class="dc-rx-drug-id" name="items['+idx+'][drug_id]" data-idx="'+idx+'">'+
                '<input type="hidden" class="dc-rx-drug-name-field" name="items['+idx+'][drug_name]">'+
                '<div class="dc-rx-drug-results" data-idx="'+idx+'" style="display:none;position:absolute;top:100%;right:0;left:0;background:#fff;border:1px solid #ddd;border-radius:8px;max-height:180px;overflow-y:auto;z-index:20;box-shadow:0 6px 16px rgba(0,0,0,.12);"></div>'+
                '</div>'+
                '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:8px;">'+
                '<div><label class="dc-label" style="font-size:10px;">تعداد</label><input type="number" name="items['+idx+'][quantity]" class="dc-input dc-rx-qty" value="1" min="0.5" step="0.5" style="height:34px;"></div>'+
                '<div><label class="dc-label" style="font-size:10px;">واحد</label><select name="items['+idx+'][quantity_unit]" class="dc-select dc-rx-unit" style="height:34px;"><?php foreach(Dental_Prescription_Manager::get_unit_options() as $u): ?><option><?php echo esc_html($u); ?></option><?php endforeach; ?></select></div>'+
                '<div><label class="dc-label" style="font-size:10px;">مدت (روز)</label><input type="number" name="items['+idx+'][duration_days]" class="dc-input dc-rx-duration" value="7" min="1" style="height:34px;"></div>'+
                '</div>'+
                '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;">'+
                '<div><label class="dc-label" style="font-size:10px;">دفعات مصرف</label><select name="items['+idx+'][frequency]" class="dc-select dc-rx-freq" style="height:34px;"><?php foreach(Dental_Prescription_Manager::get_frequency_options() as $f): ?><option><?php echo esc_html($f); ?></option><?php endforeach; ?></select></div>'+
                '<div><label class="dc-label" style="font-size:10px;">زمان مصرف</label><select name="items['+idx+'][timing_note]" class="dc-select dc-rx-timing" style="height:34px;"><?php foreach(Dental_Prescription_Manager::get_timing_options() as $t): ?><option><?php echo esc_html($t); ?></option><?php endforeach; ?></select></div>'+
                '</div>'+
                '<input type="text" name="items['+idx+'][extra_note]" class="dc-input dc-rx-extra" placeholder="توضیح اضافه (اختیاری، مثلاً با آب فراوان)" style="height:34px;">'+
                '<button type="button" onclick="this.parentElement.remove()" style="position:absolute;top:8px;left:8px;background:none;border:none;color:var(--dc-danger);cursor:pointer;font-size:12px;">✕</button>';
            document.getElementById('dc-rx-items').appendChild(wrap);

            var searchBox = wrap.querySelector('.dc-rx-drug-search');
            var hiddenId  = wrap.querySelector('.dc-rx-drug-id');
            var hiddenName= wrap.querySelector('.dc-rx-drug-name-field');
            var results   = wrap.querySelector('.dc-rx-drug-results');
            var timer;
            function dcRenderDrugResults(drugs){
                if (!drugs.length) { results.innerHTML='<div style="padding:8px;font-size:12px;color:#999;">یافت نشد</div>'; results.style.display='block'; return; }
                results.innerHTML = drugs.map(function(d){
                    return '<div class="dc-rx-drug-opt" data-id="'+d.id+'" data-name="'+d.name.replace(/"/g,'&quot;')+'" style="padding:8px 10px;cursor:pointer;border-bottom:1px solid #f5f5f5;font-size:12px;">'+
                        '<b>'+d.name+'</b>'+(d.english_name?' <span style="color:#999;direction:ltr;display:inline-block;font-size:10px;">('+d.english_name+')</span>':'')+
                        (d.default_dose_note?'<div style="font-size:10px;color:#999;">'+d.default_dose_note+'</div>':'')+'</div>';
                }).join('');
                results.style.display = 'block';
                results.querySelectorAll('.dc-rx-drug-opt').forEach(function(el){
                    el.addEventListener('click', function(){
                        hiddenId.value = this.dataset.id;
                        hiddenName.value = this.dataset.name;
                        searchBox.value = this.dataset.name;
                        results.style.display = 'none';
                    });
                });
            }
            function dcFetchDrugs(q){
                var fd = new FormData();
                fd.append('action','dental_search_drugs');
                fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
                fd.append('q', q);
                fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                    .then(r=>r.json()).then(function(res){ if(res.success) dcRenderDrugResults(res.data.drugs); });
            }
            // ─── با فوکوس روی کادر (حتی خالی)، کل لیست داروهای فعال
            // مثل یه کشویی باز می‌شه — لازم نیست حتماً تایپ کنید ────────
            searchBox.addEventListener('focus', function(){
                if (!this.value.trim()) dcFetchDrugs('');
            });
            searchBox.addEventListener('input', function(){
                clearTimeout(timer);
                hiddenId.value = '';
                hiddenName.value = this.value.trim(); // حتی اگه از لیست انتخاب نکنه، اسم تایپ‌شده ثبت بشه
                var q = this.value.trim();
                timer = setTimeout(function(){ dcFetchDrugs(q); }, 250);
            });
            document.addEventListener('click', function(e){ if (!results.contains(e.target) && e.target !== searchBox) results.style.display='none'; });
        }
        </script>

        <!-- مودال خلاصه‌ی سریع جزئیات عصب‌کشی (فقط نمایش، نه فرم کامل) -->
        <div id="dc-endo-summary-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.8);z-index:999999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:16px;padding:20px;width:100%;max-width:420px;">
                <div style="font-weight:700;font-size:14px;margin-bottom:12px;">📋 خلاصه‌ی جزئیات عصب‌کشی</div>
                <div id="dc-endo-summary-content" style="font-size:13px;line-height:1.9;"></div>
                <div style="display:flex;gap:8px;margin-top:16px;">
                    <button type="button" id="dc-endo-edit-btn" class="dc-btn dc-btn-secondary dc-btn-sm" style="flex:1;">✏️ ویرایش</button>
                    <button type="button" onclick="document.getElementById('dc-endo-summary-modal').style.display='none'" class="dc-btn dc-btn-ghost dc-btn-sm">بستن</button>
                </div>
            </div>
        </div>
        <script>
        function dcShowEndoSummary(treatmentId, patientId, toothNumber){
            document.getElementById('dc-endo-summary-modal').style.display = 'flex';
            document.getElementById('dc-endo-summary-content').innerHTML = 'در حال بارگذاری...';
            var fd = new FormData();
            fd.append('action','dental_get_endodontic_details');
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
            fd.append('treatment_id', treatmentId);
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                .then(r=>r.json()).then(function(res){
                    var d = res.success ? res.data.detail : null;
                    if (!d) { document.getElementById('dc-endo-summary-content').innerHTML = 'اطلاعاتی ثبت نشده.'; return; }
                    var html = '';
                    if (d.number_of_canals) html += '<div>🔢 تعداد کانال: <b>'+d.number_of_canals+'</b></div>';
                    if (d.number_of_visits) html += '<div>📅 تعداد جلسات: <b>'+d.number_of_visits+'</b></div>';
                    if (d.canals && d.canals.length) {
                        html += '<div style="margin-top:8px;font-weight:700;">کانال‌ها:</div>';
                        d.canals.forEach(function(c){
                            html += '<div style="font-size:12px;color:#5A7080;padding-right:10px;">• '+c.canal_name+
                                (c.working_length?' — طول: '+c.working_length+'mm':'')+
                                (c.final_file?' — فایل نهایی: '+c.final_file:'')+'</div>';
                        });
                    }
                    if (d.irrigation) html += '<div style="margin-top:6px;">💧 شست‌وشو: '+d.irrigation+'</div>';
                    if (d.sealer) html += '<div>🧪 سیلر: '+d.sealer+'</div>';
                    if (d.obturation_method) html += '<div>🔩 روش پرکردن: '+d.obturation_method+'</div>';
                    if (d.intracanal_medication) html += '<div>💊 دارو داخل کانال: '+d.intracanal_medication+'</div>';
                    if (d.temporary_restoration) html += '<div>🦷 ترمیم موقت: '+d.temporary_restoration+'</div>';
                    if (d.notes) html += '<div style="margin-top:6px;color:#7A96A4;">📝 '+d.notes+'</div>';
                    document.getElementById('dc-endo-summary-content').innerHTML = html || 'اطلاعاتی ثبت نشده.';
                    document.getElementById('dc-endo-edit-btn').onclick = function(){
                        document.getElementById('dc-endo-summary-modal').style.display = 'none';
                        dcOpenEndoModal(treatmentId, patientId, toothNumber);
                    };
                });
        }
        </script>

        <!-- مودال جزئیات تخصصی عصب‌کشی -->
        <div id="dc-endo-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.8);z-index:999999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:16px;padding:22px;width:100%;max-width:540px;max-height:88vh;overflow-y:auto;">
                <div style="font-weight:700;font-size:14px;margin-bottom:14px;">📝 جزئیات تخصصی عصب‌کشی</div>
                <input type="hidden" id="dc-endo-treatment-id">
                <input type="hidden" id="dc-endo-patient-id">
                <input type="hidden" id="dc-endo-tooth">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
                    <div><label class="dc-label">تعداد کانال</label><input type="number" id="dc-endo-canal-count" class="dc-input" min="1" max="6"></div>
                    <div><label class="dc-label">تعداد جلسات</label><input type="number" id="dc-endo-visits" class="dc-input" min="1" max="10"></div>
                </div>

                <div id="dc-endo-canals-wrap" style="margin-bottom:14px;"></div>
                <button type="button" onclick="dcAddEndoCanalRow()" class="dc-btn dc-btn-ghost dc-btn-sm" style="width:100%;margin-bottom:14px;">➕ افزودن کانال</button>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
                    <div><label class="dc-label">محلول شست‌وشو (Irrigation)</label><input type="text" id="dc-endo-irrigation" class="dc-input" placeholder="مثلاً: هیپوکلریت سدیم ۵.۲۵٪"></div>
                    <div><label class="dc-label">سیلر (Sealer)</label><input type="text" id="dc-endo-sealer" class="dc-input"></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
                    <div>
                        <label class="dc-label">روش پرکردن (Obturation)</label>
                        <select id="dc-endo-obturation" class="dc-select">
                            <option value="">— انتخاب —</option>
                            <?php foreach(Dental_Endodontic_Manager::get_obturation_options() as $o): ?>
                            <option><?php echo esc_html($o); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="dc-label">ترمیم موقت</label>
                        <select id="dc-endo-restoration" class="dc-select">
                            <option value="">— انتخاب —</option>
                            <?php foreach(Dental_Endodontic_Manager::get_restoration_options() as $o): ?>
                            <option><?php echo esc_html($o); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="dc-label">دارو داخل کانال (Intracanal Medication)</label>
                    <input type="text" id="dc-endo-medication" class="dc-input" placeholder="مثلاً: کلسیم هیدروکساید">
                </div>
                <div style="margin-bottom:14px;">
                    <label class="dc-label">توضیحات</label>
                    <textarea id="dc-endo-notes" class="dc-input" rows="2"></textarea>
                </div>

                <div style="display:flex;gap:8px;">
                    <button type="button" onclick="dcSaveEndoDetails()" class="dc-btn dc-btn-primary" style="flex:1;">💾 ذخیره</button>
                    <button type="button" onclick="document.getElementById('dc-endo-modal').style.display='none'" class="dc-btn dc-btn-ghost">انصراف</button>
                </div>
            </div>
        </div>
        <script>
        var dcEndoCanalCount = 0;
        function dcAddEndoCanalRow(name, wl, ff, notes){
            var idx = dcEndoCanalCount++;
            var wrap = document.getElementById('dc-endo-canals-wrap');
            var row = document.createElement('div');
            row.style.cssText = 'display:grid;grid-template-columns:1.2fr 1fr 1fr 1.5fr auto;gap:6px;margin-bottom:6px;align-items:center;';
            row.innerHTML =
                '<input type="text" class="dc-input dc-endo-canal-name" placeholder="نام کانال (مثلاً MB)" value="'+(name||'')+'" style="height:32px;font-size:12px;">'+
                '<input type="number" step="0.1" class="dc-input dc-endo-canal-wl" placeholder="طول (mm)" value="'+(wl||'')+'" style="height:32px;font-size:12px;">'+
                '<input type="text" class="dc-input dc-endo-canal-ff" placeholder="فایل نهایی" value="'+(ff||'')+'" style="height:32px;font-size:12px;">'+
                '<input type="text" class="dc-input dc-endo-canal-notes" placeholder="توضیح" value="'+(notes||'')+'" style="height:32px;font-size:12px;">'+
                '<button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:var(--dc-danger);cursor:pointer;">✕</button>';
            wrap.appendChild(row);
        }
        function dcOpenEndoModal(treatmentId, patientId, toothNumber){
            document.getElementById('dc-endo-treatment-id').value = treatmentId;
            document.getElementById('dc-endo-patient-id').value = patientId;
            document.getElementById('dc-endo-tooth').value = toothNumber;
            document.getElementById('dc-endo-canals-wrap').innerHTML = '';
            dcEndoCanalCount = 0;
            ['dc-endo-canal-count','dc-endo-visits','dc-endo-irrigation','dc-endo-sealer','dc-endo-medication','dc-endo-notes'].forEach(function(id){ document.getElementById(id).value = ''; });
            document.getElementById('dc-endo-obturation').value = '';
            document.getElementById('dc-endo-restoration').value = '';
            document.getElementById('dc-endo-modal').style.display = 'flex';

            var fd = new FormData();
            fd.append('action','dental_get_endodontic_details');
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
            fd.append('treatment_id', treatmentId);
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                .then(r=>r.json()).then(function(res){
                    var d = res.success ? res.data.detail : null;
                    if (!d) { dcAddEndoCanalRow(); dcAddEndoCanalRow(); return; }
                    document.getElementById('dc-endo-canal-count').value = d.number_of_canals || '';
                    document.getElementById('dc-endo-visits').value = d.number_of_visits || '';
                    document.getElementById('dc-endo-irrigation').value = d.irrigation || '';
                    document.getElementById('dc-endo-sealer').value = d.sealer || '';
                    document.getElementById('dc-endo-obturation').value = d.obturation_method || '';
                    document.getElementById('dc-endo-restoration').value = d.temporary_restoration || '';
                    document.getElementById('dc-endo-medication').value = d.intracanal_medication || '';
                    document.getElementById('dc-endo-notes').value = d.notes || '';
                    (d.canals || []).forEach(function(c){ dcAddEndoCanalRow(c.canal_name, c.working_length, c.final_file, c.notes); });
                    if (!d.canals || !d.canals.length) { dcAddEndoCanalRow(); }
                });
        }
        function dcSaveEndoDetails(){
            var fd = new FormData();
            fd.append('action','dental_save_endodontic_details');
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
            fd.append('treatment_id', document.getElementById('dc-endo-treatment-id').value);
            fd.append('patient_id', document.getElementById('dc-endo-patient-id').value);
            fd.append('tooth_number', document.getElementById('dc-endo-tooth').value);
            fd.append('number_of_canals', document.getElementById('dc-endo-canal-count').value);
            fd.append('number_of_visits', document.getElementById('dc-endo-visits').value);
            fd.append('irrigation', document.getElementById('dc-endo-irrigation').value);
            fd.append('sealer', document.getElementById('dc-endo-sealer').value);
            fd.append('obturation_method', document.getElementById('dc-endo-obturation').value);
            fd.append('temporary_restoration', document.getElementById('dc-endo-restoration').value);
            fd.append('intracanal_medication', document.getElementById('dc-endo-medication').value);
            fd.append('notes', document.getElementById('dc-endo-notes').value);
            document.querySelectorAll('#dc-endo-canals-wrap > div').forEach(function(row, i){
                fd.append('canals['+i+'][canal_name]', row.querySelector('.dc-endo-canal-name').value);
                fd.append('canals['+i+'][working_length]', row.querySelector('.dc-endo-canal-wl').value);
                fd.append('canals['+i+'][final_file]', row.querySelector('.dc-endo-canal-ff').value);
                fd.append('canals['+i+'][notes]', row.querySelector('.dc-endo-canal-notes').value);
            });
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                .then(r=>r.json()).then(function(res){
                    if (res.success) location.reload();
                    else alert('خطا: ' + (res.data && res.data.message ? res.data.message : 'ذخیره نشد'));
                });
        }
        </script>

        <!-- مودال مدارک بیمه -->
        <div id="dc-ins-docs-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.8);z-index:999999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:16px;padding:22px;width:100%;max-width:420px;max-height:85vh;overflow-y:auto;">
                <div style="font-weight:700;font-size:14px;margin-bottom:14px;">📋 مدارک لازم بیمه</div>
                <div id="dc-ins-docs-list"></div>
                <button type="button" onclick="document.getElementById('dc-ins-docs-modal').style.display='none';location.reload();" class="dc-btn dc-btn-ghost" style="width:100%;margin-top:10px;">بستن</button>
            </div>
        </div>
        <script>
        function dcOpenInsuranceDocs(treatmentId){
            document.getElementById('dc-ins-docs-modal').style.display = 'flex';
            var fd = new FormData();
            fd.append('action','dental_get_insurance_checklist');
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
            fd.append('treatment_id', treatmentId);
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                .then(r=>r.json()).then(function(res){
                    var list = document.getElementById('dc-ins-docs-list');
                    if (!res.success || !res.data.checklist.length) { list.innerHTML = '<p style="color:#999;text-align:center;">مدرکی لازم نیست</p>'; return; }
                    list.innerHTML = res.data.checklist.map(function(d){
                        return '<div style="border:1px solid '+(d.uploaded?'#2ECC9A':'#E0E0E0')+';border-radius:8px;padding:10px;margin-bottom:8px;">'+
                            '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">'+
                            '<b style="font-size:13px;">'+d.label+'</b>'+
                            '<span style="font-size:11px;color:'+(d.uploaded?'#2ECC9A':'#E05252')+';font-weight:700;">'+(d.uploaded?'✅ آپلود شد':'❌ ناقص')+'</span>'+
                            '</div>'+
                            '<input type="file" accept="image/jpeg,image/png,application/pdf" onchange="dcUploadInsuranceDoc('+treatmentId+',\''+d.doc_type+'\',this)">'+
                            '</div>';
                    }).join('');
                });
        }
        function dcUploadInsuranceDoc(treatmentId, docType, input){
            if (!input.files.length) return;
            var fd = new FormData();
            fd.append('action','dental_insurance_upload_doc');
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
            fd.append('treatment_id', treatmentId);
            fd.append('doc_type', docType);
            fd.append('file', input.files[0]);
            input.disabled = true;
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                .then(function(r){ return r.text(); })
                .then(function(text){
                    var res;
                    try { res = JSON.parse(text); }
                    catch(e) {
                        // اگه JSON خراب باشه (مثلاً یه Warning PHP قبلش چاپ شده)،
                        // به‌جای شکست کاملاً بی‌صدا، متن خام خطا رو نشون بده
                        alert('⚠️ خطای غیرمنتظره از سرور:\n' + text.substring(0,300));
                        input.disabled = false;
                        return;
                    }
                    if (res.success) { dcOpenInsuranceDocs(treatmentId); }
                    else { alert('خطا: ' + (res.data && res.data.message ? res.data.message : 'ناموفق')); input.disabled = false; }
                })
                .catch(function(err){
                    alert('⚠️ خطای شبکه: ' + err.message);
                    input.disabled = false;
                });
        }
        </script>

        <div id="dc-catalog-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.75);z-index:999998;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:16px;width:100%;max-width:560px;max-height:85vh;display:flex;flex-direction:column;overflow:hidden;">
                <div style="background:linear-gradient(135deg,var(--dc-primary),var(--dc-primary-dark));color:#fff;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-size:15px;font-weight:700;">انتخاب خدمت از کاتالوگ</div>
                        <div id="dc-catalog-breadcrumb" style="font-size:11px;opacity:.8;margin-top:3px;">دندانپزشکی</div>
                    </div>
                    <button onclick="dcCloseCatalogPicker()" style="background:none;border:none;color:#fff;font-size:20px;cursor:pointer;">✕</button>
                </div>
                <div style="padding:12px 20px;border-bottom:1px solid var(--dc-neutral-100);display:flex;gap:8px;align-items:center;">
                    <button id="dc-catalog-back" onclick="dcCatalogGoBack()" class="dc-btn dc-btn-ghost dc-btn-sm" style="display:none;">← بازگشت</button>
                    <input type="text" id="dc-catalog-search" class="dc-input" placeholder="🔍 جستجوی خدمت..." style="flex:1;" autocomplete="off">
                </div>
                <div id="dc-catalog-list" style="flex:1;overflow-y:auto;padding:8px 0;"></div>
            </div>
        </div>

        <script>
        (function(){
            var searchTimer;
            document.addEventListener('DOMContentLoaded', function(){
                var searchBox = document.getElementById('dc-catalog-search');
                if (!searchBox) return;
                searchBox.addEventListener('input', function(){
                    clearTimeout(searchTimer);
                    var q = this.value.trim();
                    if (q.length === 0) {
                        dcCatalogRenderLevel(dcCatalogStack.length ? dcCatalogStack[dcCatalogStack.length-1].children : dcCatalogFullTree, 'دندانپزشکی');
                        return;
                    }
                    if (q.length < 2) return;
                    searchTimer = setTimeout(function(){
                        // ─── کاملاً client-side — از همون درخت کش‌شده، بدون
                        // هیچ درخواست شبکه‌ی جدید (سریع، حتی برای کاتالوگ بزرگ) ──
                        var list = document.getElementById('dc-catalog-list');
                        document.getElementById('dc-catalog-breadcrumb').textContent = '🔍 نتایج جستجو — روی هرکدوم بزنید';
                        document.getElementById('dc-catalog-back').style.display = 'inline-block';
                        document.getElementById('dc-catalog-back').onclick = function(){
                            searchBox.value = '';
                            document.getElementById('dc-catalog-back').onclick = dcCatalogGoBack;
                            dcCatalogRenderLevel(dcCatalogStack.length ? dcCatalogStack[dcCatalogStack.length-1].children : dcCatalogFullTree, 'دندانپزشکی');
                        };
                        var results = dcCatalogSearchTree(q);
                        if (!results.length) { list.innerHTML = '<p style="text-align:center;color:#A0B4C0;padding:20px;">چیزی یافت نشد</p>'; return; }
                        list.innerHTML = results.map(function(r){
                            var it = r.node;
                            var priceTxt = it.price ? Number(it.price).toLocaleString() + ' تومان' : '⚠️ قیمت‌گذاری نشده';
                            return '<div onclick="dcCatalogNodeClick('+it.id+')" '+
                                'style="padding:10px 20px;cursor:pointer;border-bottom:1px solid #F5F5F5;" onmouseover="this.style.background=\'#F0F6F9\'" onmouseout="this.style.background=\'\'">'+
                                '<div style="font-size:13px;font-weight:600;">✓ '+it.name+'</div>'+
                                '<div style="font-size:10px;color:#A0B4C0;">📁 '+r.path+'</div>'+
                                '<div style="font-size:11px;color:#1A6B8A;">'+priceTxt+'</div></div>';
                        }).join('');
                    }, 200);
                });
            });
        })();
        </script>

        <!-- مودال انتخاب دندان بعد از انتخاب خدمت نهایی -->
                <!-- مودال ثبت مصرف کالای انبار توسط پزشک -->
        <div id="dc-consumption-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.85);z-index:999999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:16px;padding:24px;width:100%;max-width:400px;">
                <div style="font-size:14px;font-weight:700;margin-bottom:14px;">📦 ثبت مصرف کالا</div>
                <div class="dc-form-group" style="margin-bottom:12px;">
                    <label class="dc-label">کالا</label>
                    <select id="dc-cons-item" class="dc-select"></select>
                </div>
                <div class="dc-form-group" style="margin-bottom:12px;">
                    <label class="dc-label">مقدار مصرف‌شده</label>
                    <input type="number" step="0.01" id="dc-cons-qty" class="dc-input" value="1">
                </div>
                <div style="display:flex;gap:8px;margin-top:16px;">
                    <button onclick="dcSaveConsumption()" class="dc-btn dc-btn-primary" style="flex:1;">ثبت</button>
                    <button onclick="document.getElementById('dc-consumption-modal').style.display='none'" class="dc-btn dc-btn-ghost">انصراف</button>
                </div>
            </div>
        </div>
        <script>
        var dcConsPatientId = 0;
        function dcOpenConsumptionModal(patientId){
            dcConsPatientId = patientId;
            var fd = new FormData();
            fd.append('action','dental_get_inventory_items');
            fd.append('_wpnonce', dcWpNonce);
            fetch(dcAjaxUrl, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                if(!res.success) return;
                var sel = document.getElementById('dc-cons-item');
                sel.innerHTML = res.data.items.map(function(it){
                    return '<option value="'+it.id+'">'+it.name+' (موجودی: '+it.stock+' '+it.unit+')</option>';
                }).join('');
                document.getElementById('dc-consumption-modal').style.display = 'flex';
            });
        }
        function dcSaveConsumption(){
            var itemId = document.getElementById('dc-cons-item').value;
            var qty = document.getElementById('dc-cons-qty').value;
            var fd = new FormData();
            fd.append('action','dental_log_consumption');
            fd.append('_wpnonce', dcWpNonce);
            fd.append('item_id', itemId);
            fd.append('quantity', qty);
            fd.append('patient_id', dcConsPatientId);
            fetch(dcAjaxUrl, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                document.getElementById('dc-consumption-modal').style.display = 'none';
                if(res.success) alert('✅ ثبت شد.');
                else alert('❌ ' + (res.data && res.data.message ? res.data.message : 'خطا'));
            });
        }
        </script>

        <div id="dc-catalog-tooth-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.85);z-index:999999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:16px;padding:24px;width:100%;max-width:640px;max-height:88vh;overflow-y:auto;text-align:center;">
                <div id="dc-catalog-selected-name" style="font-size:14px;font-weight:700;color:var(--dc-primary);margin-bottom:6px;"></div>
                <div id="dc-catalog-selected-price" style="font-size:12px;color:var(--dc-neutral-500);margin-bottom:16px;"></div>
                <div style="font-size:12px;color:var(--dc-neutral-600);margin-bottom:10px;">محل درمان را انتخاب کنید:</div>
                <div id="dc-tooth-picker-grid"></div>
                <button onclick="dcCatalogSaveGeneral()" id="dc-catalog-no-tooth-btn" class="dc-btn dc-btn-secondary" style="width:100%;margin-bottom:8px;display:none;">
                    این خدمت مربوط به دندان خاصی نیست (ثبت عمومی)
                </button>
                <button onclick="dcCloseToothPicker()" class="dc-btn dc-btn-ghost" style="width:100%;margin-top:10px;">انصراف</button>
            </div>
        </div>

        <!-- مودال خلاصه پرونده (چارت + تاریخچه کامل) -->
        <div id="dc-summary-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.75);z-index:999997;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:16px;width:100%;max-width:650px;max-height:88vh;display:flex;flex-direction:column;overflow:hidden;">
                <div style="background:linear-gradient(135deg,var(--dc-primary),var(--dc-primary-dark));color:#fff;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;">
                    <div style="font-size:15px;font-weight:700;">📋 خلاصه پرونده</div>
                    <button onclick="document.getElementById('dc-summary-modal').style.display='none'" style="background:none;border:none;color:#fff;font-size:20px;cursor:pointer;">✕</button>
                </div>
                <div id="dc-summary-content" style="flex:1;overflow-y:auto;"></div>
            </div>
        </div>
        <!-- Tooltip شناور برای هاور روی دندون -->
        <div id="dc-tooth-tooltip" style="display:none;position:fixed;background:#1A2733;color:#fff;padding:10px 14px;border-radius:8px;font-size:11px;
            max-width:280px;z-index:9999999;box-shadow:0 6px 20px rgba(0,0,0,.3);line-height:1.8;"></div>

        <script>
        function dcOpenPatientSummary(patientId){
            document.getElementById('dc-summary-modal').style.display = 'flex';
            document.getElementById('dc-summary-content').innerHTML = '<div style="padding:40px;text-align:center;color:#A0B4C0;">در حال بارگذاری...</div>';
            fetch(dcAjaxUrl + '?action=dental_get_patient_summary&patient_id=' + patientId + '&_wpnonce=' + dcWpNonce)
            .then(r=>r.json()).then(function(res){
                document.getElementById('dc-summary-content').innerHTML = res.success ? res.data.html : '<p style="padding:20px;color:#E05252;">خطا در بارگذاری</p>';
            });
        }
        function dcShowToothTooltip(el, lines){
            var tip = document.getElementById('dc-tooth-tooltip');
            tip.innerHTML = lines.join('<br>');
            var rect = el.getBoundingClientRect();
            tip.style.display = 'block';
            tip.style.top = (rect.bottom + 8) + 'px';
            tip.style.left = Math.max(10, rect.left - 100) + 'px';
        }
        function dcHideToothTooltip(){
            document.getElementById('dc-tooth-tooltip').style.display = 'none';
        }
        </script>

        <script>
        if (typeof dcWpNonce === 'undefined') { var dcWpNonce = '<?php echo esc_js(wp_create_nonce("dental_workspace")); ?>'; }
        if (typeof dcAjaxUrl === 'undefined') { var dcAjaxUrl = '<?php echo esc_js(admin_url("admin-ajax.php")); ?>'; }

        // ═══════════ مودال انتخاب درمان از کاتالوگ ═══════════════════
        // نکته مهم کارایی: به‌جای اینکه هر «باز کردن زیرشاخه» یه
        // درخواست AJAX جدا بزنه (که چون admin-ajax.php هر بار کل
        // وردپرس رو بوت می‌کنه، محسوس کنده بود)، الان کل درخت کاتالوگ
        // رو یه‌بار (اولین بار که مودال باز می‌شه) می‌گیریم و کش می‌کنیم —
        // از اون به بعد، مرور/جستجو/بازگشت همه کاملاً client-side و آنیه.
        var dcCatalogPatientId = null;
        var dcCatalogStack = [];
        var dcCatalogSelectedLeaf = null;
        var dcCatalogFullTree = null; // کش کل درخت — یه‌بار پر می‌شه

        function dcOpenCatalogPicker(patientId){
            dcCatalogPatientId = patientId;
            dcCatalogStack = [];
            document.getElementById('dc-catalog-modal').style.display = 'flex';
            var sb = document.getElementById('dc-catalog-search');
            if (sb) sb.value = '';
            if (dcCatalogFullTree) {
                dcCatalogRenderLevel(dcCatalogFullTree, 'دندانپزشکی');
            } else {
                var list = document.getElementById('dc-catalog-list');
                list.innerHTML = '<div style="text-align:center;padding:30px;color:#A0B4C0;">در حال بارگذاری کاتالوگ (فقط بار اول)...</div>';
                var fd = new FormData();
                fd.append('action','dental_catalog_full_tree');
                fd.append('_wpnonce', dcWpNonce);
                fetch(dcAjaxUrl, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                    if(!res.success){ list.innerHTML = '<p style="text-align:center;color:#E05252;">خطا در بارگذاری کاتالوگ</p>'; return; }
                    dcCatalogFullTree = res.data.tree;
                    dcCatalogRenderLevel(dcCatalogFullTree, 'دندانپزشکی');
                });
            }
        }
        function dcCloseCatalogPicker(){
            document.getElementById('dc-catalog-modal').style.display = 'none';
        }
        function dcCatalogGoBack(){
            dcCatalogStack.pop();
            var prev = dcCatalogStack.pop();
            var level = prev ? prev.children : dcCatalogFullTree;
            dcCatalogRenderLevel(level, prev ? prev.name : 'دندانپزشکی', prev);
        }
        // ─── رندر یه سطح از درخت (کاملاً از حافظه، بدون شبکه) ──────────
        function dcCatalogRenderLevel(nodes, label, pushNode){
            if (pushNode) dcCatalogStack.push(pushNode);
            var sb = document.getElementById('dc-catalog-search');
            if (sb) sb.value = '';
            document.getElementById('dc-catalog-back').onclick = dcCatalogGoBack;
            document.getElementById('dc-catalog-back').style.display = dcCatalogStack.length > 0 ? 'inline-block' : 'none';
            var crumbs = dcCatalogStack.map(function(n){return n.name;});
            document.getElementById('dc-catalog-breadcrumb').textContent = crumbs.length ? crumbs.join(' > ') : 'دندانپزشکی';

            var list = document.getElementById('dc-catalog-list');
            if (!nodes || !nodes.length) { list.innerHTML = '<p style="text-align:center;color:#A0B4C0;padding:20px;">زیرمجموعه‌ای ندارد</p>'; return; }
            list.innerHTML = nodes.map(function(c){
                var priceTxt = c.is_leaf ? (c.price ? Number(c.price).toLocaleString() + ' تومان' : '⚠️ قیمت‌گذاری نشده') : '';
                return '<div onclick="dcCatalogNodeClick('+c.id+')" '+
                    'style="display:flex;align-items:center;justify-content:space-between;padding:12px 20px;cursor:pointer;border-bottom:1px solid #F5F5F5;" '+
                    'onmouseover="this.style.background=\'#F0F6F9\'" onmouseout="this.style.background=\'#fff\'">'+
                    '<span style="font-size:13px;">'+(c.is_leaf?'✓ ':'📁 ')+c.name+'</span>'+
                    '<span style="font-size:11px;color:#A0B4C0;">'+priceTxt+(c.is_leaf?'':' ›')+'</span>'+
                '</div>';
            }).join('');
        }
        // ─── کلیک روی یه آیتم توی درخت اصلی (نه سرچ) ───────────────────
        function dcCatalogNodeClick(id){
            var node = dcCatalogFindById(dcCatalogFullTree, id);
            if (!node) return;
            if (node.is_leaf) {
                dcCatalogClick(node.id, node.name, 1, node.tooth_filter || 'all', node.price || 0);
            } else {
                dcCatalogRenderLevel(node.children, node.name, node);
            }
        }
        function dcCatalogFindById(nodes, id){
            if (!nodes) return null;
            for (var i=0; i<nodes.length; i++){
                if (nodes[i].id === id) return nodes[i];
                if (nodes[i].children && nodes[i].children.length){
                    var found = dcCatalogFindById(nodes[i].children, id);
                    if (found) return found;
                }
            }
            return null;
        }
        // ─── جستجو — کاملاً روی درخت کش‌شده، بدون شبکه ──────────────────
        function dcCatalogSearchTree(query){
            var q = query.trim().toLowerCase();
            var results = [];
            function walk(nodes, pathNames){
                for (var i=0; i<nodes.length; i++){
                    var n = nodes[i];
                    var newPath = pathNames.concat([n.name]);
                    if (n.is_leaf && n.name.toLowerCase().indexOf(q) !== -1){
                        results.push({node:n, path:newPath.join(' > ')});
                    }
                    if (n.children && n.children.length) walk(n.children, newPath);
                }
            }
            if (dcCatalogFullTree) walk(dcCatalogFullTree, []);
            return results.slice(0, 100);
        }
        function dcCatalogClick(id, name, isLeaf, toothFilter, price){
            if(!isLeaf){
                var node = dcCatalogFindById(dcCatalogFullTree, id);
                dcCatalogRenderLevel(node ? node.children : [], name, node);
                return;
            }
            // برگ نهایی — مودال انتخاب دندان رو باز کن
            dcCatalogSelectedLeaf = {id:id, name:name, price:price};
            dcCloseCatalogPicker();
            dcOpenToothPicker();
        }

        // ─── انتخاب چندتایی — پزشک می‌تونه چند دندون/فک با هم انتخاب کنه و یه‌جا ثبت کنه ──
        var dcSelectedTargets = []; // [{code, label}, ...]

        function dcOpenToothPicker(){
            var leaf = dcCatalogSelectedLeaf;
            dcSelectedTargets = [];
            document.getElementById('dc-catalog-selected-name').textContent = leaf.name;
            document.getElementById('dc-catalog-selected-price').textContent = leaf.price ? Number(leaf.price).toLocaleString()+' تومان' : '⚠️ هنوز قیمت‌گذاری نشده — به مدیر اطلاع دهید';
            var grid = document.getElementById('dc-tooth-picker-grid');
            grid.innerHTML = '';
            grid.style.cssText = 'text-align:center;';

            // ─── چارت بصری — دقیقاً شبیه چارت اصلی: نوار فک بالا، دو نیم‌فک
            // کنار هم، خط جداکننده وسط، بعد نوار فک پایین ───────────────
            var chartBox = document.createElement('div');
            chartBox.style.cssText = 'background:var(--dc-neutral-50);border:1px solid var(--dc-neutral-200);border-radius:12px;padding:14px;margin-bottom:14px;';

            chartBox.appendChild(dcMakeJawBar('کل فک بالا', 91));

            var upperHalves = document.createElement('div');
            upperHalves.style.cssText = 'display:flex;justify-content:space-between;align-items:center;margin:8px 0 4px;';
            upperHalves.appendChild(dcMakeHalfBtn('نیم‌فک بالا راست', 2));
            upperHalves.appendChild(dcMakeHalfBtn('نیم‌فک بالا چپ', 1));
            chartBox.appendChild(upperHalves);

            chartBox.appendChild(dcBuildQuadrantChart(8, function(q,n){ return q*10+n; }, function(n){ return n; }, [2,1]));

            var midLine = document.createElement('div');
            midLine.style.cssText = 'border-top:2px solid var(--dc-neutral-300);margin:8px 0;';
            chartBox.appendChild(midLine);

            chartBox.appendChild(dcBuildQuadrantChart(8, function(q,n){ return q*10+n; }, function(n){ return n; }, [3,4]));

            var lowerHalves = document.createElement('div');
            lowerHalves.style.cssText = 'display:flex;justify-content:space-between;align-items:center;margin:4px 0 8px;';
            lowerHalves.appendChild(dcMakeHalfBtn('نیم‌فک پایین راست', 3));
            lowerHalves.appendChild(dcMakeHalfBtn('نیم‌فک پایین چپ', 4));
            chartBox.appendChild(lowerHalves);

            chartBox.appendChild(dcMakeJawBar('کل فک پایین', 92));

            grid.appendChild(chartBox);

            // دکمه کل دهان + دندان‌های شیری زیر چارت اصلی
            var extraRow = document.createElement('div');
            extraRow.style.cssText = 'display:flex;gap:6px;margin-bottom:10px;';
            extraRow.appendChild(dcMakeQuickChip('🦷 کل دهان (بدون دندان خاص)', null));
            var toggleBtn = document.createElement('button');
            toggleBtn.textContent = '👶 نمایش دندان‌های شیری';
            toggleBtn.className = 'dc-btn dc-btn-ghost dc-btn-sm';
            toggleBtn.style.flex = '1';
            var primWrap = document.createElement('div');
            primWrap.style.cssText = 'display:none;background:var(--dc-neutral-50);border:1px solid var(--dc-neutral-200);border-radius:12px;padding:14px;margin-bottom:10px;';
            primWrap.appendChild(dcBuildQuadrantChart(5, function(q,n){ return (q+4)*10+n; }, function(n){ return String.fromCharCode(64+n); }, [5,6]));
            var midLine2 = document.createElement('div');
            midLine2.style.cssText = 'border-top:2px solid var(--dc-neutral-300);margin:8px 0;';
            primWrap.appendChild(midLine2);
            primWrap.appendChild(dcBuildQuadrantChart(5, function(q,n){ return (q+4)*10+n; }, function(n){ return String.fromCharCode(64+n); }, [7,8]));
            toggleBtn.onclick = function(){ primWrap.style.display = primWrap.style.display==='none' ? 'block' : 'none'; };
            extraRow.appendChild(toggleBtn);
            grid.appendChild(extraRow);
            grid.appendChild(primWrap);

            // ─── نوار خلاصه انتخاب‌ها + دکمه ثبت نهایی ────────────────
            var summary = document.createElement('div');
            summary.id = 'dc-selected-summary';
            summary.style.cssText = 'text-align:right;font-size:12px;color:var(--dc-neutral-500);margin-bottom:10px;min-height:20px;';
            summary.textContent = 'هنوز چیزی انتخاب نشده';
            grid.appendChild(summary);

            var saveBtn = document.createElement('button');
            saveBtn.id = 'dc-confirm-multi-btn';
            saveBtn.className = 'dc-btn dc-btn-primary';
            saveBtn.style.cssText = 'width:100%;padding:12px;font-size:14px;';
            saveBtn.textContent = '✅ ثبت موارد انتخاب‌شده';
            saveBtn.disabled = true;
            saveBtn.onclick = dcConfirmMultiSave;
            grid.appendChild(saveBtn);

            document.getElementById('dc-catalog-no-tooth-btn').style.display = 'none';
            document.getElementById('dc-catalog-tooth-modal').style.display = 'flex';
        }

        // ─── نوار کامل فک (کلیک‌پذیر، سرتاسر عرض چارت) ─────────────
        function dcMakeJawBar(label, code){
            var bar = document.createElement('div');
            bar.textContent = label;
            bar.dataset.code = code;
            bar.style.cssText = 'background:#fff;border:1px solid var(--dc-neutral-300);border-radius:8px;padding:6px;font-size:11px;font-weight:700;color:var(--dc-neutral-600);cursor:pointer;text-align:center;';
            bar.onclick = function(){ dcToggleTarget(code, label, bar); };
            return bar;
        }
        // ─── دکمه نیم‌فک (کنار چارت) ─────────────────────────────────
        function dcMakeHalfBtn(label, code){
            var b = document.createElement('div');
            b.textContent = label;
            b.style.cssText = 'background:#fff;border:1px solid var(--dc-neutral-300);border-radius:8px;padding:5px 10px;font-size:10px;font-weight:700;color:var(--dc-neutral-600);cursor:pointer;';
            b.onclick = function(){ dcToggleTarget(code, label, b); };
            return b;
        }
        function dcMakeQuickChip(label, code){
            var b = document.createElement('button');
            b.textContent = label;
            b.className = 'dc-btn dc-btn-secondary dc-btn-sm';
            b.style.flex = '1';
            b.onclick = function(){ dcToggleTarget(code, label, b); };
            return b;
        }

        // ─── ساخت چارت ۴کوادرانتی — فقط دوتای مشخص‌شده در quadrants ────
        function dcBuildQuadrantChart(teethPerQuadrant, codeFn, labelFn, quadrants){
            var rowDiv = document.createElement('div');
            rowDiv.style.cssText = 'display:flex;gap:3px;justify-content:center;align-items:center;margin:4px 0;';
            for(var n=teethPerQuadrant; n>=1; n--) rowDiv.appendChild(dcMakeToothBtn(quadrants[0], n, labelFn, codeFn));
            var divider = document.createElement('div');
            divider.style.cssText = 'width:1px;height:26px;background:var(--dc-neutral-300);margin:0 6px;';
            rowDiv.appendChild(divider);
            for(var n2=1; n2<=teethPerQuadrant; n2++) rowDiv.appendChild(dcMakeToothBtn(quadrants[1], n2, labelFn, codeFn));
            return rowDiv;
        }
        // ─── دکمه هر دندون — دقیقاً شبیه ظاهر چارت اصلی، با حالت انتخاب‌شده ──
        function dcMakeToothBtn(quadrant, n, labelFn, codeFn){
            var toothCode = codeFn(quadrant, n);
            var btn = document.createElement('button');
            btn.textContent = labelFn(n);
            btn.dataset.code = toothCode;
            btn.style.cssText = 'width:30px;height:30px;border-radius:6px;font-size:11px;font-weight:700;background:#fff;border:1.5px solid var(--dc-neutral-300);color:var(--dc-neutral-700);cursor:pointer;transition:all .15s;';
            btn.onclick = function(){ dcToggleTarget(toothCode, 'دندان '+labelFn(n), btn); };
            return btn;
        }

        // ─── تیک‌زدن/برداشتن یک هدف (دندون/فک/نیم‌فک) از لیست انتخاب ────
        function dcToggleTarget(code, label, el){
            var idx = dcSelectedTargets.findIndex(function(t){ return t.code === code; });
            if(idx > -1){
                dcSelectedTargets.splice(idx, 1);
                el.style.background = '#fff'; el.style.color = 'var(--dc-neutral-700)'; el.style.borderColor = 'var(--dc-neutral-300)';
            } else {
                dcSelectedTargets.push({code: code, label: label});
                el.style.background = 'var(--dc-primary)'; el.style.color = '#fff'; el.style.borderColor = 'var(--dc-primary)';
            }
            var summary = document.getElementById('dc-selected-summary');
            var btn = document.getElementById('dc-confirm-multi-btn');
            if(dcSelectedTargets.length){
                summary.innerHTML = '✅ انتخاب‌شده: ' + dcSelectedTargets.map(function(t){return t.label;}).join('، ');
                btn.disabled = false;
            } else {
                summary.textContent = 'هنوز چیزی انتخاب نشده';
                btn.disabled = true;
            }
        }

        // ─── ثبت همه موارد انتخاب‌شده با هم (یکی‌یکی، پشت سر هم) ────────
        function dcConfirmMultiSave(){
            var btn = document.getElementById('dc-confirm-multi-btn');
            btn.disabled = true; btn.textContent = '⏳ در حال ثبت...';
            var targets = dcSelectedTargets.slice();
            var done = 0;
            var errors = [];
            function saveNext(){
                if(done >= targets.length){
                    dcCloseToothPicker();
                    if (errors.length) {
                        alert('⚠️ برخی موارد ذخیره نشدن:\n' + errors.join('\n'));
                    }
                    dcRefreshAfterCatalogSave();
                    return;
                }
                var t = targets[done];
                var fd = new FormData();
                fd.append('action','dental_catalog_record_treatment');
                fd.append('_wpnonce', dcWpNonce);
                fd.append('patient_id', dcCatalogPatientId);
                fd.append('catalog_id', dcCatalogSelectedLeaf.id);
                if(t.code !== null) fd.append('tooth_number', t.code);
                fetch(dcAjaxUrl, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                    // رفع باگ مهم: قبلاً اینجا جواب سرور اصلاً چک نمی‌شد —
                    // حتی اگه ذخیره واقعاً شکست می‌خورد، کد فرض می‌کرد موفق
                    // بوده و پیام «همه موارد ثبت شد» غلط نشون می‌داد.
                    if (!res.success) {
                        errors.push(t.label + ': ' + (res.data && res.data.message ? res.data.message : 'خطای نامشخص'));
                    }
                    done++;
                    saveNext();
                });
            }
            saveNext();
        }
        function dcRefreshAfterCatalogSave(){
            alert('✅ همه موارد ثبت شد.');
            if(typeof dcDeskLoadPatient === 'function'){
                var row = document.querySelector('.dc-desk-patient-row[data-patient-id="'+dcCatalogPatientId+'"]');
                if(row) dcDeskLoadPatient(dcCatalogPatientId, row);
            } else if(typeof dcLoadQuickView === 'function'){
                dcLoadQuickView(dcCatalogPatientId);
            }
        }
        function dcCloseToothPicker(){
            document.getElementById('dc-catalog-tooth-modal').style.display = 'none';
        }
        function dcCatalogSaveGeneral(){ dcCatalogSaveTreatment(null); }
        function dcCatalogSaveTreatment(toothNum){
            var fd = new FormData();
            fd.append('action','dental_catalog_record_treatment');
            fd.append('_wpnonce', dcWpNonce);
            fd.append('patient_id', dcCatalogPatientId);
            fd.append('catalog_id', dcCatalogSelectedLeaf.id);
            if(toothNum !== null) fd.append('tooth_number', toothNum);
            fetch(dcAjaxUrl, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                dcCloseToothPicker();
                if(res.success){
                    if(res.data.requires_consent){
                        alert('✅ ثبت شد.\n\n⚠️ توجه: این درمان نیاز به رضایت‌نامه امضاشده دارد. لطفاً از تب «رضایت‌نامه‌ها» بررسی کنید که برای این بیمار ثبت شده باشد.');
                    } else {
                        alert('✅ ثبت شد.');
                    }
                    // رفرش پنل جزئیات بیمار برای دیدن آیتم جدید
                    if(typeof dcDeskLoadPatient === 'function'){
                        var row = document.querySelector('.dc-desk-patient-row[data-patient-id="'+dcCatalogPatientId+'"]');
                        if(row) dcDeskLoadPatient(dcCatalogPatientId, row);
                    } else if(typeof dcLoadQuickView === 'function'){
                        dcLoadQuickView(dcCatalogPatientId);
                    }
                } else {
                    alert('❌ ثبت انجام نشد: ' + (res.data && res.data.message ? res.data.message : 'خطای نامشخص'));
                }
            });
        }
        function dcToggleCatalogTreatment(id, done, checkboxEl){
            var titleEl = checkboxEl.closest('label').querySelector('.dc-task-title');
            if(titleEl) titleEl.classList.toggle('done', done);
            var fd = new FormData();
            fd.append('action','dental_catalog_toggle_treatment');
            fd.append('_wpnonce', dcWpNonce);
            fd.append('id', id);
            fd.append('done', done?1:0);
            fetch(dcAjaxUrl, {method:'POST', body:fd});
        }
        function dcDeleteCatalogTreatment(id, patientId){
            if(!confirm('این درمان حذف بشه؟')) return;
            var fd = new FormData();
            fd.append('action','dental_catalog_delete_treatment');
            fd.append('_wpnonce', dcWpNonce);
            fd.append('id', id);
            fetch(dcAjaxUrl, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                if(res.success){
                    if(typeof dcDeskLoadPatient === 'function'){
                        var row = document.querySelector('.dc-desk-patient-row[data-patient-id="'+patientId+'"]');
                        if(row) dcDeskLoadPatient(patientId, row);
                    } else if(typeof dcLoadQuickView === 'function'){
                        dcLoadQuickView(patientId);
                    }
                } else {
                    alert('❌ ' + (res.data && res.data.message ? res.data.message : 'حذف انجام نشد'));
                }
            });
        }

        function dcQvTab(uid, tab, btn){
            document.querySelectorAll('.'+uid+'-panel').forEach(p => p.style.display = (p.dataset.tab===tab ? 'block' : 'none'));
            document.querySelectorAll('.'+uid+'-tabbtn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
        function dcToggleChartItem(patientId, itemKey, done, checkboxEl){
            // بازخورد فوری بصری — بدون نیاز به رفتن به جای دیگه و برگشتن
            if (checkboxEl) {
                var titleEl = checkboxEl.closest('label').querySelector('.dc-task-title');
                if (titleEl) titleEl.classList.toggle('done', done);
            }
            var fd = new FormData();
            fd.append('action','dental_toggle_chart_item');
            fd.append('_wpnonce', dcWpNonce);
            fd.append('patient_id', patientId);
            fd.append('item_key', itemKey);
            fd.append('done', done?1:0);
            fetch(dcAjaxUrl, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                if(!res || !res.success){
                    alert('ذخیره نشد! لطفاً دوباره تلاش کنید.');
                    if (checkboxEl) checkboxEl.checked = !done; // برگردوندن به حالت قبل چون ذخیره نشد
                }
            }).catch(function(){
                alert('خطا در ارتباط با سرور.');
                if (checkboxEl) checkboxEl.checked = !done;
            });
        }
        function dcUploadGalleryImage(patientId, input){
            if(!input.files[0]) return;
            var fd = new FormData();
            fd.append('action','dental_upload_gallery_image');
            fd.append('_wpnonce', dcWpNonce);
            fd.append('patient_id', patientId);
            fd.append('image', input.files[0]);
            fetch(dcAjaxUrl, {method:'POST', body:fd}).then(r=>r.json()).then(function(res){
                if(res.success){
                    var grid = document.querySelectorAll('[id$="-gallery-grid"]')[0];
                    var img = document.createElement('img');
                    img.src = res.data.url;
                    img.style.cssText = 'width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;cursor:zoom-in;border:1px solid var(--dc-neutral-200);';
                    img.onclick = function(){ dcOpenLightbox(res.data.full); };
                    grid.appendChild(img);
                }
            });
        }
        var dcLbZoom = 1, dcLbRotate = 0;
        function dcOpenLightbox(url){
            dcLbZoom = 1; dcLbRotate = 0;
            document.getElementById('dc-lightbox-img').src = url;
            document.getElementById('dc-lightbox-img').style.transform = 'scale(1) rotate(0deg)';
            document.getElementById('dc-lightbox').style.display = 'flex';
        }
        function dcLightboxZoom(f){ dcLbZoom *= f; dcLightboxApply(); }
        function dcLightboxRotate(d){ dcLbRotate += d; dcLightboxApply(); }
        function dcLightboxApply(){
            document.getElementById('dc-lightbox-img').style.transform = 'scale('+dcLbZoom+') rotate('+dcLbRotate+'deg)';
        }
        </script>
        <?php
    }

    // ─── Helpers ─────────────────────────────────────────────────
    public function ajax_check_today_appt(): void {
        check_ajax_referer('dental_check_appt');
        $patient_id = (int)($_GET['patient_id'] ?? 0);
        if (!$patient_id || !class_exists('Dental_Reception_Manager')) {
            wp_send_json_error();
        }
        $appt = Dental_Reception_Manager::find_today_appointment($patient_id);
        if ($appt) {
            wp_send_json_success([
                'appointment_id' => (int)$appt['id'],
                'doctor_id'      => (int)$appt['doctor_id'],
                'time'           => substr($appt['start_time'], 0, 5),
            ]);
        }
        wp_send_json_success(null);
    }

    private function get_sms_count_today(): int {
        global $wpdb;
        return (int)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dental_sms_log WHERE DATE(created_at)=%s",
            current_time('Y-m-d')
        ));
    }

    private function get_total_received(): float {
        global $wpdb;
        $first_day = current_time('Y-m-01');
        return (float)$wpdb->get_var($wpdb->prepare(
            "SELECT SUM(paid_amount) FROM {$wpdb->prefix}dental_installment_items
             WHERE status='paid' AND paid_date >= %s",
            $first_day
        ));
    }

    // ─── تعداد فیش‌های پرداختی که آپلود شدن ولی هنوز تأیید نشدن —
    // منظور کاربر این بود، نه تعداد پرداخت‌های تکمیل‌شده ─────────────
    private function get_pending_receipts_count(): int {
        global $wpdb;
        return (int)$wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dental_installment_items
             WHERE status='partial' AND receipt_image_id IS NOT NULL AND receipt_image_id > 0"
        );
    }

    private function format_currency(float $amount): string {
        return number_format($amount) . ' ت';
    }
}
