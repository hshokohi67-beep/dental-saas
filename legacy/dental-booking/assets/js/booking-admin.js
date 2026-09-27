(function($){
"use strict";

// init Lucide
document.addEventListener("DOMContentLoaded", function(){
    if(typeof lucide !== "undefined") lucide.createIcons();
});

// ─── تقویم — انتخاب تاریخ با datepicker ─────────────────────
window.BookingAdmin = {

    // رفرش صفحه با تاریخ جدید
    goToDate: function(jalali_date) {
        if(!jalali_date) return;
        // تبدیل شمسی به میلادی از طریق API
        fetch(dentalBooking.apiBase + "/slots?doctor_id=0&date=" + encodeURIComponent(jalali_date), {
            headers: {"X-WP-Nonce": dentalBooking.nonce}
        }).then(r => r.json()).then(function(d){
            if(d.data && d.data.gregorian) {
                window.location.href = window.location.pathname + "?page=dental-booking&date=" + d.data.gregorian;
            }
        }).catch(function(){
            // fallback: parse ساده
            var parts = jalali_date.split("/");
            if(parts.length === 3) {
                window.location.href = window.location.pathname + "?page=dental-booking&date=" + jalali_date;
            }
        });
    },

    // لود اسلات‌ها برای فرم نوبت جدید
    loadSlots: function(doctorId, jalaliDate, targetSelect, shiftInput) {
        if(!doctorId || !jalaliDate) return;
        targetSelect.innerHTML = '<option value="">در حال بارگذاری...</option>';

        fetch(dentalBooking.apiBase + "/slots?doctor_id=" + doctorId + "&date=" + encodeURIComponent(jalaliDate), {
            headers: {"X-WP-Nonce": dentalBooking.nonce}
        }).then(r => r.json()).then(function(d){
            if(d.success && d.data.slots && d.data.slots.length) {
                targetSelect.innerHTML = d.data.slots.map(function(s){
                    return '<option value="' + s.time + '" data-shift="' + s.shift_id + '"' +
                        (s.available ? '' : ' disabled') + '>' +
                        s.time + (s.available ? '' : ' (پر)') + '</option>';
                }).join('');

                // آپدیت shift_id با تغییر ساعت
                targetSelect.addEventListener("change", function(){
                    var opt = this.options[this.selectedIndex];
                    if(shiftInput) shiftInput.value = opt.dataset.shift || '';
                });
                // trigger اولیه
                var e = new Event('change');
                targetSelect.dispatchEvent(e);
            } else {
                targetSelect.innerHTML = '<option value="">اسلوتی موجود نیست</option>';
            }
        }).catch(function(){
            targetSelect.innerHTML = '<option value="">خطا در بارگذاری</option>';
        });
    },

    // تأیید سریع نوبت با SweetAlert
    quickConfirm: function(apptId, action) {
        var labels = {
            confirmed: {title:'تأیید نوبت',  text:'این نوبت تأیید شود؟',  btn:'تأیید',  color:'#2ECC9A'},
            rejected:  {title:'رد نوبت',     text:'این نوبت رد شود؟',     btn:'رد',     color:'#E05252'},
            cancelled: {title:'لغو نوبت',    text:'این نوبت لغو شود؟',    btn:'لغو',    color:'#F0A500'},
            done:      {title:'اتمام نوبت',  text:'نوبت انجام شد؟',       btn:'تأیید',  color:'#1A6B8A'},
        };
        var cfg = labels[action];
        if(!cfg) return;

        Swal.fire({
            title: cfg.title,
            text:  cfg.text,
            icon:  'question',
            showCancelButton: true,
            confirmButtonText: cfg.btn,
            cancelButtonText:  'انصراف',
            confirmButtonColor: cfg.color,
            ...(action === 'cancelled' ? {
                input: 'text',
                inputPlaceholder: 'دلیل لغو (اختیاری)'
            } : {})
        }).then(function(r){
            if(!r.isConfirmed) return;
            var form = document.createElement('form');
            form.method = 'post';
            form.innerHTML = [
                '<input type="hidden" name="_wpnonce" value="' + dentalBooking.bookingNonce + '">',
                '<input type="hidden" name="appt_id" value="' + apptId + '">',
                '<input type="hidden" name="booking_action" value="' + action + '">',
                action === 'cancelled' ? '<input type="hidden" name="cancel_reason" value="' + (r.value||'') + '">' : '',
            ].join('');
            document.body.appendChild(form);
            form.submit();
        });
    },
};

// ─── داتپیکر روی فیلدهای ادمین ──────────────────────────────
$(document).ready(function(){
    // بارگذاری اسلات‌ها هنگام تغییر پزشک یا تاریخ در فرم نوبت جدید
    var doctorSel = document.getElementById("bk-doctor");
    var dateInput = document.getElementById("bk-date");
    var timeSel   = document.getElementById("bk-time");
    var shiftInp  = document.getElementById("bk-shift");

    function tryLoadSlots(){
        var doc  = doctorSel ? doctorSel.value : '';
        var date = dateInput  ? dateInput.value.trim() : '';
        if(doc && date && date.length >= 8) {
            BookingAdmin.loadSlots(doc, date, timeSel, shiftInp);
        }
    }

    if(doctorSel) doctorSel.addEventListener("change", tryLoadSlots);
    if(dateInput) dateInput.addEventListener("input",  tryLoadSlots);
    if(dateInput) dateInput.addEventListener("change", tryLoadSlots);
});

})(jQuery);
