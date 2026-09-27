<?php
defined('ABSPATH') || exit;

class Dental_SMS_Cron {

    public function __construct() {
        add_action('dental_sms_cron',   [$this, 'process_sms_queue']);
        add_action('dental_daily_cron', [$this, 'send_installment_reminders']);
        add_action('dental_daily_cron', [$this, 'send_overdue_alerts']);
        add_action('dental_daily_cron', [$this, 'send_birthday_greetings']);
    }

    // ─── پردازش صف SMS — نکته مهم: این متد دیگه خودش پیامک نمی‌فرسته،
    // چون Dental_SMS_Dispatcher::process_queue() از قبل دقیقاً همین کارو
    // با منطق کامل‌تر (retry, log) انجام می‌ده. نگه‌داشتن دو مسیر جدا
    // برای ارسال از صف، ریسک ارسال دوباره/تکراری داشت.
    public function process_sms_queue(): void {
        if (class_exists('Dental_SMS_Dispatcher')) {
            (new Dental_SMS_Dispatcher())->process_queue();
        }
    }

    // ─── یادآور اقساط ────────────────────────────────────────
    public function send_installment_reminders(): void {
        global $wpdb;

        $days3 = (int)get_option('dental_reminder_days_3', 3);
        $days1 = (int)get_option('dental_reminder_days_1', 1);
        $tpl   = get_option('dental_sms_tpl_installment_reminder',
            "کلینیک {clinic_name}\nکاربر گرامی {patient_name}،\nقسط {amount} تومان در تاریخ {due_date} سررسید دارد."
        );
        $clinic = get_option('dental_clinic_name', get_bloginfo('name'));

        foreach([$days3, $days1] as $days) {
            $target_date = date('Y-m-d', strtotime("+{$days} days"));

            $items = $wpdb->get_results($wpdb->prepare(
                "SELECT ii.*, i.patient_id, p.post_title as patient_name,
                        pm.meta_value as mobile
                 FROM {$wpdb->prefix}dental_installment_items ii
                 JOIN {$wpdb->prefix}dental_installments i ON ii.installment_id = i.id
                 JOIN {$wpdb->posts} p ON i.patient_id = p.ID
                 JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_patient_mobile'
                 WHERE ii.due_date = %s
                 AND ii.status = 'pending'
                 AND (ii.reminder_sent_at IS NULL OR ii.reminder_count < 2)
                 AND p.post_status = 'publish'",
                $target_date
            ), ARRAY_A);

            foreach($items as $item) {
                if (empty($item['mobile'])) continue;

                $msg = str_replace(
                    ['{patient_name}', '{amount}', '{due_date}', '{clinic_name}'],
                    [$item['patient_name'], number_format($item['amount']), $item['due_date_jalali'], $clinic],
                    $tpl
                );

                $this->queue_sms($item['mobile'], $msg, 'installment_reminder', $item['patient_id']);

                // آپدیت reminder_sent_at
                $wpdb->update(
                    $wpdb->prefix . 'dental_installment_items',
                    [
                        'reminder_sent_at' => current_time('mysql'),
                        'reminder_count'   => (int)$item['reminder_count'] + 1,
                    ],
                    ['id' => $item['id']],
                    ['%s','%d'], ['%d']
                );
            }
        }
    }

    // ─── اطلاع معوقه ─────────────────────────────────────────
    public function send_overdue_alerts(): void {
        if (!get_option('dental_overdue_sms_enabled', 1)) return;

        global $wpdb;
        $tpl    = get_option('dental_sms_tpl_overdue_installment',
            "کلینیک {clinic_name}\nکاربر گرامی {patient_name}،\nقسط {amount} تومان از سررسید گذشته است."
        );
        $clinic = get_option('dental_clinic_name', get_bloginfo('name'));

        // آپدیت وضعیت معوقه
        $wpdb->query(
            "UPDATE {$wpdb->prefix}dental_installment_items
             SET status='overdue'
             WHERE status='pending' AND due_date < CURDATE()"
        );

        // ارسال SMS برای معوقه‌هایی که امروز تازه معوقه شدن
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT ii.*, i.patient_id, p.post_title as patient_name, pm.meta_value as mobile
             FROM {$wpdb->prefix}dental_installment_items ii
             JOIN {$wpdb->prefix}dental_installments i ON ii.installment_id = i.id
             JOIN {$wpdb->posts} p ON i.patient_id = p.ID
             JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_patient_mobile'
             WHERE ii.status = 'overdue'
             AND DATE(ii.due_date) = %s
             AND p.post_status = 'publish'",
            current_time('Y-m-d')
        ), ARRAY_A);

        foreach($items as $item) {
            if(empty($item['mobile'])) continue;
            $msg = str_replace(
                ['{patient_name}','{amount}','{due_date}','{clinic_name}'],
                [$item['patient_name'], number_format($item['amount']), $item['due_date_jalali'], $clinic],
                $tpl
            );
            $this->queue_sms($item['mobile'], $msg, 'overdue_installment', $item['patient_id']);
        }
    }

    // ─── تبریک تولد ──────────────────────────────────────────
    public function send_birthday_greetings(): void {
        if (!get_option('dental_birthday_sms_enabled', 0)) return;

        $tpl    = get_option('dental_sms_tpl_birthday',
            "کلینیک {clinic_name}\n{patient_name} عزیز، روز تولدتان مبارک! 🎂"
        );
        $clinic = get_option('dental_clinic_name', get_bloginfo('name'));
        $today  = Dental_Jalali::today();
        $parts  = explode('/', $today);
        if (count($parts) < 3) return;
        $today_md = '/' . $parts[1] . '/' . $parts[2]; // ماه/روز

        $patients = get_posts([
            'post_type'      => 'dental_patient',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => [
                ['key' => '_patient_dob_jalali', 'compare' => 'EXISTS'],
            ],
        ]);

        foreach($patients as $p) {
            $dob    = get_post_meta($p->ID, '_patient_dob_jalali', true);
            $mobile = get_post_meta($p->ID, '_patient_mobile', true);
            if (!$dob || !$mobile) continue;

            $dob_parts = explode('/', $dob);
            if (count($dob_parts) < 3) continue;
            $dob_md = '/' . $dob_parts[1] . '/' . $dob_parts[2];

            if ($dob_md === $today_md) {
                $msg = str_replace(['{patient_name}','{clinic_name}'], [$p->post_title, $clinic], $tpl);
                $this->queue_sms($mobile, $msg, 'birthday', $p->ID);
            }
        }
    }

    // ─── اضافه به صف ─────────────────────────────────────────
    private function queue_sms(string $mobile, string $message, string $trigger, int $patient_id): void {
        if (class_exists('Dental_SMS_Dispatcher')) {
            (new Dental_SMS_Dispatcher())->queue($mobile, $message, $trigger, $patient_id);
        }
    }
}
