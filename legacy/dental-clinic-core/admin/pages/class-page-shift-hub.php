<?php
defined('ABSPATH') || exit;

class Dental_Page_Shift_Hub {

    public function render(): void {
        $cards = [
            ['icon'=>'door-open',    'title'=>'تنظیمات اتاق‌ها',           'desc'=>'فعال/غیرفعال‌بودن اتاق‌ها، دکتر ثابت، تنظیمات دستیاران', 'view'=>'rooms',              'color'=>'var(--dc-primary)'],
            ['icon'=>'stethoscope',  'title'=>'برنامه ماهانه دکترها',      'desc'=>'کدوم روز/شیفت هر دکتر کار می‌کنه',                        'view'=>'monthly-doctors',    'color'=>'var(--dc-accent-dark)'],
            ['icon'=>'users',       'title'=>'برنامه ماهانه دستیارها',    'desc'=>'شامل قفل روز و چیدمان خودکار بر اساس دکترها',              'view'=>'monthly-assistants', 'color'=>'#8B5CF6'],
            ['icon'=>'calendar-range','title'=>'برنامه هفتگی اتاق‌ها',    'desc'=>'چیدمان دکتر/دستیار در هر اتاق، هشدارها، پرینت',            'view'=>'weekly',             'color'=>'var(--dc-accent-warm)'],
        ];
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <i data-lucide="calendar-cog" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                شیفت‌بندی و چرخش پرسنل
            </h1>
            <p class="dc-text-muted" style="margin-bottom:24px;">مدیریت اتاق‌ها، برنامه ماهانه دکتر/دستیار، و چیدمان هفتگی اتاق‌ها.</p>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
                <?php foreach($cards as $c): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&shift_view='.$c['view'])); ?>" style="text-decoration:none;">
                    <div class="dc-card" style="margin-bottom:0;height:100%;transition:all .2s;cursor:pointer;" onmouseover="this.style.boxShadow='0 6px 20px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
                        <div class="dc-card-body" style="padding:22px;">
                            <div style="width:44px;height:44px;border-radius:12px;background:<?php echo $c['color']; ?>18;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                                <i data-lucide="<?php echo $c['icon']; ?>" style="width:22px;height:22px;color:<?php echo $c['color']; ?>;"></i>
                            </div>
                            <div style="font-size:14px;font-weight:700;color:var(--dc-neutral-900);margin-bottom:6px;"><?php echo esc_html($c['title']); ?></div>
                            <div style="font-size:12px;color:var(--dc-neutral-500);line-height:1.7;"><?php echo esc_html($c['desc']); ?></div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }
}
