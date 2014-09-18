/*global window */
$(document).ready(function() {
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

})