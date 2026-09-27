<?php
defined('ABSPATH') || exit;

/**
 * Wizard رزرو نوبت در پنل بیمار (۳ مرحله) + صفحه ورودی گرافیکی
 */
class Dental_Booking_Wizard {

    private int $patient_id;
    private bool $show_hero;

    public function __construct(int $patient_id, bool $show_hero = true) {
        $this->patient_id = $patient_id;
        $this->show_hero  = $show_hero;
    }

    public function render(): void {
        global $wpdb;

        $upcoming = Dental_Booking_Appointment::get_patient_appointments($this->patient_id,'upcoming');
        $past     = Dental_Booking_Appointment::get_patient_appointments($this->patient_id,'past');
        $doctors  = get_users(['role__in'=>['dental_doctor'],'fields'=>['ID','display_name']]);
        $services = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dental_services WHERE is_active=1 ORDER BY sort_order",ARRAY_A);

        // ─── نگاشت پزشک→خدمات مجاز — برای فیلتر سمت جاوااسکریپت (بدون
        // AJAX جدید، طبق همون درسی که از باگ‌های قبلی گرفتیم) ──────────
        $doctor_services_map = [];
        $doctor_photos = [];
        foreach ($doctors as $d) {
            $ids = $wpdb->get_col($wpdb->prepare(
                "SELECT service_id FROM {$wpdb->prefix}dental_doctor_services WHERE doctor_id=%d", $d->ID
            ));
            $doctor_services_map[$d->ID] = empty($ids) ? null : array_map('intval', $ids);
            $photo_id = get_user_meta($d->ID, '_dental_doctor_photo', true);
            $doctor_photos[$d->ID] = $photo_id ? wp_get_attachment_image_url($photo_id, 'thumbnail') : '';
        }

        $auto_confirm = (int)get_option('dental_booking_auto_confirm',0);
        $clinic       = get_option('dental_clinic_name', get_bloginfo('name'));

        $status_cfg = [
            'pending'   => ['#F0A500','⏳','در انتظار تأیید'],
            'confirmed' => ['#2ECC9A','✅','تأیید شده'],
            'cancelled' => ['#E05252','❌','لغو شده'],
            'rejected'  => ['#7A96A4','🚫','رد شده'],
            'done'      => ['#1A6B8A','✔️','انجام شده'],
        ];
        ?>

        <!-- ─── استایل کلی صفحه نوبت‌دهی ─────────────────────────── -->
        <style>
        body:has(#dental-booking-app) h1.wp-block-post-title,
        body:has(#dental-booking-app) .entry-title { display:none !important; }

        #dental-booking-app{direction:rtl;font-family:Vazirmatn,Tahoma,sans-serif;max-width:1000px;margin:0 auto;}

        /* ─── هیرو / صفحه ورودی — تمام عرض صفحه، خارج از قالب تم ──── */
        .bk-hero{position:relative;left:50%;right:50%;margin-left:-50vw;margin-right:-50vw;
            width:100vw;overflow:hidden;min-height:100vh;box-sizing:border-box;
            display:flex;align-items:center;justify-content:center;text-align:center;
            background:linear-gradient(135deg,#0F4D66,#1A6B8A 55%,#0B3A4D);margin-top:0;margin-bottom:0;}
        .bk-blob-outer{position:absolute;inset:0;transition:transform .25s ease-out;pointer-events:none;}
        .bk-blob{position:absolute;border-radius:50%;filter:blur(50px);opacity:.55;
            animation:bkFloat 10s ease-in-out infinite;}
        .bk-blob.b1{width:320px;height:320px;background:#2ECC9A;top:-60px;right:-60px;animation-duration:11s;}
        .bk-blob.b2{width:260px;height:260px;background:#8B5CF6;bottom:-80px;left:-40px;animation-duration:13s;animation-delay:1s;}
        .bk-blob.b3{width:200px;height:200px;background:#F0A500;top:40%;left:20%;animation-duration:9s;animation-delay:.5s;}
        .bk-blob.b4{width:180px;height:180px;background:#1A6B8A;bottom:10%;right:15%;animation-duration:14s;animation-delay:2s;}
        @keyframes bkFloat{
            0%,100%{transform:translate(0,0) scale(1);}
            33%{transform:translate(18px,-22px) scale(1.08);}
            66%{transform:translate(-14px,16px) scale(.95);}
        }
        .bk-hero-content{position:relative;z-index:2;padding:48px 24px;color:#fff;}
        .bk-hero-icon{width:76px;height:76px;background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.3);
            border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:34px;margin:0 auto 20px;
            backdrop-filter:blur(4px);}
        .bk-hero-title{font-size:26px;font-weight:800;margin-bottom:10px;}
        .bk-hero-sub{font-size:14px;opacity:.82;margin-bottom:30px;max-width:420px;margin-left:auto;margin-right:auto;line-height:1.9;}
        .bk-hero-btn{background:#fff;color:#0F4D66;border:none;border-radius:12px;padding:15px 40px;
            font-family:Vazirmatn,Tahoma,sans-serif;font-size:16px;font-weight:800;cursor:pointer;
            box-shadow:0 8px 24px rgba(0,0,0,.25);transition:transform .2s,box-shadow .2s;}
        .bk-hero-btn:hover{transform:translateY(-3px);box-shadow:0 12px 30px rgba(0,0,0,.3);}
        .bk-hero-btn:active{transform:translateY(0);}

        /* ─── محتوای اصلی (بعد از ورود) ──────────────────────── */
        #dental-booking-main{display:none;animation:bkFadeIn .5s ease;padding-top:28px;}
        @keyframes bkFadeIn{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}

        .bk-appt-card{background:#fff;border-radius:12px;border:1px solid #EEF2F5;padding:14px 16px;
            display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:10px;}
        .bk-section-title{font-size:14px;font-weight:700;color:#1A2733;margin-bottom:12px;display:flex;align-items:center;gap:8px;}

        .bk-wizard-card{background:#fff;border-radius:16px;border:1px solid #EEF2F5;overflow:hidden;margin-bottom:24px;
            box-shadow:0 2px 16px rgba(15,77,102,.06);}
        .bk-wizard-head{background:linear-gradient(135deg,#1A6B8A,#0F4D66);padding:18px 22px;color:#fff;}
        .bk-wizard-title{font-size:16px;font-weight:700;display:flex;align-items:center;gap:8px;}

        .bk-steps{display:flex;align-items:center;margin-top:18px;}
        .bk-step-item{flex:1;text-align:center;position:relative;}
        .bk-step-circle{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;
            margin:0 auto 6px;font-size:13px;font-weight:700;transition:all .3s;background:rgba(255,255,255,.25);color:rgba(255,255,255,.75);}
        .bk-step-circle.on{background:#fff;color:#1A6B8A;box-shadow:0 0 0 4px rgba(255,255,255,.2);}
        .bk-step-label{font-size:11px;opacity:.85;}
        .bk-step-line{position:absolute;top:15px;left:50%;right:-50%;height:2px;background:rgba(255,255,255,.25);z-index:0;}

        .bk-body{padding:22px;}
        .bk-label{font-size:12px;font-weight:700;color:#5A7080;display:block;margin-bottom:10px;}

        .bk-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(135px,1fr));gap:9px;margin-bottom:20px;}
        .bk-doctor-btn,.bk-service-btn{
            padding:12px 10px;border:2px solid #EEF2F5;border-radius:12px;background:#fff;
            font-family:Vazirmatn,Tahoma,sans-serif;font-size:13px;cursor:pointer;transition:all .18s;
            color:#3D5460;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;
            min-height:64px;text-align:center;line-height:1.4;}
        .bk-doctor-btn:hover,.bk-service-btn:hover{border-color:#B8DAE6;background:#F7FBFD;transform:translateY(-2px);box-shadow:0 4px 12px rgba(26,107,138,.08);}
        .bk-doctor-btn.sel,.bk-service-btn.sel{border-color:#1A6B8A;background:#E8F4F8;color:#1A6B8A;font-weight:700;box-shadow:0 4px 14px rgba(26,107,138,.15);}
        .bk-doctor-btn{padding-top:16px;padding-bottom:14px;}
        .bk-doctor-avatar{width:52px;height:52px;border-radius:50%;object-fit:cover;margin-bottom:2px;border:2px solid #EEF2F5;}
        .bk-doctor-btn.sel .bk-doctor-avatar{border-color:#1A6B8A;}
        .bk-doctor-avatar-fallback{display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#1A6B8A,#0F4D66);color:#fff;font-size:20px;font-weight:700;}
        .bk-doctor-name{font-size:12.5px;font-weight:600;}
        .bk-service-btn{border-top:4px solid var(--svc-color,#1A6B8A);}
        .bk-service-dur{font-size:10px;color:#A0B4C0;font-weight:400;}
        .bk-service-btn.sel .bk-service-dur{color:#1A6B8A;opacity:.75;}

        .bk-textarea{width:100%;padding:12px;border:1.5px solid #C8D4DC;border-radius:10px;
            font-family:Vazirmatn,Tahoma,sans-serif;font-size:13px;resize:none;height:70px;box-sizing:border-box;direction:rtl;}

        .bk-btn{border:none;border-radius:10px;padding:12px 22px;font-family:Vazirmatn,Tahoma,sans-serif;
            font-size:14px;font-weight:700;cursor:pointer;transition:all .2s;}
        .bk-btn-primary{background:#1A6B8A;color:#fff;width:100%;}
        .bk-btn-primary:hover{background:#0F4D66;}
        .bk-btn-ghost{background:#F0F4F6;color:#5A7080;flex:1;}
        .bk-btn-ghost:hover{background:#E4ECF0;}
        .bk-btn-success{background:linear-gradient(135deg,#2ECC9A,#22A87F);color:#fff;flex:2;}

        .bk-date-input{width:100%;padding:12px 14px;border:1.5px solid #C8D4DC;border-radius:10px;
            font-family:Vazirmatn,Tahoma,sans-serif;font-size:14px;font-weight:700;box-sizing:border-box;}
        .bk-slot-btn{padding:9px 15px;border:2px solid #EEF2F5;border-radius:9px;background:#fff;
            font-family:Vazirmatn,Tahoma,sans-serif;font-size:13px;cursor:pointer;direction:ltr;transition:all .15s;}
        .bk-slot-btn.sel{border-color:#2ECC9A;background:#E8FAF4;font-weight:700;color:#1a6b3a;}
        .bk-slot-btn:disabled{color:#C8D4DC;background:#F7F7F7;cursor:not-allowed;}

        @media (max-width:560px){
            .bk-hero{min-height:340px;}
            .bk-hero-title{font-size:21px;}
            .bk-grid{grid-template-columns:repeat(auto-fill,minmax(105px,1fr));}
        }
        </style>

        <div id="dental-booking-app">

            <?php if ($this->show_hero): ?>
            <!-- ═══ صفحه ورودی گرافیکی ═══ -->
            <div class="bk-hero" id="bk-hero">
                <div class="bk-blob-outer" id="bk-blob-outer">
                    <div class="bk-blob b1"></div>
                    <div class="bk-blob b2"></div>
                    <div class="bk-blob b3"></div>
                    <div class="bk-blob b4"></div>
                </div>
                <div class="bk-hero-content">
                    <div class="bk-hero-icon">🦷</div>
                    <div class="bk-hero-title"><?php echo esc_html($clinic); ?></div>
                    <div class="bk-hero-sub">در چند مرحله ساده، پزشک، خدمت و زمان دلخواه خود را انتخاب کنید و نوبتتان را رزرو نمایید.</div>
                    <button type="button" class="bk-hero-btn" id="bk-hero-btn">شروع رزرو نوبت ←</button>
                </div>
            </div>
            <?php endif; ?>

            <!-- ═══ محتوای اصلی ═══ -->
            <div id="dental-booking-main" <?php echo $this->show_hero ? '' : 'style="display:block;"'; ?>>

                <?php if(!empty($upcoming)): ?>
                <div style="margin-bottom:24px;">
                    <h3 class="bk-section-title">
                        <i data-lucide="calendar-check" style="width:16px;height:16px;color:#2ECC9A;"></i>
                        نوبت‌های آینده
                    </h3>
                    <?php foreach($upcoming as $a):
                        [$scolor,$sicon,$slabel] = $status_cfg[$a['status']] ?? ['#999','•',''];
                    ?>
                    <div class="bk-appt-card">
                        <div style="width:48px;height:48px;background:<?php echo $scolor; ?>22;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
                            <?php echo $sicon; ?>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:14px;font-weight:700;color:#1A2733;"><?php echo esc_html($a['service_title']??'نوبت'); ?></div>
                            <div style="font-size:12px;color:#7A96A4;margin-top:3px;display:flex;gap:10px;flex-wrap:wrap;">
                                <span>📅 <?php echo esc_html($a['appt_date_jalali']); ?></span>
                                <span>🕐 <?php echo esc_html(substr($a['start_time'],0,5)); ?></span>
                                <span>👨‍⚕️ <?php echo esc_html($a['doctor_name']); ?></span>
                            </div>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
                            <span style="background:<?php echo $scolor; ?>22;color:<?php echo $scolor; ?>;border-radius:10px;padding:3px 10px;font-size:11px;font-weight:700;"><?php echo esc_html($slabel); ?></span>
                            <?php if(in_array($a['status'],['pending','confirmed'])): ?>
                            <button onclick="DentalBooking.cancelAppointment(<?php echo (int)$a['id']; ?>)"
                                style="background:none;border:1px solid #E05252;color:#E05252;border-radius:6px;padding:4px 10px;font-size:11px;cursor:pointer;font-family:Vazirmatn,Tahoma;">
                                لغو نوبت
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Wizard رزرو جدید -->
                <div class="bk-wizard-card">
                    <div class="bk-wizard-head">
                        <div class="bk-wizard-title">
                            <i data-lucide="calendar-plus" style="width:18px;height:18px;"></i>
                            رزرو نوبت جدید
                        </div>
                        <div class="bk-steps">
                            <?php foreach([1=>'انتخاب خدمت',2=>'انتخاب تاریخ',3=>'تأیید'] as $step=>$label): ?>
                            <div class="bk-step-item">
                                <div class="bk-step-circle <?php echo $step===1?'on':''; ?>" id="step-circle-<?php echo $step; ?>"><?php echo $step; ?></div>
                                <div class="bk-step-label"><?php echo esc_html($label); ?></div>
                                <?php if($step<3): ?><div class="bk-step-line" id="step-line-<?php echo $step; ?>"></div><?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bk-body">

                        <!-- مرحله ۱ -->
                        <div id="bk-step-1">
                            <label class="bk-label">انتخاب پزشک</label>
                            <div class="bk-grid" id="bk-doctor-list">
                                <?php foreach($doctors as $d):
                                    $dphoto = $doctor_photos[$d->ID] ?? '';
                                    $dinitial = mb_substr($d->display_name, 0, 1);
                                ?>
                                <button type="button" class="bk-doctor-btn" data-id="<?php echo $d->ID; ?>"
                                    onclick="DentalBooking.selectDoctor(<?php echo $d->ID; ?>,this)">
                                    <?php if ($dphoto): ?>
                                    <img src="<?php echo esc_url($dphoto); ?>" class="bk-doctor-avatar">
                                    <?php else: ?>
                                    <span class="bk-doctor-avatar bk-doctor-avatar-fallback"><?php echo esc_html($dinitial); ?></span>
                                    <?php endif; ?>
                                    <span class="bk-doctor-name"><?php echo esc_html($d->display_name); ?></span>
                                </button>
                                <?php endforeach; ?>
                            </div>

                            <label class="bk-label">نوع خدمت</label>
                            <div class="bk-grid" id="bk-service-list">
                                <?php foreach($services as $s): ?>
                                <button type="button" class="bk-service-btn" data-svc-id="<?php echo (int)$s['id']; ?>" style="--svc-color:<?php echo esc_attr($s['color']); ?>"
                                    data-id="<?php echo $s['id']; ?>" data-duration="<?php echo $s['duration']; ?>"
                                    onclick="DentalBooking.selectService(<?php echo $s['id']; ?>,<?php echo $s['duration']; ?>,this)">
                                    <?php echo esc_html($s['title']); ?>
                                    <span class="bk-service-dur"><?php echo $s['duration']; ?> دقیقه</span>
                                </button>
                                <?php endforeach; ?>
                            </div>

                            <label class="bk-label">یادداشت (اختیاری)</label>
                            <textarea id="bk-notes" class="bk-textarea" placeholder="توضیحات اضافه..." style="margin-bottom:18px;"></textarea>

                            <button type="button" class="bk-btn bk-btn-primary" onclick="DentalBooking.goStep(2)">بعدی: انتخاب تاریخ ←</button>
                        </div>

                        <!-- مرحله ۲ -->
                        <div id="bk-step-2" style="display:none;">
                            <label class="bk-label">تاریخ مراجعه (شمسی)</label>
                            <input type="text" id="bk-date-input" class="dc-datepicker bk-date-input" placeholder="تاریخ را انتخاب کنید" dir="ltr">
                            <button type="button" class="bk-btn" onclick="DentalBooking.loadSlots()"
                                style="width:100%;margin:8px 0 16px;background:#F0F6F9;color:#1A6B8A;border:1px solid #C8D4DC;">
                                🔍 مشاهده ساعت‌های خالی
                            </button>
                            <div id="bk-slots-wrap" style="display:none;">
                                <label class="bk-label">انتخاب ساعت</label>
                                <div id="bk-slots" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px;"></div>
                            </div>
                            <div style="display:flex;gap:8px;">
                                <button type="button" class="bk-btn bk-btn-ghost" onclick="DentalBooking.goStep(1)">→ بازگشت</button>
                                <button type="button" class="bk-btn bk-btn-primary" style="flex:2;" onclick="DentalBooking.goStep(3)">بعدی: تأیید ←</button>
                            </div>
                        </div>

                        <!-- مرحله ۳ -->
                        <div id="bk-step-3" style="display:none;">
                            <div style="background:#F8FAFB;border-radius:12px;padding:16px;margin-bottom:16px;" id="bk-summary"></div>
                            <?php if(!$auto_confirm): ?>
                            <div style="background:#FEF6E4;border:1px solid #F0A500;border-radius:10px;padding:11px 14px;font-size:12px;color:#7a5200;margin-bottom:16px;">
                                ⏳ نوبت شما پس از بررسی و تأیید توسط کلینیک نهایی خواهد شد. SMS تأیید برای شما ارسال می‌شود.
                            </div>
                            <?php endif; ?>
                            <div style="display:flex;gap:8px;">
                                <button type="button" class="bk-btn bk-btn-ghost" onclick="DentalBooking.goStep(2)">→ بازگشت</button>
                                <button type="button" class="bk-btn bk-btn-success" onclick="DentalBooking.confirmBooking()">✅ ثبت نهایی نوبت</button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if(!empty($past)): ?>
                <div>
                    <h3 style="font-size:13px;font-weight:700;color:#7A96A4;margin-bottom:10px;">نوبت‌های قبلی</h3>
                    <?php foreach(array_slice($past,0,3) as $a):
                        [$scolor,$sicon,$slabel] = $status_cfg[$a['status']] ?? ['#999','•',''];
                    ?>
                    <div style="background:#F8FAFB;border-radius:10px;padding:11px 14px;margin-bottom:8px;display:flex;align-items:center;gap:10px;font-size:12px;color:#7A96A4;">
                        <span><?php echo $sicon; ?></span>
                        <span><?php echo esc_html($a['appt_date_jalali']); ?></span>
                        <span><?php echo esc_html(substr($a['start_time'],0,5)); ?></span>
                        <span><?php echo esc_html($a['service_title']??''); ?></span>
                        <span style="margin-right:auto;color:<?php echo $scolor; ?>;font-weight:700;"><?php echo esc_html($slabel); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <script>
        (function(){
            var hero = document.getElementById('bk-hero');
            var main = document.getElementById('dental-booking-main');
            var btn  = document.getElementById('bk-hero-btn');
            if(btn) btn.addEventListener('click', function(){
                hero.style.display = 'none';
                main.style.display = 'block';
                main.scrollIntoView({behavior:'smooth', block:'start'});
            });

            var outer = document.getElementById('bk-blob-outer');
            if(hero && outer){
                hero.addEventListener('mousemove', function(e){
                    var r = hero.getBoundingClientRect();
                    var mx = ((e.clientX - r.left) / r.width  - .5) * 30;
                    var my = ((e.clientY - r.top)  / r.height - .5) * 30;
                    outer.style.transform = 'translate(' + mx + 'px,' + my + 'px)';
                });
                hero.addEventListener('mouseleave', function(){
                    outer.style.transform = 'translate(0,0)';
                });
            }
        })();

        window.DentalBooking = (function(){
            var state = { doctor_id:0, doctor_name:'', service_id:0, service_name:'', service_duration:0, date:'', time:'', shift_id:0, notes:'' };
            // ─── نگاشت پزشک→خدمات مجاز — null یعنی این پزشک محدودیتی
            // نداره (همه‌ی خدمات رو نشون بده) ─────────────────────────
            var doctorServicesMap = <?php echo wp_json_encode($doctor_services_map); ?>;

            function filterServicesForDoctor(docId){
                var allowed = doctorServicesMap[docId];
                document.querySelectorAll('.bk-service-btn').forEach(function(btn){
                    var svcId = parseInt(btn.getAttribute('data-svc-id'), 10);
                    var ok = !allowed || allowed.indexOf(svcId) !== -1;
                    btn.style.display = ok ? '' : 'none';
                    // اگه خدمتِ قبلاً انتخاب‌شده دیگه مجاز نیست، انتخابش پاک بشه
                    if (!ok && btn.classList.contains('sel')) {
                        btn.classList.remove('sel');
                        state.service_id = 0; state.service_name=''; state.service_duration=0;
                    }
                });
            }

            function goStep(n){
                if(n===2 && (!state.doctor_id||!state.service_id)){
                    Swal.fire({icon:'warning',title:'لطفاً پزشک و خدمت را انتخاب کنید',confirmButtonColor:'#1A6B8A'}); return;
                }
                if(n===3 && (!state.date||!state.time)){
                    Swal.fire({icon:'warning',title:'لطفاً تاریخ و ساعت را انتخاب کنید',confirmButtonColor:'#1A6B8A'}); return;
                }
                [1,2,3].forEach(function(s){
                    var el = document.getElementById('bk-step-'+s);
                    if(el) el.style.display = s===n?'block':'none';
                    var circle = document.getElementById('step-circle-'+s);
                    if(circle) circle.classList.toggle('on', s<=n);
                });
                if(n===3) buildSummary();
            }

            function selectDoctor(id, el){
                state.doctor_id   = id;
                var nameEl = el.querySelector('.bk-doctor-name');
                state.doctor_name = nameEl ? nameEl.textContent.trim() : el.textContent.trim();
                document.querySelectorAll('.bk-doctor-btn').forEach(function(b){ b.classList.remove('sel'); });
                el.classList.add('sel');
                filterServicesForDoctor(id);
            }

            function selectService(id, duration, el){
                state.service_id       = id;
                state.service_name     = el.childNodes[0].textContent.trim();
                state.service_duration = duration;
                document.querySelectorAll('.bk-service-btn').forEach(function(b){ b.classList.remove('sel'); });
                el.classList.add('sel');
            }

            function loadSlots(){
                var date = document.getElementById('bk-date-input').value.trim();
                if(!date||!state.doctor_id){
                    Swal.fire({icon:'warning',title:'ابتدا پزشک و تاریخ را انتخاب کنید',confirmButtonColor:'#1A6B8A'}); return;
                }
                state.date = date;
                var wrap  = document.getElementById('bk-slots-wrap');
                var slots = document.getElementById('bk-slots');
                if(wrap) wrap.style.display = 'block';
                if(slots) slots.innerHTML = '<div style="color:#A0B4C0;font-size:13px;">در حال بارگذاری...</div>';

                fetch(dentalPortal.apiBase.replace('dental/v1','dental-booking/v1')+'/slots?doctor_id='+state.doctor_id+'&date='+encodeURIComponent(date),{
                    headers:{'X-WP-Nonce':dentalPortal.nonce}
                }).then(r=>r.json()).then(function(d){
                    if(!d.success||!d.data.slots.length){
                        if(slots) slots.innerHTML='<div style="color:#E05252;font-size:13px;">برای این تاریخ و خدمت، اسلاتی موجود نیست — یا این روز کاری این پزشک نیست، یا این خدمت رو این روز انجام نمی‌ده. یه تاریخ دیگه امتحان کنید.</div>';
                        return;
                    }
                    if(slots) slots.innerHTML = d.data.slots.map(function(s){
                        return '<button type="button" class="bk-slot-btn" '+(s.available?'':'disabled')+
                            ' onclick="DentalBooking.selectSlot(\''+s.time+'\',\''+s.time_full+'\','+s.shift_id+',this)">'+
                            s.time+'</button>';
                    }).join('');
                });
            }

            function selectSlot(time, timeFull, shiftId, el){
                state.time     = time;
                state.shift_id = shiftId;
                document.querySelectorAll('.bk-slot-btn').forEach(function(b){ b.classList.remove('sel'); });
                el.classList.add('sel');
            }

            function buildSummary(){
                state.notes = document.getElementById('bk-notes').value;
                var s = document.getElementById('bk-summary');
                if(!s) return;
                s.innerHTML = [
                    ['پزشک',  state.doctor_name],
                    ['خدمت',  state.service_name],
                    ['تاریخ', state.date],
                    ['ساعت',  state.time],
                    state.notes ? ['یادداشت', state.notes] : null,
                ].filter(Boolean).map(function(r){
                    return '<div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px dashed #EEF2F5;font-size:13px;">'+
                        '<span style="color:#7A96A4;">'+r[0]+'</span>'+
                        '<span style="font-weight:700;color:#1A2733;">'+r[1]+'</span></div>';
                }).join('');
            }

            function confirmBooking(){
                Swal.fire({
                    title:'ثبت نوبت', text:'نوبت شما ثبت و SMS اطلاع‌رسانی ارسال می‌شود.', icon:'question',
                    showCancelButton:true, confirmButtonText:'بله، ثبت شود', cancelButtonText:'انصراف',
                    confirmButtonColor:'#2ECC9A'
                }).then(function(r){
                    if(!r.isConfirmed) return;
                    fetch(dentalPortal.apiBase.replace('dental/v1','dental-booking/v1')+'/book',{
                        method:'POST',
                        headers:{'Content-Type':'application/json','X-WP-Nonce':dentalPortal.nonce},
                        body:JSON.stringify({
                            doctor_id:state.doctor_id, date:state.date, time:state.time,
                            shift_id:state.shift_id, service_type:state.service_id, notes:state.notes
                        })
                    }).then(r=>r.json()).then(function(d){
                        if(d.success){
                            Swal.fire({icon:'success',title:'نوبت ثبت شد',text:d.message,confirmButtonColor:'#1A6B8A'}).then(()=>location.reload());
                        } else {
                            Swal.fire({icon:'error',title:'خطا',text:d.message||'مشکلی پیش آمد'});
                        }
                    });
                });
            }

            function cancelAppointment(id){
                Swal.fire({
                    title:'لغو نوبت',icon:'warning', input:'text', inputPlaceholder:'دلیل لغو (اختیاری)',
                    showCancelButton:true, confirmButtonText:'لغو نوبت', cancelButtonText:'انصراف',
                    confirmButtonColor:'#E05252'
                }).then(function(r){
                    if(!r.isConfirmed) return;
                    fetch(dentalPortal.apiBase.replace('dental/v1','dental-booking/v1')+'/cancel/'+id,{
                        method:'POST',
                        headers:{'Content-Type':'application/json','X-WP-Nonce':dentalPortal.nonce},
                        body:JSON.stringify({reason:r.value||''})
                    }).then(r=>r.json()).then(function(d){
                        if(d.success) Swal.fire({icon:'success',title:'نوبت لغو شد',timer:2000,showConfirmButton:false}).then(()=>location.reload());
                        else Swal.fire({icon:'error',title:d.message||'خطا'});
                    });
                });
            }

            return { goStep,selectDoctor,selectService,loadSlots,selectSlot,confirmBooking,cancelAppointment };
        })();
        if(typeof lucide!=='undefined') lucide.createIcons();
        </script>
        <?php
    }
}
