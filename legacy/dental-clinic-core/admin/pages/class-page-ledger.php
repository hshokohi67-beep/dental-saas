<?php
defined('ABSPATH') || exit;

class Dental_Page_Ledger {

    // ─── رفع ریشه‌ای همون باگ «صفحه‌ی سفید بعد از ذخیره» — این‌بار
    // برای دفتر روزانه. کل منطق ذخیره/حذف/تأیید که قبلاً داخل render()
    // بود (و همیشه با «headers already sent» شکست می‌خورد، چون render()
    // دیر صدا زده می‌شه)، به این متد مستقل منتقل شد تا از admin_init
    // (زودهنگام، قبل از هر HTML) اجرا بشه.
    public function maybe_handle_post(): void {
        $cu = wp_get_current_user();
        if (!current_user_can('manage_options') && !array_intersect((array)$cu->roles, ['dental_admin','dental_financial','dental_secretary'])) {
            return; // چک واقعی دسترسی توی render() هم هست؛ اینجا فقط از اجرای بی‌مجوز جلوگیری می‌کنیم
        }

        if (isset($_GET['export_ledger_csv']) && wp_verify_nonce($_GET['_wpnonce'] ?? '', 'dental_export_ledger')) {
            $this->export_ledger_csv();
        }

        // ─── ذخیره / حذف ──────────────────────────────────────
        if (isset($_POST['dental_save_ledger']) && check_admin_referer('dental_ledger_save')) {
            Dental_Ledger_Manager::create($_POST);
            wp_safe_redirect(add_query_arg(['tab'=>'daily','date'=>$_POST['entry_date_jalali']??'','saved'=>1], remove_query_arg(['saved']))); exit;
        }
        if (isset($_POST['dental_update_ledger']) && check_admin_referer('dental_ledger_update')) {
            Dental_Ledger_Manager::update((int)$_POST['ledger_id'], $_POST);
            wp_safe_redirect(add_query_arg('saved','1')); exit;
        }
        if (isset($_GET['delete_ledger']) && check_admin_referer('delete_ledger_'.$_GET['delete_ledger'])) {
            Dental_Ledger_Manager::delete((int)$_GET['delete_ledger']);
            wp_safe_redirect(remove_query_arg(['delete_ledger','_wpnonce'])); exit;
        }
        // ─── بررسی و دریافت وجه کامل — تخفیف + یا پرداخت کامل یا
        // قسط‌بندی، همه یکجا و متصل به هم (نه دو مرحله‌ی جدا) ─────────
        if (isset($_POST['dental_confirm_ledger_full']) && check_admin_referer('dental_confirm_ledger_full')) {
            $ledger_id  = (int)$_POST['ledger_id'];
            $patient_id = (int)$_POST['patient_id'];
            $charged    = (float)$_POST['charged_amount'];
            $discount   = (float)($_POST['discount'] ?? 0);
            $final      = max(0, $charged - $discount);
            $is_installment = !empty($_POST['is_installment']) && $_POST['is_installment'] === '1';

            if ($is_installment) {
                $down     = (float)($_POST['down_payment'] ?? 0);
                $count    = max(1, (int)($_POST['installment_count'] ?? 1));
                $dp_method= sanitize_key($_POST['down_payment_method'] ?? 'cash');

                // ─── دقیقاً همون treatment_id که از کاتالوگ اومده، رد
                // می‌شه — پلن قسطی به رکورد اصلی ثبت خدمت وصل می‌مونه ──
                $ledger_row = Dental_Ledger_Manager::get($ledger_id);
                $treatment_id = (int)($ledger_row['catalog_treatment_id'] ?? 0);

                // ─── رفع باگ مهم: create() خودش تخفیف رو کم می‌کنه (چون
                // صفحه‌ی «مالی و اقساط» دستی هم همینو انتظار داره) — پس
                // اینجا باید مبلغ خام ($charged) بفرستیم، نه $final که
                // از قبل تخفیف خورده، وگرنه تخفیف دوبار کم می‌شه.
                $result = Dental_Installment_Manager::create(
                    $patient_id, $treatment_id, $charged, $down, $count, '', $discount,
                    'ساخته‌شده از دفتر روزانه — ' . ($ledger_row['treatment_title'] ?? ''),
                    $dp_method, $ledger_id
                );

                if (!$result['success']) {
                    wp_die('خطا در ساخت پلن قسطی: ' . esc_html($result['message']));
                }
                // رکورد دفتر روزانه رو با همون مبلغ پیش‌پرداخت (نه کل) تأیید کن
                Dental_Ledger_Manager::confirm($ledger_id, $down, $discount, 'installment');

                if (class_exists('Dental_Audit_Log')) {
                    Dental_Audit_Log::log('installment_create',
                        "پلن قسطی برای «{$ledger_row['treatment_title']}» ساخته شد — {$count} قسط، پیش‌پرداخت {$down} تومان" . ($discount>0?" (تخفیف {$discount} تومان)":''),
                        ['entity_type'=>'installment', 'entity_id'=>$result['installment_id'], 'amount'=>$final]
                    );
                }

            } else {
                $received = (float)($_POST['amount_received'] ?? $final);
                $method   = sanitize_key($_POST['payment_method'] ?? 'cash');

                if ($method === 'wallet') {
                    $wallet_result = Dental_Patient_Wallet::deduct($patient_id, $received, 'ledger_payment', $ledger_id, 'پرداخت مستقیم دفتر روزانه');
                    if (!$wallet_result['success']) {
                        wp_die('خطا: ' . esc_html($wallet_result['message']));
                    }
                }
                Dental_Ledger_Manager::confirm($ledger_id, $received, $discount, $method);

                if (class_exists('Dental_Audit_Log')) {
                    $method_fa = ['cash'=>'نقدی','card'=>'کارتخوان','wallet'=>'کیف پول'][$method] ?? $method;
                    Dental_Audit_Log::log('ledger_confirm',
                        "دریافت {$received} تومان ({$method_fa})" . ($discount>0?" با {$discount} تومان تخفیف":'') . " برای رکورد دفتر روزانه #{$ledger_id}",
                        ['entity_type'=>'ledger', 'entity_id'=>$ledger_id, 'amount'=>$received]
                    );
                }
            }

            wp_safe_redirect(add_query_arg(['tab'=>'daily','date'=>current_time('Y-m-d'),'saved'=>1])); exit;
        }

        // ─── بررسی و دریافت وجه چندتایی — چندکار یه بیمار با هم انتخاب
        // می‌شن، جمع می‌بندن، و مبلغ/تخفیف بین همه‌شون به‌نسبت مبلغ هر
        // کدوم توزیع می‌شه (تا گزارش‌های تک‌تک هم درست بمونن) ──────────
        if (isset($_POST['dental_confirm_ledger_multi']) && check_admin_referer('dental_confirm_ledger_full')) {
            $ledger_ids = array_filter(array_map('intval', explode(',', $_POST['ledger_ids'] ?? '')));
            $patient_id = (int)$_POST['patient_id'];
            $charged    = (float)$_POST['charged_amount'];
            $discount   = (float)($_POST['discount'] ?? 0);
            $is_installment = !empty($_POST['is_installment']) && $_POST['is_installment'] === '1';

            if (empty($ledger_ids) || $charged <= 0) {
                wp_die('اطلاعات ناقص است.');
            }

            $rows = array_map(fn($id) => Dental_Ledger_Manager::get($id), $ledger_ids);
            $rows = array_filter($rows);
            $titles = implode('، ', array_column($rows, 'treatment_title'));

            if ($is_installment) {
                $down      = (float)($_POST['down_payment'] ?? 0);
                $count     = max(1, (int)($_POST['installment_count'] ?? 1));
                $dp_method = sanitize_key($_POST['down_payment_method'] ?? 'cash');
                $first_treatment_id = (int)($rows[array_key_first($rows)]['catalog_treatment_id'] ?? 0);

                $result = Dental_Installment_Manager::create(
                    $patient_id, $first_treatment_id, $charged, $down, $count, '', $discount,
                    'ساخته‌شده از دفتر روزانه (چندتایی) — ' . $titles,
                    $dp_method, $ledger_ids[0]
                );
                if (!$result['success']) {
                    wp_die('خطا در ساخت پلن قسطی: ' . esc_html($result['message']));
                }

                foreach ($rows as $r) {
                    $share = $charged > 0 ? ((float)$r['amount_charged'] / $charged) : 0;
                    Dental_Ledger_Manager::confirm((int)$r['id'], round($down * $share), round($discount * $share), 'installment');
                }

                if (class_exists('Dental_Audit_Log')) {
                    Dental_Audit_Log::log('installment_create',
                        "پلن قسطی چندتایی ({$titles}) ساخته شد — {$count} قسط، پیش‌پرداخت {$down} تومان",
                        ['entity_type'=>'installment', 'entity_id'=>$result['installment_id'], 'amount'=>$charged]
                    );
                }

            } else {
                $received = (float)($_POST['amount_received'] ?? max(0, $charged - $discount));
                $method   = sanitize_key($_POST['payment_method'] ?? 'cash');

                if ($method === 'wallet') {
                    $wallet_result = Dental_Patient_Wallet::deduct($patient_id, $received, 'ledger_payment', 0, 'پرداخت چندتایی دفتر روزانه');
                    if (!$wallet_result['success']) {
                        wp_die('خطا: ' . esc_html($wallet_result['message']));
                    }
                }

                foreach ($rows as $r) {
                    $share = $charged > 0 ? ((float)$r['amount_charged'] / $charged) : 0;
                    Dental_Ledger_Manager::confirm((int)$r['id'], round($received * $share), round($discount * $share), $method);
                }

                if (class_exists('Dental_Audit_Log')) {
                    $method_fa = ['cash'=>'نقدی','card'=>'کارتخوان','wallet'=>'کیف پول'][$method] ?? $method;
                    Dental_Audit_Log::log('ledger_confirm',
                        "دریافت چندتایی {$received} تومان ({$method_fa}) برای: {$titles}",
                        ['entity_type'=>'ledger', 'entity_id'=>$ledger_ids[0], 'amount'=>$received]
                    );
                }
            }

            wp_safe_redirect(add_query_arg(['tab'=>'daily','date'=>current_time('Y-m-d'),'saved'=>1])); exit;
        }

        // ─── تأیید مالی رکورد خودکار از کاتالوگ (نسخه‌ی قدیمی، ساده) ──
        if (isset($_POST['dental_confirm_ledger']) && check_admin_referer('dental_confirm_ledger')) {
            $received = !empty($_POST['amount_received']) ? (float)$_POST['amount_received'] : null;
            Dental_Ledger_Manager::confirm((int)$_POST['ledger_id'], $received);
            wp_safe_redirect(add_query_arg(['tab'=>'daily','date'=>current_time('Y-m-d'),'saved'=>1])); exit;
        }
    }

