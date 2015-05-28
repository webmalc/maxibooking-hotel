/*global window, $, services, document */
$(document).ready(function () {
    "use strict";

    // package service form
    (function () {
        if (!$('#mbh_bundle_packagebundle_package_service_type_nights').length) {
            return;
        }
        var priceInput = $('#mbh_bundle_packagebundle_package_service_type_price'),
            nightsInput = $('#mbh_bundle_packagebundle_package_service_type_nights'),
            nightsDiv = nightsInput.closest('div.form-group'),
            personsInput = $('#mbh_bundle_packagebundle_package_service_type_persons'),
            personsDiv = personsInput.closest('div.form-group'),
            dateInput = $('#mbh_bundle_packagebundle_package_service_type_begin'),
            dateDiv = dateInput.closest('div.form-group'),
            dateDefault = dateInput.val(),
            serviceInput = $('#mbh_bundle_packagebundle_package_service_type_service'),
            serviceHelp = serviceInput.next('span.help-block'),
            amountInput = $('#mbh_bundle_packagebundle_package_service_type_amount'),
            amountHelp = amountInput.closest('div.input-group').next('span.help-block'),
            timeInput = $('#mbh_bundle_packagebundle_package_service_type_time_time'),
            timeDiv = timeInput.closest('div.form-group'),

            hide = function () {
                nightsDiv.hide();
                personsDiv.hide();
                dateDiv.hide();
                timeDiv.hide();
                dateInput.val(dateDefault);
                personsInput.val(1);
                nightsInput.val(1);
                amountHelp.html('');
                amountInput.val(1);
                serviceHelp.html('<small>Услуга для добавления к броне</small>');
            },

            calc = function () {

                var info = services[serviceInput.val()];

                amountHelp.html('');
                if (serviceInput.val() !== null && typeof info !== 'undefined') {
                    var nights = nightsInput.val(),
                        price = priceInput.val() * amountInput.val() * nights * personsInput.val();
                    amountHelp.html($.number(price, 2) + ' руб. за ' + amountInput.val() + ' шт.');
                }
            },

            show = function (info) {
                hide();
                if (info.calcType === 'per_night' || info.calcType === 'per_stay') {
                    personsInput.val(services.package_guests);
                    personsDiv.show();
                }
                if (info.calcType === 'per_night') {
                    nightsInput.val(services.package_duration);
                    nightsDiv.show();
                }
                priceInput.show();
                if (info.date) {
                    dateDiv.show();
                }
                if (info.time) {
                    timeDiv.show();
                }

                var peoplesStr = (info.calcType === 'per_night' || info.calcType === 'per_stay') ? ' за 1 человека ' : ' ';
                serviceHelp.html($.number(info.price, 2) + ' рублей' + peoplesStr + info.calcTypeStr);
                calc();
            },
            hideShow = function () {
                if (serviceInput.val() !== null) {
                    var info = services[serviceInput.val()];
                    if (typeof info === 'undefined') {
                        return;
                    }
                    priceInput.val(info.price);
                    show(info);
                } else {
                    hide();
                }
            };
        timeInput.timepicker({
            showMeridian: false,
            defaultTime: '00:00'
        });
        nightsDiv.change(calc);
        personsDiv.change(calc);
        amountInput.change(calc);
        serviceInput.change(calc);
        priceInput.change(calc);
        serviceInput.change(hideShow);
        hideShow();
    }());
});