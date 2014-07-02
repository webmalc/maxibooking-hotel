/*global window */
$(document).ready(function() {
    'use strict';

    //spinners
    $('#mbh_bundle_hotelbundle_hoteltype_saleDays').TouchSpin({
        min: 0,
        max: 365,
        step: 1,
        boostat: 5,
        maxboostedstep: 10,
    });
});

