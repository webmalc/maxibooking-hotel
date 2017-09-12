var docReadyCash = function () {
    var $paidDate = $('#mbh_bundle_cash_cash_document_paid_date');

    $('#mbh_bundle_cash_cash_document_document_date').add($paidDate).datepicker({
        language: "ru",
        autoclose: true,
        startView: 0,
        format: 'dd.mm.yyyy'
    });

    var $groupPaidDate = $paidDate.closest('.form-group');
    var checkDisplayPaidDate = function(){
        this.checked ? $groupPaidDate.show() : $groupPaidDate.hide();
    };

    var $isPaidCheckbox = $('#mbh_bundle_cash_cash_document_isPaid');

    $isPaidCheckbox
        .on('switchChange.bootstrapSwitch', checkDisplayPaidDate);
    //.on('init.bootstrapSwitch', checkDisplayPaidDate)


    checkDisplayPaidDate.call($isPaidCheckbox[0]);

    var $payerSelect = $('#mbh_bundle_cash_cash_document_payer_select');
    var $organizationPayerInput = $('#mbh_bundle_cash_cash_document_organizationPayer');
    var $touristPayerInput = $('#mbh_bundle_cash_cash_document_touristPayer');


    new mbh.payerSelect($payerSelect, $organizationPayerInput, $touristPayerInput);

    //$payerSelect.select2("updateResults");

    /*
     var formatResult = function(text){
     console.log(text)
     return '<i class="fa fa-male"></i> ' + text
     }
     $payerSelect.select2({"templateSelection": formatResult});*/

};

/**

 */
(function ($) {
    docReadyCash();
})(window.jQuery)