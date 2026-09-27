<?php
defined('ABSPATH') || exit;

// ─── آدرس مطمئن صفحه پرتال بیمار — جایگزین همه‌ی جاهایی که مستقیم
// get_page_by_path('portal-bimar') صدا می‌زدن. اگه صفحه به هر دلیلی
// (زباله‌دان/بازگردوندن، تغییر وضعیت انتشار) پیدا نشه، به‌جای برگردوندن
// یه URL خراب (که باعث می‌شد پرینت‌ها به‌جای صفحه‌ی درست، همون صفحه‌ی
// فعلی رو دوباره باز کنن)، صریح خالی برمی‌گردونه تا کدِ صدازننده بتونه
// تصمیم درست بگیره (مثلاً پیام خطای واضح بده).
if (!function_exists('dental_get_portal_url')) {
    function dental_get_portal_url(): string {
        static $cached = null;
        if ($cached !== null) return $cached;
        $page = get_page_by_path('portal-bimar', OBJECT, 'page');
        if (!$page || $page->post_status !== 'publish') { $cached = ''; return ''; }
        $url = get_permalink($page);
        $cached = $url ?: '';
        return $cached;
    }
}

final class Dental_Core {

    private static ?Dental_Core $instance = null;
    private $roles = null;
    private $api   = null;
    private $admin = null;
    private $sms   = null;

    private function __construct() {
        $this->load_config();
        $this->check_dependencies();
        $this->load_textdomain();
        $this->init_modules();
        $this->register_hooks();
    }

    public static function get_instance(): self {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }

    private function load_config(): void {
        $f = DENTAL_CORE_PATH . 'dental-config.php';
        if (file_exists($f)) require_once $f;
    }

    private function check_dependencies(): void {
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            add_action('admin_notices', function(){
                echo '<div class="notice notice-error"><p>پلاگین کلینیک دندانپزشکی نیاز به PHP 8.0+ دارد.</p></div>';
            });
        }
    }

    private function load_textdomain(): void {
        load_plugin_textdomain('dental-clinic-core', false, DENTAL_CORE_PATH . 'languages');
    }

    private function init_modules(): void {
        if (class_exists('Dental_Roles_Manager'))  $this->roles = new Dental_Roles_Manager();
        if (class_exists('Dental_SMS_Dispatcher')) $this->sms   = new Dental_SMS_Dispatcher();
        if (class_exists('Dental_API_Router'))     $this->api   = new Dental_API_Router();

        $this->maybe_init('Dental_CPT_Patient');
        $this->maybe_init('Dental_CPT_Treatment');
        $this->maybe_init('Dental_SMS_Cron');
        $this->maybe_init('Dental_Payment_Gateway');
        $this->maybe_init('Dental_Frontend');
        $this->maybe_init('Dental_Universal_Login');

        if (is_admin() && class_exists('Dental_Admin')) {
            $this->admin = new Dental_Admin();
        }
    }

    private function maybe_init(string $class): void {
        if (class_exists($class)) new $class();
    }

    private function register_hooks(): void {
        add_action('init', [$this, 'maybe_update_db']);
        add_action('init', [$this, 'maybe_register_roles']);

        // Cron Jobs
        add_action('dental_daily_cron',  [$this, 'run_daily_tasks']);
        add_action('dental_hourly_cron', [$this, 'run_hourly_tasks']);

        if (!wp_next_scheduled('dental_daily_cron')) {
            wp_schedule_event(strtotime('tomorrow 08:00:00'), 'daily', 'dental_daily_cron');
        }
        if (!wp_next_scheduled('dental_hourly_cron')) {
            wp_schedule_event(time(), 'hourly', 'dental_hourly_cron');
        }

        add_filter('dental_format_currency', [$this, 'format_currency'],    10, 2);
        add_filter('dental_format_date',     [$this, 'format_jalali_date'], 10, 2);
    }

    public function maybe_register_roles(): void {
        if (!get_option('dental_roles_registered') && class_exists('Dental_Roles_Manager')) {
            Dental_Roles_Manager::register_roles();
            update_option('dental_roles_registered', DENTAL_CORE_VERSION);
        }
    }

    public function maybe_update_db(): void {
        if (version_compare(get_option('dental_core_db_version', '0'), DENTAL_CORE_DB_VER, '<')) {
            Dental_Installer::create_tables();
            update_option('dental_core_db_version', DENTAL_CORE_DB_VER);
        }
    }

    public function run_daily_tasks(): void {
        // بررسی اقساط معوقه
        if (class_exists('Dental_Installment_Manager')) {
            Dental_Installment_Manager::check_overdue();
        }
        // پردازش صف SMS
        if ($this->sms) $this->sms->process_queue();

        do_action('dental_send_appointment_reminders');
        do_action('dental_check_overdue_installments');
    }

    public function run_hourly_tasks(): void {
        if ($this->sms) $this->sms->process_queue();
    }

    public function format_currency(float $amount, string $suffix = 'تومان'): string {
        return number_format($amount, 0, '.', ',') . ' ' . $suffix;
    }

    public function format_jalali_date(string $date, string $format = 'Y/m/d'): string {
        return class_exists('Dental_Jalali') ? Dental_Jalali::to_jalali($date, $format) : $date;
    }

    private function __clone() {}
    public function __wakeup(): void { throw new \Exception('Not allowed.'); }
}
