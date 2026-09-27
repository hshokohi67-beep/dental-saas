/**
 * Dental Jalali Datepicker v4
 * تقویم شمسی با قابلیت انتخاب سریع ماه و سال از لیست
 */
(function($){
'use strict';

var JD = {
    toG: function(jy, jm, jd) {
        var jy2=jy-979, jm2=jm-1, jd2=jd-1;
        var jdm=[31,31,31,31,31,31,30,30,30,30,30,29];
        var j_day_no=365*jy2+Math.floor(jy2/33)*8+Math.floor((jy2%33+3)/4);
        for(var i=0;i<jm2;i++) j_day_no+=jdm[i];
        j_day_no+=jd2;
        var g_day_no=j_day_no+79;
        var gy=1600+400*Math.floor(g_day_no/146097);
        g_day_no%=146097;
        var leap=true;
        if(g_day_no>=36525){g_day_no--;gy+=100*Math.floor(g_day_no/36524);g_day_no%=36524;if(g_day_no>=365)g_day_no++;else leap=false;}
        gy+=4*Math.floor(g_day_no/1461);g_day_no%=1461;
        if(g_day_no>=366){leap=false;g_day_no--;gy+=Math.floor(g_day_no/365);g_day_no%=365;}
        var gd=[31,(leap?29:28),31,30,31,30,31,31,30,31,30,31];
        var gm=0;for(gm=0;gm<12;gm++){if(g_day_no<gd[gm])break;g_day_no-=gd[gm];}
        return [gy,gm+1,g_day_no+1];
    },
    toJ: function(gy, gm, gd) {
        var g_d_no,j_d_no,i;
        var gdm=[31,28,31,30,31,30,31,31,30,31,30,31];
        var jdm=[31,31,31,31,31,31,30,30,30,30,30,29];
        gy-=1600; gm-=1; gd-=1;
        g_d_no=365*gy+Math.floor((gy+3)/4)-Math.floor((gy+99)/100)+Math.floor((gy+399)/400);
        for(i=0;i<gm;i++) g_d_no+=gdm[i];
        if(gm>1&&((gy%4===0&&gy%100!==0)||(gy%400===0))) g_d_no++;
        g_d_no+=gd;
        j_d_no=g_d_no-79;
        var j_np=Math.floor(j_d_no/12053); j_d_no%=12053;
        var jy=979+33*j_np+4*Math.floor(j_d_no/1461);
        j_d_no%=1461;
        if(j_d_no>=366){jy+=Math.floor((j_d_no-1)/365); j_d_no=(j_d_no-1)%365;}
        for(i=0;i<11&&j_d_no>=jdm[i];i++) j_d_no-=jdm[i];
        return [jy,i+1,j_d_no+1];
    },
    monthLen: function(jy,jm){
        if(jm<=6) return 31;
        if(jm<=11) return 30;
        return ([1,5,9,13,17,22,26,30].indexOf(jy%33)>-1)?30:29;
    },
    firstDow: function(jy,jm){
        var g=JD.toG(jy,jm,1);
        var dt=new Date(g[0],g[1]-1,g[2]);
        return (dt.getDay()+1)%7;
    }
};

var MONTHS = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
var DAYS   = ['ش','ی','د','س','چ','پ','ج'];
function fa(n){ return String(n).replace(/\d/g,function(d){return '۰۱۲۳۴۵۶۷۸۹'[d];}); }

if(!$('#ddp-style').length){
    $('<style id="ddp-style">\
.ddp{display:none;position:fixed;z-index:2147483647;background:#fff;border:1px solid #C8D4DC;\
border-radius:12px;box-shadow:0 8px 32px rgba(26,39,51,.22);width:280px;\
direction:rtl;font-family:Vazirmatn,Tahoma,sans-serif;overflow:hidden;user-select:none;}\
.ddp-h{background:linear-gradient(135deg,#1A6B8A,#0F4D66);color:#fff;padding:10px 14px;\
display:flex;align-items:center;justify-content:space-between;}\
.ddp-hbtn{background:rgba(255,255,255,.18);border:none;color:#fff;width:30px;height:30px;\
border-radius:7px;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}\
.ddp-hbtn:hover{background:rgba(255,255,255,.35);}\
.ddp-htitle{font-size:14px;font-weight:700;cursor:pointer;padding:4px 10px;border-radius:6px;transition:background .15s;}\
.ddp-htitle:hover{background:rgba(255,255,255,.2);}\
.ddp-b{padding:8px 10px;}\
.ddp-wk{display:grid;grid-template-columns:repeat(7,1fr);margin-bottom:2px;}\
.ddp-wk span{text-align:center;font-size:11px;color:#7A96A4;padding:4px 0;font-weight:600;}\
.ddp-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:1px;}\
.ddp-grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:4px;}\
.ddp-cell{text-align:center;padding:0;height:34px;display:flex;align-items:center;\
justify-content:center;border-radius:7px;font-size:12px;color:#1A2733;\
cursor:pointer;border:none;background:none;font-family:Vazirmatn,Tahoma,sans-serif;width:100%;}\
.ddp-cell:not(.ddp-empty):hover{background:#E8F4F8;color:#1A6B8A;font-weight:700;}\
.ddp-cell.ddp-today{color:#1A6B8A;font-weight:700;}\
.ddp-cell.ddp-today::after{content:"•";display:block;font-size:8px;line-height:0;margin-top:2px;}\
.ddp-cell.ddp-sel{background:#1A6B8A!important;color:#fff!important;font-weight:700;}\
.ddp-cell.ddp-empty{pointer-events:none;}\
.ddp-mcell{height:46px;font-size:13px;font-weight:600;}\
.ddp-mcell.ddp-cur{color:#1A6B8A;font-weight:700;background:#E8F4F8;}\
.ddp-f{padding:6px 12px;border-top:1px solid #EEF2F5;display:flex;justify-content:space-between;}\
.ddp-fbtn{background:none;border:1px solid #C8D4DC;border-radius:6px;padding:4px 12px;\
font-size:11px;cursor:pointer;font-family:Vazirmatn,Tahoma;color:#5A7080;}\
.ddp-fbtn:hover{background:#F0F6F9;}\
.ddp-fbtn.del{border-color:#E05252;color:#E05252;}\
.ddp-fbtn.del:hover{background:#FDEAEA;}\
    </style>').appendTo('head');
}

$.fn.dentalDatepicker = function(){
    return this.each(function(){
        var $inp = $(this);
        if($inp.data('_ddp')) return;

        var now = new Date();
        var tj = JD.toJ(now.getFullYear(), now.getMonth()+1, now.getDate());
        var TY=tj[0], TM=tj[1], TD=tj[2];
        var vY=TY, vM=TM;
        var sY=0, sM=0, sD=0;
        var view = 'days';
        var yBlock = TY - (TY % 12);

        var iv=$inp.val().trim();
        if(/^\d{4}\/\d{1,2}\/\d{1,2}$/.test(iv)){
            var p=iv.split('/'); sY=+p[0]; sM=+p[1]; sD=+p[2]; vY=sY; vM=sM;
            yBlock = vY - (vY % 12);
        }

        var $pop = $('<div class="ddp"></div>').appendTo('body');
        $inp.data('_ddp', $pop);

        function pos(){
            var r = $inp[0].getBoundingClientRect();
            var popW=280, popH=340;
            var t = r.bottom + 4;
            var l = r.left;
            if(t + popH > window.innerHeight){ t = r.top - popH - 4; if(t<4) t=4; }
            if(l + popW > window.innerWidth) l = window.innerWidth - popW - 8;
            if(l < 4) l = 4;
            $pop.css({top:t, left:l});
        }

        function drawDays(){
            var ml=JD.monthLen(vY,vM), dow=JD.firstDow(vY,vM);
            var h = '<div class="ddp-h">'
                  + '<button class="ddp-hbtn" data-act="prev">&rsaquo;</button>'
                  + '<span class="ddp-htitle" data-act="open-months">'+MONTHS[vM-1]+' '+fa(vY)+'</span>'
                  + '<button class="ddp-hbtn" data-act="next">&lsaquo;</button>'
                  + '</div><div class="ddp-b"><div class="ddp-wk">';
            DAYS.forEach(function(d){ h+='<span>'+d+'</span>'; });
            h += '</div><div class="ddp-grid">';
            for(var i=0;i<dow;i++) h+='<button class="ddp-cell ddp-empty" disabled></button>';
            for(var d=1;d<=ml;d++){
                var c='ddp-cell';
                if(vY===TY&&vM===TM&&d===TD) c+=' ddp-today';
                if(vY===sY&&vM===sM&&d===sD) c+=' ddp-sel';
                h+='<button class="'+c+'" data-d="'+d+'">'+fa(d)+'</button>';
            }
            h += '</div></div><div class="ddp-f">'
               + '<button class="ddp-fbtn" data-act="today">امروز</button>'
               + '<button class="ddp-fbtn del" data-act="clear">پاک</button>'
               + '</div>';
            $pop.html(h);
        }

        function drawMonths(){
            var h = '<div class="ddp-h">'
                  + '<button class="ddp-hbtn" data-act="prev">&rsaquo;</button>'
                  + '<span class="ddp-htitle" data-act="open-years">'+fa(vY)+'</span>'
                  + '<button class="ddp-hbtn" data-act="next">&lsaquo;</button>'
                  + '</div><div class="ddp-b"><div class="ddp-grid3">';
            MONTHS.forEach(function(m, idx){
                var c='ddp-cell ddp-mcell';
                if(idx+1===vM) c+=' ddp-cur';
                h += '<button class="'+c+'" data-m="'+(idx+1)+'">'+m+'</button>';
            });
            h += '</div></div>';
            $pop.html(h);
        }

        function drawYears(){
            var h = '<div class="ddp-h">'
                  + '<button class="ddp-hbtn" data-act="prev">&rsaquo;</button>'
                  + '<span class="ddp-htitle">'+fa(yBlock)+' - '+fa(yBlock+11)+'</span>'
                  + '<button class="ddp-hbtn" data-act="next">&lsaquo;</button>'
                  + '</div><div class="ddp-b"><div class="ddp-grid3">';
            for(var y=yBlock; y<yBlock+12; y++){
                var c='ddp-cell ddp-mcell';
                if(y===vY) c+=' ddp-cur';
                h += '<button class="'+c+'" data-y="'+y+'">'+fa(y)+'</button>';
            }
            h += '</div></div>';
            $pop.html(h);
        }

        function draw(){
            if(view==='months') drawMonths();
            else if(view==='years') drawYears();
            else drawDays();
        }

        $pop.on('click', '[data-act]', function(e){
            e.stopPropagation();
            var act = $(this).data('act');
            if(act==='prev'){
                if(view==='days'){ vM--; if(vM<1){vM=12;vY--;} }
                else if(view==='months'){ vY--; }
                else if(view==='years'){ yBlock -= 12; }
                draw();
            } else if(act==='next'){
                if(view==='days'){ vM++; if(vM>12){vM=1;vY++;} }
                else if(view==='months'){ vY++; }
                else if(view==='years'){ yBlock += 12; }
                draw();
            } else if(act==='today'){
                vY=TY; vM=TM; pick(TD);
            } else if(act==='clear'){
                $inp.val('').trigger('input').trigger('change');
                sY=sM=sD=0; hide();
            } else if(act==='open-months'){
                view='months'; draw();
            } else if(act==='open-years'){
                yBlock = vY - (vY % 12);
                view='years'; draw();
            }
        });

        $pop.on('click', '[data-d]', function(e){
            e.stopPropagation();
            pick(+$(this).data('d'));
        });

        $pop.on('click', '[data-m]', function(e){
            e.stopPropagation();
            vM = +$(this).data('m');
            view = 'days';
            draw();
        });

        $pop.on('click', '[data-y]', function(e){
            e.stopPropagation();
            vY = +$(this).data('y');
            view = 'months';
            draw();
        });

        function pick(d){
            sY=vY; sM=vM; sD=d;
            var v = sY+'/'+String(sM).padStart(2,'0')+'/'+String(sD).padStart(2,'0');
            $inp.val(v).trigger('input').trigger('change');
            if(typeof calcPreview==='function') calcPreview();
            if(typeof loadSlots==='function') loadSlots();
            hide();
        }

        function show(){ view='days'; pos(); draw(); $pop.show(); }
        function hide(){ $pop.hide(); }

        $inp.attr('autocomplete','off').prop('readonly',true).css('cursor','pointer');
        $inp.on('click', function(e){ e.stopPropagation(); $pop.is(':visible') ? hide() : show(); });
        $pop.on('click', function(e){ e.stopPropagation(); });
        $(document).on('click', function(){ hide(); });
    });
};

function dcDP(ctx){
    $(ctx||document).find([
        'input[name="patient_dob"]','input[name="start_date_jalali"]',
        'input[name="appt_date_jalali"]','input[name="blocked_date_jalali"]',
        '#inp-start','#bk-date','#bk-date-input','.dc-datepicker'
    ].join(',')).each(function(){
        if(!$(this).data('_ddp')) $(this).dentalDatepicker();
    });
}

$(document).ready(function(){
    dcDP();
    if(typeof MutationObserver!=='undefined'){
        new MutationObserver(function(ms){
            ms.forEach(function(m){
                m.addedNodes.forEach(function(n){ if(n.nodeType===1) dcDP(n); });
            });
        }).observe(document.body,{childList:true,subtree:true});
    }
});

})(jQuery);
