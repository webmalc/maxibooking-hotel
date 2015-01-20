/*global window, $ */
$(document).ready(function() {
    'use strict';

    // show|hide robokassa
    (function(){
        var paymentTypes = $('#mbh_bundle_onlinebundle_form_type_paymentTypes'),
            fieldset = $('.paysystem-params').closest('fieldset'),
            toggle = function () {
                fieldset.hide();

                if (($.inArray('online_full', paymentTypes.val())) !== -1 || ($.inArray('online_first_day', paymentTypes.val()))  !== -1) {
                    fieldset.show();
                }
            }

        toggle();
        paymentTypes.change(function() {
            toggle();
        });
    }());
});