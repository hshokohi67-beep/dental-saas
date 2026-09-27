<?php
defined('ABSPATH') || exit;

/**
 * صفحه اختصاصی پزشک — چیدمان Master-Detail (۳۰٪ چپ / ۷۰٪ راست)
 * چپ: تقویم + لیست انتظار + اقدام‌شده (بر اساس تاریخ انتخابی)
 * راست: جزئیات بیمار با AJAX (بدون رفرش) + دکمه ثبت طرح درمان
 */
class Dental_Page_Doctor_Desk {

    public function render(): void {
        $cu = wp_get_current_user();
        $today_j = Dental_Jalali::today();
        $today_g = current_time('Y-m-d');

        // تاریخ انتخابی از تقویم (پیش‌فرض: امروز)
        if (!empty($_GET['jdate'])) {
            $g = Dental_Jalali::to_gregorian(sanitize_text_field($_GET['jdate']));
            $view_date = $g ?: $today_g;
        } else {
            $view_date = $today_g;
        }
        $view_j = Dental_Jalali::to_jalali($view_date, 'Y/m/d');

        $waiting = Dental_Reception_Manager::get_queue_for_date($view_date, $cu->ID);
        $done    = Dental_Reception_Manager::get_done_for_date($view_date, $cu->ID);
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                <i data-lucide="stethoscope" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                پرونده روزانه
                <?php if (class_exists('Dental_Shift_Scheduler')):
                    $my_room = Dental_Shift_Scheduler::get_doctor_room_today($cu->ID);
                    if ($my_room):
                ?>
                <span style="font-size:12px;font-weight:700;background:var(--dc-primary-light);color:var(--dc-primary);border-radius:20px;padding:5px 14px;">
                    🚪 امروز اتاق <?php echo (int)$my_room['room_number']; ?> — شیفت <?php echo esc_html($my_room['shift']); ?>
                </span>
                <?php endif; endif; ?>
                <?php if (class_exists('Dental_Inventory_Manager')): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dental-dashboard&inv_view=request')); ?>" class="dc-btn dc-btn-ghost dc-btn-sm" style="margin-right:auto;">
                    📢 درخواست کالا
                </a>
                <?php endif; ?>
            </h1>

            <!-- ═══ چیدمان Master-Detail ۳۰/۷۰ ═══ -->
            <div style="display:grid;grid-template-columns:30% 70%;gap:16px;align-items:start;">

                <!-- ═══ ستون چپ (Master) ═══ -->
                <div style="display:flex;flex-direction:column;gap:14px;">

                    <!-- تقویم انتخاب تاریخ -->
                    <div class="dc-card">
                        <div class="dc-card-body" style="padding:14px;">
                            <label class="dc-label">مشاهده تاریخچه یک روز خاص</label>
                            <form method="get" style="display:flex;gap:6px;">
                                <input type="hidden" name="page" value="dental-doctor-desk">
                                <input type="text" name="jdate" class="dc-input dc-datepicker" value="<?php echo esc_attr($view_j); ?>" dir="ltr" style="flex:1;">
                                <button type="submit" class="dc-btn dc-btn-secondary dc-btn-sm">برو</button>
                            </form>
                            <?php if ($view_date !== $today_g): ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=dental-doctor-desk')); ?>" style="font-size:11px;color:var(--dc-primary);display:inline-block;margin-top:6px;">↩ بازگشت به امروز</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- لیست انتظار -->
                    <div class="dc-card">
                        <div class="dc-card-header">
                            <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:6px;font-size:13px;">
                                ⏳ لیست انتظار <span class="dc-badge dc-badge-primary"><?php echo count($waiting); ?></span>
                            </h3>
                        </div>
                        <div style="max-height:320px;overflow-y:auto;">
                            <?php if (empty($waiting)): ?>
                            <p style="text-align:center;color:var(--dc-neutral-400);font-size:12px;padding:20px;">خالی است</p>
                            <?php else: foreach($waiting as $w):
                                $is_prog = $w['status']==='in_progress';
                            ?>
                            <div class="dc-desk-patient-row" data-patient-id="<?php echo (int)$w['patient_id']; ?>">
                                <span class="dc-desk-dot" style="background:<?php echo $is_prog?'#1A6B8A':'#F0A500'; ?>;"></span>
                                <span onclick="dcDeskLoadPatient(<?php echo (int)$w['patient_id']; ?>, this)" style="cursor:pointer;flex:1;"><?php echo esc_html($w['patient_name']); ?></span>
                                <span style="font-size:10px;color:var(--dc-neutral-400);"><?php echo esc_html(date('H:i',strtotime($w['checked_in_at']))); ?></span>
                                <?php if (!$is_prog): ?>
                                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(['page'=>'dental-reception','qid'=>$w['id'],'status'=>'in_progress'],admin_url('admin.php')),'dental_queue_status_'.$w['id'])); ?>"
                                   class="dc-btn dc-btn-primary dc-btn-sm" style="padding:3px 8px;font-size:10px;">شروع</a>
                                <?php else: ?>
                                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(['page'=>'dental-reception','qid'=>$w['id'],'status'=>'done','goto_ledger'=>$w['patient_id']],admin_url('admin.php')),'dental_queue_status_'.$w['id'])); ?>"
                                   class="dc-btn dc-btn-success dc-btn-sm" style="padding:3px 8px;font-size:10px;">پایان</a>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>

                    <!-- اقدام‌شده -->
                    <div class="dc-card">
                        <div class="dc-card-header">
                            <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:6px;font-size:13px;">
                                ✅ اقدام شده <span class="dc-badge dc-badge-success"><?php echo count($done); ?></span>
                            </h3>
                        </div>
                        <div style="max-height:320px;overflow-y:auto;">
                            <?php if (empty($done)): ?>
                            <p style="text-align:center;color:var(--dc-neutral-400);font-size:12px;padding:20px;">خالی است</p>
                            <?php else: foreach($done as $d): ?>
                            <div class="dc-desk-patient-row" data-patient-id="<?php echo (int)$d['patient_id']; ?>"
                                 onclick="dcDeskLoadPatient(<?php echo (int)$d['patient_id']; ?>, this)">
                                <span class="dc-desk-dot" style="background:#2ECC9A;"></span>
                                <span><?php echo esc_html($d['patient_name']); ?></span>
                                <span style="font-size:10px;color:var(--dc-neutral-400);margin-right:auto;"><?php echo $d['finished_at']?esc_html(date('H:i',strtotime($d['finished_at']))):''; ?></span>
                            </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ═══ ستون راست (Detail) — با AJAX پر می‌شود ═══ -->
                <div class="dc-card" style="min-height:400px;">
                    <div id="dc-desk-detail-panel" style="display:flex;align-items:center;justify-content:center;height:400px;color:var(--dc-neutral-400);font-size:13px;text-align:center;padding:20px;">
                        یک بیمار را از لیست کناری انتخاب کنید تا پرونده‌اش اینجا نمایش داده شود
                    </div>
                </div>
            </div>
        </div>

        <style>
        .dc-desk-patient-row{display:flex;align-items:center;gap:8px;padding:10px 14px;cursor:pointer;
            font-size:12px;border-bottom:1px solid var(--dc-neutral-50);transition:background .15s;}
        .dc-desk-patient-row:hover{background:var(--dc-primary-light);}
        .dc-desk-patient-row.active{background:var(--dc-primary-light);border-right:3px solid var(--dc-primary);}
        .dc-desk-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
        </style>

        <script>
        var dcDeskNonce = '<?php echo esc_js(wp_create_nonce("dental_workspace")); ?>';
        var dcDeskAjax  = '<?php echo esc_js(admin_url("admin-ajax.php")); ?>';

        function dcDeskLoadPatient(patientId, el){
            document.querySelectorAll('.dc-desk-patient-row').forEach(r=>r.classList.remove('active'));
            el.classList.add('active');

            var panel = document.getElementById('dc-desk-detail-panel');
            panel.style.height = 'auto';
            panel.style.display = 'block';
            panel.innerHTML = '<div style="padding:60px;text-align:center;color:#A0B4C0;">در حال بارگذاری...</div>';

            // نکته: show_chart=1 چون این صفحه مخصوص پزشک است — دکمه «ثبت طرح درمان» نمایش داده شود
            fetch(dcDeskAjax + '?action=dental_quick_patient_view&patient_id=' + patientId + '&show_chart=1&_wpnonce=' + dcDeskNonce)
            .then(r=>r.json()).then(function(res){
                if(res.success) panel.innerHTML = res.data.html;
                else panel.innerHTML = '<p style="padding:20px;color:#E05252;">خطا در بارگذاری اطلاعات</p>';
            });
        }
        </script>
        <?php Dental_Admin::render_quick_view_assets(); ?>
        <script>
        if(typeof lucide!=="undefined") lucide.createIcons();
        </script>
        <?php
    }
}
