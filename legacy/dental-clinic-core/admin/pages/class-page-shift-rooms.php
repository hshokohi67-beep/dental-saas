<?php
defined('ABSPATH') || exit;

class Dental_Page_Shift_Rooms {

    public function render(): void {
        if (isset($_POST['dental_save_rooms']) && check_admin_referer('dental_shift_rooms')) {
            foreach ($_POST['rooms'] ?? [] as $id => $r) {
                Dental_Shift_Scheduler::save_room((int)$id, $r);
            }
            echo '<div class="notice notice-success"><p>✅ ذخیره شد.</p></div>';
        }

        $rooms   = Dental_Shift_Scheduler::get_rooms();
        $doctors = Dental_Shift_Scheduler::get_doctors();
        ?>
        <div class="dental-admin-wrap">
            <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&shift_view=hub')); ?>" style="font-size:12px;color:var(--dc-primary);text-decoration:none;display:inline-block;margin-bottom:10px;">← بازگشت به شیفت‌بندی</a>
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <i data-lucide="door-open" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                تنظیمات اتاق‌ها
            </h1>
            <p class="dc-text-muted" style="margin-bottom:20px;">مشخص کنید هر اتاق در شیفت صبح/عصر فعاله یا نه، و اگه دکتر ثابتی داره.</p>

            <?php if (count($doctors) === 0): ?>
            <div style="background:var(--dc-warning-light);border:1px solid var(--dc-accent-warm);border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;">
                ⚠️ هنوز هیچ پزشکی با نقش «دندانپزشک» توی سیستم ثبت نشده — اول از بخش کاربران، پزشکان رو اضافه کنید.
            </div>
            <?php endif; ?>

            <form method="post">
                <?php wp_nonce_field('dental_shift_rooms'); ?>
                <div class="dc-card">
                    <div class="dc-table-wrap">
                        <table class="dc-table">
                            <thead><tr>
                                <th>اتاق</th>
                                <th>فعال صبح</th>
                                <th>فعال عصر</th>
                                <th>دکتر ثابت صبح (اختیاری)</th>
                                <th>دکتر ثابت عصر (اختیاری)</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach($rooms as $r): ?>
                            <tr>
                                <td style="font-weight:700;">اتاق <?php echo (int)$r['room_number']; ?></td>
                                <td style="text-align:center;">
                                    <input type="checkbox" name="rooms[<?php echo $r['id']; ?>][active_morning]" value="1" <?php checked($r['active_morning']); ?> style="width:18px;height:18px;">
                                </td>
                                <td style="text-align:center;">
                                    <input type="checkbox" name="rooms[<?php echo $r['id']; ?>][active_evening]" value="1" <?php checked($r['active_evening']); ?> style="width:18px;height:18px;">
                                </td>
                                <td>
                                    <select name="rooms[<?php echo $r['id']; ?>][fixed_doctor_morning]" class="dc-select">
                                        <option value="">— بدون دکتر ثابت —</option>
                                        <?php foreach($doctors as $d): ?>
                                        <option value="<?php echo $d->ID; ?>" <?php selected($r['fixed_doctor_morning'],$d->ID); ?>><?php echo esc_html($d->display_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="rooms[<?php echo $r['id']; ?>][fixed_doctor_evening]" class="dc-select">
                                        <option value="">— بدون دکتر ثابت —</option>
                                        <?php foreach($doctors as $d): ?>
                                        <option value="<?php echo $d->ID; ?>" <?php selected($r['fixed_doctor_evening'],$d->ID); ?>><?php echo esc_html($d->display_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="dc-card-body">
                        <button type="submit" name="dental_save_rooms" class="dc-btn dc-btn-primary">💾 ذخیره تنظیمات اتاق‌ها</button>
                    </div>
                </div>
            </form>

            <div class="dc-card" style="margin-top:20px;">
                <div class="dc-card-header"><h3 class="dc-heading-4">👥 پرسنل ثبت‌شده در سیستم</h3></div>
                <div class="dc-card-body" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                    <div>
                        <div style="font-size:12px;font-weight:700;color:var(--dc-primary);margin-bottom:8px;">🦷 پزشکان (<?php echo count($doctors); ?>)</div>
                        <?php foreach($doctors as $d): ?><div style="font-size:12px;padding:3px 0;"><?php echo esc_html($d->display_name); ?></div><?php endforeach; ?>
                    </div>
                    <div>
                        <?php $assistants = Dental_Shift_Scheduler::get_assistants(); ?>
                        <div style="font-size:12px;font-weight:700;color:var(--dc-accent-dark);margin-bottom:8px;">🧑‍⚕️ دستیاران (<?php echo count($assistants); ?>)</div>
                        <?php foreach($assistants as $a): ?><div style="font-size:12px;padding:3px 0;"><?php echo esc_html($a->display_name); ?></div><?php endforeach; ?>
                    </div>
                    <div>
                        <?php $reception = Dental_Shift_Scheduler::get_reception_staff(); ?>
                        <div style="font-size:12px;font-weight:700;color:var(--dc-accent-warm);margin-bottom:8px;">📋 پذیرش (<?php echo count($reception); ?>)</div>
                        <?php foreach($reception as $r2): ?><div style="font-size:12px;padding:3px 0;"><?php echo esc_html($r2->display_name); ?></div><?php endforeach; ?>
                    </div>
                </div>
                <div style="padding:0 20px 16px;font-size:11px;color:var(--dc-neutral-400);">
                    این لیست‌ها خودکار از کاربران واقعی سیستم (بخش کاربران) میان — نیازی به ثبت جدا نیست.
                </div>
            </div>

            <?php
            // ─── ذخیره تنظیمات دستیار (نوع شیفت + مجاز دوشیفتی) ──────
            if (isset($_POST['dental_save_as_prefs']) && check_admin_referer('dental_as_prefs')) {
                foreach ($_POST['as_prefs'] ?? [] as $uid => $p) {
                    Dental_Shift_Scheduler::save_assistant_prefs((int)$uid, sanitize_key($p['shift']??'flexible'), !empty($p['can_double']));
                }
                echo '<div class="notice notice-success"><p>✅ تنظیمات دستیاران ذخیره شد.</p></div>';
            }
            $assistants2 = Dental_Shift_Scheduler::get_assistants();
            ?>
            <?php if (!empty($assistants2)): ?>
            <div class="dc-card" style="margin-top:20px;">
                <div class="dc-card-header"><h3 class="dc-heading-4">⚙️ تنظیمات چیدمان خودکار دستیاران</h3></div>
                <div class="dc-card-body" style="font-size:12px;color:var(--dc-neutral-500);padding-bottom:0;">
                    این‌ها فقط برای «چیدمان خودکار» توی برنامه ماهانه دستیارها استفاده می‌شن.
                </div>
                <form method="post">
                    <?php wp_nonce_field('dental_as_prefs'); ?>
                    <div class="dc-table-wrap">
                        <table class="dc-table">
                            <thead><tr><th>دستیار</th><th>نوع شیفت پیش‌فرض</th><th>مجاز به دوشیفتی</th></tr></thead>
                            <tbody>
                            <?php foreach($assistants2 as $a):
                                $prefs = Dental_Shift_Scheduler::get_assistant_prefs($a->ID);
                            ?>
                            <tr>
                                <td style="font-weight:600;"><?php echo esc_html($a->display_name); ?></td>
                                <td>
                                    <select name="as_prefs[<?php echo $a->ID; ?>][shift]" class="dc-select">
                                        <option value="morning" <?php selected($prefs['shift'],'morning'); ?>>صبح ثابت</option>
                                        <option value="evening" <?php selected($prefs['shift'],'evening'); ?>>عصر ثابت</option>
                                        <option value="flexible" <?php selected($prefs['shift'],'flexible'); ?>>انعطاف‌پذیر</option>
                                    </select>
                                </td>
                                <td style="text-align:center;">
                                    <input type="checkbox" name="as_prefs[<?php echo $a->ID; ?>][can_double]" value="1" <?php checked($prefs['can_double']); ?> style="width:18px;height:18px;">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="dc-card-body">
                        <button type="submit" name="dental_save_as_prefs" class="dc-btn dc-btn-primary">💾 ذخیره تنظیمات دستیاران</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }
}
