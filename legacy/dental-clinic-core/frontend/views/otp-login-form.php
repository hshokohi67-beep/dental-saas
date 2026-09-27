<?php
defined('ABSPATH') || exit;

$dv  = defined('DENTAL_DEV_MODE') && DENTAL_DEV_MODE;
$dc  = defined('DENTAL_DEV_OTP_CODE') ? DENTAL_DEV_OTP_CODE : '123456';
$ex  = defined('DENTAL_OTP_EXPIRY_MINUTES') ? (int)DENTAL_OTP_EXPIRY_MINUTES * 60 : 300;
$cc  = defined('DENTAL_OTP_RESEND_COOLDOWN') ? (int)DENTAL_OTP_RESEND_COOLDOWN : 90;
$uid = 'dc' . rand(1000,9999);
$clinic = get_option('dental_clinic_name', get_bloginfo('name'));
$portal_url  = dental_get_portal_url() ?: home_url('/');

// اولویت با redirect_to در URL (برای برگشت به همون صفحه‌ای که کاربر ازش اومده)
// این مقدار را جدا از fallback عمومی نگه می‌داریم، چون fallback (خانه سایت)
// نباید در حالت «قبلاً لاگین هستید» به‌جای پنل کاربری استفاده شود.
$explicit_redirect = '';
if (!empty($_GET['redirect_to'])) {
    $explicit_redirect = wp_validate_redirect(urldecode($_GET['redirect_to']), '');
}
if (!$explicit_redirect && !empty($atts['redirect'])) {
    $explicit_redirect = $atts['redirect'];
}
$redirect = $explicit_redirect ?: $portal_url;

// ─── اگر کاربر از قبل لاگین است، دوباره فرم شماره موبایل نشان داده نشود ──
if (is_user_logged_in()) {
    // اگر آدرس برگشت مشخصی خواسته نشده، حتماً به پنل کاربری برو، نه خانه سایت
    $go_url = $explicit_redirect ?: $portal_url;
    ?>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">
    <div style="max-width:420px;margin:40px auto;font-family:Vazirmatn,Tahoma,sans-serif;direction:rtl;text-align:center;
        background:#fff;border-radius:20px;box-shadow:0 8px 40px rgba(26,107,138,.15);padding:40px 24px;">
        <div style="width:64px;height:64px;background:#E8FAF4;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;">✅</div>
        <div style="font-size:17px;font-weight:700;color:#1A2733;margin-bottom:8px;">شما وارد شده‌اید</div>
        <div style="font-size:13px;color:#7A96A4;margin-bottom:24px;">
            با حساب <strong><?php echo esc_html(wp_get_current_user()->display_name); ?></strong> در سیستم هستید.
        </div>
        <a href="<?php echo esc_url($go_url); ?>" style="display:inline-block;background:linear-gradient(135deg,#1A6B8A,#0F4D66);color:#fff;padding:12px 28px;border-radius:10px;text-decoration:none;font-size:14px;font-weight:700;margin-bottom:12px;">
            ورود به پنل کاربری ←
        </a>
        <div>
            <a href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>" style="font-size:12px;color:#E05252;text-decoration:none;">
                خروج از این حساب و ورود با شماره دیگر
            </a>
        </div>
    </div>
    <?php
    return;
}
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">

