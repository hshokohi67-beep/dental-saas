<?php
defined('ABSPATH') || exit;

// ─── نسخه فعلی پلاگین + تاریخچه تغییرات — هر بار آپدیت مهمی دادید،
// شماره نسخه رو بالا ببرید و یه ورودی جدید به آرایه اضافه کنید.
// هر کاربر (نه فقط یه‌بار کلی) اولین ورودش بعد آپدیت، پنجره رو می‌بینه.
define('DENTAL_PLUGIN_VERSION', '2.4.0');

class Dental_Changelog {
    public static function get_log(): array {
        return [
            '2.4.0' => ['date'=>'1405/05/19','title'=>'سیستم انبار + بازنگری حضور','items'=>[
                'ماژول کامل انبار (کالاها، ورود/خروج، هشدار کسری و انقضا، درخواست کالا)',
                'بازطراحی کامل «وضعیت من و همکاران» — الان کاملاً واقعی و خودکاره',
                'ورود/خروج خودکار بر اساس فعالیت واقعی',
                'سهمیه مرخصی قابل‌تنظیم برای هرکس',
                'صفحه کامل پیام‌های داخلی (مثل چت)',
                'چندنقشی برای کاربران + انتخاب نقش هنگام ورود',
            ]],
            '2.3.0' => ['date'=>'1405/05/10','title'=>'شیفت‌بندی اتاق‌ها','items'=>[
                'برنامه ماهانه دکتر/دستیار با چیدمان خودکار',
                'برنامه هفتگی اتاق‌ها با سیستم هشدار',
                'اتصال نوبت‌دهی به برنامه ماهانه پزشک',
            ]],
        ];
    }

    public static function latest_version(): string {
        $log = self::get_log();
        return array_key_first($log);
    }

    // ─── کاربر این نسخه رو دیده یا نه؟ (per-user، نه یه‌بار کلی) ────
    public static function user_needs_to_see(int $user_id): bool {
        $seen = get_user_meta($user_id, '_dental_changelog_seen_version', true);
        return $seen !== self::latest_version();
    }

    public static function mark_seen(int $user_id): void {
        update_user_meta($user_id, '_dental_changelog_seen_version', self::latest_version());
    }
}
