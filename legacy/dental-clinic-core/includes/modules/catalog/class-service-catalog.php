<?php
defined('ABSPATH') || exit;

/**
 * مدیریت کاتالوگ درختی خدمات دندانپزشکی + قیمت‌گذاری هر خدمت
 */
class Dental_Service_Catalog {

    // ─── افزودن یک گره جدید به درخت ──────────────────────────────
    public static function add_node(?int $parent_id, string $name, bool $is_leaf, string $tooth_filter = 'all', int $sort = 0): int {
        global $wpdb;
        $wpdb->insert($wpdb->prefix.'dental_service_catalog', [
            'parent_id'    => $parent_id,
            'name'         => $name,
            'is_leaf'      => $is_leaf ? 1 : 0,
            'tooth_filter' => $tooth_filter,
            'sort_order'   => $sort,
            'is_active'    => 1,
        ], ['%d','%s','%d','%s','%d','%d']);
        return (int)$wpdb->insert_id;
    }

    // ─── گرفتن فرزندهای مستقیم یک گره (برای نمایش پله‌به‌پله درخت) ──
    public static function get_children(?int $parent_id): array {
        global $wpdb;
        if ($parent_id === null) {
            $rows = $wpdb->get_results(
                "SELECT c.*, p.price FROM {$wpdb->prefix}dental_service_catalog c
                 LEFT JOIN {$wpdb->prefix}dental_service_pricing p ON c.id=p.catalog_id
                 WHERE c.parent_id IS NULL AND c.is_active=1 ORDER BY c.sort_order ASC, c.id ASC", ARRAY_A
            );
        } else {
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT c.*, p.price FROM {$wpdb->prefix}dental_service_catalog c
                 LEFT JOIN {$wpdb->prefix}dental_service_pricing p ON c.id=p.catalog_id
                 WHERE c.parent_id=%d AND c.is_active=1 ORDER BY c.sort_order ASC, c.id ASC", $parent_id
            ), ARRAY_A);
        }
        return $rows;
    }

    // ─── مسیر کامل یک گره تا ریشه (برای نمایش breadcrumb) ─────────
    public static function get_path(int $node_id): array {
        global $wpdb;
        $path = [];
        $current = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_service_catalog WHERE id=%d", $node_id
        ), ARRAY_A);
        while ($current) {
            array_unshift($path, $current);
            if (!$current['parent_id']) break;
            $current = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dental_service_catalog WHERE id=%d", $current['parent_id']
            ), ARRAY_A);
        }
        return $path;
    }

    // ─── کل درخت کاتالوگ، یکجا و تودرتو — برای کش سمت مرورگر، دقیقاً
    // شبیه الگوی سلامت‌نگار (یه بار لود، بقیه‌ش کاملاً کلاینت‌ساید و
    // آنیه) — به‌جای اینکه هر باز‌کردن زیرشاخه یه درخواست HTTP جدا
    // بزنه (که چون admin-ajax.php هر بار کل وردپرس رو بوت می‌کنه، کنده) ──
    public static function get_full_tree(): array {
        global $wpdb;
        $rows = $wpdb->get_results(
            "SELECT c.id, c.parent_id, c.name, c.is_leaf, c.tooth_filter, p.price
             FROM {$wpdb->prefix}dental_service_catalog c
             LEFT JOIN {$wpdb->prefix}dental_service_pricing p ON c.id=p.catalog_id
             WHERE c.is_active=1 ORDER BY c.sort_order ASC, c.id ASC", ARRAY_A
        );
        $by_id = [];
        foreach ($rows as $r) {
            $by_id[$r['id']] = [
                'id' => (int)$r['id'], 'name' => $r['name'],
                'is_leaf' => (int)$r['is_leaf'], 'tooth_filter' => $r['tooth_filter'],
                'price' => (int)($r['price'] ?? 0), 'parent_id' => $r['parent_id'] ? (int)$r['parent_id'] : null,
                'children' => [],
            ];
        }
        $tree = [];
        foreach ($by_id as $id => &$node) {
            if ($node['parent_id'] && isset($by_id[$node['parent_id']])) {
                $by_id[$node['parent_id']]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }
        unset($node);
        return $tree;
    }


    public static function get_node(int $id): ?array {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT c.*, p.price, p.patient_share FROM {$wpdb->prefix}dental_service_catalog c
             LEFT JOIN {$wpdb->prefix}dental_service_pricing p ON c.id=p.catalog_id
             WHERE c.id=%d", $id
        ), ARRAY_A);
        return $row ?: null;
    }

    // ─── ذخیره/به‌روزرسانی قیمت یک برگ ─────────────────────────────
    public static function set_price(int $catalog_id, int $price, ?int $patient_share = null): void {
        global $wpdb;
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}dental_service_pricing WHERE catalog_id=%d", $catalog_id
        ));
        $data = ['price' => $price, 'patient_share' => $patient_share, 'updated_at' => current_time('mysql')];
        if ($exists) {
            $wpdb->update($wpdb->prefix.'dental_service_pricing', $data, ['catalog_id'=>$catalog_id]);
        } else {
            $data['catalog_id'] = $catalog_id;
            $wpdb->insert($wpdb->prefix.'dental_service_pricing', $data);
        }
    }

    // ─── جستجوی متنی در کل کاتالوگ (فقط برگ‌ها) — برای جستجوی سریع پزشک ──
    public static function search_leaves(string $q, int $limit = 20): array {
        global $wpdb;
        $like = '%' . $wpdb->esc_like($q) . '%';
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, p.price, p.patient_share FROM {$wpdb->prefix}dental_service_catalog c
             LEFT JOIN {$wpdb->prefix}dental_service_pricing p ON c.id=p.catalog_id
             WHERE c.is_leaf=1 AND c.is_active=1 AND c.name LIKE %s
             ORDER BY c.name ASC LIMIT %d", $like, $limit
        ), ARRAY_A);
        // نکته: بدون مسیر کامل، «کامپوزیت یک سطحی» می‌تونه مال اطفال/بزرگسال/
        // قدامی/خلفی هرکدوم باشه — پس مسیر کامل رو هم اضافه می‌کنیم.
        foreach ($rows as &$r) {
            $path = self::get_path((int)$r['id']);
            $r['full_path'] = implode(' > ', array_column($path, 'name'));
        }
        return $rows;
    }

    // ─── فهرست تخت همه برگ‌ها (برای صفحه قیمت‌گذاری کامل) ───────────
    public static function get_all_leaves_flat(): array {
        global $wpdb;
        $rows = $wpdb->get_results(
            "SELECT c.*, p.price, p.patient_share FROM {$wpdb->prefix}dental_service_catalog c
             LEFT JOIN {$wpdb->prefix}dental_service_pricing p ON c.id=p.catalog_id
             WHERE c.is_leaf=1 AND c.is_active=1 ORDER BY c.id ASC", ARRAY_A
        );
        foreach ($rows as &$r) {
            $path = self::get_path((int)$r['id']);
            $r['full_path'] = implode(' > ', array_column($path, 'name'));
        }
        return $rows;
    }

    // ─── وارد کردن کامل کاتالوگ از فایل seed (فقط یک‌بار) ─────────
    public static function import_seed(): int {
        global $wpdb;
        // اگر قبلاً چیزی وارد شده، دوباره وارد نکن (جلوگیری از تکراری‌شدن)
        $existing = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dental_service_catalog");
        if ($existing > 0) return 0;

        $data = Dental_Catalog_Seed_Data::get();
        $count = 0;
        self::import_recursive($data, null, $count);
        update_option('dental_catalog_imported', 1);
        delete_transient('dental_catalog_full_tree_cache');
        return $count;
    }

    // ─── ورود فقط یک دسته‌ی سطح‌بالای خاص (مثلاً «زیبایی») — برای
    // کلینیک‌هایی که کاتالوگ رو قبلاً وارد کردن و import_seed() دیگه
    // کاری نمی‌کنه، ولی یه دسته‌ی جدید بعداً به seed اضافه شده ────────
    public static function import_category_by_name(string $category_name): array {
        global $wpdb;
        // اگه از قبل با همین اسم دسته‌ی سطح‌بالا وجود داره، دوباره وارد نکن
        $already = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}dental_service_catalog WHERE parent_id IS NULL AND name=%s", $category_name
        ));
        if ($already) return ['success'=>false, 'message'=>'این دسته از قبل وجود داره.'];

        $data = Dental_Catalog_Seed_Data::get();
        $branch = null;
        foreach ($data as $node) {
            if ($node['n'] === $category_name) { $branch = $node; break; }
        }
        if (!$branch) return ['success'=>false, 'message'=>'این دسته توی داده‌ی اولیه پیدا نشد.'];

        $count = 0;
        self::import_recursive([$branch], null, $count);
        delete_transient('dental_catalog_full_tree_cache');
        return ['success'=>true, 'count'=>$count];
    }

    // ─── کلیدواژه‌هایی که نشون میدن این خدمت نیاز به رضایت‌نامه داره ──
    // (جراحی، ایمپلنت، عصب‌کشی، ارتودنسی، کشیدن و...) — قابل تغییر دستی
    // بعداً از صفحه قیمت‌گذاری برای هر خدمت خاص.
    private static function detect_requires_consent(string $name): bool {
        $keywords = ['ايمپلنت','ایمپلنت','جراح','كشيدن','کشیدن','ارتودنس','اپيكواكتومي','اپیکواکتومی',
            'اكسيژن','اکسیژن','بيوپسي','بیوپسی','پيوند','پیوند','قطع نوك ريشه','قطع نوک ریشه','رزكسيون'];
        foreach ($keywords as $k) {
            if (mb_stripos($name, $k) !== false) return true;
        }
        return false;
    }

    // ─── تشخیص خودکار فیلتر فک/نیم‌فک از روی نام خدمت ────────────
    private static function detect_jaw_filter(string $name): ?string {
        if (mb_stripos($name,'1/2 فک')!==false || mb_stripos($name,'۱/۲ فک')!==false || mb_stripos($name,'نیم‌فک')!==false || mb_stripos($name,'نیم فک')!==false) {
            // نمی‌دونیم دقیقاً کدوم نیم‌فکه — پیش‌فرض null می‌ذاریم تا مدیر
            // دستی از صفحه قیمت‌گذاری مشخص کنه کدوم نیم‌فک دقیقاً مدنظره
            return null;
        }
        if (mb_stripos($name,'تمام دهان')!==false || mb_stripos($name,'کامل دهان')!==false || mb_stripos($name,'کل دهان')!==false) {
            return 'whole_mouth'; // نیازی به انتخاب دندون خاص نیست — فقط ثبت عمومی
        }
        return null;
    }

    private static function import_recursive(array $nodes, ?int $parent_id, int &$count, int &$sort = 0): void {
        foreach ($nodes as $node) {
            $sort++;
            $is_leaf = !empty($node['leaf']) || empty($node['c']);
            $requires_consent = $is_leaf && self::detect_requires_consent($node['n']);
            $filter = $node['f'] ?? (self::detect_jaw_filter($node['n']) ?? 'all');
            $id = self::add_node($parent_id, $node['n'], $is_leaf, $filter, $sort);
            if ($requires_consent) {
                global $wpdb;
                $wpdb->update($wpdb->prefix.'dental_service_catalog', ['requires_consent'=>1], ['id'=>$id]);
            }
            $count++;
            if (!empty($node['c'])) {
                $child_sort = 0;
                self::import_recursive($node['c'], $id, $count, $child_sort);
            }
        }
    }

    // ─── حذف کامل کاتالوگ (برای وارد کردن دوباره در صورت نیاز) ─────
    public static function reset_catalog(): void {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}dental_service_catalog");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}dental_service_pricing");
        delete_option('dental_catalog_imported');
    }
    // ─── ثبت یک درمان جدید از کاتالوگ برای بیمار ────────────────────
    public static function record_treatment(int $patient_id, ?int $tooth_number, int $catalog_id, int $doctor_id): array {
        global $wpdb;
        $node = self::get_node($catalog_id);
        if (!$node || !$node['is_leaf']) return ['id'=>0, 'requires_consent'=>false];

        $path = self::get_path($catalog_id);
        $full_name = implode(' > ', array_column($path, 'name'));
        $now = current_time('mysql');

        // نکته مهم: برخلاف طرح درمان قدیمی (که اول برنامه‌ریزی می‌شه، بعد
        // جدا تیک می‌خوره)، ثبت از کاتالوگ یعنی همین الان انجام شده — پس
        // is_done از همون لحظه ثبت ۱ می‌شه، بدون نیاز به یک اکشن جدا.
        $wpdb->insert($wpdb->prefix.'dental_catalog_treatments', [
            'patient_id'    => $patient_id,
            'tooth_number'  => $tooth_number,
            'catalog_id'    => $catalog_id,
            'service_name'  => $full_name,
            'price'         => (int)($node['price'] ?? 0),
            'doctor_id'     => $doctor_id,
            'recorded_date' => current_time('Y-m-d'),
            'is_done'       => 1,
            'done_at'       => $now,
            'done_by'       => $doctor_id,
            'created_at'    => $now,
        ], ['%d','%d','%d','%s','%d','%d','%s','%d','%s','%d','%s']);
        $id = (int)$wpdb->insert_id;

        // ─── محاسبه‌ی خودکار سهم بیمار/بیمه — اگه بیمار بیمه‌ی فعال
        // داشته باشه، طبق تعرفه‌ی ثبت‌شده محاسبه می‌شه؛ اگه بیمه نداشت
        // یا تعرفه‌ای ثبت نشده بود، کل مبلغ رو خودِ بیمار می‌ده (طبق قبل) ──
        $insurance_share = null; $patient_share_calc = null; $required_docs = [];
        if (class_exists('Dental_Insurance_Manager')) {
            $ins = Dental_Insurance_Manager::get_patient_insurance($patient_id);
            if (!empty($ins['insurance_id'])) {
                $calc = Dental_Insurance_Manager::calculate($ins['insurance_id'], $catalog_id, (float)($node['price'] ?? 0));
                if ($calc['has_tariff']) {
                    $insurance_share = $calc['insurance_share'];
                    $patient_share_calc = $calc['patient_share'];
                    $required_docs = $calc['requires_docs'];
                }
            }
        }
        // اگه تعرفه‌ی دستی مدارک نداشت، بر اساس اسم خدمت حدس بزن
        if (empty($required_docs) && class_exists('Dental_Insurance_Manager')) {
            $required_docs = Dental_Insurance_Manager::detect_required_docs($full_name);
        }
        if ($insurance_share !== null) {
            $wpdb->update($wpdb->prefix.'dental_catalog_treatments', [
                'insurance_id'    => $ins['insurance_id'],
                'insurance_share' => $insurance_share,
                'patient_share'   => $patient_share_calc,
                'insurance_docs_status' => !empty($required_docs) ? 'pending' : null,
            ], ['id' => $id]);
        }

        // ثبت خودکار در «کارکرد پزشکان»
        if ($id && class_exists('Dental_Doctor_Activity')) {
            Dental_Doctor_Activity::log($doctor_id, $patient_id, 'treatment_done', $full_name . ($tooth_number ? ' — '.self::describe_tooth_number($tooth_number) : ''));
        }

        // ─── ساخت خودکار رکورد مالی در انتظار تأیید ────────────────
        // نکته: اگه بیمه محاسبه شده باشه، مبلغی که توی دفتر روزانه از
        // بیمار خواسته می‌شه فقط سهمِ خودِ بیماره (بعد از کسر سهم بیمه)،
        // نه کل مبلغ خدمت — دقیقاً همون چیزی که واقعاً باید بگیره.
        if ($id && class_exists('Dental_Ledger_Manager')) {
            // اولویت: سهم محاسبه‌شده‌ی بیمه > سهم دستی قدیمی سطح‌خدمت > کل قیمت
            $node_patient_share = $node['patient_share'] ?? null;
            $amount = $patient_share_calc !== null ? (float)$patient_share_calc
                    : ($node_patient_share !== null ? (float)$node_patient_share : (float)($node['price'] ?? 0));
            $ledger_notes = 'ثبت خودکار از کاتالوگ خدمات — در انتظار تأیید مالی';
            if ($insurance_share !== null) {
                $ledger_notes .= " (سهم بیمه: " . number_format($insurance_share) . " تومان کسر شد)";
            }
            Dental_Ledger_Manager::create([
                'patient_id'      => $patient_id,
                'doctor_id'       => $doctor_id,
                'treatment_title' => $full_name . ($tooth_number ? ' — '.self::describe_tooth_number($tooth_number) : ''),
                'amount_charged'  => $amount,
                'amount_received' => 0,
                'notes'           => $ledger_notes,
                'status'          => 'pending',
                'source'          => 'catalog',
                'catalog_treatment_id' => $id,
            ]);
        }

        return ['id' => $id, 'requires_consent' => !empty($node['requires_consent'])];
    }

    public static function get_patient_treatments(int $patient_id): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT t.*, u.display_name as doctor_name, d.display_name as done_by_name
             FROM {$wpdb->prefix}dental_catalog_treatments t
             LEFT JOIN {$wpdb->users} u ON t.doctor_id=u.ID
             LEFT JOIN {$wpdb->users} d ON t.done_by=d.ID
             WHERE t.patient_id=%d ORDER BY t.created_at DESC", $patient_id
        ), ARRAY_A);
    }

    public static function toggle_treatment_done(int $id, bool $done, int $by_user): void {
        global $wpdb;
        $wpdb->update($wpdb->prefix.'dental_catalog_treatments', [
            'is_done' => $done ? 1 : 0,
            'done_at' => $done ? current_time('mysql') : null,
            'done_by' => $done ? $by_user : null,
        ], ['id' => $id]);

        // ─── وقتی «انجام‌شده» تیک می‌خوره، خودکار یه رکورد مالی در
        // انتظار تأیید ساخته می‌شه — واحد مالی باید تأییدش کنه، خودکار
        // به‌عنوان درآمد قطعی حساب نمی‌شه.
        if ($done && class_exists('Dental_Ledger_Manager')) {
            $treatment = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dental_catalog_treatments WHERE id=%d", $id
            ), ARRAY_A);
            if ($treatment) {
                $node = self::get_node((int)$treatment['catalog_id']);
                $patient_share = $node['patient_share'] ?? null;
                $amount = $patient_share !== null ? (float)$patient_share : (float)$treatment['price'];

                Dental_Ledger_Manager::create([
                    'patient_id'      => $treatment['patient_id'],
                    'doctor_id'       => $treatment['doctor_id'],
                    'treatment_title' => $treatment['service_name'] . ($treatment['tooth_number'] ? ' — دندان '.$treatment['tooth_number'] : ''),
                    'amount_charged'  => $amount,
                    'amount_received' => 0,
                    'notes'           => 'ثبت خودکار از کاتالوگ خدمات — در انتظار تأیید مالی',
                    'status'          => 'pending',
                    'source'          => 'catalog',
                    'catalog_treatment_id' => $id,
                ]);
            }
        }
    }

    // ─── گزارش تفکیکی درآمد/عملکرد در یک بازه (فیلتر پزشک اختیاری) ──
    public static function get_performance_report(string $from, string $to, int $doctor_id = 0): array {
        global $wpdb;
        $where = "WHERE t.recorded_date BETWEEN %s AND %s";
        $params = [$from, $to];
        if ($doctor_id) { $where .= " AND t.doctor_id=%d"; $params[] = $doctor_id; }

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT t.*, p.post_title as patient_name, u.display_name as doctor_name,
                l.status as ledger_status, l.amount_charged as confirmed_amount,
                l.discount_amount as ledger_discount
             FROM {$wpdb->prefix}dental_catalog_treatments t
             LEFT JOIN {$wpdb->posts} p ON t.patient_id=p.ID
             LEFT JOIN {$wpdb->users} u ON t.doctor_id=u.ID
             LEFT JOIN {$wpdb->prefix}dental_daily_ledger l ON l.catalog_treatment_id=t.id
             $where ORDER BY t.recorded_date DESC, t.id DESC", $params
        ), ARRAY_A);
        return $rows;
    }

    // ─── خلاصه به تفکیک پزشک (برای کارت‌های بالای گزارش) ────────────
    public static function get_doctor_summary(string $from, string $to, int $doctor_id = 0): array {
        global $wpdb;
        $where = "WHERE t.recorded_date BETWEEN %s AND %s";
        $params = [$from, $to];
        if ($doctor_id) { $where .= " AND t.doctor_id=%d"; $params[] = $doctor_id; }

        return $wpdb->get_results($wpdb->prepare(
            "SELECT t.doctor_id, u.display_name as doctor_name,
                COUNT(*) as total_count,
                SUM(t.is_done) as done_count,
                SUM(CASE WHEN l.status='confirmed' THEN l.amount_charged ELSE 0 END) as confirmed_revenue,
                SUM(CASE WHEN l.status='pending'   THEN l.amount_charged ELSE 0 END) as pending_revenue
             FROM {$wpdb->prefix}dental_catalog_treatments t
             LEFT JOIN {$wpdb->users} u ON t.doctor_id=u.ID
             LEFT JOIN {$wpdb->prefix}dental_daily_ledger l ON l.catalog_treatment_id=t.id
             $where GROUP BY t.doctor_id ORDER BY confirmed_revenue DESC", $params
        ), ARRAY_A);
    }

    // ─── تنظیم دستی نیاز به رضایت‌نامه برای یک خدمت خاص ─────────────
    public static function set_requires_consent(int $catalog_id, bool $requires): void {
        global $wpdb;
        $wpdb->update($wpdb->prefix.'dental_service_catalog', ['requires_consent'=>$requires?1:0], ['id'=>$catalog_id]);
    }

    // ─── حذف یک درمان ثبت‌شده (فقط رکورد کاتالوگی، نه رکورد مالی تأییدشده) ──
    public static function delete_treatment(int $id): bool {
        global $wpdb;
        // اگه رکورد مالی تأییدشده باشه، اجازه حذف نده (باید از حسابداری برگشت بخوره)
        $confirmed = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dental_daily_ledger WHERE catalog_treatment_id=%d AND status='confirmed'", $id
        ));
        if ($confirmed > 0) return false;

        // پاک‌کردن رکورد مالی pending مرتبط (اگه بود) + خودِ درمان
        $wpdb->delete($wpdb->prefix.'dental_daily_ledger', ['catalog_treatment_id'=>$id]);
        $wpdb->delete($wpdb->prefix.'dental_catalog_treatments', ['id'=>$id]);
        return true;
    }

    // ─── ویرایش دندان یک درمان ثبت‌نشده (قبل از تیک انجام‌شده) ──────
    public static function update_treatment_tooth(int $id, ?int $tooth_number): bool {
        global $wpdb;
        return (bool)$wpdb->update($wpdb->prefix.'dental_catalog_treatments', ['tooth_number'=>$tooth_number], ['id'=>$id]);
    }

    // ─── تاریخچه کامل بیمار — تجمیع هر دو سیستم (چارت قدیمی + کاتالوگ) ──
    // گروه‌بندی‌شده بر اساس دندان، برای «خلاصه پرونده»
    public static function get_patient_full_history(int $patient_id): array {
        global $wpdb;
        $groups = []; // ['whole_mouth'=>[...], 'teeth'=>[tooth_number => [...]]]
        $groups['whole_mouth'] = [];
        $groups['teeth'] = [];

        // ۱. از سیستم قدیمی چارت (wp_dental_tooth_conditions + _chart_done_map)
        $conditions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_tooth_conditions WHERE patient_id=%d", $patient_id
        ), ARRAY_A);
        $done_map = get_post_meta($patient_id, '_chart_done_map', true) ?: [];
        $tx_labels = class_exists('Dental_Doctor_Activity') ? null : null; // برچسب‌ها پایین همین فایل موجوده

        foreach ($conditions as $row) {
            $tx = json_decode($row['tooth_surface'] ?: '[]', true) ?: [];
            $key = $row['tooth_number'] . '_' . $row['tooth_type'];
            // ─── تشخیص نیم‌فک/کل‌دهان — این‌ها دندان واقعی نیستن، باید
            // توی «whole_mouth» با لیبل درست نشون داده بشن، نه زیر یه
            // شماره‌ی دندان ساختگی مثل «دندان ۱» ────────────────────
            $is_special = in_array($row['tooth_type'], ['halfarch','fullarch']);
            $half_names_map = ['1'=>'نیم‌فک بالا راست','2'=>'نیم‌فک بالا چپ','3'=>'نیم‌فک پایین چپ','4'=>'نیم‌فک پایین راست'];
            $halfarch_svc_labels = [
                'bwx'=>'عکس بایت‌وینگ (BWX)', 'scaling_half'=>'جرم‌گیری نیم‌فک', 'root_planing'=>'روت پلنینگ / کورتاژ',
                'flap_surgery'=>'فلاپ جراحی', 'consult_perio_h'=>'مشاوره پریو نیم‌فک',
            ];
            $fullarch_svc_labels = [
                'panoramic'=>'عکس پانورامیک', 'scaling_full'=>'جرم‌گیری کل دهان', 'brushing'=>'بروساژ',
                'fissure_seal'=>'فیشورسیلانت', 'fluoride'=>'فلوراید', 'bleaching'=>'بلیچینگ',
                'ortho'=>'ارتودنسی', 'consult_ortho'=>'مشاوره ارتودنسی', 'study_model'=>'مدل مطالعه',
            ];

            foreach ($tx as $code) {
                $dk = $key . '_' . $code;
                $info = $done_map[$dk] ?? [];
                if (empty($info['done'])) continue; // فقط کارهای واقعاً انجام‌شده رو نشون بده
                $doctor_name = $info['doctor_name'] ?? (get_userdata($info['doctor_id'] ?? 0) ? get_userdata($info['doctor_id'])->display_name : '—');

                if ($is_special) {
                    if ($row['tooth_type'] === 'halfarch') {
                        $label = ($halfarch_svc_labels[$code] ?? $code) . ' — ' . ($half_names_map[(string)$row['tooth_number']] ?? 'نیم‌فک');
                    } else {
                        $label = $fullarch_svc_labels[$code] ?? $code;
                    }
                    $groups['whole_mouth'][] = [
                        'text'   => $label,
                        'date'   => $info['done_at'] ?? $row['recorded_date'] ?? '',
                        'doctor' => $doctor_name,
                        'source' => 'chart_' . $row['tooth_type'],
                    ];
                    continue;
                }

                $entry = [
                    'text'   => (class_exists('Dental_Doctor_Activity') ? Dental_Doctor_Activity::describe_item_key($dk) : $code),
                    'date'   => $info['done_at'] ?? $row['recorded_date'] ?? '',
                    'doctor' => $doctor_name,
                    'source' => 'chart',
                ];
                $groups['teeth'][(int)$row['tooth_number']][] = $entry;
            }
        }

        // ۲. از کاتالوگ جدید (wp_dental_catalog_treatments) — فقط انجام‌شده‌ها
        $catalog_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT t.*, u.display_name as doctor_name FROM {$wpdb->prefix}dental_catalog_treatments t
             LEFT JOIN {$wpdb->users} u ON t.doctor_id=u.ID
             WHERE t.patient_id=%d AND t.is_done=1", $patient_id
        ), ARRAY_A);
        foreach ($catalog_rows as $row) {
            $entry = [
                'text'   => $row['service_name'],
                'date'   => $row['done_at'] ?: $row['created_at'],
                'doctor' => $row['doctor_name'],
                'source' => 'catalog',
            ];            if ($row['tooth_number']) {
                $groups['teeth'][(int)$row['tooth_number']][] = $entry;
            } else {
                $groups['whole_mouth'][] = $entry;
            }
        }

        // ۳. مراحل تکمیل‌شده‌ی مسیر درمان — فقط وقتی این قابلیت فعاله
        // (طبق طراحی امن، اگه خاموش باشه، این بخش هیچ اثری نداره) ────
        if (class_exists('Dental_Pathway_Manager') && Dental_Pathway_Manager::is_enabled()) {
            $pathways = Dental_Pathway_Manager::get_patient_pathways($patient_id);
            foreach ($pathways as $pw) {
                foreach ($pw['steps'] as $step) {
                    if ($step['status'] !== 'completed') continue; // فقط انجام‌شده‌ها توی خلاصه/تاریخچه
                    $entry = [
                        'text'   => '🛤️ ' . $step['title'] . ' (از مسیر «' . $pw['title'] . '»)',
                        'date'   => $step['completed_date'] ?: $step['created_at'],
                        'doctor' => $step['doctor_name'],
                        'source' => 'pathway',
                    ];
                    if ($pw['tooth_number']) {
                        $groups['teeth'][(int)$pw['tooth_number']][] = $entry;
                    } else {
                        $groups['whole_mouth'][] = $entry;
                    }
                }
            }
        }

        // مرتب‌سازی هر گروه بر اساس تاریخ (جدیدترین اول)
        usort($groups['whole_mouth'], fn($a,$b) => strcmp($b['date'], $a['date']));
        foreach ($groups['teeth'] as &$list) {
            usort($list, fn($a,$b) => strcmp($b['date'], $a['date']));
        }
        ksort($groups['teeth']);

        return $groups;
    }

    // ─── رمزگشایی شماره دندان کدگذاری‌شده (quadrant*10+n یا کد فک/نیم‌فک) ──
    public static function describe_tooth_number(?int $tooth_number): string {
        if (!$tooth_number) return '—';
        // کدهای ویژه فک کامل/نیم‌فک (خارج از بازه دندان‌های واقعی)
        $jaw_codes = [91=>'کل فک بالا', 92=>'کل فک پایین'];
        if (isset($jaw_codes[$tooth_number])) return $jaw_codes[$tooth_number];
        if (in_array($tooth_number, [1,2,3,4])) {
            $half_labels = [1=>'نیم‌فک بالا چپ',2=>'نیم‌فک بالا راست',3=>'نیم‌فک پایین چپ',4=>'نیم‌فک پایین راست'];
            return $half_labels[$tooth_number];
        }
        // دندان‌های شیری — کوادرانت ۵ تا ۸ با حرف A-E (استاندارد FDI)
        $q = (int)($tooth_number/10); $n = $tooth_number % 10;
        $primary_q_names = [5=>'بالا راست',6=>'بالا چپ',7=>'پایین چپ',8=>'پایین راست'];
        if (isset($primary_q_names[$q]) && $n>=1 && $n<=5) {
            $letter = chr(64 + $n); // 1=A, 2=B, ...
            return 'دندان شیری '.$letter.' فک '.$primary_q_names[$q];
        }
        // دندان‌های دائمی — کوادرانت ۱ تا ۴
        $q_names = [1=>'بالا راست',2=>'بالا چپ',3=>'پایین چپ',4=>'پایین راست'];
        if (isset($q_names[$q]) && $n>=1 && $n<=8) {
            return 'دندان '.$n.' فک '.$q_names[$q];
        }
        return 'دندان '.$tooth_number; // سازگاری با رکوردهای قدیمی‌تر بدون کدگذاری فک
    }

    // ─── محدوده دندان مجاز بر اساس فیلتر برگ (برای محدودکردن چارت) ──
    // نکته: بعضی خدمات (جرم‌گیری نیم‌فک، فلورایدتراپی کل دهان و...) به
    // یه دندان خاص مربوط نیستن، بلکه به کل فک یا نیم‌فک — این‌ها دیگه
    // چارت دندان نشون داده نمی‌شه، فقط یه دکمه تأیید ساده.
    public static function is_jaw_level_filter(string $filter): bool {
        return in_array($filter, ['upper_jaw','lower_jaw','half_ur','half_ul','half_ll','half_lr','whole_mouth']);
    }

    public static function jaw_filter_label(string $filter): string {
        $labels = [
            'whole_mouth' => 'کل دهان (بدون دندان خاص)',
            'upper_jaw' => 'کل فک بالا',
            'lower_jaw' => 'کل فک پایین',
            'half_ur'   => 'نیم‌فک بالا راست',
            'half_ul'   => 'نیم‌فک بالا چپ',
            'half_ll'   => 'نیم‌فک پایین چپ',
            'half_lr'   => 'نیم‌فک پایین راست',
        ];
        return $labels[$filter] ?? $filter;
    }

    // ─── کد ذخیره‌سازی مخصوص فک/نیم‌فک (خارج از بازه اعداد دندان ۱۱-۴۸) ──
    public static function jaw_filter_to_code(string $filter): int {
        $codes = ['whole_mouth'=>0, 'upper_jaw'=>91, 'lower_jaw'=>92, 'half_ur'=>2, 'half_ul'=>1, 'half_ll'=>3, 'half_lr'=>4];
        return $codes[$filter] ?? 0;
    }

    public static function tooth_range(string $filter): array {
        switch ($filter) {
            case 'anterior':  return [1,2,3];
            case 'posterior': return [3,4,5,6,7,8];
            case 'premolar':  return [4,5];
            case 'molar':     return [6,7,8];
            default:          return [1,2,3,4,5,6,7,8];
        }
    }
}
