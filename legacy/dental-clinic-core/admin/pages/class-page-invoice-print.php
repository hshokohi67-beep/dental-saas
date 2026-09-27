<?php
defined('ABSPATH') || exit;

/**
 * چاپ فاکتور/رسید — لیست خدمات ثبت‌شده یک بیمار از کاتالوگ
 */
class Dental_Page_Invoice_Print {

    public function render(): void {
        $patient_id = (int)($_GET['patient_id'] ?? 0);
        $only_date  = sanitize_text_field($_GET['date'] ?? ''); // اختیاری: فقط یک روز خاص (مثلاً همون ویزیت امروز)
        $patient = get_post($patient_id);
        if (!$patient) { wp_die('بیمار یافت نشد.'); }

        $clinic  = get_option('dental_clinic_name', get_bloginfo('name'));
        $phone   = get_option('dental_clinic_phone', '');
        $address = get_option('dental_clinic_address', '');
        $mobile  = get_post_meta($patient_id, '_patient_mobile', true);

        $all = Dental_Service_Catalog::get_patient_treatments($patient_id);
        if ($only_date) {
            $all = array_filter($all, fn($t) => $t['recorded_date'] === $only_date);
        }
        $total = array_sum(array_column($all, 'price'));
        $invoice_no = 'INV-' . $patient_id . '-' . date('Ymd');
        ?>
        <!DOCTYPE html>
        <html lang="fa" dir="rtl">
        <head>
        <meta charset="UTF-8">
        <title>فاکتور — <?php echo esc_html($patient->post_title); ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700;800&display=swap" rel="stylesheet">
        <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:Vazirmatn,Tahoma,sans-serif; }
        body { direction:rtl; background:#f3f6f8; color:#1A2733; }
        .page { width:210mm; min-height:297mm; margin:20px auto; background:#fff; padding:15mm 18mm; box-shadow:0 0 20px rgba(0,0,0,.08); }
        .header { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:3px solid #1A6B8A; padding-bottom:16px; margin-bottom:20px; }
        .clinic-name { font-size:22px; font-weight:800; color:#1A6B8A; }
        .clinic-meta { font-size:12px; color:#5A7080; margin-top:6px; }
        .invoice-badge { text-align:left; }
        .invoice-badge .label { font-size:11px; color:#A0B4C0; }
        .invoice-badge .no { font-size:15px; font-weight:700; color:#1A2733; direction:ltr; }
        .patient-box { background:#F0F6F9; border-radius:10px; padding:14px 18px; margin-bottom:20px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px; }
        .patient-box div { font-size:13px; }
        .patient-box b { color:#1A6B8A; }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        th { background:#1A6B8A; color:#fff; padding:10px; font-size:12px; text-align:right; }
        td { padding:10px; border-bottom:1px solid #EEF2F5; font-size:12.5px; }
        tr:nth-child(even) td { background:#FAFBFC; }
        .totals { display:flex; justify-content:flex-end; margin-bottom:30px; }
        .totals-box { width:280px; }
        .totals-row { display:flex; justify-content:space-between; padding:8px 14px; font-size:13px; }
        .totals-row.grand { background:#1A6B8A; color:#fff; font-weight:700; border-radius:8px; font-size:15px; }
        .footer { display:flex; justify-content:space-between; margin-top:60px; font-size:12px; color:#5A7080; }
        .sign-box { text-align:center; width:160px; }
        .sign-line { border-top:1px solid #C8D4DC; margin-top:40px; padding-top:6px; }
        .no-print { position:fixed; top:16px; left:16px; }
        .no-print button { background:#1A6B8A; color:#fff; border:none; border-radius:8px; padding:10px 22px; font-family:inherit; font-size:13px; cursor:pointer; }
        @media print { body{background:#fff;} .page{box-shadow:none;margin:0;width:auto;} .no-print{display:none!important;} }
        </style>
        </head>
        <body>
            <div class="no-print"><button onclick="window.print()">🖨️ چاپ فاکتور</button></div>
            <div class="page">
                <div class="header">
                    <div>
                        <div class="clinic-name">🦷 <?php echo esc_html($clinic); ?></div>
                        <div class="clinic-meta"><?php echo esc_html($phone); ?><?php if($address): ?> — <?php echo esc_html($address); ?><?php endif; ?></div>
                    </div>
                    <div class="invoice-badge">
                        <div class="label">شماره فاکتور</div>
                        <div class="no"><?php echo esc_html($invoice_no); ?></div>
                        <div class="label" style="margin-top:6px;">تاریخ صدور</div>
                        <div class="no" style="font-size:13px;"><?php echo esc_html(Dental_Jalali::today('Y/m/d')); ?></div>
                    </div>
                </div>

                <div class="patient-box">
                    <div>نام بیمار: <b><?php echo esc_html($patient->post_title); ?></b></div>
                    <div>کد ملی: <b style="direction:ltr;display:inline-block;"><?php echo esc_html(get_post_meta($patient_id,'_patient_national_id',true) ?: '—'); ?></b></div>
                    <div>موبایل: <b style="direction:ltr;display:inline-block;"><?php echo esc_html($mobile ?: '—'); ?></b></div>
                    <?php if ($only_date): ?>
                    <div>ویزیت: <b><?php echo esc_html(Dental_Jalali::to_jalali($only_date,'Y/m/d')); ?></b></div>
                    <?php endif; ?>
                </div>

                <table>
                    <thead><tr><th>ردیف</th><th>تاریخ</th><th>شرح خدمت</th><th>دندان</th><th>پزشک</th><th>مبلغ (تومان)</th></tr></thead>
                    <tbody>
                    <?php if (empty($all)): ?>
                    <tr><td colspan="6" style="text-align:center;padding:24px;color:#A0B4C0;">خدمتی یافت نشد</td></tr>
                    <?php else: $i=0; foreach($all as $t): $i++; ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td style="direction:ltr;text-align:right;"><?php echo esc_html(Dental_Jalali::to_jalali($t['recorded_date'],'Y/m/d')); ?></td>
                        <td><?php echo esc_html($t['service_name']); ?></td>
                        <td><?php echo esc_html(Dental_Service_Catalog::describe_tooth_number($t['tooth_number']?:null)); ?></td>
                        <td><?php echo esc_html($t['doctor_name']); ?></td>
                        <td style="font-weight:700;"><?php echo number_format($t['price']); ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>

                <div class="totals">
                    <div class="totals-box">
                        <div class="totals-row"><span>تعداد خدمات</span><span><?php echo count($all); ?></span></div>
                        <?php
                        $total_insurance = array_sum(array_column($all,'insurance_share'));
                        $total_patient   = array_sum(array_filter(array_column($all,'patient_share')));
                        if ($total_insurance > 0):
                        ?>
                        <div class="totals-row"><span>سهم بیمه</span><span><?php echo number_format($total_insurance); ?> تومان</span></div>
                        <div class="totals-row"><span>سهم بیمار</span><span><?php echo number_format($total_patient); ?> تومان</span></div>
                        <?php endif; ?>
                        <div class="totals-row grand"><span>جمع کل خدمات</span><span><?php echo number_format($total); ?> تومان</span></div>
                    </div>
                </div>

                <div class="footer">
                    <div class="sign-box"><div class="sign-line">امضای بیمار</div></div>
                    <div class="sign-box"><div class="sign-line">مهر و امضای کلینیک</div></div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}
