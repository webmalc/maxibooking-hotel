/*global window, $, document */
$(document).ready(function () {
    'use strict';
    //spinner
    $('#mbh_bundle_clientbundle_client_config_type_searchDates, #mbh_bundle_clientbundle_client_config_type_defaultAdultsQuantity, #mbh_bundle_clientbundle_client_config_type_defaultChildrenQuantity').TouchSpin({min: 0, max: 10, step: 1});
    $('#mbh_bundle_clientbundle_client_config_type_searchTariffs').TouchSpin({min: 0, max: 99900, step: 1});
    $('#mbh_bundle_clientbundle_client_config_type_numberOfDaysForPayment').TouchSpin({min: 0, max: 100, step: 1});
    $('#mbh_bundle_clientbundle_client_config_type_currencyRatioFix').TouchSpin({min: 0, max: 3, step: 0.001, decimals: 3});

    var $beginDateInput = $('#mbh_bundle_clientbundle_client_config_type_beginDate');
    var $beginDateOffsetInput = $('#mbh_bundle_clientbundle_client_config_type_beginDateOffset');
    $beginDateOffsetInput.TouchSpin({min: -1000, max: 1000, step: 1});
    $beginDateInput.on('changeDate', function () {
        if ($beginDateInput.val()) {
            $beginDateOffsetInput.val('');
        }
    });
    $beginDateOffsetInput.change(function () {
        if ($beginDateOffsetInput.val()) {
            $beginDateInput.val('').datepicker('update');
        }
    });
});