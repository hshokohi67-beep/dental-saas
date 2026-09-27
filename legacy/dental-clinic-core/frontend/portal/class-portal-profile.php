<?php
defined('ABSPATH') || exit;

class Dental_Portal_Profile {

    private int $patient_id;

    public function __construct(int $patient_id) {
        $this->patient_id = $patient_id;
    }

    public function render(): void {
        $patient  = get_post($this->patient_id);
        $f        = fn($k) => get_post_meta($this->patient_id,$k,true);
        $user_id  = get_current_user_id();
        $user     = get_userdata($user_id);

        // ─── تفکیک نام/نام‌خانوادگی برای نمایش — چون توی دیتابیس هنوز
        // یه post_title واحده (برای هماهنگی کامل با پنل ادمین)، اینجا
        // فقط برای UI بهتر جداشون می‌کنیم، موقع ذخیره دوباره می‌چسبونیم
        $full_name = $patient->post_title ?? '';
        $name_parts = explode(' ', $full_name, 2);
        $first_name = $name_parts[0] ?? '';
        $last_name  = $name_parts[1] ?? '';
        $national_id = $f('_patient_national_id');
        ?>
        <div style="max-width:560px;">
            <div style="background:#fff;border-radius:12px;border:1px solid #EEF2F5;overflow:hidden;">
                <div style="padding:14px 16px;border-bottom:1px solid #EEF2F5;display:flex;align-items:center;gap:8px;">
                    <i data-lucide="user" style="width:16px;height:16px;color:#1A6B8A;"></i>
                    <span style="font-size:13px;font-weight:700;color:#1A2733;">اطلاعات شخصی</span>
                </div>
                <div style="padding:20px;">
                    <div id="portal-profile-msg" style="display:none;margin-bottom:14px;border-radius:8px;padding:10px 14px;font-size:13px;"></div>

                    <div style="display:flex;flex-direction:column;gap:14px;">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div>
                                <label style="font-size:12px;color:#7A96A4;display:block;margin-bottom:5px;font-weight:600;">نام</label>
                                <input type="text" id="portal-fname" value="<?php echo esc_attr($first_name); ?>"
                                    style="width:100%;padding:10px 14px;border:1px solid #C8D4DC;border-radius:8px;font-family:Vazirmatn,Tahoma;font-size:13px;box-sizing:border-box;direction:rtl;">
                            </div>
                            <div>
                                <label style="font-size:12px;color:#7A96A4;display:block;margin-bottom:5px;font-weight:600;">نام خانوادگی</label>
                                <input type="text" id="portal-lname" value="<?php echo esc_attr($last_name); ?>"
                                    style="width:100%;padding:10px 14px;border:1px solid #C8D4DC;border-radius:8px;font-family:Vazirmatn,Tahoma;font-size:13px;box-sizing:border-box;direction:rtl;">
                            </div>
                        </div>
                        <div>
                            <label style="font-size:12px;color:#7A96A4;display:block;margin-bottom:5px;font-weight:600;">کد ملی</label>
                            <input type="text" id="portal-national-id" value="<?php echo esc_attr($national_id); ?>" dir="ltr" maxlength="10" inputmode="numeric" placeholder="۱۰ رقم"
                                style="width:100%;padding:10px 14px;border:1px solid #C8D4DC;border-radius:8px;font-family:Vazirmatn,Tahoma;font-size:13px;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:12px;color:#7A96A4;display:block;margin-bottom:5px;font-weight:600;">شماره موبایل</label>
                            <input type="tel" id="portal-phone" value="<?php echo esc_attr($f('_patient_mobile')); ?>" dir="ltr"
                                style="width:100%;padding:10px 14px;border:1px solid #C8D4DC;border-radius:8px;font-family:Vazirmatn,Tahoma;font-size:13px;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:12px;color:#7A96A4;display:block;margin-bottom:5px;font-weight:600;">ایمیل</label>
                            <input type="email" value="<?php echo esc_attr($user->user_email??''); ?>" disabled dir="ltr"
                                style="width:100%;padding:10px 14px;border:1px solid #EEF2F5;border-radius:8px;font-family:Vazirmatn,Tahoma;font-size:13px;box-sizing:border-box;background:#F8FAFB;color:#A0B4C0;">
                        </div>
                        <?php if($f('_patient_dob_jalali')): ?>
                        <div>
                            <label style="font-size:12px;color:#7A96A4;display:block;margin-bottom:5px;font-weight:600;">تاریخ تولد</label>
                            <input type="text" value="<?php echo esc_attr($f('_patient_dob_jalali')); ?>" disabled dir="ltr"
                                style="width:100%;padding:10px 14px;border:1px solid #EEF2F5;border-radius:8px;font-family:Vazirmatn,Tahoma;font-size:13px;box-sizing:border-box;background:#F8FAFB;color:#A0B4C0;">
                        </div>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top:20px;">
                        <button onclick="DentalPortal.saveProfile()"
                            style="background:#1A6B8A;color:#fff;border:none;border-radius:8px;padding:10px 24px;font-family:Vazirmatn,Tahoma;font-size:13px;font-weight:600;cursor:pointer;">
                            💾 ذخیره تغییرات
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        window.DentalPortal = window.DentalPortal || {};

        DentalPortal.saveProfile = function(){
            var fd = new FormData();
            fd.append("dental_action","update_profile");
            fd.append("nonce","<?php echo esc_js(wp_create_nonce('dental_portal_nonce')); ?>");
            var fname = document.getElementById("portal-fname").value.trim();
            var lname = document.getElementById("portal-lname").value.trim();
            fd.append("name", (fname + " " + lname).trim());
            fd.append("national_id", document.getElementById("portal-national-id").value);
            fd.append("phone",document.getElementById("portal-phone").value);

            fetch(dentalPortal.ajaxUrl,{method:"POST",body:fd})
            .then(r=>r.json()).then(function(d){
                var msg = document.getElementById("portal-profile-msg");
                msg.style.display = "block";
                if(d.success){
                    msg.style.background = "#E8FAF4";
                    msg.style.border = "1px solid #2ECC9A";
                    msg.style.color = "#1a6b3a";
                    msg.textContent = d.data.message || "ذخیره شد.";
                } else {
                    msg.style.background = "#FDEAEA";
                    msg.style.border = "1px solid #E05252";
                    msg.style.color = "#E05252";
                    msg.textContent = d.data || "خطا";
                }
                setTimeout(()=>msg.style.display="none", 4000);
            });
        };
        </script>
        <?php
    }
}
