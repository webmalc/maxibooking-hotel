/*global window, $, document, mbh */
function initDpSpEtc() {
	
    //spinners
    $('.price-spinner').TouchSpin({
        min: 0,
        max: 9007199254740992,
        stepinterval: 50,
		forcestepdivisibility: 'none',
        decimals: 2,
        step: 0.5,
        maxboostedstep: 10000000,
        postfix: '<i class="' + mbh.currency.icon + '"></i>'
    });

    $('.spinner-0f').TouchSpin({
        min: 0,
        max: 9007199254740992,
        step: 0.5,
        decimals: 2,
        stepinterval: 50,
		forcestepdivisibility: 'none',
        maxboostedstep: 10000000
    });
}

$(document).ready(function () {
    'use strict';
	
	initDpSpEtc();

	if (document.getElementById('warehouse-table')) {
		$('.warehouse-table').DataTable($.extend(mbh.datatablesOptions, {
			"columns": [
				{"orderable": false},
				null,
				null,
				null,
				{"orderable": false},
				{"orderable": false},
			]
		}));
	}
	
});
