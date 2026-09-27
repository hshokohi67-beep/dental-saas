<?php
defined('ABSPATH') || exit;

class Dental_Setup_Wizard {

    public function render(): void {
        if (isset($_POST['dental_setup_mode']) && check_admin_referer('dental_setup_wizard')) {
            $mode = sanitize_key($_POST['dental_setup_mode']);
            update_option('dental_features', Dental_Features::preset_for($mode));
            update_option('dental_setup_done', 1);
            wp_safe_redirect(admin_url('admin.php?page=dental-dashboard&setup=1'));
            exit;
        }
        if (isset($_GET['dental_skip_wizard']) && check_admin_referer('dental_skip_wizard')) {
            update_option('dental_features', Dental_Features::defaults());
            update_option('dental_setup_done', 1);
            wp_safe_redirect(admin_url('admin.php?page=dental-dashboard'));
            exit;
        }
        ?>
        <div style="max-width:760px;margin:50px auto;font-family:Vazirmatn,Tahoma,sans-serif;direction:rtl;padding:0 20px;">
            <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700&display=swap" rel="stylesheet">
            <div style="text-align:center;margin-bottom:32px;">
                <div style="font-size:52px;">🦷</div>
                <h1 style="font-size:22px;color:#1A2733;margin:10px 0 6px;">به پلاگین کلینیک دندانپزشکی خوش آمدید</h1>
                <p style="color:#7A96A4;font-size:14px;line-height:1.9;">
                    برای شروع، نوع محل کار خود را انتخاب کنید تا امکانات مناسب همان فعال شود.<br>
                    نگران نباشید — این تنظیم بعداً هم از صفحه تنظیمات قابل تغییر است.
                </p>
            </div>
            <form method="post">
                <?php wp_nonce_field('dental_setup_wizard'); ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
                    <button type="submit" name="dental_setup_mode" value="solo"
                        style="cursor:pointer;background:#fff;border:2px solid #EEF2F5;border-radius:16px;padding:28px 20px;text-align:center;font-family:inherit;transition:all .2s;"
                        onmouseover="this.style.borderColor='#C8D4DC'" onmouseout="this.style.borderColor='#EEF2F5'">
                        <div style="font-size:38px;margin-bottom:10px;">🧑‍⚕️</div>
                        <div style="font-size:16px;font-weight:700;color:#1A2733;margin-bottom:8px;">مطب شخصی</div>
                        <div style="font-size:12px;color:#7A96A4;line-height:1.9;">
                            یک پزشک، بدون پرسنل پذیرش جداگانه.<br>امکانات ساده و سریع، بدون شلوغی اضافه.
                        </div>
                    </button>
                    <button type="submit" name="dental_setup_mode" value="clinic"
                        style="cursor:pointer;background:#F0F6F9;border:2px solid #1A6B8A;border-radius:16px;padding:28px 20px;text-align:center;font-family:inherit;">
                        <div style="font-size:38px;margin-bottom:10px;">🏥</div>
                        <div style="font-size:16px;font-weight:700;color:#1A6B8A;margin-bottom:8px;">کلینیک چندپزشکی</div>
                        <div style="font-size:12px;color:#5A7080;line-height:1.9;">
                            چند پزشک و پرسنل پذیرش.<br>صف انتظار، گزارش‌های کامل و همه امکانات.
                        </div>
                    </button>
                </div>
            </form>
            <div style="text-align:center;margin-top:22px;">
                <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('dental_skip_wizard','1'),'dental_skip_wizard')); ?>"
                   style="font-size:12px;color:#A0B4C0;text-decoration:none;">
                    فعلاً رد شو، بعداً از تنظیمات مشخص می‌کنم ←
                </a>
            </div>
        </div>
        <?php
    }
}
