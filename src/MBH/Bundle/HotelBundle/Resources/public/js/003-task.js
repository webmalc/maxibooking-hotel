/*global window, document, $, Routing, console */
$(document).ready(function () {
    'use strict';
    var $taskTable = $('#task-table'),
        $taskTableFilterForm = $('#task-table-filter'),
        processing = false,
        columns = [
            //{"bSortable" : false},
            {"name" : "number", "class" : 'text-center'},
            {"name" : "status", "class" : 'text-center'},
            {"name" : "task", "bSortable" : false},
            {"name" : "priority", "class" : 'text-center'},
            {"name" : "room", "bSortable" : false},
            {"name" : "createdAt"},
            //{"name" : "updatedAt"},
            {"bSortable" : false}
        ];

    var isAdmin = $taskTable.find('tr:first th').length == 8;
    if (isAdmin){
        columns.splice(5, 0, {"bSortable" : false});
    }
    var ajax = {
        "url": Routing.generate('task_json'),
        "beforeSend" : function () {
            processing = true;
        }
    };
    if (isAdmin) {
        ajax.data = function (data) {
            data.begin = $('#task-filter-begin').val();
            data.end = $('#task-filter-end').val();
            data.status = $('#task-filter-status').select2('val');
            data.priority = $('#task-filter-priority').select2('val');
            data.performer = $('#task-filter-performer').select2('val');
            data.group = $('#task-filter-group').select2('val');
            data.deleted = $('#task-filter-deleted').prop('checked');
        };
    }

    var dataTableOptions = {
        "processing": true,
        "serverSide": true,
        "ordering": true,
        "searching": false,
        //"paging": false,
        //'lengthChange' : false,
        //"pageLength": 10,
        "ajax": ajax,
        "aoColumns": columns,
        "drawCallback": function (settings) {
            processing = false;
            $taskTable.find('tr a[data-row-class]').each(function (){
                var $this = $(this),
                    rowClass = $this.data('row-class');

                $this.closest('tr').addClass(rowClass);
            });
        }
    };

    if (isAdmin) {
        dataTableOptions.order = [[ 6, "desc" ]];
    } else {
        dataTableOptions.order = [];//[[ 5, "desc" ]];
    }
    $taskTable.dataTable(dataTableOptions);


    var updateTaskTable = function () {
        if (!processing) {
            console.log("task");
            $taskTable.dataTable().fnDraw();
        }
    };
    $taskTableFilterForm.find('input[type!=checkbox], textarea, select').on('change', updateTaskTable);
    $taskTableFilterForm.find('input[type=checkbox]').on('switchChange.bootstrapSwitch', updateTaskTable);
    var $date = $('#mbh_bundle_hotelbundle_task_date_date'),
        $time = $('#mbh_bundle_hotelbundle_task_date_time'),
        datePickerOptions = {
            language: "ru",
            autoclose: true,
            format: 'dd.mm.yyyy'
        };
    $time.timepicker({showMeridian: false, defaultTime: ''});
    $date.datepicker(datePickerOptions);

    $date.on('change', function () {
        if ($date.val() && !$time.val()) {
            $time.val('0:00');
        }
    });

    var $housingSelect = $('#mbh_bundle_hotelbundle_task_housing'),
        $floorSelect = $('#mbh_bundle_hotelbundle_task_floor'),
        $roomsSelect = $('#mbh_bundle_hotelbundle_task_rooms'),
        roomsSelectHtml = $roomsSelect.html();

    var firstCall = true,
        changeHousingAndFloor = function (e) {
            $roomsSelect.val('');
            if(!firstCall) {
                roomsSelectHtml = roomsSelectHtml.replace('selected="selected"', '');
            }
            var housing = $housingSelect.val(),
                floor = $floorSelect.val(),
                $roomsSelectHtml = $('<select>' + roomsSelectHtml + '</select>');

            $roomsSelectHtml.find('option').map(function() {
                var isChecked = (!housing|| this.getAttribute("data-housing") == housing)
                    && (!floor || this.getAttribute("data-floor") == floor);
                if(!isChecked)
                    $(this).remove();
            });
            var newHtml = $roomsSelectHtml.html();
            $roomsSelect.select2('destroy').html(newHtml).select2();

            firstCall = false;
        };

    $housingSelect.on('change', changeHousingAndFloor);
    $floorSelect.on('change', changeHousingAndFloor);
    changeHousingAndFloor();
    $roomsSelect.parent().append(
        '<div class="btn-group pull-right" style="margin-top: 3px" role="group">' +
        '<div class="btn btn-xs btn-default clickable" id="select-all-rooms">Выбрать все</div>' +
        '<div class="btn btn-xs btn-default clickable" id="clear-rooms">Очистить</div>' +
        '<div>'
    )
    $('#select-all-rooms').on('click', function() {
        console.log('all');
        $roomsSelect.find("option").prop("selected","selected");
        $roomsSelect.trigger("change");
    })
    $('#clear-rooms').on('click', function() {
        console.log('clear');
        $roomsSelect.select2('val', null);
    })
});