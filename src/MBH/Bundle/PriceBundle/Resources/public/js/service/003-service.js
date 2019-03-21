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

    var $includeAccPriceInput = $('#mbh_bundle_pricebundle_service_type_includeInAccommodationPrice');
    var $subtractAccPriceInput = $('#mbh_bundle_pricebundle_service_type_subtractFromAccommodationPrice');
    if ($includeAccPriceInput.length === 1) {
        var $includeAndSubtractInputs = $includeAccPriceInput.add($subtractAccPriceInput);
        $includeAndSubtractInputs.on('switchChange.bootstrapSwitch', function () {
            if ($includeAndSubtractInputs.bootstrapSwitch('state') === true) {
                $includeAndSubtractInputs.not($(this)).bootstrapSwitch('state', false);
            }
        });
    }

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
    $("ul.nav-tabs > li > a").on("shown.bs.tab", function() {
        window.location.hash = $(this).attr("href").substr(1);
    });
    $('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
    $('.are-you-sure').on("submit", function() {
        var form = $(this);
        form.attr('action', form.attr('action') + window.location.hash);
    });
});
