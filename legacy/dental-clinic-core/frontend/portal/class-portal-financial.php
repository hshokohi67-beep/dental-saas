<?php
defined('ABSPATH') || exit;

class Dental_Portal_Financial {

    private int $patient_id;

    public function __construct(int $patient_id) {
        $this->patient_id = $patient_id;
    }

    public function render(): void {
        $installments = Dental_Installment_Manager::get_patient_installments($this->patient_id);
        $wallet       = Dental_Patient_Wallet::get_balance($this->patient_id);
        // ─── چندکارتی — یکی به‌صورت تصادفی انتخاب می‌شه، بیمار هیچ‌وقت
        // لیست کامل کارت‌ها رو نمی‌بینه (طبق درخواست صریح) ──────────────
        $cards = get_option('dental_cards', []);
        $card_number = $card_name = $card_bank = '';
        if (!empty($cards)) {
            $picked = $cards[array_rand($cards)];
            $card_number = $picked['number'];
            $card_name   = $picked['name'];
            $card_bank   = $picked['bank'];
        }
        $online_en    = (int)get_option('dental_online_payment_enabled',0);

        // پیام ثبت فیش
        $receipt_saved = isset($_GET['receipt_saved']);
        if($receipt_saved):
        ?>
        <div style="background:#E8FAF4;border:1px solid #2ECC9A;border-radius:8px;padding:10px 16px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:8px;">
            <i data-lucide="check-circle-2" style="width:16px;height:16px;color:#2ECC9A;"></i>
            فیش شما ثبت شد و در انتظار تأیید مدیریت مالی است.
        </div>
        <?php endif;

        if(empty($installments)):
        ?>
        <div style="text-align:center;padding:48px;background:#fff;border-radius:12px;border:1px solid #EEF2F5;">
            <div style="font-size:48px;margin-bottom:12px;">💰</div>
            <p style="color:#7A96A4;font-size:14px;">قسطی ثبت نشده</p>
        </div>
        <?php return; endif;

        foreach($installments as $plan):
            $items     = Dental_Installment_Manager::get_items((int)$plan['id']);
            $total_paid_items = array_sum(array_column($items,'paid_amount'));
            $progress  = $plan['total_amount']>0 ? min(100,round(((float)$plan['down_payment']+$total_paid_items)/(float)$plan['total_amount']*100)) : 0;
            $status_cfg = [
                'active'    => ['#1A6B8A','فعال'],
                'completed' => ['#2ECC9A','تسویه شده'],
                'overdue'   => ['#E05252','معوقه'],
            ];
            [$scolor,$slabel] = $status_cfg[$plan['status']] ?? ['#999',$plan['status']];
        ?>
        <div style="background:#fff;border-radius:12px;border:1px solid #EEF2F5;margin-bottom:20px;overflow:hidden;">
            <!-- هدر پلان -->
            <div style="padding:16px 20px;border-bottom:1px solid #EEF2F5;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                            <span style="font-size:14px;font-weight:700;color:#1A2733;">پلان قسطی #<?php echo (int)$plan['id']; ?></span>
                            <span style="background:<?php echo $scolor; ?>22;color:<?php echo $scolor; ?>;border-radius:10px;padding:2px 10px;font-size:11px;font-weight:700;"><?php echo esc_html($slabel); ?></span>
                        </div>
                        <div style="display:flex;gap:14px;font-size:12px;color:#5A7080;flex-wrap:wrap;">
                            <span>کل: <strong><?php echo number_format($plan['total_amount']); ?> ت</strong></span>
                            <span>پیش‌پرداخت: <strong><?php echo number_format($plan['down_payment']); ?> ت</strong></span>
                            <span>پرداخت شده: <strong style="color:#2ECC9A;"><?php echo number_format($total_paid_items); ?> ت</strong></span>
                        </div>
                    </div>
                    <div style="font-size:24px;font-weight:700;color:<?php echo $scolor; ?>;"><?php echo $progress; ?>%</div>
                </div>
                <div style="margin-top:10px;height:6px;background:#EEF2F5;border-radius:3px;overflow:hidden;">
                    <div style="height:100%;width:<?php echo $progress; ?>%;background:<?php echo $plan['status']==='overdue'?'#E05252':'#2ECC9A'; ?>;border-radius:3px;transition:width .5s;"></div>
                </div>
            </div>

            <!-- اقلام قسط -->
            <div style="padding:0 20px;">
                <?php foreach($items as $item):
                    $s_map = [
                        'paid'    => ['#2ECC9A','check-circle-2','پرداخت شده'],
                        'overdue' => ['#E05252','alert-circle',  'معوقه'],
                        'pending' => ['#7A96A4','clock',         'در انتظار'],
                        'partial' => ['#F0A500','circle',        'جزئی'],
                    ];
                    [$icolor,$iicon,$ilabel] = $s_map[$item['status']] ?? ['#999','circle',''];
                    $is_payable = in_array($item['status'],['pending','overdue','partial']);
                ?>
                <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #F5F5F5;flex-wrap:wrap;">
                    <div style="width:32px;height:32px;border-radius:8px;background:<?php echo $icolor; ?>22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="<?php echo esc_attr($iicon); ?>" style="width:15px;height:15px;color:<?php echo $icolor; ?>;"></i>
                    </div>
                    <div style="flex:1;min-width:120px;">
                        <div style="font-size:13px;font-weight:600;color:#1A2733;">قسط <?php echo (int)$item['item_number']; ?> — <?php echo number_format($item['amount']); ?> تومان</div>
                        <div style="font-size:11px;color:#7A96A4;">سررسید: <?php echo esc_html($item['due_date_jalali']); ?></div>
                    </div>
                    <span style="font-size:11px;color:<?php echo $icolor; ?>;font-weight:600;"><?php echo esc_html($ilabel); ?></span>

                    <?php if($is_payable): ?>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <!-- پرداخت از کیف پول -->
                        <?php if($wallet >= (float)$item['amount']): ?>
                        <button onclick="DentalPortal.payFromWallet(<?php echo (int)$item['id']; ?>,<?php echo (float)$item['amount']; ?>)"
                            style="background:#E8F4F8;color:#1A6B8A;border:1px solid #1A6B8A;border-radius:6px;padding:6px 12px;font-size:12px;cursor:pointer;font-family:Vazirmatn,Tahoma;">
                            👛 کیف پول
                        </button>
                        <?php endif; ?>

                        <!-- پرداخت آنلاین -->
                        <?php if($online_en): ?>
                        <button onclick="DentalPortal.payOnline(<?php echo (int)$item['id']; ?>,<?php echo (float)$item['amount']; ?>)"
                            style="background:#2ECC9A;color:#fff;border:none;border-radius:6px;padding:6px 12px;font-size:12px;cursor:pointer;font-family:Vazirmatn,Tahoma;">
                            💳 پرداخت آنلاین
                        </button>
                        <?php endif; ?>

                        <!-- انتقال کارت -->
                        <?php if($card_number): ?>
                        <button onclick="DentalPortal.showCardTransfer(<?php echo (int)$item['id']; ?>,<?php echo (float)$item['amount']; ?>)"
                            style="background:#FEF6E4;color:#7a5200;border:1px solid #F0A500;border-radius:6px;padding:6px 12px;font-size:12px;cursor:pointer;font-family:Vazirmatn,Tahoma;">
                            🏦 انتقال کارت
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach;

        // مودال انتقال کارت + ثبت فیش
        if($card_number): ?>
        <div id="portal-card-modal" style="display:none;position:fixed;inset:0;background:rgba(26,39,51,.65);z-index:9999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:14px;width:100%;max-width:460px;margin:20px;overflow:hidden;">
                <div style="background:linear-gradient(135deg,#1A6B8A,#0F4D66);padding:14px 18px;display:flex;align-items:center;justify-content:space-between;color:#fff;">
                    <h4 style="margin:0;font-size:15px;font-family:Vazirmatn,Tahoma;">انتقال به کارت</h4>
                    <button onclick="document.getElementById('portal-card-modal').style.display='none'"
                        style="background:rgba(255,255,255,.2);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:15px;">✕</button>
                </div>
                <div style="padding:20px;direction:rtl;font-family:Vazirmatn,Tahoma;">
                    <!-- اطلاعات کارت -->
                    <div style="background:linear-gradient(135deg,#1A6B8A,#0F4D66);border-radius:12px;padding:20px;color:#fff;margin-bottom:16px;">
                        <div style="font-size:11px;opacity:.7;margin-bottom:8px;">شماره کارت</div>
                        <div style="font-size:18px;font-weight:700;letter-spacing:4px;direction:ltr;text-align:center;margin-bottom:12px;"><?php echo esc_html($card_number); ?></div>
                        <div style="display:flex;justify-content:space-between;font-size:12px;opacity:.8;">
                            <span><?php echo esc_html($card_name); ?></span>
                            <span><?php echo esc_html($card_bank); ?></span>
                        </div>
                    </div>
                    <div style="background:#FEF6E4;border-radius:8px;padding:12px;font-size:13px;color:#7a5200;margin-bottom:16px;">
                        <strong>مبلغ قابل پرداخت:</strong> <span id="card-amount" style="font-weight:700;"></span> تومان
                    </div>

                    <!-- فرم ثبت فیش -->
                    <form id="portal-receipt-form" enctype="multipart/form-data">
                        <input type="hidden" name="dental_action" value="upload_receipt">
                        <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('dental_portal_nonce')); ?>">
                        <input type="hidden" name="item_id" id="receipt-item-id">

