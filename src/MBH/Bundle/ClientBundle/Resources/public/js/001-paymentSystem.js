/*global window, $, document */
$(document).ready(function () {
    'use strict';
    //payment system form
    (function () {
        var select = $('#mbh_bundle_clientbundle_client_payment_system_type_paymentSystem'),
            showHideFields = function () {
                $('.payment-system-params').closest('.form-group').hide();
                $('input.' + select.val() + ',select.' + select.val() + ', textarea.' + select.val()).closest('.form-group').show();
            };

        if (!select.length) {
            return;
        }
        showHideFields();
        select.change(showHideFields);
    }());
});