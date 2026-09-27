<?php
defined('ABSPATH') || exit;

class Dental_Inventory_Manager {

    // ─── دسته‌بندی‌ها ────────────────────────────────────────────
    public static function get_categories(): array {
        global $wpdb;
        $rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dental_inventory_categories ORDER BY sort_order ASC, name ASC", ARRAY_A);
        if (empty($rows)) {
            $defaults = ['کنترل عفونت و PPE','مصرفی‌ها','بی‌حسی','مواد ترمیمی','اندو (عصب‌کشی)','ارتودنسی','بهداشت و پروفیلاکسی','لوازم اداری و اتاق انتظار','آزمایشگاهی'];
            foreach ($defaults as $i => $name) {
                $wpdb->insert($wpdb->prefix.'dental_inventory_categories', ['name'=>$name, 'sort_order'=>$i], ['%s','%d']);
            }
            return self::get_categories();
        }
        return $rows;
    }

    public static function save_category(string $name): int {
        global $wpdb;
        $wpdb->insert($wpdb->prefix.'dental_inventory_categories', ['name'=>sanitize_text_field($name)], ['%s']);
        return (int)$wpdb->insert_id;
    }

    // ─── کالاها ──────────────────────────────────────────────────
    public static function get_items(?int $category_id = null, bool $active_only = true): array {
        global $wpdb;
        $where = $active_only ? 'WHERE is_active=1' : 'WHERE 1=1';
        $params = [];
        if ($category_id) { $where .= ' AND category_id=%d'; $params[] = $category_id; }
        $sql = "SELECT * FROM {$wpdb->prefix}dental_inventory_items {$where} ORDER BY name ASC";
        return $params ? $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A) : $wpdb->get_results($sql, ARRAY_A);
    }

    public static function get_item(int $id): ?array {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}dental_inventory_items WHERE id=%d", $id), ARRAY_A);
        return $row ?: null;
    }

    public static function save_item(?int $id, array $data): int {
        global $wpdb;
        $row = [
            'category_id'    => !empty($data['category_id']) ? (int)$data['category_id'] : null,
            'name'           => sanitize_text_field($data['name']),
            'unit'           => sanitize_text_field($data['unit'] ?: 'عدد'),
            'min_stock'      => (float)($data['min_stock'] ?? 0),
            'unit_cost'      => (int)($data['unit_cost'] ?? 0),
            'supplier_name'  => sanitize_text_field($data['supplier_name'] ?? ''),
            'supplier_phone' => sanitize_text_field($data['supplier_phone'] ?? ''),
        ];
        if ($id) {
            $wpdb->update($wpdb->prefix.'dental_inventory_items', $row, ['id'=>$id]);
            return $id;
        }
        $row['current_stock'] = 0;
        $row['created_at'] = current_time('mysql');
        $wpdb->insert($wpdb->prefix.'dental_inventory_items', $row);
        return (int)$wpdb->insert_id;
    }

    public static function deactivate_item(int $id): void {
        global $wpdb;
        $wpdb->update($wpdb->prefix.'dental_inventory_items', ['is_active'=>0], ['id'=>$id]);
    }

    // ─── تراکنش‌ها (ورود/خروج) ───────────────────────────────────
    public static function add_transaction(int $item_id, string $type, float $qty, array $extra = []): array {
        global $wpdb;
        $item = self::get_item($item_id);
        if (!$item) return ['success'=>false,'message'=>'کالا یافت نشد.'];
        if ($qty <= 0) return ['success'=>false,'message'=>'مقدار باید بزرگتر از صفر باشد.'];

        if ($type === 'out' && $item['current_stock'] < $qty) {
            return ['success'=>false,'message'=>"موجودی کافی نیست. موجودی فعلی: {$item['current_stock']} {$item['unit']}"];
        }

        $wpdb->insert($wpdb->prefix.'dental_inventory_transactions', [
            'item_id'      => $item_id,
            'type'         => $type,
            'quantity'     => $qty,
            'batch_expiry' => $extra['batch_expiry'] ?? null,
            'unit_cost'    => $extra['unit_cost'] ?? $item['unit_cost'],
            'patient_id'   => $extra['patient_id'] ?? null,
            'note'         => sanitize_text_field($extra['note'] ?? ''),
            'recorded_by'  => get_current_user_id(),
            'created_at'   => current_time('mysql'),
        ], ['%d','%s','%f','%s','%d','%d','%s','%d','%s']);

        $new_stock = $type === 'in' ? $item['current_stock'] + $qty : $item['current_stock'] - $qty;
        $update = ['current_stock' => $new_stock];
        // اگه ورود بود و تاریخ انقضا داشت، نزدیک‌ترین انقضا رو به‌روز کن
        if ($type === 'in' && !empty($extra['batch_expiry'])) {
            if (!$item['nearest_expiry'] || $extra['batch_expiry'] < $item['nearest_expiry']) {
                $update['nearest_expiry'] = $extra['batch_expiry'];
            }
        }
        $wpdb->update($wpdb->prefix.'dental_inventory_items', $update, ['id'=>$item_id]);

        return ['success'=>true, 'new_stock'=>$new_stock];
    }

    public static function get_transactions(int $item_id = 0, int $limit = 50): array {
        global $wpdb;
        $where = $item_id ? $wpdb->prepare('WHERE t.item_id=%d', $item_id) : '';
        return $wpdb->get_results(
            "SELECT t.*, i.name as item_name, i.unit, u.display_name as recorded_by_name
             FROM {$wpdb->prefix}dental_inventory_transactions t
             LEFT JOIN {$wpdb->prefix}dental_inventory_items i ON t.item_id=i.id
             LEFT JOIN {$wpdb->users} u ON t.recorded_by=u.ID
             {$where} ORDER BY t.created_at DESC LIMIT {$limit}", ARRAY_A
        );
    }

    // ─── ورود دسته‌جمعی از فایل seed اولیه — یک‌بار مصرف، آیتم‌های
    // تکراری (بر اساس نام) رد می‌شن تا دوباره اجرا هم مشکلی نسازه ──
    public static function bulk_import_from_seed(): array {
        if (!class_exists('Dental_Inventory_Seed_Data')) return ['imported'=>0,'skipped'=>0,'error'=>'فایل seed پیدا نشد'];
        global $wpdb;
        $existing_names = $wpdb->get_col("SELECT name FROM {$wpdb->prefix}dental_inventory_items");
        $existing_names = array_map('mb_strtolower', $existing_names);

        $categories = self::get_categories();
        $cat_map = array_column($categories, 'id', 'name');

        $imported = 0; $skipped = 0;
        foreach (Dental_Inventory_Seed_Data::get_items() as $row) {
            if (in_array(mb_strtolower($row['name']), $existing_names)) { $skipped++; continue; }
            $cat_id = $cat_map[$row['category']] ?? null;
            if (!$cat_id) {
                $cat_id = self::save_category($row['category']);
                $cat_map[$row['category']] = $cat_id;
            }
            $wpdb->insert($wpdb->prefix.'dental_inventory_items', [
                'category_id' => $cat_id, 'name' => $row['name'], 'unit' => 'عدد',
                'current_stock' => 0, 'min_stock' => 0, 'unit_cost' => 0, 'is_active' => 1,
                'created_at' => current_time('mysql'),
            ], ['%d','%s','%s','%f','%f','%d','%d','%s']);
            $existing_names[] = mb_strtolower($row['name']);
            $imported++;
        }
        return ['imported'=>$imported, 'skipped'=>$skipped];
    }

    // ─── ویرایش سریع یک کالا (برای ویرایش داخل جدول، بدون رفرش) ──────
    public static function quick_update(int $id, array $fields): bool {
        global $wpdb;
        $allowed = ['name','unit','min_stock','unit_cost','category_id','supplier_name','supplier_phone','current_stock'];
        $update = [];
        foreach ($fields as $k => $v) {
            if (!in_array($k, $allowed)) continue;
            $update[$k] = in_array($k,['min_stock','current_stock']) ? (float)$v : ($k === 'unit_cost' || $k === 'category_id' ? (int)$v : sanitize_text_field($v));
        }
        if (empty($update)) return false;
        return (bool)$wpdb->update($wpdb->prefix.'dental_inventory_items', $update, ['id'=>$id]);
    }

    // ─── لیست کالاها با فیلتر دسته + جستجو + صفحه‌بندی سمت سرور ──────
    public static function get_items_paged(int $page = 1, int $per_page = 50, int $category_id = 0, string $search = ''): array {
        global $wpdb;
        $where = ['is_active=1'];
        $params = [];
        if ($category_id) { $where[] = 'category_id=%d'; $params[] = $category_id; }
        if ($search) { $where[] = 'name LIKE %s'; $params[] = '%'.$wpdb->esc_like($search).'%'; }
        $where_sql = implode(' AND ', $where);

        $count_sql = "SELECT COUNT(*) FROM {$wpdb->prefix}dental_inventory_items WHERE {$where_sql}";
        $total = (int)($params ? $wpdb->get_var($wpdb->prepare($count_sql, $params)) : $wpdb->get_var($count_sql));

        $offset = ($page - 1) * $per_page;
        $sql = "SELECT * FROM {$wpdb->prefix}dental_inventory_items WHERE {$where_sql} ORDER BY name ASC LIMIT %d OFFSET %d";
        $items = $wpdb->get_results($wpdb->prepare($sql, [...$params, $per_page, $offset]), ARRAY_A);

        return ['items' => $items, 'total' => $total, 'pages' => (int)ceil($total / $per_page)];
    }


    // ─── ثبت چند درخواست هم‌زمان (سبد) — هر آیتم می‌تونه تعداد جدا داشته باشه ──
    public static function create_requests_batch(array $items, int $user_id): int {
        $count = 0;
        foreach ($items as $it) {
            $item_id = (int)($it['item_id'] ?? 0);
            $qty     = sanitize_text_field($it['qty'] ?? '');
            if (!$item_id) continue;
            $note = $qty ? "تعداد درخواستی: {$qty}" : '';
            if (self::create_request($item_id, $user_id, $note)) $count++;
        }
        return $count;
    }

    // ─── سرچ با فیلتر دسته اختیاری (برای صفحه درخواست کالا) ─────────
    public static function search_items_with_category(string $q, int $category_id = 0, int $limit = 100): array {
        global $wpdb;
        $where = ['is_active=1'];
        $params = [];
        if ($q !== '') { $where[] = 'name LIKE %s'; $params[] = '%'.$wpdb->esc_like($q).'%'; }
        if ($category_id) { $where[] = 'category_id=%d'; $params[] = $category_id; }
        $where_sql = implode(' AND ', $where);
        $sql = "SELECT * FROM {$wpdb->prefix}dental_inventory_items WHERE {$where_sql} ORDER BY name ASC LIMIT %d";
        $params[] = $limit;
        return $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
    }

    public static function search_items(string $q, int $limit = 15): array {
        global $wpdb;
        $like = '%' . $wpdb->esc_like($q) . '%';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_inventory_items
             WHERE is_active=1 AND name LIKE %s ORDER BY name ASC LIMIT %d", $like, $limit
        ), ARRAY_A);
    }

    // ─── درخواست کالا — هر پرسنلی می‌تونه ثبت کنه ────────────────────
    public static function create_request(int $item_id, int $user_id, string $note = ''): int {
        global $wpdb;
        $result = $wpdb->insert($wpdb->prefix.'dental_inventory_requests', [
            'item_id'      => $item_id,
            'requested_by' => $user_id,
            'note'         => sanitize_text_field($note),
            'status'       => 'pending',
            'created_at'   => current_time('mysql'),
        ], ['%d','%d','%s','%s','%s']);
        return $result ? (int)$wpdb->insert_id : 0;
    }

    public static function get_requests(string $status = 'pending', int $limit = 100): array {
        global $wpdb;
        $where = $status !== 'all' ? $wpdb->prepare('AND r.status=%s', $status) : '';
        return $wpdb->get_results(
            "SELECT r.*, i.name as item_name, i.unit, u.display_name as requester_name, res.display_name as resolver_name
             FROM {$wpdb->prefix}dental_inventory_requests r
             LEFT JOIN {$wpdb->prefix}dental_inventory_items i ON r.item_id=i.id
             LEFT JOIN {$wpdb->users} u ON r.requested_by=u.ID
             LEFT JOIN {$wpdb->users} res ON r.resolved_by=res.ID
             WHERE 1=1 $where ORDER BY r.created_at DESC LIMIT $limit", ARRAY_A
        );
    }

    // ─── درخواست‌های خودِ یک کاربر خاص (برای «درخواست‌های من» — همه می‌بینن) ──
    public static function get_my_requests(int $user_id, int $limit = 30): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT r.*, i.name as item_name, i.unit
             FROM {$wpdb->prefix}dental_inventory_requests r
             LEFT JOIN {$wpdb->prefix}dental_inventory_items i ON r.item_id=i.id
             WHERE r.requested_by=%d ORDER BY r.created_at DESC LIMIT %d", $user_id, $limit
        ), ARRAY_A);
    }

    public static function resolve_request(int $request_id, string $status, int $resolver_id): void {
        global $wpdb;
        $wpdb->update($wpdb->prefix.'dental_inventory_requests', [
            'status'      => in_array($status,['fulfilled','rejected']) ? $status : 'pending',
            'resolved_by' => $resolver_id,
            'resolved_at' => current_time('mysql'),
        ], ['id'=>$request_id]);
    }

    public static function count_pending_requests(): int {
        global $wpdb;
        return (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dental_inventory_requests WHERE status='pending'");
    }

    // ─── خروجی اکسل (CSV) کل تاریخچه درخواست‌ها ─────────────────────
    public static function export_requests_csv(): void {
        global $wpdb;
        $rows = $wpdb->get_results(
            "SELECT r.*, i.name as item_name, i.unit, u.display_name as requester_name, res.display_name as resolver_name
             FROM {$wpdb->prefix}dental_inventory_requests r
             LEFT JOIN {$wpdb->prefix}dental_inventory_items i ON r.item_id=i.id
             LEFT JOIN {$wpdb->users} u ON r.requested_by=u.ID
             LEFT JOIN {$wpdb->users} res ON r.resolved_by=res.ID
             ORDER BY r.created_at DESC", ARRAY_A
        );
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=درخواست‌های-کالا-' . date('Y-m-d') . '.csv');
        echo "\xEF\xBB\xBF"; // BOM برای نمایش درست فارسی توی اکسل
        $out = fopen('php://output', 'w');
        fputcsv($out, ['نام کالا','درخواست‌دهنده','توضیح','وضعیت','بررسی‌شده توسط','تاریخ درخواست','تاریخ بررسی']);
        $status_labels = ['pending'=>'در انتظار','fulfilled'=>'تأمین شد','rejected'=>'رد شد'];
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['item_name'], $r['requester_name'], $r['note'],
                $status_labels[$r['status']] ?? $r['status'], $r['resolver_name'] ?: '—',
                $r['created_at'], $r['resolved_at'] ?: '—',
            ]);
        }
        fclose($out);
        exit;
    }


    public static function get_low_stock_items(): array {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}dental_inventory_items
             WHERE is_active=1 AND min_stock > 0 AND current_stock <= min_stock
             ORDER BY (current_stock - min_stock) ASC", ARRAY_A
        );
    }

    public static function get_expiring_items(int $days = 30): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_inventory_items
             WHERE is_active=1 AND nearest_expiry IS NOT NULL
             AND nearest_expiry <= DATE_ADD(CURDATE(), INTERVAL %d DAY)
             ORDER BY nearest_expiry ASC", $days
        ), ARRAY_A);
    }

    public static function get_alert_counts(): array {
        return [
            'low_stock' => count(self::get_low_stock_items()),
            'expiring'  => count(self::get_expiring_items()),
        ];
    }

    // ─── گزارش مصرف ماهانه (برای گزارش‌گیری) ─────────────────────
    public static function get_monthly_consumption(string $from_date, string $to_date): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT i.name, i.unit, SUM(t.quantity) as total_qty, SUM(t.quantity * t.unit_cost) as total_cost
             FROM {$wpdb->prefix}dental_inventory_transactions t
             LEFT JOIN {$wpdb->prefix}dental_inventory_items i ON t.item_id=i.id
             WHERE t.type='out' AND t.created_at BETWEEN %s AND %s
             GROUP BY t.item_id ORDER BY total_qty DESC", $from_date, $to_date
        ), ARRAY_A);
    }
}
