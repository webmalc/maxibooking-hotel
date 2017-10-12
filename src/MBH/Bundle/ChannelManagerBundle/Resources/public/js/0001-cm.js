/*global window, $, services, document, datepicker, deleteLink, Routing */
$(document).ready(function () {
    'use strict';

    $('.ratio-spinner').TouchSpin({
        min: 0,
        max: 9999999999999999,
        step: 0.01,
        decimals: 2,
        boostat: 10,
        maxboostedstep: 20,
        postfix: '%'
    });
    $('.price-spinner').TouchSpin({
        min: 0,
        max: 9999999999999999,
        step: 0.1,
        decimals: 2,
        boostat: 10,
        maxboostedstep: 20
    });
    $('.days-spinner').TouchSpin({
        min: 0,
        max: 9999999999999999,
        step: 1,
        decimals: 0,
        boostat: 10,
        maxboostedstep: 20
    });

    (function () {
        var currencyInput = $('select.currency-input'),
            defaultCurrencyInput = $('input.currency-default-ratio-input'),
            defaultCurrencyInputWrapper = defaultCurrencyInput.closest('.form-group'),
            show = function () {
                if (!currencyInput.length) {
                    return;
                }
                if (currencyInput.val()) {
                    defaultCurrencyInputWrapper.show();
                } else {
                    defaultCurrencyInputWrapper.hide();
                }
            };
        show();
        currencyInput.change(show);
    }());

});

