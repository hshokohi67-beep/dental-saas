<?php
defined('ABSPATH') || exit;

/**
 * Class Dental_Patient_Wallet
 * مدیریت کیف پول، شارژ، برداشت، امتیازات
 */
class Dental_Patient_Wallet {

    /**
     * دریافت موجودی
     */
    public static function get_balance(int $patient_id): float {
        return (float) get_post_meta($patient_id, '_wallet_balance', true);
    }

    /**
     * شارژ کیف پول — رفع امنیتی: قبلاً با get_post_meta+update_post_meta
     * (خواندن در PHP، نوشتن در PHP) انجام می‌شد که در برابر درخواست‌های
     * هم‌زمان (Race Condition) آسیب‌پذیره. الان با یه کوئری SQL اتمیک
     * انجام می‌شه که خودِ دیتابیس تضمین می‌کنه هیچ درخواست دیگه‌ای
     * وسط این عملیات دخالت نکنه.
     */
    public static function credit(
        int    $patient_id,
        float  $amount,
        string $source      = 'top_up',
        int    $reference_id = 0,
        string $description  = ''
    ): array {
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'مبلغ باید بزرگتر از صفر باشد.'];
        }

        global $wpdb;
        // اگه ردیف meta هنوز وجود نداشته باشه، اول با صفر بسازش (فقط
        // یه‌بار، برای هر بیمار) — چون آپدیت اتمیک روی ردیف ناموجود اثر نمی‌کنه
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key='_wallet_balance'", $patient_id
        ));
        if (!$exists) {
            add_post_meta($patient_id, '_wallet_balance', '0', true);
        }

        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->postmeta} SET meta_value = CAST(meta_value AS DECIMAL(15,2)) + %f
             WHERE post_id = %d AND meta_key = '_wallet_balance'",
            $amount, $patient_id
        ));

        $new_balance = self::get_balance($patient_id);
        self::log_transaction($patient_id, 'credit', $amount, $new_balance, $source, $reference_id, $description);

        // امتیاز وفاداری
        if ($source === 'top_up') {
            $rate   = (int) get_option('dental_loyalty_rate', 1);
            $points = (int) ($amount / 1000 * $rate);
            if ($points > 0) {
                self::add_points($patient_id, $points);
            }
        }

        Dental_Dev_Logger::log('INFO', "کیف پول شارژ شد", [
            'patient_id'  => $patient_id,
            'amount'      => $amount,
            'new_balance' => $new_balance,
            'source'      => $source,
        ]);

        if (class_exists('Dental_Audit_Log')) {
            Dental_Audit_Log::log('wallet_credit', "شارژ کیف پول بیمار #{$patient_id} — منبع: {$source}",
                ['entity_type'=>'patient', 'entity_id'=>$patient_id, 'amount'=>$amount]);
        }

        return [
            'success'     => true,
            'new_balance' => $new_balance,
            'message'     => 'کیف پول با موفقیت شارژ شد.',
        ];
    }

    /**
     * برداشت از کیف پول — رفع امنیتی: کوئری اتمیک با شرط «موجودی کافی
     * باشه» توی خودِ WHERE — اگه دو درخواست هم‌زمان بیان، دیتابیس خودش
     * تضمین می‌کنه فقط یکی موفق بشه و موجودی هرگز منفی نشه.
     */
    public static function deduct(
        int    $patient_id,
        float  $amount,
        string $source       = 'installment_pay',
        int    $reference_id = 0,
        string $description  = ''
    ): array {
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'مبلغ باید بزرگتر از صفر باشد.'];
        }

        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->postmeta} SET meta_value = CAST(meta_value AS DECIMAL(15,2)) - %f
             WHERE post_id = %d AND meta_key = '_wallet_balance' AND CAST(meta_value AS DECIMAL(15,2)) >= %f",
            $amount, $patient_id, $amount
        ));

        // اگه هیچ ردیفی تغییر نکرد، یعنی موجودی کافی نبوده (یا اصلاً
        // ردیفی نبود) — این دقیقاً همون چیزیه که جلوی منفی‌شدن رو می‌گیره
        if ($wpdb->rows_affected === 0) {
            $current = self::get_balance($patient_id);
            return [
                'success' => false,
                'message' => "موجودی کافی نیست. موجودی فعلی: " . number_format($current) . " تومان",
                'balance' => $current,
            ];
        }

        $new_balance = self::get_balance($patient_id);
        self::log_transaction($patient_id, 'debit', $amount, $new_balance, $source, $reference_id, $description);

        if (class_exists('Dental_Audit_Log')) {
            Dental_Audit_Log::log('wallet_deduct', "برداشت از کیف پول بیمار #{$patient_id} — منبع: {$source}",
                ['entity_type'=>'patient', 'entity_id'=>$patient_id, 'amount'=>$amount]);
        }

        return [
            'success'     => true,
            'new_balance' => $new_balance,
            'message'     => 'پرداخت از کیف پول انجام شد.',
        ];
    }

    /**
     * اضافه کردن امتیاز
     */
    public static function add_points(int $patient_id, int $points): void {
        $current = (int) get_post_meta($patient_id, '_loyalty_points', true);
        update_post_meta($patient_id, '_loyalty_points', $current + $points);

        Dental_Dev_Logger::log('INFO', "امتیاز اضافه شد", [
            'patient_id' => $patient_id,
            'points'     => $points,
            'total'      => $current + $points,
        ]);
    }

    /**
     * تبدیل امتیاز به موجودی کیف پول
     * هر ۱۰۰ امتیاز = ۱۰,۰۰۰ تومان
     */
    public static function redeem_points(int $patient_id, int $points): array {
        $current_points = (int) get_post_meta($patient_id, '_loyalty_points', true);

        if ($points > $current_points) {
            return ['success' => false, 'message' => 'امتیاز کافی ندارید.'];
        }

        $value = ($points / 100) * 10000;
        update_post_meta($patient_id, '_loyalty_points', $current_points - $points);

        return self::credit(
            $patient_id, $value,
            'loyalty_reward', 0,
            "تبدیل {$points} امتیاز به کیف پول"
        );
    }

    /**
     * تاریخچه تراکنش‌ها
     */
    public static function get_transactions(int $patient_id, int $limit = 50): array {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_wallet_transactions
             WHERE patient_id = %d ORDER BY created_at DESC LIMIT %d",
            $patient_id, $limit
        ), ARRAY_A);
    }

    /**
     * ذخیره تراکنش در DB
     */
    private static function log_transaction(
        int    $patient_id,
        string $type,
        float  $amount,
        float  $balance_after,
        string $source,
        int    $reference_id,
        string $description
    ): void {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'dental_wallet_transactions',
            [
                'patient_id'       => $patient_id,
                'transaction_type' => $type,
                'amount'           => $amount,
                'balance_after'    => $balance_after,
                'source'           => $source,
                'reference_id'     => $reference_id ?: null,
                'description'      => $description,
                'created_at'       => current_time('mysql'),
                'created_by'       => get_current_user_id(),
            ],
            ['%d','%s','%f','%f','%s','%d','%s','%s','%d']
        );
    }
}
