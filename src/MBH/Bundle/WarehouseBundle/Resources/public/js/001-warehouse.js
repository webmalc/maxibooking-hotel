/*global window, $, document, mbh */
$(document).ready(function () {
    'use strict';

    //spinners
    $('.price-spinner').TouchSpin({
        min: 0,
        max: 9007199254740992,
        //boostat: 50,
        stepinterval: 50,
        decimals: 2,
        step: 0.5,
        maxboostedstep: 10000000,
        postfix: '<i class="' + mbh.currency.icon + '"></i>'
    });

    $('.spinner-0f').TouchSpin({
        min: 0,
        max: 9007199254740992,
        step: 0.5,
        //boostat: 50,
        decimals: 2,
        stepinterval: 50,
        maxboostedstep: 10000000
    });
	
	$('.table-actions').DataTable({
		retrieve: true,
		"columns": [
			{"orderable": false},
			null,
			null,
			null,
			{"orderable": false},
			{"orderable": false},
		]
	});
	
});
