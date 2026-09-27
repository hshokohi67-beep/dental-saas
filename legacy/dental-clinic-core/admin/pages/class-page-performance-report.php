<?php
defined('ABSPATH') || exit;

class Dental_Page_Performance_Report {

    // ─── خروجی CSV — عمومی تا از هوک زودهنگام admin_init قابل صدا زدن باشد ──
    public function export_csv(): void {
        $cu = wp_get_current_user();
        $is_admin  = current_user_can('manage_options') || in_array('dental_admin', (array)$cu->roles);
        $is_doctor = in_array('dental_doctor', (array)$cu->roles) && !$is_admin;

        $from_j = sanitize_text_field($_GET['from'] ?? Dental_Jalali::add_days(Dental_Jalali::today(), -30));
        $to_j   = sanitize_text_field($_GET['to']   ?? Dental_Jalali::today());
        $from_g = Dental_Jalali::to_gregorian($from_j) ?: date('Y-m-d', strtotime('-30 days'));
        $to_g   = Dental_Jalali::to_gregorian($to_j)   ?: current_time('Y-m-d');
        $doctor_f = $is_doctor ? $cu->ID : (int)($_GET['doctor'] ?? 0);

        $details = Dental_Service_Catalog::get_performance_report($from_g, $to_g, $doctor_f);

        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=gozaresh-daramad-' . $from_j . '_' . $to_j . '.csv');

        $out = fopen('php://output', 'w');
        // BOM برای نمایش درست فارسی در اکسل
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['تاریخ','بیمار','موبایل بیمار','پزشک','خدمت','دندان','مبلغ کل (تومان)','تخفیف','مبلغ نهایی','وضعیت مالی']);

