<?php
defined('ABSPATH') || exit;

/**
 * مسیر درمان (Treatment Pathway) — فاز ۱.
 *
 * نکته‌ی مهم امنیتی: کل این ماژول پشت یه کلید تنظیمات (dental_pathway_enabled)
 * است، پیش‌فرض خاموش. اگه get_option('dental_pathway_enabled') فعال نباشه،
 * هیچ‌جای دیگه‌ی پلاگین (چارت، پرونده‌ی بیمار) به این کلاس دست نمی‌زنه —
 * یعنی برای غیرفعال‌کردن کامل، فقط کافیه این یه گزینه رو خاموش کنید،
 * بدون نیاز به حذف فایل یا دستکاری دیتابیس.
 */
class Dental_Pathway_Manager {

    public static function is_enabled(): bool {
        return class_exists('Dental_Features') && Dental_Features::enabled('treatment_pathway');
    }

    public static function get_status_labels(): array {
        return [
            'pending'     => ['⏳ در انتظار', '#A0B4C0'],
            'scheduled'   => ['📅 برنامه‌ریزی‌شده', '#F0A500'],
            'in_progress' => ['🔄 در حال انجام', '#1A6B8A'],
            'completed'   => ['✅ انجام‌شده', '#2ECC9A'],
            'skipped'     => ['⏭️ رد شده', '#999999'],
            'cancelled'   => ['❌ لغوشده', '#E05252'],
            'blocked'     => ['🚫 مسدود (پیش‌نیاز ناقص)', '#E05252'],
        ];
    }
    public static function get_pathway_status_labels(): array {
        return [
            'draft'     => 'پیش‌نویس',
            'active'    => 'فعال',
            'paused'    => 'متوقف‌شده',
            'completed' => 'تکمیل‌شده',
            'cancelled' => 'لغوشده',
        ];
    }

    // ═══════════════ قالب‌ها ══════════════════════════════════════
    public static function get_templates(bool $active_only = true): array {
        global $wpdb;
        $where = $active_only ? 'WHERE is_active=1' : '';
        $templates = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dental_pathway_templates {$where} ORDER BY name ASC", ARRAY_A);
        foreach ($templates as &$t) { $t['steps'] = self::get_template_steps((int)$t['id']); }
        return $templates;
    }

