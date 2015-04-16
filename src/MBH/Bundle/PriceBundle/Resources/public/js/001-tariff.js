/*global window, $, document */
$(document).ready(function () {
    'use strict';

    //spinners
    $('.price-spinner').TouchSpin({
        min: 0,
        max: 9007199254740992,
        step: 1,
        //boostat: 50,
        stepinterval: 50,
        maxboostedstep: 10000000,
        postfix: '<i class="fa fa-ruble"></i>'
    });

    $('.percent-spinner').TouchSpin({
        min: 0,
        max: 9007199254740992,
        step: 1,
        //boostat: 50,
        stepinterval: 50,
        maxboostedstep: 10000000,
        postfix: '%'
    });

    $('.spinner').TouchSpin({
        min: 1,
        max: 9007199254740992,
        step: 1,
        //boostat: 50,
        stepinterval: 50,
        maxboostedstep: 10000000
    });
    $('.spinner-0').TouchSpin({
        min: 0,
        max: 9007199254740992,
        step: 1,
        //boostat: 50,
        stepinterval: 50,
        maxboostedstep: 10000000
    });
    $('.spinner-1').TouchSpin({
        min: 1,
        max: 9007199254740992,
        step: 1,
        //boostat: 50,
        stepinterval: 50,
        maxboostedstep: 10000000
    });
    $('.spinner--1').TouchSpin({
        min: -1,
        max: 9007199254740992,
        step: 1,
        //boostat: 50,
        stepinterval: 50,
        maxboostedstep: 10000000
    });
});