var docReadyCash = function () {

    var $groupPaidDate = $('#mbh_bundle_cash_cash_document_paid_date').closest('.form-group'),
        checkDisplayPaidDate = function() {
            this.checked ? $groupPaidDate.show(400) : $groupPaidDate.hide(400);
        },
        $isPaidCheckbox = $('#mbh_bundle_cash_cash_document_isPaid');

    $isPaidCheckbox
        .on('switchChange.bootstrapSwitch', checkDisplayPaidDate);

    checkDisplayPaidDate.call($isPaidCheckbox[0]);

    var $payerSelect = $('#mbh_bundle_cash_cash_document_payer_select'),
        $organizationPayerInput = $('#mbh_bundle_cash_cash_document_organizationPayer'),
        $touristPayerInput = $('#mbh_bundle_cash_cash_document_touristPayer');

    new mbh.payerSelect($payerSelect, $organizationPayerInput, $touristPayerInput);
};

(function ($) {
    docReadyCash();
})(window.jQuery);