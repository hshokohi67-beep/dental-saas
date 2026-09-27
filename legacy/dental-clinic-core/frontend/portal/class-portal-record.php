<?php
defined('ABSPATH') || exit;

class Dental_Portal_Record {

    private int $patient_id;

    public function __construct(int $patient_id) {
        $this->patient_id = $patient_id;
    }

    public function render(): void {
        global $wpdb;
        $pid = $this->patient_id;

        $conditions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_tooth_conditions WHERE patient_id=%d AND is_active=1",
            $pid
        ), ARRAY_A);

        $done_map = get_post_meta($pid,'_chart_done_map',true)?:[];
        $diseases  = json_decode(get_post_meta($pid,'_systemic_diseases',true)?:'[]',true)?:[];
        $allergies = get_post_meta($pid,'_drug_allergies',true);
        $d_list    = class_exists('Dental_Medical_History') ? Dental_Medical_History::get_disease_list() : [];

        $LABELS = $this->tx_labels();
        $qNames = [1=>'بالا راست',2=>'بالا چپ',3=>'پایین چپ',4=>'پایین راست',
                   5=>'شیری بالا راست',6=>'شیری بالا چپ',7=>'شیری پایین چپ',8=>'شیری پایین راست'];

        // ساخت طرح درمان
        $plan_rows = [];
        foreach($conditions as $row) {
            $tx   = json_decode($row['tooth_surface']?:'[]',true)?:[];
            if(empty($tx)) continue;
            $key  = $row['tooth_number'].'_'.$row['tooth_type'];
            $q    = (int)($row['tooth_number']/10);
            $n    = $row['tooth_number']%10;
            $tl   = $row['tooth_type']==='primary'?' (شیری)':'';
            foreach($tx as $code) {
                $dk   = $key.'_'.$code;
                $done = !empty($done_map[$dk]['done']);
                $plan_rows[] = [
                    'title' => ($LABELS[$code]??$code).' — دندان '.$n.' ('.(($qNames[$q]??$q).$tl).')',
                    'done'  => $done,
                    'doctor'=> $done_map[$dk]['doctor_name']??'',
                    'date'  => isset($done_map[$dk]['done_at']) ? Dental_Jalali::to_jalali($done_map[$dk]['done_at'],'Y/m/d') : '',
                ];
            }
        }

        $done_count  = count(array_filter($plan_rows,fn($r)=>$r['done']));
        $total_count = count($plan_rows);
        $pct = $total_count>0 ? round($done_count/$total_count*100) : 0;
        ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">

            <!-- طرح درمان -->
            <div style="background:#fff;border-radius:12px;border:1px solid #EEF2F5;overflow:hidden;">
                <div style="padding:14px 16px;border-bottom:1px solid #EEF2F5;display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i data-lucide="clipboard-list" style="width:16px;height:16px;color:#1A6B8A;"></i>
                        <span style="font-size:13px;font-weight:700;color:#1A2733;">طرح درمان</span>
                    </div>
                    <span style="font-size:12px;color:#7A96A4;"><?php echo $done_count; ?>/<?php echo $total_count; ?> انجام شده</span>
                </div>
                <div style="padding:14px 16px;">
                    <!-- Progress -->
                    <div style="height:6px;background:#EEF2F5;border-radius:3px;overflow:hidden;margin-bottom:14px;">
                        <div style="height:100%;width:<?php echo $pct; ?>%;background:linear-gradient(90deg,#2ECC9A,#1A6B8A);border-radius:3px;"></div>
                    </div>
                    <?php if(empty($plan_rows)): ?>
                    <p style="text-align:center;color:#A0B4C0;font-size:13px;padding:16px 0;">طرح درمانی ثبت نشده</p>
                    <?php else: foreach($plan_rows as $row): ?>
                    <div style="display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid #F5F5F5;">
                        <i data-lucide="<?php echo $row['done']?'check-circle-2':'circle'; ?>"
                           style="width:14px;height:14px;color:<?php echo $row['done']?'#2ECC9A':'#C8D4DC'; ?>;flex-shrink:0;"></i>
                        <span style="font-size:12px;flex:1;color:<?php echo $row['done']?'#A0B4C0':'#3D5460'; ?>;<?php echo $row['done']?'text-decoration:line-through;':'font-weight:500;'; ?>">
                            <?php echo esc_html($row['title']); ?>
                        </span>
                        <?php if($row['done'] && ($row['doctor']||$row['date'])): ?>
                        <span style="font-size:10px;color:#A0B4C0;"><?php echo esc_html($row['doctor']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- سوابق پزشکی -->
            <div style="background:#fff;border-radius:12px;border:1px solid #EEF2F5;overflow:hidden;">
                <div style="padding:14px 16px;border-bottom:1px solid #EEF2F5;display:flex;align-items:center;gap:8px;">
                    <i data-lucide="heart-pulse" style="width:16px;height:16px;color:#E05252;"></i>
                    <span style="font-size:13px;font-weight:700;color:#1A2733;">سوابق پزشکی</span>
                </div>
                <div style="padding:14px 16px;display:flex;flex-direction:column;gap:8px;">
                    <?php if($allergies): ?>
                    <div style="background:#FDEAEA;border:1px solid #E05252;border-radius:8px;padding:10px 12px;">
                        <div style="font-size:11px;color:#E05252;font-weight:700;margin-bottom:3px;">⚠️ آلرژی به دارو</div>
                        <div style="font-size:13px;color:#1A2733;"><?php echo esc_html($allergies); ?></div>
                    </div>
                    <?php endif; ?>
                    <?php foreach($diseases as $dk):
                        if(!isset($d_list[$dk])) continue;
                        $d = $d_list[$dk];
                    ?>
                    <div style="background:#FEF6E4;border:1px solid #F0A500;border-radius:8px;padding:10px 12px;">
                        <div style="font-size:13px;font-weight:600;color:#7a5200;"><?php echo esc_html($d['label']); ?></div>
                        <?php if($d['alert']): ?>
                        <div style="font-size:11px;color:#7a5200;opacity:.8;margin-top:2px;"><?php echo esc_html($d['alert']); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if(!$allergies && empty($diseases)): ?>
                    <p style="text-align:center;color:#A0B4C0;font-size:13px;padding:12px 0;">موردی ثبت نشده</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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
