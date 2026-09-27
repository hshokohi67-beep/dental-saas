<?php
defined('ABSPATH') || exit;

class Dental_Page_Analytics {

    public function render(): void {
        $kpi = Dental_Analytics_Manager::get_kpi_summary();
        $revenue_trend = Dental_Analytics_Manager::get_revenue_trend(6);
        $visits_trend  = Dental_Analytics_Manager::get_visits_trend(6);
        $by_doctor     = Dental_Analytics_Manager::get_revenue_by_doctor(30);
        $top_treatments = Dental_Analytics_Manager::get_top_treatments(8, 30);
        $new_vs_return = Dental_Analytics_Manager::get_new_vs_returning(30);
        $appt_status   = Dental_Analytics_Manager::get_appointment_status_breakdown(30);
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                <i data-lucide="bar-chart-3" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                آمار و عملکرد کلینیک
            </h1>
            <p class="dc-text-muted" style="margin-bottom:24px;">یه نگاه سریع به وضعیت کلی کلینیک — درآمد، بیماران، پزشکان و نوبت‌ها.</p>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:28px;">
                <div class="dc-card" style="margin-bottom:0;">
                    <div class="dc-card-body" style="padding:20px;">
                        <div style="font-size:11px;color:var(--dc-neutral-500);margin-bottom:6px;">💰 درآمد این ماه</div>
                        <div style="font-size:22px;font-weight:800;color:var(--dc-primary);"><?php echo number_format($kpi['revenue_this']); ?> <span style="font-size:12px;font-weight:400;">تومان</span></div>
                        <div style="font-size:11px;margin-top:6px;color:<?php echo $kpi['revenue_change']>=0?'var(--dc-accent-dark)':'var(--dc-danger)'; ?>;">
                            <?php echo $kpi['revenue_change']>=0?'▲':'▼'; ?> <?php echo abs($kpi['revenue_change']); ?>٪ نسبت به ماه قبل
                        </div>
                    </div>
                </div>
                <div class="dc-card" style="margin-bottom:0;">
                    <div class="dc-card-body" style="padding:20px;">
                        <div style="font-size:11px;color:var(--dc-neutral-500);margin-bottom:6px;">👥 بیماران این ماه</div>
                        <div style="font-size:22px;font-weight:800;color:#8B5CF6;"><?php echo number_format($kpi['patients_this']); ?> <span style="font-size:12px;font-weight:400;">نفر</span></div>
                        <div style="font-size:11px;margin-top:6px;color:<?php echo $kpi['patients_change']>=0?'var(--dc-accent-dark)':'var(--dc-danger)'; ?>;">
                            <?php echo $kpi['patients_change']>=0?'▲':'▼'; ?> <?php echo abs($kpi['patients_change']); ?>٪ نسبت به ماه قبل
                        </div>
                    </div>
                </div>
                <div class="dc-card" style="margin-bottom:0;">
                    <div class="dc-card-body" style="padding:20px;">
                        <div style="font-size:11px;color:var(--dc-neutral-500);margin-bottom:6px;">🧾 میانگین ارزش هر ویزیت</div>
                        <div style="font-size:22px;font-weight:800;color:var(--dc-accent-warm);"><?php echo number_format($kpi['avg_visit']); ?> <span style="font-size:12px;font-weight:400;">تومان</span></div>
                    </div>
                </div>
                <?php if ($kpi['no_show_rate'] !== null): ?>
                <div class="dc-card" style="margin-bottom:0;">
                    <div class="dc-card-body" style="padding:20px;">
                        <div style="font-size:11px;color:var(--dc-neutral-500);margin-bottom:6px;">🚷 نرخ عدم‌حضور</div>
                        <div style="font-size:22px;font-weight:800;color:<?php echo $kpi['no_show_rate']>15?'var(--dc-danger)':'var(--dc-accent-dark)'; ?>;"><?php echo $kpi['no_show_rate']; ?>٪</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="dc-card" style="margin-bottom:20px;">
                <div class="dc-card-header"><h3 class="dc-heading-4">📈 روند درآمد ۶ ماه اخیر</h3></div>
                <div class="dc-card-body"><canvas id="dc-chart-revenue" height="80"></canvas></div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;" class="dc-analytics-grid">
                <div class="dc-card" style="margin-bottom:0;">
                    <div class="dc-card-header"><h3 class="dc-heading-4">📊 تعداد ویزیت و بیمار ماهانه</h3></div>
                    <div class="dc-card-body"><canvas id="dc-chart-visits" height="100"></canvas></div>
                </div>
                <div class="dc-card" style="margin-bottom:0;">
                    <div class="dc-card-header"><h3 class="dc-heading-4">🆕 بیمار جدید در برابر مراجعه‌کننده (۳۰ روز)</h3></div>
                    <div class="dc-card-body"><canvas id="dc-chart-newret" height="100"></canvas></div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;" class="dc-analytics-grid">
                <div class="dc-card" style="margin-bottom:0;">
                    <div class="dc-card-header"><h3 class="dc-heading-4">🦷 سهم درآمد هر پزشک (۳۰ روز اخیر)</h3></div>
                    <div class="dc-card-body"><canvas id="dc-chart-doctors" height="100"></canvas></div>
                </div>
                <div class="dc-card" style="margin-bottom:0;">
                    <div class="dc-card-header"><h3 class="dc-heading-4">🔝 پرتکرارترین خدمات (۳۰ روز اخیر)</h3></div>
                    <div class="dc-card-body"><canvas id="dc-chart-treatments" height="100"></canvas></div>
                </div>
            </div>

            <?php if (!empty($appt_status)): ?>
            <div class="dc-card" style="margin-bottom:20px;">
                <div class="dc-card-header"><h3 class="dc-heading-4">📅 وضعیت نوبت‌ها (۳۰ روز اخیر)</h3></div>
                <div class="dc-card-body"><canvas id="dc-chart-appts" height="60"></canvas></div>
            </div>
            <?php endif; ?>
        </div>

        <style>
        @media (max-width: 900px) { .dc-analytics-grid { grid-template-columns: 1fr !important; } }
        </style>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
        if(typeof lucide!=="undefined")lucide.createIcons();

        Chart.defaults.font.family = "Vazirmatn, Tahoma, sans-serif";
        Chart.defaults.color = "#5A7080";

        var PALETTE = ['#1A6B8A','#8B5CF6','#2ECC9A','#F0A500','#E05252','#5DADE2','#D4A574','#7A96A4'];

        new Chart(document.getElementById('dc-chart-revenue'), {
            type: 'line',
            data: {
                labels: <?php echo wp_json_encode(array_column($revenue_trend,'label')); ?>,
                datasets: [{
                    label: 'درآمد (تومان)',
                    data: <?php echo wp_json_encode(array_column($revenue_trend,'value')); ?>,
                    borderColor: '#1A6B8A', backgroundColor: 'rgba(26,107,138,.1)',
                    fill: true, tension: .35, borderWidth: 2.5, pointRadius: 4, pointBackgroundColor: '#1A6B8A',
                }]
            },
            options: { plugins:{legend:{display:false}}, scales:{ y:{ ticks:{ callback:v=>Number(v).toLocaleString() } } } }
        });

        new Chart(document.getElementById('dc-chart-visits'), {
            type: 'bar',
            data: {
                labels: <?php echo wp_json_encode(array_column($visits_trend,'label')); ?>,
                datasets: [
                    { label: 'تعداد ویزیت', data: <?php echo wp_json_encode(array_column($visits_trend,'visits')); ?>, backgroundColor: '#1A6B8A', borderRadius: 6 },
                    { label: 'بیمار یکتا', data: <?php echo wp_json_encode(array_column($visits_trend,'patients')); ?>, backgroundColor: '#8B5CF6', borderRadius: 6 },
                ]
            },
            options: { plugins:{legend:{position:'bottom'}} }
        });

        new Chart(document.getElementById('dc-chart-newret'), {
            type: 'doughnut',
            data: {
                labels: ['بیمار جدید','مراجعه‌کننده قدیمی'],
                datasets: [{ data: [<?php echo (int)$new_vs_return['new']; ?>, <?php echo (int)$new_vs_return['returning']; ?>], backgroundColor: ['#2ECC9A','#1A6B8A'] }]
            },
            options: { plugins:{legend:{position:'bottom'}}, cutout:'65%' }
        });

        new Chart(document.getElementById('dc-chart-doctors'), {
            type: 'bar',
            data: {
                labels: <?php echo wp_json_encode(array_column($by_doctor,'doctor')); ?>,
                datasets: [{ label: 'درآمد', data: <?php echo wp_json_encode(array_map('floatval',array_column($by_doctor,'revenue'))); ?>, backgroundColor: PALETTE, borderRadius: 6 }]
            },
            options: { indexAxis: 'y', plugins:{legend:{display:false}} }
        });

        new Chart(document.getElementById('dc-chart-treatments'), {
            type: 'bar',
            data: {
                labels: <?php echo wp_json_encode(array_column($top_treatments,'service_name')); ?>,
                datasets: [{ label: 'تعداد', data: <?php echo wp_json_encode(array_map('intval',array_column($top_treatments,'cnt'))); ?>, backgroundColor: '#F0A500', borderRadius: 6 }]
            },
            options: { indexAxis: 'y', plugins:{legend:{display:false}} }
        });

        <?php if (!empty($appt_status)):
            $status_labels_fa = ['pending'=>'در انتظار','confirmed'=>'تأییدشده','done'=>'انجام‌شده','cancelled'=>'لغوشده','rejected'=>'ردشده','no_show'=>'عدم‌حضور'];
            $status_colors = ['pending'=>'#F0A500','confirmed'=>'#5DADE2','done'=>'#2ECC9A','cancelled'=>'#B8860B','rejected'=>'#7A96A4','no_show'=>'#E05252'];
        ?>
        new Chart(document.getElementById('dc-chart-appts'), {
            type: 'bar',
            data: {
                labels: <?php echo wp_json_encode(array_map(fn($r)=>$status_labels_fa[$r['status']]??$r['status'], $appt_status)); ?>,
                datasets: [{ data: <?php echo wp_json_encode(array_map('intval',array_column($appt_status,'cnt'))); ?>,
                    backgroundColor: <?php echo wp_json_encode(array_map(fn($r)=>$status_colors[$r['status']]??'#999', $appt_status)); ?>, borderRadius: 6 }]
            },
            options: { indexAxis: 'y', plugins:{legend:{display:false}} }
        });
        <?php endif; ?>
        </script>
        <?php
    }
}
