<?php
defined('ABSPATH') || exit;

class Dental_Installer {

    public static function run(): void {
        self::create_tables();
        self::fix_missing_columns(); // ← رفع مطمئن ستون‌هایی که dbDelta گاهی جا می‌ندازه
        self::create_roles();
        self::create_pages();
        self::schedule_cron();
        update_option('dental_version', DENTAL_CORE_VERSION);

        // فقط اگر تا حالا ویزارد نصب اجرا نشده، یک‌بار ریدایرکت کن
        if (!get_option('dental_setup_done')) {
            set_transient('dental_activation_redirect', 1, 60);
        }
    }

    // ─── رفع صریح ستون‌های جامانده ────────────────────────────────
    // dbDelta برای ALTER جدول‌های قدیمی همیشه قابل‌اعتماد نیست (به‌خصوص
    // اگه فرمت نوشتاری دقیقاً استاندارد نباشه) — این تابع مستقیم چک
    // می‌کنه و اگه ستونی جا مونده باشه، با ALTER TABLE اضافه‌اش می‌کنه.
    public static function fix_missing_columns(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'dental_daily_ledger';

        $existing = $wpdb->get_col("SHOW COLUMNS FROM `{$table}`");

        $needed = [
            'status'                => "VARCHAR(20) NOT NULL DEFAULT 'confirmed'",
            'source'                => "VARCHAR(30) DEFAULT NULL",
            'catalog_treatment_id'  => "BIGINT UNSIGNED DEFAULT NULL",
            'discount_amount'       => "DECIMAL(12,2) NOT NULL DEFAULT 0",
        ];

        foreach ($needed as $col => $definition) {
            if (!in_array($col, $existing)) {
                $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$definition}");
            }
        }

        // ─── ستون‌های جامانده جدول اقساط — برای ردیابی پیش‌پرداخت واقعی
        // (روش پرداخت + کد رهگیری) که قبلاً فقط یه عدد بی‌ربط بود ────
        $inst_table = $wpdb->prefix . 'dental_installments';
        $inst_existing = $wpdb->get_col("SHOW COLUMNS FROM `{$inst_table}`");
        $inst_needed = [
            'down_payment_method' => "VARCHAR(20) DEFAULT NULL",
            'down_payment_ref'    => "VARCHAR(100) DEFAULT NULL",
            'ledger_id'           => "BIGINT UNSIGNED DEFAULT NULL COMMENT 'اتصال به رکورد دفتر روزانه‌ای که این قسط ازش ساخته شده'",
        ];
        foreach ($inst_needed as $col => $definition) {
            if (!in_array($col, $inst_existing)) {
                $wpdb->query("ALTER TABLE `{$inst_table}` ADD COLUMN `{$col}` {$definition}");
            }
        }

        // ─── ستون‌های بیمه روی جدول ثبت خدمات — کدوم بیمه، سهم بیمار/بیمه ──
        $treat_table = $wpdb->prefix . 'dental_catalog_treatments';
        $treat_existing = $wpdb->get_col("SHOW COLUMNS FROM `{$treat_table}`");
        $treat_needed = [
            'insurance_id'      => "BIGINT UNSIGNED DEFAULT NULL",
            'insurance_share'   => "BIGINT UNSIGNED DEFAULT NULL",
            'patient_share'     => "BIGINT UNSIGNED DEFAULT NULL",
            'insurance_docs_status' => "VARCHAR(20) DEFAULT NULL COMMENT 'pending|complete — وضعیت مدارک لازم بیمه'",
        ];
        foreach ($treat_needed as $col => $definition) {
            if (!in_array($col, $treat_existing)) {
                $wpdb->query("ALTER TABLE `{$treat_table}` ADD COLUMN `{$col}` {$definition}");
            }
        }

