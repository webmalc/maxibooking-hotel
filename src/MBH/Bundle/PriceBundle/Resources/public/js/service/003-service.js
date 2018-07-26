/*global window, document, $, mbh */
$(document).ready(function () {
    'use strict';

    // show/hide dates fields
    (function () {
        var type = $('#mbh_bundle_pricebundle_service_type_calcType');
        var toggle = function () {
            var dates = $('.toggle-date').closest('div.form-group');
            if (type.val() === 'per_stay') {
               dates.show();
            } else {
               dates.hide();
            }
        };
        toggle();
        type.change(toggle);
    }());

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

    //roomType rooms datatables
    $('.service-tables').dataTable();
});
