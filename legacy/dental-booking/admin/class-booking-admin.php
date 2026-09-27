<?php
defined('ABSPATH') || exit;

class Dental_Booking_Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_menus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_init', [$this, 'maybe_print_day']);
        add_action('admin_init', [$this, 'maybe_self_heal_columns']);
        add_action('admin_init', [$this, 'maybe_handle_new_booking']);
    }

    // ─── همون رفع «صفحه‌ی سفید بعد از ذخیره» — ثبت نوبت جدید (شامل
    // «فقط قفل کن») باید قبل از رندر chrome ادمین پردازش بشه، وگرنه
    // wp_safe_redirect() با «headers already sent» شکست می‌خوره ────
    public function maybe_handle_new_booking(): void {
        if (!isset($_GET['page']) || $_GET['page'] !== 'dental-booking-list') return;
        if (!isset($_POST['dental_book_new'])) return;
        if (class_exists('Dental_Page_Booking_List')) {
            (new Dental_Page_Booking_List())->maybe_handle_post();
        }
    }

    // ─── پرینت روزانه — باید قبل از رندر chrome ادمین اجرا شود ───
    // اگر داخل callback صفحه (render_calendar) چک شود، خیلی دیر است:
    // وردپرس تا آن لحظه قبلاً کل قالب پیشخوان (هدر، منو، ...) را چاپ کرده
    // و خروجی پرینت داخل همان صفحه تو در تو می‌شود نه به‌صورت مستقل.
    public function maybe_self_heal_columns(): void {
        if (get_option('dental_booking_columns_healed_v1')) return;
        if (class_exists('Dental_Booking_Installer')) {
            Dental_Booking_Installer::fix_missing_columns();
        }
        update_option('dental_booking_columns_healed_v1', 1);
    }

    public function maybe_print_day(): void {
        if (!isset($_GET['page']) || $_GET['page'] !== 'dental-booking') return;
        if (!isset($_GET['print_day'])) return;
        // رفع «باید رفرش بشه تا پرینت بیاد» — هوک‌های admin_init دیگه
        // (خودترمیمی ستون‌ها و...) ممکنه قبل از این یه خروجی کوچیک تولید
        // کرده باشن؛ این خط بافر رو پاک می‌کنه تا پاسخ از صفر شروع بشه.
        while (ob_get_level() > 0) { ob_end_clean(); }
        $cu_roles = (array)wp_get_current_user()->roles;
        if (!current_user_can('manage_options') && !array_intersect($cu_roles, ['dental_admin','dental_secretary','dental_doctor'])) {
            wp_die('دسترسی مجاز نیست.');
        }

        $date   = sanitize_text_field($_GET['date'] ?? current_time('Y-m-d'));
        $doctor = (int)($_GET['doctor'] ?? 0);
        (new Dental_Page_Booking_Calendar())->render_print_day($date, $doctor);
        exit;
    }

    public function register_menus(): void {
        // ─── چک مطمئن نقش — همون الگویی که توی کل پلاگین اصلی استفاده
        // می‌شه؛ رفع باگ: قبلاً از current_user_can('dental_manage_booking')
        // استفاده می‌شد که یه capability سفارشیه و معمولاً واقعاً grant
        // نمی‌شه، و مهم‌تر: مدیر کلینیک (dental_admin) رو هم از بخش‌های
        // ساختاری (شیفت/گزارش/تنظیمات) محروم می‌کرد، نه فقط منشی رو.
        $cu = wp_get_current_user();
        $cu_roles = (array)$cu->roles;
        $is_admin_or_manager = current_user_can('manage_options') || in_array('dental_admin', $cu_roles);

        // تقویم و لیست نوبت‌ها — کاری روزمره‌ست، پس منشی و دکتر هم دسترسی دارن.
        $daily_cap = (current_user_can('manage_options') || array_intersect($cu_roles, ['dental_admin','dental_secretary','dental_doctor'])) ? 'read' : 'do_not_allow';
        // مدیریت شیفت پزشکان، گزارش‌ها و تنظیمات — طبق درخواست صریح،
        // منشی به همه‌ی بخش‌های نوبت‌دهی (حتی ساختاری) دسترسی داره،
        // نه فقط تقویم/لیست.
        $structural_cap = (current_user_can('manage_options') || array_intersect($cu_roles, ['dental_admin','dental_secretary'])) ? 'read' : 'do_not_allow';

        add_menu_page(
            'نوبت‌دهی', 'نوبت‌دهی 📅',
            $daily_cap, 'dental-booking',
            [$this, 'render_calendar'],
            'dashicons-calendar-alt', 26
        );
        add_submenu_page('dental-booking','تقویم نوبت‌ها','تقویم',$daily_cap,
            'dental-booking', [$this, 'render_calendar']);
        add_submenu_page('dental-booking','لیست نوبت‌ها','لیست نوبت‌ها',$daily_cap,
            'dental-booking-list', fn() => (new Dental_Page_Booking_List())->render());
        add_submenu_page('dental-booking','پزشکان و شیفت‌ها','پزشکان و شیفت‌ها',$structural_cap,
            'dental-booking-doctors', fn() => (new Dental_Page_Booking_Doctors())->render());
        add_submenu_page('dental-booking','گزارش‌ها','گزارش‌ها',$structural_cap,
            'dental-booking-reports', fn() => (new Dental_Page_Booking_Reports())->render());
        add_submenu_page('dental-booking','تنظیمات نوبت','تنظیمات',$structural_cap,
            'dental-booking-settings', fn() => (new Dental_Page_Booking_Settings())->render());
    }

    public function render_calendar(): void {
        (new Dental_Page_Booking_Calendar())->render();
    }

    public function enqueue_assets(string $hook): void {
        if (strpos($hook, 'dental-booking') === false) return;

        // ─── Dental Jalali Datepicker ────────────────────────────
        wp_enqueue_script('dental-datepicker',
            plugin_dir_url(dirname(dirname(__FILE__))) . 'dental-clinic-core/assets/js/dental-datepicker.js',
            ['jquery'], DENTAL_CORE_VERSION, true);

        wp_enqueue_script('lucide',
            'https://cdn.jsdelivr.net/npm/lucide@0.408.0/dist/umd/lucide.min.js',
            [], '0.408.0', true);
        
        wp_enqueue_script('sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
            [], '11', true);

        wp_enqueue_script('dental-booking-admin',
            DENTAL_BOOKING_URL . 'assets/js/booking-admin.js',
            ['jquery','lucide','dental-datepicker'], DENTAL_BOOKING_VERSION, true);

        wp_localize_script('dental-booking-admin','dentalBooking',[
            'apiBase'       => rest_url('dental-booking/v1'),
            'nonce'         => wp_create_nonce('wp_rest'),
            'ajaxUrl'       => admin_url('admin-ajax.php'),
            'bookingNonce'  => wp_create_nonce('dental_booking_action'),
        ]);
    }
}
