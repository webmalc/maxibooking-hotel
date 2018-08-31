/*global window, $, document */
$(document).ready(function() {
    'use strict';
    //payment system form
    (function() {
        var select = $('#mbh_bundle_clientbundle_client_payment_system_type_paymentSystem'),
            showHideFields = function() {
                $('.paymentSystem').hide().find('[data-required]').removeAttr('required');
                $('.paymentSystem[data-name="' + select.val() + '"]').
                    show().
                    find('[data-required]').
                    attr('required', 'required');
            };

        if (!select.length) {
            return;
        }
        showHideFields();
        select.change(showHideFields);

        var changeRequired = function(element, state) {
            var elem = $(element);
            if (state) {
                elem.attr('required', 'required');
            } else {
                elem.removeAttr('required');
            }
        };
        var changeVisible = function(element, state) {
            if (state) {
                $(element).show(400);
            } else {
                $(element).hide(400);
            }
        };

        var changeSelectFiscalization = function(element, state) {
            $(element).closest('.paymentSystem').find('.select_tax_code').each(function() {
                changeRequired(this, state);
            }).closest('.form-group').each(function() {
                changeVisible(this, state)
            });
        };

        var $fiscalizationFieldsSwitcher = $('.checkboxForIsWithFiscalization');

        $fiscalizationFieldsSwitcher.each(function() {
            var state = $(this).bootstrapSwitch('state');
            changeRequired(this, state);
            changeSelectFiscalization(this, state);
        });

        $fiscalizationFieldsSwitcher.on('switchChange.bootstrapSwitch', function(event, state) {
            changeRequired(this, state);
            changeSelectFiscalization(this, state);
        });

    }());

    var $paymentUrlsModal = $('#payment-urls-form-modal');
    var $paymentUrlsBodyModal = $('#payment-urls-form-modal-body');
    $('#change-payment-urls-button').click(function() {
        $.ajax({
            url    : Routing.generate('client_payment_urls'),
            success: function(response) {
                $paymentUrlsBodyModal.html(response);
                $paymentUrlsModal.modal('show');
            }
        });
    });

    $('#save-config-urls-button').click(function() {
        var data = $('form[name="mbhclient_bundle_payment_systems_urls_type"]').serialize();
        var successUrl = $('#mbhclient_bundle_payment_systems_urls_type_successUrl').val();
        var failUrl = $('#mbhclient_bundle_payment_systems_urls_type_failUrl').val();
        $paymentUrlsBodyModal.html(mbh.loader.html);

        $.ajax({
            url    : Routing.generate('client_save_payment_urls'),
            method : 'POST',
            data   : data,
            success: function(response) {
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