    public function render(): void {
        $cu = wp_get_current_user();
        if (!current_user_can('manage_options') && !array_intersect((array)$cu->roles, ['dental_admin','dental_financial','dental_secretary'])) {
            wp_die('این بخش فقط برای مدیر کلینیک، مسئول مالی، و منشی قابل‌دسترسه.');
        }
        $tab = sanitize_key($_GET['tab'] ?? 'daily');
        ?>
        <div class="dental-admin-wrap">
            <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
                <i data-lucide="book-text" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                دفتر روزانه درمان
            </h1>

            <?php if(isset($_GET['saved'])): ?>
            <div style="background:var(--dc-accent-light);border:1px solid var(--dc-accent);border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:13px;">✅ ثبت شد.</div>
            <?php endif; ?>

            <div class="dc-tabs" style="margin-bottom:20px;">
                <a href="<?php echo esc_url(add_query_arg('tab','daily')); ?>" class="dc-tab <?php echo $tab==='daily'?'active':''; ?>">
                    <i data-lucide="calendar-days" style="width:14px;height:14px;"></i> ثبت روزانه
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab','pending')); ?>" class="dc-tab <?php echo $tab==='pending'?'active':''; ?>">
                    <i data-lucide="clock" style="width:14px;height:14px;"></i> در انتظار تأیید مالی
                    <?php $pc = count(Dental_Ledger_Manager::get_pending()); if($pc): ?>
                    <span class="dc-badge dc-badge-danger"><?php echo $pc; ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg('tab','reports')); ?>" class="dc-tab <?php echo $tab==='reports'?'active':''; ?>">
                    <i data-lucide="bar-chart-3" style="width:14px;height:14px;"></i> گزارش‌ها
                </a>
            </div>

