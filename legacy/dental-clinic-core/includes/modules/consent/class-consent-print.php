<?php
defined('ABSPATH') || exit;

class Dental_Consent_Print {

    public static function render(int $patient_id, string $consent_key): void {
        $patient  = get_post($patient_id);
        $f        = fn($k) => get_post_meta($patient_id, $k, true);
        $clinic   = get_option('dental_clinic_name', get_bloginfo('name'));
        $phone    = get_option('dental_clinic_phone', '');
        $address  = get_option('dental_clinic_address', '');

        $data     = get_post_meta($patient_id, '_consent_signed_' . $consent_key, true);
        if (!$data) wp_die('رضایت‌نامه یافت نشد.');

        $parts    = explode('_', $consent_key);
        $code     = end($parts);
        $tx_name  = Dental_Consent_Form::get_consent_required_treatments()[$code] ?? $code;

        $q_names  = [1=>'بالا راست',2=>'بالا چپ',3=>'پایین چپ',4=>'پایین راست'];
        $q_parts  = explode('_', $consent_key);
        $tooth_no = (int)($q_parts[0] ?? 0);
        $q        = (int)($tooth_no / 10);
        $n        = $tooth_no % 10;
        $tooth_info = $tooth_no ? 'دندان ' . $n . ' — ' . ($q_names[$q] ?? '') : '';

        $template = Dental_Consent_Form::get_template($code);
        $text = str_replace(
            ['{patient_name}','{tooth_info}','{treatment_name}','{doctor_name}','{date}'],
            [$patient->post_title??'', $tooth_info, $tx_name, $data['doctor_name']??'', $data['signed_jalali']??''],
            $template
        );

        $sig_url = !empty($data['attach_id']) ? wp_get_attachment_url($data['attach_id']) : '';
        // ─── شماره‌ی سند — برای پیگیری/آرشیو رسمی‌تر ────────────────
        $doc_ref = 'RC-' . $patient_id . '-' . substr(md5($consent_key), 0, 6);
        $national_id = get_post_meta($patient_id, '_patient_national_id', true);
        ?>
        <!DOCTYPE html>
        <html lang="fa" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>رضایت‌نامه — <?php echo esc_html($patient->post_title??''); ?></title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
            <style>
            * { margin:0;padding:0;box-sizing:border-box; }
            body {
                font-family:'Vazirmatn',Tahoma,sans-serif;
                direction:rtl;font-size:13.5px;color:#1A2733;background:#EEF2F5;
            }
            .page {
                width:210mm;min-height:297mm;padding:16mm 18mm;margin:14px auto;
                background:#fff;position:relative;
                box-shadow:0 2px 16px rgba(15,42,56,.08);
            }
            .top-bar { height:6px;background:linear-gradient(90deg,#1A6B8A,#0F4D66);border-radius:3px 3px 0 0;margin:-16mm -18mm 20px; }
            .header { display:flex;align-items:flex-start;justify-content:space-between;padding-bottom:16px;border-bottom:2px solid #EEF2F5;margin-bottom:22px; }
            .clinic-name { font-size:21px;font-weight:800;color:#1A6B8A;letter-spacing:-.3px; }
            .clinic-meta { font-size:11px;color:#7A96A4;margin-top:5px;line-height:1.8; }
            .doc-ref { text-align:left;font-size:10.5px;color:#A0B4C0;line-height:1.9; }
            .doc-ref b { color:#5A7080;font-weight:600; }
            .doc-title-wrap { text-align:center;margin-bottom:22px; }
            .doc-title { font-size:19px;font-weight:800;color:#1A2733;display:inline-block;position:relative;padding-bottom:8px; }
            .doc-title:after { content:'';position:absolute;bottom:0;right:50%;transform:translateX(50%);width:60px;height:3px;background:#1A6B8A;border-radius:2px; }
            .patient-box {
                display:flex;gap:28px;flex-wrap:wrap;background:#F8FAFB;
                border:1px solid #E5ECF0;border-radius:10px;padding:16px 18px;margin-bottom:22px;
            }
            .pf { font-size:12px;min-width:90px; }
            .pf-label { color:#8CA0AB;margin-bottom:4px;font-size:10.5px;font-weight:500; }
            .pf-val { font-weight:700;font-size:14px;color:#1A2733; }
            .consent-text {
                font-size:13.5px;line-height:2.3;border:1px solid #E5ECF0;border-radius:10px;
                padding:20px 22px;margin-bottom:26px;background:#FDFDFD;white-space:pre-line;
                text-align:justify;
            }
            .sig-section { display:flex;justify-content:space-between;align-items:flex-end;margin-top:32px;gap:24px; }
            .sig-box { flex:1;text-align:center; }
            .sig-label { font-size:12px;color:#5A7080;margin-bottom:10px;font-weight:600; }
            .sig-img { max-width:200px;max-height:80px;border-bottom:1.5px solid #1A2733;padding-bottom:8px; }
            .sig-line { width:200px;border-bottom:1.5px solid #1A2733;margin:0 auto 8px;height:36px; }
            .sig-name { font-size:11.5px;color:#7A96A4;margin-top:6px; }
            .stamp-box {
                width:96px;height:96px;border:2px dashed #C8D4DC;border-radius:50%;
                display:flex;align-items:center;justify-content:center;font-size:11px;color:#C8D4DC;margin:0 auto;
            }
            .legal-note {
                margin-top:26px;padding:12px 16px;background:#FBF8F0;border-radius:8px;
                font-size:10.5px;color:#8A7548;line-height:1.9;border-right:3px solid #E0C989;
            }
            .footer {
                margin-top:24px;padding-top:12px;border-top:1px solid #EEF2F5;
                font-size:10.5px;color:#A0B4C0;display:flex;justify-content:space-between;
            }
            @media print {
                body { background:#fff; }
                .page { box-shadow:none;margin:0;width:auto;min-height:auto; }
                .no-print { display:none!important; }
            }
            @page { size:A4; margin:0; }
            </style>
        </head>
        <body>
            <div class="no-print" style="position:fixed;top:16px;left:16px;z-index:9999;display:flex;gap:8px;">
                <button onclick="window.print()" style="background:#1A6B8A;color:#fff;border:none;border-radius:8px;padding:10px 20px;font-family:Vazirmatn,Tahoma;font-size:14px;cursor:pointer;">🖨️ پرینت</button>
                <button onclick="window.close()" style="background:#F0F4F6;color:#5A7080;border:1px solid #C8D4DC;border-radius:8px;padding:10px 20px;font-family:Vazirmatn,Tahoma;font-size:14px;cursor:pointer;">بستن</button>
            </div>

            <div class="page">
                <div class="top-bar"></div>
                <div class="header">
                    <div>
                        <div class="clinic-name">🦷 <?php echo esc_html($clinic); ?></div>
                        <div class="clinic-meta">
                            <?php if($phone): ?>📞 <?php echo esc_html($phone); ?><?php endif; ?>
                            <?php if($address): ?><br>📍 <?php echo esc_html($address); ?><?php endif; ?>
                        </div>
                    </div>
                    <div class="doc-ref">
                        <div>شماره سند: <b><?php echo esc_html($doc_ref); ?></b></div>
                        <div>تاریخ چاپ: <b><?php echo esc_html(Dental_Jalali::today()); ?></b></div>
                    </div>
                </div>

                <div class="doc-title-wrap">
                    <div class="doc-title">فرم رضایت‌نامه‌ی آگاهانه — <?php echo esc_html($tx_name); ?></div>
                </div>

                <div class="patient-box">
                    <div class="pf"><div class="pf-label">نام بیمار</div><div class="pf-val"><?php echo esc_html($patient->post_title??''); ?></div></div>
                    <?php if($national_id): ?>
                    <div class="pf"><div class="pf-label">کد ملی</div><div class="pf-val" style="direction:ltr;display:inline-block;"><?php echo esc_html($national_id); ?></div></div>
                    <?php endif; ?>
                    <?php if($f('_patient_mobile')): ?>
                    <div class="pf"><div class="pf-label">موبایل</div><div class="pf-val" style="direction:ltr;display:inline-block;"><?php echo esc_html($f('_patient_mobile')); ?></div></div>
                    <?php endif; ?>
                    <?php if($tooth_info): ?>
                    <div class="pf"><div class="pf-label">ناحیه درمان</div><div class="pf-val"><?php echo esc_html($tooth_info); ?></div></div>
                    <?php endif; ?>
                    <div class="pf"><div class="pf-label">پزشک معالج</div><div class="pf-val"><?php echo esc_html($data['doctor_name']??''); ?></div></div>
                    <div class="pf"><div class="pf-label">تاریخ امضا</div><div class="pf-val"><?php echo esc_html($data['signed_jalali']??''); ?></div></div>
                </div>

                <div class="consent-text"><?php echo nl2br(esc_html($text)); ?></div>

                <div class="sig-section">
                    <div class="sig-box">
                        <div class="sig-label">امضای بیمار / سرپرست قانونی</div>
                        <?php if($sig_url): ?>
                        <img src="<?php echo esc_url($sig_url); ?>" class="sig-img" alt="امضا">
                        <?php else: ?>
                        <div class="sig-line"></div>
                        <?php endif; ?>
                        <div class="sig-name"><?php echo esc_html($patient->post_title??''); ?></div>
                    </div>
                    <div class="sig-box">
                        <div class="sig-label">امضای پزشک معالج</div>
                        <div class="sig-line"></div>
                        <div class="sig-name"><?php echo esc_html($data['doctor_name']??''); ?></div>
                    </div>
                    <div style="flex:0 0 auto;">
                        <div class="sig-label">مهر کلینیک</div>
                        <div class="stamp-box">محل مهر</div>
                    </div>
                </div>

                <div class="legal-note">
                    ⚠️ این سند تأییدی است بر اینکه توضیحات مربوط به روند درمان، عوارض احتمالی، و گزینه‌های جایگزین به‌طور کامل و شفاف برای بیمار (یا سرپرست قانونی وی) بیان شده و ایشان با رضایت آگاهانه، ادامه‌ی درمان را تأیید کرده‌اند. این فرم بخشی از پرونده‌ی پزشکی رسمی بیمار محسوب می‌شود.
                </div>

                <div class="footer">
                    <div><?php echo esc_html($clinic); ?><?php if($phone): ?> — <?php echo esc_html($phone); ?><?php endif; ?></div>
                    <div>IP ثبت‌کننده: <?php echo esc_html($data['ip']??'—'); ?></div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}
