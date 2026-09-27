<?php
defined('ABSPATH') || exit;

class Dental_Endpoint_Chart {

    private const NS = 'dental/v1';

    public function register_routes(): void {
        register_rest_route(self::NS, '/chart/save', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'save_condition'],
            'permission_callback' => [$this, 'can_edit'],
        ]);
        register_rest_route(self::NS, '/chart/set-mode', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'set_mode'],
            'permission_callback' => [$this, 'can_edit'],
        ]);
        register_rest_route(self::NS, '/chart/toggle-done', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'toggle_done'],
            'permission_callback' => [$this, 'can_edit'],
        ]);
        // ─── جدید: ذخیره‌ی خدمات نیم‌فک و کل‌دهان — قبلاً اصلاً این
        // endpoint ها وجود نداشتن، فقط توی حافظه‌ی مرورگر می‌موندن ────
        register_rest_route(self::NS, '/chart/set-halfarch', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'set_halfarch'],
            'permission_callback' => [$this, 'can_edit'],
        ]);
        register_rest_route(self::NS, '/chart/set-fullarch', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'set_fullarch'],
            'permission_callback' => [$this, 'can_edit'],
        ]);
        register_rest_route(self::NS, '/chart/(?P<patient_id>\d+)', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_conditions'],
            'permission_callback' => [$this, 'can_view'],
        ]);
    }

    // ─── ذخیره‌ی خدمات یه نیم‌فک خاص (q1 تا q4) — postmeta ساده،
    // دقیقاً هم‌الگو با _chart_done_map موجود ─────────────────────────
    public function set_halfarch(WP_REST_Request $req): WP_REST_Response {
        $patient_id = (int)$req->get_param('patient_id');
        $half       = sanitize_key($req->get_param('half'));
        $services   = array_map('sanitize_text_field', (array)($req->get_param('services') ?: []));
        if (!$patient_id || !in_array($half, ['q1','q2','q3','q4'])) {
            return new WP_REST_Response(['success'=>false,'message'=>'اطلاعات نامعتبر'], 400);
        }
        $data = get_post_meta($patient_id, '_chart_halfarch', true);
        if (!is_array($data)) $data = [];
        $existing_half = $data[$half] ?? [];
        $new_half = [];
        foreach ($services as $code) {
            // اگه قبلاً تیک خورده بود، همون تاریخِ اولیه‌ش رو نگه دار؛
            // فقط برای موارد تازه‌تیک‌خورده تاریخ امروز رو ثبت کن
            $new_half[$code] = is_array($existing_half[$code] ?? null)
                ? $existing_half[$code]
                : ['done' => true, 'date' => current_time('mysql'), 'by' => get_current_user_id()];
        }
        $data[$half] = $new_half;
        update_post_meta($patient_id, '_chart_halfarch', $data);
        return new WP_REST_Response(['success'=>true], 200);
    }

    // ─── ذخیره‌ی خدمات کل دهان ──────────────────────────────────────
    public function set_fullarch(WP_REST_Request $req): WP_REST_Response {
        $patient_id = (int)$req->get_param('patient_id');
        $services   = array_map('sanitize_text_field', (array)($req->get_param('services') ?: []));
        if (!$patient_id) {
            return new WP_REST_Response(['success'=>false,'message'=>'اطلاعات نامعتبر'], 400);
        }
        $existing = get_post_meta($patient_id, '_chart_fullarch', true);
        if (!is_array($existing)) $existing = [];
        $new_data = [];
        foreach ($services as $code) {
            $new_data[$code] = is_array($existing[$code] ?? null)
                ? $existing[$code]
                : ['done' => true, 'date' => current_time('mysql'), 'by' => get_current_user_id()];
        }
        update_post_meta($patient_id, '_chart_fullarch', $new_data);
        return new WP_REST_Response(['success'=>true], 200);
    }

    /**
     * ذخیره وضعیت دندان — شامل لیست چند درمان همزمان
     */
    public function save_condition(WP_REST_Request $req): WP_REST_Response {
        global $wpdb;

        $patient_id    = (int)$req->get_param('patient_id');
        $tooth_number  = (int)$req->get_param('tooth_number');
        $tooth_type    = sanitize_text_field($req->get_param('tooth_type') ?: 'permanent');
        $display_color = sanitize_text_field($req->get_param('display_color') ?: 'healthy');
        $treatments    = array_map('sanitize_text_field', (array)($req->get_param('treatments') ?: []));
        $notes         = sanitize_textarea_field($req->get_param('notes') ?: '');
        $exam_doctor   = (int)($req->get_param('exam_doctor_id') ?: get_current_user_id());
        $is_erupted    = (int)($req->get_param('is_erupted') ?? 1);

        $table = $wpdb->prefix . 'dental_tooth_conditions';

        // ─── رفع باگ: وقتی چند درمان با هم روی یک دندان ثبت می‌شود،
        // آرایه JSON آن‌ها ممکن است طولانی‌تر از ظرفیت ستون (اگر قبلاً
        // VARCHAR کوچک تعریف شده باشد) شود و خطای دیتابیس بدهد.
        // این دستور ستون را همیشه TEXT نگه می‌دارد — بی‌خطر و idempotent،
        // اجرای دوباره‌اش هم مشکلی ایجاد نمی‌کند.
        $wpdb->query("ALTER TABLE `{$table}` MODIFY `tooth_surface` TEXT NULL");

        $color_map = [
            'healthy'=>'#FFFFFF','caries'=>'#E8A87C','rct'=>'#9B7FD4',
            'crown'=>'#F0C040','implant'=>'#4DB6AC','missing'=>'#B0BEC5',
            'bridge'=>'#78C0E0','filling'=>'#5DADE2','veneer'=>'#D98FD6',
        ];

        // حذف رکورد قبلی این دندان (اگر وجود داشت)
        $wpdb->delete($table, [
            'patient_id'  => $patient_id,
            'tooth_number'=> $tooth_number,
            'tooth_type'  => $tooth_type,
        ], ['%d','%d','%s']);

        // اگر همه چیز سالم و بدون درمان است — فقط حذف کافیه
        if (empty($treatments) && $display_color === 'healthy') {
            return new WP_REST_Response(['success'=>true,'message'=>'پاک شد'], 200);
        }

        // ذخیره رکورد جدید
        $wpdb->insert($table, [
            'patient_id'      => $patient_id,
            'tooth_number'    => $tooth_number,
            'tooth_type'      => $tooth_type,
            'condition_code'  => $display_color,
            'condition_color' => $color_map[$display_color] ?? '#FFFFFF',
            'tooth_surface'   => wp_json_encode($treatments), // treatments در این ستون
            'is_erupted'      => $is_erupted,
            'notes'           => $notes,
            'recorded_date'   => current_time('Y-m-d'),
            'recorded_by'     => $exam_doctor,
            'is_active'       => 1,
        ], ['%d','%d','%s','%s','%s','%s','%d','%s','%s','%d','%d']);

        if ($wpdb->last_error) {
            return new WP_REST_Response(['success'=>false,'message'=>$wpdb->last_error], 500);
        }

        return new WP_REST_Response(['success'=>true,'message'=>'ذخیره شد'], 200);
    }

    public function set_mode(WP_REST_Request $req): WP_REST_Response {
        $patient_id = (int)$req->get_param('patient_id');
        $mode = in_array($req->get_param('mode'),['adult','peds']) ? $req->get_param('mode') : 'adult';
        update_post_meta($patient_id, '_chart_mode', $mode);
        return new WP_REST_Response(['success'=>true], 200);
    }

    public function toggle_done(WP_REST_Request $req): WP_REST_Response {
        global $wpdb;
        $patient_id    = (int)$req->get_param('patient_id');
        $key           = sanitize_text_field($req->get_param('key'));
        $code          = sanitize_text_field($req->get_param('code'));
        $done          = (int)$req->get_param('done');
        $done_doctor   = (int)$req->get_param('done_doctor_id') ?: get_current_user_id();

        // ذخیره وضعیت تیک در post_meta
        $done_data = get_post_meta($patient_id, '_chart_done_map', true) ?: [];
        if(!is_array($done_data)) $done_data = [];

        $item_key = $key . '_' . $code;
        if ($done) {
            $done_data[$item_key] = [
                'done'       => 1,
                'doctor_id'  => $done_doctor,
                'doctor_name'=> get_userdata($done_doctor) ? get_userdata($done_doctor)->display_name : '',
                'done_at'    => current_time('mysql'),
            ];
        } else {
            unset($done_data[$item_key]);
        }

        update_post_meta($patient_id, '_chart_done_map', $done_data);

        // ثبت در «کارکرد پزشکان» تا مدیریت این کار انجام‌شده را ببیند
        if ($done && class_exists('Dental_Doctor_Activity')) {
            Dental_Doctor_Activity::log($done_doctor, $patient_id, 'treatment_done', Dental_Doctor_Activity::format_item_key($item_key));
        }

        return new WP_REST_Response(['success'=>true], 200);
    }

    public function get_conditions(WP_REST_Request $req): WP_REST_Response {
        global $wpdb;
        $patient_id = (int)$req->get_param('patient_id');

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_tooth_conditions WHERE patient_id=%d AND is_active=1",
            $patient_id
        ), ARRAY_A);

        $done_map = get_post_meta($patient_id, '_chart_done_map', true) ?: [];

        $result = [];
        foreach ($rows as $row) {
            $key = $row['tooth_number'].'_'.$row['tooth_type'];
            $treatments = json_decode($row['tooth_surface'] ?: '[]', true) ?: [];

            // بازسازی done_map برای این دندان
            $tooth_done_map = [];
            $tooth_done_doc_map = [];
            foreach ($done_map as $dk => $dv) {
                if (str_starts_with($dk, $key.'_')) {
                    $code = substr($dk, strlen($key)+1);
                    $tooth_done_map[$code]     = $dv['done'] ?? 0;
                    $tooth_done_doc_map[$code] = $dv['doctor_id'] ?? 0;
                }
            }

            $result[$key] = [
                'tooth_number'    => $row['tooth_number'],
                'tooth_type'      => $row['tooth_type'],
                'display_color'   => $row['condition_code'],
                'treatments'      => $treatments,
                'notes'           => $row['notes'],
                'exam_doctor_id'  => $row['recorded_by'],
                'is_erupted'      => $row['is_erupted'],
                'done_map'        => $tooth_done_map,
                'done_doctor_map' => $tooth_done_doc_map,
            ];
        }

        return new WP_REST_Response(['success'=>true,'conditions'=>$result,
            'halfarch' => get_post_meta($patient_id, '_chart_halfarch', true) ?: (object)[],
            'fullarch' => get_post_meta($patient_id, '_chart_fullarch', true) ?: (object)[],
        ], 200);
    }

    /** فقط doctor و admin می‌توانند ویرایش کنند — رفع باگ: قبلاً از
     * current_user_can('dental_edit_chart') استفاده می‌شد، یه capability
     * سفارشی که معمولاً واقعاً grant نمی‌شه (همون خانواده‌ی باگی که
     * بارها توی این پلاگین دیدیم) — نتیجه‌ش این بود که حتی دکتر/مدیر
     * کلینیک هم ممکن بود نتونن ذخیره کنن، بدون هیچ خطای واضحی. */
    public function can_edit(WP_REST_Request $req): bool {
        if (current_user_can('manage_options')) return true;

        $cu = wp_get_current_user();
        $cu_roles = (array)$cu->roles;
        if (in_array('dental_admin', $cu_roles, true)) return true;
        if (!in_array('dental_doctor', $cu_roles, true)) return false;

        // دکتر فقط روی چارت بیمار خودش — دقیقاً همون محدودیتی که برای
        // dental_edit_patient توی Dental_Roles_Manager پیاده شده، اینجا
        // هم اعمال می‌شه؛ قبلاً فقط نقش چک می‌شد، پس دکتر B می‌تونست
        // چارت بیمار دکتر A رو هم ویرایش کنه (IDOR).
        $patient_id = (int)$req->get_param('patient_id');
        if (!$patient_id) return false;
        $patient_doctor = (int)get_post_meta($patient_id, '_treatment_doctor_id', true);
        return $patient_doctor === $cu->ID;
    }

    /** doctor، secretary و admin می‌توانند ببینند */
    public function can_view(): bool {
        if (!is_user_logged_in()) return false;
        $cu_roles = (array)wp_get_current_user()->roles;
        return current_user_can('manage_options') || !empty(array_intersect($cu_roles, ['dental_admin','dental_doctor','dental_secretary','dental_assistant']));
    }
}
