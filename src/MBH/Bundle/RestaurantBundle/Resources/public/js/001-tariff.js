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
});
