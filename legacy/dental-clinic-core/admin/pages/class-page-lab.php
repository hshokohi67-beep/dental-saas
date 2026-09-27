<?php
defined('ABSPATH') || exit;

/**
 * رندر تب Lab در پروفایل بیمار
 */
class Dental_Page_Lab {

    private int $patient_id;

    public function __construct(int $patient_id) {
        $this->patient_id = $patient_id;
    }

    public function maybe_handle_post(): void {
        $can_edit = current_user_can('manage_options') || current_user_can('dental_manage_lab');
        if ($can_edit && isset($_POST['dental_save_lab'])) {
            check_admin_referer('dental_lab_' . $this->patient_id);
            $result = Dental_CPT_Lab_Order::create(
                $this->patient_id,
                (int)($_POST['treatment_id'] ?? 0),
                get_current_user_id(),
                sanitize_text_field($_POST['lab_name']    ?? ''),
                sanitize_text_field($_POST['work_type']   ?? ''),
                sanitize_text_field($_POST['shade']       ?? ''),
                sanitize_text_field($_POST['delivery']    ?? ''),
                sanitize_textarea_field($_POST['lab_notes'] ?? '')
            );
            if ($result) {
                wp_safe_redirect(add_query_arg(['tab'=>'lab','saved'=>'1'],
                    admin_url("admin.php?page=dental-patients&action=profile&id={$this->patient_id}")));
                exit;
            }
        }
        if ($can_edit && isset($_POST['dental_update_lab_status'])) {
            check_admin_referer('dental_lab_status_' . $this->patient_id);
            Dental_CPT_Lab_Order::update_status(
                (int)$_POST['lab_order_id'],
                sanitize_text_field($_POST['new_status']),
                sanitize_text_field($_POST['received_date'] ?? '')
            );
            wp_safe_redirect(add_query_arg(['tab'=>'lab','saved'=>'1'],
                admin_url("admin.php?page=dental-patients&action=profile&id={$this->patient_id}")));
            exit;
        }
    }

