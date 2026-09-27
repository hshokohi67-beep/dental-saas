<?php
defined('ABSPATH') || exit;

class Dental_Workspace_Manager {

    // ═══════════════ چک‌لیست کارها ═══════════════════════════════

    public static function assign_task(string $title, int $assigned_to, int $assigned_by, string $due_date = ''): int {
        global $wpdb;
        $wpdb->insert($wpdb->prefix.'dental_tasks', [
            'title'       => sanitize_text_field($title),
            'assigned_to' => $assigned_to,
            'assigned_by' => $assigned_by,
            'due_date'    => $due_date ?: null,
            'is_done'     => 0,
            'created_at'  => current_time('mysql'),
        ], ['%s','%d','%d','%s','%d','%s']);
        return (int)$wpdb->insert_id;
    }

    public static function toggle_task(int $task_id, bool $done): bool {
        global $wpdb;
        return (bool)$wpdb->update($wpdb->prefix.'dental_tasks', [
            'is_done' => $done ? 1 : 0,
            'done_at' => $done ? current_time('mysql') : null,
        ], ['id'=>$task_id], ['%d','%s'], ['%d']);
    }

    // پیش‌فرض: فقط کارهای «امروز» یا «بدون تاریخ مشخص» (هر زمان)
    public static function get_my_tasks(int $user_id, bool $include_done = false, bool $today_only = true): array {
        global $wpdb;
        $where = $include_done ? '' : 'AND is_done = 0';
        $date_where = $today_only ? $wpdb->prepare("AND (due_date IS NULL OR due_date = %s)", current_time('Y-m-d')) : '';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT t.*, u.display_name as assigned_by_name
             FROM {$wpdb->prefix}dental_tasks t
             LEFT JOIN {$wpdb->users} u ON t.assigned_by = u.ID
             WHERE t.assigned_to = %d $where $date_where
             ORDER BY t.is_done ASC, t.due_date ASC, t.created_at DESC LIMIT 30",
            $user_id
        ), ARRAY_A);
    }

    // تعداد کارهای انجام‌نشده امروز (برای بج عددی)
    public static function get_my_tasks_today_count(int $user_id): int {
        global $wpdb;
        return (int)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dental_tasks
             WHERE assigned_to=%d AND is_done=0 AND (due_date IS NULL OR due_date=%s)",
            $user_id, current_time('Y-m-d')
        ));
    }

    // برای مدیر — همه کارهایی که خودش محول کرده، با وضعیت انجام هرکدوم
    public static function get_assigned_by_me(int $manager_id): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT t.*, u.display_name as assigned_to_name
             FROM {$wpdb->prefix}dental_tasks t
             LEFT JOIN {$wpdb->users} u ON t.assigned_to = u.ID
             WHERE t.assigned_by = %d
             ORDER BY t.is_done ASC, t.created_at DESC LIMIT 40",
            $manager_id
        ), ARRAY_A);
    }

    // ═══════════════ پیام‌های داخلی ═══════════════════════════════

    public static function send_message(int $from, int $to, string $text): int {
        global $wpdb;
        $wpdb->insert($wpdb->prefix.'dental_messages', [
            'from_user_id' => $from,
            'to_user_id'   => $to, // 0 = عمومی برای همه کارکنان
            'message'      => sanitize_textarea_field($text),
            'is_read'      => 0,
            'created_at'   => current_time('mysql'),
        ], ['%d','%d','%s','%d','%s']);
        return (int)$wpdb->insert_id;
    }

    // ─── فقط پیام‌های دریافتی (برای شمارش نخوانده و ویجت خلاصه) ────
    public static function get_my_messages(int $user_id, int $limit = 20): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT m.*, u.display_name as from_name
             FROM {$wpdb->prefix}dental_messages m
             LEFT JOIN {$wpdb->users} u ON m.from_user_id = u.ID
             WHERE m.to_user_id = %d OR m.to_user_id = 0
             ORDER BY m.created_at DESC LIMIT %d",
            $user_id, $limit
        ), ARRAY_A);
    }

    // ─── لیست مخاطبینی که باهاشون مکالمه داشتیم (مثل لیست چت‌ها) —
    // شامل «📢 همه» به‌عنوان یه مخاطب ویژه اگه پیام عمومی رد و بدل شده ──
    public static function get_conversation_partners(int $user_id): array {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT
                CASE WHEN from_user_id=%d THEN to_user_id ELSE from_user_id END as partner_id,
                MAX(created_at) as last_time
             FROM {$wpdb->prefix}dental_messages
             WHERE from_user_id=%d OR to_user_id=%d OR to_user_id=0
             GROUP BY partner_id
             ORDER BY last_time DESC",
            $user_id, $user_id, $user_id
        ), ARRAY_A);

        $out = [];
        foreach ($rows as $r) {
            $pid = (int)$r['partner_id'];
            if ($pid === $user_id) continue; // مکالمه با خودم که معنی نداره
            $name = $pid === 0 ? '📢 اعلان عمومی به همه' : (get_userdata($pid) ? get_userdata($pid)->display_name : 'کاربر حذف‌شده');
            $unread = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}dental_messages
                 WHERE ((to_user_id=%d AND from_user_id=%d) OR (%d=0 AND to_user_id=0))
                 AND is_read=0 AND from_user_id!=%d",
                $user_id, $pid, $pid, $user_id
            ));
            $last_msg = $wpdb->get_row($wpdb->prepare(
                "SELECT message, created_at, from_user_id FROM {$wpdb->prefix}dental_messages
                 WHERE (from_user_id=%d AND to_user_id=%d) OR (from_user_id=%d AND to_user_id=%d) OR (%d=0 AND to_user_id=0)
                 ORDER BY created_at DESC LIMIT 1",
                $user_id, $pid, $pid, $user_id, $pid
            ), ARRAY_A);
            $out[] = [
                'partner_id' => $pid, 'name' => $name, 'unread' => $unread,
                'last_message' => $last_msg['message'] ?? '', 'last_time' => $last_msg['created_at'] ?? $r['last_time'],
                'last_from_me' => isset($last_msg['from_user_id']) && (int)$last_msg['from_user_id'] === $user_id,
            ];
        }
        return $out;
    }

    // ─── کل گفتگوی دوطرفه با یه نفر خاص (یا پیام‌های عمومی اگه partner_id=0) ──
    public static function get_conversation(int $user_id, int $partner_id, int $limit = 100): array {
        global $wpdb;
        if ($partner_id === 0) {
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT m.*, u.display_name as from_name FROM {$wpdb->prefix}dental_messages m
                 LEFT JOIN {$wpdb->users} u ON m.from_user_id=u.ID
                 WHERE m.to_user_id=0 ORDER BY m.created_at ASC LIMIT %d", $limit
            ), ARRAY_A);
        } else {
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT m.*, u.display_name as from_name FROM {$wpdb->prefix}dental_messages m
                 LEFT JOIN {$wpdb->users} u ON m.from_user_id=u.ID
                 WHERE (m.from_user_id=%d AND m.to_user_id=%d) OR (m.from_user_id=%d AND m.to_user_id=%d)
                 ORDER BY m.created_at ASC LIMIT %d",
                $user_id, $partner_id, $partner_id, $user_id, $limit
            ), ARRAY_A);
        }
        return $rows;
    }

    // ─── علامت‌گذاری همه‌ی پیام‌های یه مخاطب خاص به‌عنوان خوانده‌شده ──
    public static function mark_conversation_read(int $user_id, int $partner_id): void {
        global $wpdb;
        if ($partner_id === 0) {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}dental_messages SET is_read=1 WHERE to_user_id=0 AND from_user_id!=%d", $user_id
            ));
        } else {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}dental_messages SET is_read=1 WHERE to_user_id=%d AND from_user_id=%d", $user_id, $partner_id
            ));
        }
    }

    // ─── حذف پیام — فقط خودِ فرستنده بتونه حذف کنه (چک مالکیت) ──────
    public static function delete_message(int $message_id, int $requester_id): bool {
        global $wpdb;
        $msg = $wpdb->get_row($wpdb->prepare(
            "SELECT from_user_id FROM {$wpdb->prefix}dental_messages WHERE id=%d", $message_id
        ), ARRAY_A);
        if (!$msg || (int)$msg['from_user_id'] !== $requester_id) return false;
        $wpdb->delete($wpdb->prefix.'dental_messages', ['id'=>$message_id]);
        return true;
    }

    public static function mark_read(int $message_id, int $requester_id = 0): void {
        global $wpdb;
        // نکته امنیتی: اگه requester_id داده بشه، فقط اگه واقعاً گیرنده‌ی
        // همون پیام باشه (یا پیام عمومیه) اجازه‌ی علامت‌گذاری داره
        if ($requester_id) {
            $msg = $wpdb->get_row($wpdb->prepare(
                "SELECT to_user_id FROM {$wpdb->prefix}dental_messages WHERE id=%d", $message_id
            ), ARRAY_A);
            if (!$msg || ((int)$msg['to_user_id'] !== $requester_id && (int)$msg['to_user_id'] !== 0)) return;
        }
        $wpdb->update($wpdb->prefix.'dental_messages', ['is_read'=>1], ['id'=>$message_id], ['%d'], ['%d']);
    }

    public static function unread_count(int $user_id): int {
        global $wpdb;
        return (int)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dental_messages
             WHERE (to_user_id = %d OR to_user_id = 0) AND is_read = 0 AND from_user_id != %d",
            $user_id, $user_id
        ));
    }

    // ─── پاک‌سازی خودکار پیام‌های قدیمی‌تر از ۱ روز (هر روز با کرون) ──
    public static function cleanup_old_messages(): void {
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->prefix}dental_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)"
        );
    }

    // ═══════════════ وضعیت حضور ═══════════════════════════════════

    public static function statuses(): array {
        return [
            'available' => ['🟢', 'در دسترس',  '#2ECC9A'],
            'lunch'     => ['🍽️', 'ناهار',    '#8B5CF6'],
            'break'     => ['🛋️', 'استراحت',  '#5DADE2'],
            'left'      => ['🚪', 'خارج شده', '#B8860B'],
            'offline'   => ['⚫', 'آفلاین',    '#C8D4DC'],
        ];
    }
    // نکته: «غایب» و «آفلاین» دیگه قابل‌انتخاب دستی نیستن — چون وقتی
    // کاربر توی پنل نباشه، خودِ سیستم (بر اساس آخرین بازدید) این رو
    // به‌صورت خودکار به بقیه نشون می‌ده، نیازی به انتخاب دستی نداره.

    public static function set_my_status(int $user_id, string $status, string $note = ''): void {
        $statuses = array_keys(self::statuses());
        if (!in_array($status, $statuses)) $status = 'available';
        update_user_meta($user_id, '_dental_status', $status);
        update_user_meta($user_id, '_dental_status_note', sanitize_text_field($note));
        update_user_meta($user_id, '_dental_status_time', current_time('mysql'));
    }

    // وضعیت زنده همه کارکنان (برای نمایش به بقیه)
    // ─── بازطراحی کامل: وضعیت هر نفر مستقیم از سیستم واقعی حضور
    // (Dental_Attendance_Manager) میاد، نه یه فیلد جدا و دستی که قبلاً
    // هیچ‌وقت با دکمه‌های واقعی ناهار/استراحت آپدیت نمی‌شد ────────────
    public static function get_all_staff_status(): array {
        $users = get_users([
            'role__in' => ['dental_admin','dental_doctor','dental_secretary','dental_financial','dental_assistant','administrator'],
            'fields'   => ['ID','display_name'],
        ]);
        $out = [];
        foreach ($users as $u) {
            $status = 'offline'; $note = ''; $time = ''; $is_online = false;

            if (class_exists('Dental_Attendance_Manager')) {
                $is_online = Dental_Attendance_Manager::is_online($u->ID);
                $att = Dental_Attendance_Manager::get_today_status($u->ID);

                if (!$att['clocked_in']) {
                    // یا اصلاً امروز نیومده، یا از قبل خروج زده
                    if (!empty($att['attendance']['clock_out'])) {
                        $status = 'left';
                        $time   = $att['attendance']['clock_out'];
                    } else {
                        $status = 'offline';
                    }
                } else {
                    $open_break = Dental_Attendance_Manager::get_open_break($u->ID);
                    if ($open_break) {
                        $status = $open_break['break_type']; // 'lunch' یا 'break'
                        $time   = $open_break['start_time'];
                    } else {
                        $status = 'available';
                        $time   = $att['attendance']['clock_in'];
                    }
                }
            }

            $out[] = ['id'=>$u->ID, 'name'=>$u->display_name, 'status'=>$status, 'note'=>$note, 'time'=>$time, 'is_online'=>$is_online];
        }
        return $out;
    }
}
