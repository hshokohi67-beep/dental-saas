<?php
defined('ABSPATH') || exit;

class Dental_Booking_Frontend {

    public function __construct() {
        add_shortcode('dental_booking', [$this, 'render_booking_tab']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets(): void {
        if (!$this->is_booking_page()) return;

        wp_enqueue_style('vazirmatn',
            'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap',
            [], null);

        wp_enqueue_style('sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
            [], '11');

        wp_enqueue_script('lucide',
            'https://cdn.jsdelivr.net/npm/lucide@0.408.0/dist/umd/lucide.min.js',
            [], '0.408.0', true);

        wp_enqueue_script('sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
            [], '11', true);

        // ─── تقویم شمسی (از پلاگین کلینیک) ────────────────────────
        if (defined('DENTAL_CORE_URL')) {
            wp_enqueue_script('dental-datepicker',
                DENTAL_CORE_URL . 'assets/js/dental-datepicker.js',
                ['jquery'], DENTAL_CORE_VERSION, true);

            wp_localize_script('dental-datepicker', 'dentalPortal', [
                'apiBase' => rest_url('dental/v1'),
                'nonce'   => wp_create_nonce('wp_rest'),
            ]);
        }
    }

    private function is_booking_page(): bool {
        global $post;
        return is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'dental_booking');
    }

    public function render_booking_tab(): string {
        if (!is_user_logged_in()) {
            $login_page = get_page_by_path('dental-login');
            $login_url  = $login_page
                ? add_query_arg('redirect_to', urlencode(get_permalink()), get_permalink($login_page))
                : wp_login_url(get_permalink());
            return '<div style="direction:rtl;text-align:center;padding:48px 20px;font-family:Vazirmatn,Tahoma,sans-serif;">
                <div style="font-size:48px;margin-bottom:16px;">🔒</div>
                <h2 style="color:#1A2733;margin-bottom:8px;">ورود لازم است</h2>
                <p style="color:#5A7080;margin-bottom:20px;">برای رزرو نوبت ابتدا وارد حساب کاربری خود شوید.</p>
                <a href="' . esc_url($login_url) . '" style="background:#1A6B8A;color:#fff;padding:10px 24px;border-radius:8px;text-decoration:none;font-size:14px;">ورود / ثبت‌نام</a>
            </div>';
        }
        $patient_id = Dental_Frontend::get_current_patient_id();
        if (!$patient_id) {
            return '<div style="direction:rtl;text-align:center;padding:48px 20px;font-family:Vazirmatn,Tahoma,sans-serif;">
                <div style="font-size:48px;margin-bottom:16px;">🦷</div>
                <h2 style="color:#1A2733;">پرونده‌ای یافت نشد</h2>
                <p style="color:#5A7080;">حساب کاربری شما به هیچ پرونده‌ای متصل نیست.</p>
            </div>';
        }
        ob_start();
        (new Dental_Booking_Wizard($patient_id))->render();
        return ob_get_clean();
    }
}
