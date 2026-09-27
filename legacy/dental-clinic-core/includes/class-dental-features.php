<?php
defined('ABSPATH') || exit;

/**
 * مدیریت امکانات قابل روشن/خاموش‌کردن پلاگین —
 * برای تفاوت بین «مطب شخصی» و «کلینیک چندپزشکی»
 */
class Dental_Features {

    public static function defaults(): array {
        return [
            'reception'        => true,  // پذیرش و صف انتظار
            'ledger'           => true,  // دفتر روزانه درمان
            'consent'          => true,  // رضایت‌نامه دیجیتال
            'wallet_loyalty'   => true,  // کیف پول و امتیاز وفاداری
            'advanced_reports' => true,  // گزارش‌های پیشرفته و نمودارها
            // ─── قابلیت جدید و بزرگ — عمداً پیش‌فرض خاموشه. تا وقتی
            // از همین‌جا فعالش نکنید، هیچ‌جای دیگه‌ی پلاگین (چارت،
            // پرونده‌ی بیمار) بهش دست نمی‌زنه — یعنی صفر ریسک برای
            // کارکرد فعلی سیستم.
            'treatment_pathway'=> false, // مسیر درمان چندمرحله‌ای
        ];
    }

    public static function labels(): array {
        return [
            'reception'        => ['پذیرش و صف انتظار',        'منوی «پذیرش امروز» و ویجت «صف انتظار من» در داشبورد پزشک'],
            'ledger'           => ['دفتر روزانه درمان',          'ثبت هزینه هر مراجعه و گزارش‌های آن'],
            'consent'          => ['رضایت‌نامه دیجیتال',         'فرم‌های رضایت با امضای الکترونیک برای بیمار'],
            'wallet_loyalty'   => ['کیف پول و امتیاز وفاداری',   'شارژ کیف پول، پرداخت از کیف پول و امتیاز مراجعه'],
            'advanced_reports' => ['گزارش‌های پیشرفته',          'نمودارها و گزارش تفکیکی درآمد/عملکرد'],
            'treatment_pathway'=> ['🆕 مسیر درمان چندمرحله‌ای',  'پیگیری درمان‌های زنجیره‌ای (مثلاً عصب‌کشی→روکش) با پیش‌نیاز و قالب آماده — قابلیت جدید، پیشنهاد می‌شود اول با احتیاط تست شود'],
        ];
    }

    public static function get_all(): array {
        $saved = get_option('dental_features', []);
        if (!is_array($saved)) $saved = [];
        return wp_parse_args($saved, self::defaults());
    }

    public static function enabled(string $key): bool {
        $all = self::get_all();
        return !empty($all[$key]);
    }

    public static function preset_for(string $mode): array {
        if ($mode === 'solo') {
            return [
                'reception'        => false,
                'ledger'           => true,
                'consent'          => true,
                'wallet_loyalty'   => true,
                'advanced_reports' => false,
                'treatment_pathway'=> false,
            ];
        }
        return self::defaults(); // کلینیک = همه روشن (به‌جز treatment_pathway که همیشه پیش‌فرض خاموشه)
    }
}
