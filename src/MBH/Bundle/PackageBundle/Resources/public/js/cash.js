var docReadyCash = function() {
    var $paidDate = $('#mbh_bundle_cashbundle_cashdocumenttype_paid_date');

    $('#mbh_bundle_cashbundle_cashdocumenttype_document_date').add($paidDate).datepicker({
        language: "ru",
        autoclose: true,
        startView: 2,
        format: 'dd.mm.yyyy'
    });

    var $groupPaidDate = $paidDate.closest('.form-group');
    var checkDisplayPaidDate = function(){
        this.checked ? $groupPaidDate.show() : $groupPaidDate.hide();
    };

    var $isPaidCheckbox = $('#mbh_bundle_cashbundle_cashdocumenttype_isPaid');

    $isPaidCheckbox
        .on('switchChange.bootstrapSwitch', checkDisplayPaidDate);
    //.on('init.bootstrapSwitch', checkDisplayPaidDate)


    checkDisplayPaidDate.call($isPaidCheckbox[0])

    var $payerSelect = $('#mbh_bundle_cashbundle_cashdocumenttype_payer_select');
    var $organizationPayerInput = $('#mbh_bundle_cashbundle_cashdocumenttype_organizationPayer');
    var $touristPayerInput = $('#mbh_bundle_cashbundle_cashdocumenttype_touristPayer');
    var $bothPayerInputs = $organizationPayerInput.add($touristPayerInput);


    if($organizationPayerInput.val()){
        $payerSelect.val('org_' + $organizationPayerInput.val());
    } else if($touristPayerInput.val()){
        $payerSelect.val('tourist_' + $touristPayerInput.val());
    }
    //$payerSelect.select2("updateResults");

    /*
     var formatResult = function(text){
     console.log(text)
     return '<i class="fa fa-male"></i> ' + text
     }
     $payerSelect.select2({"templateSelection": formatResult});*/

    $payerSelect.on('change', function(){
        /** @type String */
        var value = $(this).val();
        $bothPayerInputs.val(null);
        if(value){
            value = value.split('_');
            if(value[0] == 'org')
                $organizationPayerInput.val(value[1])
            else if(value[0] == 'tourist')
                $touristPayerInput.val(value[1])
        }
    });
};

/**
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
(function($){
    docReadyCash();
})(window.jQuery)