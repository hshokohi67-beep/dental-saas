<?php
defined('ABSPATH') || exit;

class Dental_Page_Insurance_Settlement {

    public function render(): void {
        $companies = Dental_Insurance_Manager::get_companies(true);

        if (isset($_POST['dental_create_settlement']) && check_admin_referer('dental_insurance_settlement')) {
            $insurance_id = (int)($_POST['insurance_id'] ?? 0);
            $from_j = sanitize_text_field($_POST['from_date'] ?? '');
            $to_j   = sanitize_text_field($_POST['to_date'] ?? '');
            $from_g = Dental_Jalali::to_gregorian($from_j) ?: date('Y-m-01');
            $to_g   = Dental_Jalali::to_gregorian($to_j) ?: current_time('Y-m-d');
            $treatment_ids = array_map('intval', $_POST['treatment_ids'] ?? []);
            if ($insurance_id && !empty($treatment_ids)) {
                $sid = Dental_Insurance_Manager::create_settlement($insurance_id, $from_g, $to_g, $treatment_ids);
                if ($sid && class_exists('Dental_Audit_Log')) {
                    Dental_Audit_Log::log('settings_changed', "درخواست تسویه بیمه جدید ساخته شد (#{$sid}) — " . count($treatment_ids) . " خدمت", ['entity_type'=>'insurance_settlement','entity_id'=>$sid]);
                }
                echo '<div class="notice notice-success"><p>✅ درخواست تسویه ساخته شد.</p></div>';
            }
        }
        if (isset($_POST['dental_update_settlement']) && check_admin_referer('dental_insurance_settlement')) {
            Dental_Insurance_Manager::update_settlement_status(
                (int)$_POST['settlement_id'], sanitize_key($_POST['new_status']),
                ['reference_no'=>$_POST['reference_no']??'', 'paid_amount'=>$_POST['paid_amount']??null, 'notes'=>$_POST['notes']??'']
            );
            echo '<div class="notice notice-success"><p>✅ بروزرسانی شد.</p></div>';
        }

        $view_id = (int)($_GET['view_settlement'] ?? 0);
        if ($view_id) { $this->render_detail($view_id); return; }

        $sel_insurance = (int)($_GET['sel_ins'] ?? ($companies[0]['id'] ?? 0));
        $from_default = Dental_Jalali::to_jalali(date('Y-m-01'),'Y/m/d');
        $to_default   = Dental_Jalali::today('Y/m/d');
        $unsettled = $sel_insurance ? Dental_Insurance_Manager::get_unsettled_treatments($sel_insurance, date('Y-m-01'), current_time('Y-m-d')) : [];
        $settlements = Dental_Insurance_Manager::get_settlements();
        $status_labels = ['draft'=>['پیش‌نویس','var(--dc-neutral-500)'],'submitted'=>['ارسال‌شده به بیمه','var(--dc-accent-warm)'],'paid'=>['تسویه‌شده','var(--dc-accent-dark)']];
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <i data-lucide="file-check-2" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                تسویه دوره‌ای با بیمه
            </h1>
            <p class="dc-text-muted" style="margin-bottom:20px;">برای کلینیک‌های طرف‌قرارداد مستقیم — لیست تجمیعی سهم بیمه رو برای یه بازه بسازید و وضعیت ارسال/تسویه‌ش رو پیگیری کنید.</p>

            <div class="dc-card" style="margin-bottom:24px;">
                <div class="dc-card-header"><h3 class="dc-heading-4">➕ ساخت درخواست تسویه جدید</h3></div>
                <div class="dc-card-body">
                    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;margin-bottom:16px;">
                        <input type="hidden" name="page" value="dental-insurance">
                        <input type="hidden" name="section" value="settlement">
                        <div>
                            <label class="dc-label">بیمه</label>
                            <select name="sel_ins" class="dc-select" onchange="this.form.submit()">
                                <?php foreach($companies as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php selected($sel_insurance,$c['id']); ?>><?php echo esc_html($c['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <?php if (empty($unsettled)): ?>
                    <p style="text-align:center;color:var(--dc-neutral-400);padding:20px;">هیچ خدمت تسویه‌نشده‌ای این ماه برای این بیمه ثبت نشده</p>
                    <?php else: ?>
                    <form method="post">
                        <?php wp_nonce_field('dental_insurance_settlement'); ?>
                        <input type="hidden" name="insurance_id" value="<?php echo $sel_insurance; ?>">
                        <div class="dc-grid dc-grid-2" style="gap:10px;margin-bottom:14px;max-width:400px;">
                            <div><label class="dc-label">از تاریخ</label><input type="text" name="from_date" class="dc-input" value="<?php echo esc_attr($from_default); ?>"></div>
                            <div><label class="dc-label">تا تاریخ</label><input type="text" name="to_date" class="dc-input" value="<?php echo esc_attr($to_default); ?>"></div>
                        </div>
                        <div class="dc-table-wrap" style="max-height:320px;overflow-y:auto;margin-bottom:14px;">
                            <table class="dc-table">
                                <thead><tr>
                                    <th><input type="checkbox" onclick="document.querySelectorAll('.dc-settle-chk').forEach(c=>c.checked=this.checked)"></th>
                                    <th>بیمار</th><th>خدمت</th><th>پزشک</th><th>تاریخ</th><th>سهم بیمه</th>
                                </tr></thead>
                                <tbody>
                                <?php foreach($unsettled as $t): ?>
                                <tr>
                                    <td><input type="checkbox" class="dc-settle-chk" name="treatment_ids[]" value="<?php echo $t['id']; ?>" checked></td>
                                    <td style="font-size:12px;"><?php echo esc_html($t['patient_name']); ?></td>
                                    <td style="font-size:12px;"><?php echo esc_html($t['service_name']); ?></td>
                                    <td style="font-size:12px;"><?php echo esc_html($t['doctor_name']); ?></td>
                                    <td style="font-size:11px;color:var(--dc-neutral-500);"><?php echo esc_html(Dental_Jalali::to_jalali($t['recorded_date'],'Y/m/d')); ?></td>
                                    <td style="font-weight:700;"><?php echo number_format($t['insurance_share']); ?> ت</td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <div style="font-size:13px;font-weight:700;color:var(--dc-primary);">جمع کل: <?php echo number_format(array_sum(array_column($unsettled,'insurance_share'))); ?> تومان</div>
                            <button type="submit" name="dental_create_settlement" class="dc-btn dc-btn-primary">📤 ساخت درخواست تسویه</button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dc-card">
                <div class="dc-card-header"><h3 class="dc-heading-4">📋 تاریخچه‌ی درخواست‌های تسویه</h3></div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>بیمه</th><th>بازه</th><th>تعداد</th><th>مبلغ</th><th>وضعیت</th><th></th></tr></thead>
                        <tbody>
                        <?php if (empty($settlements)): ?>
                        <tr><td colspan="6" style="text-align:center;padding:20px;color:var(--dc-neutral-400);">درخواستی ثبت نشده</td></tr>
                        <?php else: foreach($settlements as $s):
                            $st = $status_labels[$s['status']];
                        ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo esc_html($s['insurance_name']); ?></td>
                            <td style="font-size:12px;"><?php echo esc_html(Dental_Jalali::to_jalali($s['period_from'],'Y/m/d')); ?> تا <?php echo esc_html(Dental_Jalali::to_jalali($s['period_to'],'Y/m/d')); ?></td>
                            <td><?php echo (int)$s['treatment_count']; ?></td>
                            <td style="font-weight:700;"><?php echo number_format($s['total_amount']); ?> ت</td>
                            <td><span style="color:<?php echo $st[1]; ?>;font-weight:700;font-size:12px;"><?php echo $st[0]; ?></span></td>
                            <td><a href="<?php echo esc_url(add_query_arg('view_settlement',$s['id'])); ?>" class="dc-btn dc-btn-ghost dc-btn-sm">مشاهده</a></td>
                        </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }

    private function render_detail(int $id): void {
        global $wpdb;
        $s = $wpdb->get_row($wpdb->prepare(
            "SELECT s.*, i.name as insurance_name FROM {$wpdb->prefix}dental_insurance_settlements s
             LEFT JOIN {$wpdb->prefix}dental_insurance_companies i ON s.insurance_id=i.id WHERE s.id=%d", $id
        ), ARRAY_A);
        if (!$s) { wp_die('یافت نشد.'); }
        $items = Dental_Insurance_Manager::get_settlement_details($id);
        $status_labels = ['draft'=>'پیش‌نویس','submitted'=>'ارسال‌شده به بیمه','paid'=>'تسویه‌شده'];
        ?>
        <div class="dental-admin-wrap">
            <a href="<?php echo esc_url(remove_query_arg('view_settlement')); ?>" style="font-size:12px;color:var(--dc-primary);text-decoration:none;display:inline-block;margin-bottom:10px;">← بازگشت به لیست</a>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
                <h1 class="dc-heading-2" style="margin:0;">تسویه #<?php echo $id; ?> — <?php echo esc_html($s['insurance_name']); ?></h1>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=dental-dashboard&print_settlement_bundle=1&settlement_id='.$id),'dental_print_settlement_bundle')); ?>" target="_blank" class="dc-btn dc-btn-primary dc-btn-sm">
                    📎 دانلود بسته‌ی کامل تسویه (لیست + مدارک همه)
                </a>
            </div>

            <div class="dc-card" style="margin-bottom:20px;">
                <div class="dc-card-body" style="display:flex;gap:30px;flex-wrap:wrap;">
                    <div><div style="font-size:11px;color:var(--dc-neutral-500);">بازه</div><div style="font-weight:700;"><?php echo esc_html(Dental_Jalali::to_jalali($s['period_from'],'Y/m/d')); ?> تا <?php echo esc_html(Dental_Jalali::to_jalali($s['period_to'],'Y/m/d')); ?></div></div>
                    <div><div style="font-size:11px;color:var(--dc-neutral-500);">تعداد خدمات</div><div style="font-weight:700;"><?php echo (int)$s['treatment_count']; ?></div></div>
                    <div><div style="font-size:11px;color:var(--dc-neutral-500);">مبلغ درخواستی</div><div style="font-weight:700;color:var(--dc-primary);"><?php echo number_format($s['total_amount']); ?> تومان</div></div>
                    <div><div style="font-size:11px;color:var(--dc-neutral-500);">وضعیت فعلی</div><div style="font-weight:700;"><?php echo $status_labels[$s['status']]; ?></div></div>
                </div>
            </div>

            <div class="dc-card" style="margin-bottom:20px;">
                <div class="dc-card-header"><h3 class="dc-heading-4">🔄 بروزرسانی وضعیت</h3></div>
                <form method="post">
                    <?php wp_nonce_field('dental_insurance_settlement'); ?>
                    <input type="hidden" name="settlement_id" value="<?php echo $id; ?>">
                    <div class="dc-card-body" style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                        <div>
                            <label class="dc-label">وضعیت جدید</label>
                            <select name="new_status" class="dc-select">
                                <option value="draft" <?php selected($s['status'],'draft'); ?>>پیش‌نویس</option>
                                <option value="submitted" <?php selected($s['status'],'submitted'); ?>>ارسال‌شده به بیمه</option>
                                <option value="paid" <?php selected($s['status'],'paid'); ?>>تسویه‌شده</option>
                            </select>
                        </div>
                        <div>
                            <label class="dc-label">شماره پیگیری/نامه</label>
                            <input type="text" name="reference_no" class="dc-input" value="<?php echo esc_attr($s['reference_no']); ?>">
                        </div>
                        <div>
                            <label class="dc-label">مبلغ واقعی واریزشده (اگه تسویه شده)</label>
                            <input type="number" name="paid_amount" class="dc-input" value="<?php echo esc_attr($s['paid_amount']); ?>">
                        </div>
                        <div style="grid-column:1/-1;">
                            <label class="dc-label">یادداشت</label>
                            <textarea name="notes" class="dc-input" rows="2"><?php echo esc_textarea($s['notes']); ?></textarea>
                        </div>
                    </div>
                    <div class="dc-card-body" style="padding-top:0;">
                        <button type="submit" name="dental_update_settlement" class="dc-btn dc-btn-primary">ذخیره</button>
                    </div>
                </form>
            </div>

            <div class="dc-card">
                <div class="dc-card-header"><h3 class="dc-heading-4">📋 جزئیات خدمات این تسویه</h3></div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>بیمار</th><th>خدمت</th><th>پزشک</th><th>تاریخ</th><th>سهم بیمه</th></tr></thead>
                        <tbody>
                        <?php foreach($items as $t): ?>
                        <tr>
                            <td><?php echo esc_html($t['patient_name']); ?></td>
                            <td style="font-size:12px;"><?php echo esc_html($t['service_name']); ?></td>
                            <td style="font-size:12px;"><?php echo esc_html($t['doctor_name']); ?></td>
                            <td style="font-size:11px;color:var(--dc-neutral-500);"><?php echo esc_html(Dental_Jalali::to_jalali($t['recorded_date'],'Y/m/d')); ?></td>
                            <td style="font-weight:700;"><?php echo number_format($t['insurance_share']); ?> ت</td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
}
