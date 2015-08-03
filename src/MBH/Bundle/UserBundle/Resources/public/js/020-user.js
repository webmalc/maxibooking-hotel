/*global window, $, console, document */
$(document).ready(function () {
    'use strict';

    $('.password').pwstrength({
        ui: {
            showVerdictsInsideProgressBar: true,
            verdicts: ["Плохой", "Обычный", "Хороший", "Отличный", "Супер"],
        },
        common: {
            minChar: 8
        }
    });

    /*$('#mbh_bundle_userbundle_usertype_birthday').datepicker({
        startDate: new Date("1 January 1987"),
        autoclose: true
    });*/
})