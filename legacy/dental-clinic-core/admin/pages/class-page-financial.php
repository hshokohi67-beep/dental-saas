<?php
defined('ABSPATH') || exit;

class Dental_Page_Financial {

    public function maybe_handle_post(): void {
        // چک واقعی دسترسی هم اینجا لازمه، نه فقط توی render() — چون این
        // متد زودتر (روی admin_init) صدا زده می‌شه، پس بدون این چک هر
        // نقشی می‌تونست مستقیم فرم بفرسته حتی اگه هیچ‌وقت صفحه رو نبینه.
        $cu = wp_get_current_user();
        if (!current_user_can('manage_options') && !array_intersect((array)$cu->roles, ['dental_admin','dental_financial','dental_secretary','dental_doctor'])) {
            return;
        }

        $patient_id = (int)($_GET['patient_id'] ?? $_POST['patient_id'] ?? 0);
        if (isset($_POST['dental_save_installment'])) {
            check_admin_referer('dental_installment_' . $patient_id);
            $this->save_installment($patient_id);
        }
        if (isset($_POST['dental_edit_plan'])) {
            check_admin_referer('dental_edit_plan');
            $this->update_installment();
        }
        if (isset($_POST['dental_pay_item'])) {
            check_admin_referer('dental_pay_item');
            $this->pay_installment_item();
        }
        if (isset($_POST['dental_archive_plan'])) {
            check_admin_referer('dental_archive_plan');
            $this->archive_plan();
        }
        // ─── تأیید/رد فیش پرداخت — این بخش تا الان اصلاً وجود نداشت؛
        // فیش‌ها ذخیره می‌شدن ولی هیچ‌جا برای بررسی نشون داده نمی‌شدن.
        if (isset($_POST['dental_confirm_receipt']) && check_admin_referer('dental_confirm_receipt')) {
            $item_id = (int)$_POST['item_id'];
            global $wpdb;
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}dental_installment_items WHERE id=%d", $item_id), ARRAY_A);
            if ($item) {
                // قبلاً اینجا مستقیم $wpdb->update می‌زد — نه چک اضافه‌پرداخت
                // داشت، نه آپدیت اتمیک (race condition)، نه SMS رسید. حالا از
                // همون مسیر امنی رد می‌شه که دکمه‌های «نقدی»/«کیف پول» استفاده می‌کنن.
                $remaining = max(0, (float)$item['amount'] - (float)$item['paid_amount']);
                $result = Dental_Installment_Manager::pay_item($item_id, $remaining, 'receipt', '', false);
                if ($result['success'] && class_exists('Dental_Audit_Log')) {
                    Dental_Audit_Log::log('installment_pay', "تأیید فیش پرداخت قسط #{$item_id} — {$remaining} تومان", ['entity_type'=>'installment_item','entity_id'=>$item_id]);
                }
            }
            wp_safe_redirect(add_query_arg(['action'=>'receipts','saved'=>1], remove_query_arg('saved'))); exit;
        }
        if (isset($_POST['dental_reject_receipt']) && check_admin_referer('dental_confirm_receipt')) {
            $item_id = (int)$_POST['item_id'];
            global $wpdb;
            $wpdb->update($wpdb->prefix.'dental_installment_items', [
                'status' => 'pending', 'receipt_image_id' => null,
            ], ['id' => $item_id]);
            if (class_exists('Dental_Audit_Log')) {
                Dental_Audit_Log::log('installment_pay', "فیش پرداخت قسط #{$item_id} رد شد", ['entity_type'=>'installment_item','entity_id'=>$item_id]);
            }
            wp_safe_redirect(add_query_arg(['action'=>'receipts','saved'=>1], remove_query_arg('saved'))); exit;
        }
    }

    public function render(): void {
        $action     = sanitize_key($_GET['action'] ?? 'list');
        $patient_id = (int)($_GET['patient_id'] ?? $_POST['patient_id'] ?? 0);
        $saved      = isset($_GET['saved']);
        ?>
        <div class="dental-admin-wrap">

            <!-- هدر -->
            <div class="dc-flex dc-items-center dc-justify-between" style="margin-bottom:24px;">
                <div>
                    <h1 class="dc-heading-2" style="display:flex;align-items:center;gap:10px;">
                        <i data-lucide="credit-card" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                        مالی و اقساط
                    </h1>
                    <p class="dc-text-muted">مدیریت پرداخت‌ها و پلان‌های قسطی</p>
                </div>
            </div>

            <?php if ($saved): ?>
            <div style="background:var(--dc-accent-light);border:1px solid var(--dc-accent);border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:8px;">
                <i data-lucide="check-circle-2" style="width:16px;height:16px;color:var(--dc-accent-dark);"></i>
                عملیات با موفقیت انجام شد.
            </div>
            <?php endif; ?>

            <?php
            if ($action === 'new' && $patient_id) {
                $this->render_new_installment($patient_id);
            } elseif ($action === 'view' && $patient_id) {
                $this->render_patient_installments($patient_id);
            } elseif ($action === 'archive') {
                $this->render_archive();
            } elseif ($action === 'report') {
                $this->render_report();
            } elseif ($action === 'receipts') {
                $this->render_receipts();
            } else {
                $this->render_overview();
            }
            ?>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }

    // ─── نمای کلی ──────────────────────────────────────────────
    // ─── لیست فیش‌های پرداخت آپلودشده که منتظر تأیید/رد شما هستن ────
    private function render_receipts(): void {
        global $wpdb;
        $items = $wpdb->get_results(
            "SELECT ii.*, i.patient_id, p.post_title as patient_name
             FROM {$wpdb->prefix}dental_installment_items ii
             JOIN {$wpdb->prefix}dental_installments i ON ii.installment_id = i.id
             LEFT JOIN {$wpdb->posts} p ON i.patient_id = p.ID
             WHERE ii.status='partial' AND ii.receipt_image_id IS NOT NULL AND ii.receipt_image_id > 0
             ORDER BY ii.id DESC", ARRAY_A
        );
        ?>
        <a href="<?php echo esc_url(remove_query_arg(['action','patient_id'])); ?>" style="font-size:12px;color:var(--dc-primary);text-decoration:none;display:inline-block;margin-bottom:14px;">← بازگشت</a>
        <h2 class="dc-heading-3" style="margin-bottom:16px;">🧾 فیش‌های پرداخت در انتظار بررسی (<?php echo count($items); ?>)</h2>
        <?php if (empty($items)): ?>
        <div class="dc-card"><div class="dc-card-body" style="text-align:center;padding:40px;color:var(--dc-neutral-400);">فیش در انتظاری نیست.</div></div>
        <?php else: foreach($items as $it):
            $img_url = wp_get_attachment_image_url((int)$it['receipt_image_id'], 'large');
        ?>
        <div class="dc-card" style="margin-bottom:16px;">
            <div style="padding:16px;display:flex;gap:16px;flex-wrap:wrap;">
                <?php if ($img_url): ?>
                <a href="<?php echo esc_url($img_url); ?>" target="_blank">
                    <img src="<?php echo esc_url($img_url); ?>" style="width:140px;height:140px;object-fit:cover;border-radius:8px;border:1px solid var(--dc-neutral-200);">
                </a>
                <?php endif; ?>
                <div style="flex:1;min-width:200px;">
                    <div style="font-weight:700;font-size:14px;margin-bottom:6px;"><?php echo esc_html($it['patient_name']); ?></div>
                    <div style="font-size:13px;color:var(--dc-neutral-600);margin-bottom:4px;">مبلغ قسط: <b><?php echo number_format($it['amount']); ?> تومان</b></div>
                    <?php if ($it['notes']): ?><div style="font-size:12px;color:var(--dc-neutral-500);margin-bottom:8px;">یادداشت بیمار: <?php echo esc_html($it['notes']); ?></div><?php endif; ?>
                    <div style="display:flex;gap:8px;margin-top:10px;">
                        <form method="post" style="display:inline;">
                            <?php wp_nonce_field('dental_confirm_receipt'); ?>
                            <input type="hidden" name="item_id" value="<?php echo (int)$it['id']; ?>">
                            <button type="submit" name="dental_confirm_receipt" class="dc-btn dc-btn-primary dc-btn-sm">✅ تأیید پرداخت</button>
                        </form>
                        <form method="post" style="display:inline;" onsubmit="return confirm('این فیش رد بشه؟ قسط دوباره به حالت در انتظار برمی‌گرده.');">
                            <?php wp_nonce_field('dental_confirm_receipt'); ?>
                            <input type="hidden" name="item_id" value="<?php echo (int)$it['id']; ?>">
                            <button type="submit" name="dental_reject_receipt" class="dc-btn dc-btn-ghost dc-btn-sm" style="color:var(--dc-danger);">❌ رد فیش</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; endif;
    }

    private function render_overview(): void {
        global $wpdb;

        $stats = $wpdb->get_row(
            "SELECT
                COUNT(*) as total_plans,
                SUM(total_amount) as total_amount,
                SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status='overdue'   THEN 1 ELSE 0 END) as overdue_plans
             FROM {$wpdb->prefix}dental_installments WHERE status != 'archived'"
        );

        $overdue_amount = (float)$wpdb->get_var(
            "SELECT SUM(amount - paid_amount) FROM {$wpdb->prefix}dental_installment_items
             WHERE status = 'overdue'"
        );

        $this_month_received = (float)$wpdb->get_var($wpdb->prepare(
            "SELECT SUM(paid_amount) FROM {$wpdb->prefix}dental_installment_items
             WHERE status='paid' AND paid_date >= %s",
            current_time('Y-m-01')
        ));

        $patients = get_posts(['post_type'=>'dental_patient','posts_per_page'=>-1,'post_status'=>'publish','orderby'=>'title','order'=>'ASC']);

        // فیلتر وضعیت
        $filter_status = sanitize_key($_GET['filter'] ?? 'active');
        ?>

        <?php
        $pending_receipts_count = (int)$wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dental_installment_items WHERE status='partial' AND receipt_image_id IS NOT NULL AND receipt_image_id > 0"
        );
        if ($pending_receipts_count > 0): ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=dental-financial&action=receipts')); ?>" style="text-decoration:none;">
            <div style="background:var(--dc-warning-light);border:1px solid var(--dc-accent-warm);border-radius:10px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:13px;color:#7a5200;font-weight:600;">🧾 <?php echo $pending_receipts_count; ?> فیش پرداخت منتظر بررسی شماست</span>
                <span style="font-size:12px;color:var(--dc-primary);">مشاهده ←</span>
            </div>
        </a>
        <?php endif; ?>

        <!-- کارت‌های آماری -->
        <div class="dc-grid dc-grid-4" style="gap:14px;margin-bottom:20px;">
            <?php
            $cards = [
                ['icon'=>'receipt',        'value'=>number_format((int)($stats->total_plans??0)),   'label'=>'پلان فعال',       'color'=>'var(--dc-primary)',    'bg'=>'var(--dc-primary-light)',  'link'=>''],
                ['icon'=>'trending-up',    'value'=>number_format($this_month_received),             'label'=>'دریافتی این ماه', 'color'=>'var(--dc-accent-dark)','bg'=>'var(--dc-accent-light)',  'link'=>'?page=dental-financial&action=report'],
                ['icon'=>'alert-triangle', 'value'=>number_format((int)($stats->overdue_plans??0)), 'label'=>'دارای معوقه',     'color'=>'var(--dc-accent-warm)','bg'=>'var(--dc-warning-light)', 'link'=>'?page=dental-financial&filter=overdue'],
                ['icon'=>'trending-down',  'value'=>number_format($overdue_amount),                  'label'=>'جمع معوقه (ت)',   'color'=>'var(--dc-danger)',     'bg'=>'var(--dc-danger-light)',  'link'=>''],
            ];
            foreach($cards as $c):
            ?>
            <a href="<?php echo esc_url($c['link'] ? admin_url($c['link']) : '#'); ?>" style="text-decoration:none;">
            <div class="dc-card" style="padding:16px;border-top:3px solid <?php echo $c['color']; ?>;cursor:<?php echo $c['link']?'pointer':'default'; ?>;">
                <div style="width:36px;height:36px;border-radius:8px;background:<?php echo $c['bg']; ?>;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
                    <i data-lucide="<?php echo esc_attr($c['icon']); ?>" style="width:18px;height:18px;color:<?php echo $c['color']; ?>;"></i>
                </div>
                <div style="font-size:20px;font-weight:700;color:<?php echo $c['color']; ?>;"><?php echo esc_html($c['value']); ?></div>
                <div style="font-size:12px;color:var(--dc-neutral-600);margin-top:4px;"><?php echo esc_html($c['label']); ?></div>
            </div>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- انتخاب بیمار -->
        <div class="dc-card" style="margin-bottom:20px;">
            <div class="dc-card-header">
                <div style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="user-search" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                    <h3 class="dc-heading-4" style="margin:0;">انتخاب بیمار</h3>
                </div>
            </div>
            <div class="dc-card-body" style="padding:16px 20px;">
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <input type="text" id="dc-patient-search" class="dc-input" style="max-width:400px;"
                        placeholder="جستجو با نام یا موبایل...">
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <select id="dc-patient-select" class="dc-select" style="min-width:320px;">
                            <option value="">بیمار را انتخاب کنید...</option>
                            <?php foreach($patients as $p):
                                $mobile = get_post_meta($p->ID,'_patient_mobile',true);
                            ?>
                            <option value="<?php echo $p->ID; ?>" data-mobile="<?php echo esc_attr($mobile); ?>">
                                <?php echo esc_html($p->post_title); ?><?php if($mobile): ?> — <?php echo esc_html($mobile); ?><?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="dc-btn dc-btn-secondary"
                            onclick="var v=document.getElementById('dc-patient-select').value;if(v)window.location='<?php echo esc_js(admin_url('admin.php?page=dental-financial&action=view&patient_id=')); ?>'+v;else alert('ابتدا بیمار را انتخاب کنید');">
                            <i data-lucide="eye" style="width:14px;height:14px;"></i> مشاهده اقساط
                        </button>
                        <button type="button" class="dc-btn dc-btn-primary"
                            onclick="var v=document.getElementById('dc-patient-select').value;if(v)window.location='<?php echo esc_js(admin_url('admin.php?page=dental-financial&action=new&patient_id=')); ?>'+v;else alert('ابتدا بیمار را انتخاب کنید');">
                            <i data-lucide="plus-circle" style="width:14px;height:14px;"></i> پلان قسطی جدید
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- فیلتر + لیست -->
        <div class="dc-card">
            <div class="dc-card-header" style="flex-wrap:wrap;gap:10px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="list" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                    <h3 class="dc-heading-4" style="margin:0;">لیست پلان‌های قسطی</h3>
                </div>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <?php foreach(['active'=>'فعال','overdue'=>'معوقه','completed'=>'تسویه شده'] as $fs=>$fl):
                        $is_active = $filter_status === $fs;
                    ?>
                    <a href="<?php echo esc_url(admin_url("admin.php?page=dental-financial&filter={$fs}")); ?>"
                       style="padding:5px 12px;border-radius:20px;font-size:12px;text-decoration:none;font-family:Tahoma;
                              background:<?php echo $is_active?'var(--dc-primary)':'var(--dc-neutral-100)'; ?>;
                              color:<?php echo $is_active?'#fff':'var(--dc-neutral-600)'; ?>;">
                        <?php echo esc_html($fl); ?>
                    </a>
                    <?php endforeach; ?>
                    <a href="<?php echo esc_url(admin_url("admin.php?page=dental-financial&action=archive")); ?>"
                       style="padding:5px 12px;border-radius:20px;font-size:12px;text-decoration:none;font-family:Tahoma;background:var(--dc-neutral-100);color:var(--dc-neutral-600);">
                        🗂️ بایگانی
                    </a>
                    <a href="<?php echo esc_url(admin_url("admin.php?page=dental-financial&action=report")); ?>"
                       style="padding:5px 12px;border-radius:20px;font-size:12px;text-decoration:none;font-family:Tahoma;background:var(--dc-neutral-100);color:var(--dc-neutral-600);">
                        📊 گزارش مالی
                    </a>
                </div>
            </div>
            <?php $this->render_plans_table($filter_status); ?>
        </div>

        <script>
        // جستجوی زنده
        (function(){
            var search = document.getElementById("dc-patient-search");
            var sel    = document.getElementById("dc-patient-select");
            if(!search || !sel) return;
            var opts = Array.from(sel.options).slice(1);
            search.addEventListener("input", function(){
                var q = this.value.trim().toLowerCase();
                while(sel.options.length > 1) sel.remove(1);
                opts.forEach(function(o){
                    if(!q || o.text.toLowerCase().indexOf(q)>-1 || (o.dataset.mobile||"").indexOf(q)>-1)
                        sel.add(o.cloneNode(true));
                });
            });
        })();
        </script>
        <?php
    }

    // ─── جدول پلان‌ها ──────────────────────────────────────────
    private function render_plans_table(string $status_filter = 'active'): void {
        global $wpdb;

        $where = $status_filter === 'all' ? "WHERE i.status != 'archived'" : $wpdb->prepare("WHERE i.status = %s", $status_filter);

        $plans = $wpdb->get_results(
            "SELECT i.*,
                p.post_title as patient_name,
                (SELECT SUM(ii.paid_amount) FROM {$wpdb->prefix}dental_installment_items ii WHERE ii.installment_id=i.id) as total_paid,
                (SELECT COUNT(*) FROM {$wpdb->prefix}dental_installment_items ii WHERE ii.installment_id=i.id AND ii.status='paid') as paid_items,
                (SELECT due_date_jalali FROM {$wpdb->prefix}dental_installment_items ii WHERE ii.installment_id=i.id AND ii.status='pending' ORDER BY item_number ASC LIMIT 1) as next_due
             FROM {$wpdb->prefix}dental_installments i
             LEFT JOIN {$wpdb->posts} p ON i.patient_id=p.ID
             {$where}
             ORDER BY i.created_at DESC LIMIT 100"
        );
        ?>
        <div class="dc-table-wrap">
            <table class="dc-table">
                <thead>
                    <tr>
                        <th>بیمار</th>
                        <th>مبلغ کل</th>
                        <th>پیش‌پرداخت</th>
                        <th>اقساط</th>
                        <th>پیشرفت</th>
                        <th>قسط بعدی</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(empty($plans)): ?>
                <tr><td colspan="8" style="text-align:center;padding:32px;color:var(--dc-neutral-400);">موردی یافت نشد</td></tr>
                <?php else: foreach($plans as $plan):
                    $total_paid = (float)($plan->total_paid ?? 0);
                    $grand_total = (float)$plan->total_amount;
                    // progress = (پیش‌پرداخت + پرداخت اقساط) / کل
                    $progress = $grand_total > 0 ? min(100, round((((float)$plan->down_payment + $total_paid) / $grand_total) * 100)) : 0;

                    $status_cfg = [
                        'active'    => ['var(--dc-primary)',    'فعال'],
                        'completed' => ['var(--dc-accent-dark)','تسویه شده'],
                        'overdue'   => ['var(--dc-danger)',      'معوقه'],
                        'cancelled' => ['var(--dc-neutral-400)','لغو شده'],
                    ];
                    [$scolor,$slabel] = $status_cfg[$plan->status] ?? ['#999',$plan->status];
                    $date = Dental_Jalali::to_jalali($plan->created_at,'Y/m/d');
                ?>
                <tr>
                    <td>
                        <a href="<?php echo esc_url(admin_url("admin.php?page=dental-financial&action=view&patient_id={$plan->patient_id}")); ?>"
                           style="color:var(--dc-primary);font-weight:600;text-decoration:none;">
                            <?php echo esc_html($plan->patient_name); ?>
                        </a>
                    </td>
                    <td style="font-weight:600;"><?php echo number_format($grand_total); ?> ت</td>
                    <td style="color:var(--dc-neutral-600);">
                        <?php echo number_format($plan->down_payment); ?> ت
                        <?php if (!empty($plan->down_payment_method)):
                            $dp_labels = ['cash'=>'نقدی','card'=>'کارتخوان','wallet'=>'کیف پول'];
                        ?>
                        <div style="font-size:10px;color:var(--dc-neutral-400);">(<?php echo esc_html($dp_labels[$plan->down_payment_method] ?? $plan->down_payment_method); ?>)</div>
                        <?php endif; ?>
                        <?php if (!empty($plan->ledger_id)): ?>
                        <div style="font-size:10px;color:var(--dc-primary);">🔗 از دفتر روزانه</div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;">
                        <span style="font-weight:700;color:var(--dc-primary);"><?php echo (int)$plan->paid_items; ?></span>
                        <span style="color:var(--dc-neutral-400);">/<?php echo (int)$plan->installment_count; ?></span>
                    </td>
                    <td style="min-width:120px;">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <div style="flex:1;height:6px;background:var(--dc-neutral-100);border-radius:3px;overflow:hidden;">
                                <div style="height:100%;width:<?php echo $progress; ?>%;background:<?php echo $plan->status==='overdue'?'var(--dc-danger)':'var(--dc-accent)'; ?>;border-radius:3px;transition:width .4s;"></div>
                            </div>
                            <span style="font-size:11px;color:var(--dc-neutral-500);min-width:30px;"><?php echo $progress; ?>%</span>
                        </div>
                    </td>
                    <td style="font-size:12px;color:<?php echo $plan->status==='overdue'?'var(--dc-danger)':'var(--dc-neutral-600)'; ?>;">
                        <?php echo $plan->next_due ? esc_html($plan->next_due) : '—'; ?>
                    </td>
                    <td>
                        <span style="background:<?php echo $scolor; ?>22;color:<?php echo $scolor; ?>;border-radius:12px;padding:3px 10px;font-size:11px;font-weight:700;">
                            <?php echo esc_html($slabel); ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <a href="<?php echo esc_url(admin_url("admin.php?page=dental-financial&action=view&patient_id={$plan->patient_id}")); ?>"
                               class="dc-btn dc-btn-secondary dc-btn-sm">مشاهده</a>
                            <?php if($plan->status === 'completed'): ?>
                            <form method="post" style="display:inline;" onsubmit="return confirm('این پلان بایگانی شود؟')">
                                <?php wp_nonce_field('dental_archive_plan'); ?>
                                <input type="hidden" name="plan_id" value="<?php echo (int)$plan->id; ?>">
                                <button type="submit" name="dental_archive_plan" class="dc-btn dc-btn-ghost dc-btn-sm">🗂️</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    // ─── بایگانی ────────────────────────────────────────────────
    private function render_archive(): void {
        global $wpdb;
        $plans = $wpdb->get_results(
            "SELECT i.*, p.post_title as patient_name,
                (SELECT SUM(ii.paid_amount) FROM {$wpdb->prefix}dental_installment_items ii WHERE ii.installment_id=i.id) as total_paid
             FROM {$wpdb->prefix}dental_installments i
             LEFT JOIN {$wpdb->posts} p ON i.patient_id=p.ID
             WHERE i.status='archived'
             ORDER BY i.updated_at DESC LIMIT 100"
        );
        ?>
        <div class="dc-card">
            <div class="dc-card-header">
                <div style="display:flex;align-items:center;gap:8px;">
                    <i data-lucide="archive" style="width:16px;height:16px;color:var(--dc-neutral-500);"></i>
                    <h3 class="dc-heading-4" style="margin:0;">بایگانی پلان‌های تسویه‌شده</h3>
                </div>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dental-financial')); ?>" class="dc-btn dc-btn-ghost dc-btn-sm">← بازگشت</a>
            </div>
            <div class="dc-table-wrap">
                <table class="dc-table">
                    <thead><tr><th>بیمار</th><th>مبلغ کل</th><th>اقساط</th><th>کل پرداخت</th><th>تاریخ بایگانی</th></tr></thead>
                    <tbody>
                    <?php if(empty($plans)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--dc-neutral-400);">بایگانی خالی است</td></tr>
                    <?php else: foreach($plans as $p): ?>
                    <tr>
                        <td><?php echo esc_html($p->patient_name); ?></td>
                        <td><?php echo number_format($p->total_amount); ?> ت</td>
                        <td style="text-align:center;"><?php echo (int)$p->installment_count; ?> قسط</td>
                        <td style="color:var(--dc-accent-dark);font-weight:600;"><?php echo number_format($p->total_paid); ?> ت</td>
                        <td style="font-size:12px;color:var(--dc-neutral-500);"><?php echo esc_html(Dental_Jalali::to_jalali($p->updated_at,'Y/m/d')); ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ─── گزارش مالی ────────────────────────────────────────────
    private function render_report(): void {
        global $wpdb;

        // آمار ۶ ماه اخیر
        $monthly = $wpdb->get_results(
            "SELECT DATE_FORMAT(paid_date,'%Y-%m') as month,
                    COUNT(*) as count,
                    SUM(paid_amount) as total
             FROM {$wpdb->prefix}dental_installment_items
             WHERE status='paid' AND paid_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY DATE_FORMAT(paid_date,'%Y-%m')
             ORDER BY month ASC"
        , ARRAY_A);

        $total_receivable = (float)$wpdb->get_var(
            "SELECT SUM(remaining_amount) FROM {$wpdb->prefix}dental_installments WHERE status NOT IN ('completed','archived','cancelled')"
        );
        $total_overdue = (float)$wpdb->get_var(
            "SELECT SUM(amount-paid_amount) FROM {$wpdb->prefix}dental_installment_items WHERE status='overdue'"
        );
        $total_received_all = (float)$wpdb->get_var(
            "SELECT SUM(paid_amount) FROM {$wpdb->prefix}dental_installment_items WHERE status='paid'"
        );

        // تبدیل ماه‌ها به شمسی
        $chart_labels = [];
        $chart_data   = [];
        foreach($monthly as $m) {
            $chart_labels[] = Dental_Jalali::to_jalali($m['month'].'-01','Y/m');
            $chart_data[]   = (float)$m['total'];
        }
        ?>
        <div class="dc-flex dc-items-center dc-justify-between" style="margin-bottom:16px;">
            <h2 class="dc-heading-3" style="display:flex;align-items:center;gap:8px;">
                <i data-lucide="bar-chart-2" style="width:20px;height:20px;color:var(--dc-primary);"></i>
                گزارش مالی
            </h2>
            <div style="display:flex;gap:8px;">
                <button type="button" class="dc-btn dc-btn-secondary dc-btn-sm" onclick="exportReport()">
                    <i data-lucide="download" style="width:14px;height:14px;"></i> دانلود Excel
                </button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dental-financial')); ?>" class="dc-btn dc-btn-ghost dc-btn-sm">← بازگشت</a>
            </div>
        </div>

        <!-- کارت‌های خلاصه -->
        <div class="dc-grid dc-grid-3" style="gap:14px;margin-bottom:20px;">
            <?php foreach([
                ['کل دریافت شده',  number_format($total_received_all), 'var(--dc-accent-dark)',  'var(--dc-accent-light)',  'check-circle-2'],
                ['قابل دریافت',    number_format($total_receivable),   'var(--dc-primary)',      'var(--dc-primary-light)', 'clock'],
                ['معوقه',          number_format($total_overdue),      'var(--dc-danger)',        'var(--dc-danger-light)',  'alert-triangle'],
            ] as [$label,$val,$color,$bg,$icon]): ?>
            <div class="dc-card" style="padding:16px;border-top:3px solid <?php echo $color; ?>;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                    <div style="width:32px;height:32px;border-radius:8px;background:<?php echo $bg; ?>;display:flex;align-items:center;justify-content:center;">
                        <i data-lucide="<?php echo esc_attr($icon); ?>" style="width:16px;height:16px;color:<?php echo $color; ?>;"></i>
                    </div>
                    <span style="font-size:12px;color:var(--dc-neutral-600);"><?php echo esc_html($label); ?></span>
                </div>
                <div style="font-size:22px;font-weight:700;color:<?php echo $color; ?>;"><?php echo esc_html($val); ?></div>
                <div style="font-size:11px;color:var(--dc-neutral-500);">تومان</div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- نمودار Chart.js -->
        <div class="dc-card" style="margin-bottom:20px;">
            <div class="dc-card-header">
                <h3 class="dc-heading-4">نمودار دریافتی ماهانه (۶ ماه اخیر)</h3>
            </div>
            <div class="dc-card-body" style="padding:20px;">
                <canvas id="dc-monthly-chart" height="120"></canvas>
            </div>
        </div>

        <!-- جدول تفصیلی -->
        <div class="dc-card">
            <div class="dc-card-header">
                <h3 class="dc-heading-4">جزئیات ماهانه</h3>
            </div>
            <div class="dc-table-wrap">
                <table class="dc-table" id="dc-report-table">
                    <thead><tr><th>ماه</th><th>تعداد پرداخت</th><th>مجموع دریافتی (تومان)</th></tr></thead>
                    <tbody>
                    <?php foreach($monthly as $m): ?>
                    <tr>
                        <td><?php echo esc_html(Dental_Jalali::to_jalali($m['month'].'-01','Y/m')); ?></td>
                        <td style="text-align:center;"><?php echo (int)$m['count']; ?></td>
                        <td style="font-weight:600;color:var(--dc-accent-dark);"><?php echo number_format($m['total']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
        document.addEventListener("DOMContentLoaded", function(){
            if(typeof Chart === "undefined") return;
            var ctx = document.getElementById("dc-monthly-chart");
            if(!ctx) return;
            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: <?php echo wp_json_encode($chart_labels); ?>,
                    datasets: [{
                        label: "دریافتی (تومان)",
                        data: <?php echo wp_json_encode($chart_data); ?>,
                        backgroundColor: "rgba(26,107,138,0.7)",
                        borderColor: "rgba(26,107,138,1)",
                        borderWidth: 1,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(c){ return " " + c.raw.toLocaleString("fa-IR") + " تومان"; }
                            }
                        }
                    },
                    scales: {
                        y: { ticks: { callback: function(v){ return v.toLocaleString("fa-IR"); } } }
                    }
                }
            });
        });

        function exportReport(){
            // اعداد فرمت‌شده مثل 1,234,567 خودشون ویرگول دارن —
            // بدون quote کردن، اکسل هر ویرگول رو یه ستون جدید حساب می‌کرد
            function csvField(v){
                v = String(v).replace(/"/g, '""');
                return '"' + v + '"';
            }
            var rows = [["ماه","تعداد پرداخت","مجموع دریافتی"]];
            document.querySelectorAll("#dc-report-table tbody tr").forEach(function(tr){
                var cells = tr.querySelectorAll("td");
                rows.push([cells[0].textContent.trim(), cells[1].textContent.trim(), cells[2].textContent.trim()]);
            });
            var csv = rows.map(function(r){ return r.map(csvField).join(","); }).join("\r\n");
            var blob = new Blob(["\uFEFF"+csv], {type:"text/csv;charset=utf-8;"});
            var a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "dental-report.csv";
            a.click();
        }
        </script>
        <?php
    }

    // ─── فرم پلان جدید ─────────────────────────────────────────
    private function render_new_installment(int $patient_id): void {
        $patient = get_post($patient_id);
        if(!$patient){ echo '<p>بیمار یافت نشد.</p>'; return; }
        ?>
        <div class="dc-card" style="max-width:680px;">
            <div class="dc-card-header">
                <h3 class="dc-heading-4">
                    <i data-lucide="plus-circle" style="width:16px;height:16px;color:var(--dc-primary);"></i>
                    پلان قسطی جدید — <?php echo esc_html($patient->post_title); ?>
                </h3>
                <a href="<?php echo esc_url(admin_url("admin.php?page=dental-financial&action=view&patient_id={$patient_id}")); ?>"
                   class="dc-btn dc-btn-ghost dc-btn-sm">← بازگشت</a>
            </div>
            <div class="dc-card-body">
                <!-- پیش‌نمایش زنده -->
                <div style="background:var(--dc-primary-light);border-radius:10px;padding:16px;margin-bottom:20px;">
                    <div class="dc-grid dc-grid-3" style="gap:12px;">
                        <div style="text-align:center;">
                            <div style="font-size:11px;color:var(--dc-neutral-600);margin-bottom:4px;">مبلغ هر قسط</div>
                            <div id="calc-each" style="font-size:22px;font-weight:700;color:var(--dc-primary);">۰</div>
                            <div style="font-size:10px;color:var(--dc-neutral-500);">تومان</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-size:11px;color:var(--dc-neutral-600);margin-bottom:4px;">مانده پس از پیش‌پرداخت</div>
                            <div id="calc-remaining" style="font-size:22px;font-weight:700;color:var(--dc-neutral-800);">۰</div>
                            <div style="font-size:10px;color:var(--dc-neutral-500);">تومان</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-size:11px;color:var(--dc-neutral-600);margin-bottom:4px;">آخرین قسط</div>
                            <div id="calc-last-date" style="font-size:16px;font-weight:700;color:var(--dc-neutral-800);">—</div>
                        </div>
                    </div>
                </div>

                <form method="post">
                    <?php wp_nonce_field('dental_installment_' . $patient_id); ?>
                    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
                    <div class="dc-grid dc-grid-2" style="gap:14px;">
                        <div class="dc-form-group">
                            <label class="dc-label">مبلغ کل درمان (تومان) <span style="color:red">*</span></label>
                            <input type="number" name="total_amount" id="inp-total" class="dc-input" required min="0" step="1000" placeholder="مثال: 5000000" oninput="calcPreview()">
                        </div>
                        <div class="dc-form-group">
                            <label class="dc-label">پیش‌پرداخت (تومان)</label>
                            <input type="number" name="down_payment" id="inp-down" class="dc-input" min="0" step="1000" placeholder="0" oninput="calcPreview()">
                        </div>
                        <div class="dc-form-group">
                            <label class="dc-label">تعداد اقساط <span style="color:red">*</span></label>
                            <select name="installment_count" id="inp-count" class="dc-select" onchange="calcPreview()">
                                <?php for($i=1;$i<=24;$i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> قسط</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="dc-form-group">
                            <label class="dc-label">تاریخ اولین قسط (شمسی) <span style="color:red">*</span></label>
                            <input type="text" name="start_date_jalali" id="inp-start" class="dc-input" dir="ltr"
                                placeholder="<?php echo esc_attr(Dental_Jalali::today()); ?>"
                                value="<?php echo esc_attr(Dental_Jalali::today()); ?>"
                                oninput="calcPreview()">
                        </div>
                        <div class="dc-form-group">
                            <label class="dc-label">تخفیف (تومان)</label>
                            <input type="number" name="discount" id="inp-discount" class="dc-input" min="0" step="1000" placeholder="0" oninput="calcPreview()">
                        </div>
                        <div class="dc-form-group">
                            <label class="dc-label">یادداشت</label>
                            <input type="text" name="notes" class="dc-input" placeholder="توضیحات اختیاری...">
                        </div>
                    </div>

                    <div id="dates-preview" style="display:none;background:var(--dc-neutral-50);border:1px solid var(--dc-neutral-200);border-radius:8px;padding:14px;margin-bottom:16px;">
                        <div style="font-size:12px;font-weight:700;color:var(--dc-neutral-700);margin-bottom:10px;">📅 تاریخ‌های سررسید:</div>
                        <div id="dates-list" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
                    </div>

                    <button type="submit" name="dental_save_installment" class="dc-btn dc-btn-primary">
                        <i data-lucide="save" style="width:15px;height:15px;"></i> ثبت پلان قسطی
                    </button>
                </form>
            </div>
        </div>
        <script>
        function calcPreview(){
            var total    = parseFloat(document.getElementById("inp-total")    ? document.getElementById("inp-total").value    : 0)||0;
            var down     = parseFloat(document.getElementById("inp-down")     ? document.getElementById("inp-down").value     : 0)||0;
            var count    = parseInt(document.getElementById("inp-count")      ? document.getElementById("inp-count").value    : 1)||1;
            var discount = parseFloat(document.getElementById("inp-discount") ? document.getElementById("inp-discount").value : 0)||0;
            var startStr = document.getElementById("inp-start") ? document.getElementById("inp-start").value.trim() : "";
            var remaining = total - down - discount;
            var each      = count > 0 ? Math.round(remaining / count) : 0;
            if(document.getElementById("calc-each"))      document.getElementById("calc-each").textContent      = each.toLocaleString("fa-IR");
            if(document.getElementById("calc-remaining")) document.getElementById("calc-remaining").textContent = remaining.toLocaleString("fa-IR");
            if(startStr && remaining > 0 && count > 0){
                var parts = startStr.split("/");
                if(parts.length === 3){
                    var y=parseInt(parts[0]),m=parseInt(parts[1]),d=parseInt(parts[2]);
                    var dates=[];
                    for(var i=0;i<count;i++){
                        var nm=m+i,ny=y;
                        while(nm>12){nm-=12;ny++;}
                        dates.push(ny+"/"+String(nm).padStart(2,"0")+"/"+String(d).padStart(2,"0"));
                    }
                    if(document.getElementById("calc-last-date")) document.getElementById("calc-last-date").textContent=dates[dates.length-1]||"—";
                    var dEl=document.getElementById("dates-list");
                    var pEl=document.getElementById("dates-preview");
                    if(pEl) pEl.style.display="block";
                    if(dEl) dEl.innerHTML=dates.map(function(dt,i){
                        return "<span style='background:#fff;border:1px solid var(--dc-neutral-200);border-radius:6px;padding:4px 10px;font-size:12px;direction:ltr;'><span style='color:var(--dc-neutral-400);font-size:10px;margin-left:4px;'>قسط "+(i+1)+"</span>"+dt+"</span>";
                    }).join("");
                }
            } else {
                if(document.getElementById("dates-preview")) document.getElementById("dates-preview").style.display="none";
                if(document.getElementById("calc-last-date")) document.getElementById("calc-last-date").textContent="—";
            }
        }
        document.addEventListener("DOMContentLoaded", calcPreview);
        </script>
        <?php
    }

    // ─── جزئیات اقساط بیمار ────────────────────────────────────
    private function render_patient_installments(int $patient_id): void {
        $patient      = get_post($patient_id);
        $installments = Dental_Installment_Manager::get_patient_installments($patient_id);
        $wallet       = class_exists('Dental_Patient_Wallet') ? Dental_Patient_Wallet::get_balance($patient_id) : 0;
        ?>
        <div class="dc-flex dc-items-center dc-justify-between" style="margin-bottom:20px;flex-wrap:wrap;gap:10px;">
            <div>
                <h2 class="dc-heading-3" style="display:flex;align-items:center;gap:8px;margin:0 0 4px;">
                    <i data-lucide="user" style="width:18px;height:18px;color:var(--dc-primary);"></i>
                    <?php echo esc_html($patient->post_title ?? ''); ?>
                </h2>
                <span style="font-size:13px;color:var(--dc-neutral-600);">
                    کیف پول: <strong><?php echo number_format($wallet); ?> تومان</strong>
                </span>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="<?php echo esc_url(admin_url("admin.php?page=dental-financial&action=new&patient_id={$patient_id}")); ?>"
                   class="dc-btn dc-btn-primary">
                    <i data-lucide="plus-circle" style="width:14px;height:14px;"></i> پلان جدید
                </a>
                <a href="<?php echo esc_url(admin_url("admin.php?page=dental-patients&action=profile&id={$patient_id}")); ?>"
                   class="dc-btn dc-btn-secondary">
                    <i data-lucide="user" style="width:14px;height:14px;"></i> پروفایل
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dental-financial')); ?>"
                   class="dc-btn dc-btn-ghost">← بازگشت</a>
            </div>
        </div>

        <?php if(empty($installments)): ?>
        <div class="dc-card">
            <div class="dc-card-body" style="text-align:center;padding:48px;color:var(--dc-neutral-500);">
                <i data-lucide="receipt" style="width:48px;height:48px;color:var(--dc-neutral-300);margin-bottom:12px;"></i>
                <p>پلان قسطی ثبت نشده</p>
                <a href="<?php echo esc_url(admin_url("admin.php?page=dental-financial&action=new&patient_id={$patient_id}")); ?>"
                   class="dc-btn dc-btn-primary" style="margin-top:12px;">➕ ثبت پلان جدید</a>
            </div>
        </div>
        <?php else: foreach($installments as $plan):
            $items    = Dental_Installment_Manager::get_items((int)$plan['id']);
            $total_paid_items = array_sum(array_column($items, 'paid_amount'));
            $grand_total = (float)$plan['total_amount'];
            $progress = $grand_total > 0 ? min(100, round((((float)$plan['down_payment'] + $total_paid_items) / $grand_total) * 100)) : 0;

            $status_cfg = [
                'active'    => ['var(--dc-primary)',    'فعال'],
                'completed' => ['var(--dc-accent-dark)','تسویه شده'],
                'overdue'   => ['var(--dc-danger)',      'معوقه'],
                'cancelled' => ['var(--dc-neutral-400)','لغو شده'],
            ];
            [$scolor,$slabel] = $status_cfg[$plan['status']] ?? ['#999',$plan['status']];
        ?>
        <div class="dc-card" style="margin-bottom:20px;">
            <div class="dc-card-header" style="flex-wrap:wrap;gap:10px;">
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                        <h3 class="dc-heading-4" style="margin:0;">پلان #<?php echo (int)$plan['id']; ?></h3>
                        <span style="background:<?php echo $scolor; ?>22;color:<?php echo $scolor; ?>;border-radius:12px;padding:2px 10px;font-size:11px;font-weight:700;"><?php echo esc_html($slabel); ?></span>
                    </div>
                    <div style="display:flex;gap:14px;font-size:13px;color:var(--dc-neutral-600);flex-wrap:wrap;">
                        <span>کل: <strong><?php echo number_format($plan['total_amount']); ?> ت</strong></span>
                        <span>پیش‌پرداخت: <strong><?php echo number_format($plan['down_payment']); ?> ت</strong></span>
                        <span>پرداخت اقساط: <strong style="color:var(--dc-accent-dark);"><?php echo number_format($total_paid_items); ?> ت</strong></span>
                        <span><?php echo (int)$plan['paid_items']; ?>/<?php echo (int)$plan['total_items']; ?> قسط</span>
                    </div>
                    <div style="margin-top:10px;height:6px;background:var(--dc-neutral-100);border-radius:3px;overflow:hidden;max-width:500px;">
                        <div style="height:100%;width:<?php echo $progress; ?>%;background:<?php echo $plan['status']==='overdue'?'var(--dc-danger)':'var(--dc-accent)'; ?>;border-radius:3px;transition:width .5s;"></div>
                    </div>
                    <div style="font-size:11px;color:var(--dc-neutral-500);margin-top:3px;"><?php echo $progress; ?>% پرداخت شده</div>
                </div>
                <?php if($plan['status'] !== 'completed' && $plan['status'] !== 'archived' && (int)$plan['paid_items'] === 0): ?>
                <button type="button" class="dc-btn dc-btn-secondary dc-btn-sm"
                    onclick="DentalPlan.editPlan(<?php echo (int)$plan['id']; ?>,<?php echo (int)$plan['installment_count']; ?>,<?php echo (float)$plan['total_amount']; ?>,<?php echo (float)$plan['down_payment']; ?>,<?php echo (float)($plan['discount_amount']??0); ?>)">
                    <i data-lucide="edit-3" style="width:13px;height:13px;"></i> ویرایش
                </button>
                <?php endif; ?>
            </div>
            <div class="dc-card-body" style="padding:0;">
                <?php foreach($items as $item):
                    $status_map = [
                        'paid'    => ['check-circle-2','var(--dc-accent)',      'پرداخت شده'],
                        'overdue' => ['alert-circle',  'var(--dc-danger)',      'معوقه'],
                        'pending' => ['clock',         'var(--dc-neutral-500)', 'در انتظار'],
                        'partial' => ['circle',        'var(--dc-accent-warm)', 'جزئی'],
                    ];
                    [$icon,$icolor,$ilabel] = $status_map[$item['status']] ?? ['circle','#999',''];
                ?>
                <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--dc-neutral-100);">
                    <div style="width:32px;height:32px;border-radius:8px;background:<?php echo $icolor; ?>22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="<?php echo esc_attr($icon); ?>" style="width:16px;height:16px;color:<?php echo $icolor; ?>;"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span style="font-size:13px;font-weight:600;">قسط <?php echo (int)$item['item_number']; ?></span>
                            <span style="font-size:13px;color:var(--dc-neutral-700);"><?php echo number_format($item['amount']); ?> تومان</span>
                            <span style="font-size:11px;color:<?php echo $icolor; ?>;font-weight:600;"><?php echo esc_html($ilabel); ?></span>
                            <?php if($item['status']==='overdue'): ?>
                            <span style="font-size:10px;background:var(--dc-danger-light);color:var(--dc-danger);padding:2px 8px;border-radius:10px;">سررسید گذشته</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:11px;color:var(--dc-neutral-500);margin-top:2px;display:flex;gap:12px;flex-wrap:wrap;">
                            <span>سررسید: <?php echo esc_html($item['due_date_jalali']); ?></span>
                            <?php if($item['paid_date']): ?>
                            <span>پرداخت: <?php echo esc_html(Dental_Jalali::to_jalali($item['paid_date'],'Y/m/d')); ?></span>
                            <?php endif; ?>
                            <?php if($item['payment_method']): ?>
                            <span>روش: <?php echo esc_html($item['payment_method']==='cash'?'نقدی':($item['payment_method']==='wallet'?'کیف پول':$item['payment_method'])); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if($item['status'] !== 'paid' && $item['status'] !== 'waived'): ?>
                    <div style="display:flex;gap:6px;flex-shrink:0;">
                        <form method="post" style="display:inline;">
                            <?php wp_nonce_field('dental_pay_item'); ?>
                            <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
                            <input type="hidden" name="amount"  value="<?php echo (float)$item['amount']; ?>">
                            <input type="hidden" name="method"  value="cash">
                            <button type="submit" name="dental_pay_item" class="dc-btn dc-btn-success dc-btn-sm"
                                onclick="return confirm('پرداخت نقدی ثبت شود؟')">
                                <i data-lucide="banknote" style="width:13px;height:13px;"></i> نقدی
                            </button>
                        </form>
                        <?php if($wallet >= (float)$item['amount']): ?>
                        <form method="post" style="display:inline;">
                            <?php wp_nonce_field('dental_pay_item'); ?>
                            <input type="hidden" name="item_id"     value="<?php echo (int)$item['id']; ?>">
                            <input type="hidden" name="amount"      value="<?php echo (float)$item['amount']; ?>">
                            <input type="hidden" name="method"      value="wallet">
                            <input type="hidden" name="from_wallet" value="1">
                            <button type="submit" name="dental_pay_item" class="dc-btn dc-btn-secondary dc-btn-sm"
                                onclick="return confirm('پرداخت از کیف پول ثبت شود؟')">
                                <i data-lucide="wallet" style="width:13px;height:13px;"></i> کیف پول
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <span style="font-size:12px;color:var(--dc-accent-dark);font-weight:600;">
                        ✅ <?php echo number_format($item['paid_amount']); ?> ت
                    </span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php endforeach; endif; ?>

        <!-- مودال ویرایش -->
        <div id="dc-edit-plan-modal" style="display:none;position:fixed;inset:0;background:rgba(26,39,51,.65);z-index:99999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:14px;width:100%;max-width:480px;margin:20px;overflow:hidden;">
                <div style="background:linear-gradient(135deg,var(--dc-primary),var(--dc-primary-dark));padding:14px 18px;display:flex;align-items:center;justify-content:space-between;color:#fff;">
                    <h4 style="margin:0;font-size:15px;font-family:var(--dc-font-family);">ویرایش پلان قسطی</h4>
                    <button onclick="document.getElementById('dc-edit-plan-modal').style.display='none'" type="button"
                        style="background:rgba(255,255,255,.2);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:15px;">✕</button>
                </div>
                <div style="padding:20px;">
                    <form method="post">
                        <?php wp_nonce_field('dental_edit_plan'); ?>
                        <input type="hidden" name="dental_edit_plan_id" id="edit-plan-id">
                        <input type="hidden" name="patient_id" value="<?php echo (int)$patient_id; ?>">
                        <div style="display:flex;flex-direction:column;gap:12px;">
                            <div class="dc-form-group" style="margin:0;"><label class="dc-label">مبلغ کل (تومان)</label><input type="number" name="edit_total_amount" id="edit-total" class="dc-input" min="0" step="1000" oninput="calcEditPreview()"></div>
                            <div class="dc-form-group" style="margin:0;"><label class="dc-label">پیش‌پرداخت (تومان)</label><input type="number" name="edit_down_payment" id="edit-down" class="dc-input" min="0" step="1000" oninput="calcEditPreview()"></div>
                            <div class="dc-form-group" style="margin:0;"><label class="dc-label">تخفیف (تومان)</label><input type="number" name="edit_discount" id="edit-discount" class="dc-input" min="0" step="1000" value="0" oninput="calcEditPreview()"></div>
                            <div class="dc-form-group" style="margin:0;"><label class="dc-label">تعداد اقساط</label>
                                <select name="edit_installment_count" id="edit-count" class="dc-select" onchange="calcEditPreview()">
                                    <?php for($i=1;$i<=24;$i++): ?><option value="<?php echo $i; ?>"><?php echo $i; ?> قسط</option><?php endfor; ?>
                                </select>
                            </div>
                            <div style="background:var(--dc-primary-light);border-radius:8px;padding:12px;text-align:center;">
                                <div style="font-size:11px;color:var(--dc-neutral-600);margin-bottom:4px;">مبلغ هر قسط</div>
                                <div id="edit-each-amount" style="font-size:22px;font-weight:700;color:var(--dc-primary);">۰</div>
                                <div style="font-size:11px;color:var(--dc-neutral-500);">تومان</div>
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;margin-top:16px;">
                            <button type="submit" name="dental_edit_plan" class="dc-btn dc-btn-primary">💾 ذخیره</button>
                            <button type="button" onclick="document.getElementById('dc-edit-plan-modal').style.display='none'" class="dc-btn dc-btn-ghost">انصراف</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        window.DentalPlan = {
            editPlan: function(id,count,total,down,discount){
                document.getElementById("edit-plan-id").value  = id;
                document.getElementById("edit-total").value    = total;
                document.getElementById("edit-down").value     = down;
                document.getElementById("edit-discount").value = discount;
                document.getElementById("edit-count").value    = count;
                calcEditPreview();
                document.getElementById("dc-edit-plan-modal").style.display = "flex";
            }
        };
        function calcEditPreview(){
            var t=parseFloat(document.getElementById("edit-total").value)||0;
            var d=parseFloat(document.getElementById("edit-down").value)||0;
            var x=parseFloat(document.getElementById("edit-discount").value)||0;
            var c=parseInt(document.getElementById("edit-count").value)||1;
            var each=c>0?Math.round((t-d-x)/c):0;
            document.getElementById("edit-each-amount").textContent=each.toLocaleString("fa-IR");
        }
        </script>
        <?php
    }

    // ─── ذخیره پلان ────────────────────────────────────────────
    private function save_installment(int $patient_id): void {
        Dental_Dev_Logger::log('INFO','save_installment called',[
            'patient_id'=>$patient_id,
            'total_amount'=>$_POST['total_amount']??'MISSING',
            'installment_count'=>$_POST['installment_count']??'MISSING',
            'start_date'=>$_POST['start_date_jalali']??'MISSING',
        ]);
        $result = Dental_Installment_Manager::create(
            $patient_id, 0,
            (float)($_POST['total_amount']??0),
            (float)($_POST['down_payment']??0),
            (int)($_POST['installment_count']??1),
            sanitize_text_field($_POST['start_date_jalali']??''),
            (float)($_POST['discount']??0),
            sanitize_text_field($_POST['notes']??'')
        );
        if($result['success']){
            wp_safe_redirect(admin_url("admin.php?page=dental-financial&action=view&patient_id={$patient_id}&saved=1"));
            exit;
        }
    }

    // ─── ویرایش پلان ───────────────────────────────────────────
    private function update_installment(): void {
        global $wpdb;
        $plan_id  = (int)($_POST['dental_edit_plan_id']??0);
        $pid      = (int)($_POST['patient_id']??0);
        $total    = (float)($_POST['edit_total_amount']??0);
        $down     = (float)($_POST['edit_down_payment']??0);
        $discount = (float)($_POST['edit_discount']??0);
        $count    = (int)($_POST['edit_installment_count']??1);
        if(!$plan_id||!$pid||$total<=0) return;

        $remaining = $total - $down - $discount;
        $each      = $count>0 ? round($remaining/$count) : 0;

        $wpdb->update($wpdb->prefix.'dental_installments',[
            'total_amount'=>$total,'down_payment'=>$down,'remaining_amount'=>$remaining,
            'installment_count'=>$count,'installment_amount'=>$each,'discount_amount'=>$discount,
            'updated_at'=>current_time('mysql'),
        ],['id'=>$plan_id],['%f','%f','%f','%d','%f','%f','%s'],['%d']);

        $wpdb->delete($wpdb->prefix.'dental_installment_items',['installment_id'=>$plan_id],['%d']);
        $dates = Dental_Jalali::get_installment_dates(Dental_Jalali::today(),$count);
        foreach($dates as $i=>$jdate){
            $wpdb->insert($wpdb->prefix.'dental_installment_items',[
                'installment_id'=>$plan_id,'item_number'=>$i+1,
                'due_date_jalali'=>$jdate,'due_date'=>Dental_Jalali::to_gregorian($jdate),
                'amount'=>$each,'paid_amount'=>0,'status'=>'pending',
            ],['%d','%d','%s','%s','%f','%f','%s']);
        }
        wp_safe_redirect(admin_url("admin.php?page=dental-financial&action=view&patient_id={$pid}&saved=1"));
        exit;
    }

    // ─── پرداخت قسط ────────────────────────────────────────────
    private function pay_installment_item(): void {
        $item_id     = (int)($_POST['item_id']??0);
        $amount      = (float)($_POST['amount']??0);
        $method      = sanitize_text_field($_POST['method']??'cash');
        $from_wallet = !empty($_POST['from_wallet']);
        Dental_Installment_Manager::pay_item($item_id,$amount,$method,'',$from_wallet);
        $ref = wp_get_referer() ?: admin_url('admin.php?page=dental-financial');
        wp_safe_redirect(add_query_arg('saved','1',$ref));
        exit;
    }

    // ─── بایگانی پلان ──────────────────────────────────────────
    private function archive_plan(): void {
        global $wpdb;
        $plan_id = (int)($_POST['plan_id']??0);
        if(!$plan_id) return;
        $wpdb->update($wpdb->prefix.'dental_installments',
            ['status'=>'archived','updated_at'=>current_time('mysql')],
            ['id'=>$plan_id],['%s','%s'],['%d']
        );
        wp_safe_redirect(admin_url('admin.php?page=dental-financial&saved=1'));
        exit;
    }
}
