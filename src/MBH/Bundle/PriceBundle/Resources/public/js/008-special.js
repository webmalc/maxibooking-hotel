/*global window, $, document, mbh, Routing, deleteLink */
$(document).ready(function () {
    'use strict';

    var specialFilterForm = $('#special-filter-form'),
        specialTable = $('#special-table'),
        process = false;

    specialTable.dataTable({
        "processing": true,
        "serverSide": true,
        "ordering": false,
        "drawCallback": function() {
            process = false;
            deleteLink();
        },
        "ajax": {
            "method": "POST",
            "url": Routing.generate('special'),
            "data": function (requestData) {
                process = true;
                requestData.form = specialFilterForm.serializeObject();
                return requestData;
            }
        }
    });

    specialFilterForm.find('input, select').on('change switchChange.bootstrapSwitch', function () {
        if (!process) {
            specialTable.dataTable().fnDraw();
        }
    });
});