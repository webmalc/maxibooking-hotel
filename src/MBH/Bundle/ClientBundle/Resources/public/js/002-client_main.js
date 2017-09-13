/*global window, $, document */
$(document).ready(function () {
    'use strict';
    //spinner
    $('#mbh_bundle_clientbundle_client_config_type_searchDates, #mbh_bundle_clientbundle_client_config_type_defaultAdultsQuantity, #mbh_bundle_clientbundle_client_config_type_defaultChildrenQuantity').TouchSpin({min: 0, max: 10, step: 1});
    $('#mbh_bundle_clientbundle_client_config_type_searchTariffs').TouchSpin({min: 0, max: 99900, step: 1});
    $('#mbh_bundle_clientbundle_client_config_type_numberOfDaysForPayment').TouchSpin({min: 0, max: 100, step: 1});
    $('#mbh_bundle_clientbundle_client_config_type_currencyRatioFix').TouchSpin({min: 0, max: 3, step: 0.001, decimals: 3});
});