        $status_labels = ['confirmed'=>'تأییدشده','pending'=>'در انتظار تأیید'];
        foreach ($details as $d) {
            $mobile = get_post_meta($d['patient_id'], '_patient_mobile', true);
            $discount_amt = (float)($d['ledger_discount'] ?? 0);
            fputcsv($out, [
                Dental_Jalali::to_jalali($d['recorded_date'],'Y/m/d'),
                $d['patient_name'],
                $mobile,
                $d['doctor_name'],
                $d['service_name'],
                Dental_Service_Catalog::describe_tooth_number($d['tooth_number']?:null),
                $d['price'],
                $discount_amt,
                (float)$d['price'] - $discount_amt,
                $status_labels[$d['ledger_status']] ?? '—',
            ]);
        }
        fclose($out);
    }

    public function render(): void {
        $cu = wp_get_current_user();
        $is_admin  = current_user_can('manage_options') || in_array('dental_admin', (array)$cu->roles);
        $is_doctor = in_array('dental_doctor', (array)$cu->roles) && !$is_admin;

        $from_j = sanitize_text_field($_GET['from'] ?? Dental_Jalali::add_days(Dental_Jalali::today(), -30));
        // ─── ذخیره‌ی درصد کارانه‌ی هر پزشک — فقط مدیر می‌تونه تنظیم کنه ──
        if ($is_admin && isset($_POST['dental_save_commission']) && check_admin_referer('dental_commission')) {
            $doc_id = (int)($_POST['commission_doctor_id'] ?? 0);
            $percent = max(0, min(100, (float)($_POST['commission_percent'] ?? 0)));
            if ($doc_id) update_user_meta($doc_id, '_dental_commission_percent', $percent);
        }

        $to_j   = sanitize_text_field($_GET['to']   ?? Dental_Jalali::today());
        $from_g = Dental_Jalali::to_gregorian($from_j) ?: date('Y-m-d', strtotime('-30 days'));
        $to_g   = Dental_Jalali::to_gregorian($to_j)   ?: current_time('Y-m-d');

        // ─── دکتر فقط گزارش خودش را می‌بیند، مدیریت می‌تواند فیلتر کند ──
        $doctor_f = $is_doctor ? $cu->ID : (int)($_GET['doctor'] ?? 0);

        $summary = Dental_Service_Catalog::get_doctor_summary($from_g, $to_g, $doctor_f);
        $details = Dental_Service_Catalog::get_performance_report($from_g, $to_g, $doctor_f);
        $doctors = get_users(['role__in'=>['dental_doctor'],'fields'=>['ID','display_name']]);

        // ─── محاسبه‌ی کارانه‌ی هر پزشک — درصد ذخیره‌شده × درآمد تأییدشده ──
        foreach ($summary as &$s) {
            $percent = (float)get_user_meta($s['doctor_id'], '_dental_commission_percent', true);
            $s['commission_percent'] = $percent;
            $s['commission_amount']  = round($s['confirmed_revenue'] * $percent / 100);
        }
        unset($s);

        $total_confirmed = array_sum(array_column($summary,'confirmed_revenue'));
        $total_pending   = array_sum(array_column($summary,'pending_revenue'));
        $total_count     = array_sum(array_column($summary,'total_count'));
        $total_commission= array_sum(array_column($summary,'commission_amount'));
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                <i data-lucide="trending-up" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                گزارش درآمد و عملکرد<?php echo $is_doctor ? ' من' : ''; ?>
            </h1>

            <!-- فیلتر -->
            <div class="dc-card" style="margin-bottom:16px;">
                <div class="dc-card-body" style="padding:16px;">
                    <form method="get" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
                        <input type="hidden" name="page" value="dental-reports">
                        <input type="hidden" name="section" value="performance">
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">از تاریخ</label>
                            <input type="text" name="from" class="dc-input dc-datepicker" value="<?php echo esc_attr($from_j); ?>" dir="ltr" style="width:130px;">
                        </div>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">تا تاریخ</label>
                            <input type="text" name="to" class="dc-input dc-datepicker" value="<?php echo esc_attr($to_j); ?>" dir="ltr" style="width:130px;">
                        </div>
                        <?php if ($is_admin): ?>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">پزشک</label>
                            <select name="doctor" class="dc-select" style="width:160px;">
                                <option value="0">همه پزشکان</option>
                                <?php foreach($doctors as $d): ?>
                                <option value="<?php echo $d->ID; ?>" <?php selected($doctor_f,$d->ID); ?>><?php echo esc_html($d->display_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        <button type="submit" class="dc-btn dc-btn-primary">🔍 اعمال فیلتر</button>
                    </form>
                </div>
            </div>

            <!-- خلاصه کلی -->
            <div class="dc-grid <?php echo $total_commission > 0 ? 'dc-grid-4' : 'dc-grid-3'; ?>" style="gap:16px;margin-bottom:20px;">
                <div class="dc-stat-card">
                    <div class="dc-stat-icon">💰</div>
                    <div class="dc-stat-value"><?php echo number_format($total_confirmed); ?></div>
                    <div class="dc-stat-label">درآمد تأییدشده (تومان)</div>
                </div>
                <div class="dc-stat-card">
                    <div class="dc-stat-icon">⏳</div>
                    <div class="dc-stat-value"><?php echo number_format($total_pending); ?></div>
                    <div class="dc-stat-label">در انتظار تأیید (تومان)</div>
                </div>
                <div class="dc-stat-card">
                    <div class="dc-stat-icon">📋</div>
                    <div class="dc-stat-value"><?php echo number_format($total_count); ?></div>
                    <div class="dc-stat-label">تعداد کل خدمات ثبت‌شده</div>
                </div>
                <?php if ($total_commission > 0): ?>
                <div class="dc-stat-card">
                    <div class="dc-stat-icon">🎯</div>
                    <div class="dc-stat-value" style="color:var(--dc-primary);"><?php echo number_format($total_commission); ?></div>
                    <div class="dc-stat-label"><?php echo $is_doctor ? 'کارانه‌ی من' : 'جمع کارانه'; ?> (تومان)</div>
                    <?php if ($is_doctor && !empty($summary[0]['commission_percent'])): ?>
                    <div style="font-size:10px;color:var(--dc-neutral-400);margin-top:2px;">بر اساس <?php echo esc_html($summary[0]['commission_percent']); ?>٪ درآمد تأییدشده</div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($is_admin && count($summary) > 1): ?>
            <!-- جدول مقایسه‌ای پزشکان (فقط مدیریت) -->
            <div class="dc-card" style="margin-bottom:20px;">
                <div class="dc-card-header">
                    <h3 class="dc-heading-4">📊 مقایسه پزشکان</h3>
                    <span style="font-size:11px;color:var(--dc-neutral-500);">جمع کارانه‌ی این بازه: <b style="color:var(--dc-primary);"><?php echo number_format($total_commission); ?> تومان</b></span>
                </div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>پزشک</th><th>تعداد کل</th><th>انجام‌شده</th><th>درآمد تأییدشده</th><th>در انتظار</th><th>درصد کارانه</th><th>مبلغ کارانه</th></tr></thead>
                        <tbody>
                        <?php foreach($summary as $s): ?>
                        <tr>
                            <td style="font-weight:600;cursor:pointer;color:var(--dc-primary);text-decoration:underline;"
                                onclick="dcToggleDoctorDetails(<?php echo (int)$s['doctor_id']; ?>,'<?php echo esc_js($from_j); ?>','<?php echo esc_js($to_j); ?>',this)">
                                <?php echo esc_html($s['doctor_name']); ?> <span style="font-size:10px;">▾</span>
                            </td>
                            <td style="text-align:center;"><?php echo (int)$s['total_count']; ?></td>
                            <td style="text-align:center;"><?php echo (int)$s['done_count']; ?></td>
                            <td style="color:var(--dc-accent-dark);font-weight:700;"><?php echo number_format($s['confirmed_revenue']); ?></td>
                            <td style="color:var(--dc-accent-warm);"><?php echo number_format($s['pending_revenue']); ?></td>
                            <td>
                                <form method="post" style="display:flex;align-items:center;gap:4px;" onsubmit="return true;">
                                    <?php wp_nonce_field('dental_commission'); ?>
                                    <input type="hidden" name="commission_doctor_id" value="<?php echo (int)$s['doctor_id']; ?>">
                                    <input type="number" name="commission_percent" value="<?php echo esc_attr($s['commission_percent']); ?>" min="0" max="100" step="1"
                                        style="width:55px;padding:4px 6px;border:1px solid var(--dc-neutral-200);border-radius:6px;font-size:12px;text-align:center;">
                                    <span style="font-size:11px;">٪</span>
                                    <button type="submit" name="dental_save_commission" class="dc-btn dc-btn-ghost dc-btn-sm" style="padding:2px 8px;font-size:11px;">ذخیره</button>
                                </form>
                            </td>
                            <td style="font-weight:700;color:var(--dc-primary);"><?php echo number_format($s['commission_amount']); ?></td>
                        </tr>
                        <tr class="dc-doctor-detail-row" data-doctor="<?php echo (int)$s['doctor_id']; ?>" style="display:none;">
                            <td colspan="7" style="padding:0;background:var(--dc-neutral-50);">
                                <div class="dc-doctor-detail-content" style="padding:14px;"></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- جزئیات کامل -->
            <div class="dc-card">
                <div class="dc-card-header"><h3 class="dc-heading-4">📜 جزئیات خدمات ثبت‌شده (<?php echo count($details); ?>)</h3>
                    <div style="display:flex;gap:6px;">
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=dental-dashboard&print_perf=1&from='.$from_j.'&to='.$to_j.'&doctor='.$doctor_f),'dental_print_perf')); ?>" target="_blank" class="dc-btn dc-btn-secondary dc-btn-sm">
                            <i data-lucide="printer" style="width:13px;height:13px;"></i> پرینت
                        </a>
                        <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(['export_csv'=>1]),'dental_export_perf')); ?>" class="dc-btn dc-btn-secondary dc-btn-sm">
                            <i data-lucide="file-down" style="width:13px;height:13px;"></i> خروجی اکسل (CSV)
                        </a>
                    </div>
                </div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>تاریخ</th><th>بیمار</th><?php if(!$is_doctor): ?><th>پزشک</th><?php endif; ?><th>خدمت</th><th>دندان</th><th>مبلغ کل</th><th>تخفیف</th><th>مبلغ نهایی</th><th>وضعیت مالی</th></tr></thead>
                        <tbody>
                        <?php if (empty($details)): ?>
                        <tr><td colspan="9" style="text-align:center;padding:28px;color:var(--dc-neutral-400);">داده‌ای در این بازه نیست</td></tr>
                        <?php else: foreach($details as $d):
                            $status_label = ['confirmed'=>['✅ تأییدشده','var(--dc-accent-dark)'],'pending'=>['⏳ در انتظار','var(--dc-accent-warm)']][$d['ledger_status']] ?? ['—','var(--dc-neutral-400)'];
                            $discount_amt = (float)($d['ledger_discount'] ?? 0);
                            $final_amt = (float)$d['price'] - $discount_amt;
                        ?>
                        <tr>
                            <td><?php echo esc_html(Dental_Jalali::to_jalali($d['recorded_date'],'Y/m/d')); ?></td>
                            <td style="font-weight:600;"><?php echo esc_html($d['patient_name']); ?></td>
                            <?php if(!$is_doctor): ?><td><?php echo esc_html($d['doctor_name']); ?></td><?php endif; ?>
                            <td style="font-size:12px;"><?php echo esc_html($d['service_name']); ?></td>
                            <td><?php echo esc_html(Dental_Service_Catalog::describe_tooth_number($d['tooth_number']?:null)); ?></td>
                            <td><?php echo number_format($d['price']); ?></td>
                            <td style="color:<?php echo $discount_amt>0?'var(--dc-danger)':'var(--dc-neutral-400)'; ?>;"><?php echo $discount_amt>0 ? '−'.number_format($discount_amt) : '—'; ?></td>
                            <td style="font-weight:700;color:var(--dc-primary);"><?php echo number_format($final_amt); ?></td>
                            <td style="color:<?php echo $status_label[1]; ?>;font-size:12px;"><?php echo $status_label[0]; ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <script>
        function dcToggleDoctorDetails(doctorId, from, to, el){
            var row = document.querySelector('.dc-doctor-detail-row[data-doctor="'+doctorId+'"]');
            if (!row) return;
            if (row.style.display === 'table-row') { row.style.display = 'none'; el.querySelector('span').textContent='▾'; return; }
            row.style.display = 'table-row';
            el.querySelector('span').textContent = '▴';
            var content = row.querySelector('.dc-doctor-detail-content');
            if (content.dataset.loaded) return;
            content.innerHTML = '<div style="text-align:center;color:#A0B4C0;padding:10px;">در حال بارگذاری...</div>';
            var fd = new FormData();
            fd.append('action','dental_doctor_report_details');
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
            fd.append('doctor_id', doctorId);
            fd.append('from', '<?php echo esc_js($from_g); ?>');
            fd.append('to', '<?php echo esc_js($to_g); ?>');
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                .then(r=>r.json()).then(function(res){
                    content.innerHTML = res.success ? res.data.html : '<p style="color:#E05252;text-align:center;">خطا در بارگذاری</p>';
                    content.dataset.loaded = '1';
                });
        }
        </script>
        <?php
    }

    // ─── پرینت تمیز گزارش (بدون چارچوب پیشخوان) ──────────────────
    public function render_print(string $from_j, string $to_j, int $doctor_f = 0): void {
        $from_g = Dental_Jalali::to_gregorian($from_j) ?: date('Y-m-d', strtotime('-30 days'));
        $to_g   = Dental_Jalali::to_gregorian($to_j)   ?: current_time('Y-m-d');
        $summary = Dental_Service_Catalog::get_doctor_summary($from_g, $to_g, $doctor_f);
        $details = Dental_Service_Catalog::get_performance_report($from_g, $to_g, $doctor_f);
        $total_confirmed = array_sum(array_column($summary,'confirmed_revenue'));
        $total_pending   = array_sum(array_column($summary,'pending_revenue'));
        ?>
        <!DOCTYPE html>
        <html lang="fa" dir="rtl">
        <head>
        <meta charset="UTF-8">
        <title>گزارش درآمد و عملکرد</title>
        <style>
        * { font-family: Tahoma, sans-serif; margin:0; padding:0; box-sizing:border-box; }
        body { direction:rtl; padding:20px; }
        h2 { text-align:center; margin-bottom:6px; font-size:17px; }
        .sub { text-align:center; font-size:12px; color:#555; margin-bottom:16px; }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; font-size:11px; }
        th, td { border:1px solid #999; padding:5px 6px; text-align:center; }
        th { background:#eee; }
        .totals { display:flex; justify-content:center; gap:30px; margin-bottom:20px; font-size:13px; font-weight:700; }
        .no-print { text-align:center; margin-bottom:16px; }
        @media print { .no-print{display:none;} }
        </style>
        </head>
        <body>
            <div class="no-print"><button onclick="window.print()" style="padding:10px 20px;">🖨️ چاپ</button></div>
            <h2>گزارش درآمد و عملکرد</h2>
            <div class="sub">از <?php echo esc_html($from_j); ?> تا <?php echo esc_html($to_j); ?></div>
            <div class="totals">
                <div>✅ دریافتی تأییدشده: <?php echo number_format($total_confirmed); ?> تومان</div>
                <div>⏳ در انتظار تأیید: <?php echo number_format($total_pending); ?> تومان</div>
            </div>
            <table>
                <thead><tr><th>پزشک</th><th>تعداد خدمت</th><th>دریافتی تأییدشده</th><th>در انتظار تأیید</th></tr></thead>
                <tbody>
                <?php foreach($summary as $s): ?>
                <tr>
                    <td><?php echo esc_html($s['doctor_name']); ?></td>
                    <td><?php echo (int)$s['total_count']; ?></td>
                    <td><?php echo number_format($s['confirmed_revenue']); ?></td>
                    <td><?php echo number_format($s['pending_revenue']); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <table>
                <thead><tr><th>تاریخ</th><th>بیمار</th><th>پزشک</th><th>خدمت</th><th>دندان</th><th>مبلغ</th><th>وضعیت</th></tr></thead>
                <tbody>
                <?php foreach($details as $d): ?>
                <tr>
                    <td><?php echo esc_html(Dental_Jalali::to_jalali($d['recorded_date'],'Y/m/d')); ?></td>
                    <td><?php echo esc_html($d['patient_name']); ?></td>
                    <td><?php echo esc_html($d['doctor_name']); ?></td>
                    <td><?php echo esc_html($d['service_name']); ?></td>
                    <td><?php echo esc_html(Dental_Service_Catalog::describe_tooth_number($d['tooth_number']?:null)); ?></td>
                    <td><?php echo number_format($d['price']); ?></td>
                    <td><?php echo $d['status']==='confirmed'?'تأییدشده':'در انتظار'; ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </body>
        </html>
        <?php
    }
}
