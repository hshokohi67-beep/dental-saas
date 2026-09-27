(function(){
"use strict";

var COLOR_PRIORITY = ["missing","implant","rct","crown","filling","veneer","inlay_onlay","composite","amalgam","caries","healthy"];
var COLORS = {
    healthy:    {fill:"#FFFFFF",stroke:"#C8D4DC",label:"سالم"},
    caries:     {fill:"#FFE8D6",stroke:"#E8A87C",label:"پوسیدگی"},
    composite:  {fill:"#E8F4FC",stroke:"#5DADE2",label:"ترمیم"},
    amalgam:    {fill:"#E8F4FC",stroke:"#5DADE2",label:"ترمیم"},
    filling:    {fill:"#E8F4FC",stroke:"#5DADE2",label:"ترمیم"},
    rct:        {fill:"#EDE7F6",stroke:"#9B7FD4",label:"عصب‌کشی"},
    crown:      {fill:"#FEF9E7",stroke:"#F0C040",label:"روکش"},
    implant:    {fill:"#E8F8F5",stroke:"#4DB6AC",label:"ایمپلنت"},
    missing:    {fill:"#F2F3F4",stroke:"#B0BEC5",label:"کشیده شده"},
    bridge_abutment:{fill:"#EBF5FB",stroke:"#78C0E0",label:"بریج"},
    veneer:     {fill:"#FEF0FB",stroke:"#D98FD6",label:"لامینیت"},
    inlay_onlay:{fill:"#E8F4FC",stroke:"#5DADE2",label:"انله/آنله"},
};

function getAutoColor(treatments){
    for(var i=0;i<COLOR_PRIORITY.length;i++){
        if(treatments.indexOf(COLOR_PRIORITY[i])>-1) return COLOR_PRIORITY[i];
    }
    return "healthy";
}

var TOOTH_TX = [
    {code:"composite",      label:"ترمیم کامپوزیت",       peds:true, adult:true},
    {code:"amalgam",        label:"ترمیم آمالگام",         peds:true, adult:true},
    {code:"rct",            label:"عصب‌کشی (RCT)",        peds:false,adult:true},
    {code:"pulpotomy",      label:"پالپوتومی",             peds:true, adult:false},
    {code:"pulpectomy",     label:"پالپکتومی",             peds:true, adult:false},
    {code:"buildup",        label:"بیلدآپ",               peds:false,adult:true},
    {code:"crown",          label:"روکش",                 peds:true, adult:true},
    {code:"veneer",         label:"لامینیت / ونیر",        peds:false,adult:true},
    {code:"inlay_onlay",    label:"انله / آنله",           peds:false,adult:true},
    {code:"extraction",     label:"کشیدن ساده",            peds:true, adult:true},
    {code:"surgical_ext",   label:"کشیدن جراحی",          peds:false,adult:true},
    {code:"implant",        label:"ایمپلنت",              peds:false,adult:true},
    {code:"apicoectomy",    label:"آپیکوستومی",           peds:false,adult:true},
    {code:"bridge_abutment",label:"بریج (پایه)",          peds:false,adult:true},
    {code:"retainer_fix",   label:"ریتینر فیکس",          peds:false,adult:true},
    {code:"xray_pa",        label:"عکس PA",               peds:true, adult:true},
    {code:"cbct",           label:"CBCT تک دندان",        peds:false,adult:true},
    {code:"consult_perio",  label:"مشاوره پریو",          peds:false,adult:true},
    {code:"consult_endo",   label:"مشاوره اندو",          peds:false,adult:true},
    {code:"consult_surgeon",label:"مشاوره جراح",          peds:true, adult:true},
    {code:"consult_prosth", label:"مشاوره پروتز",         peds:false,adult:true},
    {code:"consult_resto",  label:"مشاوره متخصص ترمیم",  peds:false,adult:true},
    {code:"consult_peds",   label:"مشاوره اطفال",         peds:true, adult:true},
    // ─── ۹ مورد جدید — طبق لیست کاربر، اضافه‌شده بدون دست‌زدن به
    // خطوط بالا (که از قبل کار می‌کردن) ──────────────────────────
    {code:"suspect_endo",   label:"مشکوک به اندو",         peds:true, adult:true},
    {code:"suspect_resto",  label:"مشکوک به ترمیم",        peds:true, adult:true},
    {code:"consult_diag",   label:"مشاوره تخصصی تشخیص",   peds:true, adult:true},
    {code:"resto_control",  label:"کنترل ترمیم",           peds:true, adult:true},
    {code:"xray_occlusal",  label:"رادیوگرافی اکلوزال",    peds:true, adult:true},
    {code:"specialist_tx",  label:"درمان تخصصی",           peds:true, adult:true},
    {code:"xray_lat_ceph",  label:"رادیوگرافی لترال سفالیک",peds:false,adult:true},
    {code:"consult_general",label:"مشاوره تخصصی",         peds:true, adult:true},
    {code:"xray_tmj",       label:"رادیوگرافی لترال T.M.J",peds:false,adult:true},
];

var HALFARCH_SVC = [
    {code:"bwx",            label:"عکس بایت‌وینگ (BWX)"},
    {code:"scaling_half",   label:"جرم‌گیری نیم‌فک"},
    {code:"root_planing",   label:"روت پلنینگ / کورتاژ"},
    {code:"flap_surgery",   label:"فلاپ جراحی"},
    {code:"consult_perio_h",label:"مشاوره پریو نیم‌فک"},
];

var FULLARCH_SVC = [
    {code:"panoramic",      label:"عکس پانورامیک"},
    {code:"scaling_full",   label:"جرم‌گیری کل دهان"},
    {code:"brushing",       label:"بروساژ"},
    {code:"fissure_seal",   label:"فیشورسیلانت"},
    {code:"fluoride",       label:"فلوراید"},
    {code:"bleaching",      label:"بلیچینگ"},
    {code:"ortho",          label:"ارتودنسی"},
    {code:"consult_ortho",  label:"مشاوره ارتودنسی"},
    {code:"study_model",    label:"مدل مطالعه"},
];

// دندان‌های شیری — فقط ۵ تا در هر ربع
var PRIM = {
    q1:[{fdi:55,l:"e"},{fdi:54,l:"d"},{fdi:53,l:"c"},{fdi:52,l:"b"},{fdi:51,l:"a"}],
    q2:[{fdi:61,l:"a"},{fdi:62,l:"b"},{fdi:63,l:"c"},{fdi:64,l:"d"},{fdi:65,l:"e"}],
    q3:[{fdi:71,l:"a"},{fdi:72,l:"b"},{fdi:73,l:"c"},{fdi:74,l:"d"},{fdi:75,l:"e"}],
    q4:[{fdi:85,l:"e"},{fdi:84,l:"d"},{fdi:83,l:"c"},{fdi:82,l:"b"},{fdi:81,l:"a"}],
};

var cfg={}, state={};

function init(config){
    cfg=config;
    // ─── طبق پیشنهاد کاربر: به‌جای وابستگی به config.halfarch/fullarch
    // جدا (که مسیرش مشکل‌دار بود)، این‌ها رو مستقیم از همون conditions
    // (که مطمئناً درست لود می‌شه، چون تک‌دندان‌ها همیشه درست کار کردن)
    // استخراج می‌کنیم — کلید "1_halfarch" تا "4_halfarch" برای نیم‌فک‌ها،
    // و "99_fullarch" برای کل دهان.
    var derivedHalf = {}, derivedFull = {};
    var quadToHalf = {1:"q1",2:"q2",3:"q3",4:"q4"};
    var conds = config.conditions || {};
    Object.keys(conds).forEach(function(key){
        var c = conds[key];
        if (!c || !c.treatments) return;
        if (key.indexOf("_halfarch") !== -1) {
            var toothNum = parseInt(key.split("_")[0], 10);
            var half = quadToHalf[toothNum];
            if (half) {
                derivedHalf[half] = {};
                c.treatments.forEach(function(code){ derivedHalf[half][code] = true; });
            }
        } else if (key.indexOf("_fullarch") !== -1) {
            c.treatments.forEach(function(code){ derivedFull[code] = true; });
        }
    });

    state={
        mode:      config.mode||"adult",
        patientId: config.patientId,
        conditions:config.conditions||{},
        halfarch:  derivedHalf,
        fullarch:  derivedFull,
        nonce:     config.nonce,
        apiBase:   config.apiBase,
        doctors:   config.doctors||[],
        examDoctor:config.examDoctor||0,
        canEdit:   config.canEdit||false,
    };
    render();
    renderPlan();
}

// ─── رندر اصلی ────────────────────────────────────────────────
function render(){
    var w=document.getElementById("dc-chart-area");
    if(!w) return;
    w.innerHTML = state.mode==="peds" ? buildPeds() : buildAdult();
    bindEvents();
}

// ─── بزرگسال ──────────────────────────────────────────────────
// نکته مهم: چارت از دید پزشکِ رو‌به‌روی بیمار ترسیم می‌شود (استاندارد بالینی) —
// سمت راست واقعی بیمار (کوادرانت‌های ۱ و ۴ در FDI) در سمت چپ چارت،
// و سمت چپ واقعی بیمار (کوادرانت‌های ۲ و ۳) در سمت راست چارت قرار می‌گیرد.
function buildAdult(){
    var ur=[],ul=[],ll=[],lr=[];
    // دندان ۱ (میانی) باید کنار خط وسط باشد و دندان ۸ (عقل) در لبه بیرونی —
    // چون کوادرانت‌ها جابه‌جا شدند، جهت شمارش هم باید برعکس شود
    for(var i=8;i>=1;i--) ur.push({fdi:20+i,n:i,type:"permanent"}); // کوادرانت ۲ → راست چارت؛ ۸ بیرون، ۱ وسط
    for(var i=1;i<=8;i++) ul.push({fdi:10+i,n:i,type:"permanent"}); // کوادرانت ۱ → چپ چارت؛ ۱ وسط، ۸ بیرون
    for(var i=1;i<=8;i++) ll.push({fdi:40+i,n:i,type:"permanent"}); // کوادرانت ۴ → چپ چارت؛ ۱ وسط، ۸ بیرون
    for(var i=8;i>=1;i--) lr.push({fdi:30+i,n:i,type:"permanent"}); // کوادرانت ۳ → راست چارت؛ ۸ بیرون، ۱ وسط
    return buildFrame(
        ur.map(function(t){return tCell(t.fdi,t.n,"permanent","top")}).join(""),
        ul.map(function(t){return tCell(t.fdi,t.n,"permanent","top")}).join(""),
        ll.map(function(t){return tCell(t.fdi,t.n,"permanent","bot")}).join(""),
        lr.map(function(t){return tCell(t.fdi,t.n,"permanent","bot")}).join("")
    );
}

// ─── اطفال ────────────────────────────────────────────────────
// همان منطق دید بالینی (پزشک رو‌به‌روی بیمار) در چارت بزرگسال، اینجا هم اعمال شده:
// سمت راست واقعی بیمار در چپ چارت، سمت چپ واقعی بیمار در راست چارت.
function buildPeds(){
    // دندان ۱ (میانی) باید کنار خط وسط باشد و دندان ۸/e در لبه بیرونی —
    // آرایه‌های PRIM از پیش برای چیدمان قبلی مرتب شده بودند، پس برعکس می‌شوند
    var primUR = PRIM.q2.slice().reverse().map(function(t){return primCell(t.fdi,t.l,"top")}).join(""); // چپ بیمار → راست چارت؛ e بیرون
    var primUL = PRIM.q1.slice().reverse().map(function(t){return primCell(t.fdi,t.l,"top")}).join(""); // راست بیمار → چپ چارت؛ a وسط
    // ردیف دائمی بالا — همه ۸ تا
    var permUR=[],permUL=[],permLL=[],permLR=[];
    for(var i=8;i>=1;i--) permUR.push({fdi:20+i,n:i}); // کوادرانت ۲ → راست چارت؛ ۸ بیرون، ۱ وسط
    for(var i=1;i<=8;i++) permUL.push({fdi:10+i,n:i}); // کوادرانت ۱ → چپ چارت؛ ۱ وسط، ۸ بیرون
    for(var i=1;i<=8;i++) permLL.push({fdi:40+i,n:i}); // کوادرانت ۴ → چپ چارت؛ ۱ وسط، ۸ بیرون
    for(var i=8;i>=1;i--) permLR.push({fdi:30+i,n:i}); // کوادرانت ۳ → راست چارت؛ ۸ بیرون، ۱ وسط
    // ردیف شیری پایین
    var primLL = PRIM.q4.slice().reverse().map(function(t){return primCell(t.fdi,t.l,"bot")}).join(""); // راست بیمار → چپ چارت؛ a وسط
    var primLR = PRIM.q3.slice().reverse().map(function(t){return primCell(t.fdi,t.l,"bot")}).join(""); // چپ بیمار → راست چارت؛ e بیرون

    var s = "<div style='direction:rtl;font-family:Tahoma,sans-serif;font-size:12px;'>";
    s += fullArchBtn("top");
    s += "<div style='display:flex;gap:6px;margin:6px 0;'>"+halfBtn("q2","بالا چپ")+halfBtn("q1","بالا راست")+"</div>";

    // شیری بالا
    s += "<div style='display:flex;border:1px solid #DDE5EB;border-radius:8px 8px 0 0;overflow:hidden;background:#EEF7FF;margin-bottom:2px;'>";
    s += "<div style='flex:1;display:flex;justify-content:flex-end;gap:3px;padding:6px 6px 6px 3px;border-left:1px solid #DDE5EB;'>"+primUR+"</div>";
    s += "<div style='flex:1;display:flex;justify-content:flex-start;gap:3px;padding:6px 3px 6px 6px;'>"+primUL+"</div>";
    s += "</div>";

    // دائمی بالا
    s += "<div style='display:flex;border:1px solid #DDE5EB;border-top:none;overflow:hidden;background:#fff;'>";
    s += "<div style='flex:1;display:flex;justify-content:flex-end;gap:3px;padding:8px 6px 8px 3px;border-left:1px solid #DDE5EB;'>";
    s += permUR.map(function(t){return tCell(t.fdi,t.n,"permanent","top")}).join("");
    s += "</div><div style='flex:1;display:flex;justify-content:flex-start;gap:3px;padding:8px 3px 8px 6px;'>";
    s += permUL.map(function(t){return tCell(t.fdi,t.n,"permanent","top")}).join("");
    s += "</div></div>";

    // خط فک
    s += "<div style='height:5px;background:linear-gradient(90deg,#f5f5f5,#e0e0e0,#f5f5f5);border-right:1px solid #DDE5EB;border-left:1px solid #DDE5EB;'></div>";

    // دائمی پایین
    s += "<div style='display:flex;border:1px solid #DDE5EB;border-top:none;overflow:hidden;background:#fff;'>";
    s += "<div style='flex:1;display:flex;justify-content:flex-end;gap:3px;padding:8px 6px 8px 3px;border-left:1px solid #DDE5EB;'>";
    s += permLR.map(function(t){return tCell(t.fdi,t.n,"permanent","bot")}).join("");
    s += "</div><div style='flex:1;display:flex;justify-content:flex-start;gap:3px;padding:8px 3px 8px 6px;'>";
    s += permLL.map(function(t){return tCell(t.fdi,t.n,"permanent","bot")}).join("");
    s += "</div></div>";

    // شیری پایین
    s += "<div style='display:flex;border:1px solid #DDE5EB;border-top:none;border-radius:0 0 8px 8px;overflow:hidden;background:#EEF7FF;margin-top:2px;'>";
    s += "<div style='flex:1;display:flex;justify-content:flex-end;gap:3px;padding:6px 6px 6px 3px;border-left:1px solid #DDE5EB;'>"+primLR+"</div>";
    s += "<div style='flex:1;display:flex;justify-content:flex-start;gap:3px;padding:6px 3px 6px 6px;'>"+primLL+"</div>";
    s += "</div>";

    s += "<div style='display:flex;gap:6px;margin:6px 0;'>"+halfBtn("q3","پایین چپ")+halfBtn("q4","پایین راست")+"</div>";
    s += fullArchBtn("bot");
    s += buildLegend()+buildSummary();
    s += "</div>";
    return s;
}

function buildFrame(ur,ul,ll,lr){
    var s="<div style='direction:rtl;font-family:Tahoma,sans-serif;font-size:12px;'>";
    s+=fullArchBtn("top");
    s+="<div style='display:flex;gap:6px;margin:6px 0;'>"+halfBtn("q2","بالا چپ")+halfBtn("q1","بالا راست")+"</div>";
    s+="<div style='display:flex;border:1.5px solid #DDE5EB;border-radius:10px 10px 0 0;overflow:hidden;background:#fff;'>";
    s+="<div style='flex:1;display:flex;justify-content:flex-end;gap:3px;padding:8px 6px 8px 3px;border-left:1.5px solid #DDE5EB;'>"+ur+"</div>";
    s+="<div style='flex:1;display:flex;justify-content:flex-start;gap:3px;padding:8px 3px 8px 6px;'>"+ul+"</div>";
    s+="</div>";
    s+="<div style='height:5px;background:linear-gradient(90deg,#f5f5f5,#e0e0e0,#f5f5f5);border-right:1.5px solid #DDE5EB;border-left:1.5px solid #DDE5EB;'></div>";
    s+="<div style='display:flex;border:1.5px solid #DDE5EB;border-radius:0 0 10px 10px;border-top:none;overflow:hidden;background:#fff;'>";
    s+="<div style='flex:1;display:flex;justify-content:flex-end;gap:3px;padding:8px 6px 8px 3px;border-left:1.5px solid #DDE5EB;'>"+lr+"</div>";
    s+="<div style='flex:1;display:flex;justify-content:flex-start;gap:3px;padding:8px 3px 8px 6px;'>"+ll+"</div>";
    s+="</div>";
    s+="<div style='display:flex;gap:6px;margin:6px 0;'>"+halfBtn("q3","پایین چپ")+halfBtn("q4","پایین راست")+"</div>";
    s+=fullArchBtn("bot");
    s+=buildLegend()+buildSummary();
    s+="</div>";
    return s;
}

// ─── سلول دندان دائمی ─────────────────────────────────────────
function tCell(fdi,n,type,pos){
    var key=fdi+"_"+type;
    var d=state.conditions[key];
    var tx=d?(d.treatments||[]):[];
    var code=getAutoColor(tx);
    var col=COLORS[code]||COLORS.healthy;
    var cnt=tx.length;
    // برخی خدمات (عکس، مشاوره و ...) رنگ اختصاصی در چارت ندارند —
    // بدون این نشانگر، دندانی که این خدمات برایش ثبت شده کاملاً سالم به نظر می‌رسد.
    var unclassified = (code==="healthy" && cnt>0);
    var boxBorder = unclassified ? "#7A96A4" : col.stroke;
    var boxBg     = unclassified ? "#EEF2F5" : col.fill;
    var boxStyle  = unclassified ? "dashed" : "solid";
    var showBadge = cnt>1 || unclassified;
    var badgeColor= unclassified ? "#7A96A4" : "#E05252";
    return "<div class='dc-tooth' data-fdi='"+fdi+"' data-type='"+type+"' "+
        "style='display:flex;flex-direction:column;align-items:center;cursor:pointer;width:32px;user-select:none;'"+
        (unclassified?" title='خدمتی بدون رنگ اختصاصی برای این دندان ثبت شده'":"")+">"+
        "<div style='font-size:9px;color:#7A96A4;height:13px;line-height:13px;font-weight:600;text-align:center;'>"+n+"</div>"+
        "<div style='width:28px;height:40px;border:2px "+boxStyle+" "+boxBorder+";border-radius:5px;background:"+boxBg+";"+
        "display:flex;align-items:center;justify-content:center;position:relative;transition:all .15s;'>"+
        (showBadge?"<span style='position:absolute;top:-5px;right:-5px;background:"+badgeColor+";color:#fff;border-radius:50%;width:14px;height:14px;font-size:8px;font-weight:700;display:flex;align-items:center;justify-content:center;'>"+cnt+"</span>":"")+
        "</div>"+
        "<div style='font-size:9px;color:#7A96A4;height:13px;line-height:13px;font-weight:600;text-align:center;'>"+n+"</div>"+
    "</div>";
}

// ─── سلول دندان شیری ──────────────────────────────────────────
function primCell(fdi,label,pos){
    var key=fdi+"_primary";
    var d=state.conditions[key];
    var tx=d?(d.treatments||[]):[];
    var code=getAutoColor(tx);
    var col=COLORS[code]||COLORS.healthy;
    var cnt=tx.length;
    var unclassified = (code==="healthy" && cnt>0);
    var boxBorder = unclassified ? "#7A96A4" : col.stroke;
    var boxBg     = unclassified ? "#EEF2F5" : col.fill;
    var boxStyle  = unclassified ? "dashed" : "solid";
    var showBadge = cnt>1 || unclassified;
    var badgeColor= unclassified ? "#7A96A4" : "#E05252";
    return "<div class='dc-tooth' data-fdi='"+fdi+"' data-type='primary' "+
        "style='display:flex;flex-direction:column;align-items:center;cursor:pointer;width:32px;user-select:none;'"+
        (unclassified?" title='خدمتی بدون رنگ اختصاصی برای این دندان ثبت شده'":"")+">"+
        "<div style='width:28px;height:34px;border:2px "+boxStyle+" "+boxBorder+";border-radius:5px;background:"+boxBg+";"+
        "display:flex;align-items:center;justify-content:center;position:relative;transition:all .15s;font-size:12px;font-weight:700;color:#1A6B8A;'>"+
        label+
        (showBadge?"<span style='position:absolute;top:-4px;right:-4px;background:"+badgeColor+";color:#fff;border-radius:50%;width:13px;height:13px;font-size:7px;font-weight:700;display:flex;align-items:center;justify-content:center;'>"+cnt+"</span>":"")+
        "</div>"+
    "</div>";
}

function fullArchBtn(pos){
    return "<div class='dc-full-btn' style='background:#F0F7FA;border:1.5px dashed #A0C4D8;border-radius:8px;padding:7px 12px;cursor:pointer;font-size:12px;color:#1A6B8A;text-align:center;margin:"+(pos==="top"?"8px 0 0 0":"0 0 8px 0")+";font-family:Tahoma;'>"+
        "🦷 کل دهان — کلیک برای خدمات عمومی</div>";
}
function halfBtn(q,label){
    var active=state.halfarch[q]&&Object.keys(state.halfarch[q]).some(function(k){return state.halfarch[q][k];});
    return "<div class='dc-half-btn' data-half='"+q+"' style='flex:1;background:#F8FAFB;border:1px solid "+(active?"#1A6B8A":"#DDE5EB")+";border-radius:6px;padding:6px 8px;cursor:pointer;font-size:11px;color:"+(active?"#1A6B8A":"#5A7080")+";text-align:center;font-family:Tahoma;'>"+label+(active?" ✓":"")+"</div>";
}
function buildLegend(){
    var shown={};
    var h="<div style='display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;padding-top:8px;border-top:1px solid #EEF2F5;'>";
    for(var k in COLORS){
        if(shown[COLORS[k].label]) continue;
        shown[COLORS[k].label]=true;
        var c=COLORS[k];
        h+="<div style='display:flex;align-items:center;gap:4px;font-size:11px;color:#5A7080;'>"+
           "<div style='width:11px;height:11px;border-radius:3px;background:"+c.fill+";border:1.5px solid "+c.stroke+";flex-shrink:0;'></div>"+c.label+"</div>";
    }
    return h+"</div>";
}
function buildSummary(){
    var counts={};
    for(var k in state.conditions){
        var d=state.conditions[k];
        if(!d||!d.treatments||!d.treatments.length) continue;
        d.treatments.forEach(function(c){counts[c]=(counts[c]||0)+1;});
    }
    if(!Object.keys(counts).length) return "";
    var h="<div style='display:flex;flex-wrap:wrap;gap:5px;margin-top:8px;'>";
    for(var code in counts){
        var t=TOOTH_TX.find(function(x){return x.code===code;});
        h+="<div style='background:#EEF2F5;border-radius:12px;padding:2px 9px;font-size:11px;color:#1A2733;font-family:Tahoma;'>"+(t?t.label:code)+": <b>"+counts[code]+"</b></div>";
    }
    return h+"</div>";
}

// ─── Events ───────────────────────────────────────────────────
function bindEvents(){
    document.querySelectorAll(".dc-tooth").forEach(function(el){
        el.addEventListener("mouseenter",function(){
            var box=this.querySelector("div[style*='height:40px'],div[style*='height:34px']");
            if(box) box.style.transform="scale(1.1)";
        });
        el.addEventListener("mouseleave",function(){
            var box=this.querySelector("div[style*='height:40px'],div[style*='height:34px']");
            if(box) box.style.transform="scale(1)";
        });
        el.addEventListener("click",function(){
            openToothModal(parseInt(this.dataset.fdi),this.dataset.type);
        });
    });
    document.querySelectorAll(".dc-full-btn").forEach(function(el){
        el.addEventListener("click",openFullArchModal);
    });
    document.querySelectorAll(".dc-half-btn").forEach(function(el){
        el.addEventListener("click",function(){openHalfModal(this.dataset.half);});
    });
}

// ─── مودال دندان ──────────────────────────────────────────────
function openToothModal(fdi,type){
    var key=fdi+"_"+type;
    var d=state.conditions[key]||{};
    var tx=d.treatments||[];
    var note=d.notes||"";
    var isPrim=type==="primary";
    var isPeds=state.mode==="peds";
    var q=Math.floor(fdi/10);
    var n=fdi%10;
    var qn={1:"بالا راست",2:"بالا چپ",3:"پایین چپ",4:"پایین راست",5:"شیری بالا راست",6:"شیری بالا چپ",7:"شیری پایین چپ",8:"شیری پایین راست"}[q]||q;
    var title="دندان "+n+" — "+qn+(isPrim?" (شیری)":" (دائمی)");

    var filtered=TOOTH_TX.filter(function(t){return isPrim?t.peds:t.adult;});

    // ─── نمایش خلاصه‌ی مسیر درمان (اگه این دندون داشته باشه) — فقط
    // خواندنی، از همون داده‌ای که از اول با config اومده، بدون AJAX ──
    var pwBody = "";
    var pwList = (cfg.pathways && cfg.pathways[fdi]) ? cfg.pathways[fdi] : null;
    if (pwList && pwList.length) {
        var pwIcons = {pending:"○",scheduled:"📅",in_progress:"🔄",completed:"✓",skipped:"⏭",cancelled:"✕",blocked:"🔒"};
        pwList.forEach(function(pw){
            pwBody += "<div style='background:#F0F6F9;border-radius:8px;padding:8px 10px;margin-bottom:8px;font-size:12px;'>"+
                "<b style='color:#1A6B8A;'>🛤️ "+pw.title+"</b> — "+pw.remaining+" مرحله باقی‌مانده<br>";
            pw.steps.forEach(function(s){
                pwBody += "<span style='color:#5A7080;'>"+(pwIcons[s.status]||"•")+" "+s.title+"</span> ";
            });
            pwBody += "</div>";
        });
    }
    var groups=[
        {label:"درمان‌ها",  codes:["composite","amalgam","rct","pulpotomy","pulpectomy","buildup","crown","veneer","inlay_onlay","extraction","surgical_ext","implant","apicoectomy","bridge_abutment","retainer_fix"]},
        {label:"تشخیصی",   codes:["suspect_endo","suspect_resto","resto_control"]},
        {label:"رادیوگرافی",codes:["xray_pa","cbct","xray_occlusal","xray_lat_ceph","xray_tmj"]},
        {label:"مشاوره",    codes:["consult_perio","consult_endo","consult_surgeon","consult_prosth","consult_resto","consult_peds","consult_diag","consult_general"]},
        {label:"سایر",      codes:["specialist_tx"]},
    ];

    var body="";
    body += pwBody;
    groups.forEach(function(g){
        var items=filtered.filter(function(t){return g.codes.indexOf(t.code)>-1;});
        if(!items.length) return;
        body+="<div style='margin-bottom:12px;'><div style='font-size:12px;font-weight:700;color:#1A6B8A;margin-bottom:6px;padding-bottom:4px;border-bottom:1px solid #EEF2F5;'>"+g.label+"</div>"+
            "<div style='display:grid;grid-template-columns:1fr 1fr;gap:5px;'>";
        items.forEach(function(t){
            var on=tx.indexOf(t.code)>-1;
            body+="<label style='display:flex;align-items:center;gap:6px;padding:6px 8px;border-radius:6px;border:1.5px solid "+(on?"#1A6B8A":"#E0E0E0")+";background:"+(on?"#E8F4F8":"#fff")+";cursor:pointer;font-size:12px;'>"+
                "<input type='checkbox' name='dc_tx' value='"+t.code+"' "+(on?"checked":"")+"> "+t.label+"</label>";
        });
        body+="</div></div>";
    });

    body+="<div><label style='font-size:12px;font-weight:600;color:#3D5460;display:block;margin-bottom:5px;'>یادداشت</label>"+
        "<textarea id='dc_note' style='width:100%;height:60px;border:1px solid #C8D4DC;border-radius:6px;padding:7px;font-family:Tahoma;font-size:12px;resize:vertical;box-sizing:border-box;direction:rtl;'>"+note+"</textarea></div>";

    showModal(title, body, function(){
        if(!cfg.canEdit){alert("مجاز به ویرایش نیستید.");closeModal();return;}
        var txEls=document.querySelectorAll("input[name='dc_tx']:checked");
        var txList=Array.from(txEls).map(function(e){return e.value;});
        var noteVal=document.getElementById("dc_note").value.trim();
        post("/chart/save",{
            patient_id:state.patientId,tooth_number:fdi,tooth_type:type,
            treatments:txList,notes:noteVal,exam_doctor_id:state.examDoctor,is_erupted:1
        },function(r){
            if(r&&r.success){
                state.conditions[key]={treatments:txList,notes:noteVal,exam_doctor_id:state.examDoctor,done_map:(d.done_map||{}),done_doctor_map:(d.done_doctor_map||{})};
                closeModal();render();renderPlan();
            } else alert((r&&r.message)||"خطا در ذخیره");
        });
    });
}

function openHalfModal(half){
    var cur=state.halfarch[half]||{};
    var names={q1:"بالا راست",q2:"بالا چپ",q3:"پایین چپ",q4:"پایین راست"};
    var body="<div style='display:grid;grid-template-columns:1fr 1fr;gap:8px;'>";
    HALFARCH_SVC.forEach(function(s){
        var on=!!cur[s.code];
        body+="<label style='display:flex;align-items:center;gap:6px;padding:8px;border:1.5px solid "+(on?"#9B7FD4":"#E0E0E0")+";border-radius:8px;cursor:pointer;background:"+(on?"#EDE7F6":"#fff")+";font-size:12px;'>"+
            "<input type='checkbox' name='dc_half' value='"+s.code+"' "+(on?"checked":"")+"> "+s.label+"</label>";
    });
    body+="</div>";
    showModal("نیم‌فک "+(names[half]||half),body,function(){
        if(!state.halfarch[half]) state.halfarch[half]={};
        document.querySelectorAll("input[name='dc_half']").forEach(function(cb){state.halfarch[half][cb.value]=cb.checked;});
        closeModal();renderPlan();
        // ─── طبق پیشنهاد کاربر: به‌جای endpoint جدا (که با وجود همه‌
        // تلاش‌ها مشکل‌دار موند)، از همون /chart/save مطمئن استفاده
        // می‌کنیم — با tooth_number = کد کوادرانت (۱ تا ۴، دقیقاً همون
        // کدگذاری‌ای که از قبل جای دیگه‌ی پلاگین برای نیم‌فک استفاده
        // می‌شد) و tooth_type='halfarch' برای تشخیصش از دندان واقعی.
        var quadCode = {q1:1,q2:2,q3:3,q4:4}[half] || 0;
        var activeCodes = Object.keys(state.halfarch[half]).filter(function(k){return state.halfarch[half][k];});
        post("/chart/save",{
            patient_id: state.patientId, tooth_number: quadCode, tooth_type: "halfarch",
            display_color: activeCodes.length ? "treatment" : "healthy",
            treatments: activeCodes, notes: "", exam_doctor_id: state.examDoctor, is_erupted: 1
        },function(r){
            if(!r||!r.success){ alert("⚠️ ذخیره نشد: "+((r&&r.message)||"خطای ناشناخته")); }
        });
    });
}

function openFullArchModal(){
    var cur=state.fullarch||{};
    var body="<div style='display:grid;grid-template-columns:1fr 1fr;gap:8px;'>";
    FULLARCH_SVC.forEach(function(s){
        var on=!!cur[s.code];
        body+="<label style='display:flex;align-items:center;gap:6px;padding:8px;border:1.5px solid "+(on?"#1A6B8A":"#E0E0E0")+";border-radius:8px;cursor:pointer;background:"+(on?"#E8F4F8":"#fff")+";font-size:12px;'>"+
            "<input type='checkbox' name='dc_full' value='"+s.code+"' "+(on?"checked":"")+"> "+s.label+"</label>";
    });
    body+="</div>";
    showModal("خدمات کل دهان",body,function(){
        FULLARCH_SVC.forEach(function(s){
            var cb=document.querySelector("input[name='dc_full'][value='"+s.code+"']");
            state.fullarch[s.code]=cb&&cb.checked;
        });
        closeModal();renderPlan();
        // ─── همون الگو — tooth_number=99 یعنی «کل دهان» (کد ویژه‌ی
        // جدید، چون ۹۱/۹۲ از قبل برای «کل فک بالا/پایین» جدا رزرو شده) ──
        var activeCodes = Object.keys(state.fullarch).filter(function(k){return state.fullarch[k];});
        post("/chart/save",{
            patient_id: state.patientId, tooth_number: 99, tooth_type: "fullarch",
            display_color: activeCodes.length ? "treatment" : "healthy",
            treatments: activeCodes, notes: "", exam_doctor_id: state.examDoctor, is_erupted: 1
        },function(r){
            if(!r||!r.success){ alert("⚠️ ذخیره نشد: "+((r&&r.message)||"خطای ناشناخته")); }
        });
    });
}

// ─── طرح درمان ────────────────────────────────────────────────
function renderPlan(){
    var el=document.getElementById("dc-plan-list");
    if(!el) return;
    var rows=[];

    for(var key in state.conditions){
        var d=state.conditions[key];
        if(!d||!d.treatments||!d.treatments.length) continue;
        var parts=key.split("_");
        var fdi=parseInt(parts[0]);
        var type=parts[1];
        var q=Math.floor(fdi/10);
        var n=fdi%10;
        var qn={1:"بالا راست",2:"بالا چپ",3:"پایین چپ",4:"پایین راست",5:"شیری بالا راست",6:"شیری بالا چپ",7:"شیری پایین چپ",8:"شیری پایین راست"}[q]||q;
        rows.push({key:key,title:"دندان "+n+" — "+qn+(type==="primary"?" (شیری)":""),treatments:d.treatments,notes:d.notes||"",exam_doc:d.exam_doctor_id||0,done_map:d.done_map||{},done_doc_map:d.done_doctor_map||{},_tx:TOOTH_TX});
    }

    var halfNames={q1:"بالا راست",q2:"بالا چپ",q3:"پایین چپ",q4:"پایین راست"};
    for(var half in state.halfarch){
        var active=HALFARCH_SVC.filter(function(s){return state.halfarch[half][s.code];});
        if(!active.length) continue;
        var labels={};
        active.forEach(function(s){labels[s.code]=s.label;});
        rows.push({key:"half_"+half,title:"نیم‌فک "+halfNames[half],treatments:active.map(function(s){return s.code;}),notes:"",exam_doc:0,done_map:{},done_doc_map:{},_labels:labels});
    }

    var fullActive=FULLARCH_SVC.filter(function(s){return state.fullarch[s.code];});
    if(fullActive.length){
        var fl={};
        fullActive.forEach(function(s){fl[s.code]=s.label;});
        rows.push({key:"fullarch",title:"کل دهان",treatments:fullActive.map(function(s){return s.code;}),notes:"",exam_doc:0,done_map:{},done_doc_map:{},_labels:fl});
    }

    if(!rows.length){
        el.innerHTML="<p style='color:#A0B4C0;font-size:12px;text-align:center;padding:20px 0;'>پس از معاینه و ثبت وضعیت دندان‌ها، طرح درمان اینجا نمایش داده می‌شود.</p>";
        return;
    }

    el.innerHTML=rows.map(function(row){
        var txHTML=row.treatments.map(function(code){
            var label=row._labels?(row._labels[code]||code):((row._tx||TOOTH_TX).find(function(t){return t.code===code;})||{label:code}).label;
            var done=!!(row.done_map[code]);
            var dDoc=row.done_doc_map[code]||"";
            var examName=getDoctorName(row.exam_doc);
            var docSel="";
            if(cfg.canEdit&&state.doctors.length){
                docSel="<select onchange=\"DentalChart.setDoneDoc('"+row.key+"','"+code+"',this.value)\" style='font-size:10px;border:1px solid #DDE5EB;border-radius:4px;padding:1px 4px;font-family:Tahoma;color:#5A7080;max-width:110px;'>"+
                    "<option value=''>پزشک درمان</option>"+
                    state.doctors.map(function(dr){return "<option value='"+dr.id+"' "+(dDoc==dr.id?"selected":"")+">"+dr.name+"</option>";}).join("")+"</select>";
            }
            return "<div style='display:flex;align-items:center;gap:6px;padding:5px 0;border-bottom:1px solid #F5F5F5;'>"+
                (cfg.canEdit
                    ?"<input type='checkbox' "+(done?"checked":"")+" onchange=\"DentalChart.toggleTx('"+row.key+"','"+code+"',this.checked)\" style='width:14px;height:14px;accent-color:#2ECC9A;flex-shrink:0;'>"
                    :"<span style='width:14px;height:14px;border-radius:3px;border:1px solid #C8D4DC;display:flex;align-items:center;justify-content:center;font-size:10px;flex-shrink:0;'>"+(done?"✓":"")+"</span>"
                )+
                "<span style='font-size:12px;"+(done?"text-decoration:line-through;color:#A0B4C0;":"color:#1A2733;font-weight:500;")+"'>"+label+"</span>"+
                "<span style='margin-right:auto;font-size:10px;color:#7A96A4;'>"+(row.exam_doc?"معاینه: "+examName:"")+"</span>"+
                docSel+
            "</div>";
        }).join("");
        return "<div style='padding:10px 0;border-bottom:1.5px solid #EEF2F5;'>"+
            "<div style='font-size:13px;font-weight:700;color:#1A6B8A;margin-bottom:4px;'>"+row.title+"</div>"+
            (row.notes?"<div style='font-size:11px;color:#7A96A4;margin-bottom:6px;'>"+row.notes+"</div>":"")+
            txHTML+"</div>";
    }).join("");
}

function getDoctorName(id){
    if(!id) return "";
    var d=state.doctors.find(function(x){return String(x.id)===String(id);});
    return d?d.name:"";
}

window.DentalChart={
    init:init,
    setMode:function(mode){
        state.mode=mode;
        document.querySelectorAll(".dc-mode-btn").forEach(function(b){
            var a=b.dataset.mode===mode;
            b.style.background=a?"#1A6B8A":"#F0F4F6";
            b.style.color=a?"#fff":"#5A7080";
        });
        post("/chart/set-mode",{patient_id:state.patientId,mode:mode},function(){});
        render();
    },
    toggleTx:function(key,code,done){
        if(!state.conditions[key]) return;
        var dm=state.conditions[key].done_map||{};
        dm[code]=done;
        state.conditions[key].done_map=dm;
        post("/chart/toggle-done",{patient_id:state.patientId,key:key,code:code,done:done?1:0,done_doctor_id:0},function(){});
        renderPlan();
    },
    setDoneDoc:function(key,code,docId){
        if(!state.conditions[key]) return;
        (state.conditions[key].done_doctor_map=state.conditions[key].done_doctor_map||{})[code]=docId;
    },
    closeModal:closeModal,
};

function showModal(title,body,onSave){
    var m=document.getElementById("dc-modal");
    if(!m) return;
    document.getElementById("dc-modal-title").textContent=title;
    document.getElementById("dc-modal-body").innerHTML=body;
    document.getElementById("dc-modal-save").onclick=onSave;
    m.style.display="flex";
    setTimeout(function(){
        document.querySelectorAll("input[name='dc_tx']").forEach(function(cb){
            cb.addEventListener("change",function(){
                var lbl=this.closest("label");
                if(lbl){lbl.style.borderColor=this.checked?"#1A6B8A":"#E0E0E0";lbl.style.background=this.checked?"#E8F4F8":"#fff";}
            });
        });
    },50);
}
function closeModal(){var m=document.getElementById("dc-modal");if(m)m.style.display="none";}
function post(path,data,cb){
    var x=new XMLHttpRequest();
    x.open("POST",cfg.apiBase+path);
    x.setRequestHeader("Content-Type","application/json");
    x.setRequestHeader("X-WP-Nonce",cfg.nonce);
    x.onload=function(){try{cb(JSON.parse(x.responseText));}catch(e){cb({success:false});}};
    x.onerror=function(){cb({success:false});};
    x.send(JSON.stringify(data));
}

document.addEventListener("DOMContentLoaded",function(){
    if(typeof dentalChartConfig!=="undefined") window.DentalChart.init(dentalChartConfig);
    var m=document.getElementById("dc-modal");
    if(m) m.addEventListener("click",function(e){if(e.target===m)closeModal();});
});
})();
