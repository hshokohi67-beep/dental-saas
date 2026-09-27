<?php
defined('ABSPATH') || exit;

class Dental_Page_Inventory_Hub {

    public function render(): void {
        $low_stock = Dental_Inventory_Manager::get_low_stock_items();
        $expiring  = Dental_Inventory_Manager::get_expiring_items();
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <i data-lucide="warehouse" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                انبار
            </h1>
            <p class="dc-text-muted" style="margin-bottom:24px;">مدیریت کالاها، ثبت ورود/خروج، و هشدارهای کسری و انقضا.</p>

            <?php if (!empty($low_stock) || !empty($expiring)): ?>
            <div class="dc-grid dc-grid-2" style="gap:16px;margin-bottom:20px;">
                <?php if (!empty($low_stock)): ?>
                <div class="dc-card" style="border:1px solid var(--dc-danger);">
                    <div class="dc-card-body">
                        <div style="font-size:13px;font-weight:700;color:var(--dc-danger);margin-bottom:8px;">⚠️ کسری موجودی (<?php echo count($low_stock); ?> کالا)</div>
                        <?php foreach(array_slice($low_stock,0,5) as $it): ?>
                        <div style="font-size:12px;padding:3px 0;display:flex;justify-content:space-between;">
                            <span><?php echo esc_html($it['name']); ?></span>
                            <span style="font-weight:700;color:var(--dc-danger);"><?php echo number_format($it['current_stock'],1); ?> / <?php echo number_format($it['min_stock'],1); ?> <?php echo esc_html($it['unit']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($expiring)): ?>
                <div class="dc-card" style="border:1px solid var(--dc-accent-warm);">
                    <div class="dc-card-body">
                        <div style="font-size:13px;font-weight:700;color:var(--dc-accent-warm);margin-bottom:8px;">⏳ نزدیک به انقضا (<?php echo count($expiring); ?> کالا)</div>
                        <?php foreach(array_slice($expiring,0,5) as $it): ?>
                        <div style="font-size:12px;padding:3px 0;display:flex;justify-content:space-between;">
                            <span><?php echo esc_html($it['name']); ?></span>
                            <span style="font-weight:700;color:var(--dc-accent-warm);"><?php echo esc_html(Dental_Jalali::to_jalali($it['nearest_expiry'],'Y/m/d')); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div style="background:var(--dc-accent-light);border-radius:10px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:var(--dc-accent-dark);">✅ هیچ هشداری فعلاً نیست — موجودی همه‌چیز کافیه.</div>
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&inv_view=items')); ?>" style="text-decoration:none;">
                    <div class="dc-card" style="margin-bottom:0;height:100%;">
                        <div class="dc-card-body" style="padding:22px;">
                            <div style="width:44px;height:44px;border-radius:12px;background:var(--dc-primary-light);display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                                <i data-lucide="package" style="width:22px;height:22px;color:var(--dc-primary);"></i>
                            </div>
                            <div style="font-size:14px;font-weight:700;margin-bottom:6px;">کالاهای انبار</div>
                            <div style="font-size:12px;color:var(--dc-neutral-500);">افزودن، ویرایش، تعیین حداقل موجودی</div>
                        </div>
                    </div>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&inv_view=log')); ?>" style="text-decoration:none;">
                    <div class="dc-card" style="margin-bottom:0;height:100%;">
                        <div class="dc-card-body" style="padding:22px;">
                            <div style="width:44px;height:44px;border-radius:12px;background:#F1EFFE;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                                <i data-lucide="arrow-left-right" style="width:22px;height:22px;color:#8B5CF6;"></i>
                            </div>
                            <div style="font-size:14px;font-weight:700;margin-bottom:6px;">ثبت ورود / خروج</div>
                            <div style="font-size:12px;color:var(--dc-neutral-500);">خرید کالا، ثبت مصرف، تاریخچه کامل</div>
                        </div>
                    </div>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&inv_view=request')); ?>" style="text-decoration:none;">
                    <div class="dc-card" style="margin-bottom:0;height:100%;">
                        <div class="dc-card-body" style="padding:22px;">
                            <div style="width:44px;height:44px;border-radius:12px;background:var(--dc-danger-light);display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                                <i data-lucide="bell-plus" style="width:22px;height:22px;color:var(--dc-danger);"></i>
                            </div>
                            <div style="font-size:14px;font-weight:700;margin-bottom:6px;">درخواست‌های کالا</div>
                            <div style="font-size:12px;color:var(--dc-neutral-500);">درخواست‌های کالای پرسنل و بررسی‌شون</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }
}
