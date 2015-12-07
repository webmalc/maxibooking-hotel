var docReadyCash = function () {
    var $paidDate = $('#mbh_bundle_cash_cash_document_paid_date');

    $('#mbh_bundle_cash_cash_document_document_date').add($paidDate).datepicker({
        language: "ru",
        autoclose: true,
        startView: 2,
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
    var $bothPayerInputs = $organizationPayerInput.add($touristPayerInput);


    if ($organizationPayerInput.val()) {
        $payerSelect.val('org_' + $organizationPayerInput.val());
    } else if ($touristPayerInput.val()) {
        $payerSelect.val('tourist_' + $touristPayerInput.val());
    }
    //$payerSelect.select2("updateResults");

    /*
     var formatResult = function(text){
     console.log(text)
     return '<i class="fa fa-male"></i> ' + text
     }
     $payerSelect.select2({"templateSelection": formatResult});*/

    function updateOrganizationPayerInput(type, value) {
        if (type === 'org'){
            $organizationPayerInput.val(value);
        } else if (type === 'tourist') {
            $touristPayerInput.val(value);
        } else {
            //throw new Error("");
        }
    }

    $payerSelect.on('change', function () {
        /** @type String */
        var value = $(this).val();
        $bothPayerInputs.val(null);
        if (value) {
            value = value.split('_');
            updateOrganizationPayerInput(value[0], value[1]);
        }
    });

    if ($payerSelect.val()) {
        var value = $payerSelect.val().split('_');
        updateOrganizationPayerInput(value[0], value[1]);
    };
};

/**
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
(function ($) {
    docReadyCash();
})(window.jQuery)