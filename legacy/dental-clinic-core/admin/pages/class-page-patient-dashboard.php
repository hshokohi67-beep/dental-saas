<?php
defined('ABSPATH') || exit;

class Dental_Page_Patient_Dashboard {

    private int $patient_id;

    public function __construct(int $patient_id) {
        $this->patient_id = $patient_id;
    }

    public function render(): void {
        $data = $this->collect_data();
        ?>
        <div style="direction:rtl;font-family:var(--dc-font-family);">

        <?php $this->render_stats($data); ?>

        <div class="dc-grid dc-grid-2" style="gap:16px;margin-top:16px;">
            <?php $this->render_treatment_plan($data); ?>
            <?php $this->render_installments($data); ?>
            <?php $this->render_medical_alerts($data); ?>
            <?php $this->render_lab_orders($data); ?>
        </div>

        <?php $this->render_timeline($data); ?>

        </div>
        <?php
    }

    private function collect_data(): array {
        global $wpdb;
        $pid = $this->patient_id;

        $wallet = (float)get_post_meta($pid, '_wallet_balance', true);
        $points = (int)get_post_meta($pid, '_loyalty_points', true);

        // شرایط دندانی
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT tooth_surface, notes, recorded_by, tooth_number, tooth_type FROM {$wpdb->prefix}dental_tooth_conditions WHERE patient_id=%d AND is_active=1",
            $pid
        ), ARRAY_A);

        $done_map  = get_post_meta($pid, '_chart_done_map', true) ?: [];
        $total_tx  = 0;
        $done_tx   = 0;
        $plan_rows = [];
        $LABELS    = $this->tx_labels();
        $qNames    = $this->q_names();

        foreach ($rows as $row) {
            $tx   = json_decode($row['tooth_surface'] ?: '[]', true) ?: [];
            $key  = $row['tooth_number'] . '_' . $row['tooth_type'];
            $fdi  = (int)$row['tooth_number'];
            $q    = (int)($fdi / 10);
            $n    = $fdi % 10;
            $tl   = $row['tooth_type'] === 'primary' ? ' (شیری)' : '';
            foreach ($tx as $code) {
                $total_tx++;
                $dk   = $key . '_' . $code;
                $done = !empty($done_map[$dk]['done']);
                if ($done) $done_tx++;
                $plan_rows[] = [
                    'label'  => ($LABELS[$code] ?? $code) . ' — دندان ' . $n . ' (' . ($qNames[$q] ?? '') . $tl . ')',
                    'done'   => $done,
                    'doctor' => $done_map[$dk]['doctor_name'] ?? '',
                ];
            }
        }

        // اقساط
        $next_due      = null;
        $overdue_count = 0;
        $inst_data     = [];
        $inst_rows     = $wpdb->get_results($wpdb->prepare(
            "SELECT i.*,
                (SELECT COUNT(*) FROM {$wpdb->prefix}dental_installment_items ii WHERE ii.installment_id=i.id AND ii.status='paid') as paid_count,
                (SELECT COUNT(*) FROM {$wpdb->prefix}dental_installment_items ii WHERE ii.installment_id=i.id) as total_count
             FROM {$wpdb->prefix}dental_installments i WHERE i.patient_id=%d ORDER BY i.created_at DESC LIMIT 1",
            $pid
        ), ARRAY_A);

        if (!empty($inst_rows)) {
            $plan  = $inst_rows[0];
            $items = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dental_installment_items WHERE installment_id=%d ORDER BY item_number ASC",
                $plan['id']
            ), ARRAY_A);
            foreach ($items as $it) {
                if ($it['status'] === 'overdue') $overdue_count++;
                if (!$next_due && $it['status'] === 'pending') $next_due = $it['due_date_jalali'];
            }
            $inst_data = ['plan' => $plan, 'items' => $items];
        }

        // هشدارهای پزشکی
        $diseases    = json_decode(get_post_meta($pid, '_systemic_diseases', true) ?: '[]', true) ?: [];
        $allergies   = get_post_meta($pid, '_drug_allergies', true);
        $disease_map = class_exists('Dental_Medical_History') ? Dental_Medical_History::get_disease_list() : [];
        $alerts      = [];
        foreach ($diseases as $dk) {
            if (isset($disease_map[$dk])) {
                $alerts[] = ['label' => $disease_map[$dk]['label'], 'alert' => $disease_map[$dk]['alert'], 'type' => 'warning'];
            }
        }
        if ($allergies) {
            $alerts[] = ['label' => 'آلرژی: ' . $allergies, 'alert' => '', 'type' => 'danger'];
        }

        // سفارشات لب
        $lab_orders = [];
        if (class_exists('Dental_CPT_Lab_Order')) {
            $sl_map = Dental_CPT_Lab_Order::get_statuses();
            foreach (array_slice(Dental_CPT_Lab_Order::get_by_patient($pid), 0, 3) as $o) {
                $st = get_post_meta($o->ID, '_lab_status', true);
                $lab_orders[] = [
                    'title'    => $o->post_title,
                    'lab'      => get_post_meta($o->ID, '_lab_name', true),
                    'status'   => $st,
                    's_label'  => $sl_map[$st] ?? '',
                    'delivery' => get_post_meta($o->ID, '_lab_delivery_jalali', true),
                ];
            }
        }

        // تایم‌لاین
        $timeline = $this->build_timeline($pid, $done_map);

        return compact('wallet','points','total_tx','done_tx','plan_rows',
            'inst_data','next_due','overdue_count','alerts','lab_orders','timeline');
    }

    private function render_stats(array $d): void {
        $pct = $d['total_tx'] > 0 ? round($d['done_tx'] / $d['total_tx'] * 100) : 0;
        $plan = $d['inst_data']['plan'] ?? null;
        $inst_val = $plan ? $plan['paid_count'] . ' از ' . $plan['total_count'] : '—';
        $inst_sub = $d['next_due'] ? 'سررسید: ' . $d['next_due'] : ($plan ? 'تکمیل شده' : 'بدون قسط');
        $inst_color = $d['overdue_count'] > 0 ? 'var(--dc-danger)' : 'var(--dc-accent-warm)';

        $cards = [
            ['icon'=>'wallet','label'=>'موجودی کیف پول','value'=>number_format($d['wallet']),'sub'=>'تومان','color'=>'var(--dc-primary)','bg'=>'var(--dc-primary-light)'],
            ['icon'=>'receipt','label'=>'اقساط','value'=>$inst_val,'sub'=>$inst_sub,'color'=>$inst_color,'bg'=>'var(--dc-warning-light)'],
            ['icon'=>'tooth','label'=>'درمان‌های انجام‌شده','value'=>$d['done_tx'].'/'.$d['total_tx'],'sub'=>$pct.'٪ تکمیل شده','color'=>'var(--dc-accent-dark)','bg'=>'var(--dc-accent-light)'],
            ['icon'=>'star','label'=>'امتیاز وفاداری','value'=>number_format($d['points']),'sub'=>'امتیاز','color'=>'#8B5CF6','bg'=>'#F3F0FF'],
        ];
        ?>
        <div class="dc-grid dc-grid-4" style="gap:14px;">
        <?php foreach ($cards as $c): ?>
        <div class="dc-card" style="padding:18px 20px;border-top:3px solid <?php echo $c['color']; ?>;">
            <div style="width:40px;height:40px;border-radius:10px;background:<?php echo $c['bg']; ?>;display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                <i data-lucide="<?php echo esc_attr($c['icon']); ?>" style="width:20px;height:20px;color:<?php echo $c['color']; ?>;"></i>
            </div>
            <div style="font-size:22px;font-weight:700;color:<?php echo $c['color']; ?>;line-height:1.2;"><?php echo esc_html($c['value']); ?></div>
            <div style="font-size:11px;color:var(--dc-neutral-500);margin-top:3px;"><?php echo esc_html($c['sub']); ?></div>
            <div style="font-size:12px;color:var(--dc-neutral-700);margin-top:6px;font-weight:500;"><?php echo esc_html($c['label']); ?></div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php
    }

    private function render_treatment_plan(array $d): void {
        $pct  = $d['total_tx'] > 0 ? round($d['done_tx'] / $d['total_tx'] * 100) : 0;
        $rows = array_slice($d['plan_rows'], 0, 6);
        ?>
        <div class="dc-card">
            <div class="dc-card-header">
                <div style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="clipboard-list" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                    <h4 class="dc-heading-4" style="margin:0;">طرح درمان</h4>
                </div>
                <span style="font-size:12px;color:var(--dc-neutral-500);"><?php echo $d['done_tx']; ?> از <?php echo $d['total_tx']; ?> انجام شده</span>
            </div>
            <div class="dc-card-body" style="padding:16px 20px;">
                <!-- Progress Bar -->
                <div style="height:6px;background:var(--dc-neutral-100);border-radius:3px;margin-bottom:14px;overflow:hidden;">
                    <div style="height:100%;width:<?php echo $pct; ?>%;background:linear-gradient(90deg,var(--dc-accent),var(--dc-primary));border-radius:3px;transition:width .6s ease;"></div>
                </div>
                <?php if (empty($rows)): ?>
                <p style="font-size:12px;color:var(--dc-neutral-400);text-align:center;padding:16px 0;">طرح درمانی ثبت نشده</p>
                <?php else:
                foreach ($rows as $row): ?>
                <div style="display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--dc-neutral-100);">
                    <?php if ($row['done']): ?>
                    <i data-lucide="check-circle-2" style="width:15px;height:15px;color:var(--dc-accent);flex-shrink:0;"></i>
                    <span style="font-size:12px;color:var(--dc-neutral-400);text-decoration:line-through;flex:1;"><?php echo esc_html($row['label']); ?></span>
                    <?php if ($row['doctor']): ?>
                    <span style="font-size:10px;color:var(--dc-neutral-400);"><?php echo esc_html($row['doctor']); ?></span>
                    <?php endif; ?>
                    <?php else: ?>
                    <i data-lucide="circle" style="width:15px;height:15px;color:var(--dc-neutral-300);flex-shrink:0;"></i>
                    <span style="font-size:12px;color:var(--dc-neutral-800);font-weight:500;flex:1;"><?php echo esc_html($row['label']); ?></span>
                    <?php endif; ?>
                </div>
                <?php endforeach;
                endif; ?>
                <?php if (count($d['plan_rows']) > 6): ?>
                <p style="font-size:11px;color:var(--dc-neutral-400);text-align:center;margin-top:8px;">+ <?php echo count($d['plan_rows'])-6; ?> مورد دیگر</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function render_installments(array $d): void {
        $plan  = $d['inst_data']['plan']  ?? null;
        $items = $d['inst_data']['items'] ?? [];
        $status_cfg = [
            'paid'    => ['check-circle-2', 'var(--dc-accent)',      'پرداخت شده'],
            'overdue' => ['alert-circle',   'var(--dc-danger)',       'معوقه'],
            'pending' => ['clock',          'var(--dc-accent-warm)',  'در انتظار'],
            'partial' => ['circle-half',    'var(--dc-accent-warm)',  'جزئی'],
        ];
        ?>
        <div class="dc-card">
            <div class="dc-card-header">
                <div style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="credit-card" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                    <h4 class="dc-heading-4" style="margin:0;">اقساط</h4>
                </div>
                <?php if ($d['next_due']): ?>
                <span style="font-size:11px;background:var(--dc-warning-light);color:var(--dc-accent-warm);padding:3px 10px;border-radius:20px;font-weight:600;">سررسید: <?php echo esc_html($d['next_due']); ?></span>
                <?php endif; ?>
            </div>
            <div class="dc-card-body" style="padding:16px 20px;">
                <?php if (!$plan): ?>
                <p style="font-size:12px;color:var(--dc-neutral-400);text-align:center;padding:16px 0;">قسطی ثبت نشده</p>
                <?php else: foreach (array_slice($items, 0, 5) as $item):
                    [$icon, $color, $slabel] = $status_cfg[$item['status']] ?? ['circle','#999',''];
                ?>
                <div style="display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--dc-neutral-100);">
                    <i data-lucide="<?php echo esc_attr($icon); ?>" style="width:15px;height:15px;color:<?php echo $color; ?>;flex-shrink:0;"></i>
                    <span style="font-size:12px;color:var(--dc-neutral-700);flex:1;">قسط <?php echo (int)$item['item_number']; ?></span>
                    <span style="font-size:12px;font-weight:600;color:var(--dc-neutral-800);"><?php echo number_format($item['amount']); ?> ت</span>
                    <span style="font-size:11px;color:<?php echo $color; ?>;"><?php echo esc_html($item['due_date_jalali']); ?></span>
                </div>
                <?php endforeach;
                if (count($items) > 5): ?>
                <p style="font-size:11px;color:var(--dc-neutral-400);text-align:center;margin-top:8px;">+ <?php echo count($items)-5; ?> قسط دیگر</p>
                <?php endif; endif; ?>
            </div>
        </div>
        <?php
    }

    private function render_medical_alerts(array $d): void {
        ?>
        <div class="dc-card">
            <div class="dc-card-header">
                <div style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="shield-alert" style="width:16px;height:16px;color:var(--dc-danger);"></i>
                    <h4 class="dc-heading-4" style="margin:0;">هشدارهای پزشکی</h4>
                </div>
            </div>
            <div class="dc-card-body" style="padding:16px 20px;display:flex;flex-direction:column;gap:8px;">
                <?php if (empty($d['alerts'])): ?>
                <p style="font-size:12px;color:var(--dc-neutral-400);text-align:center;padding:12px 0;">موردی ثبت نشده</p>
                <?php else: foreach ($d['alerts'] as $a):
                    $is_danger = $a['type'] === 'danger';
                    $bg    = $is_danger ? 'var(--dc-danger-light)'  : 'var(--dc-warning-light)';
                    $color = $is_danger ? 'var(--dc-danger)'        : 'var(--dc-accent-warm)';
                    $icon  = $is_danger ? 'alert-triangle'          : 'info';
                ?>
                <div style="background:<?php echo $bg; ?>;border:1px solid <?php echo $color; ?>;border-radius:8px;padding:10px 12px;display:flex;align-items:flex-start;gap:8px;">
                    <i data-lucide="<?php echo esc_attr($icon); ?>" style="width:15px;height:15px;color:<?php echo $color; ?>;flex-shrink:0;margin-top:1px;"></i>
                    <div>
                        <div style="font-size:12px;font-weight:600;color:<?php echo $color; ?>;"><?php echo esc_html($a['label']); ?></div>
                        <?php if ($a['alert']): ?>
                        <div style="font-size:11px;color:<?php echo $color; ?>;opacity:.8;margin-top:2px;"><?php echo esc_html($a['alert']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        <?php
    }

    private function render_lab_orders(array $d): void {
        $status_cfg = [
            'sent'     => ['var(--dc-info-light)',    'var(--dc-info)',         'send'],
            'received' => ['var(--dc-accent-light)',  'var(--dc-accent-dark)', 'package-check'],
            'placed'   => ['#F3F0FF',                 '#8B5CF6',               'check-square'],
            'redo'     => ['var(--dc-danger-light)',  'var(--dc-danger)',       'refresh-cw'],
        ];
        ?>
        <div class="dc-card">
            <div class="dc-card-header">
                <div style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="flask-conical" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                    <h4 class="dc-heading-4" style="margin:0;">سفارشات لب</h4>
                </div>
            </div>
            <div class="dc-card-body" style="padding:16px 20px;display:flex;flex-direction:column;gap:10px;">
                <?php if (empty($d['lab_orders'])): ?>
                <p style="font-size:12px;color:var(--dc-neutral-400);text-align:center;padding:12px 0;">سفارشی ثبت نشده</p>
                <?php else: foreach ($d['lab_orders'] as $o):
                    [$bg,$color,$icon] = $status_cfg[$o['status']] ?? ['#f5f5f5','#999','package'];
                ?>
                <div style="border:1px solid var(--dc-neutral-200);border-radius:8px;padding:10px 12px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                        <span style="font-size:12px;font-weight:600;color:var(--dc-neutral-800);"><?php echo esc_html($o['title']); ?></span>
                        <span style="display:flex;align-items:center;gap:4px;font-size:10px;background:<?php echo $bg; ?>;color:<?php echo $color; ?>;padding:3px 8px;border-radius:12px;font-weight:600;">
                            <i data-lucide="<?php echo esc_attr($icon); ?>" style="width:11px;height:11px;"></i>
                            <?php echo esc_html($o['s_label']); ?>
                        </span>
                    </div>
                    <div style="font-size:11px;color:var(--dc-neutral-500);display:flex;gap:10px;">
                        <?php if ($o['lab']): ?><span><?php echo esc_html($o['lab']); ?></span><?php endif; ?>
                        <?php if ($o['delivery']): ?><span>تحویل: <?php echo esc_html($o['delivery']); ?></span><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        <?php
    }

    private function render_timeline(array $d): void {
        if (empty($d['timeline'])) return;
        $type_cfg = [
            'payment'   => ['credit-card',      'var(--dc-accent)',      'var(--dc-accent-light)'],
            'treatment' => ['activity',          'var(--dc-primary)',     'var(--dc-primary-light)'],
            'lab'       => ['flask-conical',     '#8B5CF6',               '#F3F0FF'],
            'wallet'    => ['wallet',            'var(--dc-accent-warm)', 'var(--dc-warning-light)'],
        ];
        ?>
        <div class="dc-card" style="margin-top:16px;">
            <div class="dc-card-header">
                <div style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="activity" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                    <h4 class="dc-heading-4" style="margin:0;">آخرین فعالیت‌ها</h4>
                </div>
            </div>
            <div class="dc-card-body" style="padding:8px 20px;">
                <?php foreach (array_slice($d['timeline'], 0, 8) as $i => $ev):
                    [$icon,$color,$bg] = $type_cfg[$ev['type']] ?? ['circle','#999','#f5f5f5'];
                    $is_last = $i === min(7, count($d['timeline'])-1);
                ?>
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;<?php echo !$is_last?'border-bottom:1px solid var(--dc-neutral-100);':''; ?>">
                    <div style="width:32px;height:32px;border-radius:8px;background:<?php echo $bg; ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="<?php echo esc_attr($icon); ?>" style="width:15px;height:15px;color:<?php echo $color; ?>;"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:13px;color:var(--dc-neutral-800);font-weight:500;"><?php echo esc_html($ev['title']); ?></div>
                        <?php if (!empty($ev['doctor'])): ?>
                        <div style="font-size:11px;color:var(--dc-neutral-500);margin-top:2px;"><?php echo esc_html($ev['doctor']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:11px;color:var(--dc-neutral-400);flex-shrink:0;"><?php echo esc_html($ev['date']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }

    private function build_timeline(int $pid, array $done_map): array {
        global $wpdb;
        $events = [];
        $LABELS = $this->tx_labels();

        // پرداخت‌ها
        $pays = $wpdb->get_results($wpdb->prepare(
            "SELECT ii.paid_date,ii.paid_amount,ii.item_number FROM {$wpdb->prefix}dental_installment_items ii
             JOIN {$wpdb->prefix}dental_installments i ON ii.installment_id=i.id
             WHERE i.patient_id=%d AND ii.status='paid' AND ii.paid_date IS NOT NULL ORDER BY ii.paid_date DESC LIMIT 8",
            $pid
        ), ARRAY_A);
        foreach ($pays as $p) {
            $events[] = ['type'=>'payment','date'=>Dental_Jalali::to_jalali($p['paid_date'],'Y/m/d'),'title'=>'پرداخت قسط '.(int)$p['item_number'].' — '.number_format($p['paid_amount']).' تومان','doctor'=>'','sort'=>$p['paid_date']];
        }

        // تراکنش‌های کیف پول
        $txs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_wallet_transactions WHERE patient_id=%d ORDER BY created_at DESC LIMIT 5",
            $pid
        ), ARRAY_A);
        foreach ($txs as $tx) {
            $events[] = ['type'=>'wallet','date'=>Dental_Jalali::to_jalali($tx['created_at'],'Y/m/d'),'title'=>($tx['transaction_type']==='credit'?'شارژ':'برداشت').' کیف پول — '.number_format($tx['amount']).' تومان','doctor'=>'','sort'=>$tx['created_at']];
        }

        // درمان‌های انجام‌شده
        $qn = $this->q_names();
        foreach ($done_map as $key => $dv) {
            if (empty($dv['done'])) continue;
            $parts = explode('_', $key);
            if (count($parts) < 3) continue;
            $fdi  = (int)$parts[0];
            $code = implode('_', array_slice($parts, 2));
            $events[] = ['type'=>'treatment','date'=>isset($dv['done_at'])?Dental_Jalali::to_jalali($dv['done_at'],'Y/m/d'):'—','title'=>($LABELS[$code]??$code).' — دندان '.($fdi%10),'doctor'=>$dv['doctor_name']??'','sort'=>$dv['done_at']??''];
        }

        // سفارشات لب
        if (class_exists('Dental_CPT_Lab_Order')) {
            foreach (array_slice(Dental_CPT_Lab_Order::get_by_patient($pid),0,5) as $o) {
                $sent = get_post_meta($o->ID,'_lab_sent_date_jalali',true);
                $dr   = get_userdata($o->post_author);
                $events[] = ['type'=>'lab','date'=>$sent?:'—','title'=>'سفارش لب: '.$o->post_title,'doctor'=>$dr?$dr->display_name:'','sort'=>$o->post_date];
            }
        }

        usort($events,fn($a,$b)=>strcmp($b['sort']??'',$a['sort']??''));
        return $events;
    }

    private function q_names(): array {
        return [1=>'بالا راست',2=>'بالا چپ',3=>'پایین چپ',4=>'پایین راست',
                5=>'شیری بالا راست',6=>'شیری بالا چپ',7=>'شیری پایین چپ',8=>'شیری پایین راست'];
    }

    private function tx_labels(): array {
        return ['composite'=>'ترمیم کامپوزیت','amalgam'=>'ترمیم آمالگام','rct'=>'عصب‌کشی',
            'pulpotomy'=>'پالپوتومی','pulpectomy'=>'پالپکتومی','buildup'=>'بیلدآپ',
            'crown'=>'روکش','veneer'=>'لامینیت','inlay_onlay'=>'انله/آنله',
            'extraction'=>'کشیدن ساده','surgical_ext'=>'کشیدن جراحی','implant'=>'ایمپلنت',
            'apicoectomy'=>'آپیکوستومی','bridge_abutment'=>'بریج','retainer_fix'=>'ریتینر',
            'xray_pa'=>'عکس PA','cbct'=>'CBCT','consult_perio'=>'مشاوره پریو',
            'consult_endo'=>'مشاوره اندو','consult_surgeon'=>'مشاوره جراح',
            'consult_prosth'=>'مشاوره پروتز','consult_resto'=>'مشاوره ترمیم','consult_peds'=>'مشاوره اطفال'];
    }
}
