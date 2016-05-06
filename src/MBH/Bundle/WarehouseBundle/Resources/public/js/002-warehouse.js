/* 
 * Record filter scripts (main page)
 */

var docReadyRecords = function () {
    'use strict';

    var $recordsForm = $('#records-filter-form');
    var $recordsTable = $('#records-table');
    var $operation = $recordsForm.find('#mbh_warehousebundle_recordfiltertype_operation');
	
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
                    recordDateFrom: $recordsForm.find('#mbh_warehousebundle_recordfiltertype_recordDateFrom').val(),
                    recordDateTo: $recordsForm.find('#mbh_warehousebundle_recordfiltertype_recordDateTo').val(),
                    operation: $operation.val(),
                    hotel: $recordsForm.find('#mbh_warehousebundle_recordfiltertype_hotel').val(),
                    wareItem: $recordsForm.find('#mbh_warehousebundle_recordfiltertype_wareItem').val(),
					_token: $recordsForm.find('#mbh_warehousebundle_recordfiltertype__token').val()
                };
				
                return requestData;
            }
        }
    });
	
    $recordsTable.dataTable().fnSetFilteringDelay();

    $recordsForm.find('input, select').on('change', function () {
        $recordsTable.dataTable().fnDraw();
    });

}

/*global document, window, Routing, $ */
$(document).ready(function () {
    'use strict';

    docReadyRecords();
});


