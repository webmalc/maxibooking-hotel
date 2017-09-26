/*global window, $, document */
$(document).ready(function () {
    'use strict';
    //payment system form
    (function () {
        var select = $('#mbh_bundle_clientbundle_client_payment_system_type_paymentSystem'),
            showHideFields = function () {
                $('.payment-system-params').closest('.form-group').hide();
                $('input.' + select.val() + ',select.' + select.val()).closest('.form-group').show();
            };

        if (!select.length) {
            return;
        }
        showHideFields();
        select.change(showHideFields);
    }());

    var $unitellerFiscalizationFieldsSwitcher = $('#mbh_bundle_clientbundle_client_payment_system_type_isUnitellerWithFiscalization');
    setUnitellerSpecialFieldsVisibility($unitellerFiscalizationFieldsSwitcher.bootstrapSwitch('state'));
    $unitellerFiscalizationFieldsSwitcher.on('switchChange.bootstrapSwitch', function (event, state) {
        setUnitellerSpecialFieldsVisibility(state);
    });

    function setUnitellerSpecialFieldsVisibility(isVisible) {
        var $fields = $('#mbh_bundle_clientbundle_client_payment_system_type_taxationRateCode, #mbh_bundle_clientbundle_client_payment_system_type_taxationSystemCode');
        var $fieldsFormGroups = $fields.closest('.form-group');
        isVisible ? $fieldsFormGroups.show() : $fieldsFormGroups.hide();
    }
});