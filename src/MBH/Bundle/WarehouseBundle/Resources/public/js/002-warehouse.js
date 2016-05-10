/* 
 * Record filter scripts (main page)
 */

var docReadyRecords = function () {
    'use strict';

    var $recordsTable = $('#records-table');
	
	$recordsTable.dataTable().fnDestroy();
	
    $recordsTable.dataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        ajax: {
            method: "POST",
            url: Routing.generate('records_json'),
            data: function (requestData) {
                requestData.form = {
                    recordDateFrom: $('#mbh_warehousebundle_recordfiltertype_recordDateFrom').val(),
                    recordDateTo: $('#mbh_warehousebundle_recordfiltertype_recordDateTo').val(),
                    operation: $('#mbh_warehousebundle_recordfiltertype_operation').val(),
                    hotel: $('#mbh_warehousebundle_recordfiltertype_hotel').val(),
                    wareItem: $('#mbh_warehousebundle_recordfiltertype_wareItem').val(),
					_token: $('#mbh_warehousebundle_recordfiltertype__token').val()
                };
				
                return requestData;
            },
        },
        "columnDefs": [
			{ "orderable": false, "targets": 0 },
			{ "orderable": false, "targets": 6 },
			{ "orderable": false, "targets": -1 }
		]
    });
	
    $recordsTable.dataTable().fnSetFilteringDelay();

    $('#records-filter-form').find('input, select').on('change', function () {
        $recordsTable.dataTable().fnDraw();
    });

};

var docReadyInventory = function () {
    'use strict';

    var $inventoryTable = $('#inventory-table');
	
	$inventoryTable.dataTable().fnDestroy();
	
    $inventoryTable.dataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        ajax: {
            method: "POST",
            url: Routing.generate('inventory_json'),
            data: function (requestData) {
                requestData.form = {
                    hotel: $('#mbh_warehousebundle_recordfiltertype_hotel').val(),
                    wareItem: $('#mbh_warehousebundle_recordfiltertype_wareItem').val(),
					_token: $('#mbh_warehousebundle_recordfiltertype__token').val()
                };
				
                return requestData;
            },
        },
        "columnDefs": [
			{ "orderable": false, "targets": 0 },
			{ "orderable": false, "targets": -1 }
		]
    });
	
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


