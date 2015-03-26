/*global window, document, $ */
$(document).ready(function () {
    'use strict';

    (function () {

        var icon = $('#mbh_bundle_pricebundle_service_type_price').next('span.bootstrap-touchspin-postfix'),
            calcType = $('#mbh_bundle_pricebundle_service_type_calcType'),
            iconChange = function () {
                if (calcType.val() === 'day_percent') {
                    icon.html('%');
                } else {
                    icon.html('<i class="fa fa-ruble"></i>');
                }
            };

        iconChange();
        calcType.change(iconChange);
    }());
});