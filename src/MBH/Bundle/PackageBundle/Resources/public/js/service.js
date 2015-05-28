/**
 * @author Aleksandr Arofikin
 */
/*global window, $, document, Routing*/
/*jslint regexp: true */
$(document).ready(function () {
    'use strict';
    var $serviceFilterForm = $('#service-filter'),
        $serviceTable = $('#service-table'),
        processing = false;
    $serviceTable.dataTable({
        "processing": true,
        "serverSide": true,
        "searching": false,
        "ordering": true,
        "autoWidth": false,
        "ajax": {
            "url": Routing.generate('ajax_service_list'),
            "data": function (d) {
                d = $.extend(d, $serviceFilterForm.serializeObject());
                d.services = $('#select-services').select2('val');
            },
            beforeSend: function () {processing = true; }
        },
        "aoColumns": [
            {"name": "icon", "bSortable": false},
            {"name": "order"},
            null,
            {"name": "title", "bSortable": false},
            null,
            null,
            null,
            null,
            null,
            {"name": "calc_type", "bSortable": false},
            {"name": "total", "bSortable": false},
            {"name": "note", "bSortable": false},
            {"name": "createAt", "bSortable": true}
        ],
        "fnDrawCallback" : function () {
            processing = false;
            var $markDeleted = $serviceTable.find('.mark-deleted');
            $markDeleted.closest('tr').addClass('danger');
        }
    }).fnDraw();

    $serviceFilterForm.find('input,select').on('change switchChange.bootstrapSwitch', function () {
        if (!processing) {
            $serviceTable.dataTable().fnDraw();
        }
    });
})