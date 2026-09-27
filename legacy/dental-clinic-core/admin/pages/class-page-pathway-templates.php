<?php
defined('ABSPATH') || exit;

class Dental_Page_Pathway_Templates {

    public function render(): void {
        if (!class_exists('Dental_Pathway_Manager') || !Dental_Pathway_Manager::is_enabled()) {
            echo '<div class="dental-admin-wrap"><div class="dc-card"><div class="dc-card-body" style="text-align:center;padding:40px;">این قابلیت از تنظیمات ← امکانات خاموشه.</div></div></div>';
            return;
        }

        if (isset($_POST['dental_save_pathway_template']) && check_admin_referer('dental_pathway_template')) {
            $id = (int)($_POST['template_id'] ?? 0) ?: null;
            $steps = [];
            $titles = $_POST['step_title'] ?? [];
            foreach ($titles as $i => $title) {
                if (empty(trim($title))) continue;
                $steps[] = [
                    'title'      => $title,
                    'catalog_id' => $_POST['step_catalog_id'][$i] ?? '',
                    'interval'   => $_POST['step_interval'][$i] ?? '',
                    'depends_on' => $_POST['step_depends'][$i] ?? '',
                    'notes'      => $_POST['step_notes'][$i] ?? '',
                ];
            }
            $template_id = Dental_Pathway_Manager::save_template($id, $_POST['template_name'] ?? '', $_POST['template_description'] ?? '', $steps);
            if (class_exists('Dental_Audit_Log')) {
                Dental_Audit_Log::log('settings_changed', ($id ? 'ویرایش' : 'ساخت') . " قالب مسیر درمان «{$_POST['template_name']}»", ['entity_type'=>'pathway_template','entity_id'=>$template_id]);
            }
            echo '<div class="notice notice-success"><p>✅ قالب ذخیره شد.</p></div>';
        }
        if (isset($_GET['deactivate_template'])) {
            global $wpdb;
            $wpdb->update($wpdb->prefix.'dental_pathway_templates', ['is_active'=>0], ['id'=>(int)$_GET['deactivate_template']]);
        }

        $edit_id = (int)($_GET['edit'] ?? 0);
        if ($edit_id) { $this->render_form($edit_id); return; }
        if (isset($_GET['new'])) { $this->render_form(0); return; }

        $templates = Dental_Pathway_Manager::get_templates(false);
        ?>
        <div class="dental-admin-wrap">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h1 class="dc-heading-2" style="margin:0;display:flex;align-items:center;gap:10px;">
                    <i data-lucide="route" style="width:26px;height:26px;color:var(--dc-primary);"></i>
                    قالب‌های مسیر درمان
                </h1>
                <a href="<?php echo esc_url(add_query_arg('new',1)); ?>" class="dc-btn dc-btn-primary dc-btn-sm">➕ قالب جدید</a>
            </div>
            <p class="dc-text-muted" style="margin-bottom:20px;">قالب‌های پیش‌فرض («⭐ پیش‌فرض») بر اساس دانش عمومی/پایه‌ی دندان‌پزشکی seed شدن — کاملاً قابل ویرایش/غیرفعال‌کردن. هر قالب دستی هم که بسازید، دقیقاً همینجا کنارشون میاد.</p>

            <?php foreach($templates as $t): ?>
            <div class="dc-card" style="margin-bottom:14px;<?php echo !$t['is_active']?'opacity:.5;':''; ?>">
                <div class="dc-card-header">
                    <h3 class="dc-heading-4" style="font-size:13px;">
                        <?php echo $t['is_default'] ? '⭐ ' : ''; ?><?php echo esc_html($t['name']); ?>
                        <span style="font-size:11px;color:var(--dc-neutral-500);font-weight:400;"> — <?php echo count($t['steps']); ?> مرحله</span>
                    </h3>
                    <div style="display:flex;gap:8px;">
                        <a href="<?php echo esc_url(add_query_arg('edit',$t['id'])); ?>" class="dc-btn dc-btn-ghost dc-btn-sm">✏️ ویرایش</a>
                        <?php if ($t['is_active']): ?>
                        <a href="<?php echo esc_url(add_query_arg('deactivate_template',$t['id'])); ?>" onclick="return confirm('غیرفعال بشه؟ قالب‌های استفاده‌شده‌ی قبلی دست‌نخورده می‌مونن.');" class="dc-btn dc-btn-ghost dc-btn-sm" style="color:var(--dc-danger);">غیرفعال</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="padding:12px 16px;">
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        <?php foreach($t['steps'] as $i => $s): ?>
                        <span style="font-size:11px;background:var(--dc-neutral-50);padding:4px 10px;border-radius:12px;">
                            <?php echo $i+1; ?>. <?php echo esc_html($s['title']); ?>
                            <?php if($s['suggested_interval_days']): ?> <span style="color:var(--dc-neutral-400);">(+<?php echo $s['suggested_interval_days']; ?>روز)</span><?php endif; ?>
                        </span>
                        <?php if ($i < count($t['steps'])-1): ?><span style="color:var(--dc-neutral-300);">←</span><?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($t['description']): ?><p style="font-size:11px;color:var(--dc-neutral-500);margin-top:8px;"><?php echo esc_html($t['description']); ?></p><?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <script>if(typeof lucide!=="undefined")lucide.createIcons();</script>
        <?php
    }