            <?php
            match($tab) {
                'pending' => $this->render_pending(),
                'reports' => $this->render_reports(),
                default   => $this->render_daily(),
            };
            ?>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }

    // ─── تب ثبت روزانه ───────────────────────────────────────
    // ─── تب: در انتظار تأیید مالی (رکوردهای خودکار از کاتالوگ) ────
    private function render_pending(): void {
        $pending = Dental_Ledger_Manager::get_pending();
        // ─── گروه‌بندی بر اساس بیمار — برای دکمه‌ی «انتخاب همه‌ی کارهای
        // این بیمار» که کنار هرکدوم می‌ذاریم ──────────────────────────
        $by_patient = [];
        foreach ($pending as $p) { $by_patient[$p['patient_id']][] = $p['id']; }
        ?>
        <div class="dc-card">
            <div class="dc-card-header">
                <h3 class="dc-heading-4">⏳ رکوردهای در انتظار تأیید (خودکار از کاتالوگ خدمات)</h3>
                <div id="dc-multi-bar" style="display:none;align-items:center;gap:10px;">
                    <span id="dc-multi-count" style="font-size:12px;color:var(--dc-primary);font-weight:700;"></span>
                    <button type="button" class="dc-btn dc-btn-primary dc-btn-sm" onclick="dcOpenMultiConfirmModal()">✅ بررسی و دریافت وجه (چندتایی)</button>
                </div>
            </div>
            <div style="padding:0;">
                <?php if (empty($pending)): ?>
                <p style="text-align:center;color:var(--dc-neutral-400);padding:32px;">چیزی در انتظار تأیید نیست 👍</p>
                <?php else: foreach($pending as $p): ?>
                <div style="padding:16px 20px;border-bottom:1px solid var(--dc-neutral-100);">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <input type="checkbox" class="dc-pending-chk" data-id="<?php echo (int)$p['id']; ?>" data-patient="<?php echo (int)$p['patient_id']; ?>"
                                data-patient-name="<?php echo esc_attr($p['patient_name']); ?>" data-amount="<?php echo (int)$p['amount_charged']; ?>"
                                data-title="<?php echo esc_attr($p['treatment_title']); ?>" onchange="dcUpdateMultiBar()" style="width:18px;height:18px;">
                            <div>
                                <div style="font-weight:700;font-size:14px;"><?php echo esc_html($p['patient_name']); ?></div>
                                <div style="font-size:12px;color:var(--dc-neutral-500);">
                                    <?php echo esc_html($p['treatment_title']); ?> — توسط <?php echo esc_html($p['doctor_name']); ?>
                                </div>
                                <div style="font-size:12px;color:var(--dc-primary);font-weight:700;margin-top:4px;">
                                    💰 مبلغ: <?php echo number_format($p['amount_charged']); ?> تومان
                                </div>
                            </div>
                        </div>
                        <button type="button" class="dc-btn dc-btn-success dc-btn-sm"
                            onclick="dcOpenConfirmModal(<?php echo (int)$p['id']; ?>,<?php echo (int)$p['patient_id']; ?>,<?php echo (int)$p['amount_charged']; ?>,'<?php echo esc_js($p['treatment_title']); ?>')">
                            ✅ بررسی و دریافت وجه
                        </button>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- مودال تأیید چندتایی -->
        <div id="dc-multi-confirm-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.8);z-index:999999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:16px;padding:22px;width:100%;max-width:460px;max-height:88vh;overflow-y:auto;">
                <div style="font-weight:700;font-size:14px;margin-bottom:4px;">✅ تأیید و دریافت وجه — چند مورد</div>
                <div id="dc-multi-items-list" style="font-size:12px;color:var(--dc-neutral-500);margin-bottom:14px;max-height:120px;overflow-y:auto;background:var(--dc-neutral-50);border-radius:8px;padding:10px;"></div>
                <div style="font-size:12px;color:var(--dc-neutral-500);margin-bottom:4px;">جمع مبلغ کارها: <b id="dc-multi-total" style="color:var(--dc-neutral-700);"></b> تومان</div>

                <form method="post" id="dc-multi-confirm-form">
                    <?php wp_nonce_field('dental_confirm_ledger_full'); ?>
                    <input type="hidden" name="ledger_ids" id="dc-multi-ledger-ids">
                    <input type="hidden" name="patient_id" id="dc-multi-patient-id">
                    <input type="hidden" name="charged_amount" id="dc-multi-charged">

                    <div class="dc-form-group" style="margin:14px 0;">
                        <label class="dc-label">تخفیف (تومان، اختیاری — روی کل جمع اعمال می‌شه)</label>
                        <input type="number" name="discount" id="dc-multi-discount" class="dc-input" value="0" oninput="dcRecalcMultiFinal()">
                    </div>
                    <div style="background:var(--dc-primary-light);border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;">
                        مبلغ نهایی: <b id="dc-multi-final" style="color:var(--dc-primary);"></b> تومان
                    </div>

                    <div class="dc-form-group" style="margin-bottom:14px;">
                        <label class="dc-label">آیا قسط‌بندی بشه؟</label>
                        <div style="display:flex;gap:16px;font-size:13px;">
                            <label><input type="radio" name="is_installment" value="0" checked onchange="dcToggleMultiInstallment(false)"> خیر — پرداخت همین الان</label>
                            <label><input type="radio" name="is_installment" value="1" onchange="dcToggleMultiInstallment(true)"> بله — قسط‌بندی</label>
                        </div>
                    </div>

                    <div id="dc-multi-full-pay">
                        <div class="dc-form-group" style="margin-bottom:14px;">
                            <label class="dc-label">مبلغ دریافتی</label>
                            <input type="number" name="amount_received" id="dc-multi-received" class="dc-input">
                        </div>
                        <div class="dc-form-group" style="margin-bottom:14px;">
                            <label class="dc-label">روش پرداخت</label>
                            <select name="payment_method" id="dc-multi-method" class="dc-select">
                                <option value="cash">نقدی</option>
                                <option value="card">کارتخوان (دستی ثبت شد)</option>
                                <option value="wallet">کیف پول بیمار</option>
                            </select>
                        </div>
                        <button type="button" onclick="dcSendMultiToPos()" class="dc-btn dc-btn-secondary dc-btn-sm" style="margin-bottom:14px;">💳 ارسال مبلغ به کارتخوان</button>
                        <div id="dc-multi-pos-result" style="font-size:12px;margin-bottom:10px;"></div>
                    </div>

                    <div id="dc-multi-installment" style="display:none;">
                        <div class="dc-grid dc-grid-2" style="gap:10px;margin-bottom:14px;">
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">پیش‌پرداخت</label>
                                <input type="number" name="down_payment" id="dc-multi-down" class="dc-input" value="0">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">تعداد قسط</label>
                                <input type="number" name="installment_count" class="dc-input" value="3" min="1">
                            </div>
                        </div>
                        <div class="dc-form-group" style="margin-bottom:14px;">
                            <label class="dc-label">روش دریافت پیش‌پرداخت</label>
                            <select name="down_payment_method" class="dc-select">
                                <option value="cash">نقدی</option>
                                <option value="card">کارتخوان (دستی ثبت شد)</option>
                                <option value="wallet">کیف پول بیمار</option>
                            </select>
                        </div>
                        <button type="button" onclick="dcSendMultiToPos()" class="dc-btn dc-btn-secondary dc-btn-sm" style="margin-bottom:14px;">💳 ارسال پیش‌پرداخت به کارتخوان</button>
                    </div>

                    <div style="display:flex;gap:8px;margin-top:10px;">
                        <button type="submit" name="dental_confirm_ledger_multi" class="dc-btn dc-btn-primary" style="flex:1;">✅ ثبت نهایی همه</button>
                        <button type="button" onclick="document.getElementById('dc-multi-confirm-modal').style.display='none'" class="dc-btn dc-btn-ghost">انصراف</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        function dcUpdateMultiBar(){
            var checked = document.querySelectorAll('.dc-pending-chk:checked');
            var bar = document.getElementById('dc-multi-bar');
            if (checked.length < 2) { bar.style.display = 'none'; return; }
            // همه باید مال یه بیمار باشن — چک کن
            var patientIds = Array.from(checked).map(c=>c.dataset.patient);
            var uniquePatients = [...new Set(patientIds)];
            if (uniquePatients.length > 1) {
                alert('فقط می‌تونید کارهای یه بیمار رو با هم انتخاب کنید. لطفاً انتخاب‌های بیمار دیگه رو بردارید.');
                event.target.checked = false;
                dcUpdateMultiBar();
                return;
            }
            bar.style.display = 'flex';
            document.getElementById('dc-multi-count').textContent = checked.length + ' مورد انتخاب شد';
        }
        function dcOpenMultiConfirmModal(){
            var checked = document.querySelectorAll('.dc-pending-chk:checked');
            if (checked.length < 2) return;
            var ids = [], total = 0, patientId = checked[0].dataset.patient, patientName = checked[0].dataset.patientName;
            var listHtml = '';
            checked.forEach(function(c){
                ids.push(c.dataset.id);
                total += parseFloat(c.dataset.amount);
                listHtml += '• ' + c.dataset.title + ' — ' + Number(c.dataset.amount).toLocaleString() + ' تومان<br>';
            });
            document.getElementById('dc-multi-items-list').innerHTML = '<b>' + patientName + '</b><br>' + listHtml;
            document.getElementById('dc-multi-ledger-ids').value = ids.join(',');
            document.getElementById('dc-multi-patient-id').value = patientId;
            document.getElementById('dc-multi-charged').value = total;
            document.getElementById('dc-multi-total').textContent = total.toLocaleString();
            document.getElementById('dc-multi-discount').value = 0;
            document.getElementById('dc-multi-received').value = total;
            document.getElementById('dc-multi-down').value = 0;
            dcRecalcMultiFinal();
            document.getElementById('dc-multi-confirm-modal').style.display = 'flex';
        }
        function dcRecalcMultiFinal(){
            var charged = parseFloat(document.getElementById('dc-multi-charged').value) || 0;
            var discount = parseFloat(document.getElementById('dc-multi-discount').value) || 0;
            var final = Math.max(0, charged - discount);
            document.getElementById('dc-multi-final').textContent = final.toLocaleString();
            document.getElementById('dc-multi-received').value = final;
        }
        function dcToggleMultiInstallment(isInstallment){
            document.getElementById('dc-multi-full-pay').style.display = isInstallment ? 'none' : 'block';
            document.getElementById('dc-multi-installment').style.display = isInstallment ? 'block' : 'none';
        }
        function dcSendMultiToPos(){
            var amount = document.getElementById('dc-multi-installment').style.display === 'block'
                ? (document.getElementById('dc-multi-down').value || 0)
                : (document.getElementById('dc-multi-received').value || 0);
            var patientId = document.getElementById('dc-multi-patient-id').value;
            var box = document.getElementById('dc-multi-pos-result');
            box.textContent = '⏳ در حال ارسال به دستگاه...';
            var fd = new FormData();
            fd.append('action','dental_pos_charge');
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
            fd.append('amount', amount);
            fd.append('patient_id', patientId);
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                .then(r=>r.json()).then(function(res){
                    box.innerHTML = res.success
                        ? '<span style="color:var(--dc-accent-dark);">✅ به دستگاه ارسال شد.</span>'
                        : '<span style="color:var(--dc-danger);">❌ ' + (res.data && res.data.message ? res.data.message : 'خطا') + '</span>';
                });
        }
        </script>

        <!-- مودال بررسی و دریافت — تخفیف، قسط‌بندی یا پرداخت کامل -->
        <div id="dc-confirm-modal" style="display:none;position:fixed;inset:0;background:rgba(15,42,56,.8);z-index:999999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:16px;padding:22px;width:100%;max-width:460px;max-height:88vh;overflow-y:auto;">
                <div style="font-weight:700;font-size:14px;margin-bottom:4px;" id="dc-cm-title"></div>
                <div style="font-size:12px;color:var(--dc-neutral-500);margin-bottom:16px;">مبلغ خدمت: <b id="dc-cm-amount"></b> تومان</div>

                <form method="post" id="dc-confirm-form">
                    <?php wp_nonce_field('dental_confirm_ledger_full'); ?>
                    <input type="hidden" name="ledger_id" id="dc-cm-ledger-id">
                    <input type="hidden" name="patient_id" id="dc-cm-patient-id">
                    <input type="hidden" name="charged_amount" id="dc-cm-charged">

                    <div class="dc-form-group" style="margin-bottom:14px;">
                        <label class="dc-label">تخفیف (تومان، اختیاری)</label>
                        <input type="number" name="discount" id="dc-cm-discount" class="dc-input" value="0" oninput="dcRecalcFinal()">
                    </div>

                    <div style="background:var(--dc-primary-light);border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;">
                        مبلغ نهایی: <b id="dc-cm-final" style="color:var(--dc-primary);"></b> تومان
                    </div>

                    <div class="dc-form-group" style="margin-bottom:14px;">
                        <label class="dc-label">آیا قسط‌بندی بشه؟</label>
                        <div style="display:flex;gap:16px;font-size:13px;">
                            <label><input type="radio" name="is_installment" value="0" checked onchange="dcToggleInstallment(false)"> خیر — پرداخت همین الان</label>
                            <label><input type="radio" name="is_installment" value="1" onchange="dcToggleInstallment(true)"> بله — قسط‌بندی</label>
                        </div>
                    </div>

                    <!-- حالت پرداخت کامل/جزئی همین الان -->
                    <div id="dc-cm-full-pay">
                        <div class="dc-form-group" style="margin-bottom:14px;">
                            <label class="dc-label">مبلغ دریافتی</label>
                            <input type="number" name="amount_received" id="dc-cm-received" class="dc-input">
                        </div>
                        <div class="dc-form-group" style="margin-bottom:14px;">
                            <label class="dc-label">روش پرداخت</label>
                            <select name="payment_method" id="dc-cm-method" class="dc-select">
                                <option value="cash">نقدی</option>
                                <option value="card">کارتخوان (دستی ثبت شد)</option>
                                <option value="wallet">کیف پول بیمار</option>
                            </select>
                        </div>
                        <button type="button" onclick="dcSendToPos()" class="dc-btn dc-btn-secondary dc-btn-sm" style="margin-bottom:14px;">💳 ارسال مبلغ به کارتخوان</button>
                        <div id="dc-pos-result" style="font-size:12px;margin-bottom:10px;"></div>
                    </div>

                    <!-- حالت قسط‌بندی -->
                    <div id="dc-cm-installment" style="display:none;">
                        <div class="dc-grid dc-grid-2" style="gap:10px;margin-bottom:14px;">
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">پیش‌پرداخت</label>
                                <input type="number" name="down_payment" id="dc-cm-down" class="dc-input" value="0">
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">تعداد قسط</label>
                                <input type="number" name="installment_count" class="dc-input" value="3" min="1">
                            </div>
                        </div>
                        <div class="dc-form-group" style="margin-bottom:14px;">
                            <label class="dc-label">روش دریافت پیش‌پرداخت (اگه بالای صفره)</label>
                            <select name="down_payment_method" class="dc-select">
                                <option value="cash">نقدی</option>
                                <option value="card">کارتخوان (دستی ثبت شد)</option>
                                <option value="wallet">کیف پول بیمار</option>
                            </select>
                        </div>
                        <button type="button" onclick="dcSendToPos()" class="dc-btn dc-btn-secondary dc-btn-sm" style="margin-bottom:14px;">💳 ارسال پیش‌پرداخت به کارتخوان</button>
                    </div>

                    <div style="display:flex;gap:8px;margin-top:10px;">
                        <button type="submit" name="dental_confirm_ledger_full" class="dc-btn dc-btn-primary" style="flex:1;">✅ ثبت نهایی</button>
                        <button type="button" onclick="document.getElementById('dc-confirm-modal').style.display='none'" class="dc-btn dc-btn-ghost">انصراف</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        function dcOpenConfirmModal(ledgerId, patientId, amount, title){
            document.getElementById('dc-cm-title').textContent = title;
            document.getElementById('dc-cm-amount').textContent = amount.toLocaleString();
            document.getElementById('dc-cm-ledger-id').value = ledgerId;
            document.getElementById('dc-cm-patient-id').value = patientId;
            document.getElementById('dc-cm-charged').value = amount;
            document.getElementById('dc-cm-discount').value = 0;
            document.getElementById('dc-cm-received').value = amount;
            document.getElementById('dc-cm-down').value = 0;
            dcRecalcFinal();
            document.getElementById('dc-confirm-modal').style.display = 'flex';
        }
        function dcRecalcFinal(){
            var charged = parseFloat(document.getElementById('dc-cm-charged').value) || 0;
            var discount = parseFloat(document.getElementById('dc-cm-discount').value) || 0;
            var final = Math.max(0, charged - discount);
            document.getElementById('dc-cm-final').textContent = final.toLocaleString();
            document.getElementById('dc-cm-received').value = final;
        }
        function dcToggleInstallment(isInstallment){
            document.getElementById('dc-cm-full-pay').style.display = isInstallment ? 'none' : 'block';
            document.getElementById('dc-cm-installment').style.display = isInstallment ? 'block' : 'none';
        }
        function dcSendToPos(){
            var amount = document.getElementById('dc-cm-installment').style.display === 'block'
                ? (document.getElementById('dc-cm-down').value || 0)
                : (document.getElementById('dc-cm-received').value || 0);
            var patientId = document.getElementById('dc-cm-patient-id').value;
            var box = document.getElementById('dc-pos-result');
            box.textContent = '⏳ در حال ارسال به دستگاه...';
            var fd = new FormData();
            fd.append('action','dental_pos_charge');
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
            fd.append('amount', amount);
            fd.append('patient_id', patientId);
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                .then(r=>r.json()).then(function(res){
                    box.innerHTML = res.success
                        ? '<span style="color:var(--dc-accent-dark);">✅ به دستگاه ارسال شد — منتظر تکمیل توسط بیمار باشید، بعد «ثبت نهایی» رو بزنید.</span>'
                        : '<span style="color:var(--dc-danger);">❌ ' + (res.data && res.data.message ? res.data.message : 'خطا در ارسال') + '</span>';
                });
        }
        </script>
        <?php
    }

    private function render_daily(): void {
        $today_j   = Dental_Jalali::today();
        // رفع باگ تایم‌زون: date() خام از تنظیمات تایم‌زون سرور استفاده
        // می‌کنه (معمولاً UTC)، نه تایم‌زون واقعی سایت — current_time
        // درسته و با Dental_Jalali::today() (که الان اصلاح شده) هماهنگه.
        $today_g   = current_time('Y-m-d');

        if (!empty($_GET['jdate'])) {
            // این پارامتر از تقویم شمسی می‌آید — نیاز به تبدیل دارد
            $g = Dental_Jalali::to_gregorian(sanitize_text_field($_GET['jdate']));
            $view_date = $g ?: $today_g;
        } elseif (!empty($_GET['date'])) {
            // این پارامتر از لینک‌های قبلی/بعدی می‌آید و از قبل میلادی است —
            // تبدیل دوباره باعث می‌شد چند صد سال جابه‌جا شود.
            $view_date = sanitize_text_field($_GET['date']);
        } else {
            $view_date = $today_g;
        }
        $view_j = Dental_Jalali::to_jalali($view_date, 'Y/m/d');
        $prev   = date('Y-m-d', strtotime($view_date.' -1 day'));
        $next   = date('Y-m-d', strtotime($view_date.' +1 day'));

        $entries      = Dental_Ledger_Manager::get_by_date($view_date);
        $logged_appts = Dental_Ledger_Manager::get_logged_appointment_ids($view_date);

        $total_charged  = array_sum(array_column($entries, 'amount_charged'));
        $total_received = array_sum(array_column($entries, 'amount_received'));
        $total_due      = $total_charged - $total_received;

        // نوبت‌های امروز از پلاگین نوبت‌دهی (در صورت فعال بودن)
        $appts = [];
        $booking_active = class_exists('Dental_Booking_Appointment');
        if ($booking_active) {
            $appts = Dental_Booking_Appointment::get_day_appointments($view_date);
        }

        $patients = get_posts(['post_type'=>'dental_patient','posts_per_page'=>-1,'post_status'=>'publish','orderby'=>'title','order'=>'ASC']);
        ?>
        <!-- ناوبری تاریخ -->
        <div class="dc-card" style="margin-bottom:18px;">
            <div class="dc-card-body" style="padding:14px 18px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <a href="<?php echo esc_url(add_query_arg(['tab'=>'daily','date'=>$prev])); ?>" class="dc-btn dc-btn-ghost dc-btn-sm">
                            <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
                        </a>
                        <div style="text-align:center;">
                            <div style="font-size:18px;font-weight:700;color:var(--dc-primary);"><?php echo esc_html($view_j); ?></div>
                            <div style="font-size:11px;color:var(--dc-neutral-500);"><?php echo esc_html($view_date); ?></div>
                        </div>
                        <a href="<?php echo esc_url(add_query_arg(['tab'=>'daily','date'=>$next])); ?>" class="dc-btn dc-btn-ghost dc-btn-sm">
                            <i data-lucide="chevron-left" style="width:14px;height:14px;"></i>
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(['tab'=>'daily','date'=>$today_g])); ?>" class="dc-btn dc-btn-secondary dc-btn-sm">امروز</a>
                    </div>
                    <form method="get" style="display:flex;gap:6px;">
                        <input type="hidden" name="page" value="dental-financial">
                        <input type="hidden" name="section" value="ledger">
                        <input type="hidden" name="tab" value="daily">
                        <input type="text" name="jdate" class="dc-datepicker dc-input" style="width:130px;height:34px;" placeholder="رفتن به تاریخ..." dir="ltr">
                        <button type="submit" class="dc-btn dc-btn-ghost dc-btn-sm">برو</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- خلاصه مالی روز -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;">
            <div class="dc-card" style="padding:16px;border-top:3px solid var(--dc-primary);">
                <div style="font-size:12px;color:var(--dc-neutral-500);">جمع مبلغ درمان</div>
                <div style="font-size:22px;font-weight:700;color:var(--dc-primary);"><?php echo number_format($total_charged); ?></div>
            </div>
            <div class="dc-card" style="padding:16px;border-top:3px solid var(--dc-accent-dark);">
                <div style="font-size:12px;color:var(--dc-neutral-500);">جمع دریافتی</div>
                <div style="font-size:22px;font-weight:700;color:var(--dc-accent-dark);"><?php echo number_format($total_received); ?></div>
            </div>
            <div class="dc-card" style="padding:16px;border-top:3px solid <?php echo $total_due>0?'var(--dc-danger)':'var(--dc-accent-dark)'; ?>;">
                <div style="font-size:12px;color:var(--dc-neutral-500);">باقی‌مانده</div>
                <div style="font-size:22px;font-weight:700;color:<?php echo $total_due>0?'var(--dc-danger)':'var(--dc-accent-dark)'; ?>;"><?php echo number_format($total_due); ?></div>
            </div>
        </div>

        <!-- کارهای امروز (از پلاگین نوبت‌دهی) -->
        <div class="dc-card" style="margin-bottom:20px;">
            <div class="dc-card-header">
                <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="calendar-clock" style="width:15px;height:15px;color:var(--dc-primary);"></i>
                    کارهای این روز
                </h3>
            </div>
            <?php if (!$booking_active): ?>
            <div class="dc-card-body">
                <p style="font-size:12px;color:var(--dc-neutral-500);">برای نمایش خودکار نوبت‌های روز، پلاگین «نوبت‌دهی» را فعال کنید. همچنان می‌توانید از فرم پایین صفحه، ثبت دستی انجام دهید.</p>
            </div>
            <?php elseif (empty($appts)): ?>
            <div class="dc-card-body"><p style="font-size:13px;color:var(--dc-neutral-400);text-align:center;padding:16px 0;">نوبتی برای این روز ثبت نشده</p></div>
            <?php else: ?>
            <div style="padding:0;">
                <?php foreach($appts as $a):
                    $already = in_array((int)$a['id'], $logged_appts);
                ?>
                <div style="display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid #F5F5F5;flex-wrap:wrap;">
                    <div style="min-width:60px;font-weight:700;color:var(--dc-primary);direction:ltr;"><?php echo esc_html(substr($a['start_time'],0,5)); ?></div>
                    <div style="flex:1;">
                        <div style="font-size:13px;font-weight:600;"><?php echo esc_html($a['patient_name']); ?></div>
                        <div style="font-size:11px;color:var(--dc-neutral-500);"><?php echo esc_html($a['service_title']??'—'); ?> — <?php echo esc_html($a['doctor_name']); ?></div>
                    </div>
                    <?php if ($already): ?>
                    <span style="background:var(--dc-accent-light);color:var(--dc-accent-dark);border-radius:10px;padding:4px 12px;font-size:11px;font-weight:700;">
                        <i data-lucide="check" style="width:11px;height:11px;vertical-align:middle;"></i> ثبت شده
                    </span>
                    <?php else: ?>
                    <button type="button" class="dc-btn dc-btn-primary dc-btn-sm"
                        onclick="quickLog(<?php echo (int)$a['patient_id']; ?>,'<?php echo esc_js($a['patient_name']); ?>',<?php echo (int)$a['doctor_id']; ?>,'<?php echo esc_js($a['service_title']??''); ?>',<?php echo (int)$a['id']; ?>)">
                        <i data-lucide="plus" style="width:12px;height:12px;"></i> ثبت هزینه
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- رکوردهای ثبت‌شده امروز -->
        <div class="dc-card" style="margin-bottom:20px;">
            <div class="dc-card-header">
                <h3 class="dc-heading-4" style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="list" style="width:15px;height:15px;color:var(--dc-primary);"></i>
                    رکوردهای ثبت‌شده
                </h3>
                <button type="button" class="dc-btn dc-btn-primary dc-btn-sm" onclick="quickLog(0,'',0,'',0)">
                    <i data-lucide="plus-circle" style="width:14px;height:14px;"></i> ثبت دستی جدید
                </button>
            </div>
            <div class="dc-table-wrap">
                <table class="dc-table">
                    <thead><tr><th>بیمار</th><th>شرح درمان</th><th>مبلغ درمان</th><th>تخفیف</th><th>دریافتی</th><th>روش پرداخت</th><th>یادداشت</th><th>عملیات</th></tr></thead>
                    <tbody>
                    <?php if(empty($entries)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:28px;color:var(--dc-neutral-400);">رکوردی ثبت نشده</td></tr>
                    <?php else: foreach($entries as $e):
                        $method_labels = ['cash'=>'نقدی','card'=>'کارت','wallet'=>'کیف پول','installment'=>'قسطی'];
                    ?>
                    <tr>
                        <td style="font-weight:600;"><?php echo esc_html($e['patient_name']??'—'); ?></td>
                        <td><?php echo esc_html($e['treatment_title']); ?></td>
                        <td><?php echo number_format($e['amount_charged']); ?></td>
                        <td style="color:<?php echo ($e['discount_amount']??0)>0?'var(--dc-danger)':'var(--dc-neutral-400)'; ?>;"><?php echo ($e['discount_amount']??0)>0 ? '−'.number_format($e['discount_amount']) : '—'; ?></td>
                        <td style="color:var(--dc-accent-dark);font-weight:600;"><?php echo number_format($e['amount_received']); ?></td>
                        <td><?php echo esc_html($method_labels[$e['payment_method']]??$e['payment_method']); ?></td>
                        <td style="font-size:12px;color:var(--dc-neutral-500);"><?php echo esc_html(mb_substr($e['notes']??'',0,30)); ?></td>
                        <td>
                            <div style="display:flex;gap:4px;">
                                <button type="button" class="dc-btn dc-btn-ghost dc-btn-sm"
                                    onclick='editEntry(<?php echo wp_json_encode($e); ?>)'>
                                    <i data-lucide="edit-3" style="width:12px;height:12px;"></i>
                                </button>
                                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('delete_ledger',$e['id']),'delete_ledger_'.$e['id'])); ?>"
                                   class="dc-btn dc-btn-ghost dc-btn-sm" onclick="return confirm('این رکورد حذف شود؟')">
                                    <i data-lucide="trash-2" style="width:12px;height:12px;color:var(--dc-danger);"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- مودال ثبت/ویرایش -->
        <div id="ledger-modal" style="display:none;position:fixed;inset:0;background:rgba(26,39,51,.65);z-index:99999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:14px;width:100%;max-width:460px;margin:20px;overflow:hidden;">
                <div style="background:linear-gradient(135deg,var(--dc-primary),var(--dc-primary-dark));padding:14px 18px;display:flex;align-items:center;justify-content:space-between;color:#fff;">
                    <h4 id="ledger-modal-title" style="margin:0;font-size:15px;font-family:var(--dc-font-family);">ثبت هزینه درمان</h4>
                    <button onclick="document.getElementById('ledger-modal').style.display='none'" type="button"
                        style="background:rgba(255,255,255,.2);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:15px;">✕</button>
                </div>
                <div style="padding:20px;">
                    <form method="post" id="ledger-form">
                        <?php wp_nonce_field('dental_ledger_save'); ?>
                        <input type="hidden" name="entry_date_jalali" value="<?php echo esc_attr($view_j); ?>">
                        <input type="hidden" name="appointment_id" id="lf-appt-id" value="">
                        <input type="hidden" name="ledger_id" id="lf-ledger-id" value="">

                        <div style="display:flex;flex-direction:column;gap:12px;">
                            <div class="dc-form-group" style="margin:0;" id="lf-patient-wrap">
                                <label class="dc-label">بیمار</label>
                                <select name="patient_id" id="lf-patient" class="dc-select" required>
                                    <option value="">انتخاب بیمار...</option>
                                    <?php foreach($patients as $p): ?>
                                    <option value="<?php echo $p->ID; ?>"><?php echo esc_html($p->post_title); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">شرح درمان</label>
                                <input type="text" name="treatment_title" id="lf-treatment" class="dc-input" required placeholder="مثال: جرمگیری">
                            </div>
                            <div class="dc-grid dc-grid-2" style="gap:12px;">
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">مبلغ درمان (تومان)</label>
                                    <input type="number" name="amount_charged" id="lf-charged" class="dc-input" min="0" step="1000" required oninput="syncReceived()">
                                </div>
                                <div class="dc-form-group" style="margin:0;">
                                    <label class="dc-label">مبلغ دریافتی (تومان)</label>
                                    <input type="number" name="amount_received" id="lf-received" class="dc-input" min="0" step="1000">
                                </div>
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">روش پرداخت</label>
                                <select name="payment_method" id="lf-method" class="dc-select">
                                    <option value="cash">نقدی</option>
                                    <option value="card">کارت به کارت</option>
                                    <option value="wallet">کیف پول</option>
                                </select>
                            </div>
                            <div class="dc-form-group" style="margin:0;">
                                <label class="dc-label">یادداشت</label>
                                <textarea name="notes" id="lf-notes" class="dc-textarea" placeholder="توضیحات اختیاری..."></textarea>
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;margin-top:16px;">
                            <button type="submit" name="dental_save_ledger" id="lf-submit-btn" class="dc-btn dc-btn-primary">💾 ثبت</button>
                            <button type="button" onclick="document.getElementById('ledger-modal').style.display='none'" class="dc-btn dc-btn-ghost">انصراف</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        function quickLog(patientId, patientName, doctorId, serviceTitle, apptId){
            document.getElementById('lf-ledger-id').value = '';
            document.getElementById('lf-appt-id').value  = apptId || '';
            document.getElementById('lf-treatment').value = serviceTitle || '';
            document.getElementById('lf-charged').value  = '';
            document.getElementById('lf-received').value = '';
            document.getElementById('lf-notes').value    = '';
            document.getElementById('ledger-modal-title').textContent = 'ثبت هزینه درمان';
            document.getElementById('lf-submit-btn').name = 'dental_save_ledger';

            var patientSel = document.getElementById('lf-patient');
            if (patientId) { patientSel.value = patientId; patientSel.disabled = true; }
            else { patientSel.value = ''; patientSel.disabled = false; }

            document.getElementById('ledger-modal').style.display = 'flex';
        }

        function editEntry(e){
            document.getElementById('lf-ledger-id').value = e.id;
            document.getElementById('lf-treatment').value = e.treatment_title;
            document.getElementById('lf-charged').value   = e.amount_charged;
            document.getElementById('lf-received').value  = e.amount_received;
            document.getElementById('lf-method').value    = e.payment_method || 'cash';
            document.getElementById('lf-notes').value     = e.notes || '';
            document.getElementById('ledger-modal-title').textContent = 'ویرایش رکورد';

            var form = document.getElementById('ledger-form');
            form.querySelector('[name="dental_save_ledger"]')?.remove();
            var hidden = document.createElement('input');
            hidden.type = 'hidden'; hidden.name = 'dental_update_ledger'; hidden.value = '1';
            form.appendChild(hidden);

            document.getElementById('lf-patient').disabled = true;
            document.getElementById('ledger-modal').style.display = 'flex';
        }

        function syncReceived(){
            var r = document.getElementById('lf-received');
            if (!r.value) r.value = document.getElementById('lf-charged').value;
        }

        <?php if (!empty($_GET['open_patient'])):
            $op_id   = (int)$_GET['open_patient'];
            $op_post = get_post($op_id);
            $op_name = $op_post ? $op_post->post_title : '';
        ?>
        // آمده از «پایان معاینه» در صف پذیرش — فرم ثبت هزینه را خودکار با این بیمار باز کن
        document.addEventListener('DOMContentLoaded', function(){
            quickLog(<?php echo (int)$op_id; ?>, '<?php echo esc_js($op_name); ?>', 0, '', 0);
        });
        <?php endif; ?>
        </script>
        <?php
    }

    // ─── تب گزارش‌ها ──────────────────────────────────────────
    // ─── خروجی اکسل — باید همیشه از همون اول درخواست (قبل از هر
    // echo/HTML) صدا زده بشه، وگرنه header() اثر نمی‌کنه ─────────────
    private function export_ledger_csv(): void {
        $range = sanitize_key($_GET['range'] ?? 'month');
        $today = current_time('Y-m-d');
        switch ($range) {
            case 'week':    $from = date('Y-m-d', strtotime('-7 days'));  break;
            case 'quarter': $from = date('Y-m-d', strtotime('-90 days')); break;
            default: $from = date('Y-m-d', strtotime('-30 days'));
        }
        $entries = Dental_Ledger_Manager::get_by_range($from, $today);
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=dafar-rozaneh-' . $from . '_' . $today . '.csv');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['تاریخ','بیمار','شرح درمان','مبلغ درمان','تخفیف','دریافتی','روش پرداخت','وضعیت']);
        $method_labels_csv = ['cash'=>'نقدی','card'=>'کارت','wallet'=>'کیف پول','installment'=>'قسطی'];
        $status_labels_csv = ['confirmed'=>'تأییدشده','pending'=>'در انتظار'];
        foreach ($entries as $e) {
            fputcsv($out, [
                Dental_Jalali::to_jalali($e['entry_date'],'Y/m/d'),
                $e['patient_name'] ?? '—',
                $e['treatment_title'],
                $e['amount_charged'],
                $e['discount_amount'] ?? 0,
                $e['amount_received'],
                $method_labels_csv[$e['payment_method']] ?? $e['payment_method'],
                $status_labels_csv[$e['status']] ?? $e['status'],
            ]);
        }
        fclose($out);
        exit;
    }

    private function render_reports(): void {
        $range = sanitize_key($_GET['range'] ?? 'month');
        // رفع باگ تایم‌زون — همون دلیل بالا
        $today = current_time('Y-m-d');
        switch ($range) {
            case 'week':    $from = date('Y-m-d', strtotime('-7 days'));  $label='۷ روز اخیر'; break;
            case 'quarter': $from = date('Y-m-d', strtotime('-90 days')); $label='۹۰ روز اخیر'; break;
            default: $range='month'; $from = date('Y-m-d', strtotime('-30 days')); $label='۳۰ روز اخیر';
        }

        $stats = Dental_Ledger_Manager::get_report_stats($from, $today);
        $overall = $stats['overall'];

        $chart_labels = array_map(fn($d)=>$d['entry_date_jalali'], $stats['daily']);
        $chart_data   = array_map(fn($d)=>(float)$d['total'], $stats['daily']);
        ?>
        <div style="display:flex;gap:6px;margin-bottom:16px;">
            <?php foreach(['week'=>'۷ روز','month'=>'۳۰ روز','quarter'=>'۹۰ روز'] as $r=>$l): $active=$range===$r; ?>
            <a href="<?php echo esc_url(add_query_arg(['tab'=>'reports','range'=>$r])); ?>"
               style="padding:7px 16px;border-radius:20px;font-size:12px;text-decoration:none;font-family:Tahoma;
                      background:<?php echo $active?'var(--dc-primary)':'var(--dc-neutral-100)'; ?>;
                      color:<?php echo $active?'#fff':'var(--dc-neutral-700)'; ?>;"><?php echo esc_html($l); ?></a>
            <?php endforeach; ?>
        </div>
        <p style="color:var(--dc-neutral-500);font-size:13px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;">
            <span>آمار مربوط به <?php echo esc_html($label); ?></span>
            <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(['tab'=>'reports','range'=>$range,'export_ledger_csv'=>1]),'dental_export_ledger')); ?>" class="dc-btn dc-btn-secondary dc-btn-sm">
                <i data-lucide="file-down" style="width:13px;height:13px;"></i> خروجی اکسل
            </a>
        </p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:20px;">
            <?php foreach([
                ['clipboard-list','تعداد رکورد',(int)($overall['total_entries']??0),'var(--dc-primary)','var(--dc-primary-light)'],
                ['receipt','جمع مبلغ درمان',number_format((float)($overall['total_charged']??0)),'var(--dc-accent-warm)','var(--dc-warning-light)'],
                ['banknote','جمع دریافتی',number_format((float)($overall['total_received']??0)),'var(--dc-accent-dark)','var(--dc-accent-light)'],
            ] as [$icon,$label2,$val,$color,$bg]): ?>
            <div class="dc-card" style="padding:16px;border-top:3px solid <?php echo $color; ?>;">
                <div style="width:34px;height:34px;border-radius:8px;background:<?php echo $bg; ?>;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
                    <i data-lucide="<?php echo esc_attr($icon); ?>" style="width:17px;height:17px;color:<?php echo $color; ?>;"></i>
                </div>
                <div style="font-size:20px;font-weight:700;color:<?php echo $color; ?>;"><?php echo $val; ?></div>
                <div style="font-size:12px;color:var(--dc-neutral-600);margin-top:2px;"><?php echo esc_html($label2); ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="dc-card" style="margin-bottom:20px;">
            <div class="dc-card-header"><h3 class="dc-heading-4">📈 روند دریافتی روزانه</h3></div>
            <div class="dc-card-body" style="padding:20px;">
                <?php if(empty($stats['daily'])): ?>
                <p style="text-align:center;color:var(--dc-neutral-400);font-size:13px;padding:20px 0;">داده‌ای برای نمایش نیست</p>
                <?php else: ?>
                <canvas id="dc-ledger-chart" height="100"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div class="dc-card">
                <div class="dc-card-header"><h3 class="dc-heading-4">🦷 بر اساس نوع درمان</h3></div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>شرح درمان</th><th>تعداد</th><th>جمع دریافتی</th></tr></thead>
                        <tbody>
                        <?php if(empty($stats['by_treatment'])): ?>
                        <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--dc-neutral-400);">داده‌ای نیست</td></tr>
                        <?php else: foreach($stats['by_treatment'] as $t): ?>
                        <tr>
                            <td><?php echo esc_html($t['treatment_title']); ?></td>
                            <td style="text-align:center;"><?php echo (int)$t['cnt']; ?></td>
                            <td style="font-weight:600;color:var(--dc-accent-dark);"><?php echo number_format($t['total']); ?></td>
                        </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dc-card">
                <div class="dc-card-header"><h3 class="dc-heading-4">👨‍⚕️ بر اساس پزشک (روی اسم کلیک کنید)</h3></div>
                <div class="dc-table-wrap">
                    <table class="dc-table">
                        <thead><tr><th>پزشک</th><th>تعداد</th><th>جمع دریافتی</th></tr></thead>
                        <tbody>
                        <?php if(empty($stats['by_doctor'])): ?>
                        <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--dc-neutral-400);">داده‌ای نیست</td></tr>
                        <?php else: foreach($stats['by_doctor'] as $d): ?>
                        <tr style="cursor:pointer;" onclick="dcToggleLedgerDoctorDetails(<?php echo (int)$d['doctor_id']; ?>,this)">
                            <td style="color:var(--dc-primary);text-decoration:underline;"><?php echo esc_html($d['doctor_name']?:'—'); ?> <span style="font-size:10px;">▾</span></td>
                            <td style="text-align:center;"><?php echo (int)$d['cnt']; ?></td>
                            <td style="font-weight:600;color:var(--dc-accent-dark);"><?php echo number_format($d['total']); ?></td>
                        </tr>
                        <tr class="dc-ledger-doctor-row" data-doctor="<?php echo (int)$d['doctor_id']; ?>" style="display:none;">
                            <td colspan="3" style="padding:0;background:var(--dc-neutral-50);">
                                <div class="dc-ledger-doctor-content" style="padding:12px;"></div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
        function dcToggleLedgerDoctorDetails(doctorId, trigger){
            var row = document.querySelector('.dc-ledger-doctor-row[data-doctor="'+doctorId+'"]');
            if (!row) return;
            if (row.style.display === 'table-row') { row.style.display = 'none'; return; }
            row.style.display = 'table-row';
            var content = row.querySelector('.dc-ledger-doctor-content');
            if (content.dataset.loaded) return;
            content.innerHTML = '<div style="text-align:center;color:#A0B4C0;padding:10px;">در حال بارگذاری...</div>';
            var fd = new FormData();
            fd.append('action','dental_doctor_report_details');
            fd.append('_wpnonce', '<?php echo esc_js(wp_create_nonce('dental_workspace')); ?>');
            fd.append('doctor_id', doctorId);
            fd.append('from', '<?php echo esc_js($from); ?>');
            fd.append('to', '<?php echo esc_js($today); ?>');
            fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {method:'POST', body:fd})
                .then(r=>r.json()).then(function(res){
                    content.innerHTML = res.success ? res.data.html : '<p style="color:#E05252;text-align:center;">خطا در بارگذاری</p>';
                    content.dataset.loaded = '1';
                });
        }
        </script>

        <?php if(!empty($stats['daily'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            var ctx = document.getElementById('dc-ledger-chart');
            if(!ctx || typeof Chart==='undefined') return;
            new Chart(ctx, {
                type: 'bar',
                data: { labels: <?php echo wp_json_encode($chart_labels); ?>,
                    datasets: [{ label:'دریافتی', data: <?php echo wp_json_encode($chart_data); ?>,
                        backgroundColor:'rgba(46,204,154,0.75)', borderRadius:6 }] },
                options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
            });
        });
        </script>
        <?php endif; ?>
        <?php
    }
}
