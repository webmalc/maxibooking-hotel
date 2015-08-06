/*global window, document, $, mbh */
$(document).ready(function () {
    'use strict';

    (function () {

        var icon = $('#mbh_bundle_pricebundle_service_type_price').next('span.bootstrap-touchspin-postfix'),
            calcType = $('#mbh_bundle_pricebundle_service_type_calcType'),
            iconChange = function () {
                if (calcType.val() === 'day_percent') {
                    icon.html('%');
                } else {
                    icon.html('<i class="' + mbh.currency.icon + '"></i>');
                }
            };

        iconChange();
        calcType.change(iconChange);
    }());

    var counter = 1;
    //roomType rooms datatables
    $('.service-tables').each(function () {

        window.addExcelButtons($(this).dataTable(), counter);
        ++counter;
    });
    $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
        var counter = 1;
        $('.service-tables').each(function () {
            $(this).dataTable().fnDestroy();
            window.addExcelButtons($(this).dataTable(), counter);
            ++counter;
        });
    });
});