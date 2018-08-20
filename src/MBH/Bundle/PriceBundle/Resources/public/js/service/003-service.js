/*global window, document, $, mbh */
$(document).ready(function () {
    'use strict';

    $('.price-spinner').TouchSpin({
        min: 0,
        max: 9007199254740992,
        //boostat: 50,
        stepinterval: 50,
        decimals: 2,
        step: 0.1,
        maxboostedstep: 10000000,
        postfix: '<i class="' + mbh.currency.icon + '"></i>'
    });

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
            var $recalcByGuests = $('.recalc-caused-by-guests').closest('div.form-group');
            if (type.val() === "per_night" || type.val() === "per_stay") {
                $recalcByGuests.show();
            } else {
                $recalcByGuests.hide();
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
    $('.service-tables').dataTable({
      language    : mbh.datatablesOptions.language,
      pageLength  : mbh.datatablesOptions.pageLength
    });
});
