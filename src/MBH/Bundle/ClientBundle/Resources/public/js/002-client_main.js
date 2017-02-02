/*global window, $, document */
$(document).ready(function () {
    'use strict';
    //spinner

    $('#mbh_bundle_clientbundle_client_config_type_searchDates').TouchSpin({min: 0, max: 10, step: 1});
    $('#mbh_bundle_clientbundle_client_config_type_searchTariffs').TouchSpin({min: 0, max: 99900, step: 1});
});