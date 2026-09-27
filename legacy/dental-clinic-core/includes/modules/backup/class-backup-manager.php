<?php
defined('ABSPATH') || exit;

class Dental_Backup_Manager {

    // ─── لیست همه جدول‌های اختصاصی این پلاگین‌ها — خودکار پیدا می‌شن
    // (هر جدول جدیدی هم که بعداً اضافه بشه، نیازی به آپدیت این کد نیست) ──
    public static function get_plugin_tables(): array {
        global $wpdb;
        $like = $wpdb->esc_like($wpdb->prefix . 'dental_') . '%';
        return $wpdb->get_col($wpdb->prepare("SHOW TABLES LIKE %s", $like));
    }

    // ─── تولید محتوای کامل SQL — جدا از استریم مستقیم، تا هم برای
    // دانلود دستی هم برای بکاپ خودکار (ذخیره روی فایل) قابل استفاده باشه ──
    public static function generate_sql_content(): string {
        global $wpdb;
        $tables = self::get_plugin_tables();
        $out = '';
        $out .= "-- ═══════════════════════════════════════════════════\n";
        $out .= "-- بک‌آپ دیتابیس پلاگین کلینیک دندانپزشکی\n";
        $out .= "-- کلینیک: " . get_option('dental_clinic_name','—') . "\n";
        $out .= "-- تاریخ: " . current_time('Y-m-d H:i:s') . " (" . Dental_Jalali::today('Y/m/d') . ")\n";
        $out .= "-- تعداد جدول: " . count($tables) . "\n";
        $out .= "-- ═══════════════════════════════════════════════════\n\n";
        $out .= "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $create = $wpdb->get_row("SHOW CREATE TABLE `{$table}`", ARRAY_N);
            $out .= "-- ─── جدول: {$table} ───────────────────────────────\n";
            $out .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $out .= $create[1] . ";\n\n";

            $total = (int)$wpdb->get_var("SELECT COUNT(*) FROM `{$table}`");
            if ($total === 0) { $out .= "\n"; continue; }

            $cols = $wpdb->get_col("SHOW COLUMNS FROM `{$table}`", 0);
            $col_list = '`' . implode('`,`', $cols) . '`';

            for ($offset = 0; $offset < $total; $offset += 500) {
                $rows = $wpdb->get_results("SELECT * FROM `{$table}` LIMIT 500 OFFSET {$offset}", ARRAY_A);
                if (empty($rows)) break;

                $values_sql = [];
                foreach ($rows as $row) {
                    $vals = array_map(function($v) use ($wpdb) {
                        if ($v === null) return 'NULL';
                        return "'" . esc_sql(str_replace(["\\", "\0"], ["\\\\", ''], $v)) . "'";
                    }, array_values($row));
                    $values_sql[] = '(' . implode(',', $vals) . ')';
                }
                $out .= "INSERT INTO `{$table}` ({$col_list}) VALUES\n" . implode(",\n", $values_sql) . ";\n\n";
            }
        }
        $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $out;
    }

    // ─── ساخت کامل فایل SQL (ساختار + داده هر جدول) و خروجی مستقیم ──
    public static function stream_backup(): void {
        $clinic = sanitize_file_name(get_option('dental_clinic_name', 'clinic'));
        $filename = "backup-{$clinic}-" . date('Y-m-d_H-i') . ".sql";

        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        echo self::generate_sql_content();
        exit;
    }

    // ═══════════════ بکاپ خودکار روزانه (WP-Cron) ═══════════════════
    // ذخیره توی همون پوشه‌ی امن Imaging (خارج از uploads عمومی، با
    // .htaccess مسدود) — سیستم ذخیره‌سازی جدا نمی‌سازیم.
    public static function get_backup_dir(): string {
        $upload = wp_upload_dir();
        $dir = trailingslashit($upload['basedir']) . 'dental-backups-private';
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
            file_put_contents($dir . '/.htaccess', "Deny from all\n");
            file_put_contents($dir . '/index.php', "<?php // silence\n");
        }
        return $dir;
    }

    public static function run_scheduled_backup(): void {
        $dir = self::get_backup_dir();
        $clinic = sanitize_file_name(get_option('dental_clinic_name', 'clinic'));
        $filename = "auto-backup-{$clinic}-" . date('Y-m-d_H-i') . ".sql";
        $content = self::generate_sql_content();
        file_put_contents($dir . '/' . $filename, $content);

        // ─── فقط ۱۰ بکاپ آخر رو نگه دار — وگرنه دیسک پر می‌شه ──────
        $files = glob($dir . '/auto-backup-*.sql');
        if ($files && count($files) > 10) {
            usort($files, fn($a,$b) => filemtime($a) - filemtime($b));
            $to_delete = array_slice($files, 0, count($files) - 10);
            foreach ($to_delete as $f) { @unlink($f); }
        }

        update_option('dental_last_auto_backup', current_time('mysql'));

        if (class_exists('Dental_Audit_Log')) {
            Dental_Audit_Log::log('settings_changed', "بکاپ خودکار روزانه ساخته شد ({$filename})", ['entity_type'=>'system']);
        }
    }

    public static function get_scheduled_backups(): array {
        $dir = self::get_backup_dir();
        $files = glob($dir . '/auto-backup-*.sql');
        if (!$files) return [];
        usort($files, fn($a,$b) => filemtime($b) - filemtime($a));
        return array_map(fn($f) => ['name' => basename($f), 'size' => size_format(filesize($f)), 'date' => date('Y-m-d H:i', filemtime($f))], $files);
    }

    // ─── خلاصه‌ای که قبل از دانلود نشون داده می‌شه (چندتا جدول، چندتا ردیف) ──
    public static function get_summary(): array {
        global $wpdb;
        $tables = self::get_plugin_tables();
        $total_rows = 0;
        $details = [];
        foreach ($tables as $t) {
            $c = (int)$wpdb->get_var("SELECT COUNT(*) FROM `{$t}`");
            $total_rows += $c;
            $details[] = ['table' => $t, 'rows' => $c];
        }
        return ['table_count' => count($tables), 'total_rows' => $total_rows, 'details' => $details];
    }
}