        // ─── نام انگلیسی/علمی دارو — برای اینکه داروخانه‌ها روی نسخه
        // اشکال نگیرن (اکثراً بر پایه‌ی اسم ژنریک انگلیسی کار می‌کنن) ────
        $drugs_table = $wpdb->prefix . 'dental_drugs';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$drugs_table}'") === $drugs_table) {
            $drugs_existing = $wpdb->get_col("SHOW COLUMNS FROM `{$drugs_table}`");
            if (!in_array('english_name', $drugs_existing)) {
                $wpdb->query("ALTER TABLE `{$drugs_table}` ADD COLUMN `english_name` VARCHAR(150) DEFAULT NULL COMMENT 'نام ژنریک/علمی انگلیسی برای داروخانه' AFTER `name`");
            }
        }
        $rx_items_table = $wpdb->prefix . 'dental_prescription_items';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$rx_items_table}'") === $rx_items_table) {
            $rx_items_existing = $wpdb->get_col("SHOW COLUMNS FROM `{$rx_items_table}`");
            if (!in_array('english_name_snapshot', $rx_items_existing)) {
                $wpdb->query("ALTER TABLE `{$rx_items_table}` ADD COLUMN `english_name_snapshot` VARCHAR(150) DEFAULT NULL AFTER `drug_name_snapshot`");
            }
        }

        // ─── همون مشکل، این‌بار جدول تسک‌ها (چک‌لیست کارها) ────────────
        $tasks_table = $wpdb->prefix . 'dental_tasks';
        $tasks_existing = $wpdb->get_col("SHOW COLUMNS FROM `{$tasks_table}`");
        $tasks_needed = [
            'due_date' => "DATE DEFAULT NULL",
            'done_at'  => "DATETIME DEFAULT NULL",
        ];
        foreach ($tasks_needed as $col => $definition) {
            if (!in_array($col, $tasks_existing)) {
                $wpdb->query("ALTER TABLE `{$tasks_table}` ADD COLUMN `{$col}` {$definition}");
            }
        }

        // ─── جدول حضور — ستون تشخیص خروج خودکار استنتاجی ────────────
        $att_table = $wpdb->prefix . 'dental_attendance';
        $att_existing = $wpdb->get_col("SHOW COLUMNS FROM `{$att_table}`");
        if (!in_array('auto_closed', $att_existing)) {
            $wpdb->query("ALTER TABLE `{$att_table}` ADD COLUMN `auto_closed` TINYINT(1) NOT NULL DEFAULT 0");
        }

        // ─── برنامه ماهانه شیفت — ستون قفل چیدمان خودکار. dbDelta برای
        // این جدول هم (مثل بقیه) قابل‌اعتماد نبود؛ نبودنش باعث خطای
        // دیتابیس در هر جایی می‌شد که برنامه ماهانه خونده می‌شه (از جمله
        // نوبت‌دهی آنلاین، وقتی می‌خواد ببینه پزشک این ماه شیفت داره یا نه) ──
        $rota_table = $wpdb->prefix . 'dental_monthly_rota';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$rota_table}'") === $rota_table) {
            $rota_existing = $wpdb->get_col("SHOW COLUMNS FROM `{$rota_table}`");
            if (!in_array('is_locked', $rota_existing)) {
                $wpdb->query("ALTER TABLE `{$rota_table}` ADD COLUMN `is_locked` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'قفل‌شده = چیدمان خودکار دست نمی‌زنه'");
            }
        }
    }

    public static function deactivate(): void {
        wp_clear_scheduled_hook('dental_sms_cron');
        wp_clear_scheduled_hook('dental_daily_cron');
    }

    // ─── حذف پلاگین (uninstall.php) ──────────────────────────────
    // عمداً هیچ جدول یا پست بیماری (پرونده پزشکی/مالی) را پاک نمی‌کند:
    // «حذف پلاگین» در وردپرس با چند کلیک انجام می‌شود و نباید بی‌برگشت
    // داده‌ی حساس کلینیک را نابود کند. فقط چیزهای بی‌خطر و بازسازی‌پذیر
    // (نقش‌های سفارشی، کرون‌جاب‌ها، فلگ‌های داخلی) پاک می‌شوند؛ جدول‌ها و
    // پرونده‌ها دست‌نخورده می‌مانند تا اگر پلاگین دوباره نصب شد، چیزی از
    // دست نرفته باشد.
    public static function uninstall(): void {
        if (class_exists('Dental_Roles_Manager')) {
            Dental_Roles_Manager::remove_roles();
        }
        // Roles_Manager فقط ۴ نقش را می‌شناسد؛ dental_financial/dental_assistant
        // را create_roles() این فایل جداگانه می‌سازد، پس اینجا هم صریح حذف می‌شوند.
        foreach (['dental_financial', 'dental_assistant'] as $extra_role) {
            remove_role($extra_role);
        }

        wp_clear_scheduled_hook('dental_sms_cron');
        wp_clear_scheduled_hook('dental_daily_cron');
        wp_clear_scheduled_hook('dental_hourly_cron');
        wp_clear_scheduled_hook('dental_auto_backup_cron');

        delete_option('dental_version');
        delete_option('dental_roles_registered');
        delete_option('dental_setup_done');
        delete_option('dental_core_db_version');
        delete_transient('dental_activation_redirect');
    }

    // ─── جداول ──────────────────────────────────────────────
    public static function create_tables(): void {
        global $wpdb;
        $c = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_otp_codes` (
            `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `mobile`     VARCHAR(15)     NOT NULL,
            `otp_code`   VARCHAR(10)     NOT NULL,
            `purpose`    VARCHAR(20)     NOT NULL DEFAULT 'login',
            `is_used`    TINYINT(1)      NOT NULL DEFAULT 0,
            `created_at` DATETIME        NOT NULL,
            `expires_at` DATETIME        NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_mobile` (`mobile`),
            KEY `idx_expires` (`expires_at`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_installments` (
            `id`                 BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `patient_id`         BIGINT UNSIGNED  NOT NULL,
            `treatment_id`       BIGINT UNSIGNED  DEFAULT NULL,
            `total_amount`       DECIMAL(12,2)    NOT NULL DEFAULT 0,
            `down_payment`       DECIMAL(12,2)    NOT NULL DEFAULT 0,
            `remaining_amount`   DECIMAL(12,2)    NOT NULL DEFAULT 0,
            `installment_count`  INT UNSIGNED     NOT NULL DEFAULT 1,
            `installment_amount` DECIMAL(12,2)    NOT NULL DEFAULT 0,
            `discount_amount`    DECIMAL(12,2)    NOT NULL DEFAULT 0,
            `status`             VARCHAR(20)      NOT NULL DEFAULT 'active',
            `notes`              TEXT             DEFAULT NULL,
            `created_at`         DATETIME         NOT NULL,
            `updated_at`         DATETIME         NOT NULL,
            `created_by`         BIGINT UNSIGNED  DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_patient` (`patient_id`),
            KEY `idx_status`  (`status`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_installment_items` (
            `id`               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `installment_id`   BIGINT UNSIGNED  NOT NULL,
            `item_number`      INT UNSIGNED     NOT NULL,
            `due_date_jalali`  VARCHAR(10)      NOT NULL,
            `due_date`         DATE             NOT NULL,
            `amount`           DECIMAL(12,2)    NOT NULL DEFAULT 0,
            `paid_amount`      DECIMAL(12,2)    NOT NULL DEFAULT 0,
            `paid_date`        DATETIME         DEFAULT NULL,
            `payment_method`   VARCHAR(30)      DEFAULT NULL,
            `transaction_ref`  VARCHAR(100)     DEFAULT NULL,
            `receipt_image_id` BIGINT UNSIGNED  DEFAULT NULL,
            `reminder_sent_at` DATETIME         DEFAULT NULL,
            `reminder_count`   INT UNSIGNED     DEFAULT 0,
            `notes`            TEXT             DEFAULT NULL,
            `status`           VARCHAR(20)      NOT NULL DEFAULT 'pending',
            PRIMARY KEY (`id`),
            KEY `idx_installment` (`installment_id`),
            KEY `idx_due_date`    (`due_date`),
            KEY `idx_status`      (`status`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_wallet_transactions` (
            `id`               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `patient_id`       BIGINT UNSIGNED  NOT NULL,
            `amount`           DECIMAL(12,2)    NOT NULL,
            `transaction_type` VARCHAR(20)      NOT NULL DEFAULT 'credit',
            `source`           VARCHAR(50)      DEFAULT NULL,
            `reference_id`     BIGINT UNSIGNED  DEFAULT NULL,
            `description`      TEXT             DEFAULT NULL,
            `created_by`       BIGINT UNSIGNED  DEFAULT NULL,
            `created_at`       DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_patient` (`patient_id`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_tooth_conditions` (
            `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `patient_id`   BIGINT UNSIGNED NOT NULL,
            `tooth_number` TINYINT UNSIGNED NOT NULL,
            `tooth_type`   VARCHAR(10)     NOT NULL DEFAULT 'permanent',
            `tooth_surface`TEXT            DEFAULT NULL,
            `condition_data`TEXT           DEFAULT NULL,
            `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
            `created_at`   DATETIME        NOT NULL,
            `updated_at`   DATETIME        NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_patient_tooth` (`patient_id`,`tooth_number`,`tooth_type`),
            KEY `idx_patient` (`patient_id`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_sms_log` (
            `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `patient_id`   BIGINT UNSIGNED DEFAULT NULL,
            `mobile`       VARCHAR(15)     NOT NULL,
            `message`      TEXT            NOT NULL,
            `trigger_type` VARCHAR(50)     DEFAULT NULL,
            `gateway`      VARCHAR(30)     DEFAULT NULL,
            `status`       VARCHAR(20)     NOT NULL DEFAULT 'queued',
            `gateway_ref`  VARCHAR(100)    DEFAULT NULL,
            `retry_count`  INT UNSIGNED    NOT NULL DEFAULT 0,
            `scheduled_at` DATETIME        DEFAULT NULL,
            `sent_at`      DATETIME        DEFAULT NULL,
            `created_at`   DATETIME        NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_status`  (`status`),
            KEY `idx_patient` (`patient_id`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_doctor_shifts` (
            `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `doctor_id`  BIGINT UNSIGNED NOT NULL,
            `day_of_week`TINYINT UNSIGNED NOT NULL,
            `start_time` TIME            NOT NULL,
            `end_time`   TIME            NOT NULL,
            `is_active`  TINYINT(1)      NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `idx_doctor` (`doctor_id`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_holidays` (
            `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `holiday_date`DATE            NOT NULL,
            `title`       VARCHAR(200)    DEFAULT NULL,
            `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_date` (`holiday_date`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_daily_ledger` (
            `id`                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `patient_id`        BIGINT UNSIGNED  NOT NULL,
            `appointment_id`    BIGINT UNSIGNED  DEFAULT NULL,
            `doctor_id`         BIGINT UNSIGNED  DEFAULT NULL,
            `treatment_title`   VARCHAR(200)     NOT NULL,
            `amount_charged`    DECIMAL(12,2)    NOT NULL DEFAULT 0,
            `amount_received`   DECIMAL(12,2)    NOT NULL DEFAULT 0,
            `payment_method`    VARCHAR(30)      DEFAULT NULL,
            `entry_date`        DATE             NOT NULL,
            `entry_date_jalali` VARCHAR(10)      NOT NULL,
            `notes`             TEXT             DEFAULT NULL,
            `status`            VARCHAR(20)      NOT NULL DEFAULT 'confirmed' COMMENT 'pending|confirmed — pending یعنی خودکار از کاتالوگ ساخته شده و منتظر تأیید مالیه',
            `source`            VARCHAR(30)      DEFAULT NULL COMMENT 'manual|catalog',
            `catalog_treatment_id` BIGINT UNSIGNED DEFAULT NULL,
            `created_by`        BIGINT UNSIGNED  DEFAULT NULL,
            `created_at`        DATETIME         NOT NULL,
            `updated_at`        DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_date`        (`entry_date`),
            KEY `idx_patient`     (`patient_id`),
            KEY `idx_appointment` (`appointment_id`),
            KEY `idx_status`      (`status`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_reception_queue` (
            `id`               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `patient_id`       BIGINT UNSIGNED  NOT NULL,
            `doctor_id`        BIGINT UNSIGNED  NOT NULL,
            `appointment_id`   BIGINT UNSIGNED  DEFAULT NULL,
            `reason`           VARCHAR(200)     DEFAULT NULL,
            `status`           VARCHAR(20)      NOT NULL DEFAULT 'waiting',
            `queue_date`       DATE             NOT NULL,
            `queue_date_jalali`VARCHAR(10)      NOT NULL,
            `checked_in_at`    DATETIME         NOT NULL,
            `started_at`       DATETIME         DEFAULT NULL,
            `finished_at`      DATETIME         DEFAULT NULL,
            `created_by`       BIGINT UNSIGNED  DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_date`   (`queue_date`),
            KEY `idx_doctor` (`doctor_id`),
            KEY `idx_status` (`status`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_tasks` (
            `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `title`         VARCHAR(255)     NOT NULL,
            `assigned_to`   BIGINT UNSIGNED  NOT NULL,
            `assigned_by`   BIGINT UNSIGNED  NOT NULL,
            `due_date`      DATE             DEFAULT NULL COMMENT 'تاریخ مربوطه — خالی یعنی هر زمان',
            `is_done`       TINYINT(1)       NOT NULL DEFAULT 0,
            `done_at`       DATETIME         DEFAULT NULL,
            `created_at`    DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_assigned_to` (`assigned_to`),
            KEY `idx_assigned_by` (`assigned_by`),
            KEY `idx_due_date` (`due_date`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_messages` (
            `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `from_user_id`  BIGINT UNSIGNED  NOT NULL,
            `to_user_id`    BIGINT UNSIGNED  NOT NULL DEFAULT 0 COMMENT '0 = پیام عمومی برای همه',
            `message`       TEXT             NOT NULL,
            `is_read`       TINYINT(1)       NOT NULL DEFAULT 0,
            `created_at`    DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_to`   (`to_user_id`),
            KEY `idx_from` (`from_user_id`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_attendance` (
            `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `user_id`       BIGINT UNSIGNED  NOT NULL,
            `work_date`     DATE             NOT NULL,
            `clock_in`      DATETIME         NOT NULL,
            `clock_out`     DATETIME         DEFAULT NULL,
            `total_minutes` INT UNSIGNED     DEFAULT NULL,
            `created_at`    DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user_date` (`user_id`,`work_date`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_breaks` (
            `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `user_id`       BIGINT UNSIGNED  NOT NULL,
            `work_date`     DATE             NOT NULL,
            `break_type`    VARCHAR(20)      NOT NULL DEFAULT 'break' COMMENT 'lunch|break',
            `start_time`    DATETIME         NOT NULL,
            `end_time`      DATETIME         DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user_date` (`user_id`,`work_date`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_leave_requests` (
            `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `user_id`       BIGINT UNSIGNED  NOT NULL,
            `leave_type`    VARCHAR(20)      NOT NULL DEFAULT 'daily' COMMENT 'daily|hourly',
            `date_from`     DATE             NOT NULL,
            `date_to`       DATE             NOT NULL,
            `hours`         DECIMAL(5,2)     DEFAULT NULL COMMENT 'فقط برای مرخصی ساعتی',
            `days_count`    DECIMAL(5,2)     NOT NULL DEFAULT 1,
            `reason`        TEXT             DEFAULT NULL,
            `status`        VARCHAR(20)      NOT NULL DEFAULT 'pending' COMMENT 'pending|approved|rejected',
            `admin_note`    VARCHAR(255)     DEFAULT NULL,
            `reviewed_by`   BIGINT UNSIGNED  DEFAULT NULL,
            `reviewed_at`   DATETIME         DEFAULT NULL,
            `created_at`    DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user`   (`user_id`),
            KEY `idx_status` (`status`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_doctor_activity` (
            `id`             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `doctor_id`      BIGINT UNSIGNED  NOT NULL,
            `patient_id`     BIGINT UNSIGNED  NOT NULL,
            `activity_type`  VARCHAR(30)      NOT NULL COMMENT 'exam_done|treatment_done',
            `description`    VARCHAR(255)     DEFAULT NULL,
            `created_at`     DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_doctor` (`doctor_id`),
            KEY `idx_patient`(`patient_id`),
            KEY `idx_date`   (`created_at`)
        ) $c;");

        // ─── کاتالوگ خدمات دندانپزشکی (درختی) — جایگزین لیست ساده قبلی ──
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_service_catalog` (
            `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `parent_id`     BIGINT UNSIGNED  DEFAULT NULL,
            `name`          VARCHAR(500)     NOT NULL,
            `is_leaf`       TINYINT(1)       NOT NULL DEFAULT 0,
            `tooth_filter`  VARCHAR(20)      NOT NULL DEFAULT 'all' COMMENT 'all|anterior|posterior|premolar|molar',
            `requires_consent` TINYINT(1)    NOT NULL DEFAULT 0,
            `sort_order`    INT              NOT NULL DEFAULT 0,
            `is_active`     TINYINT(1)       NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `idx_parent` (`parent_id`),
            KEY `idx_leaf`   (`is_leaf`)
        ) $c;");

        // ─── قیمت‌گذاری هر برگ کاتالوگ (مستقل، توسط مدیر تنظیم می‌شود) ──
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_service_pricing` (
            `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `catalog_id`    BIGINT UNSIGNED  NOT NULL,
            `price`         BIGINT UNSIGNED  NOT NULL DEFAULT 0,
            `patient_share` BIGINT UNSIGNED  DEFAULT NULL COMMENT 'اگر خالی باشد، کل مبلغ سهم بیمار است',
            `updated_at`    DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_catalog` (`catalog_id`)
        ) $c;");

        // ─── درمان‌های ثبت‌شده از کاتالوگ (جدا از سیستم ساده قبلی) ──
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_catalog_treatments` (
            `id`             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `patient_id`     BIGINT UNSIGNED  NOT NULL,
            `tooth_number`   INT              DEFAULT NULL COMMENT 'خالی برای خدمات کلی مثل رادیوگرافی',
            `catalog_id`     BIGINT UNSIGNED  NOT NULL,
            `service_name`   VARCHAR(500)     NOT NULL COMMENT 'اسنپ‌شات نام در لحظه ثبت',
            `price`          BIGINT UNSIGNED  NOT NULL DEFAULT 0 COMMENT 'اسنپ‌شات قیمت در لحظه ثبت',
            `doctor_id`      BIGINT UNSIGNED  NOT NULL,
            `recorded_date`  DATE             NOT NULL,
            `is_done`        TINYINT(1)       NOT NULL DEFAULT 0,
            `done_at`        DATETIME         DEFAULT NULL,
            `done_by`        BIGINT UNSIGNED  DEFAULT NULL,
            `created_at`     DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_patient` (`patient_id`),
            KEY `idx_doctor`  (`doctor_id`)
        ) $c;");

        // ─── سیستم زمان‌بندی اتاق و چرخش پرسنل (شیفت‌بندی ماهانه) ──────
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_shift_rooms` (
            `id`             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `room_number`    INT              NOT NULL,
            `active_morning` TINYINT(1)       NOT NULL DEFAULT 1,
            `active_evening` TINYINT(1)       NOT NULL DEFAULT 1,
            `fixed_doctor_morning` BIGINT UNSIGNED DEFAULT NULL,
            `fixed_doctor_evening` BIGINT UNSIGNED DEFAULT NULL,
            `sort_order`     INT              NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        ) $c;");

        // هر سلول = یک اتاق (یا پذیرش/تحویل‌وسایل/آزاد) در یک تاریخ و شیفت خاص
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_shift_assignments` (
            `id`               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `work_date`        DATE             NOT NULL,
            `shift`            VARCHAR(1)       NOT NULL COMMENT 'm=صبح, e=عصر',
            `slot_key`         VARCHAR(20)      NOT NULL COMMENT 'r1..r10 یا recv/handover/free',
            `doctor_id`        BIGINT UNSIGNED  DEFAULT NULL,
            `assistant_id`     BIGINT UNSIGNED  DEFAULT NULL,
            `is_double`        TINYINT(1)       NOT NULL DEFAULT 0,
            `doctor2_id`       BIGINT UNSIGNED  DEFAULT NULL,
            `assistant2_id`    BIGINT UNSIGNED  DEFAULT NULL,
            `single_assistant` TINYINT(1)       NOT NULL DEFAULT 0,
            `extra_data`       TEXT             DEFAULT NULL COMMENT 'JSON — برای پذیرش: assistantIds[]+radiologyId',
            `is_locked`        TINYINT(1)       NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_slot` (`work_date`,`shift`,`slot_key`)
        ) $c;");
        // برنامه ماهانه — کدام روز/شیفت هر پزشک یا دستیار طبق برنامه کار می‌کند
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_monthly_rota` (
            `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `person_id`     BIGINT UNSIGNED  NOT NULL,
            `person_type`   VARCHAR(10)      NOT NULL COMMENT 'doctor|assistant',
            `work_date`     DATE             NOT NULL,
            `shift`         VARCHAR(1)       NOT NULL COMMENT 'm|e|l|d (d=دوشیفتی)',
            `is_locked`     TINYINT(1)       NOT NULL DEFAULT 0 COMMENT 'قفل‌شده = چیدمان خودکار دست نمی‌زنه',
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_unique` (`person_id`,`person_type`,`work_date`)
        ) $c;");
        // ─── ماژول انبار ──────────────────────────────────────────────
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_inventory_categories` (
            `id`         BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `name`       VARCHAR(150)     NOT NULL,
            `sort_order` INT              NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_inventory_items` (
            `id`               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `category_id`      BIGINT UNSIGNED  DEFAULT NULL,
            `name`             VARCHAR(200)     NOT NULL,
            `unit`             VARCHAR(30)      NOT NULL DEFAULT 'عدد',
            `current_stock`    DECIMAL(10,2)    NOT NULL DEFAULT 0,
            `min_stock`        DECIMAL(10,2)    NOT NULL DEFAULT 0,
            `unit_cost`        BIGINT UNSIGNED  NOT NULL DEFAULT 0,
            `supplier_name`    VARCHAR(150)     DEFAULT NULL,
            `supplier_phone`   VARCHAR(20)       DEFAULT NULL,
            `nearest_expiry`   DATE             DEFAULT NULL,
            `is_active`        TINYINT(1)       NOT NULL DEFAULT 1,
            `created_at`       DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_category` (`category_id`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_inventory_transactions` (
            `id`             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `item_id`        BIGINT UNSIGNED  NOT NULL,
            `type`           VARCHAR(3)       NOT NULL COMMENT 'in|out',
            `quantity`       DECIMAL(10,2)    NOT NULL,
            `batch_expiry`   DATE             DEFAULT NULL COMMENT 'فقط برای ورود — تاریخ انقضای این محموله',
            `unit_cost`      BIGINT UNSIGNED  DEFAULT NULL COMMENT 'قیمت واحد در لحظه این تراکنش',
            `patient_id`     BIGINT UNSIGNED  DEFAULT NULL COMMENT 'فقط برای خروج — اگه مربوط به یه بیمار خاص بود',
            `note`           VARCHAR(255)     DEFAULT NULL,
            `recorded_by`    BIGINT UNSIGNED  NOT NULL,
            `created_at`     DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_item` (`item_id`)
        ) $c;");

        // ─── درخواست کالا — هر پرسنلی (هر نقشی) می‌تونه اعلام کسری بده ──
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_inventory_requests` (
            `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `item_id`       BIGINT UNSIGNED  NOT NULL,
            `requested_by`  BIGINT UNSIGNED  NOT NULL,
            `note`          VARCHAR(255)     DEFAULT NULL COMMENT 'مثلاً: حدود ۲ بسته لازم داریم',
            `status`        VARCHAR(20)      NOT NULL DEFAULT 'pending' COMMENT 'pending|fulfilled|rejected',
            `resolved_by`   BIGINT UNSIGNED  DEFAULT NULL,
            `resolved_at`   DATETIME         DEFAULT NULL,
            `created_at`    DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_item` (`item_id`),
            KEY `idx_status` (`status`)
        ) $c;");

        // ─── سیستم ریکال — یادآوری چکاپ دوره‌ای (حفظ مشتری) ────────────
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_recall_rules` (
            `id`                 BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `name`               VARCHAR(150)     NOT NULL COMMENT 'مثلاً: یادآور جرم‌گیری ۶ ماهه',
            `trigger_catalog_id` BIGINT UNSIGNED  DEFAULT NULL COMMENT 'کدوم خدمت کاتالوگ فعالش می‌کنه — خالی=هر خدمتی',
            `recall_months`      INT              NOT NULL DEFAULT 6,
            `sms_template`       TEXT             DEFAULT NULL,
            `is_active`          TINYINT(1)       NOT NULL DEFAULT 1,
            `created_at`         DATETIME         NOT NULL,
            PRIMARY KEY (`id`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_recall_log` (
            `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `patient_id`    BIGINT UNSIGNED  NOT NULL,
            `rule_id`       BIGINT UNSIGNED  NOT NULL,
            `due_date`      DATE             NOT NULL,
            `sms_sent_at`   DATETIME         DEFAULT NULL,
            `status`        VARCHAR(20)      NOT NULL DEFAULT 'due' COMMENT 'due|sent|dismissed|booked',
            `created_at`    DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_patient` (`patient_id`),
            KEY `idx_status` (`status`)
        ) $c;");

        // ─── لاگ فعالیت — کی، کِی، چه کاری انجام داده ────────────────
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_audit_log` (
            `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `user_id`       BIGINT UNSIGNED  DEFAULT NULL,
            `user_name`     VARCHAR(150)     DEFAULT NULL COMMENT 'اسنپ‌شات — حتی اگه بعداً کاربر حذف بشه، اسم می‌مونه',
            `action_type`   VARCHAR(50)      NOT NULL COMMENT 'مثلاً: ledger_confirm, installment_create, wallet_credit',
            `entity_type`   VARCHAR(50)      DEFAULT NULL COMMENT 'مثلاً: patient, installment, ledger',
            `entity_id`     BIGINT UNSIGNED  DEFAULT NULL,
            `description`   TEXT             DEFAULT NULL,
            `amount`        DECIMAL(12,2)    DEFAULT NULL COMMENT 'اگه مبلغی درگیر بود',
            `ip_address`    VARCHAR(45)      DEFAULT NULL,
            `created_at`    DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user`   (`user_id`),
            KEY `idx_action` (`action_type`),
            KEY `idx_entity` (`entity_type`,`entity_id`),
            KEY `idx_date`   (`created_at`)
        ) $c;");

        // ─── بیمه — شرکت‌های طرف‌قرارداد + تعرفه هر خدمت برای هرکدوم ──
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_insurance_companies` (
            `id`               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `name`             VARCHAR(150)     NOT NULL COMMENT 'مثلاً: البرز، آسیا، دی',
            `type`             VARCHAR(20)      NOT NULL DEFAULT 'supplementary' COMMENT 'supplementary|base',
            `default_franchise_percent` DECIMAL(5,2) NOT NULL DEFAULT 20 COMMENT 'فرانشیز پیش‌فرض — قابل بازنویسی per-tariff',
            `contact_info`     TEXT             DEFAULT NULL,
            `is_active`        TINYINT(1)       NOT NULL DEFAULT 1,
            `created_at`       DATETIME         NOT NULL,
            PRIMARY KEY (`id`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_insurance_tariffs` (
            `id`                 BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `insurance_id`       BIGINT UNSIGNED  NOT NULL,
            `catalog_id`         BIGINT UNSIGNED  NOT NULL COMMENT 'اتصال به برگ کاتالوگ خدمات',
            `approved_tariff`    BIGINT UNSIGNED  NOT NULL COMMENT 'تعرفه مصوب سندیکا برای این خدمت نزد این بیمه',
            `franchise_percent`  DECIMAL(5,2)     DEFAULT NULL COMMENT 'اگه خالی بود از پیش‌فرض بیمه استفاده می‌شه',
            `requires_docs`      VARCHAR(255)     DEFAULT NULL COMMENT 'کلیدهای مدارک لازم، کاما جدا: xray_before,xray_after,initial_exam',
            `updated_at`         DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_ins_cat` (`insurance_id`,`catalog_id`)
        ) $c;");

        // ─── تسویه دوره‌ای با بیمه — برای کلینیک‌هایی که قرارداد مستقیم
        // دارن و باید ماهانه یه لیست تجمیعی به بیمه بفرستن ────────────
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_insurance_settlements` (
            `id`               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `insurance_id`     BIGINT UNSIGNED  NOT NULL,
            `period_from`      DATE             NOT NULL,
            `period_to`        DATE             NOT NULL,
            `total_amount`     BIGINT UNSIGNED  NOT NULL DEFAULT 0 COMMENT 'جمع سهم بیمه این دوره',
            `treatment_count`  INT UNSIGNED     NOT NULL DEFAULT 0,
            `status`           VARCHAR(20)      NOT NULL DEFAULT 'draft' COMMENT 'draft|submitted|paid',
            `submitted_at`     DATETIME         DEFAULT NULL,
            `paid_at`          DATETIME         DEFAULT NULL,
            `paid_amount`      BIGINT UNSIGNED  DEFAULT NULL COMMENT 'مبلغی که واقعاً واریز شده — ممکنه با total_amount فرق کنه',
            `reference_no`     VARCHAR(100)     DEFAULT NULL COMMENT 'شماره پیگیری/نامه ارسالی به بیمه',
            `notes`            TEXT             DEFAULT NULL,
            `created_by`       BIGINT UNSIGNED  DEFAULT NULL,
            `created_at`       DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_insurance` (`insurance_id`),
            KEY `idx_status`    (`status`)
        ) $c;");

        // ─── اتصال هر رکورد ثبت خدمت به یه دسته‌ی تسویه خاص — تا معلوم
        // باشه کدوم خدمت‌ها توی کدوم درخواست تسویه لحاظ شدن ────────────
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_insurance_settlement_items` (
            `id`             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `settlement_id`  BIGINT UNSIGNED  NOT NULL,
            `treatment_id`   BIGINT UNSIGNED  NOT NULL COMMENT 'اتصال به dental_catalog_treatments',
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_settle_treat` (`settlement_id`,`treatment_id`)
        ) $c;");

        // ═══════════════ سیستم Imaging (فاز ۱ — بدون DICOM واقعی) ══════
        // معماری طوری طراحی شده که فاز ۲ (اتصال واقعی DICOM/PACS/Cornerstone3D)
        // بدون تغییر ساختار جدول‌ها روش سوار بشه — همین الان ستون‌های
        // is_dicom و modality برای همین آماده‌ان.
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_imaging_studies` (
            `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `patient_id`    BIGINT UNSIGNED  NOT NULL,
            `doctor_id`     BIGINT UNSIGNED  DEFAULT NULL,
            `appointment_id`BIGINT UNSIGNED  DEFAULT NULL,
            `treatment_id`  BIGINT UNSIGNED  DEFAULT NULL,
            `study_date`    DATE             NOT NULL,
            `modality`      VARCHAR(30)      NOT NULL COMMENT 'periapical|bitewing|panoramic|cephalometric|cbct|intraoral_photo|extraoral_photo|clinical_photo|other',
            `description`   VARCHAR(255)     DEFAULT NULL,
            `created_by`    BIGINT UNSIGNED  DEFAULT NULL,
            `created_at`    DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_patient` (`patient_id`),
            KEY `idx_treatment` (`treatment_id`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_imaging_images` (
            `id`             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `study_id`       BIGINT UNSIGNED  NOT NULL,
            `file_path`      VARCHAR(500)     NOT NULL COMMENT 'مسیر نسبی — خارج از uploads عمومی وردپرس',
            `thumbnail_path` VARCHAR(500)     DEFAULT NULL,
            `file_type`      VARCHAR(10)      NOT NULL COMMENT 'jpg|png|dcm',
            `is_dicom`       TINYINT(1)       NOT NULL DEFAULT 0 COMMENT 'فاز ۲ — الان همیشه ۰',
            `tooth_number`   INT              DEFAULT NULL COMMENT 'همون کدگذاری FDI/کوادرانتی فعلی پلاگین',
            `before_after`   VARCHAR(10)      DEFAULT NULL COMMENT 'before|after|null',
            `width`          INT              DEFAULT NULL,
            `height`         INT              DEFAULT NULL,
            `uploaded_by`    BIGINT UNSIGNED  DEFAULT NULL,
            `created_at`     DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_study` (`study_id`),
            KEY `idx_tooth` (`tooth_number`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_imaging_annotations` (
            `id`          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `image_id`    BIGINT UNSIGNED  NOT NULL,
            `type`        VARCHAR(20)      NOT NULL COMMENT 'arrow|line|rect|circle|text|tooth_label|freehand',
            `data`        TEXT             NOT NULL COMMENT 'JSON مختصات/متن — نسبی به ابعاد تصویر (۰ تا ۱) نه پیکسل مطلق',
            `color`       VARCHAR(20)      DEFAULT '#E05252',
            `created_by`  BIGINT UNSIGNED  DEFAULT NULL,
            `created_at`  DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_image` (`image_id`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_imaging_measurements` (
            `id`          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `image_id`    BIGINT UNSIGNED  NOT NULL,
            `type`        VARCHAR(20)      NOT NULL COMMENT 'distance|angle',
            `points`      TEXT             NOT NULL COMMENT 'JSON مختصات نسبی نقاط',
            `value`       DECIMAL(10,2)    DEFAULT NULL COMMENT 'مقدار خام پیکسلی نسبی — فاز ۱ واحد میلی‌متر نداره چون Pixel Spacing واقعی (DICOM) نیست',
            `unit`        VARCHAR(10)      DEFAULT 'px' COMMENT 'فقط px تا وقتی DICOM واقعی نداریم — هیچ‌وقت mm جعلی نمی‌سازیم',
            `created_by`  BIGINT UNSIGNED  DEFAULT NULL,
            `created_at`  DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_image` (`image_id`)
        ) $c;");

        // ─── مدارک بیمه — هر مدرک لازم (رادیوگرافی قبل/بعد، گواهی
        // معاینه و...) جدا برای هر رکورد ثبت‌خدمت ردیابی می‌شه، با
        // فایل واقعی آپلودشده (اگه باشه) — از همون پوشه‌ی امن Imaging
        // استفاده می‌کنه، سیستم ذخیره‌سازی جدا نمی‌سازه ────────────────
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_insurance_docs` (
            `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `treatment_id`  BIGINT UNSIGNED  NOT NULL COMMENT 'اتصال به dental_catalog_treatments',
            `doc_type`      VARCHAR(30)      NOT NULL COMMENT 'xray_before|xray_after|initial_exam|pos_receipt',
            `file_path`     VARCHAR(500)     DEFAULT NULL COMMENT 'خالی یعنی هنوز آپلود نشده',
            `uploaded_by`   BIGINT UNSIGNED  DEFAULT NULL,
            `uploaded_at`   DATETIME         DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_treat_doc` (`treatment_id`,`doc_type`)
        ) $c;");

        // ═══════════════ نسخه‌نویسی دارویی ═══════════════════════════
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_drugs` (
            `id`               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `name`             VARCHAR(150)     NOT NULL COMMENT 'مثلاً: آموکسی‌سیلین ۵۰۰',
            `category`         VARCHAR(30)      NOT NULL DEFAULT 'other' COMMENT 'antibiotic|analgesic|other',
            `default_dose_note`VARCHAR(255)     DEFAULT NULL COMMENT 'یادداشت پیش‌فرض دوز، مثلاً برای راهنمایی دکتر',
            `is_active`        TINYINT(1)       NOT NULL DEFAULT 1,
            `created_at`       DATETIME         NOT NULL,
            PRIMARY KEY (`id`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_prescriptions` (
            `id`             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `patient_id`     BIGINT UNSIGNED  NOT NULL,
            `doctor_id`      BIGINT UNSIGNED  DEFAULT NULL,
            `treatment_id`   BIGINT UNSIGNED  DEFAULT NULL COMMENT 'اتصال اختیاری به رکورد ویزیت/درمان',
            `prescribed_date`DATE             NOT NULL,
            `notes`          TEXT             DEFAULT NULL,
            `created_at`     DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_patient` (`patient_id`),
            KEY `idx_treatment` (`treatment_id`)
        ) $c;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_prescription_items` (
            `id`               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `prescription_id`  BIGINT UNSIGNED  NOT NULL,
            `drug_id`          BIGINT UNSIGNED  DEFAULT NULL,
            `drug_name_snapshot` VARCHAR(150)   NOT NULL COMMENT 'اسم دارو در لحظه‌ی نسخه — حتی اگه بعداً دارو ویرایش/حذف بشه، نسخه‌های قدیمی درست می‌مونن',
            `english_name_snapshot` VARCHAR(150) DEFAULT NULL COMMENT 'اسم انگلیسی/ژنریک در لحظه‌ی نسخه',
            `quantity`         DECIMAL(6,2)     NOT NULL DEFAULT 1,
            `quantity_unit`    VARCHAR(20)      NOT NULL DEFAULT 'عدد' COMMENT 'عدد|قطره|میلی‌لیتر|کپسول',
            `frequency`        VARCHAR(50)      NOT NULL COMMENT 'مثلاً: هر ۸ ساعت (۳ بار در روز)',
            `duration_days`    INT UNSIGNED     NOT NULL DEFAULT 1,
            `timing_note`      VARCHAR(30)      DEFAULT NULL COMMENT 'قبل از غذا|بعد از غذا|همراه غذا|فرقی ندارد',
            `extra_note`       VARCHAR(255)     DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_prescription` (`prescription_id`)
        ) $c;");

        // ═══════════════ مسیر درمان (Treatment Pathway) — فاز ۱ ═══════
        // نکته‌ی مهم امنیتی/معماری: این بخش کاملاً افزایشیه — هیچ جدول
        // موجودی (چارت، کاتالوگ، نوبت‌دهی) رو تغییر نمی‌ده، فقط بهشون
        // اشاره می‌کنه (با ستون‌های *_id ساده، نه Foreign Key سخت‌گیرانه
        // که اگه جایی داده‌ی قدیمی ناهماهنگ بود، خطای فاجعه‌بار نسازه).
        // کل این ماژول پشت یه کلید روشن/خاموش توی تنظیماته (پیش‌فرض
        // خاموش) — یعنی تا وقتی فعالش نکنید، هیچ‌جای دیگه‌ی پلاگین
        // اصلاً بهش برخورد نمی‌کنه.

        // ─── قالب‌های آماده (مثلاً «عصب‌کشی + روکش») ────────────────────
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_pathway_templates` (
            `id`          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `name`        VARCHAR(150)     NOT NULL,
            `description` VARCHAR(255)     DEFAULT NULL,
            `is_default`  TINYINT(1)       NOT NULL DEFAULT 0 COMMENT 'قالب‌های پیش‌فرض seed‌شده — قابل ویرایش/حذف، فقط برای تشخیص منبع',
            `is_active`   TINYINT(1)       NOT NULL DEFAULT 1,
            `created_by`  BIGINT UNSIGNED  DEFAULT NULL,
            `created_at`  DATETIME         NOT NULL,
            PRIMARY KEY (`id`)
        ) $c;");

        // ─── مراحل هر قالب — catalog_id اشاره به برگ کاتالوگ خودمون
        // (نه چیز جدید)، depends_on یعنی این مرحله به کدوم مرحله‌ی
        // قبلیِ همین قالب وابسته‌ست (برای پیش‌نیاز/Blocked) ──────────
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_pathway_template_steps` (
            `id`                    BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `template_id`           BIGINT UNSIGNED  NOT NULL,
            `step_order`            INT UNSIGNED     NOT NULL DEFAULT 1,
            `title`                 VARCHAR(150)     NOT NULL,
            `catalog_id`            BIGINT UNSIGNED  DEFAULT NULL COMMENT 'اتصال اختیاری به برگ کاتالوگ خدمات موجود',
            `suggested_interval_days` INT UNSIGNED   DEFAULT NULL COMMENT 'فاصله‌ی پیشنهادی از مرحله‌ی قبل، بر حسب روز',
            `depends_on_order`      INT UNSIGNED     DEFAULT NULL COMMENT 'شماره‌ترتیب مرحله‌ای که این بهش وابسته‌ست',
            `notes`                 VARCHAR(255)     DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_template` (`template_id`)
        ) $c;");

        // ─── مسیر واقعیِ یه بیمار خاص (از یه قالب کپی می‌شه، یا از صفر
        // دستی ساخته می‌شه) ───────────────────────────────────────────
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_treatment_pathways` (
            `id`                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `patient_id`        BIGINT UNSIGNED  NOT NULL,
            `tooth_number`      INT              DEFAULT NULL COMMENT 'اگه مربوط به یه دندان خاصه — همون کدگذاری فعلی چارت',
            `template_id`       BIGINT UNSIGNED  DEFAULT NULL COMMENT 'از کدوم قالب ساخته شده — خالی یعنی دستی از صفر',
            `title`             VARCHAR(150)     NOT NULL,
            `status`            VARCHAR(20)      NOT NULL DEFAULT 'active' COMMENT 'draft|active|paused|completed|cancelled',
            `start_date`        DATE             DEFAULT NULL,
            `end_date`          DATE             DEFAULT NULL,
            `doctor_id`         BIGINT UNSIGNED  DEFAULT NULL,
            `notes`             TEXT             DEFAULT NULL,
            `created_by`        BIGINT UNSIGNED  DEFAULT NULL,
            `created_at`        DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_patient` (`patient_id`),
            KEY `idx_tooth` (`tooth_number`)
        ) $c;");

        // ─── مراحل واقعی یه مسیر — treatment_id وقتی این مرحله واقعاً
        // انجام شد، به همون رکورد dental_catalog_treatments وصل می‌شه
        // (نه اینکه رکورد جدید/تکراری بسازیم) ──────────────────────────
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_treatment_pathway_steps` (
            `id`                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `pathway_id`        BIGINT UNSIGNED  NOT NULL,
            `step_order`        INT UNSIGNED     NOT NULL DEFAULT 1,
            `title`             VARCHAR(150)     NOT NULL,
            `catalog_id`        BIGINT UNSIGNED  DEFAULT NULL,
            `status`            VARCHAR(20)      NOT NULL DEFAULT 'pending' COMMENT 'pending|scheduled|in_progress|completed|skipped|cancelled|blocked',
            `doctor_id`         BIGINT UNSIGNED  DEFAULT NULL,
            `scheduled_date`    DATE             DEFAULT NULL,
            `completed_date`    DATE             DEFAULT NULL,
            `suggested_interval_days` INT UNSIGNED DEFAULT NULL,
            `depends_on_step_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'اتصال به id مرحله‌ی پیش‌نیاز — خودِ همین جدول',
            `dependency_overridden` TINYINT(1)    NOT NULL DEFAULT 0 COMMENT 'اگه با وجود پیش‌نیاز ناقص، دکتر عمداً رد کرد',
            `appointment_id`    BIGINT UNSIGNED  DEFAULT NULL COMMENT 'اتصال اختیاری به dental_appointments',
            `treatment_id`      BIGINT UNSIGNED  DEFAULT NULL COMMENT 'وقتی انجام شد، اتصال به dental_catalog_treatments واقعی',
            `notes`             VARCHAR(255)     DEFAULT NULL,
            `created_at`        DATETIME         NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_pathway` (`pathway_id`),
            KEY `idx_status` (`status`)
        ) $c;");

        // ═══════════════ جزئیات تخصصی عصب‌کشی (Endodontic Details) ═══
        // نکته‌ی معماری مهم: این کاملاً جدا از مسیر درمان عمومیه، به
        // treatment_id واقعی (dental_catalog_treatments) وصل می‌شه —
        // نه به ساختار مسیر. یعنی حتی بدون مسیر درمان هم قابل‌استفاده‌ست،
        // و فردا اگه بخوایم برای ایمپلنت/ارتودنسی هم چیز مشابه بسازیم،
        // جدول‌های کاملاً جدا و مستقل می‌سازیم — بدون دست‌زدن به این‌ها.
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_endodontic_details` (
            `id`                     BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `treatment_id`           BIGINT UNSIGNED  NOT NULL COMMENT 'اتصال به dental_catalog_treatments واقعی',
            `patient_id`             BIGINT UNSIGNED  NOT NULL,
            `tooth_number`           INT              DEFAULT NULL,
            `number_of_canals`       TINYINT UNSIGNED DEFAULT NULL,
            `number_of_visits`       TINYINT UNSIGNED DEFAULT NULL,
            `irrigation`             VARCHAR(150)     DEFAULT NULL COMMENT 'مثلاً: هیپوکلریت سدیم ۵.۲۵٪',
            `sealer`                 VARCHAR(150)     DEFAULT NULL,
            `obturation_method`      VARCHAR(100)     DEFAULT NULL COMMENT 'مثلاً: کاندنسیشن جانبی/عمودی',
            `intracanal_medication`  VARCHAR(150)     DEFAULT NULL,
            `temporary_restoration`  VARCHAR(100)     DEFAULT NULL,
            `notes`                  TEXT             DEFAULT NULL,
            `doctor_id`              BIGINT UNSIGNED  DEFAULT NULL,
            `created_at`             DATETIME         NOT NULL,
            `updated_at`             DATETIME         DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_treatment` (`treatment_id`)
        ) $c;");

        // ─── هر کانال جدا — چون هر دندان می‌تونه چند کانال با طول/فایل
        // نهایی متفاوت داشته باشه (مثلاً MB، DB، P توی مولرها) ──────────
        dbDelta("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dental_endodontic_canals` (
            `id`                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            `detail_id`         BIGINT UNSIGNED  NOT NULL,
            `canal_name`        VARCHAR(50)      NOT NULL COMMENT 'مثلاً: MB, DB, P, مزیوباکال و...',
            `working_length`    DECIMAL(4,1)     DEFAULT NULL COMMENT 'بر حسب میلی‌متر',
            `final_file`        VARCHAR(20)       DEFAULT NULL COMMENT 'مثلاً: #25، یا کد رتاری',
            `notes`             VARCHAR(150)     DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_detail` (`detail_id`)
        ) $c;");
    }
    public static function fix_shift_columns(): void {
        global $wpdb;
        $t1 = $wpdb->prefix . 'dental_shift_rooms';
        $t2 = $wpdb->prefix . 'dental_shift_assignments';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$t1}'") !== $t1) return; // جدول هنوز نساخته نشده، دست نزن
        // اگه بعداً ستونی جا موند، اینجا اضافه می‌شه (فعلاً چیزی نیست)
    }
    public static function create_roles(): void {
        $roles = [
            'dental_admin'     => ['مدیر کلینیک',    ['read'=>true,'manage_dental'=>true,'dental_admin'=>true,'dental_manage_all'=>true]],
            'dental_doctor'    => ['دندانپزشک',       ['read'=>true,'manage_dental'=>true,'dental_doctor'=>true]],
            'dental_secretary' => ['منشی / پذیرش',    ['read'=>true,'manage_dental'=>true,'dental_secretary'=>true]],
            // نکته: کپبیلیتی‌های جمع (dental_view_financials/dental_manage_financials/
            // dental_manage_wallet) اینجا هم صریح اضافه شدن — قبلاً فقط نسخه‌ی مفرد
            // (dental_manage_financial) بود که جای دیگه‌ای (از جمله کل REST API مالی
            // که current_user_can('dental_view_financials'/'dental_manage_financials')
            // چک می‌کنه) اصلاً بهش نگاه نمی‌کرد — یعنی خودِ «مسئول مالی» عملاً از
            // API مالی رد می‌شد. صفحات ادمین چون نقش رو مستقیم چک می‌کنن مشکلی نداشتن.
            'dental_financial' => ['مسئول مالی',      ['read'=>true,'manage_dental'=>true,'dental_financial'=>true,'dental_manage_financial'=>true,'dental_view_financials'=>true,'dental_manage_financials'=>true,'dental_manage_wallet'=>true]],
            'dental_assistant' => ['دستیار',          ['read'=>true,'manage_dental'=>true,'dental_assistant'=>true]],
            'dental_patient'   => ['بیمار',           ['read'=>true,'dental_patient'=>true]],
        ];
        foreach($roles as $slug => [$name,$caps]) {
            if(!get_role($slug)) {
                add_role($slug, $name, $caps);
            } else {
                // اگر نقش از قبل بود (مثل منشی)، مجوزهای جدید را هم اضافه کن
                $role = get_role($slug);
                foreach ($caps as $cap => $v) { if(!$role->has_cap($cap)) $role->add_cap($cap); }
            }
        }
        self::grant_shared_capabilities();
    }

    // ─── مجوزهای مشترک بین کارکنان (نه بیمار) ────────────────
    // نکته مهم: مدیر وردپرس (administrator) به‌طور خودکار مجوزهای
    // سفارشی جدید را ندارد مگر این‌که صریحاً اضافه شود — همان مشکلی
    // که قبلاً باعث مخفی‌شدن منوها برای ادمین شد. اینجا صریحاً اضافه می‌کنیم.
    private static function grant_shared_capabilities(): void {
        $caps_by_role = [
            'administrator'    => ['dental_manage_booking','dental_manage_reception','dental_manage_financial'],
            'dental_admin'     => ['dental_manage_booking','dental_manage_reception','dental_manage_financial'],
            'dental_doctor'    => ['dental_manage_booking','dental_manage_reception'],
            'dental_secretary' => ['dental_manage_booking','dental_manage_reception'],
            'dental_financial' => ['dental_manage_reception'], // برای دیدن وضعیت پذیرش، نه مدیریت نوبت‌ها
            'dental_assistant' => ['dental_manage_reception'], // فقط دیدن صف، نه ویرایش
        ];
        foreach ($caps_by_role as $role_slug => $caps) {
            $role = get_role($role_slug);
            if (!$role) continue;
            foreach ($caps as $cap) {
                if (!$role->has_cap($cap)) $role->add_cap($cap);
            }
        }
    }

    // ─── صفحات ──────────────────────────────────────────────
    private static function create_pages(): void {
        $pages = [
            'portal-bimar' => ['پورتال بیمار', '[dental_patient_portal]'],
            'dental-login' => ['ورود بیماران', '[dental_otp_login]'],
        ];
        foreach($pages as $slug => [$title,$content]) {
            if(!get_page_by_path($slug)) {
                wp_insert_post([
                    'post_title'   => $title,
                    'post_name'    => $slug,
                    'post_content' => $content,
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                ]);
            }
        }
    }

    // ─── Cron ────────────────────────────────────────────────
    public static function schedule_cron(): void {
        if(!wp_next_scheduled('dental_sms_cron')) {
            wp_schedule_event(time(), 'hourly', 'dental_sms_cron');
        }
        if(!wp_next_scheduled('dental_daily_cron')) {
            // هر روز ساعت ۹ صبح
            $next_9am = strtotime('today 09:00:00');
            if($next_9am < time()) $next_9am = strtotime('tomorrow 09:00:00');
            wp_schedule_event($next_9am, 'daily', 'dental_daily_cron');
        }
        // ─── بکاپ خودکار روزانه — ساعت ۳ بامداد (کمترین ترافیک کلینیک) ──
        if(!wp_next_scheduled('dental_auto_backup_cron')) {
            $next_3am = strtotime('today 03:00:00');
            if($next_3am < time()) $next_3am = strtotime('tomorrow 03:00:00');
            wp_schedule_event($next_3am, 'daily', 'dental_auto_backup_cron');
        }
    }
}
