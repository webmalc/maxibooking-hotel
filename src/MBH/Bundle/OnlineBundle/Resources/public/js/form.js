/*global window, $, document */
$(document).ready(function () {
    'use strict';

    // roomTypeChoices
    (function (){
        var roomTypes = $('#mbh_bundle_onlinebundle_form_type_roomTypes');
        var choices = $('#mbh_bundle_onlinebundle_form_type_roomTypeChoices')
            .closest('div.form-group');
        var toggleChoices = function () {
            choices.toggle(roomTypes.is(':checked'));
        };
        toggleChoices();
        roomTypes.on('change switchChange.bootstrapSwitch', toggleChoices);
    }());
});
