<?php
defined('ABSPATH') || exit;

class Dental_Page_Patient_Profile {

    private int $patient_id = 0;
    private ?string $mobile_error = null;

    public function render(): void {
        $this->patient_id = (int)($_GET['id'] ?? 0);
        if (!$this->patient_id) {
            wp_safe_redirect(admin_url('admin.php?page=dental-patients'));
            exit;
        }

        $patient = get_post($this->patient_id);
        if (!$patient || $patient->post_type !== 'dental_patient') {
            echo '<div class="dental-admin-wrap"><p>بیمار یافت نشد.</p></div>';
            return;
        }

        // ─── پردازش فرم‌ها قبل از هر output ─────────────────────
        if (isset($_POST['dental_save_profile']) && check_admin_referer('dental_profile_' . $this->patient_id)) {
            $this->save_profile();
        }

        if (isset($_POST['dental_topup_wallet']) && check_admin_referer('dental_wallet_' . $this->patient_id)) {
            $this->do_topup();
        }

        // ─── متغیرهای نمایش ────────────────────────────────────────
        $tab      = sanitize_key($_GET['tab'] ?? 'overview');

        // ─── منشی به داده‌های بالینی (چارت، پزشکی، لابراتوار) دسترسی ندارد ──
        // (دستیار طبق تصمیم قبلی اجازه «مشاهده» چارت را دارد، پس محدود نمی‌شود)
        // این چک هم دکمه تب را مخفی می‌کند و هم اگر کسی مستقیم از آدرس
        // tab=chart را باز کند، سمت سرور جلویش را می‌گیرد.
        $cu = wp_get_current_user();
        $clinical_restricted = in_array('dental_secretary', (array)$cu->roles) && !current_user_can('manage_options');
        if ($clinical_restricted && in_array($tab, ['chart','medical','lab','imaging'], true)) {
            $tab = 'info';
        }
        $mobile   = get_post_meta($this->patient_id, '_patient_mobile', true);
        $wallet   = (float)get_post_meta($this->patient_id, '_wallet_balance', true);
        $saved    = isset($_GET['saved']);
        $ini      = mb_substr($patient->post_title, 0, 1);
        $can_edit = current_user_can('manage_options') || current_user_can('dental_edit_chart');

        $mobile_err_msg = null;
        if (isset($_GET['mobile_error'])) {
            $mobile_err_msg = get_transient('dental_mobile_error_' . get_current_user_id());
            delete_transient('dental_mobile_error_' . get_current_user_id());
        }
        ?>
        <div class="dental-admin-wrap">

            <?php if ($mobile_err_msg): ?>
            <div style="background:var(--dc-danger-light);border:1px solid var(--dc-danger);border-radius:8px;padding:12px 18px;margin-bottom:16px;font-size:13px;color:var(--dc-danger);display:flex;align-items:center;gap:8px;">
                <i data-lucide="alert-triangle" style="width:16px;height:16px;flex-shrink:0;"></i>
                <?php echo esc_html($mobile_err_msg); ?>
            </div>
            <?php endif; ?>

            <div class="dc-dashboard-header" style="margin-bottom:20px;">
                <div style="display:flex;align-items:center;gap:16px;position:relative;z-index:1;flex-wrap:wrap;">
                    <div style="width:60px;height:60px;border-radius:50%;background:rgba(255,255,255,.2);border:3px solid rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:#fff;flex-shrink:0;">
                        <?php echo esc_html($ini); ?>
                    </div>
                    <div style="color:#fff;flex:1;">
                        <h2 style="margin:0 0 4px;font-size:19px;font-weight:700;"><?php echo esc_html($patient->post_title); ?></h2>
                        <div style="opacity:.8;font-size:13px;display:flex;gap:14px;flex-wrap:wrap;">
                            <?php if($mobile): ?><span>📱 <?php echo esc_html($mobile); ?></span><?php endif; ?>
                            <span>💰 <?php echo number_format($wallet); ?> تومان</span>
                            <?php if(!$can_edit): ?>
                            <span style="background:rgba(255,255,255,.2);padding:2px 10px;border-radius:10px;font-size:11px;">👁 فقط مشاهده</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div style="margin-top:10px;position:relative;z-index:1;">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=dental-patients')); ?>" style="color:rgba(255,255,255,.7);font-size:13px;text-decoration:none;">← بازگشت</a>
                </div>
            </div>

            <?php if($saved): ?>
            <div style="background:#E8FAF4;border:1px solid #2ECC9A;border-radius:8px;padding:10px 16px;margin-bottom:14px;font-size:13px;">✅ ذخیره شد.</div>
            <?php endif; ?>

            <div class="dc-tabs">
                <?php
                $tabs_cfg = [
                    'overview' => ['layout-dashboard', 'خلاصه'],
                    'info'     => ['user',             'اطلاعات'],
                    'chart'    => ['grid-3x3',         'چارت'],
                    'medical'  => ['clipboard-list',   'پزشکی'],
                    'imaging'  => ['scan',             'تصویربرداری'],
                ];
                // ─── تب مسیر درمان — فقط وقتی از تنظیمات ← امکانات فعال
                // شده باشه؛ وگرنه اصلاً توی لیست تب‌ها ظاهر نمی‌شه ──────
                if (class_exists('Dental_Pathway_Manager') && Dental_Pathway_Manager::is_enabled()) {
                    $tabs_cfg['pathway'] = ['route', 'مسیر درمان'];
                }
                $tabs_cfg['lab']    = ['flask-conical', 'کار لابراتوار'];
                $tabs_cfg['wallet'] = ['wallet', 'مالی'];
                foreach($tabs_cfg as $s => [$icon, $lbl]):
                    if ($clinical_restricted && in_array($s, ['chart','medical','lab','imaging','pathway'], true)) continue;
                ?>
                <a href="<?php echo esc_url(admin_url("admin.php?page=dental-patients&action=profile&id={$this->patient_id}&tab={$s}")); ?>"
                   class="dc-tab <?php echo $tab===$s?'active':''; ?>">
                    <i data-lucide="<?php echo esc_attr($icon); ?>" style="width:15px;height:15px;"></i>
                    <?php echo esc_html($lbl); ?>
                </a>
                <?php endforeach; ?>
                <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
            </div>

            <?php
            switch($tab) {
                case 'overview':
                    if(class_exists('Dental_Page_Patient_Dashboard')) {
                        (new Dental_Page_Patient_Dashboard($this->patient_id))->render();
                    }
                    // نوبت بعدی
                    $next_appt = $this->get_next_appointment();
                    if($next_appt):
                        $ac = $next_appt['status']==='confirmed'?'#2ECC9A':'#F0A500';
                    ?>
                    <div class="dc-card" style="margin-top:16px;border-top:3px solid <?php echo $ac; ?>;">
                        <div class="dc-card-body" style="padding:14px 18px;">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <div style="width:38px;height:38px;border-radius:10px;background:<?php echo $ac; ?>22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i data-lucide="calendar-check" style="width:18px;height:18px;color:<?php echo $ac; ?>;"></i>
                                </div>
                                <div style="flex:1;">
                                    <div style="font-size:11px;color:var(--dc-neutral-500);">نوبت بعدی</div>
                                    <div style="font-size:15px;font-weight:700;">
                                        <?php echo esc_html($next_appt['appt_date_jalali']); ?>
                                        <span style="font-size:13px;font-weight:400;color:var(--dc-neutral-600);">ساعت <?php echo esc_html(substr($next_appt['start_time'],0,5)); ?></span>
                                    </div>
                                    <div style="font-size:12px;color:var(--dc-neutral-500);"><?php echo esc_html($next_appt['service_title']??''); ?><?php if($next_appt['doctor_name']): ?> — <?php echo esc_html($next_appt['doctor_name']); ?><?php endif; ?></div>
                                </div>
                                <a href="<?php echo esc_url(admin_url("admin.php?page=dental-booking&date={$next_appt['appt_date']}")); ?>"
                                   class="dc-btn dc-btn-secondary dc-btn-sm">مشاهده</a>
                            </div>
                        </div>
                    </div>
                    <?php endif;
                    break;
                case 'chart':
                    $this->render_chart_tab($can_edit);
                    break;
                case 'medical':
                    if(class_exists('Dental_Page_Medical')) {
                        (new Dental_Page_Medical($this->patient_id))->render();
                    } else {
                        $this->render_medical_fallback();
                    }
                    break;
                case 'imaging':
                    if(class_exists('Dental_Page_Imaging')) {
                        (new Dental_Page_Imaging($this->patient_id))->render();
                    }
                    break;
                case 'pathway':
                    if (class_exists('Dental_Pathway_Manager') && Dental_Pathway_Manager::is_enabled() && class_exists('Dental_Page_Pathway')) {
                        (new Dental_Page_Pathway($this->patient_id))->render();
                    }
                    break;
                case 'lab':
                    if(class_exists('Dental_Page_Lab')) {
                        (new Dental_Page_Lab($this->patient_id))->render();
                    } else {
                        echo '<div class="dc-card"><div class="dc-card-body" style="text-align:center;padding:40px;color:#A0B4C0;">فایل class-page-lab.php آپلود نشده.</div></div>';
                    }
                    break;
                case 'wallet':
                    $this->render_wallet_tab();
                    break;
                default:
                    $this->render_info_tab($patient, $can_edit);
            }
            ?>
        </div>
        <?php
    }

