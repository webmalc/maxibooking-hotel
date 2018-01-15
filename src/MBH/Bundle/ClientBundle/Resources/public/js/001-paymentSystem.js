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
    var isUnitellerSpecFieldsVisible = $unitellerFiscalizationFieldsSwitcher.bootstrapSwitch('state')
        && $('#mbh_bundle_clientbundle_client_payment_system_type_paymentSystem').val() === 'uniteller';
    setUnitellerSpecialFieldsVisibility(isUnitellerSpecFieldsVisible);
    $unitellerFiscalizationFieldsSwitcher.on('switchChange.bootstrapSwitch', function (event, state) {
        setUnitellerSpecialFieldsVisibility(state);
    });

    function setUnitellerSpecialFieldsVisibility(isVisible) {
        var $fields = $('#mbh_bundle_clientbundle_client_payment_system_type_taxationRateCode, #mbh_bundle_clientbundle_client_payment_system_type_taxationSystemCode');
        var $fieldsFormGroups = $fields.closest('.form-group');
        isVisible ? $fieldsFormGroups.show() : $fieldsFormGroups.hide();
    }

    var $paymentUrlsModal = $('#payment-urls-form-modal');
    var $paymentUrlsBodyModal = $('#payment-urls-form-modal-body');
    $('#change-payment-urls-button').click(function () {
        $.ajax({
            url: Routing.generate('client_payment_urls'),
            success: function (response) {
                $paymentUrlsBodyModal.html(response);
                $paymentUrlsModal.modal('show');
            }
        });
    });

    $('#save-config-urls-button').click(function () {
        var data = $('form[name="mbhclient_bundle_payment_systems_urls_type"]').serialize();
        var successUrl = $('#mbhclient_bundle_payment_systems_urls_type_successUrl').val();
        var failUrl = $('#mbhclient_bundle_payment_systems_urls_type_failUrl').val();
        $paymentUrlsBodyModal.html(mbh.loader.html);

        $.ajax({
            url: Routing.generate('client_save_payment_urls'),
            method: 'POST',
            data: data,
            success: function (response) {
                if (response.success) {
                    $('#success-payment-url').html(successUrl);
                    $('#fail-payment-url').html(failUrl);

                    $paymentUrlsModal.modal('hide');
                } else {
                    $paymentUrlsBodyModal.html(response.form);
                }
            }
        });
    });
});