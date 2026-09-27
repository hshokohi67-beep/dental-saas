(function($){
"use strict";

// ─── init Lucide ──────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function(){
    if(typeof lucide !== "undefined") lucide.createIcons();
});

// ─── init Persian Datepicker در پنل بیمار ─────────────────────
function dcPortalInitDP(context) {
    if(typeof jQuery === "undefined" || typeof jQuery.fn.persianDatepicker === "undefined") return;
    var ctx = context || document;
    jQuery(ctx).find(".dc-datepicker, input[name=appt_date_jalali], #bk-date-input").each(function(){
        var el = jQuery(this);
        if(el.data("dp-init")) return;
        el.data("dp-init", true);
        el.persianDatepicker({
            format: "YYYY/MM/DD",
            initialValueType: "persian",
            calendar: { persian: { locale: "fa" } },
            autoClose: true,
            onSelect: function(unix) {
                var d = new persianDate(unix);
                var val = d.year() + "/" + String(d.month()).padStart(2,"0") + "/" + String(d.date()).padStart(2,"0");
                el.val(val);
                el.trigger("input").trigger("change");
            }
        });
    });
}

$(document).ready(function(){
    dcPortalInitDP(document);
    // MutationObserver برای محتوای دینامیک
    if(typeof MutationObserver !== "undefined") {
        new MutationObserver(function(ms){
            ms.forEach(function(m){
                m.addedNodes.forEach(function(n){
                    if(n.nodeType===1) dcPortalInitDP(n);
                });
            });
        }).observe(document.body, {childList:true, subtree:true});
    }
});

// ─── DentalPortal namespace ───────────────────────────────────
window.DentalPortal = window.DentalPortal || {};

})(jQuery);
