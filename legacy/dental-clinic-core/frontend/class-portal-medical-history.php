<?php
defined('ABSPATH') || exit;

class Dental_Portal_Medical_History {

    private int $patient_id;

    public function __construct(int $patient_id) {
        $this->patient_id = $patient_id;
    }

    // ─── رفع همون باگ «headers already sent» — این‌بار برای فرانت‌اند.
    // چون این صفحه یه Shortcode/قالب سایته، قبل از این تابع، هدر/منوی
    // قالب سایت از قبل چاپ می‌شه — پس wp_safe_redirect() هیچ‌وقت کار
    // نمی‌کرد. این متد جدا شد تا از template_redirect (خیلی زودتر،
    // قبل از رندر قالب) صدا زده بشه.
    public function maybe_handle_post(): void {
        if (!isset($_POST['dental_save_medhistory'])) return;
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'dental_medhistory_' . $this->patient_id)) return;
        $sig = $_POST['signature_data'] ?? '';
        if ($sig) {
            Dental_Medical_History::save_patient_signed($this->patient_id, $_POST, $sig);
            wp_safe_redirect(add_query_arg('ptab','medhistory', get_permalink()));
            exit;
        }
    }

    public function render(): void {
        $existing = Dental_Medical_History::get($this->patient_id);
        $sig_info = Dental_Medical_History::get_signature($this->patient_id);
        $is_signed = Dental_Medical_History::is_signed_by_patient($this->patient_id);
        $diseases = Dental_Medical_History::get_disease_list();
        ?>

        <?php if ($is_signed): ?>
        <div style="background:#fff;border-radius:12px;border:1px solid #EEF2F5;overflow:hidden;margin-bottom:20px;">
            <div style="padding:14px 18px;border-bottom:1px solid #EEF2F5;display:flex;align-items:center;gap:8px;background:#E8FAF4;">
                <i data-lucide="check-circle-2" style="width:16px;height:16px;color:#2ECC9A;"></i>
                <span style="font-size:13px;font-weight:700;color:#1A2733;">شرح‌حال شما ثبت و امضا شده — تاریخ: <?php echo esc_html(Dental_Jalali::to_jalali(date('Y-m-d',strtotime($sig_info['signed_at'])),'Y/m/d')); ?></span>
            </div>
            <div style="padding:18px;font-size:13px;color:#1A2733;line-height:2;">
                <?php if (!empty($existing['diseases'])): ?>
                <div><b>بیماری‌های زمینه‌ای:</b> <?php echo esc_html(implode('، ', array_map(fn($d)=>$diseases[$d]['label']??$d, $existing['diseases']))); ?></div>
                <?php endif; ?>
                <?php if (!empty($existing['allergies'])): ?><div><b>آلرژی‌ها:</b> <?php echo esc_html($existing['allergies']); ?></div><?php endif; ?>
                <?php if (!empty($existing['medications'])): ?><div><b>داروهای مصرفی:</b> <?php echo esc_html($existing['medications']); ?></div><?php endif; ?>
            </div>
        </div>
        <div style="text-align:center;margin-bottom:20px;">
            <a href="#" onclick="document.getElementById('dc-medhistory-edit').style.display='block';this.style.display='none';return false;" style="font-size:12px;color:#1A6B8A;">✏️ ویرایش و امضای مجدد شرح‌حال</a>
        </div>
        <div id="dc-medhistory-edit" style="display:none;">
        <?php endif; ?>

        <div style="background:#fff;border-radius:12px;border:2px solid <?php echo $is_signed?'#EEF2F5':'#F0A500'; ?>;overflow:hidden;">
            <div style="background:linear-gradient(135deg,#1A6B8A,#0F4D66);padding:14px 18px;color:#fff;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <i data-lucide="heart-pulse" style="width:20px;height:20px;"></i>
                    <div style="font-size:15px;font-weight:700;">فرم شرح‌حال پزشکی</div>
                </div>
            </div>
            <form method="post" id="medhistory-form">
                <?php wp_nonce_field('dental_medhistory_' . $this->patient_id); ?>
                <input type="hidden" name="dental_save_medhistory" value="1">
                <input type="hidden" name="signature_data" id="sig-data-mh">
                <div style="padding:20px;">
                    <label style="font-size:13px;font-weight:700;display:block;margin-bottom:10px;">آیا به هرکدام از موارد زیر مبتلا هستید؟</label>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:20px;">
                        <?php foreach($diseases as $key => $d): ?>
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;padding:8px 10px;background:#F8FAFB;border-radius:8px;cursor:pointer;">
                            <input type="checkbox" name="diseases[]" value="<?php echo esc_attr($key); ?>" <?php checked(in_array($key,$existing['diseases']??[])); ?> style="width:16px;height:16px;accent-color:#1A6B8A;">
                            <?php echo esc_html($d['label']); ?>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="font-size:13px;font-weight:700;display:block;margin-bottom:6px;">آلرژی دارویی (اگه دارید بنویسید)</label>
                        <input type="text" name="allergies" value="<?php echo esc_attr($existing['allergies']??''); ?>" style="width:100%;padding:10px 12px;border:1px solid #C8D4DC;border-radius:8px;font-family:Vazirmatn,Tahoma;font-size:13px;">
                    </div>
                    <div style="margin-bottom:16px;">
                        <label style="font-size:13px;font-weight:700;display:block;margin-bottom:6px;">داروهایی که در حال حاضر مصرف می‌کنید</label>
                        <textarea name="medications" rows="2" style="width:100%;padding:10px 12px;border:1px solid #C8D4DC;border-radius:8px;font-family:Vazirmatn,Tahoma;font-size:13px;"><?php echo esc_textarea($existing['medications']??''); ?></textarea>
                    </div>
                    <div style="margin-bottom:20px;">
                        <label style="font-size:13px;font-weight:700;display:block;margin-bottom:6px;">توضیح دیگری که لازم است بدانیم</label>
                        <textarea name="medical_notes" rows="2" style="width:100%;padding:10px 12px;border:1px solid #C8D4DC;border-radius:8px;font-family:Vazirmatn,Tahoma;font-size:13px;"><?php echo esc_textarea($existing['notes']??''); ?></textarea>
                    </div>

                    <label style="font-size:13px;font-weight:700;display:block;margin-bottom:8px;">✍️ امضای دیجیتال</label>
                    <div style="position:relative;border:2px dashed #C8D4DC;border-radius:10px;background:#FAFAFA;overflow:hidden;margin-bottom:8px;">
                        <canvas id="sig-canvas-mh" width="600" height="160" style="width:100%;height:160px;cursor:crosshair;touch-action:none;display:block;"></canvas>
                        <div id="sig-hint-mh" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;color:#C8D4DC;font-size:14px;">اینجا امضا کنید</div>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:20px;">
                        <span style="font-size:11px;color:#A0B4C0;">با موس یا انگشت امضا کنید</span>
                        <button type="button" onclick="clearMhSig()" style="background:none;border:none;color:#E05252;font-size:12px;cursor:pointer;font-family:Vazirmatn,Tahoma;">🗑️ پاک کردن</button>
                    </div>

                    <label style="display:flex;align-items:flex-start;gap:10px;margin-bottom:16px;cursor:pointer;font-size:13px;line-height:1.6;">
                        <input type="checkbox" id="confirm-mh" style="margin-top:3px;accent-color:#1A6B8A;width:16px;height:16px;flex-shrink:0;">
                        صحت اطلاعات فوق را تأیید می‌کنم و می‌دانم که پنهان‌کردن اطلاعات پزشکی می‌تواند بر روند درمان تأثیر بگذارد.
                    </label>
                    <button type="button" onclick="submitMhForm()" style="background:linear-gradient(135deg,#1A6B8A,#0F4D66);color:#fff;border:none;border-radius:8px;padding:12px 24px;font-size:14px;font-weight:700;font-family:Vazirmatn,Tahoma;cursor:pointer;width:100%;">
                        ✅ تأیید و ثبت شرح‌حال
                    </button>
                </div>
            </form>
        </div>
        <?php if ($is_signed): ?></div><?php endif; ?>

        <script>
        (function(){
            var canvas = document.getElementById("sig-canvas-mh");
            if (!canvas) return;
            var ctx = canvas.getContext("2d");
            var drawing = false;
            var hint = document.getElementById("sig-hint-mh");
            var dpr = window.devicePixelRatio || 1;
            var rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * dpr; canvas.height = rect.height * dpr;
            ctx.scale(dpr, dpr);
            ctx.strokeStyle = "#1A2733"; ctx.lineWidth = 2.5; ctx.lineCap = "round"; ctx.lineJoin = "round";
            function getPos(e){ var r=canvas.getBoundingClientRect(); var t=e.touches?e.touches[0]:e; return {x:t.clientX-r.left, y:t.clientY-r.top}; }
            function start(e){ e.preventDefault(); drawing=true; if(hint) hint.style.display="none"; var p=getPos(e); ctx.beginPath(); ctx.moveTo(p.x,p.y); }
            function move(e){ e.preventDefault(); if(!drawing) return; var p=getPos(e); ctx.lineTo(p.x,p.y); ctx.stroke(); }
            function stop(){ drawing=false; document.getElementById("sig-data-mh").value = canvas.toDataURL("image/png"); }
            canvas.addEventListener("mousedown",start); canvas.addEventListener("mousemove",move);
            canvas.addEventListener("mouseup",stop); canvas.addEventListener("mouseleave",stop);
            canvas.addEventListener("touchstart",start,{passive:false}); canvas.addEventListener("touchmove",move,{passive:false}); canvas.addEventListener("touchend",stop);
            window._mhCanvas = {ctx:ctx, canvas:canvas};
        })();
        function clearMhSig(){
            var p = window._mhCanvas; if(!p) return;
            var r = p.canvas.getBoundingClientRect();
            p.ctx.clearRect(0,0,r.width*(window.devicePixelRatio||1),r.height*(window.devicePixelRatio||1));
            document.getElementById("sig-data-mh").value = "";
            var hint = document.getElementById("sig-hint-mh"); if(hint) hint.style.display="flex";
        }
        function submitMhForm(){
            if (!document.getElementById("confirm-mh").checked) { alert("لطفاً تأیید صحت اطلاعات را بزنید."); return; }
            if (!document.getElementById("sig-data-mh").value) { alert("لطفاً در کادر، امضای خود را رسم کنید."); return; }
            document.getElementById("medhistory-form").submit();
        }
        if(typeof lucide!=="undefined") lucide.createIcons();
        </script>
        <?php
    }
}
