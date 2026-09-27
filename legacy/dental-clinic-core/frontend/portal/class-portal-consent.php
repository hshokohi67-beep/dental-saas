<?php
defined('ABSPATH') || exit;

class Dental_Portal_Consent {

    private int $patient_id;

    public function __construct(int $patient_id) {
        $this->patient_id = $patient_id;
    }

    public function render(): void {
        $pending = Dental_Consent_Form::get_pending_consents($this->patient_id);
        $signed  = Dental_Consent_Form::get_signed_consents($this->patient_id);
        $patient = get_post($this->patient_id);

        // handle save
        if (isset($_POST['dental_sign_consent']) && check_admin_referer('dental_consent_' . $this->patient_id)) {
            $key  = sanitize_key($_POST['consent_key'] ?? '');
            $sig  = $_POST['signature_data'] ?? '';
            if ($key && $sig) {
                Dental_Consent_Form::save_consent($this->patient_id, $key, $sig);
                wp_safe_redirect(add_query_arg('ptab','consent', get_permalink()));
                exit;
            }
        }
        ?>

        <!-- رضایت‌نامه‌های در انتظار -->
        <?php if (!empty($pending)): ?>
        <div style="background:#FEF6E4;border:1px solid #F0A500;border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <i data-lucide="alert-triangle" style="width:20px;height:20px;color:#F0A500;flex-shrink:0;"></i>
            <div>
                <strong style="font-size:13px;color:#7a5200;"><?php echo count($pending); ?> رضایت‌نامه در انتظار امضا</strong>
                <div style="font-size:12px;color:#7a5200;margin-top:2px;">لطفاً قبل از انجام درمان، رضایت‌نامه‌های زیر را مطالعه و امضا کنید.</div>
            </div>
        </div>

        <?php foreach($pending as $item):
            $q_names = [1=>'بالا راست',2=>'بالا چپ',3=>'پایین چپ',4=>'پایین راست',
                        5=>'شیری بالا راست',6=>'شیری بالا چپ',7=>'شیری پایین چپ',8=>'شیری پایین راست'];
            $q     = (int)($item['tooth_number']/10);
            $n     = $item['tooth_number'] % 10;
            $ttype = $item['tooth_type'] === 'primary' ? ' (شیری)' : '';
            $tooth_info = 'دندان ' . $n . ' — ' . ($q_names[$q]??'') . $ttype;
            $doctor = wp_get_current_user();

            $template = Dental_Consent_Form::get_template($item['treatment_code']);
            $template_filled = str_replace(
                ['{patient_name}','{tooth_info}','{treatment_name}','{doctor_name}','{date}'],
                [$patient->post_title??'', $tooth_info, $item['treatment_name'], $doctor->display_name??'', Dental_Jalali::today()],
                $template
            );
        ?>
        <div style="background:#fff;border-radius:12px;border:2px solid #F0A500;margin-bottom:20px;overflow:hidden;">
            <!-- هدر -->
            <div style="background:linear-gradient(135deg,#1A6B8A,#0F4D66);padding:14px 18px;color:#fff;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <i data-lucide="file-signature" style="width:20px;height:20px;"></i>
                    <div>
                        <div style="font-size:15px;font-weight:700;">رضایت‌نامه <?php echo esc_html($item['treatment_name']); ?></div>
                        <div style="font-size:12px;opacity:.8;"><?php echo esc_html($tooth_info); ?></div>
                    </div>
                </div>
            </div>

            <div style="padding:20px;">
                <!-- متن رضایت‌نامه -->
                <div style="background:#F8FAFB;border:1px solid #EEF2F5;border-radius:8px;padding:16px;margin-bottom:20px;font-size:13px;line-height:2;white-space:pre-line;color:#1A2733;">
                    <?php echo nl2br(esc_html($template_filled)); ?>
                </div>

                <!-- فرم امضا -->
                <form method="post" id="consent-form-<?php echo esc_attr($item['key']); ?>">
                    <?php wp_nonce_field('dental_consent_' . $this->patient_id); ?>
                    <input type="hidden" name="dental_sign_consent" value="1">
                    <input type="hidden" name="consent_key" value="<?php echo esc_attr($item['key']); ?>">
                    <input type="hidden" name="signature_data" id="sig-data-<?php echo esc_attr($item['key']); ?>">

                    <div style="margin-bottom:16px;">
                        <label style="font-size:13px;font-weight:700;color:#1A2733;display:block;margin-bottom:8px;">
                            ✍️ امضای دیجیتال
                        </label>
                        <div style="position:relative;border:2px dashed #C8D4DC;border-radius:10px;background:#FAFAFA;overflow:hidden;">
                            <canvas id="sig-canvas-<?php echo esc_attr($item['key']); ?>"
                                width="600" height="180"
                                style="width:100%;height:180px;cursor:crosshair;touch-action:none;display:block;">
                            </canvas>
                            <div id="sig-hint-<?php echo esc_attr($item['key']); ?>"
                                style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;color:#C8D4DC;font-size:14px;">
                                اینجا امضا کنید
                            </div>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">
                            <span style="font-size:11px;color:#A0B4C0;">با موس یا انگشت امضا کنید</span>
                            <button type="button" onclick="clearSig('<?php echo esc_js($item['key']); ?>')"
                                style="background:none;border:none;color:#E05252;font-size:12px;cursor:pointer;font-family:Vazirmatn,Tahoma;">
                                🗑️ پاک کردن
                            </button>
                        </div>
                    </div>

                    <!-- تأیید خواندن -->
                    <label style="display:flex;align-items:flex-start;gap:10px;margin-bottom:16px;cursor:pointer;font-size:13px;color:#1A2733;line-height:1.6;">
                        <input type="checkbox" id="confirm-<?php echo esc_attr($item['key']); ?>"
                            style="margin-top:3px;accent-color:#1A6B8A;width:16px;height:16px;flex-shrink:0;">
                        متن رضایت‌نامه را مطالعه کردم و با آگاهی کامل از مزایا و خطرات احتمالی، رضایت خود را برای انجام <?php echo esc_html($item['treatment_name']); ?> اعلام می‌نمایم.
                    </label>

                    <button type="button"
                        onclick="submitConsent('<?php echo esc_js($item['key']); ?>')"
                        style="background:linear-gradient(135deg,#1A6B8A,#0F4D66);color:#fff;border:none;border-radius:8px;padding:12px 24px;font-size:14px;font-weight:700;font-family:Vazirmatn,Tahoma;cursor:pointer;width:100%;">
                        ✅ تأیید و امضای رضایت‌نامه
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- رضایت‌نامه‌های امضاشده -->
        <?php if (!empty($signed)): ?>
        <div style="background:#fff;border-radius:12px;border:1px solid #EEF2F5;overflow:hidden;">
            <div style="padding:14px 18px;border-bottom:1px solid #EEF2F5;display:flex;align-items:center;gap:8px;">
                <i data-lucide="check-circle-2" style="width:16px;height:16px;color:#2ECC9A;"></i>
                <span style="font-size:13px;font-weight:700;color:#1A2733;">رضایت‌نامه‌های امضاشده</span>
            </div>
            <div style="padding:0 18px;">
                <?php foreach($signed as $key => $data):
                    $clean_key = str_replace('_consent_signed_','',$key);
                    $parts     = explode('_', $clean_key);
                    $code      = end($parts);
                    $treatments = Dental_Consent_Form::get_consent_required_treatments();
                    $tx_name    = $treatments[$code] ?? $code;
                ?>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #F5F5F5;gap:10px;flex-wrap:wrap;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:32px;height:32px;border-radius:8px;background:#E8FAF4;display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="file-check-2" style="width:16px;height:16px;color:#2ECC9A;"></i>
                        </div>
                        <div>
                            <div style="font-size:13px;font-weight:600;color:#1A2733;"><?php echo esc_html($tx_name); ?></div>
                            <div style="font-size:11px;color:#A0B4C0;">
                                امضا: <?php echo esc_html($data['signed_jalali']??''); ?> —
                                پزشک: <?php echo esc_html($data['doctor_name']??''); ?>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($data['attach_id'])): ?>
                    <a href="<?php echo esc_url(add_query_arg(['dental_print_consent'=>$clean_key,'dental_print'=>'consent'], get_permalink())); ?>"
                       target="_blank"
                       style="display:flex;align-items:center;gap:6px;background:#E8F4F8;color:#1A6B8A;padding:6px 12px;border-radius:6px;font-size:12px;text-decoration:none;">
                        <i data-lucide="printer" style="width:13px;height:13px;"></i>
                        پرینت
                    </a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($pending) && empty($signed)): ?>
        <div style="text-align:center;padding:48px;background:#fff;border-radius:12px;border:1px solid #EEF2F5;">
            <div style="font-size:48px;margin-bottom:12px;">📋</div>
            <p style="color:#A0B4C0;font-size:14px;">رضایت‌نامه‌ای ثبت نشده</p>
        </div>
        <?php endif; ?>

        <script>
        var _sigPads = {};

        function initCanvas(key) {
            var canvas = document.getElementById("sig-canvas-" + key);
            if (!canvas || _sigPads[key]) return;
            var ctx    = canvas.getContext("2d");
            var drawing = false;
            var hint   = document.getElementById("sig-hint-" + key);

            // تنظیم resolution
            var dpr = window.devicePixelRatio || 1;
            var rect = canvas.getBoundingClientRect();
            canvas.width  = rect.width  * dpr;
            canvas.height = rect.height * dpr;
            ctx.scale(dpr, dpr);
            ctx.strokeStyle = "#1A2733";
            ctx.lineWidth   = 2.5;
            ctx.lineCap     = "round";
            ctx.lineJoin    = "round";

            function getPos(e) {
                var r = canvas.getBoundingClientRect();
                var touch = e.touches ? e.touches[0] : e;
                return {
                    x: (touch.clientX - r.left),
                    y: (touch.clientY - r.top)
                };
            }

            function start(e) {
                e.preventDefault();
                drawing = true;
                if (hint) hint.style.display = "none";
                var p = getPos(e);
                ctx.beginPath();
                ctx.moveTo(p.x, p.y);
            }
            function move(e) {
                e.preventDefault();
                if (!drawing) return;
                var p = getPos(e);
                ctx.lineTo(p.x, p.y);
                ctx.stroke();
            }
            function stop(e) {
                drawing = false;
                // ذخیره data URL
                document.getElementById("sig-data-" + key).value = canvas.toDataURL("image/png");
            }

            canvas.addEventListener("mousedown",  start);
            canvas.addEventListener("mousemove",  move);
            canvas.addEventListener("mouseup",    stop);
            canvas.addEventListener("mouseleave", stop);
            canvas.addEventListener("touchstart", start, {passive:false});
            canvas.addEventListener("touchmove",  move,  {passive:false});
            canvas.addEventListener("touchend",   stop);

            _sigPads[key] = { ctx: ctx, canvas: canvas };
        }

        function clearSig(key) {
            var pad = _sigPads[key];
            if (!pad) return;
            var r = pad.canvas.getBoundingClientRect();
            pad.ctx.clearRect(0, 0, r.width * (window.devicePixelRatio||1), r.height * (window.devicePixelRatio||1));
            document.getElementById("sig-data-" + key).value = "";
            var hint = document.getElementById("sig-hint-" + key);
            if (hint) hint.style.display = "flex";
        }

        function submitConsent(key) {
            var confirm = document.getElementById("confirm-" + key);
            if (!confirm || !confirm.checked) {
                alert("لطفاً تأیید کنید که متن رضایت‌نامه را مطالعه کرده‌اید.");
                return;
            }
            var sigData = document.getElementById("sig-data-" + key).value;
            if (!sigData || sigData === "") {
                alert("لطفاً در کادر امضا، امضای خود را رسم کنید.");
                return;
            }
            document.getElementById("consent-form-" + key).submit();
        }

        // init همه canvas‌ها
        document.addEventListener("DOMContentLoaded", function() {
            <?php foreach($pending as $item): ?>
            initCanvas("<?php echo esc_js($item['key']); ?>");
            <?php endforeach; ?>
            if (typeof lucide !== "undefined") lucide.createIcons();
        });
        </script>
        <?php
    }
}
