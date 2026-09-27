<?php
defined('ABSPATH') || exit;

class Dental_Page_Audit_Log {

    public function render(): void {
        $filters = [
            'user_id'     => (int)($_GET['f_user'] ?? 0),
            'action_type' => sanitize_key($_GET['f_action'] ?? ''),
            'from_date'   => sanitize_text_field($_GET['f_from'] ?? ''),
            'to_date'     => sanitize_text_field($_GET['f_to'] ?? ''),
            'search'      => sanitize_text_field($_GET['f_search'] ?? ''),
        ];
        if ($filters['from_date']) $filters['from_date'] = Dental_Jalali::to_gregorian($filters['from_date']) ?: '';
        if ($filters['to_date'])   $filters['to_date']   = Dental_Jalali::to_gregorian($filters['to_date']) ?: '';

        $page = max(1, (int)($_GET['pg'] ?? 1));
        $result = Dental_Audit_Log::get_logs($filters, $page, 50);
        $action_labels = Dental_Audit_Log::get_action_labels();

        $staff = get_users(['role__in'=>['dental_admin','dental_doctor','dental_secretary','dental_financial','dental_assistant'], 'fields'=>['ID','display_name']]);
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <i data-lucide="shield-check" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                لاگ فعالیت
            </h1>
            <p class="dc-text-muted" style="margin-bottom:20px;">کی، کِی، چه کاری انجام داده — برای پیگیری و شفافیت.</p>

            <div class="dc-card" style="margin-bottom:0;border-radius:12px 12px 0 0;">
                <div class="dc-card-body" style="padding:14px 16px;">
                    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                        <input type="hidden" name="page" value="dental-dashboard">
                        <input type="hidden" name="audit_view" value="1">
                        <select name="f_user" class="dc-select" style="max-width:180px;">
                            <option value="0">همه‌ی کاربران</option>
                            <?php foreach($staff as $s): ?>
                            <option value="<?php echo $s->ID; ?>" <?php selected($filters['user_id'],$s->ID); ?>><?php echo esc_html($s->display_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="f_action" class="dc-select" style="max-width:200px;">
                            <option value="">همه‌ی نوع فعالیت‌ها</option>
                            <?php foreach($action_labels as $k=>$l): ?>
                            <option value="<?php echo esc_attr($k); ?>" <?php selected($filters['action_type'],$k); ?>><?php echo esc_html($l); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="f_search" class="dc-input" value="<?php echo esc_attr($_GET['f_search']??''); ?>" placeholder="جستجو در توضیحات..." style="max-width:200px;">
                        <button type="submit" class="dc-btn dc-btn-primary dc-btn-sm">فیلتر</button>
                        <span style="margin-right:auto;font-size:12px;color:var(--dc-neutral-500);"><?php echo number_format($result['total']); ?> رکورد</span>
                    </form>
                </div>
            </div>

            <div class="dc-card" style="border-radius:0;margin-bottom:0;">
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>تاریخ/ساعت</th><th>کاربر</th><th>نوع فعالیت</th><th>توضیح</th><th>مبلغ</th><th>IP</th></tr></thead>
                        <tbody>
                        <?php if (empty($result['logs'])): ?>
                        <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--dc-neutral-400);">لاگی یافت نشد</td></tr>
                        <?php else: foreach($result['logs'] as $l): ?>
                        <tr>
                            <td style="font-size:11px;color:var(--dc-neutral-500);white-space:nowrap;">
                                <?php echo esc_html(Dental_Jalali::to_jalali(date('Y-m-d',strtotime($l['created_at'])),'Y/m/d')); ?>
                                <br><?php echo esc_html(date('H:i',strtotime($l['created_at']))); ?>
                            </td>
                            <td style="font-weight:600;font-size:12px;"><?php echo esc_html($l['user_name'] ?: '—'); ?></td>
                            <td style="font-size:12px;"><?php echo esc_html($action_labels[$l['action_type']] ?? $l['action_type']); ?></td>
                            <td style="font-size:12px;color:var(--dc-neutral-600);"><?php echo esc_html($l['description']); ?></td>
                            <td style="font-size:12px;font-weight:700;"><?php echo $l['amount'] ? number_format($l['amount']).' ت' : '—'; ?></td>
                            <td style="font-size:10px;color:var(--dc-neutral-400);direction:ltr;text-align:right;"><?php echo esc_html($l['ip_address'] ?: '—'); ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($result['pages'] > 1): ?>
                <div style="padding:14px 16px;display:flex;gap:6px;justify-content:center;border-top:1px solid var(--dc-neutral-100);">
                    <?php for ($p=1; $p<=$result['pages']; $p++): ?>
                    <a href="<?php echo esc_url(add_query_arg('pg',$p)); ?>" class="dc-btn <?php echo $p==$page?'dc-btn-primary':'dc-btn-ghost'; ?> dc-btn-sm"><?php echo $p; ?></a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }
}