    private function render_form(int $id): void {
        $template = null;
        if ($id) {
            $all = Dental_Pathway_Manager::get_templates(false);
            foreach ($all as $t) { if ($t['id'] == $id) { $template = $t; break; } }
        }
        $steps = $template ? $template['steps'] : [['title'=>'','catalog_id'=>null,'suggested_interval_days'=>null,'depends_on_order'=>null,'notes'=>null]];
        ?>
        <div class="dental-admin-wrap">
            <a href="<?php echo esc_url(remove_query_arg(['edit','new'])); ?>" style="font-size:12px;color:var(--dc-primary);text-decoration:none;display:inline-block;margin-bottom:10px;">← بازگشت به لیست</a>
            <h1 class="dc-heading-2" style="margin-bottom:16px;"><?php echo $id ? 'ویرایش قالب' : 'قالب جدید'; ?></h1>

            <form method="post" id="dc-tpl-form">
                <?php wp_nonce_field('dental_pathway_template'); ?>
                <input type="hidden" name="template_id" value="<?php echo (int)$id; ?>">

                <div class="dc-card" style="margin-bottom:16px;">
                    <div class="dc-card-body" style="display:flex;flex-direction:column;gap:12px;">
                        <div>
                            <label class="dc-label">نام قالب</label>
                            <input type="text" name="template_name" class="dc-input" required value="<?php echo esc_attr($template['name'] ?? ''); ?>">
                        </div>
                        <div>
                            <label class="dc-label">توضیح (اختیاری)</label>
                            <input type="text" name="template_description" class="dc-input" value="<?php echo esc_attr($template['description'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="dc-card">
                    <div class="dc-card-header"><h3 class="dc-heading-4">مراحل (به ترتیب)</h3></div>
                    <div id="dc-tpl-steps" style="padding:16px;">
                        <?php foreach($steps as $idx => $s): ?>
                        <?php $this->render_step_row($idx, $s); ?>
                        <?php endforeach; ?>
                    </div>
                    <div style="padding:0 16px 16px;">
                        <button type="button" onclick="dcAddTplStep()" class="dc-btn dc-btn-ghost dc-btn-sm">➕ افزودن مرحله</button>
                    </div>
                </div>

                <div style="margin-top:16px;">
                    <button type="submit" name="dental_save_pathway_template" class="dc-btn dc-btn-primary">💾 ذخیره قالب</button>
                </div>
            </form>
        </div>

        <script>
        var dcTplStepCount = <?php echo count($steps); ?>;
        function dcAddTplStep(){
            var wrap = document.createElement('div');
            wrap.innerHTML = <?php
                ob_start();
                $this->render_step_row('__IDX__', ['title'=>'','catalog_id'=>null,'suggested_interval_days'=>null,'depends_on_order'=>null,'notes'=>null]);
                $tpl_html = ob_get_clean();
                echo wp_json_encode($tpl_html);
            ?>.replace(/__IDX__/g, dcTplStepCount);
            document.getElementById('dc-tpl-steps').appendChild(wrap.firstElementChild);
            dcTplStepCount++;
        }
        function dcRemoveTplStep(btn){ btn.closest('.dc-tpl-step-row').remove(); }
        </script>
        <?php
    }

    private function render_step_row($idx, array $s): void {
        ?>
        <div class="dc-tpl-step-row" style="display:grid;grid-template-columns:auto 2fr 1fr 1fr 2fr auto;gap:8px;align-items:end;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--dc-neutral-50);">
            <div style="font-weight:700;color:var(--dc-neutral-400);padding-bottom:8px;"><?php echo is_numeric($idx) ? $idx+1 : ''; ?></div>
            <div>
                <label class="dc-label" style="font-size:10px;">عنوان مرحله</label>
                <input type="text" name="step_title[]" class="dc-input" required value="<?php echo esc_attr($s['title'] ?? ''); ?>" style="height:34px;">
            </div>
            <div>
                <label class="dc-label" style="font-size:10px;">فاصله (روز)</label>
                <input type="number" name="step_interval[]" class="dc-input" value="<?php echo esc_attr($s['suggested_interval_days'] ?? ''); ?>" style="height:34px;">
            </div>
            <div>
                <label class="dc-label" style="font-size:10px;">پیش‌نیاز (شماره مرحله)</label>
                <input type="number" name="step_depends[]" class="dc-input" min="1" value="<?php echo esc_attr($s['depends_on_order'] ?? ''); ?>" style="height:34px;">
            </div>
            <div>
                <label class="dc-label" style="font-size:10px;">توضیح (اختیاری)</label>
                <input type="text" name="step_notes[]" class="dc-input" value="<?php echo esc_attr($s['notes'] ?? ''); ?>" style="height:34px;">
            </div>
            <button type="button" onclick="dcRemoveTplStep(this)" style="background:none;border:none;color:var(--dc-danger);cursor:pointer;padding-bottom:8px;">✕</button>
            <input type="hidden" name="step_catalog_id[]" value="<?php echo esc_attr($s['catalog_id'] ?? ''); ?>">
        </div>
        <?php
    }
}
