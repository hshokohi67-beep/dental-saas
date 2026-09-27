/* dental datepicker init — لود بعد از persian-datepicker.bundle */
(function($){
    function dcInitDP(ctx) {
        $(ctx || document).find(
            'input[name="patient_dob"], input[name="start_date_jalali"], ' +
            'input[name="appt_date_jalali"], input[name="blocked_date_jalali"], ' +
            '#inp-start, #bk-date, #bk-date-input, .dc-datepicker'
        ).each(function(){
            var el = $(this);
            if (el.data('dp-init')) return;
            el.data('dp-init', true);

            if (typeof el.persianDatepicker !== 'function') return;

            el.persianDatepicker({
                format: 'YYYY/MM/DD',
                initialValueType: 'persian',
                calendar: { persian: { locale: 'fa' } },
                autoClose: true,
                onSelect: function(unix) {
                    var d = new persianDate(unix);
                    var v = d.year() + '/' +
                            String(d.month()).padStart(2,'0') + '/' +
                            String(d.date()).padStart(2,'0');
                    el.val(v).trigger('input').trigger('change');
                    if (typeof calcPreview === 'function') calcPreview();
                    if (typeof loadSlots === 'function') loadSlots();
                }
            });
        });
    }

    $(document).ready(function(){
        dcInitDP(document);

        // برای تب‌هایی که بعداً render میشن
        if (typeof MutationObserver !== 'undefined') {
            new MutationObserver(function(ms){
                ms.forEach(function(m){
                    m.addedNodes.forEach(function(n){
                        if (n.nodeType === 1) dcInitDP(n);
                    });
                });
            }).observe(document.body, { childList: true, subtree: true });
        }
    });

})(jQuery);
