/*global window, $ */
$(document).ready(function() {
    'use strict';

    //show/hide tariff main type fields
    (function() {
        var isDefault = $('#mbh_bundle_pricebundle_tariff_main_type_isDefault'),
                typeSelect = $('#mbh_bundle_pricebundle_tariff_main_type_type'),
                type = typeSelect.closest('div.form-group'),
                permanent = $('#mbh_bundle_pricebundle_tariff_main_type_permanent'),
                begin = $('#mbh_bundle_pricebundle_tariff_main_type_begin'),
                end = $('#mbh_bundle_pricebundle_tariff_main_type_end'),
                rate = $('#mbh_bundle_pricebundle_tariff_main_type_rate').closest('div.form-group'),
                showHide = function() {
                    type.hide();
                    rate.hide();

                    if (permanent.is(':checked')) {
                        begin.closest('div.form-group').hide();
                        end.closest('div.form-group').hide();
                    }

                    permanent.on('switchChange', function() {
                        if (permanent.is(':checked')) {
                            begin.val('01.01.1970').closest('div.form-group').hide();
                            end.val('19.01.2038').closest('div.form-group').hide();
                        } else {
                            begin.val(null).closest('div.form-group').show();
                            end.val(null).closest('div.form-group').show();
                        }
                    });

                    if (isDefault.is(':checked')) {
                        type.hide();
                        rate.hide();
                    } else {
                        type.show();
                        if (typeSelect.val() === 'rate') { rate.show(); }
                    }
                    if (typeSelect.val() !== 'rate') {
                        rate.hide();
                    } else {
                        rate.show();
                    }

                    isDefault.on('switchChange', function() {
                        if (isDefault.is(':checked')) {
                            type.hide();
                            rate.hide();
                        } else {
                            type.show();
                            if (typeSelect.val() === 'rate') { rate.show(); }
                        }
                    });
                    typeSelect.change(function() {
                        if ($(this).val() !== 'rate') {
                            rate.hide();
                        } else {
                            rate.show();
                        }
                    });
                };

        showHide();
    }());

    //spinners
    $('#mbh_bundle_pricebundle_tariff_main_type_rate').TouchSpin({
        min: 0,
        max: 1000,
        step: 1,
        boostat: 10,
        maxboostedstep: 20,
        postfix: '%'
    });
    
    $('.price-spinner').TouchSpin({
        min: 0,
        max: 9007199254740992,
        step: 1,
        boostat: 5,
        maxboostedstep: 10,
        postfix: '<i class="fa fa-ruble"></i>'
    });

    $('.percent-spinner').TouchSpin({
        min: 0,
        max: 9007199254740992,
        step: 1,
        boostat: 5,
        maxboostedstep: 10,
        postfix: '%'
    });
    
    $('.quota-spinner').TouchSpin({
        min: 0,
        max: 9007199254740992,
        step: 1,
        boostat: 5,
        maxboostedstep: 10
    });

    $('.spinner').TouchSpin({
        min: 1,
        max: 9007199254740992,
        step: 1,
        boostat: 5,
        maxboostedstep: 10
    });
    
    //Tariffs datatable
    $('.tariffs-table').dataTable({
        "ordering": false
    });
});