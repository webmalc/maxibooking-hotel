$(function () {
    $('.fix-percent-spinner').TouchSpin({
        min: 0,
        max: 100,
        step: 1,
        //boostat: 50,
        stepinterval: 50,
        maxboostedstep: 10000000,
        postfix: '%'
    });
    $('.price-spinner').TouchSpin({
        min: 0,
        max: 9007199254740992,
        //boostat: 50,
        stepinterval: 50,
        decimals: 2,
        step: 0.1,
        maxboostedstep: 10000000,
        postfix: '<i class="' + mbh.currency.icon + '"></i>'
    });
    amountSpin($('.amount'));
    $(document).on('prototypeAdded', function(event, prototype) {
        amountSpin($(prototype).find('.amount'));
    });

    function amountSpin($amount) {
        $amount.TouchSpin({
            min: 0,
            max: 9007199254740992,
            //boostat: 50,
            stepinterval: 50,
            decimals: 2,
            step: 0.1,
            maxboostedstep: 10000000
            // postfix: '<i class="' + mbh.currency.icon + '"></i>'
        });
    }
});
