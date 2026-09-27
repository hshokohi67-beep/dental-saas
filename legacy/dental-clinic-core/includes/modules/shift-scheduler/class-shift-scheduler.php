<?php
defined('ABSPATH') || exit;

/**
 * مدیریت اتاق‌ها و تخصیص شیفت (چرخش دکتر/دستیار در اتاق‌ها)
 */
class Dental_Shift_Scheduler {

    // ─── اتاق‌ها ──────────────────────────────────────────────────
    public static function get_rooms(): array {
        global $wpdb;
        $rows = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}dental_shift_rooms ORDER BY sort_order ASC, room_number ASC", ARRAY_A
        );
        // اگه هنوز هیچ اتاقی نساخته، ۱۰ تا پیش‌فرض بساز (مثل ابزار اصلی)
        if (empty($rows)) {
            for ($i = 1; $i <= 10; $i++) {
                $wpdb->insert($wpdb->prefix.'dental_shift_rooms', [
                    'room_number' => $i, 'active_morning' => 1, 'active_evening' => 1, 'sort_order' => $i,
                ], ['%d','%d','%d','%d']);
            }
            return self::get_rooms();
        }
        return $rows;
    }

    public static function save_room(int $id, array $data): void {
        global $wpdb;
        $update = [
            'active_morning'       => !empty($data['active_morning']) ? 1 : 0,
            'active_evening'       => !empty($data['active_evening']) ? 1 : 0,
            'fixed_doctor_morning' => !empty($data['fixed_doctor_morning']) ? (int)$data['fixed_doctor_morning'] : null,
            'fixed_doctor_evening' => !empty($data['fixed_doctor_evening']) ? (int)$data['fixed_doctor_evening'] : null,
        ];
        $wpdb->update($wpdb->prefix.'dental_shift_rooms', $update, ['id'=>$id]);
    }

    // ─── لیست دکتر/دستیار/پذیرش — از کاربران واقعی پلاگین ───────────
    public static function get_doctors(): array {
        return get_users(['role'=>'dental_doctor', 'fields'=>['ID','display_name'], 'orderby'=>'display_name']);
    }
    public static function get_assistants(): array {
        return get_users(['role'=>'dental_assistant', 'fields'=>['ID','display_name'], 'orderby'=>'display_name']);
    }
    public static function get_reception_staff(): array {
        return get_users(['role'=>'dental_secretary', 'fields'=>['ID','display_name'], 'orderby'=>'display_name']);
    }
    // دستیار + پذیرش با هم (برای اسلات‌هایی که هر دو می‌تونن توشون باشن)
    public static function get_assistants_and_reception(): array {
        return array_merge(self::get_assistants(), self::get_reception_staff());
    }

    // ─── خواندن/نوشتن یک سلول (اتاق/پذیرش/تحویل/آزاد) در یک روز و شیفت ──
    public static function get_cell(string $work_date, string $shift, string $slot_key): array {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_shift_assignments WHERE work_date=%s AND shift=%s AND slot_key=%s",
            $work_date, $shift, $slot_key
        ), ARRAY_A);
        if (!$row) return ['doctor_id'=>null,'assistant_id'=>null,'is_double'=>0,'doctor2_id'=>null,'assistant2_id'=>null,'single_assistant'=>0,'extra'=>[],'is_locked'=>0];
        $row['extra'] = json_decode($row['extra_data'] ?: '{}', true) ?: [];
        return $row;
    }

    // خواندن کل یک روز (همه سلول‌ها) — برای رندر جدول هفتگی، یه کوئری به‌جای چندتا
    public static function get_day_cells(string $work_date): array {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_shift_assignments WHERE work_date=%s", $work_date
        ), ARRAY_A);
        $out = [];
        foreach ($rows as $r) {
            $r['extra'] = json_decode($r['extra_data'] ?: '{}', true) ?: [];
            $out[$r['shift'].'_'.$r['slot_key']] = $r;
        }
        return $out;
    }

    public static function save_cell(string $work_date, string $shift, string $slot_key, array $data): void {
        global $wpdb;
        $existing_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}dental_shift_assignments WHERE work_date=%s AND shift=%s AND slot_key=%s",
            $work_date, $shift, $slot_key
        ));
        $row = [
            'doctor_id'        => !empty($data['doctor_id']) ? (int)$data['doctor_id'] : null,
            'assistant_id'     => !empty($data['assistant_id']) ? (int)$data['assistant_id'] : null,
            'is_double'        => !empty($data['is_double']) ? 1 : 0,
            'doctor2_id'       => !empty($data['doctor2_id']) ? (int)$data['doctor2_id'] : null,
            'assistant2_id'    => !empty($data['assistant2_id']) ? (int)$data['assistant2_id'] : null,
            'single_assistant' => !empty($data['single_assistant']) ? 1 : 0,
            'extra_data'       => wp_json_encode($data['extra'] ?? []),
        ];
        if ($existing_id) {
            $wpdb->update($wpdb->prefix.'dental_shift_assignments', $row, ['id'=>$existing_id]);
        } else {
            $row['work_date'] = $work_date; $row['shift'] = $shift; $row['slot_key'] = $slot_key;
            $wpdb->insert($wpdb->prefix.'dental_shift_assignments', $row);
        }
    }

    public static function toggle_lock(string $work_date, string $shift, string $slot_key, bool $locked): void {
        global $wpdb;
        // مطمئن شو ردیف وجود داره (حتی خالی) تا بشه قفلش کرد
        self::save_cell($work_date, $shift, $slot_key, self::get_cell($work_date, $shift, $slot_key));
        $wpdb->update($wpdb->prefix.'dental_shift_assignments', ['is_locked'=>$locked?1:0],
            ['work_date'=>$work_date,'shift'=>$shift,'slot_key'=>$slot_key]);
    }

    // ─── کدام دکتر/دستیار در این تاریخ/شیفت طبق برنامه ماهانه کار می‌کنه ──
    // نکته: مقدار 'd' (دوشیفتی) هم برای صبح هم برای عصر حساب می‌شه
    public static function doctors_working(string $work_date, string $shift): array {
        global $wpdb;
        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT person_id FROM {$wpdb->prefix}dental_monthly_rota
             WHERE work_date=%s AND person_type='doctor' AND (shift=%s OR shift='d')",
            $work_date, $shift
        ));
        if (empty($ids)) return [];
        $all = self::get_doctors();
        return array_values(array_filter($all, fn($d) => in_array($d->ID, $ids)));
    }
    public static function assistants_working(string $work_date, string $shift): array {
        global $wpdb;
        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT person_id FROM {$wpdb->prefix}dental_monthly_rota
             WHERE work_date=%s AND person_type='assistant' AND (shift=%s OR shift='d')",
            $work_date, $shift
        ));
        if (empty($ids)) return [];
        $all = self::get_assistants();
        return array_values(array_filter($all, fn($a) => in_array($a->ID, $ids)));
    }

    // ─── امروز این دکتر توی کدوم اتاقه؟ (برای نمایش توی پذیرش/پرونده روزانه) ──
    public static function get_doctor_room_today(int $doctor_id): ?array {
        global $wpdb;
        $today = current_time('Y-m-d');
        $shift = (int)current_time('H') < 13 ? 'm' : 'e';

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT a.slot_key, r.room_number FROM {$wpdb->prefix}dental_shift_assignments a
             LEFT JOIN {$wpdb->prefix}dental_shift_rooms r ON a.slot_key = CONCAT('r', r.id)
             WHERE a.work_date=%s AND a.shift=%s
             AND (a.doctor_id=%d OR (a.is_double=1 AND a.doctor2_id=%d))
             AND a.slot_key LIKE 'r%%' LIMIT 1",
            $today, $shift, $doctor_id, $doctor_id
        ), ARRAY_A);

        if (!$row || !$row['room_number']) return null;
        return ['room_number' => (int)$row['room_number'], 'shift' => $shift === 'm' ? 'صبح' : 'عصر'];
    }

    // ─── وضعیت یک نفر در یک روز خاص (تک‌مقداره: خالی/صبح/عصر/مرخصی/دوشیفتی) ──
    public static function get_rota_day(int $person_id, string $person_type, string $work_date): string {
        global $wpdb;
        $shift = $wpdb->get_var($wpdb->prepare(
            "SELECT shift FROM {$wpdb->prefix}dental_monthly_rota WHERE person_id=%d AND person_type=%s AND work_date=%s LIMIT 1",
            $person_id, $person_type, $work_date
        ));
        return $shift ?: '';
    }

    public static function is_rota_locked(int $person_id, string $person_type, string $work_date): bool {
        global $wpdb;
        return (bool)$wpdb->get_var($wpdb->prepare(
            "SELECT is_locked FROM {$wpdb->prefix}dental_monthly_rota WHERE person_id=%d AND person_type=%s AND work_date=%s LIMIT 1",
            $person_id, $person_type, $work_date
        ));
    }

    public static function toggle_rota_lock(int $person_id, string $person_type, string $work_date): bool {
        global $wpdb;
        $current = self::is_rota_locked($person_id, $person_type, $work_date);
        $new_locked = !$current;
        // اگه ردیفی نبود (روز خالیه)، یه ردیف خالی بساز تا بشه قفلش کرد
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}dental_monthly_rota WHERE person_id=%d AND person_type=%s AND work_date=%s",
            $person_id, $person_type, $work_date
        ));
        if (!$exists) {
            $wpdb->insert($wpdb->prefix.'dental_monthly_rota', [
                'person_id'=>$person_id,'person_type'=>$person_type,'work_date'=>$work_date,'shift'=>'','is_locked'=>1,
            ], ['%d','%s','%s','%s','%d']);
        } else {
            $wpdb->update($wpdb->prefix.'dental_monthly_rota', ['is_locked'=>$new_locked?1:0],
                ['person_id'=>$person_id,'person_type'=>$person_type,'work_date'=>$work_date]);
        }
        return $new_locked;
    }

    // چرخش: '' → m → e → l → '' — دقیقاً مثل رفتار ابزار اصلی (روزهای قفل‌شده تغییر نمی‌کنن)
    public static function cycle_rota_day(int $person_id, string $person_type, string $work_date): string {
        global $wpdb;
        if (self::is_rota_locked($person_id, $person_type, $work_date)) {
            return self::get_rota_day($person_id, $person_type, $work_date); // قفله، عوض نشو
        }
        $current = self::get_rota_day($person_id, $person_type, $work_date);
        $next = ['' => 'm', 'm' => 'e', 'e' => 'l', 'l' => '', 'd' => ''][$current] ?? '';

        $wpdb->delete($wpdb->prefix.'dental_monthly_rota', [
            'person_id'=>$person_id,'person_type'=>$person_type,'work_date'=>$work_date,
        ]);
        if ($next !== '') {
            $wpdb->insert($wpdb->prefix.'dental_monthly_rota', [
                'person_id'=>$person_id,'person_type'=>$person_type,'work_date'=>$work_date,'shift'=>$next,
            ], ['%d','%s','%s','%s']);
        }
        return $next;
    }

    // ثبت مستقیم یه مقدار خاص (برای الگوریتم چیدمان خودکار — نه چرخش)
    private static function set_rota_value(int $person_id, string $person_type, string $work_date, string $value): void {
        global $wpdb;
        $wpdb->delete($wpdb->prefix.'dental_monthly_rota', [
            'person_id'=>$person_id,'person_type'=>$person_type,'work_date'=>$work_date,
        ]);
        if ($value !== '') {
            $wpdb->insert($wpdb->prefix.'dental_monthly_rota', [
                'person_id'=>$person_id,'person_type'=>$person_type,'work_date'=>$work_date,'shift'=>$value,
            ], ['%d','%s','%s','%s']);
        }
    }

    // ─── هشدار: طبق برنامه ماهانه باید کار می‌کرد ولی ورود نزده ────
    // فقط اگه واقعاً برای امروز/این ماه برنامه پر شده باشه چک می‌کنه —
    // برای مطبی که این بخش رو استفاده نمی‌کنه، همیشه خالی برمی‌گرده.
    public static function get_rota_mismatch_alerts(): array {
        if (!class_exists('Dental_Shift_Scheduler') || !class_exists('Dental_Attendance_Manager')) return [];
        $today = current_time('Y-m-d');
        $alerts = [];

        foreach (['doctor' => Dental_Shift_Scheduler::get_doctors(), 'assistant' => Dental_Shift_Scheduler::get_assistants()] as $type => $people) {
            foreach ($people as $p) {
                $rota_today = Dental_Shift_Scheduler::get_rota_day($p->ID, $type, $today);
                if (!$rota_today || $rota_today === 'l') continue; // امروز کاری نداره یا مرخصیه

                $status = Dental_Attendance_Manager::get_today_status($p->ID);
                if (!$status['clocked_in']) {
                    $shift_label = ['m'=>'صبح','e'=>'عصر','d'=>'صبح و عصر'][$rota_today] ?? '';
                    $alerts[] = [
                        'name'  => $p->display_name,
                        'type'  => $type === 'doctor' ? 'دکتر' : 'دستیار',
                        'shift' => $shift_label,
                    ];
                }
            }
        }
        return $alerts;
    }

    public static function get_month_rota_flat(string $person_type, string $from_date, string $to_date): array {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT person_id, work_date, shift, is_locked FROM {$wpdb->prefix}dental_monthly_rota
             WHERE person_type=%s AND work_date BETWEEN %s AND %s", $person_type, $from_date, $to_date
        ), ARRAY_A);
        $map = [];
        $locks = [];
        foreach ($rows as $r) {
            $map[$r['person_id']][$r['work_date']] = $r['shift'];
            if ($r['is_locked']) $locks[$r['person_id']][$r['work_date']] = true;
        }
        return [$map, $locks];
    }

    // ─── چیدمان خودکار اتاق‌ها برای یک روز/شیفت خاص — پورت از ابزار اصلی ──
    private static function shuffle_arr(array $a): array { shuffle($a); return $a; }

    public static function auto_arrange_day_shift(string $work_date, string $shift): void {
        $doctors    = self::shuffle_arr(self::doctors_working($work_date, $shift));
        $assistants = self::shuffle_arr(self::assistants_working($work_date, $shift));
        $rooms = array_values(array_filter(self::get_rooms(), fn($r) => $shift==='m' ? $r['active_morning'] : $r['active_evening']));

        if (empty($doctors) && empty($assistants)) {
            foreach ($rooms as $r) self::save_cell($work_date, $shift, 'r'.$r['id'], []);
            self::save_cell($work_date, $shift, 'recv', ['extra'=>['assistantIds'=>[],'radiologyId'=>'']]);
            self::save_cell($work_date, $shift, 'handover', []);
            return;
        }

        $remaining_dr = $doctors;
        $room_assign = []; // room_id => doctor_id

        // اتاق‌های دکتر ثابت
        foreach ($rooms as $room) {
            $fixed_id = $shift==='m' ? $room['fixed_doctor_morning'] : $room['fixed_doctor_evening'];
            if (!$fixed_id) continue;
            foreach ($remaining_dr as $i => $dr) {
                if ($dr->ID == $fixed_id) {
                    $room_assign[$room['id']] = $dr->ID;
                    unset($remaining_dr[$i]);
                    break;
                }
            }
        }
        $remaining_dr = array_values($remaining_dr);

        // بقیه اتاق‌های فعال ← بقیه دکترها (تصادفی)
        $rem_rooms = self::shuffle_arr(array_values(array_filter($rooms, fn($r) => !isset($room_assign[$r['id']]))));
        $rem_dr = self::shuffle_arr($remaining_dr);
        $n = min(count($rem_rooms), count($rem_dr));
        for ($i = 0; $i < $n; $i++) $room_assign[$rem_rooms[$i]['id']] = $rem_dr[$i]->ID;

        // تخصیص دستیار به اتاق‌های دارای دکتر — عدالت (کمترین تکرار جفت با همون دکتر)
        static $pair_count = []; // در طول اجرای یک درخواست پابرجاست (per-request cache)
        $avail_as = $assistants;
        foreach (self::shuffle_arr(array_keys($room_assign)) as $room_id) {
            $doc_id = $room_assign[$room_id];
            if (empty($avail_as)) break;
            usort($avail_as, function($a,$b) use ($pair_count,$doc_id) {
                $ca = $pair_count[$a->ID.'_'.$doc_id] ?? 0; $cb = $pair_count[$b->ID.'_'.$doc_id] ?? 0;
                return $ca <=> $cb;
            });
            $chosen = array_shift($avail_as);
            self::save_cell($work_date, $shift, 'r'.$room_id, ['doctor_id'=>$doc_id, 'assistant_id'=>$chosen->ID]);
            $pair_count[$chosen->ID.'_'.$doc_id] = ($pair_count[$chosen->ID.'_'.$doc_id] ?? 0) + 1;
        }
        foreach ($rooms as $r) {
            if (!isset($room_assign[$r['id']])) self::save_cell($work_date, $shift, 'r'.$r['id'], []);
        }

        // پذیرش — دستی می‌مونه، خالی می‌کنیم
        self::save_cell($work_date, $shift, 'recv', ['extra'=>['assistantIds'=>[],'radiologyId'=>'']]);

        // تحویل وسایل — کمترین‌تکرار، ترجیحاً بدون دوشیفتی
        static $handover_count = [];
        $no_double = array_values(array_filter($assistants, fn($a) => Dental_Shift_Scheduler::get_rota_day($a->ID,'assistant',$work_date) !== 'd'));
        $pool = !empty($no_double) ? $no_double : $assistants;
        if (!empty($pool)) {
            usort($pool, fn($a,$b) => ($handover_count[$a->ID] ?? 0) <=> ($handover_count[$b->ID] ?? 0));
            self::save_cell($work_date, $shift, 'handover', ['assistant_id'=>$pool[0]->ID]);
            $handover_count[$pool[0]->ID] = ($handover_count[$pool[0]->ID] ?? 0) + 1;
        } else {
            self::save_cell($work_date, $shift, 'handover', []);
        }
    }

    public static function auto_arrange_week(string $from_date, string $to_date): void {
        $start = strtotime($from_date); $end = strtotime($to_date);
        for ($ts = $start; $ts <= $end; $ts += DAY_IN_SECONDS) {
            if ((int)date('N', $ts) === 5) continue; // جمعه
            $g = date('Y-m-d', $ts);
            self::auto_arrange_day_shift($g, 'm');
            self::auto_arrange_day_shift($g, 'e');
        }
    }

    // ─── سیستم هشدار — پورت از ابزار اصلی: کمبود اتاق، دستیار بدون کار،
    // دستیار هم‌زمان در دو اتاق، تکرار زیاد یه جفت دکتر+دستیار ─────────
    public static function get_week_warnings(string $from_date, string $to_date): array {
        $warns = [];
        $pair_count = [];
        $rooms = self::get_rooms();

        $start = strtotime($from_date); $end = strtotime($to_date);
        for ($ts = $start; $ts <= $end; $ts += DAY_IN_SECONDS) {
            if ((int)date('N', $ts) === 5) continue; // جمعه
            $g = date('Y-m-d', $ts);
            $day_label = Dental_Jalali::day_name($g) . ' ' . Dental_Jalali::to_jalali($g,'d');

            foreach (['m'=>'صبح','e'=>'عصر'] as $shift => $shift_label) {
                $dr_w = self::doctors_working($g, $shift);
                $as_w = self::assistants_working($g, $shift);
                if (empty($dr_w) && empty($as_w)) continue;

                $active_rooms = array_values(array_filter($rooms, fn($r) => $shift==='m' ? $r['active_morning'] : $r['active_evening']));
                $capacity = count($active_rooms);
                $double_rooms = 0;

                $assigned_dr_ids = [];
                $room_assistant_count = [];
                foreach ($active_rooms as $room) {
                    $cell = self::get_cell($g, $shift, 'r'.$room['id']);
                    if (!empty($cell['is_double'])) $double_rooms++;
                    if ($cell['doctor_id']) $assigned_dr_ids[] = (int)$cell['doctor_id'];
                    if (!empty($cell['is_double']) && $cell['doctor2_id']) $assigned_dr_ids[] = (int)$cell['doctor2_id'];
                    if ($cell['assistant_id']) $room_assistant_count[$cell['assistant_id']] = ($room_assistant_count[$cell['assistant_id']] ?? 0) + 1;
                    if (!empty($cell['is_double']) && empty($cell['single_assistant']) && $cell['assistant2_id']) {
                        $room_assistant_count[$cell['assistant2_id']] = ($room_assistant_count[$cell['assistant2_id']] ?? 0) + 1;
                    }
                    if ($cell['doctor_id'] && $cell['assistant_id']) {
                        $k = $cell['assistant_id'].'_'.$cell['doctor_id'];
                        $pair_count[$k] = ($pair_count[$k] ?? 0) + 1;
                    }
                    if ($cell['doctor_id'] && !$cell['assistant_id']) {
                        $dr = get_userdata($cell['doctor_id']);
                        $warns[] = "⚠️ {$day_label} ({$shift_label}) - اتاق {$room['room_number']}: " . ($dr?$dr->display_name:'') . ' بدون دستیار';
                    }
                }
                $real_capacity = $capacity + $double_rooms;
                if (count($dr_w) > $real_capacity) {
                    $warns[] = "🏥 {$day_label} ({$shift_label}): " . count($dr_w) . " دکتر برای {$real_capacity} ظرفیت اتاق";
                }
                foreach ($dr_w as $dr) {
                    if (!in_array($dr->ID, $assigned_dr_ids)) $warns[] = "❌ {$day_label} ({$shift_label}): {$dr->display_name} اتاق ندارد";
                }
                foreach ($room_assistant_count as $aid => $cnt) {
                    if ($cnt > 1) {
                        $a = get_userdata($aid);
                        $warns[] = "❌ {$day_label} ({$shift_label}): دستیار " . ($a?$a->display_name:'') . " در {$cnt} اتاق مختلف ثبت شده (اشتباه)";
                    }
                }
                foreach ($as_w as $a) {
                    if (empty($room_assistant_count[$a->ID])) {
                        $warns[] = "⚠️ {$day_label} ({$shift_label}): دستیار {$a->display_name} کاری تخصیص نگرفته";
                    }
                }
            }
        }

        foreach ($pair_count as $key => $cnt) {
            if ($cnt >= 4) {
                [$aid, $did] = explode('_', $key);
                $a = get_userdata($aid); $dr = get_userdata($did);
                $warns[] = "🔁 دستیار " . ($a?$a->display_name:'') . " این هفته {$cnt} بار با " . ($dr?$dr->display_name:'') . " همکار بوده";
            }
        }
        return $warns;
    }

    public static function get_assistant_prefs(int $user_id): array {
        return [
            'shift'      => get_user_meta($user_id, '_dental_assistant_shift_type', true) ?: 'flexible',
            'can_double' => (bool)get_user_meta($user_id, '_dental_assistant_can_double', true),
        ];
    }
    public static function save_assistant_prefs(int $user_id, string $shift_type, bool $can_double): void {
        update_user_meta($user_id, '_dental_assistant_shift_type', in_array($shift_type,['morning','evening','flexible']) ? $shift_type : 'flexible');
        update_user_meta($user_id, '_dental_assistant_can_double', $can_double ? 1 : 0);
    }

    // ─── شمارش دکترهای صبح/عصر یک روز خاص (بر اساس برنامه ماهانه دکترها) ──
    private static function get_day_doctor_counts(string $work_date): array {
        $m = self::doctors_working($work_date, 'm');
        $e = self::doctors_working($work_date, 'e');
        return ['m' => count($m), 'e' => count($e)];
    }

    // ─── الگوریتم چیدمان خودکار برنامه ماهانه دستیارها بر اساس تعداد
    // دکترهای صبح/عصر همون روز — پورت مستقیم از منطق ابزار اصلی ─────
    public static function auto_schedule_assistants(int $jy, int $jm): array {
        $assistants = self::get_assistants();
        if (empty($assistants)) return ['ok'=>false,'message'=>'دستیاری ثبت نشده'];

        $days_in_month = Dental_Jalali::days_in_jalali_month($jy, $jm);
        $dates = [];
        for ($d = 1; $d <= $days_in_month; $d++) {
            $dates[$d] = Dental_Jalali::to_gregorian(sprintf('%04d/%02d/%02d', $jy, $jm, $d));
        }

        $prefs = [];
        foreach ($assistants as $a) $prefs[$a->ID] = self::get_assistant_prefs($a->ID);

        // ۱) پاک‌کردن همه خانه‌ها به‌جز مرخصی و قفل‌شده‌ها
        foreach ($dates as $d => $g) {
            foreach ($assistants as $a) {
                if (self::is_rota_locked($a->ID, 'assistant', $g)) continue;
                $v = self::get_rota_day($a->ID, 'assistant', $g);
                if ($v && $v !== 'l') self::set_rota_value($a->ID, 'assistant', $g, '');
            }
        }

        $dbl_count = []; $eve_count = [];
        foreach ($assistants as $a) { $dbl_count[$a->ID] = 0; $eve_count[$a->ID] = 0; }

        foreach ($dates as $d => $g) {
            if ((int)date('N', strtotime($g)) === 5) continue; // جمعه رد شو

            $counts = self::get_day_doctor_counts($g);
            $drM = $counts['m']; $drE = $counts['e'];
            if ($drM === 0 && $drE === 0) continue;

            $avail  = array_values(array_filter($assistants, fn($a) => self::get_rota_day($a->ID,'assistant',$g) !== 'l'));
            $locked = array_values(array_filter($avail, fn($a) => self::is_rota_locked($a->ID,'assistant',$g)));
            $free   = array_values(array_filter($avail, fn($a) => !self::is_rota_locked($a->ID,'assistant',$g)));

            $fixed_m = array_values(array_filter($free, fn($a) => $prefs[$a->ID]['shift'] === 'morning'));
            $fixed_e = array_values(array_filter($free, fn($a) => $prefs[$a->ID]['shift'] === 'evening'));
            $flex    = array_values(array_filter($free, fn($a) => $prefs[$a->ID]['shift'] === 'flexible'));
            usort($flex, fn($a,$b) => $eve_count[$a->ID] <=> $eve_count[$b->ID]);

            $mA = 0; $eA = 0;
            foreach ($locked as $a) {
                $v = self::get_rota_day($a->ID, 'assistant', $g);
                if ($v === 'm') $mA++;
                elseif ($v === 'e') { $eA++; $eve_count[$a->ID]++; }
                elseif ($v === 'd') { $mA++; $eA++; $dbl_count[$a->ID]++; }
            }

            foreach ($fixed_m as $a) { if ($mA < $drM) { self::set_rota_value($a->ID,'assistant',$g,'m'); $mA++; } }
            foreach ($fixed_e as $a) { if ($eA < $drE) { self::set_rota_value($a->ID,'assistant',$g,'e'); $eA++; } }

            $needM = max(0, $drM - $mA); $needE = max(0, $drE - $eA);
            foreach ($flex as $a) {
                if ($needE > 0) { self::set_rota_value($a->ID,'assistant',$g,'e'); $eve_count[$a->ID]++; $eA++; $needE--; }
                elseif ($needM > 0) { self::set_rota_value($a->ID,'assistant',$g,'m'); $mA++; $needM--; }
            }

            // محاسبه دقیق نیاز باقی‌مانده بعد از تخصیص‌های بالا
            $count_val = fn($val) => count(array_filter($assistants, fn($a) => in_array(self::get_rota_day($a->ID,'assistant',$g), $val)));
            $needM = max(0, $drM - $count_val(['m','d']));
            $needE = max(0, $drE - $count_val(['e','d']));

            // دوشیفتی‌کردن افراد مجاز برای پوشش کمبود — دو پاس (اول رعایت «نه‌پشت‌سرهم»)
            foreach ([true, false] as $strict) {
                if ($drM > 0 && $needM > 0) {
                    $cands = array_values(array_filter($free, fn($a) => $prefs[$a->ID]['can_double'] && self::get_rota_day($a->ID,'assistant',$g)==='e'));
                    usort($cands, fn($a,$b) => $dbl_count[$a->ID] <=> $dbl_count[$b->ID]);
                    foreach ($cands as $a) {
                        if ($needM <= 0) break;
                        if ($strict && $d > 1 && self::get_rota_day($a->ID,'assistant',$dates[$d-1]) === 'd') continue;
                        self::set_rota_value($a->ID,'assistant',$g,'d'); $dbl_count[$a->ID]++; $needM--;
                    }
                }
                if ($drE > 0 && $needE > 0) {
                    $cands = array_values(array_filter($free, fn($a) => $prefs[$a->ID]['can_double'] && self::get_rota_day($a->ID,'assistant',$g)==='m'));
                    usort($cands, fn($a,$b) => $dbl_count[$a->ID] <=> $dbl_count[$b->ID]);
                    foreach ($cands as $a) {
                        if ($needE <= 0) break;
                        if ($strict && $d > 1 && self::get_rota_day($a->ID,'assistant',$dates[$d-1]) === 'd') continue;
                        self::set_rota_value($a->ID,'assistant',$g,'d'); $dbl_count[$a->ID]++; $needE--;
                    }
                }
                if ($needM === 0 && $needE === 0) break;
            }
        }

        // پاس نهایی: حداقل ۷ عصر برای انعطاف‌پذیرها در طول ماه
        foreach ($assistants as $a) {
            if ($prefs[$a->ID]['shift'] !== 'flexible') continue;
            $eve = $eve_count[$a->ID];
            if ($eve >= 7) continue;
            foreach ($dates as $d => $g) {
                if ($eve >= 7) break;
                if ((int)date('N', strtotime($g)) === 5) continue;
                if (self::get_rota_day($a->ID,'assistant',$g) !== 'm') continue;
                $drE = self::get_day_doctor_counts($g)['e'];
                if ($drE === 0) continue;
                $eCnt = count(array_filter($assistants, fn($x) => in_array(self::get_rota_day($x->ID,'assistant',$g), ['e','d'])));
                if ($eCnt < $drE) { self::set_rota_value($a->ID,'assistant',$g,'e'); $eve_count[$a->ID]++; $eve++; }
            }
        }

        return ['ok'=>true, 'double_counts'=>$dbl_count];
    }

}
