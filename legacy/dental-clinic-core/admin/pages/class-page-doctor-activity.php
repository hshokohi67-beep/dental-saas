<?php
defined('ABSPATH') || exit;

class Dental_Page_Doctor_Activity {

    public function render(): void {
        $doctor_f = (int)($_GET['doctor'] ?? 0);
        $from_j = sanitize_text_field($_GET['from'] ?? Dental_Jalali::add_days(Dental_Jalali::today(), -7));
        $to_j   = sanitize_text_field($_GET['to']   ?? Dental_Jalali::today());
        $from_g = Dental_Jalali::to_gregorian($from_j) ?: date('Y-m-d', strtotime('-7 days'));
        $to_g   = Dental_Jalali::to_gregorian($to_j)   ?: current_time('Y-m-d');

        $activities = Dental_Doctor_Activity::get_recent(200, $doctor_f, $from_g, $to_g);
        $today_summary = Dental_Doctor_Activity::get_today_summary();
        $doctors = get_users(['role__in'=>['dental_doctor'],'fields'=>['ID','display_name']]);

        $type_labels = ['exam_done'=>['🩺 معاینه/ویزیت تکمیل شد','var(--dc-primary)'],'treatment_done'=>['✅ درمان انجام شد','var(--dc-accent-dark)']];
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                <i data-lucide="activity" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                کارکرد پزشکان
            </h1>

            <!-- خلاصه امروز -->
            <div class="dc-card" style="margin-bottom:20px;">
                <div class="dc-card-header"><h3 class="dc-heading-4">📊 خلاصه امروز</h3></div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>پزشک</th><th>معاینه/ویزیت تکمیل‌شده</th><th>درمان تیک‌خورده</th></tr></thead>
                        <tbody>
                        <?php if (empty($today_summary)): ?>
                        <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--dc-neutral-400);">امروز کاری ثبت نشده</td></tr>
                        <?php else: foreach($today_summary as $s): ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo esc_html($s['doctor_name']); ?></td>
                            <td style="text-align:center;"><?php echo (int)$s['exams']; ?></td>
                            <td style="text-align:center;"><?php echo (int)$s['treatments']; ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- فیلتر -->
            <div class="dc-card" style="margin-bottom:16px;">
                <div class="dc-card-body" style="padding:16px;">
                    <form method="get" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
                        <input type="hidden" name="page" value="dental-reports">
                        <input type="hidden" name="section" value="doctors">
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">پزشک</label>
                            <select name="doctor" class="dc-select" style="width:160px;">
                                <option value="0">همه</option>
                                <?php foreach($doctors as $d): ?>
                                <option value="<?php echo $d->ID; ?>" <?php selected($doctor_f,$d->ID); ?>><?php echo esc_html($d->display_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">از تاریخ</label>
                            <input type="text" name="from" class="dc-input dc-datepicker" value="<?php echo esc_attr($from_j); ?>" dir="ltr" style="width:130px;">
                        </div>
                        <div class="dc-form-group" style="margin:0;">
                            <label class="dc-label">تا تاریخ</label>
                            <input type="text" name="to" class="dc-input dc-datepicker" value="<?php echo esc_attr($to_j); ?>" dir="ltr" style="width:130px;">
                        </div>
                        <button type="submit" class="dc-btn dc-btn-primary">🔍 اعمال فیلتر</button>
                    </form>
                </div>
            </div>

            <!-- لیست فعالیت‌ها -->
            <div class="dc-card">
                <div class="dc-card-header"><h3 class="dc-heading-4">📜 فعالیت‌ها (<?php echo count($activities); ?>)</h3></div>
                <div style="padding:0;">
                    <?php if (empty($activities)): ?>
                    <p style="text-align:center;color:var(--dc-neutral-400);padding:28px;">فعالیتی در این بازه ثبت نشده</p>
                    <?php else: foreach($activities as $a):
                        [$t_label,$t_color] = $type_labels[$a['activity_type']] ?? ['—','#999'];
                    ?>
                    <div class="dc-queue-row">
                        <span style="color:<?php echo $t_color; ?>;font-weight:700;font-size:12px;white-space:nowrap;"><?php echo $t_label; ?></span>
                        <div style="flex:1;">
                            <a href="<?php echo esc_url(admin_url("admin.php?page=dental-patients&action=profile&id={$a['patient_id']}")); ?>"
                               style="font-size:13px;font-weight:600;text-decoration:none;color:var(--dc-neutral-900);"><?php echo esc_html($a['patient_name']); ?></a>
                            <span style="font-size:11px;color:var(--dc-neutral-500);"> — توسط <?php echo esc_html($a['doctor_name']); ?></span>
                            <?php if($a['description']):
                                $desc_readable = $a['activity_type']==='treatment_done'
                                    ? Dental_Doctor_Activity::describe_item_key($a['description'])
                                    : $a['description'];
                            ?><div style="font-size:10px;color:var(--dc-neutral-400);"><?php echo esc_html($desc_readable); ?></div><?php endif; ?>
                        </div>
                        <span style="font-size:10px;color:var(--dc-neutral-400);white-space:nowrap;">
                            <?php echo esc_html(Dental_Jalali::to_jalali(date('Y-m-d',strtotime($a['created_at'])),'Y/m/d')); ?>
                            <?php echo esc_html(date('H:i',strtotime($a['created_at']))); ?>
                        </span>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }
}
