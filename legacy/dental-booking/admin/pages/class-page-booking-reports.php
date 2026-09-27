<?php
defined('ABSPATH') || exit;

class Dental_Page_Booking_Reports {

    public function render(): void {
        global $wpdb;

        // بازه زمانی
        // نکته: از current_time() استفاده می‌شود، نه date() ساده —
        // چون date() از تایم‌زون سرور PHP استفاده می‌کند که ممکن است
        // با تایم‌زون تنظیم‌شده در وردپرس یکی نباشد و باعث جا افتادن
        // رکوردهای «امروز» از بازه گزارش شود.
        $range = sanitize_key($_GET['range'] ?? 'month');
        $today = current_time('Y-m-d');
        switch ($range) {
            case 'week':
                $from = date('Y-m-d', strtotime($today . ' -7 days'));
                $range_label = '۷ روز اخیر';
                break;
            case 'quarter':
                $from = date('Y-m-d', strtotime($today . ' -90 days'));
                $range_label = '۹۰ روز اخیر';
                break;
            default:
                $range = 'month';
                $from = date('Y-m-d', strtotime($today . ' -30 days'));
                $range_label = '۳۰ روز اخیر';
        }
        $to = $today;

        // آمار کلی
        $overall = $wpdb->get_row($wpdb->prepare(
            "SELECT COUNT(*) as total,
                SUM(status='confirmed') as confirmed,
                SUM(status='done') as done,
                SUM(status='cancelled') as cancelled,
                SUM(status='rejected') as rejected,
                SUM(status='no_show') as no_show,
                SUM(status='pending') as pending
             FROM {$wpdb->prefix}dental_appointments
             WHERE appt_date BETWEEN %s AND %s",
            $from, $to
        ), ARRAY_A);

        $total_done_or_noshow = (int)($overall['done']??0) + (int)($overall['no_show']??0);
        $noshow_rate = $total_done_or_noshow > 0 ? round((int)($overall['no_show']??0) / $total_done_or_noshow * 100) : 0;
        $cancel_rate = (int)($overall['total']??0) > 0 ? round((int)($overall['cancelled']??0) / (int)$overall['total'] * 100) : 0;

        // آمار به تفکیک پزشک
        $by_doctor = $wpdb->get_results($wpdb->prepare(
            "SELECT a.doctor_id, u.display_name as doctor_name,
                COUNT(*) as total,
                SUM(a.status='done') as done,
                SUM(a.status='no_show') as no_show,
                SUM(a.status='cancelled') as cancelled,
                SUM(a.status='confirmed') as confirmed,
                SUM(a.status='pending') as pending
             FROM {$wpdb->prefix}dental_appointments a
             LEFT JOIN {$wpdb->users} u ON a.doctor_id = u.ID
             WHERE a.appt_date BETWEEN %s AND %s
             GROUP BY a.doctor_id
             ORDER BY total DESC",
            $from, $to
        ), ARRAY_A);

        $chart_labels = array_map(fn($d) => $d['doctor_name'] ?: 'نامشخص', $by_doctor);
        $chart_data   = array_map(fn($d) => (int)$d['total'], $by_doctor);
        ?>
        <div class="dental-admin-wrap">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
                <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;">
                    <i data-lucide="bar-chart-3" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                    گزارش‌های نوبت‌دهی
                </h1>
                <div style="display:flex;gap:6px;">
                    <?php foreach(['week'=>'۷ روز','month'=>'۳۰ روز','quarter'=>'۹۰ روز'] as $r=>$l):
                        $active = $range === $r;
                    ?>
                    <a href="<?php echo esc_url(add_query_arg('range',$r)); ?>"
                       style="padding:7px 16px;border-radius:20px;font-size:12px;text-decoration:none;font-family:Tahoma;
                              background:<?php echo $active?'var(--dc-primary)':'var(--dc-neutral-100)'; ?>;
                              color:<?php echo $active?'#fff':'var(--dc-neutral-700)'; ?>;">
                        <?php echo esc_html($l); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <p style="color:var(--dc-neutral-500);font-size:13px;margin-bottom:20px;">آمار مربوط به <?php echo esc_html($range_label); ?></p>

            <!-- کارت‌های آماری -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:24px;">
                <?php foreach([
                    ['calendar-days','کل نوبت‌ها',(int)($overall['total']??0),'','var(--dc-primary)','var(--dc-primary-light)'],
                    ['check-circle-2','انجام شده',(int)($overall['done']??0),'','var(--dc-accent-dark)','var(--dc-accent-light)'],
                    ['user-x','عدم حضور',(int)($overall['no_show']??0),$noshow_rate.'٪ نرخ عدم حضور','var(--dc-danger)','var(--dc-danger-light)'],
                    ['x-circle','لغوشده',(int)($overall['cancelled']??0),$cancel_rate.'٪ نرخ لغو','var(--dc-accent-warm)','var(--dc-warning-light)'],
                ] as [$icon,$label,$val,$sub,$color,$bg]): ?>
                <div class="dc-card" style="padding:16px;border-top:3px solid <?php echo $color; ?>;">
                    <div style="width:34px;height:34px;border-radius:8px;background:<?php echo $bg; ?>;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
                        <i data-lucide="<?php echo esc_attr($icon); ?>" style="width:17px;height:17px;color:<?php echo $color; ?>;"></i>
                    </div>
                    <div style="font-size:22px;font-weight:700;color:<?php echo $color; ?>;"><?php echo number_format($val); ?></div>
                    <div style="font-size:12px;color:var(--dc-neutral-600);margin-top:2px;"><?php echo esc_html($label); ?></div>
                    <?php if($sub): ?><div style="font-size:11px;color:var(--dc-neutral-400);margin-top:2px;"><?php echo esc_html($sub); ?></div><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:20px;align-items:start;">

                <!-- جدول تفکیک پزشک -->
                <div class="dc-card">
                    <div class="dc-card-header">
                        <h3 class="dc-heading-4">👨‍⚕️ عملکرد هر پزشک</h3>
                    </div>
                    <div class="dc-table-wrap">
                        <table class="dc-table">
                            <thead>
                                <tr><th>پزشک</th><th>کل</th><th>انجام‌شده</th><th>عدم حضور</th><th>لغوشده</th><th>نرخ عدم حضور</th></tr>
                            </thead>
                            <tbody>
                            <?php if(empty($by_doctor)): ?>
                            <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--dc-neutral-400);">داده‌ای در این بازه یافت نشد</td></tr>
                            <?php else: foreach($by_doctor as $d):
                                $done_ns = (int)$d['done'] + (int)$d['no_show'];
                                $rate = $done_ns > 0 ? round((int)$d['no_show']/$done_ns*100) : 0;
                                $rate_color = $rate >= 20 ? 'var(--dc-danger)' : ($rate >= 10 ? 'var(--dc-accent-warm)' : 'var(--dc-accent-dark)');
                            ?>
                            <tr>
                                <td style="font-weight:600;"><?php echo esc_html($d['doctor_name'] ?: 'نامشخص'); ?></td>
                                <td style="text-align:center;"><?php echo (int)$d['total']; ?></td>
                                <td style="text-align:center;color:var(--dc-accent-dark);font-weight:600;"><?php echo (int)$d['done']; ?></td>
                                <td style="text-align:center;color:var(--dc-danger);font-weight:600;"><?php echo (int)$d['no_show']; ?></td>
                                <td style="text-align:center;color:var(--dc-neutral-500);"><?php echo (int)$d['cancelled']; ?></td>
                                <td style="text-align:center;">
                                    <span style="background:<?php echo $rate_color; ?>22;color:<?php echo $rate_color; ?>;border-radius:10px;padding:2px 10px;font-size:11px;font-weight:700;">
                                        <?php echo $rate; ?>٪
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- نمودار -->
                <div class="dc-card">
                    <div class="dc-card-header">
                        <h3 class="dc-heading-4">📊 توزیع نوبت‌ها بین پزشکان</h3>
                    </div>
                    <div class="dc-card-body" style="padding:16px;">
                        <?php if(empty($by_doctor)): ?>
                        <p style="text-align:center;color:var(--dc-neutral-400);font-size:13px;padding:40px 0;">داده‌ای برای نمایش نیست</p>
                        <?php else: ?>
                        <canvas id="dc-doctor-chart" height="220"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if(!empty($by_doctor)): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            var ctx = document.getElementById('dc-doctor-chart');
            if(!ctx || typeof Chart === 'undefined') return;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo wp_json_encode($chart_labels); ?>,
                    datasets: [{
                        label: 'تعداد نوبت',
                        data: <?php echo wp_json_encode($chart_data); ?>,
                        backgroundColor: 'rgba(26,107,138,0.75)',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        });
        </script>
        <?php endif; ?>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }
}