    public function render(): void {
        $can_edit = current_user_can('manage_options') || current_user_can('dental_manage_lab');
        $orders   = Dental_CPT_Lab_Order::get_by_patient($this->patient_id);
        $statuses = Dental_CPT_Lab_Order::get_statuses();
        $saved    = isset($_GET['saved']);

        if ($saved):
        ?>
        <div style="background:#E8FAF4;border:1px solid #2ECC9A;border-radius:8px;padding:10px 16px;margin-bottom:14px;font-size:13px;">✅ ذخیره شد.</div>
        <?php endif; ?>

        <div class="dc-grid dc-grid-2" style="gap:20px;align-items:start;">

            <!-- فرم سفارش جدید -->
            <?php if ($can_edit): ?>
            <div class="dc-card">
                <div class="dc-card-header"><h3 class="dc-heading-4">🔬 سفارش کار جدید به لابراتوار</h3></div>
                <div class="dc-card-body">
                    <form method="post">
                        <?php wp_nonce_field('dental_lab_' . $this->patient_id); ?>
                        <div style="display:flex;flex-direction:column;gap:12px;">
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">نام لابراتوار <span style="color:red">*</span></label>
                                <input type="text" name="lab_name" class="dc-input" required placeholder="نام لابراتوار">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">نوع کار <span style="color:red">*</span></label>
                                <select name="work_type" class="dc-select" required>
                                    <option value="">انتخاب کنید</option>
                                    <?php foreach([
                                        'crown_pfm'    => 'روکش PFM',
                                        'crown_zirconia'=> 'روکش زیرکونیا',
                                        'crown_emax'   => 'روکش Emax',
                                        'bridge'       => 'بریج',
                                        'veneer'       => 'لامینیت',
                                        'denture_full' => 'دندان مصنوعی کامل',
                                        'denture_part' => 'دندان مصنوعی پارسیل',
                                        'night_guard'  => 'اسپلینت شبانه',
                                        'retainer'     => 'ریتینر',
                                        'implant_crown'=> 'روکش ایمپلنت',
                                        'inlay_onlay'  => 'انله / آنله',
                                        'other'        => 'سایر',
                                    ] as $k => $v): ?>
                                    <option value="<?php echo esc_attr($k); ?>"><?php echo esc_html($v); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">رنگ (Shade)</label>
                                <input type="text" name="shade" class="dc-input" placeholder="مثال: A2, B1" dir="ltr">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">تاریخ تحویل مورد انتظار (شمسی)</label>
                                <input type="text" name="delivery" class="dc-input dc-datepicker" dir="ltr"
                                    placeholder="<?php echo esc_attr(Dental_Jalali::add_days(Dental_Jalali::today(), 10)); ?>">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">یادداشت</label>
                                <textarea name="lab_notes" class="dc-textarea" style="min-height:70px;" placeholder="دستورالعمل‌های خاص..."></textarea>
                            </div>
                        </div>
                        <div style="margin-top:16px;">
                            <button type="submit" name="dental_save_lab" class="dc-btn dc-btn-primary">📤 ثبت سفارش</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- لیست سفارش‌ها -->
            <div class="dc-card">
                <div class="dc-card-header"><h3 class="dc-heading-4">📋 کارهای ارجاع‌شده به لابراتوار (<?php echo count($orders); ?>)</h3></div>
                <div class="dc-card-body" style="padding:0;">
                    <?php if (empty($orders)): ?>
                    <div style="padding:32px;text-align:center;color:var(--dc-neutral-500);font-size:13px;">سفارشی ثبت نشده</div>
                    <?php else: foreach($orders as $order):
                        $status    = get_post_meta($order->ID, '_lab_status', true);
                        $lab_name  = get_post_meta($order->ID, '_lab_name', true);
                        $work_type = get_post_meta($order->ID, '_lab_work_type', true);
                        $shade     = get_post_meta($order->ID, '_lab_shade', true);
                        $delivery  = get_post_meta($order->ID, '_lab_delivery_jalali', true);
                        $sent      = get_post_meta($order->ID, '_lab_sent_date_jalali', true);
                        $notes     = get_post_meta($order->ID, '_lab_notes', true);

                        $status_colors = [
                            'sent'     => ['#5DADE2','ارسال به لب'],
                            'received' => ['#2ECC9A','دریافت از لب'],
                            'placed'   => ['#9B7FD4','نصب شده'],
                            'redo'     => ['#E05252','نیاز به اصلاح'],
                        ];
                        [$scolor, $slabel] = $status_colors[$status] ?? ['#999',$status];
                    ?>
                    <div style="padding:14px 16px;border-bottom:1px solid var(--dc-neutral-100);">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                            <div style="flex:1;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                    <span style="font-size:13px;font-weight:700;"><?php echo esc_html($order->post_title); ?></span>
                                    <span style="background:<?php echo $scolor; ?>22;color:<?php echo $scolor; ?>;border-radius:10px;padding:2px 8px;font-size:10px;font-weight:700;"><?php echo esc_html($slabel); ?></span>
                                </div>
                                <div style="font-size:11px;color:var(--dc-neutral-500);display:flex;gap:12px;flex-wrap:wrap;">
                                    <span>🏢 <?php echo esc_html($lab_name); ?></span>
                                    <?php if($shade): ?><span>🎨 <?php echo esc_html($shade); ?></span><?php endif; ?>
                                    <span>📅 ارسال: <?php echo esc_html($sent); ?></span>
                                    <?php if($delivery): ?><span>🗓️ تحویل: <?php echo esc_html($delivery); ?></span><?php endif; ?>
                                </div>
                                <?php if($notes): ?>
                                <div style="font-size:11px;color:var(--dc-neutral-600);margin-top:4px;font-style:italic;"><?php echo esc_html($notes); ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- تغییر وضعیت -->
                            <?php if ($can_edit && $status !== 'placed'): ?>
                            <form method="post" style="flex-shrink:0;">
                                <?php wp_nonce_field('dental_lab_status_' . $this->patient_id); ?>
                                <input type="hidden" name="lab_order_id" value="<?php echo (int)$order->ID; ?>">
                                <select name="new_status" class="dc-select" style="font-size:12px;height:32px;width:130px;"
                                    onchange="this.form.submit()">
                                    <?php foreach($statuses as $k => $v): ?>
                                    <option value="<?php echo esc_attr($k); ?>" <?php selected($status,$k); ?>><?php echo esc_html($v); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="dental_update_lab_status" style="display:none;"></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
