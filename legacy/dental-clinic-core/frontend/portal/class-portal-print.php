<?php
defined('ABSPATH') || exit;

class Dental_Portal_Print {

    private int $patient_id;

    public function __construct(int $patient_id) {
        $this->patient_id = $patient_id;
    }

    public function render(): void {
        $pid      = $this->patient_id;
        $patient  = get_post($pid);
        $f        = fn($k) => get_post_meta($pid,$k,true);
        $clinic   = get_option('dental_clinic_name', get_bloginfo('name'));
        $phone    = get_option('dental_clinic_phone','');
        $today    = Dental_Jalali::today('Y/m/d');

        global $wpdb;
        $conditions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_tooth_conditions WHERE patient_id=%d AND is_active=1",
            $pid
        ), ARRAY_A);
        $done_map  = get_post_meta($pid,'_chart_done_map',true)?:[];
        $diseases  = json_decode($f('_systemic_diseases')?:'[]',true)?:[];
        $allergies = $f('_drug_allergies');
        $d_list    = class_exists('Dental_Medical_History') ? Dental_Medical_History::get_disease_list() : [];

        $LABELS = $this->tx_labels();
        $qNames = [1=>'بالا راست',2=>'بالا چپ',3=>'پایین چپ',4=>'پایین راست',
                   5=>'شیری بالا راست',6=>'شیری بالا چپ',7=>'شیری پایین چپ',8=>'شیری پایین راست'];

        $plan_rows = [];
        foreach($conditions as $row) {
            $tx  = json_decode($row['tooth_surface']?:'[]',true)?:[];
            if(empty($tx)) continue;
            $key = $row['tooth_number'].'_'.$row['tooth_type'];
            $q   = (int)($row['tooth_number']/10);
            $n   = $row['tooth_number']%10;
            $tl  = $row['tooth_type']==='primary'?' (شیری)':'';
            foreach($tx as $code) {
                $dk   = $key.'_'.$code;
                $done = !empty($done_map[$dk]['done']);
                $plan_rows[] = [
                    'title'  => ($LABELS[$code]??$code).' — دندان '.$n.' ('.($qNames[$q]??$q).$tl.')',
                    'done'   => $done,
                    'doctor' => $done_map[$dk]['doctor_name']??'',
                    'date'   => isset($done_map[$dk]['done_at']) ? Dental_Jalali::to_jalali($done_map[$dk]['done_at'],'Y/m/d') : '',
                ];
            }
        }

        // اقساط
        $next_due = $wpdb->get_var($wpdb->prepare(
            "SELECT ii.due_date_jalali FROM {$wpdb->prefix}dental_installment_items ii
             JOIN {$wpdb->prefix}dental_installments i ON ii.installment_id=i.id
             WHERE i.patient_id=%d AND ii.status IN ('pending','overdue')
             ORDER BY ii.due_date ASC LIMIT 1", $pid
        ));

        $total_debt = (float)$wpdb->get_var($wpdb->prepare(
            "SELECT SUM(ii.amount-ii.paid_amount) FROM {$wpdb->prefix}dental_installment_items ii
             JOIN {$wpdb->prefix}dental_installments i ON ii.installment_id=i.id
             WHERE i.patient_id=%d AND ii.status IN ('pending','overdue')", $pid
        ));
        ?>
        <!DOCTYPE html>
        <html lang="fa" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>خلاصه پرونده — <?php echo esc_html($patient->post_title??''); ?></title>
            <style>
            * { margin:0;padding:0;box-sizing:border-box; }
            body { font-family:Tahoma,Arial,sans-serif;direction:rtl;font-size:13px;color:#1A2733;background:#fff;padding:0; }
            .page { width:210mm;min-height:297mm;padding:15mm 20mm;margin:0 auto; }
            .header { display:flex;align-items:center;justify-content:space-between;padding-bottom:12px;border-bottom:2px solid #1A6B8A;margin-bottom:20px; }
            .clinic-name { font-size:20px;font-weight:700;color:#1A6B8A; }
            .clinic-info { font-size:11px;color:#5A7080;margin-top:4px; }
            .print-date { font-size:11px;color:#7A96A4; }
            .patient-box { background:#F8FAFB;border:1px solid #EEF2F5;border-radius:8px;padding:14px;margin-bottom:20px;display:flex;gap:40px;flex-wrap:wrap; }
            .patient-field { font-size:12px; }
            .patient-label { color:#7A96A4;margin-bottom:3px; }
            .patient-value { font-weight:600;font-size:14px; }
            .section { margin-bottom:20px; }
            .section-title { font-size:14px;font-weight:700;color:#1A6B8A;border-bottom:1px solid #EEF2F5;padding-bottom:6px;margin-bottom:12px;display:flex;align-items:center;gap:6px; }
            .plan-row { display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px dashed #EEF2F5;font-size:12px; }
            .check { width:14px;height:14px;border:1.5px solid #C8D4DC;border-radius:3px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0; }
            .check.done { border-color:#2ECC9A;background:#E8FAF4;color:#2ECC9A;font-size:9px; }
            .done-text { color:#A0B4C0;text-decoration:line-through; }
            .alert-box { border-radius:6px;padding:8px 12px;font-size:12px;margin-bottom:6px; }
            .alert-danger { background:#FDEAEA;border:1px solid #E05252;color:#E05252; }
            .alert-warning { background:#FEF6E4;border:1px solid #F0A500;color:#7a5200; }
            .fin-row { display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed #EEF2F5;font-size:12px; }
            .footer { margin-top:30px;padding-top:14px;border-top:1px solid #EEF2F5;display:flex;justify-content:space-between;font-size:11px;color:#A0B4C0; }
            .signature-box { border-top:1px solid #1A2733;width:150px;text-align:center;padding-top:6px;font-size:11px;color:#5A7080; }
            @media print {
                body { print-color-adjust:exact;-webkit-print-color-adjust:exact; }
                .no-print { display:none!important; }
                .page { padding:10mm 15mm; }
            }
            </style>
        </head>
        <body>
            <!-- دکمه پرینت -->
            <div class="no-print" style="position:fixed;top:16px;left:16px;z-index:9999;display:flex;gap:8px;">
                <button onclick="window.print()"
                    style="background:#1A6B8A;color:#fff;border:none;border-radius:8px;padding:10px 20px;font-family:Tahoma;font-size:14px;cursor:pointer;">
                    🖨️ پرینت
                </button>
                <button onclick="window.close()"
                    style="background:#F0F4F6;color:#5A7080;border:1px solid #C8D4DC;border-radius:8px;padding:10px 20px;font-family:Tahoma;font-size:14px;cursor:pointer;">
                    بستن
                </button>
            </div>

            <div class="page">
                <!-- هدر -->
                <div class="header">
                    <div>
                        <div class="clinic-name">🦷 <?php echo esc_html($clinic); ?></div>
                        <?php if($phone): ?><div class="clinic-info">📞 <?php echo esc_html($phone); ?></div><?php endif; ?>
                    </div>
                    <div style="text-align:left;">
                        <div style="font-size:13px;font-weight:700;color:#1A2733;">خلاصه پرونده دندانپزشکی</div>
                        <div class="print-date">تاریخ چاپ: <?php echo esc_html($today); ?></div>
                    </div>
                </div>

                <!-- اطلاعات کاربر -->
                <div class="patient-box">
                    <div class="patient-field">
                        <div class="patient-label">نام کاربر</div>
                        <div class="patient-value"><?php echo esc_html($patient->post_title??''); ?></div>
                    </div>
                    <?php if($f('_patient_mobile')): ?>
                    <div class="patient-field">
                        <div class="patient-label">موبایل</div>
                        <div class="patient-value" style="direction:ltr;"><?php echo esc_html($f('_patient_mobile')); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if($f('_patient_dob_jalali')): ?>
                    <div class="patient-field">
                        <div class="patient-label">تاریخ تولد</div>
                        <div class="patient-value"><?php echo esc_html($f('_patient_dob_jalali')); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if($f('_patient_gender')): ?>
                    <div class="patient-field">
                        <div class="patient-label">جنسیت</div>
                        <div class="patient-value"><?php echo $f('_patient_gender')==='male'?'مرد':'زن'; ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- هشدارهای پزشکی -->
                <?php if(!empty($diseases) || $allergies): ?>
                <div class="section">
                    <div class="section-title">⚠️ هشدارهای پزشکی</div>
                    <?php if($allergies): ?>
                    <div class="alert-box alert-danger"><strong>آلرژی به دارو:</strong> <?php echo esc_html($allergies); ?></div>
                    <?php endif; ?>
                    <?php foreach($diseases as $dk):
                        if(!isset($d_list[$dk])) continue;
                        $d = $d_list[$dk];
                    ?>
                    <div class="alert-box alert-warning">
                        <strong><?php echo esc_html($d['label']); ?></strong>
                        <?php if($d['alert']): ?> — <?php echo esc_html($d['alert']); ?><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- طرح درمان -->
                <?php if(!empty($plan_rows)): ?>
                <div class="section">
                    <?php
                    $done_c  = count(array_filter($plan_rows,fn($r)=>$r['done']));
                    $total_c = count($plan_rows);
                    $pct_p   = $total_c>0?round($done_c/$total_c*100):0;
                    ?>
                    <div class="section-title">
                        📋 طرح درمان
                        <span style="font-size:11px;font-weight:400;color:#7A96A4;margin-right:auto;"><?php echo $done_c; ?>/<?php echo $total_c; ?> انجام شده (<?php echo $pct_p; ?>٪)</span>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 20px;">
                        <?php foreach($plan_rows as $row): ?>
                        <div class="plan-row">
                            <span class="check <?php echo $row['done']?'done':''; ?>"><?php echo $row['done']?'✓':''; ?></span>
                            <span class="<?php echo $row['done']?'done-text':''; ?>"><?php echo esc_html($row['title']); ?></span>
                            <?php if($row['done'] && $row['doctor']): ?>
                            <span style="margin-right:auto;font-size:10px;color:#A0B4C0;"><?php echo esc_html($row['doctor']); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- وضعیت مالی -->
                <div class="section">
                    <div class="section-title">💰 وضعیت مالی</div>
                    <?php if($next_due): ?>
                    <div class="fin-row">
                        <span>تاریخ قسط بعدی</span>
                        <span style="font-weight:700;color:#F0A500;"><?php echo esc_html($next_due); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if($total_debt > 0): ?>
                    <div class="fin-row">
                        <span>جمع بدهی باقیمانده</span>
                        <span style="font-weight:700;color:#E05252;"><?php echo number_format($total_debt); ?> تومان</span>
                    </div>
                    <?php else: ?>
                    <div class="fin-row">
                        <span>وضعیت مالی</span>
                        <span style="color:#2ECC9A;font-weight:700;">✅ تسویه شده</span>
                    </div>
                    <?php endif; ?>
                    <div class="fin-row">
                        <span>موجودی کیف پول</span>
                        <span style="font-weight:700;"><?php echo number_format(Dental_Patient_Wallet::get_balance($pid)); ?> تومان</span>
                    </div>
                </div>

                <!-- فوتر و امضا -->
                <div class="footer">
                    <div>
                        <div><?php echo esc_html($clinic); ?></div>
                        <?php if($phone): ?><div><?php echo esc_html($phone); ?></div><?php endif; ?>
                    </div>
                    <div class="signature-box">
                        <div style="height:30px;"></div>
                        مهر و امضای پزشک
                    </div>
                </div>
            </div>

            <script>
            // پرینت خودکار بعد از لود
            window.addEventListener("load", function(){
                // کمی صبر کن تا فونت لود بشه
                setTimeout(function(){
                    // window.print();
                }, 500);
            });
            </script>
        </body>
        </html>
        <?php
    }

    private function tx_labels(): array {
        return [
            'composite'=>'ترمیم کامپوزیت','amalgam'=>'ترمیم آمالگام','rct'=>'عصب‌کشی',
            'pulpotomy'=>'پالپوتومی','pulpectomy'=>'پالپکتومی','buildup'=>'بیلدآپ',
            'crown'=>'روکش','veneer'=>'لامینیت','inlay_onlay'=>'انله/آنله',
            'extraction'=>'کشیدن ساده','surgical_ext'=>'کشیدن جراحی','implant'=>'ایمپلنت',
            'apicoectomy'=>'آپیکوستومی','bridge_abutment'=>'بریج','retainer_fix'=>'ریتینر',
            'xray_pa'=>'عکس PA','cbct'=>'CBCT','consult_perio'=>'مشاوره پریو',
            'consult_endo'=>'مشاوره اندو','consult_surgeon'=>'مشاوره جراح',
            'consult_prosth'=>'مشاوره پروتز','consult_resto'=>'مشاوره ترمیم','consult_peds'=>'مشاوره اطفال',
        ];
    }
}
