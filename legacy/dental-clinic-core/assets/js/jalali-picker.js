(function(global){
    'use strict';

    // ─── تبدیل میلادی به شمسی (الگوریتم سبک) ─────────────────
    function toJalali(gy, gm, gd) {
        var g_d_no, j_d_no, j_np, i;
        var gy2 = (gm > 2) ? (gy + 1) : gy;
        g_d_no = 365 * gy + Math.floor((gy2 + 3) / 4) - Math.floor((gy2 + 99) / 100)
               + Math.floor((gy2 + 399) / 400);
        for(i=0;i<gm-1;i++) g_d_no += [31,28,31,30,31,30,31,31,30,31,30,31][i];
        if(gm>2 && ((gy%4==0&&gy%100!=0)||(gy%400==0))) g_d_no++;
        g_d_no += gd - 1;
        j_d_no = g_d_no - 79;
        j_np = Math.floor(j_d_no / 12053); j_d_no %= 12053;
        var jy = 979 + 33 * j_np + 4 * Math.floor(j_d_no / 1461);
        j_d_no %= 1461;
        if(j_d_no >= 366){ jy += Math.floor((j_d_no-1)/365); j_d_no = (j_d_no-1) % 365; }
        for(i=0;i<11&&j_d_no>=[31,31,31,31,31,31,30,30,30,30,30][i];i++) j_d_no-=[31,31,31,31,31,31,30,30,30,30,30][i];
        return [jy, i+1, j_d_no+1];
    }

    function fromJalali(jy, jm, jd) {
        var jy2 = jy - 979, jm2 = jm - 1, jd2 = jd - 1;
        var j_day_no = 365*jy2 + Math.floor(jy2/33)*8 + Math.floor((jy2%33+3)/4);
        for(var i=0;i<jm2;i++) j_day_no += [31,31,31,31,31,31,30,30,30,30,30,29][i];
        j_day_no += jd2;
        var g_day_no = j_day_no + 79;
        var gy = 1600 + 400*Math.floor(g_day_no/146097); g_day_no %= 146097;
        var leap = true;
        if(g_day_no >= 36525){ g_day_no--; gy += 100*Math.floor(g_day_no/36524); g_day_no %= 36524;
            if(g_day_no >= 365) g_day_no++; else leap = false; }
        gy += 4*Math.floor(g_day_no/1461); g_day_no %= 1461;
        if(g_day_no >= 366){ leap=false; g_day_no--; gy+=Math.floor(g_day_no/365); g_day_no%=365; }
        var gm_days = [31,(leap?29:28),31,30,31,30,31,31,30,31,30,31];
        var gm2;
        for(gm2=0;gm2<12&&g_day_no>=gm_days[gm2];gm2++) g_day_no-=gm_days[gm2];
        return {year:gy, month:gm2+1, day:g_day_no+1};
    }

    var JALALI_MONTHS = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور',
                          'مهر','آبان','آذر','دی','بهمن','اسفند'];
    var JALALI_DAYS   = ['ش','ی','د','س','چ','پ','ج'];

    function pad(n){ return String(n).padStart(2,'0'); }

    function farsiNum(n){
        return String(n).replace(/[0-9]/g,function(d){
            return '۰۱۲۳۴۵۶۷۸۹'[d];
        });
    }

    // ─── ساخت popup تقویم ─────────────────────────────────────
    function createPicker(inputEl, hiddenEl) {
        var today  = new Date();
        var tj     = toJalali(today.getFullYear(), today.getMonth()+1, today.getDate());
        var viewYear  = tj[0];
        var viewMonth = tj[1]; // 1-based

        // اگر مقدار فعلی داریم آن را parse کن
        if(hiddenEl.value){
            var parts = hiddenEl.value.split('/');
            if(parts.length===3){
                viewYear  = parseInt(parts[0])||tj[0];
                viewMonth = parseInt(parts[1])||tj[1];
            }
        }

        // Overlay
        var overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;z-index:99998;';
        overlay.addEventListener('click', removePicker);

        // Popup
        var popup = document.createElement('div');
        popup.id = 'dc-jalali-popup';
        popup.style.cssText = [
            'position:absolute',
            'z-index:99999',
            'background:#fff',
            'border-radius:12px',
            'box-shadow:0 8px 32px rgba(0,0,0,.18)',
            'width:280px',
            'direction:rtl',
            'font-family:Tahoma,sans-serif',
            'font-size:13px',
            'overflow:hidden',
        ].join(';');

        document.body.appendChild(overlay);
        document.body.appendChild(popup);

        // جایگذاری popup
        var rect = inputEl.getBoundingClientRect();
        popup.style.top  = (rect.bottom + window.scrollY + 4) + 'px';
        popup.style.right = (document.body.clientWidth - rect.right + window.scrollX) + 'px';

        function removePicker(){
            if(popup.parentNode)  popup.parentNode.removeChild(popup);
            if(overlay.parentNode) overlay.parentNode.removeChild(overlay);
        }

        popup.addEventListener('click', function(e){ e.stopPropagation(); });

        function renderCalendar(){
            // اول روز هفته ماه را بیابیم
            var gStart = fromJalali(viewYear, viewMonth, 1);
            var startDate = new Date(gStart.year, gStart.month-1, gStart.day);
            // شنبه = ۰، جمعه = ۶
            var startDow = (startDate.getDay() + 1) % 7; // تبدیل Sun=0 به Sat=0

            // تعداد روزهای ماه
            var daysInMonth = viewMonth <= 6 ? 31 : (viewMonth <= 11 ? 30 : 29);
            // اسفند: اگر کبیسه بود 30 روز
            if(viewMonth === 12){
                var leapCheck = fromJalali(viewYear+1, 1, 1);
                var diff = new Date(leapCheck.year, leapCheck.month-1, leapCheck.day)
                         - new Date(gStart.year, gStart.month-1, gStart.day);
                daysInMonth = diff / 86400000;
            }

            // مقدار انتخاب‌شده فعلی
            var selParts = (hiddenEl.value||'').split('/');
            var selY = parseInt(selParts[0])||0;
            var selM = parseInt(selParts[1])||0;
            var selD = parseInt(selParts[2])||0;

            var html = '';

            // هدر ناوبری
            html += '<div style="background:#1A6B8A;color:#fff;padding:12px 10px;display:flex;justify-content:space-between;align-items:center;">';
            html += '<button onclick="window._dcPrev()" style="background:none;border:none;color:#fff;font-size:18px;cursor:pointer;padding:0 8px;">‹</button>';
            html += '<span style="font-weight:700;">' + farsiNum(viewYear) + ' ' + JALALI_MONTHS[viewMonth-1] + '</span>';
            html += '<button onclick="window._dcNext()" style="background:none;border:none;color:#fff;font-size:18px;cursor:pointer;padding:0 8px;">›</button>';
            html += '</div>';

            // تغییر سریع ماه/سال
            html += '<div style="display:flex;gap:6px;padding:8px;border-bottom:1px solid #EEF2F5;">';
            html += '<select id="dc-jy-sel" onchange="window._dcSetYear(this.value)" style="flex:1;padding:4px 6px;border:1px solid #D0DCE4;border-radius:6px;font-family:Tahoma;font-size:12px;">';
            for(var yy = tj[0]-80; yy <= tj[0]+5; yy++){
                html += '<option value="'+yy+'"'+(yy===viewYear?' selected':'')+'>' + farsiNum(yy) + '</option>';
            }
            html += '</select>';
            html += '<select id="dc-jm-sel" onchange="window._dcSetMonth(this.value)" style="flex:1;padding:4px 6px;border:1px solid #D0DCE4;border-radius:6px;font-family:Tahoma;font-size:12px;">';
            for(var mm=1; mm<=12; mm++){
                html += '<option value="'+mm+'"'+(mm===viewMonth?' selected':'')+'>' + JALALI_MONTHS[mm-1] + '</option>';
            }
            html += '</select>';
            html += '</div>';

            // سرستون روزهای هفته
            html += '<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;padding:8px 8px 4px;text-align:center;">';
            JALALI_DAYS.forEach(function(d){
                html += '<div style="font-size:11px;color:#7A96A4;font-weight:600;">' + d + '</div>';
            });
            html += '</div>';

            // روزها
            html += '<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;padding:4px 8px 12px;">';

            // خانه‌های خالی
            for(var blank=0; blank<startDow; blank++){
                html += '<div></div>';
            }

            for(var day=1; day<=daysInMonth; day++){
                var isToday  = (viewYear===tj[0] && viewMonth===tj[1] && day===tj[2]);
                var isSelect = (viewYear===selY && viewMonth===selM && day===selD);
                var isFri    = ((startDow + day - 1) % 7 === 6);

                var bg    = isSelect ? '#1A6B8A' : (isToday ? '#E8F4F8' : 'transparent');
                var color = isSelect ? '#fff' : (isFri ? '#E05252' : '#1A2733');
                var fw    = (isSelect||isToday) ? '700' : '400';

                html += '<div onclick="window._dcSelect('+viewYear+','+viewMonth+','+day+')"' +
                    ' style="text-align:center;padding:5px 2px;border-radius:6px;cursor:pointer;' +
                    'background:'+bg+';color:'+color+';font-weight:'+fw+';font-size:12px;' +
                    'transition:background .1s;" ' +
                    'onmouseover="if(!'+isSelect+')this.style.background=\'#F0F8FC\'" ' +
                    'onmouseout="if(!'+isSelect+')this.style.background=\''+bg+'\'">' +
                    farsiNum(day) + '</div>';
            }
            html += '</div>';

            // دکمه امروز
            html += '<div style="padding:0 8px 10px;border-top:1px solid #EEF2F5;padding-top:8px;">';
            html += '<button onclick="window._dcToday()" style="width:100%;padding:7px;background:#F0F8FC;border:1px solid #1A6B8A;border-radius:6px;color:#1A6B8A;font-family:Tahoma;font-size:12px;cursor:pointer;font-weight:600;">امروز</button>';
            html += '</div>';

            popup.innerHTML = html;
        }

        window._dcPrev = function(){
            viewMonth--;
            if(viewMonth<1){ viewMonth=12; viewYear--; }
            renderCalendar();
        };
        window._dcNext = function(){
            viewMonth++;
            if(viewMonth>12){ viewMonth=1; viewYear++; }
            renderCalendar();
        };
        window._dcSetYear = function(y){
            viewYear = parseInt(y);
            renderCalendar();
        };
        window._dcSetMonth = function(m){
            viewMonth = parseInt(m);
            renderCalendar();
        };
        window._dcSelect = function(y, m, d){
            var val = y + '/' + pad(m) + '/' + pad(d);
            hiddenEl.value  = val;
            inputEl.value   = val;
            removePicker();
            inputEl.dispatchEvent(new Event('change'));
        };
        window._dcToday = function(){
            window._dcSelect(tj[0], tj[1], tj[2]);
        };

        renderCalendar();
    }

    // ─── API عمومی ────────────────────────────────────────────
    global.DentalJalali = {
        init: function(displayId, hiddenId){
            var disp   = document.getElementById(displayId);
            var hidden = document.getElementById(hiddenId);
            if(!disp || !hidden){ return; }

            disp.readOnly = true;
            disp.style.cursor = 'pointer';

            disp.addEventListener('click', function(e){
                e.stopPropagation();
                var existing = document.getElementById('dc-jalali-popup');
                if(existing) existing.parentNode && existing.parentNode.removeChild(existing);
                createPicker(disp, hidden);
            });
        },

        // تبدیل میلادی به شمسی (برای نمایش)
        toJalali: function(date){
            if(!date) return '';
            var d  = date instanceof Date ? date : new Date(date);
            var j  = toJalali(d.getFullYear(), d.getMonth()+1, d.getDate());
            return j[0] + '/' + pad(j[1]) + '/' + pad(j[2]);
        }
    };

})(window);

// ─── اتوماتیک init همه input های دارای data-jalali ──────────
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('input[data-jalali-display]').forEach(function(el){
        var hiddenId = el.dataset.jalaliHidden || (el.id + '-value');
        DentalJalali.init(el.id, hiddenId);
    });

    // init مستقیم برای صفحه بیمار
    if(document.getElementById('patient-dob-display')){
        DentalJalali.init('patient-dob-display', 'patient-dob-value');
    }
});