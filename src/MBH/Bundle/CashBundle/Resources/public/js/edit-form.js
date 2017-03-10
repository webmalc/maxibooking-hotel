/* global $, document, window */
$(document).on('ready', function() {
    var $touristPayer = $('#mbh_bundle_cash_cash_document_touristPayer');
    var $organizationPayer = $('#mbh_bundle_cash_cash_document_organizationPayer');
    if (!$touristPayer.is(':hidden') && !$organizationPayer.is(':hidden')) {
        $touristPayer.mbhGuestSelectPlugin();
        $organizationPayer.mbhOrganizationSelectPlugin();
    } else {
        var $payerSelect = $('#mbh_bundle_cash_cash_document_payer_select');
        new mbh.payerSelect($payerSelect, $organizationPayer, $touristPayer);
    }
});