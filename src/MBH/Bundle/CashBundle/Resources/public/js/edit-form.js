/* global $, document, window */
$(document).on('ready', function() {
    var $touristPayer = $('#mbh_bundle_cash_cash_document_touristPayer');
    if (!$touristPayer.is(':hidden')) {
        $touristPayer.mbhGuestSelectPlugin();
    }
    var $organizationPayer = $('#mbh_bundle_cash_cash_document_organizationPayer');
    if (!$organizationPayer.is(':hidden')) {
        $organizationPayer.mbhGuestSelectPlugin();
    }

    var $payerSelect = $('#mbh_bundle_cash_cash_document_payer_select');
    var $organizationPayerInput = $('#mbh_bundle_cash_cash_document_organizationPayer');
    var $touristPayerInput = $('#mbh_bundle_cash_cash_document_touristPayer');


    new mbh.payerSelect($payerSelect, $organizationPayerInput, $touristPayerInput);
});