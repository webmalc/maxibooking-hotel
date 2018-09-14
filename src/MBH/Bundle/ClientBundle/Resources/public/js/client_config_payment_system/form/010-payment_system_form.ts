declare let $;
'use strict';
window.addEventListener('load', function () {
    //payment system form
    (function() {
        let select = $('#mbh_bundle_clientbundle_client_payment_system_type_paymentSystem');

        if (select.length === 0) {
            return;
        }

        let showHideFields = function() {
            $('.paymentSystem')
                .hide()
                .find('[data-required]')
                .removeAttr('required');
            $('.paymentSystem[data-name="' + select.val() + '"]')
                .show()
                .find('[data-required]')
                .attr('required', 'required');
        };

        showHideFields();
        select.change(showHideFields);

        let changeRequired = function(element, state) {
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
});