<?php
defined('ABSPATH') || exit;

class Dental_Portal_Dashboard {

    private int $patient_id;

    public function __construct(int $patient_id) {
        $this->patient_id = $patient_id;
    }

    public function render(): void {
        global $wpdb;
        $pid = $this->patient_id;

        $wallet  = Dental_Patient_Wallet::get_balance($pid);
        $points  = (int)get_post_meta($pid, '_loyalty_points', true);

        // قسط بعدی
        $next = $wpdb->get_row($wpdb->prepare(
            "SELECT ii.due_date_jalali, ii.amount, ii.status
             FROM {$wpdb->prefix}dental_installment_items ii
             JOIN {$wpdb->prefix}dental_installments i ON ii.installment_id=i.id
             WHERE i.patient_id=%d AND ii.status IN ('pending','overdue')
             ORDER BY ii.due_date ASC LIMIT 1",
            $pid
        ));

        // طرح درمان — تعداد انجام‌شده
        $conditions = $this->get_conditions();
        $done_map   = get_post_meta($pid, '_chart_done_map', true) ?: [];
        $total_tx = $done_tx = 0;
        foreach($conditions as $key => $d) {
            foreach(($d['treatments']??[]) as $code) {
                $total_tx++;
                if (!empty($done_map[$key.'_'.$code]['done'])) $done_tx++;
            }
        }

        // هشدارهای پزشکی
        $diseases  = json_decode(get_post_meta($pid,'_systemic_diseases',true)?:'[]',true)?:[];
        $allergies = get_post_meta($pid,'_drug_allergies',true);
        ?>

        <!-- کارت‌های آماری -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px;">
            <?php
            $pct = $total_tx > 0 ? round($done_tx/$total_tx*100) : 0;
            $cards = [
                ['wallet',          number_format($wallet),             'کیف پول',         'تومان',          '#1A6B8A','#E8F4F8','?ptab=wallet'],
                ['receipt',         $next?number_format($next->amount):'—', 'قسط بعدی', $next?$next->due_date_jalali:'بدون قسط', $next&&$next->status==='overdue'?'#E05252':'#F0A500','#FEF6E4','?ptab=financial'],
                ['activity',        $done_tx.'/'.$total_tx,             'درمان‌ها',         $pct.'٪ انجام شده','#2ECC9A','#E8FAF4','?ptab=record'],
                ['star',            number_format($points),             'امتیاز وفاداری',  'امتیاز',         '#8B5CF6','#F3F0FF','#'],
            ];
            foreach($cards as [$icon,$val,$label,$sub,$color,$bg,$link]):
            ?>
            <a href="<?php echo esc_url($link==='#'?'#':add_query_arg(ltrim($link,'?'),false,get_permalink())); ?>"
               style="text-decoration:none;">
            <div style="background:#fff;border-radius:12px;padding:16px;border:1px solid #EEF2F5;border-top:3px solid <?php echo $color; ?>;transition:box-shadow .2s;">
                <div style="width:36px;height:36px;border-radius:8px;background:<?php echo $bg; ?>;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
                    <i data-lucide="<?php echo esc_attr($icon); ?>" style="width:18px;height:18px;color:<?php echo $color; ?>;"></i>
                </div>
                <div style="font-size:20px;font-weight:700;color:<?php echo $color; ?>;"><?php echo esc_html($val); ?></div>
                <div style="font-size:11px;color:#7A96A4;margin-top:2px;"><?php echo esc_html($sub); ?></div>
                <div style="font-size:12px;color:#3D5460;margin-top:4px;font-weight:500;"><?php echo esc_html($label); ?></div>
            </div>
            </a>
            <?php endforeach; ?>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

            <!-- هشدارهای پزشکی -->
            <?php if(!empty($diseases) || $allergies): ?>
            <div style="background:#fff;border-radius:12px;border:1px solid #EEF2F5;overflow:hidden;">
                <div style="padding:12px 16px;border-bottom:1px solid #EEF2F5;display:flex;align-items:center;gap:8px;">
                    <i data-lucide="shield-alert" style="width:16px;height:16px;color:#E05252;"></i>
                    <span style="font-size:13px;font-weight:600;color:#1A2733;">هشدارهای پزشکی</span>
                </div>
                <div style="padding:12px 16px;display:flex;flex-direction:column;gap:8px;">
                    <?php if($allergies): ?>
                    <div style="background:#FDEAEA;border:1px solid #E05252;border-radius:8px;padding:8px 12px;font-size:12px;color:#E05252;">
                        <strong>آلرژی:</strong> <?php echo esc_html($allergies); ?>
                    </div>
                    <?php endif; ?>
                    <?php
                    $disease_list = class_exists('Dental_Medical_History') ? Dental_Medical_History::get_disease_list() : [];
                    foreach($diseases as $dk):
                        if(!isset($disease_list[$dk])) continue;
                        $d = $disease_list[$dk];
                    ?>
                    <div style="background:#FEF6E4;border:1px solid #F0A500;border-radius:8px;padding:8px 12px;font-size:12px;color:#7a5200;">
                        <strong><?php echo esc_html($d['label']); ?></strong>
                        <?php if($d['alert']): ?><span style="opacity:.8;"> — <?php echo esc_html($d['alert']); ?></span><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- آخرین فعالیت‌ها -->
            <div style="background:#fff;border-radius:12px;border:1px solid #EEF2F5;overflow:hidden;">
                <div style="padding:12px 16px;border-bottom:1px solid #EEF2F5;display:flex;align-items:center;gap:8px;">
                    <i data-lucide="activity" style="width:16px;height:16px;color:#1A6B8A;"></i>
                    <span style="font-size:13px;font-weight:600;color:#1A2733;">آخرین فعالیت‌ها</span>
                </div>
                <div style="padding:0 16px;">
                    <?php
                    $txs = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}dental_wallet_transactions WHERE patient_id=%d ORDER BY created_at DESC LIMIT 5",
                        $pid
                    ), ARRAY_A);
                    if(empty($txs)):
                    ?>
                    <p style="padding:20px 0;text-align:center;color:#A0B4C0;font-size:13px;">فعالیتی ثبت نشده</p>
                    <?php else: foreach($txs as $tx):
                        $cr = $tx['transaction_type']==='credit';
                        $dt = Dental_Jalali::to_jalali($tx['created_at'],'Y/m/d');
                    ?>
                    <div style="display:flex;align-items:center;padding:10px 0;border-bottom:1px solid #F5F5F5;gap:10px;font-size:12px;">
                        <div style="width:8px;height:8px;border-radius:50%;background:<?php echo $cr?'#2ECC9A':'#E05252'; ?>;flex-shrink:0;"></div>
                        <span style="flex:1;color:#3D5460;"><?php echo esc_html($tx['description']?:$tx['source']); ?></span>
                        <span style="font-weight:700;color:<?php echo $cr?'#2ECC9A':'#E05252'; ?>;"><?php echo $cr?'+':'-'; ?><?php echo number_format($tx['amount']); ?> ت</span>
                        <span style="color:#A0B4C0;"><?php echo esc_html($dt); ?></span>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_conditions(): array {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT tooth_number, tooth_type, tooth_surface FROM {$wpdb->prefix}dental_tooth_conditions WHERE patient_id=%d AND is_active=1",
            $this->patient_id
        ), ARRAY_A);
        $r = [];
        foreach($rows as $row) {
            $key = $row['tooth_number'].'_'.$row['tooth_type'];
            $r[$key] = ['treatments' => json_decode($row['tooth_surface']?:'[]',true)?:[]];
        }
        return $r;
    }
}
