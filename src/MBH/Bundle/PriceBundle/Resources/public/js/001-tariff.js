/*global window, $, document, mbh */
$(document).ready(function () {
    'use strict';

    //spinners
    $('.price-spinner').TouchSpin({
        min: 0,
        max: 9007199254740992,
        //boostat: 50,
        stepinterval: 50,
        decimals: 2,
        step: 0.1,
        maxboostedstep: 10000000,
        postfix: '<i class="' + mbh.currency.icon + '"></i>'
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
        //decimals: 2,
        stepinterval: 50,
        maxboostedstep: 10000000
    });
    $('.spinner-0f').TouchSpin({
        min: 0,
        max: 9007199254740992,
        step: 0.1,
        //boostat: 50,
        decimals: 2,
        stepinterval: 50,
        maxboostedstep: 10000000
    });
    $('.spinner-1').TouchSpin({
        min: 1,
        max: 9007199254740992,
        step: 1,
        //boostat: 50,
        //decimals: 2,
        stepinterval: 50,
        maxboostedstep: 10000000
    });
    $('.spinner--1').TouchSpin({
        min: -1,
        max: 9007199254740992,
        step: 1,
        //boostat: 50,
        //decimals: 2,
        stepinterval: 50,
        maxboostedstep: 10000000
    });
    $('.spinner--1f').TouchSpin({
        min: -1,
        max: 9007199254740992,
        step: 0.1,
        //boostat: 50,
        decimals: 2,
        stepinterval: 50,
        maxboostedstep: 10000000
    });

    var $promotionsGroup = $('#mbh_price_tariff_promotions_promotions').closest('.form-group');
    var $defaultPromotionInput = $('#mbh_price_tariff_promotions_defaultPromotion');
    $defaultPromotionInput.on('change', function() {
        switchPromotions();
    });

    var switchPromotions = function() {
        if($defaultPromotionInput.val()) {
            $promotionsGroup.hide();
        } else {
            $promotionsGroup.show();
        }
    }

    switchPromotions();


    // tariff service
    var $addServiceButton = $('.default-service a');
    var prototype = $addServiceButton.data('prototype');
    var $servicesList = $('.default-service ul');
    var serviceIndex = $servicesList.find('li').length;
    prototype = '<li>'+prototype+'</li>';

    $servicesList.on('click', '.fa-times', function () {
        $(this).closest('li').remove();
    });

    $addServiceButton.on('click', function(e){
        var newPrototype = prototype.replace(/__name__/g, serviceIndex);
        e.preventDefault();
        var $prototype = $(newPrototype);
        $servicesList.append($prototype);
        $prototype.find('select').select2();
        ++serviceIndex;
    });
});