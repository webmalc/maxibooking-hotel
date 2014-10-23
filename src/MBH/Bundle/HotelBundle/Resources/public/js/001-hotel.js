/*global window */
$(document).ready(function() {
    'use strict';

    //spinners
    $('#mbh_bundle_hotelbundle_hoteltype_saleDays').TouchSpin({
        min: 1,
        max: 365,
        step: 1,
        boostat: 5,
        maxboostedstep: 10
    });
    $('.spinner').TouchSpin({
        min: 0,
        max: 9007199254740992,
        step: 1,
        boostat: 5,
        maxboostedstep: 10
    });
});