    // ─── تب اطلاعات ────────────────────────────────────────────
    private function render_info_tab(WP_Post $p, bool $can_edit): void {
        $f = fn($k) => get_post_meta($this->patient_id, $k, true);
        ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

            <div class="dc-card">
                <div class="dc-card-header"><h3 class="dc-heading-4">👤 اطلاعات پایه</h3></div>
                <div class="dc-card-body">
                    <?php if($can_edit): ?>
                    <form method="post">
                        <?php wp_nonce_field('dental_profile_' . $this->patient_id); ?>
                        <input type="hidden" name="dental_tab" value="info">
                        <div style="display:flex;flex-direction:column;gap:12px;">
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">نام و نام خانوادگی</label>
                                <input type="text" name="patient_name" class="dc-input" value="<?php echo esc_attr($p->post_title); ?>">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">شماره موبایل</label>
                                <input type="tel" name="patient_mobile" class="dc-input" dir="ltr" value="<?php echo esc_attr($f('_patient_mobile')); ?>">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">کد ملی</label>
                                <input type="text" name="patient_national_id" class="dc-input" dir="ltr" maxlength="10" inputmode="numeric" placeholder="۱۰ رقم" value="<?php echo esc_attr($f('_patient_national_id')); ?>">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">جنسیت</label>
                                <select name="patient_gender" class="dc-select">
                                    <option value="">انتخاب کنید</option>
                                    <option value="male"   <?php selected($f('_patient_gender'),'male'); ?>>مرد</option>
                                    <option value="female" <?php selected($f('_patient_gender'),'female'); ?>>زن</option>
                                </select>
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">تاریخ تولد (شمسی)</label>
                                <input type="text" name="patient_dob" class="dc-input dc-datepicker" dir="ltr" placeholder="1370/01/01" value="<?php echo esc_attr($f('_patient_dob_jalali')); ?>">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">آلرژی به دارو</label>
                                <input type="text" name="patient_allergy" class="dc-input" value="<?php echo esc_attr($f('_drug_allergies')); ?>" placeholder="مثال: پنی‌سیلین">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">حساب کاربری وردپرس</label>
                                <?php
                                $linked_user_id = (int)get_post_meta($this->patient_id, '_patient_wp_user_id', true);
                                $all_users = get_users(['fields'=>['ID','display_name','user_email'],'number'=>200]);
                                ?>
                                <select name="patient_wp_user_id" class="dc-select">
                                    <option value="">— انتخاب کنید —</option>
                                    <?php foreach($all_users as $u): ?>
                                    <option value="<?php echo (int)$u->ID; ?>" <?php selected($linked_user_id, $u->ID); ?>>
                                        <?php echo esc_html($u->display_name); ?> (<?php echo esc_html($u->user_email); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small style="color:var(--dc-neutral-500);font-size:11px;">بیمار با این حساب وارد پنل بیمار می‌شود</small>
                            </div>

                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">یادداشت</label>
                                <textarea name="patient_notes" class="dc-textarea" style="min-height:80px;"><?php echo esc_textarea($f('_patient_notes')); ?></textarea>
                            </div>

                            <?php $next_due = $this->get_next_installment_date(); if($next_due): ?>
                            <div style="background:var(--dc-warning-light);border:1px solid var(--dc-accent-warm);border-radius:8px;padding:10px 14px;display:flex;align-items:center;gap:10px;">
                                <i data-lucide="calendar-clock" style="width:18px;height:18px;color:var(--dc-accent-warm);flex-shrink:0;"></i>
                                <div>
                                    <div style="font-size:11px;color:var(--dc-accent-warm);font-weight:600;">تاریخ قسط بعدی</div>
                                    <div style="font-size:16px;font-weight:700;color:var(--dc-neutral-800);"><?php echo esc_html($next_due); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>

                        </div>
                        <div style="margin-top:16px;">
                            <button type="submit" name="dental_save_profile" class="dc-btn dc-btn-primary">💾 ذخیره</button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:10px;font-size:13px;">
                        <?php foreach(['نام'=>$p->post_title,'موبایل'=>$f('_patient_mobile'),'آلرژی'=>$f('_drug_allergies'),'یادداشت'=>$f('_patient_notes')] as $lbl=>$val):
                            if(!$val) continue; ?>
                        <div><span style="color:#7A96A4;min-width:70px;display:inline-block;"><?php echo esc_html($lbl); ?>:</span> <?php echo esc_html($val); ?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    // ─── تاریخ قسط بعدی ────────────────────────────────────────
    private function get_next_installment_date(): ?string {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare(
            "SELECT ii.due_date_jalali
             FROM {$wpdb->prefix}dental_installment_items ii
             JOIN {$wpdb->prefix}dental_installments i ON ii.installment_id = i.id
             WHERE i.patient_id = %d AND ii.status IN ('pending','overdue')
             ORDER BY ii.due_date ASC LIMIT 1",
            $this->patient_id
        ));
    }

    // ─── نوبت بعدی از پلاگین نوبت‌دهی (در صورت فعال بودن) ──────
    private function get_next_appointment(): ?array {
        if (!class_exists('Dental_Booking_Appointment')) return null;
        $upcoming = Dental_Booking_Appointment::get_patient_appointments($this->patient_id, 'upcoming');
        return !empty($upcoming) ? $upcoming[0] : null;
    }

    // ─── طرح درمان فقط‌خواندنی ─────────────────────────────────
    private function render_plan_readonly(): void {
        $conditions = $this->get_tooth_conditions();
        $done_map   = get_post_meta($this->patient_id, '_chart_done_map', true) ?: [];

        $TOOTH_TX = [
            'composite'=>'ترمیم کامپوزیت','amalgam'=>'ترمیم آمالگام',
            'rct'=>'عصب‌کشی','pulpotomy'=>'پالپوتومی','pulpectomy'=>'پالپکتومی',
            'buildup'=>'بیلدآپ','crown'=>'روکش','veneer'=>'لامینیت/ونیر',
            'inlay_onlay'=>'انله/آنله','extraction'=>'کشیدن ساده',
            'surgical_ext'=>'کشیدن جراحی','implant'=>'ایمپلنت',
            'apicoectomy'=>'آپیکوستومی','bridge_abutment'=>'بریج(پایه)',
            'retainer_fix'=>'ریتینر فیکس','xray_pa'=>'عکس PA','cbct'=>'CBCT',
            'consult_perio'=>'مشاوره پریو','consult_endo'=>'مشاوره اندو',
            'consult_surgeon'=>'مشاوره جراح','consult_prosth'=>'مشاوره پروتز',
            'consult_resto'=>'مشاوره ترمیم','consult_peds'=>'مشاوره اطفال',
        ];
        $qNames = [1=>'بالا راست',2=>'بالا چپ',3=>'پایین چپ',4=>'پایین راست',
                   5=>'شیری بالا راست',6=>'شیری بالا چپ',7=>'شیری پایین چپ',8=>'شیری پایین راست'];

        $doctors = $this->get_doctors();
        $can_edit = current_user_can('manage_options') || current_user_can('dental_edit_chart');

        $rows = [];
        foreach($conditions as $key => $d) {
            if(empty($d['treatments'])) continue;
            $parts = explode('_', $key);
            $fdi   = (int)$parts[0];
            $type  = $parts[1] ?? 'permanent';
            $q     = (int)($fdi / 10);
            $n     = $fdi % 10;
            $rows[] = [
                'key'        => $key,
                'title'      => 'دندان '.$n.' — '.($qNames[$q]??$q).($type==='primary'?' (شیری)':''),
                'treatments' => $d['treatments'],
                'notes'      => $d['notes'] ?? '',
                'exam_doc'   => $d['exam_doctor_id'] ?? 0,
                'done_map'   => $d['done_map'] ?? [],
                'done_doc'   => $d['done_doctor_map'] ?? [],
            ];
        }

        if(empty($rows)) {
            echo '<p style="color:#A0B4C0;font-size:12px;text-align:center;padding:20px 0;">برای مشاهده طرح درمان، به تب چارت دندانی بروید.</p>';
            return;
        }

        foreach($rows as $row):
            $pid = $this->patient_id;
            ?>
            <div style="padding:8px 0;border-bottom:1.5px solid #EEF2F5;">
                <div style="font-size:13px;font-weight:700;color:#1A6B8A;margin-bottom:4px;"><?php echo esc_html($row['title']); ?></div>
                <?php if($row['notes']): ?>
                <div style="font-size:11px;color:#7A96A4;margin-bottom:4px;"><?php echo esc_html($row['notes']); ?></div>
                <?php endif; ?>
                <?php foreach($row['treatments'] as $code):
                    $label   = $TOOTH_TX[$code] ?? $code;
                    $done    = !empty($row['done_map'][$code]);
                    $done_by = $row['done_doc'][$code] ?? 0;
                    $exam_name = '';
                    foreach($doctors as $dr) { if($dr['id'] == $row['exam_doc']) { $exam_name = $dr['name']; break; } }
                    $done_name = '';
                    foreach($doctors as $dr) { if($dr['id'] == $done_by) { $done_name = $dr['name']; break; } }
                    $item_key = $row['key'].'_'.$code;
                ?>
                <div style="display:flex;align-items:center;gap:6px;padding:5px 0;border-bottom:1px solid #F5F5F5;">
                    <?php if($can_edit): ?>
                    <input type="checkbox"
                        id="chk_<?php echo esc_attr(md5($item_key)); ?>"
                        <?php echo $done ? 'checked' : ''; ?>
                        onchange="DentalPlan.toggle('<?php echo esc_js($row['key']); ?>','<?php echo esc_js($code); ?>',this.checked,<?php echo (int)$pid; ?>)"
                        style="width:15px;height:15px;accent-color:#2ECC9A;flex-shrink:0;cursor:pointer;">
                    <?php else: ?>
                    <span style="width:15px;height:15px;border-radius:3px;border:1px solid #C8D4DC;display:flex;align-items:center;justify-content:center;font-size:10px;color:#2ECC9A;flex-shrink:0;"><?php echo $done?'✓':''; ?></span>
                    <?php endif; ?>

                    <span style="font-size:12px;<?php echo $done?'text-decoration:line-through;color:#A0B4C0;':'color:#1A2733;font-weight:500;'; ?>"><?php echo esc_html($label); ?></span>

                    <div style="margin-right:auto;display:flex;align-items:center;gap:6px;">
                        <?php if($exam_name): ?>
                        <span style="font-size:10px;color:#7A96A4;">معاینه: <?php echo esc_html($exam_name); ?></span>
                        <?php endif; ?>
                        <?php if($done && $done_name): ?>
                        <span style="font-size:10px;color:#2ECC9A;">انجام: <?php echo esc_html($done_name); ?></span>
                        <?php endif; ?>
                        <?php if($can_edit && !$done): ?>
                        <select onchange="DentalPlan.setDoc('<?php echo esc_js($item_key); ?>',this.value)"
                            style="font-size:10px;border:1px solid #DDE5EB;border-radius:4px;padding:2px 4px;font-family:Tahoma;color:#5A7080;max-width:100px;">
                            <option value="">پزشک درمان</option>
                            <?php foreach($doctors as $dr): ?>
                            <option value="<?php echo (int)$dr['id']; ?>" <?php echo $done_by==$dr['id']?'selected':''; ?>><?php echo esc_html($dr['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php
        endforeach;

        // JS برای تیک زدن
        if($can_edit):
        $nonce  = wp_create_nonce('wp_rest');
        $apiUrl = rest_url('dental/v1');
        ?>
        <script>
        window.DentalPlan = {
            _docs: {},
            setDoc: function(key, docId) { this._docs[key] = docId; },
            toggle: function(rowKey, code, done, patientId) {
                var itemKey = rowKey + "_" + code;
                var docId = this._docs[itemKey] || 0;
                var x = new XMLHttpRequest();
                x.open("POST", "<?php echo esc_js($apiUrl); ?>/chart/toggle-done");
                x.setRequestHeader("Content-Type","application/json");
                x.setRequestHeader("X-WP-Nonce","<?php echo esc_js($nonce); ?>");
                x.onload = function(){
                    var r = JSON.parse(x.responseText||"{}");
                    if(!r.success) alert("خطا در ذخیره");
                };
                x.send(JSON.stringify({patient_id:patientId,key:rowKey,code:code,done:done?1:0,done_doctor_id:docId}));
            }
        };
        </script>
        <?php endif;
    }

    // ─── تب چارت ───────────────────────────────────────────────
    private function render_chart_tab(bool $can_edit): void {
        $chart_mode = get_post_meta($this->patient_id, '_chart_mode', true) ?: 'adult';
        $conditions = $this->get_tooth_conditions();
        $doctors    = $this->get_doctors();
        ?>
        <div class="dc-card">
            <div class="dc-card-header" style="flex-wrap:wrap;gap:10px;">
                <h3 class="dc-heading-4">🦷 چارت دندانی</h3>
                <div style="display:flex;gap:6px;">
                    <button class="dc-mode-btn" data-mode="adult" type="button" onclick="DentalChart.setMode('adult')"
                        style="padding:5px 16px;border-radius:20px;border:none;cursor:pointer;font-family:Tahoma;font-size:12px;font-weight:600;transition:all .2s;background:<?php echo $chart_mode==='adult'?'#1A6B8A':'#F0F4F6';?>;color:<?php echo $chart_mode==='adult'?'#fff':'#5A7080';?>;">
                        بزرگسال
                    </button>
                    <button class="dc-mode-btn" data-mode="peds" type="button" onclick="DentalChart.setMode('peds')"
                        style="padding:5px 16px;border-radius:20px;border:none;cursor:pointer;font-family:Tahoma;font-size:12px;font-weight:600;transition:all .2s;background:<?php echo $chart_mode==='peds'?'#1A6B8A':'#F0F4F6';?>;color:<?php echo $chart_mode==='peds'?'#fff':'#5A7080';?>;">
                        اطفال
                    </button>
                </div>
                <?php if(!$can_edit): ?>
                <span style="font-size:12px;color:#E05252;background:#FDEAEA;padding:4px 10px;border-radius:6px;">👁 فقط مشاهده</span>
                <?php endif; ?>
            </div>
            <div class="dc-card-body" style="padding:12px;">
                <div id="dc-chart-area"></div>
            </div>
        </div>

        <div id="dc-modal" style="display:none;position:fixed;inset:0;background:rgba(26,39,51,.65);z-index:99999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:14px;width:100%;max-width:500px;margin:20px;overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">
                <div style="background:linear-gradient(135deg,#1A6B8A,#0F4D66);padding:14px 18px;display:flex;align-items:center;justify-content:space-between;color:#fff;flex-shrink:0;">
                    <h4 id="dc-modal-title" style="margin:0;font-size:15px;font-family:Tahoma;">دندان</h4>
                    <button onclick="DentalChart.closeModal()" type="button" style="background:rgba(255,255,255,.2);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:15px;">✕</button>
                </div>
                <div id="dc-modal-body" style="padding:16px;direction:rtl;overflow-y:auto;flex:1;"></div>
                <div style="padding:12px 16px;background:#F8FAFB;border-top:1px solid #EEF2F5;display:flex;gap:8px;flex-shrink:0;">
                    <?php if($can_edit): ?>
                    <button id="dc-modal-save" type="button" class="dc-btn dc-btn-primary dc-btn-sm">💾 ذخیره</button>
                    <?php endif; ?>
                    <button onclick="DentalChart.closeModal()" type="button" class="dc-btn dc-btn-ghost dc-btn-sm">بستن</button>
                </div>
            </div>
        </div>

        <script>
        var dentalChartConfig = {
            patientId:  <?php echo (int)$this->patient_id; ?>,
            mode:       <?php echo wp_json_encode($chart_mode); ?>,
            nonce:      <?php echo wp_json_encode(wp_create_nonce('wp_rest')); ?>,
            apiBase:    <?php echo wp_json_encode(rest_url('dental/v1')); ?>,
            conditions: <?php echo wp_json_encode($conditions); ?>,
            <?php
            // رفع باگ اصلی و ریشه‌ای: این دوتا همیشه {} خالی هاردکد شده
            // بودن — یعنی حتی اگه سمت سرور درست ذخیره می‌شد (که الان
            // درسته)، موقع لود صفحه هیچ‌وقت واقعاً از دیتابیس خونده
            // نمی‌شدن؛ برای همین با هر رفرش/تعویض تب، انتخاب‌ها گم می‌شدن.
            $halfarch_data = get_post_meta($this->patient_id, '_chart_halfarch', true);
            $fullarch_data = get_post_meta($this->patient_id, '_chart_fullarch', true);
            ?>
            halfarch:   <?php echo wp_json_encode(is_array($halfarch_data) ? $halfarch_data : (object)[]); ?>,
            fullarch:   <?php echo wp_json_encode(is_array($fullarch_data) ? $fullarch_data : (object)[]); ?>,
            <?php
            // ─── اتصال مسیر درمان به چارت — یه‌طرفه، فقط نمایشی، بدون
            // هیچ AJAX جدید (همون درسی که از باگ‌های قبلی گرفتیم). یه
            // آبجکت ساده {tooth_number: [summary,...]} — فقط برای
            // دندان‌هایی که مسیر فعال دارن.
            $pathway_by_tooth = [];
            if (class_exists('Dental_Pathway_Manager') && Dental_Pathway_Manager::is_enabled()) {
                $pw_list = Dental_Pathway_Manager::get_patient_pathways($this->patient_id);
                foreach ($pw_list as $pw) {
                    if ($pw['status'] !== 'active' || !$pw['tooth_number']) continue;
                    $pathway_by_tooth[$pw['tooth_number']][] = [
                        'title'     => $pw['title'],
                        'remaining' => $pw['remaining'],
                        'steps'     => array_map(fn($s) => ['title'=>$s['title'],'status'=>$s['status']], $pw['steps']),
                    ];
                }
            }
            ?>
            pathways: <?php echo wp_json_encode($pathway_by_tooth); ?>,
            examDoctor: <?php echo (int)get_current_user_id(); ?>,
            doctors:    <?php echo wp_json_encode($doctors); ?>,
            canEdit:    <?php echo $can_edit ? 'true' : 'false'; ?>,
        };
        </script>
        <?php
    }

    // ─── Fallback Medical Tab ────────────────────────────────────
    private function render_medical_fallback(): void {
        $diseases = json_decode(get_post_meta($this->patient_id,'_systemic_diseases',true)?:'[]',true)?:[];
        $allergy  = get_post_meta($this->patient_id,'_drug_allergies',true);
        $disease_list = ['diabetes'=>'دیابت','hypertension'=>'فشار خون','heart'=>'بیماری قلبی',
            'bleeding'=>'اختلال انعقاد','asthma'=>'آسم','kidney'=>'بیماری کلیوی',
            'liver'=>'بیماری کبدی','thyroid'=>'بیماری تیروئید','pregnancy'=>'بارداری','osteoporosis'=>'پوکی استخوان'];
        ?>
        <div class="dc-card" style="max-width:620px;">
            <div class="dc-card-header"><h3 class="dc-heading-4">📋 تاریخچه پزشکی</h3></div>
            <div class="dc-card-body">
                <form method="post">
                    <?php wp_nonce_field('dental_profile_'.$this->patient_id); ?>
                    <input type="hidden" name="dental_tab" value="medical">
                    <div class="dc-form-group">
                        <label class="dc-label">آلرژی به دارو</label>
                        <input type="text" name="patient_allergy" class="dc-input" value="<?php echo esc_attr($allergy); ?>" placeholder="مثال: پنی‌سیلین">
                    </div>
                    <div class="dc-form-group">
                        <label class="dc-label" style="margin-bottom:10px;">بیماری‌های سیستمیک</label>
                        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;">
                            <?php foreach($disease_list as $k=>$l):
                                $chk = in_array($k,$diseases); ?>
                            <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;border:1px solid var(--dc-neutral-200);cursor:pointer;">
                                <input type="checkbox" name="diseases[]" value="<?php echo esc_attr($k); ?>" <?php checked($chk); ?> style="accent-color:#1A6B8A;width:15px;height:15px;">
                                <span style="font-size:13px;"><?php echo esc_html($l); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" name="dental_save_profile" class="dc-btn dc-btn-primary">💾 ذخیره</button>
                </form>
            </div>
        </div>
        <?php
    }

    // ─── تب مالی ───────────────────────────────────────────────
    private function render_wallet_tab(): void {
        global $wpdb;
        $wallet = (float)get_post_meta($this->patient_id,'_wallet_balance',true);
        $txs    = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_wallet_transactions WHERE patient_id=%d ORDER BY created_at DESC LIMIT 30",
            $this->patient_id
        ));
        $can_edit = current_user_can('manage_options') || current_user_can('dental_manage_wallet');
        ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="dc-wallet-card">
                <div class="dc-wallet-label">موجودی کیف پول</div>
                <div class="dc-wallet-balance"><?php echo number_format($wallet); ?> <span style="font-size:16px;opacity:.7;">تومان</span></div>
                <?php if($can_edit): ?>
                <form method="post" style="margin-top:20px;display:flex;flex-direction:column;gap:8px;position:relative;z-index:1;">
                    <?php wp_nonce_field('dental_wallet_'.$this->patient_id); ?>
                    <input type="number" name="topup_amount" placeholder="مبلغ شارژ (تومان)" style="height:38px;border:none;border-radius:8px;padding:0 12px;font-family:Tahoma;font-size:13px;direction:rtl;">
                    <input type="text" name="topup_desc" placeholder="توضیحات (اختیاری)" style="height:38px;border:none;border-radius:8px;padding:0 12px;font-family:Tahoma;font-size:13px;direction:rtl;">
                    <button type="submit" name="dental_topup_wallet" style="height:38px;background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.4);border-radius:8px;color:#fff;font-family:Tahoma;font-size:13px;font-weight:700;cursor:pointer;">➕ شارژ</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="dc-card">
                <div class="dc-card-header"><h3 class="dc-heading-4">تراکنش‌های اخیر</h3></div>
                <div style="max-height:320px;overflow-y:auto;">
                    <?php if(empty($txs)): ?>
                    <p style="padding:20px;text-align:center;color:var(--dc-neutral-500);font-size:13px;">تراکنشی ثبت نشده</p>
                    <?php else: foreach($txs as $tx):
                        $cr = $tx->transaction_type==='credit';
                        $dt = Dental_Jalali::to_jalali($tx->created_at,'Y/m/d');
                    ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid var(--dc-neutral-100);">
                        <div>
                            <div style="font-size:13px;font-weight:500;"><?php echo esc_html($tx->description?:$tx->source); ?></div>
                            <div style="font-size:11px;color:var(--dc-neutral-500);"><?php echo esc_html($dt); ?></div>
                        </div>
                        <div style="font-weight:700;color:<?php echo $cr?'#22A87F':'#E05252'; ?>;">
                            <?php echo $cr?'+':'-'; ?><?php echo number_format($tx->amount); ?> ت
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    // ─── Save ───────────────────────────────────────────────────
    private function save_profile(): void {
        $tab = sanitize_key($_POST['dental_tab'] ?? 'info');

        if($tab === 'info') {
            $name   = sanitize_text_field($_POST['patient_name']    ?? '');
            $mobile = sanitize_text_field($_POST['patient_mobile']   ?? '');
            $gender = sanitize_text_field($_POST['patient_gender']   ?? '');
            $dob    = sanitize_text_field($_POST['patient_dob']      ?? '');
            $allergy= sanitize_text_field($_POST['patient_allergy']  ?? '');
            $notes  = sanitize_textarea_field($_POST['patient_notes']?? '');

            if($name) wp_update_post(['ID'=>$this->patient_id,'post_title'=>$name]);

            if($mobile) {
                // ─── جلوگیری از شماره موبایل تکراری بین دو پرونده مختلف ──
                global $wpdb;
                $duplicate_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT pm.post_id FROM {$wpdb->postmeta} pm
                     JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                     WHERE pm.meta_key='_patient_mobile' AND pm.meta_value=%s
                     AND pm.post_id != %d AND p.post_type='dental_patient' AND p.post_status='publish'
                     LIMIT 1",
                    $mobile, $this->patient_id
                ));
                if ($duplicate_id) {
                    $dup_name = get_the_title($duplicate_id);
                    $this->mobile_error = "این شماره موبایل قبلاً برای بیمار «{$dup_name}» (شناسه #{$duplicate_id}) ثبت شده است. لطفاً شماره دیگری وارد کنید یا ابتدا آن پرونده را اصلاح کنید.";
                } else {
                    update_post_meta($this->patient_id,'_patient_mobile',$mobile);
                }
            }
            update_post_meta($this->patient_id,'_patient_gender',$gender);
            update_post_meta($this->patient_id,'_patient_dob_jalali',$dob);
            update_post_meta($this->patient_id,'_drug_allergies',$allergy);
            update_post_meta($this->patient_id,'_patient_notes',$notes);
            // ─── کد ملی — قبلاً هیچ‌جا امکان وارد‌کردنش نبود، با اینکه
            // توی فاکتور/نسخه/مدارک بیمه همیشه خونده و چاپ می‌شد (همیشه
            // خالی). فقط عدد و حداکثر ۱۰ رقم قبول می‌شه.
            $national_id = preg_replace('/[^0-9]/', '', $_POST['patient_national_id'] ?? '');
            update_post_meta($this->patient_id,'_patient_national_id', substr($national_id, 0, 10));

            $wp_user_id = (int)($_POST['patient_wp_user_id'] ?? 0);
            if($wp_user_id) update_post_meta($this->patient_id,'_patient_wp_user_id',$wp_user_id);
            else delete_post_meta($this->patient_id,'_patient_wp_user_id');
        }

        if($tab === 'medical') {
            $diseases = array_map('sanitize_text_field',$_POST['diseases']??[]);
            $allergy  = sanitize_text_field($_POST['patient_allergy']??'');
            update_post_meta($this->patient_id,'_systemic_diseases',wp_json_encode($diseases));
            update_post_meta($this->patient_id,'_drug_allergies',$allergy);
        }

        // ─── انتقال پیام خطای شماره تکراری از طریق transient ───────
        // (چون بعد از این همیشه ریدایرکت می‌شود و property کلاس از بین می‌رود)
        if ($this->mobile_error) {
            set_transient('dental_mobile_error_' . get_current_user_id(), $this->mobile_error, 30);
            wp_safe_redirect(admin_url("admin.php?page=dental-patients&action=profile&id={$this->patient_id}&tab={$tab}&mobile_error=1"));
            exit;
        }

        wp_safe_redirect(admin_url("admin.php?page=dental-patients&action=profile&id={$this->patient_id}&tab={$tab}&saved=1"));
        exit;
    }

