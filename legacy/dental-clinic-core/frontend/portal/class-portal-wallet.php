<?php
defined('ABSPATH') || exit;

class Dental_Portal_Wallet {

    private int $patient_id;

    public function __construct(int $patient_id) {
        $this->patient_id = $patient_id;
    }

    public function render(): void {
        $wallet  = Dental_Patient_Wallet::get_balance($this->patient_id);
        $points  = (int)get_post_meta($this->patient_id,'_loyalty_points',true);
        $txs     = Dental_Patient_Wallet::get_transactions($this->patient_id, 30);
        $redeem_rate = 10000; // هر ۱۰۰ امتیاز = ۱۰,۰۰۰ تومان
        ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">

            <!-- کارت کیف پول -->
            <div>
                <div style="background:linear-gradient(135deg,#1A6B8A,#0F4D66);border-radius:16px;padding:24px;color:#fff;margin-bottom:16px;">
                    <div style="font-size:13px;opacity:.7;margin-bottom:8px;">موجودی کیف پول</div>
                    <div style="font-size:32px;font-weight:700;margin-bottom:4px;"><?php echo number_format($wallet); ?></div>
                    <div style="font-size:14px;opacity:.7;">تومان</div>
                </div>

                <!-- امتیاز -->
                <div style="background:#fff;border-radius:12px;border:1px solid #EEF2F5;padding:16px;margin-bottom:16px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <i data-lucide="star" style="width:16px;height:16px;color:#8B5CF6;"></i>
                            <span style="font-size:13px;font-weight:600;color:#1A2733;">امتیاز وفاداری</span>
                        </div>
                        <span style="font-size:20px;font-weight:700;color:#8B5CF6;"><?php echo number_format($points); ?></span>
                    </div>
                    <?php if($points >= 100): ?>
                    <div style="background:#F3F0FF;border-radius:8px;padding:10px 12px;font-size:12px;color:#8B5CF6;margin-bottom:10px;">
                        می‌توانید <?php echo number_format(floor($points/100)*$redeem_rate); ?> تومان از امتیازهایتان استفاده کنید
                    </div>
                    <button onclick="DentalPortal.redeemPoints(<?php echo $points; ?>)"
                        style="width:100%;background:#8B5CF6;color:#fff;border:none;border-radius:8px;padding:10px;font-family:Vazirmatn,Tahoma;font-size:13px;cursor:pointer;">
                        تبدیل امتیاز به کیف پول
                    </button>
                    <?php else: ?>
                    <div style="font-size:12px;color:#A0B4C0;text-align:center;padding:8px 0;">
                        برای استفاده از امتیاز حداقل ۱۰۰ امتیاز نیاز است
                    </div>
                    <?php endif; ?>
                </div>

                <!-- راهنمای امتیاز -->
                <div style="background:#F8FAFB;border-radius:12px;border:1px solid #EEF2F5;padding:14px;">
                    <div style="font-size:12px;font-weight:600;color:#5A7080;margin-bottom:8px;">نحوه کسب امتیاز:</div>
                    <div style="font-size:12px;color:#7A96A4;display:flex;flex-direction:column;gap:4px;">
                        <div>🎯 هر ۱,۰۰۰ تومان شارژ = ۱ امتیاز</div>
                        <div>🎁 هر ۱۰۰ امتیاز = <?php echo number_format($redeem_rate); ?> تومان</div>
                    </div>
                </div>
            </div>

            <!-- تاریخچه تراکنش‌ها -->
            <div style="background:#fff;border-radius:12px;border:1px solid #EEF2F5;overflow:hidden;">
                <div style="padding:14px 16px;border-bottom:1px solid #EEF2F5;display:flex;align-items:center;gap:8px;">
                    <i data-lucide="list" style="width:16px;height:16px;color:#1A6B8A;"></i>
                    <span style="font-size:13px;font-weight:700;color:#1A2733;">تاریخچه تراکنش‌ها</span>
                </div>
                <div style="max-height:450px;overflow-y:auto;">
                    <?php if(empty($txs)): ?>
                    <p style="text-align:center;color:#A0B4C0;font-size:13px;padding:32px;">تراکنشی ثبت نشده</p>
                    <?php else: foreach($txs as $tx):
                        $cr = $tx['transaction_type']==='credit';
                        $dt = Dental_Jalali::to_jalali($tx['created_at'],'Y/m/d');
                        $type_labels = [
                            'top_up'          => 'شارژ کیف پول',
                            'installment_pay' => 'پرداخت قسط',
                            'loyalty_reward'  => 'تبدیل امتیاز',
                            'admin_adjust'    => 'تنظیم ادمین',
                            'refund'          => 'استرداد',
                        ];
                        $source_label = $type_labels[$tx['source']] ?? $tx['source'];
                    ?>
                    <div style="display:flex;align-items:center;padding:12px 16px;border-bottom:1px solid #F5F5F5;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:8px;background:<?php echo $cr?'#E8FAF4':'#FDEAEA'; ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i data-lucide="<?php echo $cr?'arrow-down-left':'arrow-up-right'; ?>"
                               style="width:16px;height:16px;color:<?php echo $cr?'#2ECC9A':'#E05252'; ?>;"></i>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:13px;font-weight:500;color:#1A2733;"><?php echo esc_html($tx['description']?:$source_label); ?></div>
                            <div style="font-size:11px;color:#A0B4C0;"><?php echo esc_html($dt); ?></div>
                        </div>
                        <div style="font-weight:700;font-size:13px;color:<?php echo $cr?'#2ECC9A':'#E05252'; ?>;">
                            <?php echo $cr?'+':'-'; ?><?php echo number_format($tx['amount']); ?> ت
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <script>
        window.DentalPortal = window.DentalPortal || {};
        DentalPortal.redeemPoints = function(points) {
            var toRedeem = Math.floor(points/100)*100;
            if(!toRedeem) return;
            Swal.fire({
                title: "تبدیل امتیاز",
                text: toRedeem + " امتیاز به کیف پول اضافه می‌شود. ادامه می‌دهید؟",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "بله",
                cancelButtonText: "انصراف",
                confirmButtonColor: "#8B5CF6"
            }).then(function(r){
                if(!r.isConfirmed) return;
                fetch(dentalPortal.apiBase+"/wallet/redeem-points",{
                    method:"POST",
                    headers:{"Content-Type":"application/json","X-WP-Nonce":dentalPortal.nonce},
                    body:JSON.stringify({patient_id:<?php echo $this->patient_id; ?>,points:toRedeem})
                }).then(r=>r.json()).then(function(d){
                    if(d.success) Swal.fire({icon:"success",title:"انجام شد",timer:2000,showConfirmButton:false}).then(()=>location.reload());
                    else Swal.fire({icon:"error",title:d.message||"خطا"});
                });
            });
        };
        </script>
        <?php
    }
}
