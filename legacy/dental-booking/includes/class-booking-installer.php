<?php
defined('ABSPATH') || exit;

class Dental_Booking_Installer {

    public static function run(): void {
        self::create_tables();
        self::fix_missing_columns();
        self::schedule_cron();
        update_option('dental_booking_version', DENTAL_BOOKING_VERSION);
    }

    // ─── رفع صریح و مطمئن ستون‌های جامانده (dbDelta گاهی ALTER رو جا می‌ندازه) ──
    public static function fix_missing_columns(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'dental_services';
        $existing = $wpdb->get_col("SHOW COLUMNS FROM `{$table}`");
        if (!in_array('daily_capacity', $existing)) {
            $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `daily_capacity` INT DEFAULT NULL");
        }
    }

    public static function deactivate(): void {
        wp_clear_scheduled_hook('dental_booking_reminder_cron');
    }

    private static function create_tables(): void {
        global $wpdb;
        $c = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // جدول نوبت‌ها
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_appointments` (
            `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `patient_id`    BIGINT UNSIGNED  NOT NULL,
            `doctor_id`     BIGINT UNSIGNED  NOT NULL,
            `shift_id`      BIGINT UNSIGNED  DEFAULT NULL,
            `appt_date`     DATE             NOT NULL,
            `appt_date_jalali` VARCHAR(10)   NOT NULL,
            `start_time`    TIME             NOT NULL,
            `end_time`      TIME             NOT NULL,
            `duration`      SMALLINT UNSIGNED NOT NULL DEFAULT 30,
            `service_type`  VARCHAR(50)      DEFAULT NULL,
            `notes`         TEXT             DEFAULT NULL,
            `status`        VARCHAR(20)      NOT NULL DEFAULT 'pending',
            `confirmed_at`  DATETIME         DEFAULT NULL,
            `confirmed_by`  BIGINT UNSIGNED  DEFAULT NULL,
            `cancelled_at`  DATETIME         DEFAULT NULL,
            `cancel_reason` TEXT             DEFAULT NULL,
            `reminder_sent` TINYINT(1)       NOT NULL DEFAULT 0,
            `created_by`    BIGINT UNSIGNED  DEFAULT NULL,
            `created_at`    DATETIME         NOT NULL,
            `updated_at`    DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_patient`  (`patient_id`),
            KEY `idx_doctor`   (`doctor_id`),
            KEY `idx_date`     (`appt_date`),
            KEY `idx_status`   (`status`)
        ) $c;");

        // جدول شیفت‌های پزشک
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_shifts` (
            `id`           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `doctor_id`    BIGINT UNSIGNED  NOT NULL,
            `day_of_week`  TINYINT UNSIGNED NOT NULL COMMENT '0=یکشنبه 1=دوشنبه ... 6=شنبه',
            `start_time`   TIME             NOT NULL,
            `end_time`     TIME             NOT NULL,
            `slot_duration`SMALLINT UNSIGNED NOT NULL DEFAULT 30 COMMENT 'دقیقه',
            `max_patients` TINYINT UNSIGNED NOT NULL DEFAULT 1,
            `is_active`    TINYINT(1)       NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `idx_doctor` (`doctor_id`),
            KEY `idx_day`    (`day_of_week`)
        ) $c;");

        // جدول تعطیلات و مسدودی
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_blocked_dates` (
            `id`          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `doctor_id`   BIGINT UNSIGNED  DEFAULT NULL COMMENT 'NULL = همه پزشکان',
            `blocked_date`DATE             NOT NULL,
            `reason`      VARCHAR(200)     DEFAULT NULL,
            `is_active`   TINYINT(1)       NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `idx_date`   (`blocked_date`),
            KEY `idx_doctor` (`doctor_id`)
        ) $c;");

        // جدول خدمات قابل ارائه
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_services` (
            `id`          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `title`       VARCHAR(200)     NOT NULL,
            `duration`    SMALLINT UNSIGNED NOT NULL DEFAULT 30,
            `daily_capacity` INT           DEFAULT NULL COMMENT 'سقف تعداد این خدمت در روز برای هر پزشک — خالی یعنی فقط محدود به شبکه زمانی شیفت',
            `color`       VARCHAR(10)      DEFAULT '#1A6B8A',
            `is_active`   TINYINT(1)       NOT NULL DEFAULT 1,
            `sort_order`  INT UNSIGNED     NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        ) $c;");

        // درج خدمات پیش‌فرض
        $wpdb->query("INSERT IGNORE INTO {$wpdb->prefix}dental_services (title,duration,color,is_active,sort_order) VALUES
            ('ویزیت و معاینه',30,'#1A6B8A',1,1),
            ('جرمگیری',60,'#2ECC9A',1,2),
            ('ترمیم دندان',45,'#F0A500',1,3),
            ('عصب‌کشی',90,'#E05252',1,4),
            ('کشیدن دندان',30,'#8B5CF6',1,5),
            ('ایمپلنت',120,'#0F4D66',1,6),
            ('روکش و بریج',60,'#2ECC9A',1,7),
            ('مشاوره',20,'#7A96A4',1,8)
        ");

        // ─── ارتباط شیفت ↔ خدمت — طبق تصحیح کاربر: نه سطح کل پزشک،
        // بلکه سطح هر شیفت خاص (چون یه پزشک می‌تونه شنبه فقط جراحی،
        // یکشنبه فقط ترمیم کار کنه). خالی‌بودن برای یه شیفت یعنی
        // «همه‌ی خدمات توی این شیفت مجازه» (سازگاری با شیفت‌های قدیمی).
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_shift_services` (
            `id`         BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `shift_id`   BIGINT UNSIGNED  NOT NULL,
            `service_id` BIGINT UNSIGNED  NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_shift_svc` (`shift_id`,`service_id`)
        ) $c;");
    }

    private static function schedule_cron(): void {
        if (!wp_next_scheduled('dental_booking_reminder_cron')) {
            wp_schedule_event(time(), 'daily', 'dental_booking_reminder_cron');
        }
    }
}