    private function do_topup(): void {
        $amount = (float)($_POST['topup_amount']??0);
        $desc   = sanitize_text_field($_POST['topup_desc']??'');
        if($amount<=0) return;
        global $wpdb;
        $wallet      = (float)get_post_meta($this->patient_id,'_wallet_balance',true);
        $new_balance = $wallet + $amount;
        update_post_meta($this->patient_id,'_wallet_balance',$new_balance);
        $wpdb->insert($wpdb->prefix.'dental_wallet_transactions',[
            'patient_id'=>$this->patient_id,'transaction_type'=>'credit',
            'amount'=>$amount,'balance_after'=>$new_balance,'source'=>'top_up',
            'description'=>$desc?:'شارژ توسط ادمین',
            'created_at'=>current_time('mysql'),'created_by'=>get_current_user_id(),
        ],['%d','%s','%f','%f','%s','%s','%s','%d']);
        wp_safe_redirect(admin_url("admin.php?page=dental-patients&action=profile&id={$this->patient_id}&tab=wallet&saved=1"));
        exit;
    }

    private function get_tooth_conditions(): array {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dental_tooth_conditions WHERE patient_id=%d AND is_active=1",
            $this->patient_id
        ),ARRAY_A);
        $done_map = get_post_meta($this->patient_id,'_chart_done_map',true)?:[];
        $result   = [];
        foreach($rows as $row){
            $key        = $row['tooth_number'].'_'.$row['tooth_type'];
            $treatments = json_decode($row['tooth_surface']?:'[]',true)?:[];
            $tdone = []; $tdoc = [];
            foreach($done_map as $dk=>$dv){
                if(strpos($dk,$key.'_')===0){
                    $code=$substr=substr($dk,strlen($key)+1);
                    $tdone[$code]=$dv['done']??0;
                    $tdoc[$code] =$dv['doctor_id']??0;
                }
            }
            $result[$key]=[
                'tooth_number'=>$row['tooth_number'],'tooth_type'=>$row['tooth_type'],
                'display_color'=>$row['condition_code'],'treatments'=>$treatments,
                'notes'=>$row['notes'],'exam_doctor_id'=>$row['recorded_by'],
                'is_erupted'=>$row['is_erupted'],'done_map'=>$tdone,'done_doctor_map'=>$tdoc,
            ];
        }
        return $result;
    }

    private function get_doctors(): array {
        $users = get_users(['role__in'=>['dental_doctor','dental_admin','administrator'],'fields'=>['ID','display_name']]);
        return array_map(fn($u)=>['id'=>$u->ID,'name'=>$u->display_name],$users);
    }
}