    public static function get_template_steps(int $template_id): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_pathway_template_steps WHERE template_id=%d ORDER BY step_order ASC", $template_id
        ), ARRAY_A);
    }

    public static function save_template(?int $id, string $name, string $description, array $steps): int {
        global $wpdb;
        $row = ['name' => sanitize_text_field($name), 'description' => sanitize_text_field($description)];
        if ($id) {
            $wpdb->update($wpdb->prefix.'dental_pathway_templates', $row, ['id'=>$id]);
            $wpdb->delete($wpdb->prefix.'dental_pathway_template_steps', ['template_id'=>$id]);
            $template_id = $id;
        } else {
            $row['is_active'] = 1;
            $row['created_by'] = get_current_user_id();
            $row['created_at'] = current_time('mysql');
            $wpdb->insert($wpdb->prefix.'dental_pathway_templates', $row);
            $template_id = (int)$wpdb->insert_id;
        }
        $order = 1;
        foreach ($steps as $s) {
            $wpdb->insert($wpdb->prefix.'dental_pathway_template_steps', [
                'template_id'    => $template_id,
                'step_order'     => $order,
                'title'          => sanitize_text_field($s['title']),
                'catalog_id'     => !empty($s['catalog_id']) ? (int)$s['catalog_id'] : null,
                'suggested_interval_days' => !empty($s['interval']) ? (int)$s['interval'] : null,
                'depends_on_order' => !empty($s['depends_on']) ? (int)$s['depends_on'] : null,
                'notes'          => sanitize_text_field($s['notes'] ?? ''),
            ]);
            $order++;
        }
        return $template_id;
    }

    // ─── قالب‌های پیش‌فرض — بر اساس منابع عمومی/پایه‌ی دندان‌پزشکی
    // (نه تصمیم بالینی اختصاصی) — همه‌شون بعداً کاملاً قابل ویرایش/حذفن.
    public static function seed_default_templates(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'dental_pathway_templates';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) return;
        if ((int)$wpdb->get_var("SELECT COUNT(*) FROM `{$table}`") > 0) return;

        $defaults = [
            [
                'name' => 'عصب‌کشی → بیلداپ → روکش (ساده)',
                'description' => 'رایج‌ترین مسیر — وقتی ساختار باقی‌مانده‌ی دندان برای نگه‌داشتن روکش کافیه',
                'steps' => [
                    ['title'=>'عصب‌کشی (RCT)'],
                    ['title'=>'ترمیم/بیلداپ', 'interval'=>0, 'depends_on'=>1],
                    ['title'=>'روکش', 'interval'=>14, 'depends_on'=>2],
                ],
            ],
            [
                'name' => 'عصب‌کشی → پست‌وکور → روکش',
                'description' => 'وقتی ساختار باقی‌مانده کم‌تره و پست/کور برای نگه‌داشتن روکش لازمه',
                'steps' => [
                    ['title'=>'عصب‌کشی (RCT)'],
                    ['title'=>'پست و کور', 'interval'=>7, 'depends_on'=>1],
                    ['title'=>'روکش', 'interval'=>14, 'depends_on'=>2],
                ],
            ],
            [
                'name' => 'عصب‌کشی → افزایش طول تاج → پست‌وکور → روکش',
                'description' => 'وقتی بعد از عصب‌کشی، جراحی افزایش طول تاج هم لازم می‌شه (طبق دوره‌ی بهبودی معمول ۶ تا ۱۲ هفته بعد از جراحی)',
                'steps' => [
                    ['title'=>'عصب‌کشی (RCT)'],
                    ['title'=>'جراحی افزایش طول تاج', 'interval'=>7, 'depends_on'=>1],
                    ['title'=>'پست و کور', 'interval'=>56, 'depends_on'=>2, 'notes'=>'حدود ۶-۸ هفته بعد از جراحی، برای بهبودی کامل بافت'],
                    ['title'=>'روکش', 'interval'=>14, 'depends_on'=>3],
                ],
            ],
            [
                'name' => 'کشیدن دندان → ایمپلنت',
                'description' => 'مسیر کامل ایمپلنت — بازه‌های زمانی بر اساس میانگین دوره‌ی بهبودی استخوان/بافت',
                'steps' => [
                    ['title'=>'کشیدن دندان'],
                    ['title'=>'پیوند استخوان (در صورت نیاز)', 'interval'=>0, 'depends_on'=>1, 'notes'=>'اگه استخوان کافی نبود'],
                    ['title'=>'دوره‌ی بهبودی', 'interval'=>60, 'depends_on'=>2, 'notes'=>'حدود ۸-۱۲ هفته'],
                    ['title'=>'کاشت ایمپلنت', 'interval'=>0, 'depends_on'=>3],
                    ['title'=>'دوره‌ی استخوان‌جوشی (Osseointegration)', 'interval'=>90, 'depends_on'=>4, 'notes'=>'حدود ۳-۶ ماه'],
                    ['title'=>'قالب‌گیری', 'interval'=>0, 'depends_on'=>5],
                    ['title'=>'روکش نهایی', 'interval'=>14, 'depends_on'=>6],
                ],
            ],
            [
                'name' => 'ونیر کامپوزیت/سرامیکی',
                'description' => 'مسیر زیبایی — از مشاوره تا نصب نهایی',
                'steps' => [
                    ['title'=>'مشاوره و طراحی لبخند'],
                    ['title'=>'موک‌آپ/وکس‌آپ', 'interval'=>3, 'depends_on'=>1],
                    ['title'=>'تراش (Preparation)', 'interval'=>7, 'depends_on'=>2],
                    ['title'=>'قالب‌گیری/اسکن', 'interval'=>0, 'depends_on'=>3],
                    ['title'=>'ترای‌این (امتحان قبل نصب نهایی)', 'interval'=>10, 'depends_on'=>4],
                    ['title'=>'چسباندن نهایی (Bonding)', 'interval'=>3, 'depends_on'=>5],
                ],
            ],
        ];

        foreach ($defaults as $tpl) {
            $wpdb->insert($table, [
                'name' => $tpl['name'], 'description' => $tpl['description'],
                'is_default' => 1, 'is_active' => 1,
                'created_by' => 0, 'created_at' => current_time('mysql'),
            ]);
            $template_id = (int)$wpdb->insert_id;
            $order = 1;
            foreach ($tpl['steps'] as $s) {
                $wpdb->insert($wpdb->prefix.'dental_pathway_template_steps', [
                    'template_id' => $template_id, 'step_order' => $order,
                    'title' => $s['title'], 'suggested_interval_days' => $s['interval'] ?? null,
                    'depends_on_order' => $s['depends_on'] ?? null, 'notes' => $s['notes'] ?? null,
                ]);
                $order++;
            }
        }
    }

    // ═══════════════ ساخت مسیر واقعی برای یه بیمار ═════════════════
    public static function create_pathway_from_template(int $template_id, int $patient_id, ?int $tooth_number, int $doctor_id): int {
        global $wpdb;
        $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}dental_pathway_templates WHERE id=%d", $template_id), ARRAY_A);
        if (!$template) return 0;

        $wpdb->insert($wpdb->prefix.'dental_treatment_pathways', [
            'patient_id'   => $patient_id,
            'tooth_number' => $tooth_number,
            'template_id'  => $template_id,
            'title'        => $template['name'],
            'status'       => 'active',
            'start_date'   => current_time('Y-m-d'),
            'doctor_id'    => $doctor_id,
            'created_by'   => get_current_user_id(),
            'created_at'   => current_time('mysql'),
        ]);
        $pathway_id = (int)$wpdb->insert_id;

        $steps = self::get_template_steps($template_id);
        $order_to_step_id = [];
        foreach ($steps as $s) {
            $wpdb->insert($wpdb->prefix.'dental_treatment_pathway_steps', [
                'pathway_id'  => $pathway_id,
                'step_order'  => $s['step_order'],
                'title'       => $s['title'],
                'catalog_id'  => $s['catalog_id'],
                'status'      => 'pending',
                'doctor_id'   => $doctor_id,
                'suggested_interval_days' => $s['suggested_interval_days'],
                'notes'       => $s['notes'],
                'created_at'  => current_time('mysql'),
            ]);
            $order_to_step_id[$s['step_order']] = (int)$wpdb->insert_id;
        }
        // ─── دوباره یه پاس بزن تا وابستگی‌ها (بر اساس step_order قالب)
        // به id واقعی مرحله‌ی همین مسیر وصل بشن ─────────────────────
        foreach ($steps as $s) {
            if (!empty($s['depends_on_order']) && isset($order_to_step_id[$s['depends_on_order']])) {
                $wpdb->update($wpdb->prefix.'dental_treatment_pathway_steps',
                    ['depends_on_step_id' => $order_to_step_id[$s['depends_on_order']]],
                    ['id' => $order_to_step_id[$s['step_order']]]
                );
            }
        }

        if (class_exists('Dental_Audit_Log')) {
            Dental_Audit_Log::log('patient_data_changed', "مسیر درمان «{$template['name']}» برای بیمار #{$patient_id} شروع شد",
                ['entity_type'=>'pathway', 'entity_id'=>$pathway_id]);
        }
        return $pathway_id;
    }

    // ═══════════════ خواندن مسیرهای بیمار/دندان ═════════════════════
    public static function get_patient_pathways(int $patient_id): array {
        global $wpdb;
        $pathways = $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, u.display_name as doctor_name FROM {$wpdb->prefix}dental_treatment_pathways p
             LEFT JOIN {$wpdb->users} u ON p.doctor_id=u.ID
             WHERE p.patient_id=%d ORDER BY p.created_at DESC", $patient_id
        ), ARRAY_A);
        foreach ($pathways as &$p) {
            $p['steps'] = self::get_pathway_steps((int)$p['id']);
            $p['remaining'] = count(array_filter($p['steps'], fn($s) => !in_array($s['status'], ['completed','skipped','cancelled'])));
        }
        return $pathways;
    }

    // ─── داشبورد «درمان‌های در انتظار ادامه» — برای هر مسیر فعال،
    // اولین مرحله‌ی ناتمومش (به ترتیب) رو به‌عنوان «مرحله‌ی بعدی» برمی‌گردونه.
    // این یه کوئری سبک روی کل کلینیکه (نه per-patient)، برای همین جدا
    // از get_patient_pathways نگهش داشتیم تا اونجا سنگین نشه.
    public static function get_pending_continuations(int $limit = 50, int $doctor_id = 0): array {
        global $wpdb;
        $where = "WHERE p.status='active'";
        $params = [];
        if ($doctor_id) { $where .= " AND p.doctor_id=%d"; $params[] = $doctor_id; }
        $sql = "SELECT p.*, pt.post_title as patient_name, u.display_name as doctor_name
             FROM {$wpdb->prefix}dental_treatment_pathways p
             LEFT JOIN {$wpdb->posts} pt ON p.patient_id=pt.ID
             LEFT JOIN {$wpdb->users} u ON p.doctor_id=u.ID
             {$where}
             ORDER BY p.created_at DESC";
        $pathways = $params ? $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A) : $wpdb->get_results($sql, ARRAY_A);
        $rows = [];
        foreach ($pathways as $p) {
            $steps = self::get_pathway_steps((int)$p['id']);
            // اولین مرحله‌ای که هنوز تکمیل/رد/لغو نشده — یعنی «مرحله‌ی بعدی»
            $next = null;
            foreach ($steps as $s) {
                if (!in_array($s['status'], ['completed','skipped','cancelled'])) { $next = $s; break; }
            }
            if (!$next) continue; // این مسیر عملاً تمومه، فقط هنوز status عوض نشده
            $rows[] = [
                'pathway_id'   => (int)$p['id'],
                'patient_id'   => (int)$p['patient_id'],
                'patient_name' => $p['patient_name'],
                'tooth_number' => $p['tooth_number'] ? (int)$p['tooth_number'] : null,
                'pathway_title'=> $p['title'],
                'next_step'    => $next['title'],
                'next_status'  => $next['status'],
                'scheduled_date' => $next['scheduled_date'],
                'doctor_name'  => $p['doctor_name'],
                'created_at'   => $p['created_at'],
            ];
        }
        // ─── قدیمی‌ترین مسیرهای فعال اول (چون احتمالاً بیشتر عقب افتادن) ──
        usort($rows, fn($a,$b) => strcmp($a['created_at'], $b['created_at']));
        return array_slice($rows, 0, $limit);
    }

    public static function get_tooth_pathways(int $patient_id, int $tooth_number): array {
        global $wpdb;
        $pathways = $wpdb->get_results($wpdb->prepare(
            "SELECT p.* FROM {$wpdb->prefix}dental_treatment_pathways p
             WHERE p.patient_id=%d AND p.tooth_number=%d AND p.status IN ('active','paused')
             ORDER BY p.created_at DESC", $patient_id, $tooth_number
        ), ARRAY_A);
        foreach ($pathways as &$p) {
            $p['steps'] = self::get_pathway_steps((int)$p['id']);
            $p['remaining'] = count(array_filter($p['steps'], fn($s) => !in_array($s['status'], ['completed','skipped','cancelled'])));
        }
        return $pathways;
    }

    public static function get_pathway_steps(int $pathway_id): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT s.*, u.display_name as doctor_name FROM {$wpdb->prefix}dental_treatment_pathway_steps s
             LEFT JOIN {$wpdb->users} u ON s.doctor_id=u.ID
             WHERE s.pathway_id=%d ORDER BY s.step_order ASC", $pathway_id
        ), ARRAY_A);
    }

    // ─── چک اینکه آیا این مرحله پیش‌نیازش تکمیل شده یا نه ────────────
    public static function is_step_blocked(array $step): bool {
        if (empty($step['depends_on_step_id'])) return false;
        if (!empty($step['dependency_overridden'])) return false;
        global $wpdb;
        $dep_status = $wpdb->get_var($wpdb->prepare(
            "SELECT status FROM {$wpdb->prefix}dental_treatment_pathway_steps WHERE id=%d", $step['depends_on_step_id']
        ));
        return $dep_status !== 'completed';
    }

    // ─── تکمیل یه مرحله همراه با اتصال به کاتالوگ — این دقیقاً همون
    // حلقه‌ی جاافتاده‌ست: اگه catalog_id این مرحله مشخص باشه (یا الان
    // انتخاب بشه)، به‌جای اینکه کاربر جدا از کاتالوگ ثبت کنه (دوباره‌کاری)،
    // همین‌جا خودکار record_treatment() صدا زده می‌شه — که خودش خودکار
    // یه رکورد «در انتظار تأیید مالی» توی دفتر روزانه هم می‌سازه
    // (دقیقاً همون رفتار همیشگی ثبت از کاتالوگ، بدون سیستم موازی) ────
    public static function complete_step_with_catalog(int $step_id, int $catalog_id, int $doctor_id): array {
        global $wpdb;
        $step = $wpdb->get_row($wpdb->prepare(
            "SELECT s.*, p.patient_id, p.tooth_number FROM {$wpdb->prefix}dental_treatment_pathway_steps s
             JOIN {$wpdb->prefix}dental_treatment_pathways p ON s.pathway_id=p.id
             WHERE s.id=%d", $step_id
        ), ARRAY_A);
        if (!$step) return ['success'=>false, 'message'=>'مرحله یافت نشد.'];

        if (self::is_step_blocked($step)) {
            return ['success'=>false, 'message'=>'این مرحله پیش‌نیازش هنوز تکمیل نشده.', 'blocked'=>true];
        }
        if (!class_exists('Dental_Service_Catalog')) {
            return ['success'=>false, 'message'=>'سیستم کاتالوگ در دسترس نیست.'];
        }

        $result = Dental_Service_Catalog::record_treatment(
            (int)$step['patient_id'], $step['tooth_number'] ? (int)$step['tooth_number'] : null, $catalog_id, $doctor_id
        );
        if (empty($result['id'])) {
            return ['success'=>false, 'message'=>'این خدمت (یا زیرشاخه‌اش) در کاتالوگ معتبر نیست.'];
        }

        // ─── مرحله رو هم به همین catalog_id (برای دفعه‌ی بعد) هم به
        // treatment_id واقعی وصل کن، بعد تکمیل‌شده علامت بزن ──────────
        $wpdb->update($wpdb->prefix.'dental_treatment_pathway_steps', [
            'catalog_id'     => $catalog_id,
            'treatment_id'   => (int)$result['id'],
            'status'         => 'completed',
            'completed_date' => current_time('Y-m-d'),
            'doctor_id'      => $doctor_id,
        ], ['id' => $step_id]);

        $all_steps = self::get_pathway_steps((int)$step['pathway_id']);
        if (!empty($all_steps) && !array_filter($all_steps, fn($s) => !in_array($s['status'], ['completed','skipped','cancelled']))) {
            $wpdb->update($wpdb->prefix.'dental_treatment_pathways', ['status'=>'completed','end_date'=>current_time('Y-m-d')], ['id'=>$step['pathway_id']]);
        }

        if (class_exists('Dental_Audit_Log')) {
            Dental_Audit_Log::log('patient_data_changed', "مرحله‌ی «{$step['title']}» تکمیل و خودکار توی دفتر روزانه ثبت شد",
                ['entity_type'=>'pathway_step', 'entity_id'=>$step_id]);
        }
        return ['success'=>true, 'requires_consent'=>$result['requires_consent'] ?? false];
    }

    // ═══════════════ تغییر وضعیت مرحله ══════════════════════════════
    public static function update_step_status(int $step_id, string $status, array $extra = []): array {
        global $wpdb;
        $step = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}dental_treatment_pathway_steps WHERE id=%d", $step_id), ARRAY_A);
        if (!$step) return ['success'=>false, 'message'=>'مرحله یافت نشد.'];

        $is_override = false;
        if ($status === 'completed' && self::is_step_blocked($step)) {
            if (empty($extra['override'])) {
                return ['success'=>false, 'message'=>'این مرحله پیش‌نیازش هنوز تکمیل نشده. برای ادامه، Override لازمه.', 'blocked'=>true];
            }
            $is_override = true;
        }

        $row = ['status' => $status];
        if ($status === 'completed') { $row['completed_date'] = current_time('Y-m-d'); }
        if ($is_override) { $row['dependency_overridden'] = 1; }
        // ─── بازگردانی به pending — طبق درخواست کاربر، وضعیت‌های
        // «رد شده/لغوشده/تکمیل‌شده» باید کاملاً قابل‌اصلاح باشن، نه
        // یه‌طرفه. تاریخ تکمیل و override قبلی هم پاک می‌شه تا واقعاً
        // انگار از اول برنامه‌ریزی‌شده.
        if ($status === 'pending') {
            $row['completed_date'] = null;
            $row['dependency_overridden'] = 0;
        }
        if (!empty($extra['scheduled_date'])) { $row['scheduled_date'] = $extra['scheduled_date']; }
        if (!empty($extra['treatment_id'])) { $row['treatment_id'] = (int)$extra['treatment_id']; }

        $wpdb->update($wpdb->prefix.'dental_treatment_pathway_steps', $row, ['id'=>$step_id]);

        if (class_exists('Dental_Audit_Log')) {
            $label = self::get_status_labels()[$status][0] ?? $status;
            $msg = "مرحله‌ی «{$step['title']}» تغییر وضعیت به «{$label}»" . ($is_override ? ' (Override پیش‌نیاز)' : '');
            Dental_Audit_Log::log('patient_data_changed', $msg, ['entity_type'=>'pathway_step', 'entity_id'=>$step_id]);
        }

        // ─── وقتی همه‌ی مراحل تکمیل/رد/لغو شدن، خودِ مسیر رو هم completed کن ──
        $all_steps = self::get_pathway_steps((int)$step['pathway_id']);
        if (!empty($all_steps) && !array_filter($all_steps, fn($s) => !in_array($s['status'], ['completed','skipped','cancelled']))) {
            $wpdb->update($wpdb->prefix.'dental_treatment_pathways', ['status'=>'completed','end_date'=>current_time('Y-m-d')], ['id'=>$step['pathway_id']]);
        } elseif ($status === 'pending') {
            // ─── برعکسش هم لازمه: اگه مسیر قبلاً خودکار «تکمیل‌شده»
            // شده بود و الان یه مرحله برگشت به «در انتظار»، مسیر هم
            // باید دوباره «فعال» بشه — وگرنه توی UI اصلاً دکمه‌ی
            // ویرایش مراحلش نشون داده نمی‌شه.
            $pathway = $wpdb->get_row($wpdb->prepare("SELECT status FROM {$wpdb->prefix}dental_treatment_pathways WHERE id=%d", $step['pathway_id']), ARRAY_A);
            if ($pathway && $pathway['status'] === 'completed') {
                $wpdb->update($wpdb->prefix.'dental_treatment_pathways', ['status'=>'active','end_date'=>null], ['id'=>$step['pathway_id']]);
            }
        }

        return ['success'=>true];
    }
}
