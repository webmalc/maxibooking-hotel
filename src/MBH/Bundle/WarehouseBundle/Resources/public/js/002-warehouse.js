/* 
 * Record filter scripts (main page)
 */

var docReadyRecords = function () {
    'use strict';

	if (! document.getElementById('records-table')) {
		return;
	}
	
    var $recordsTable = $('#records-table');
	
	var prefix = '#mbh_warehousebundle_recordfiltertype_';
	
    $recordsTable.dataTable($.extend(mbh.datatablesOptions, {
        processing: true,
        serverSide: true,
        ordering: true,
        ajax: {
            method: "POST",
            url: Routing.generate('records_json'),
            data: function (requestData) {
                requestData.form = {
                    recordDateFrom: $(prefix+'recordDateFrom').val(),
                    recordDateTo: $(prefix+'recordDateTo').val(),
                    operation: $(prefix+'operation').val(),
                    hotel: $(prefix+'hotel').val(),
                    wareItem: $(prefix+'wareItem').val(),
					_token: $(prefix+'_token').val()
                };
				
                return requestData;
            }
        },
        columnDefs: [
			{ orderable: false, targets: 0 },
			{ orderable: false, targets: 6 },
			{ orderable: false, targets: -1 },
		]
    }));
	
    $recordsTable.dataTable().fnSetFilteringDelay();

    $('#records-filter-form').find('input, select').on('change', function () {
        $recordsTable.dataTable().fnDraw();
    });

};

var docReadyInventory = function () {
    'use strict';

	if (! document.getElementById('inventory-table')) {
		return;
	}
	
    var $inventoryTable = $('#inventory-table');
	
	var prefix = '#mbh_warehousebundle_recordfiltertype_';
	
    $inventoryTable.dataTable($.extend(mbh.datatablesOptions, {
        processing: true,
        serverSide: true,
        ordering: true,
        ajax: {
            method: "POST",
            url: Routing.generate('inventory_json'),
            data: function (requestData) {
                requestData.form = {
                    hotel: $(prefix+'hotel').val(),
                    wareItem: $(prefix+'wareItem').val(),
					_token: $(prefix+'_token').val()
                };
				
                return requestData;
            }
        },
        columnDefs: [
			{ orderable: false, targets: 0 },
		]
    }));
	
    $inventoryTable.dataTable().fnSetFilteringDelay();

    $('#inventory-filter-form').find('input, select').on('change', function () {
        $inventoryTable.dataTable().fnDraw();
    });

};

/*global document, window, Routing, $ */
$(document).ready(function () {
    'use strict';

	docReadyRecords();
	docReadyInventory();	
});