<style>
#<?php echo $uid; ?>-wrap * { box-sizing:border-box; }
#<?php echo $uid; ?>-wrap {
    max-width:420px;
    margin:40px auto;
    font-family:Vazirmatn,Tahoma,sans-serif;
    direction:rtl;
}
.<?php echo $uid; ?>-card {
    background:#fff;
    border-radius:20px;
    box-shadow:0 8px 40px rgba(26,107,138,.15);
    overflow:hidden;
}
.<?php echo $uid; ?>-header {
    background:linear-gradient(135deg,#1A6B8A 0%,#0F4D66 100%);
    padding:32px 24px 28px;
    text-align:center;
    color:#fff;
    position:relative;
}
.<?php echo $uid; ?>-header::after {
    content:'';
    position:absolute;
    bottom:-1px;
    left:0;right:0;
    height:20px;
    background:#fff;
    border-radius:20px 20px 0 0;
}
.<?php echo $uid; ?>-logo {
    width:64px;height:64px;
    background:rgba(255,255,255,.15);
    border:2px solid rgba(255,255,255,.3);
    border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    margin:0 auto 14px;
    font-size:28px;
}
.<?php echo $uid; ?>-title {
    font-size:19px;font-weight:700;margin-bottom:6px;
}
.<?php echo $uid; ?>-sub {
    font-size:13px;opacity:.75;font-weight:300;
}
.<?php echo $uid; ?>-body { padding:28px 24px 24px; }
.<?php echo $uid; ?>-label {
    display:block;font-size:13px;font-weight:600;
    color:#3D5460;margin-bottom:7px;
}
.<?php echo $uid; ?>-input {
    width:100%;height:48px;
    border:1.5px solid #C8D4DC;
    border-radius:10px;
    padding:0 16px;
    font-size:16px;font-family:Vazirmatn,Tahoma,sans-serif;
    direction:ltr;text-align:center;
    outline:none;
    transition:border-color .2s,box-shadow .2s;
    color:#1A2733;
}
.<?php echo $uid; ?>-input:focus {
    border-color:#1A6B8A;
    box-shadow:0 0 0 3px rgba(26,107,138,.12);
}
.<?php echo $uid; ?>-btn {
    width:100%;height:48px;
    border:none;border-radius:10px;
    font-size:15px;font-weight:700;
    font-family:Vazirmatn,Tahoma,sans-serif;
    cursor:pointer;
    transition:all .2s;
    margin-top:6px;
}
.<?php echo $uid; ?>-btn-primary {
    background:linear-gradient(135deg,#1A6B8A,#0F4D66);
    color:#fff;
}
.<?php echo $uid; ?>-btn-primary:hover { transform:translateY(-1px);box-shadow:0 4px 16px rgba(26,107,138,.3); }
.<?php echo $uid; ?>-btn-primary:active { transform:translateY(0); }
.<?php echo $uid; ?>-btn-success {
    background:linear-gradient(135deg,#2ECC9A,#22A87F);
    color:#fff;
}
.<?php echo $uid; ?>-btn-success:hover { transform:translateY(-1px);box-shadow:0 4px 16px rgba(46,204,154,.3); }
.<?php echo $uid; ?>-error {
    color:#E05252;font-size:12px;
    min-height:18px;margin-top:5px;
    display:flex;align-items:center;gap:4px;
}
.<?php echo $uid; ?>-otp-box {
    width:46px;height:56px;
    border:2px solid #C8D4DC;
    border-radius:10px;
    text-align:center;
    font-size:22px;font-weight:700;
    font-family:Vazirmatn,Tahoma,sans-serif;
    outline:none;
    transition:all .15s;
    color:#1A2733;
}
.<?php echo $uid; ?>-otp-box:focus {
    border-color:#1A6B8A;
    box-shadow:0 0 0 3px rgba(26,107,138,.12);
    transform:scale(1.05);
}
.<?php echo $uid; ?>-otp-box.filled {
    border-color:#2ECC9A;
    background:#E8FAF4;
}
.<?php echo $uid; ?>-divider {
    display:flex;align-items:center;gap:10px;
    color:#A0B4C0;font-size:12px;
    margin:16px 0;
}
.<?php echo $uid; ?>-divider::before,
.<?php echo $uid; ?>-divider::after {
    content:'';flex:1;height:1px;background:#EEF2F5;
}
@keyframes <?php echo $uid; ?>spin { to { transform:rotate(360deg); } }
@keyframes <?php echo $uid; ?>fadeIn { from { opacity:0;transform:translateY(8px); } to { opacity:1;transform:translateY(0); } }
.<?php echo $uid; ?>-fade { animation:<?php echo $uid; ?>fadeIn .3s ease; }
</style>

<div id="<?php echo $uid; ?>-wrap">

    <?php if($dv): ?>
    <div style="background:#FEF6E4;border:1px solid #F0A500;border-radius:10px;padding:10px 14px;font-size:13px;margin-bottom:14px;display:flex;align-items:center;gap:8px;color:#7a5200;">
        🧪 <span>حالت توسعه — کد OTP ثابت: <strong><?php echo esc_html($dc); ?></strong></span>
    </div>
    <?php endif; ?>

    <div class="<?php echo $uid; ?>-card">

        <!-- هدر -->
        <div class="<?php echo $uid; ?>-header">
            <div class="<?php echo $uid; ?>-logo">🦷</div>
            <div class="<?php echo $uid; ?>-title"><?php echo esc_html($clinic); ?></div>
            <div class="<?php echo $uid; ?>-sub">ورود به پنل کاربری</div>
        </div>

        <div class="<?php echo $uid; ?>-body">

            <!-- مرحله ۱: ورود شماره موبایل -->
            <div id="<?php echo $uid; ?>-s1" class="<?php echo $uid; ?>-fade">
                <label class="<?php echo $uid; ?>-label">شماره موبایل</label>
                <input id="<?php echo $uid; ?>-mob"
                    type="tel" maxlength="11" inputmode="numeric"
                    placeholder="09xxxxxxxxx"
                    class="<?php echo $uid; ?>-input">
                <div id="<?php echo $uid; ?>-e1" class="<?php echo $uid; ?>-error"></div>
                <button type="button" id="<?php echo $uid; ?>-btn0" class="<?php echo $uid; ?>-btn <?php echo $uid; ?>-btn-primary">
                    دریافت کد تأیید
                </button>
                <div class="<?php echo $uid; ?>-divider">یا</div>
                <div style="text-align:center;font-size:13px;color:#7A96A4;">
                    با ورود، <a href="<?php echo esc_url(home_url('/privacy-policy')); ?>" style="color:#1A6B8A;">قوانین استفاده</a> را می‌پذیرید.
                </div>
            </div>

            <!-- مرحله ۲: تأیید کد OTP -->
            <div id="<?php echo $uid; ?>-s2" style="display:none;" class="<?php echo $uid; ?>-fade">
                <div style="text-align:center;margin-bottom:20px;">
                    <div style="font-size:13px;color:#5A7080;margin-bottom:4px;">کد ۶ رقمی به شماره</div>
                    <div id="<?php echo $uid; ?>-num" style="font-size:16px;font-weight:700;color:#1A6B8A;direction:ltr;"></div>
                    <div style="font-size:12px;color:#A0B4C0;margin-top:2px;">ارسال شد</div>
                </div>

                <!-- باکس‌های OTP -->
                <div style="display:flex;gap:8px;justify-content:center;direction:ltr;margin-bottom:16px;">
                    <?php for($i=0;$i<6;$i++): ?>
                    <input class="<?php echo $uid; ?>-otp-box" data-i="<?php echo $i; ?>"
                        type="text" maxlength="1" inputmode="numeric">
                    <?php endfor; ?>
                </div>

                <!-- تایمر -->
                <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin-bottom:16px;font-size:13px;color:#5A7080;">
                    <span>اعتبار کد:</span>
                    <span id="<?php echo $uid; ?>-tmr" style="font-weight:700;color:#1A6B8A;min-width:40px;text-align:center;direction:ltr;"></span>
                </div>

                <button type="button" id="<?php echo $uid; ?>-btn1" class="<?php echo $uid; ?>-btn <?php echo $uid; ?>-btn-success">
                    تأیید و ورود
                </button>
                <div id="<?php echo $uid; ?>-e2" class="<?php echo $uid; ?>-error" style="justify-content:center;margin-top:10px;"></div>

                <div style="display:flex;align-items:center;justify-content:center;gap:10px;margin-top:14px;font-size:12px;color:#A0B4C0;">
                    <span id="<?php echo $uid; ?>-rw">
                        ارسال مجدد پس از <span id="<?php echo $uid; ?>-rn" style="font-weight:700;"><?php echo (int)$cc; ?></span> ثانیه
                    </span>
                    <button type="button" id="<?php echo $uid; ?>-rb"
                        style="display:none;background:none;border:none;color:#1A6B8A;font-size:13px;cursor:pointer;font-family:Vazirmatn,Tahoma;font-weight:600;">
                        ارسال مجدد کد
                    </button>
                    <span style="color:#EEF2F5;">|</span>
                    <button type="button" id="<?php echo $uid; ?>-back"
                        style="background:none;border:none;color:#7A96A4;font-size:12px;cursor:pointer;font-family:Vazirmatn,Tahoma;">
                        تغییر شماره
                    </button>
                </div>
            </div>

            <!-- مرحله ۳: لودینگ -->
            <div id="<?php echo $uid; ?>-s3" style="display:none;text-align:center;padding:30px 0;">
                <div style="width:44px;height:44px;border:4px solid #EEF2F5;border-top-color:#1A6B8A;border-radius:50%;margin:0 auto 16px;animation:<?php echo $uid; ?>spin .8s linear infinite;"></div>
                <div style="font-size:14px;color:#5A7080;">در حال ورود...</div>
            </div>

            <!-- مرحله ۴: موفقیت -->
            <div id="<?php echo $uid; ?>-s4" style="display:none;text-align:center;padding:30px 0;" class="<?php echo $uid; ?>-fade">
                <div style="width:64px;height:64px;background:#E8FAF4;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;">✅</div>
                <div id="<?php echo $uid; ?>-ok" style="font-size:16px;font-weight:700;color:#1A2733;margin-bottom:6px;">خوش آمدید!</div>
                <div style="font-size:13px;color:#7A96A4;">در حال انتقال به پنل شما...</div>
            </div>

        </div>
    </div>
</div>

<script>
window._dcId  = <?php echo wp_json_encode($uid); ?>;
window._dcApi = <?php echo wp_json_encode(rest_url('dental/v1')); ?>;
window._dcNc  = <?php echo wp_json_encode(wp_create_nonce('wp_rest')); ?>;
window._dcRd  = <?php echo wp_json_encode($redirect); ?>;
window._dcEx  = <?php echo (int)$ex; ?>;
window._dcCc  = <?php echo (int)$cc; ?>;
window._dcDv  = <?php echo $dv ? 'true' : 'false'; ?>;
window._dcOt  = <?php echo wp_json_encode($dc); ?>;
</script>
<script src="<?php echo esc_url(DENTAL_CORE_URL . 'assets/js/otp-form.js'); ?>?v=<?php echo DENTAL_CORE_VERSION; ?>"></script>