                        <div style="margin-bottom:12px;">
                            <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">آپلود تصویر فیش یا شماره پیگیری</label>
                            <input type="file" name="receipt" accept="image/*"
                                style="width:100%;padding:8px;border:1.5px dashed #C8D4DC;border-radius:8px;font-family:Vazirmatn,Tahoma;font-size:13px;cursor:pointer;box-sizing:border-box;">
                        </div>
                        <div style="margin-bottom:16px;">
                            <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;">شماره پیگیری (اختیاری)</label>
                            <input type="text" name="notes" placeholder="کد پیگیری انتقال..."
                                style="width:100%;padding:10px 12px;border:1px solid #C8D4DC;border-radius:8px;font-family:Vazirmatn,Tahoma;font-size:13px;box-sizing:border-box;direction:ltr;">
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button type="submit"
                                style="flex:1;background:#1A6B8A;color:#fff;border:none;border-radius:8px;padding:10px;font-family:Vazirmatn,Tahoma;font-size:13px;font-weight:600;cursor:pointer;">
                                📤 ثبت فیش
                            </button>
                            <button type="button" onclick="document.getElementById('portal-card-modal').style.display='none'"
                                style="padding:10px 16px;background:#F0F4F6;color:#5A7080;border:none;border-radius:8px;font-family:Vazirmatn,Tahoma;font-size:13px;cursor:pointer;">
                                انصراف
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <script>
        window.DentalPortal = window.DentalPortal || {};
        DentalPortal.showCardTransfer = function(itemId, amount) {
            document.getElementById("receipt-item-id").value = itemId;
            document.getElementById("card-amount").textContent = amount.toLocaleString("fa-IR");
            document.getElementById("portal-card-modal").style.display = "flex";
        };
        DentalPortal.payFromWallet = function(itemId, amount) {
            if(!confirm("پرداخت " + amount.toLocaleString("fa-IR") + " تومان از کیف پول؟")) return;
            fetch(dentalPortal.apiBase + "/financial/pay-item", {
                method:"POST",
                headers:{"Content-Type":"application/json","X-WP-Nonce":dentalPortal.nonce},
                body:JSON.stringify({item_id:itemId,amount:amount,method:"wallet",from_wallet:true})
            }).then(r=>r.json()).then(function(d){
                if(d.success) { Swal.fire({icon:"success",title:"پرداخت انجام شد",timer:2000,showConfirmButton:false}).then(()=>location.reload()); }
                else Swal.fire({icon:"error",title:d.message||"خطا"});
            });
        };
        DentalPortal.payOnline = function(itemId, amount) {
            window.location.href = dentalPortal.ajaxUrl + "?action=dental_online_pay&item_id=" + itemId + "&amount=" + amount;
        };
        // ثبت فیش
        document.getElementById("portal-receipt-form")?.addEventListener("submit", function(e){
            e.preventDefault();
            var fd = new FormData(this);
            fetch(dentalPortal.ajaxUrl, {method:"POST",body:fd})
            .then(r=>r.json()).then(function(d){
                document.getElementById("portal-card-modal").style.display = "none";
                if(d.success) Swal.fire({icon:"success",title:d.data.message||"ثبت شد",timer:3000,showConfirmButton:false}).then(()=>location.reload());
                else Swal.fire({icon:"error",title:d.data||"خطا"});
            });
        });
        </script>
        <?php
    }
}
