<?php
defined('ABSPATH') || exit;

class Dental_Attendance_Manager {

    // ═══════════════ ورود / خروج ═══════════════════════════════

    public static function clock_in(int $user_id): bool {
        global $wpdb;
        // اگر امروز از قبل ورود ثبت شده و خروج نزده، اجازه ورود دوباره نده
        $open = self::get_open_attendance($user_id);
        if ($open) return false;

        $wpdb->insert($wpdb->prefix.'dental_attendance', [
            'user_id'    => $user_id,
            'work_date'  => current_time('Y-m-d'),
            'clock_in'   => current_time('mysql'),
            'created_at' => current_time('mysql'),
        ], ['%d','%s','%s','%s']);
        return true;
    }

    public static function clock_out(int $user_id): bool {
        global $wpdb;
        $open = self::get_open_attendance($user_id);
        if (!$open) return false;

        $minutes = round((strtotime(current_time('mysql')) - strtotime($open['clock_in'])) / 60);
        return (bool)$wpdb->update($wpdb->prefix.'dental_attendance', [
            'clock_out'     => current_time('mysql'),
            'total_minutes' => $minutes,
        ], ['id'=>$open['id']], ['%s','%d'], ['%d']);
    }

    // ─── ورود خودکار — اولین ورود به پنل در روز = ثبت ورود ─────────
    public static function maybe_auto_clock_in(int $user_id): void {
        if (self::get_open_attendance($user_id)) return; // از قبل بازه — کاری نکن
        global $wpdb;
        $already_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dental_attendance WHERE user_id=%d AND work_date=%s",
            $user_id, current_time('Y-m-d')
        ));
        if ($already_today > 0) return; // امروز قبلاً یه بار ورود/خروج داشته، دوباره نزن
        self::clock_in($user_id);
    }

    // ─── خروج خودکار استنتاجی — اگه کسی مرورگرش رو بسته (بدون زدن
    // «ثبت خروج») و مدت زیادی غیرفعال بوده، خودکار براش خروج ثبت کن ──
    // تا برای همیشه به‌عنوان «در دسترس» نمونه.
    public static function maybe_auto_clock_out_inactive(): void {
        $threshold_minutes = 90; // بعد از ۹۰ دقیقه بی‌فعالیتی کامل
        $open = self::get_open_attendance_all();
        foreach ($open as $att) {
            $last_seen = (int)get_user_meta($att['user_id'], '_dental_last_seen', true);
            if (!$last_seen) continue;
            if ((time() - $last_seen) < ($threshold_minutes * 60)) continue;

            // اول اگه ناهار/استراحت باز مونده، اونم ببند
            $open_break = self::get_open_break($att['user_id']);
            if ($open_break) {
                global $wpdb;
                $wpdb->update($wpdb->prefix.'dental_breaks',
                    ['end_time' => date('Y-m-d H:i:s', $last_seen)], ['id'=>$open_break['id']], ['%s'], ['%d']);
            }

            global $wpdb;
            $minutes = round(($last_seen - strtotime($att['clock_in'])) / 60);
            $wpdb->update($wpdb->prefix.'dental_attendance', [
                'clock_out'     => date('Y-m-d H:i:s', $last_seen),
                'total_minutes' => max(0, $minutes),
                'auto_closed'   => 1,
            ], ['id'=>$att['id']], ['%s','%d','%d'], ['%d']);
        }
    }

    private static function get_open_attendance_all(): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_attendance WHERE work_date=%s AND clock_out IS NULL",
            current_time('Y-m-d')
        ), ARRAY_A);
    }


    public static function get_open_attendance(int $user_id): ?array {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_attendance
             WHERE user_id=%d AND work_date=%s AND clock_out IS NULL
             ORDER BY id DESC LIMIT 1",
            $user_id, current_time('Y-m-d')
        ), ARRAY_A);
        return $row ?: null;
    }

    public static function get_today_status(int $user_id): array {
        $att = self::get_open_attendance($user_id);
        if (!$att) {
            // شاید امروز کلاً وارد نشده یا از قبل خارج شده
            global $wpdb;
            $closed = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dental_attendance WHERE user_id=%d AND work_date=%s ORDER BY id DESC LIMIT 1",
                $user_id, current_time('Y-m-d')
            ), ARRAY_A);
            return ['clocked_in'=>false, 'attendance'=>$closed];
        }
        return ['clocked_in'=>true, 'attendance'=>$att];
    }

    public static function get_history(int $user_id, string $from, string $to): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_attendance
             WHERE user_id=%d AND work_date BETWEEN %s AND %s
             ORDER BY work_date DESC", $user_id, $from, $to
        ), ARRAY_A);
    }

    public static function get_all_today(): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, u.display_name FROM {$wpdb->prefix}dental_attendance a
             LEFT JOIN {$wpdb->users} u ON a.user_id=u.ID
             WHERE a.work_date=%s ORDER BY a.clock_in ASC", current_time('Y-m-d')
        ), ARRAY_A);
    }

    // ═══════════════ استراحت / ناهار ═══════════════════════════

    public static function start_break(int $user_id, string $type = 'break'): bool {
        global $wpdb;
        if (self::get_open_break($user_id)) return false;
        $wpdb->insert($wpdb->prefix.'dental_breaks', [
            'user_id'    => $user_id,
            'work_date'  => current_time('Y-m-d'),
            'break_type' => in_array($type,['lunch','break']) ? $type : 'break',
            'start_time' => current_time('mysql'),
        ], ['%d','%s','%s','%s']);
        return true;
    }

    public static function end_break(int $user_id): bool {
        global $wpdb;
        $open = self::get_open_break($user_id);
        if (!$open) return false;
        return (bool)$wpdb->update($wpdb->prefix.'dental_breaks',
            ['end_time' => current_time('mysql')], ['id'=>$open['id']], ['%s'], ['%d']);
    }

    public static function get_open_break(int $user_id): ?array {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_breaks
             WHERE user_id=%d AND work_date=%s AND end_time IS NULL ORDER BY id DESC LIMIT 1",
            $user_id, current_time('Y-m-d')
        ), ARRAY_A);
        return $row ?: null;
    }

    // ─── مجموع دقایق مصرف‌شده امروز برای یه نوع خاص (ناهار یا استراحت) —
    // شامل همه‌ی جلسات تمام‌شده + جلسه‌ی الان بازِ احتمالی (تجمعی، نه صفر) ──
    public static function get_today_break_total_minutes(int $user_id, string $type): int {
        $breaks = self::get_today_breaks($user_id);
        $total = 0;
        foreach ($breaks as $b) {
            if ($b['break_type'] !== $type) continue;
            $end = $b['end_time'] ?: current_time('mysql'); // اگه باز مونده، تا همین الان حساب کن
            $total += round((strtotime($end) - strtotime($b['start_time'])) / 60);
        }
        return max(0, $total);
    }
    public static function get_today_breaks(int $user_id): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_breaks WHERE user_id=%d AND work_date=%s ORDER BY start_time ASC",
            $user_id, current_time('Y-m-d')
        ), ARRAY_A);
    }

    // ═══════════════ مرخصی ═══════════════════════════════════════

    public static function request_leave(int $user_id, string $type, string $from, string $to, ?float $hours, string $reason): int {
        global $wpdb;
        $days = $type === 'hourly' ? round(($hours ?: 0) / 8, 2) : (self::date_diff_days($from, $to) + 1);
        $wpdb->insert($wpdb->prefix.'dental_leave_requests', [
            'user_id'    => $user_id,
            'leave_type' => $type === 'hourly' ? 'hourly' : 'daily',
            'date_from'  => $from,
            'date_to'    => $to,
            'hours'      => $type === 'hourly' ? $hours : null,
            'days_count' => $days,
            'reason'     => sanitize_textarea_field($reason),
            'status'     => 'pending',
            'created_at' => current_time('mysql'),
        ], ['%d','%s','%s','%s','%f','%f','%s','%s','%s']);
        return (int)$wpdb->insert_id;
    }

    private static function date_diff_days(string $from, string $to): int {
        $d1 = new DateTime($from); $d2 = new DateTime($to);
        return (int)$d1->diff($d2)->days;
    }

    public static function review_leave(int $id, string $status, int $reviewer_id, string $note = ''): bool {
        global $wpdb;
        return (bool)$wpdb->update($wpdb->prefix.'dental_leave_requests', [
            'status'      => in_array($status,['approved','rejected']) ? $status : 'pending',
            'admin_note'  => sanitize_text_field($note),
            'reviewed_by' => $reviewer_id,
            'reviewed_at' => current_time('mysql'),
        ], ['id'=>$id], ['%s','%s','%d','%s'], ['%d']);
    }

    public static function get_my_leaves(int $user_id): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT l.*, u.display_name as reviewer_name
             FROM {$wpdb->prefix}dental_leave_requests l
             LEFT JOIN {$wpdb->users} u ON l.reviewed_by = u.ID
             WHERE l.user_id=%d ORDER BY l.created_at DESC LIMIT 40", $user_id
        ), ARRAY_A);
    }

    public static function get_pending_leaves(): array {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT l.*, u.display_name FROM {$wpdb->prefix}dental_leave_requests l
             LEFT JOIN {$wpdb->users} u ON l.user_id=u.ID
             WHERE l.status='pending' ORDER BY l.created_at ASC", ARRAY_A
        );
    }

    public static function get_all_leaves(string $status = 'all'): array {
        global $wpdb;
        $where = $status !== 'all' ? $wpdb->prepare("WHERE l.status=%s", $status) : '';
        return $wpdb->get_results(
            "SELECT l.*, u.display_name, r.display_name as reviewer_name
             FROM {$wpdb->prefix}dental_leave_requests l
             LEFT JOIN {$wpdb->users} u ON l.user_id=u.ID
             LEFT JOIN {$wpdb->users} r ON l.reviewed_by=r.ID
             $where ORDER BY l.created_at DESC LIMIT 100", ARRAY_A
        );
    }

    // سهمیه سالانه منهای مرخصی‌های تأییدشده امسال
    public static function get_leave_balance(int $user_id): array {
        global $wpdb;
        $quota = (float)get_user_meta($user_id, '_dental_leave_quota_days', true);
        if (!$quota) $quota = (float)get_option('dental_default_leave_quota', 20);

        $year_start = date('Y') . '-01-01';
        $year_end   = date('Y') . '-12-31';
        $used = (float)$wpdb->get_var($wpdb->prepare(
            "SELECT SUM(days_count) FROM {$wpdb->prefix}dental_leave_requests
             WHERE user_id=%d AND status='approved' AND date_from BETWEEN %s AND %s",
            $user_id, $year_start, $year_end
        ));
        return ['quota'=>$quota, 'used'=>$used, 'remaining'=>max(0,$quota-$used)];
    }

    // ─── وضعیت امروز همه کارکنان به‌صورت کارت (برای داشبورد مدیریت) ──
    public static function get_today_staff_cards(): array {
        $users = get_users([
            'role__in' => ['dental_admin','dental_doctor','dental_secretary','dental_financial','dental_assistant'],
            'fields'   => ['ID','display_name'],
        ]);
        global $wpdb;
        $wp_roles = wp_roles();
        $limits = self::get_break_limits();
        $out = [];
        foreach ($users as $u) {
            $user_obj  = get_userdata($u->ID);
            $role_slug = $user_obj->roles[0] ?? '';
            $role_name = $wp_roles->roles[$role_slug]['name'] ?? $role_slug;

            $att = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dental_attendance WHERE user_id=%d AND work_date=%s ORDER BY id DESC LIMIT 1",
                $u->ID, current_time('Y-m-d')
            ), ARRAY_A);

            if (!$att) {
                $status = 'absent'; $time = null; $break_note = null;
            } elseif (empty($att['clock_out'])) {
                $status = 'present'; $time = $att['clock_in']; $break_note = null;

                // ─── آیا الان در حال ناهار/استراحته؟ نشان بده و با سقف مقایسه کن ──
                $open_break = self::get_open_break($u->ID);
                if ($open_break) {
                    $elapsed = round((current_time('timestamp') - strtotime($open_break['start_time'])) / 60);
                    $limit   = $limits[$role_slug][$open_break['break_type']] ?? ($open_break['break_type']==='lunch'?45:15);
                    $status  = $open_break['break_type']; // 'lunch' یا 'break'
                    $break_note = [
                        'elapsed' => $elapsed,
                        'limit'   => $limit,
                        'over'    => $elapsed > $limit,
                    ];
                }
            } else {
                $status = 'left'; $time = $att['clock_out']; $break_note = null;
            }

            $out[] = ['id'=>$u->ID, 'name'=>$u->display_name, 'role'=>$role_name, 'role_slug'=>$role_slug,
                'status'=>$status, 'time'=>$time, 'break_note'=>$break_note];
        }
        return $out;
    }

    // ─── تنظیمات سقف زمانی ناهار/استراحت به تفکیک نقش (توسط مدیریت) ──
    public static function get_break_limits(): array {
        $saved = get_option('dental_break_limits', []);
        $defaults = [
            'dental_admin'     => ['lunch'=>45,'break'=>15],
            'dental_doctor'    => ['lunch'=>45,'break'=>15],
            'dental_secretary' => ['lunch'=>30,'break'=>10],
            'dental_financial' => ['lunch'=>30,'break'=>10],
            'dental_assistant' => ['lunch'=>30,'break'=>10],
        ];
        foreach ($defaults as $role => $vals) {
            if (!isset($saved[$role])) $saved[$role] = $vals;
        }
        return $saved;
    }

    public static function save_break_limits(array $limits): void {
        $clean = [];
        foreach ($limits as $role => $vals) {
            $clean[sanitize_key($role)] = [
                'lunch' => max(0, (int)($vals['lunch'] ?? 30)),
                'break' => max(0, (int)($vals['break'] ?? 10)),
            ];
        }
        update_option('dental_break_limits', $clean);
    }

    // ─── گزارش حضور در یک بازه تاریخی برای همه یا یک کارمند خاص ──
    public static function get_range_report(string $from, string $to, int $user_id = 0): array {
        global $wpdb;
        $where = $user_id ? $wpdb->prepare("AND a.user_id=%d", $user_id) : '';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, u.display_name FROM {$wpdb->prefix}dental_attendance a
             LEFT JOIN {$wpdb->users} u ON a.user_id=u.ID
             WHERE a.work_date BETWEEN %s AND %s $where
             ORDER BY a.work_date DESC, a.clock_in ASC", $from, $to
        ), ARRAY_A);
    }

    // ═══════════════ آنلاین بودن کارکنان ═══════════════════════

    public static function touch_last_seen(int $user_id): void {
        update_user_meta($user_id, '_dental_last_seen', time());
    }

    public static function is_online(int $user_id): bool {
        $last = (int)get_user_meta($user_id, '_dental_last_seen', true);
        return $last && (time() - $last) < 300; // ۵ دقیقه
    }

    public static function get_online_staff(): array {
        $users = get_users([
            'role__in' => ['dental_admin','dental_doctor','dental_secretary','dental_financial','dental_assistant','administrator'],
            'fields'   => ['ID','display_name'],
        ]);
        $out = [];
        foreach ($users as $u) {
            $out[] = ['id'=>$u->ID, 'name'=>$u->display_name, 'online'=>self::is_online($u->ID)];
        }
        usort($out, fn($a,$b) => $b['online'] <=> $a['online']);
        return $out;
    }
}
