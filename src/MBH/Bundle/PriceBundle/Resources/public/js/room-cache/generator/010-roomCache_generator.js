/*global window, $, document, Routing, mbhGridCopy*/
var mbh_restrictForDateRangePicker = true;
$(document).ready(function () {
    'use strict';

    //generator
    (function () {
        var rooms = $('input.delete-rooms'),
            quotas = $('#mbh_bundle_pricebundle_room_cache_generator_type_quotas'),
            showMessage = function () {
                rooms.each(function () {
                    var text = parseInt($(this).val(), 10) === -1 ? Translator.trans("004-roomCache.days_will_be_removed") : '';
                    $(this).closest('.col-sm-6').
                        next('.col-sm-4').
                        html('<span class="text-danger text-left input-errors">' + text +  '</span>');
                });
            },
            tariffs = $('#mbh_bundle_pricebundle_room_cache_generator_type_tariffs'),
            showTariffs = function () {
                var tariffsDiv = tariffs.closest('div.form-group');
                tariffsDiv.toggle(quotas.prop('checked'));
            },
            divForIsOpen = $('#mbh_bundle_pricebundle_room_cache_generator_type_isOpen').closest('div.form-group'),
            hideDivForIsOpen = function(animation) {
                divForIsOpen.hide(animation !== false ? 200 : null);
            },
            showDivIsOpen = function() {
                divForIsOpen.show(200);
            },
            searchKey = function(value) {
                if (mbh_tariffNotOpened[value]) {
                    return true;
                }
            },
            showIsOpen = function (animation) {
                var selected = $(tariffs).val();

                if (selected === null) {
                    hideDivForIsOpen(animation);
                    return;
                }

                if (quotas.prop('checked') && selected.some(searchKey)) {
                    showDivIsOpen();
                }
            };

        showTariffs();
        showMessage();
        showIsOpen(false);
        rooms.change(showMessage);
        quotas.on('change switchChange.bootstrapSwitch', showTariffs);
        tariffs.on('change', showIsOpen);
        setGeneratorData();
    }());
});
