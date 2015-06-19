/*global window, document, $, Routing, console */
$(document).ready(function () {
    'use strict';
    var $taskTable = $('#task-table'),
        $taskTableFilterForm = $('#task-table-filter');
    var processing = false;
    $taskTable.dataTable({
        "processing": true,
        "serverSide": true,
        "ordering": true,
        "searching": false,
        //"paging": false,
        //'lengthChange' : false,
        //"pageLength": 10,
        "ajax": {
            "url": Routing.generate('task_json'),
            "beforeSend" : function () {
                processing = true;
            },
            "data": function (data) {
                data.begin = $('#task-filter-begin').val();
                data.end = $('#task-filter-end').val();
                data.status = $('#task-filter-status').select2('val');
            }
        },
        "order": [[ 9, "desc" ]], //createdAt
        "aoColumns": [
            {"bSortable" : false},
            {"name" : "number", "class" : 'text-center'},
            {"name" : "status"},
            {"name" : "type", "bSortable" : false},
            {"name" : "priority"},
            {"bSortable" : false},
            {"class" : 'text-center', "bSortable" : false},
            {"bSortable" : false},
            {"bSortable" : false},
            {"name" : "createdAt"},
            {"name" : "updatedAt"},
            {"bSortable" : false}
        ],
        "drawCallback": function (settings) {
            processing = false;
        }
    });
    var $taskDate = $('#mbh_bundle_hotelbundle_task_date');
    if ($taskDate) {
        $taskDate.datepicker({
            language: "ru",
            autoclose: true,
            startView: 2,
            format: 'dd.mm.yyyy'
        });
    }

    $taskTableFilterForm.find('input, textarea, select').on('change', function () {
        if (!processing) {
            console.log("task");
            $('#task-table').dataTable().fnDraw();
        }
    });
});