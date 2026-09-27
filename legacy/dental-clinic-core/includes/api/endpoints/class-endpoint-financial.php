<?php
defined('ABSPATH') || exit;

class Dental_Endpoint_Financial {

    private const NS = 'dental/v1';

    public function register_routes(): void {
        // محاسبه‌گر قسط (بدون ذخیره) — فقط مشاهده/محاسبه، چیزی تغییر نمی‌کنه
        register_rest_route(self::NS, '/financial/calculate', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'calculate'],
            'permission_callback' => [$this, 'check_view_permission'],
        ]);

        // ایجاد پلان قسطی — تغییردهنده، پس سطح «مدیریت» می‌خواد
        register_rest_route(self::NS, '/financial/installment', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'create_installment'],
            'permission_callback' => [$this, 'check_manage_permission'],
        ]);

        // پرداخت قسط — تغییردهنده، پس سطح «مدیریت» می‌خواد
        register_rest_route(self::NS, '/financial/pay-item', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'pay_item'],
            'permission_callback' => [$this, 'check_manage_permission'],
        ]);

        // اقساط بیمار
        register_rest_route(self::NS, '/financial/patient/(?P<patient_id>\d+)', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_patient_installments'],
            'permission_callback' => [$this, 'check_view_permission'],
        ]);

        // موجودی کیف پول
        register_rest_route(self::NS, '/wallet/balance/(?P<patient_id>\d+)', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_balance'],
            'permission_callback' => [$this, 'check_wallet_view_permission'],
        ]);

        // شارژ کیف پول
        // شارژ کیف پول — نکته مهم: این باید سخت‌گیرانه‌تر از مشاهده باشه،
        // چون قبلاً با همون مجوز عمومی مالی (که پذیرش هم داره) قابل‌دسترس بود.
        register_rest_route(self::NS, '/wallet/credit', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'credit_wallet'],
            'permission_callback' => [$this, 'check_admin_only_permission'],
        ]);
    }

    // ─── فقط مدیر کلینیک یا ادمین واقعی وردپرس — نه پذیرش، نه مالی معمولی ──
    public function check_admin_only_permission(): bool {
        if (!is_user_logged_in()) return false;
        if (current_user_can('manage_options')) return true;
        $cu = wp_get_current_user();
        return in_array('dental_admin', (array)$cu->roles);
    }

    // ─── مشاهده موجودی کیف‌پول: بیمار فقط خودش، پرسنل فقط نقش‌های مالی/بالینی ──
    // قبلاً '__return_true' بود (کاملاً عمومی، حتی بدون لاگین) و چک مالکیت
    // فقط داخل get_balance() بود که برای مهمان (بدون نقش) اصلاً اجرا نمی‌شد
    // → موجودی/امتیاز هر بیماری با enumerate کردن patient_id قابل‌خواندن بود.
    public function check_wallet_view_permission(WP_REST_Request $req): bool {
        if (!is_user_logged_in()) return false;
        if (current_user_can('manage_options')) return true;

        if (Dental_Roles_Manager::is_patient()) {
            return Dental_Auth_Manager::get_current_patient_post_id() === (int)$req->get_param('patient_id');
        }

        $cu = wp_get_current_user();
        return !empty(array_intersect((array)$cu->roles, ['dental_admin', 'dental_financial', 'dental_secretary', 'dental_doctor']));
    }

    public function calculate(WP_REST_Request $req): WP_REST_Response {
        $result = Dental_Installment_Manager::calculate(
            (float)$req->get_param('total_amount'),
            (float)$req->get_param('down_payment'),
            (int)$req->get_param('installment_count'),
            (float)($req->get_param('discount') ?: 0)
        );

        // تاریخ‌های سررسید
        $start = sanitize_text_field($req->get_param('start_date') ?: Dental_Jalali::today());
        $result['due_dates'] = Dental_Jalali::get_installment_dates($start, (int)$req->get_param('installment_count'));

        return new WP_REST_Response(['success' => true, 'data' => $result], 200);
    }

    public function create_installment(WP_REST_Request $req): WP_REST_Response {
        $result = Dental_Installment_Manager::create(
            (int)$req->get_param('patient_id'),
            (int)($req->get_param('treatment_id') ?: 0),
            (float)$req->get_param('total_amount'),
            (float)$req->get_param('down_payment'),
            (int)$req->get_param('installment_count'),
            sanitize_text_field($req->get_param('start_date') ?: ''),
            (float)($req->get_param('discount') ?: 0),
            sanitize_textarea_field($req->get_param('notes') ?: '')
        );

        $status = $result['success'] ? 201 : 422;
        return new WP_REST_Response($result, $status);
    }

    public function pay_item(WP_REST_Request $req): WP_REST_Response {
        $result = Dental_Installment_Manager::pay_item(
            (int)$req->get_param('item_id'),
            (float)$req->get_param('amount'),
            sanitize_text_field($req->get_param('method') ?: 'cash'),
            sanitize_text_field($req->get_param('ref') ?: ''),
            (bool)$req->get_param('from_wallet')
        );
        $status = $result['success'] ? 200 : 422;
        return new WP_REST_Response($result, $status);
    }

    public function get_patient_installments(WP_REST_Request $req): WP_REST_Response {
        $patient_id   = (int)$req->get_param('patient_id');
        $installments = Dental_Installment_Manager::get_patient_installments($patient_id);
        foreach ($installments as &$plan) {
            $plan['items'] = Dental_Installment_Manager::get_items((int)$plan['id']);
        }
        return new WP_REST_Response(['success' => true, 'data' => $installments], 200);
    }

    public function get_balance(WP_REST_Request $req): WP_REST_Response {
        $patient_id = (int)$req->get_param('patient_id');

        // بیمار فقط موجودی خودش را می‌بیند
        if (Dental_Roles_Manager::is_patient()) {
            $own_id = Dental_Auth_Manager::get_current_patient_post_id();
            if ($own_id !== $patient_id) {
                return new WP_REST_Response(['success' => false, 'message' => 'دسترسی مجاز نیست.'], 403);
            }
        }

        return new WP_REST_Response([
            'success' => true,
            'balance' => Dental_Patient_Wallet::get_balance($patient_id),
            'points'  => (int)get_post_meta($patient_id, '_loyalty_points', true),
        ], 200);
    }

    public function credit_wallet(WP_REST_Request $req): WP_REST_Response {
        $result = Dental_Patient_Wallet::credit(
            (int)$req->get_param('patient_id'),
            (float)$req->get_param('amount'),
            sanitize_text_field($req->get_param('source') ?: 'top_up'),
            (int)($req->get_param('reference_id') ?: 0),
            sanitize_text_field($req->get_param('description') ?: '')
        );
        $status = $result['success'] ? 200 : 422;
        return new WP_REST_Response($result, $status);
    }

    // ─── مشاهده — کسی که فقط اجازه‌ی دیدن مالی داره هم می‌تونه (دکتر مثلاً) ──
    public function check_view_permission(): bool {
        return is_user_logged_in() && (
            current_user_can('manage_options') ||
            current_user_can('dental_view_financials') ||
            current_user_can('dental_manage_financials')
        );
    }

    // ─── مدیریت (ساخت پلن قسطی، ثبت پرداخت) — رفع باگ: قبلاً همین چک
    // «مشاهده» برای این route‌های تغییردهنده هم استفاده می‌شد، یعنی
    // دکتر (که طبق طراحی فقط باید مالی رو *ببینه*) می‌تونست پلن قسطی
    // بسازه یا پرداخت واقعی ثبت کنه.
    public function check_manage_permission(): bool {
        return is_user_logged_in() && (
            current_user_can('manage_options') ||
            current_user_can('dental_manage_financials')
        );
    }
}
