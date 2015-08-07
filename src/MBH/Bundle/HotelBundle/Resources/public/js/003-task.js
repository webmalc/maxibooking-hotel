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
                data.priority = $('#task-filter-priority').select2('val');
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

    $taskTableFilterForm.find('input, textarea, select').on('change', function () {
        if (!processing) {
            console.log("task");
            $('#task-table').dataTable().fnDraw();
        }
    });
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

    var firstCall = true;
    var allOption = '<optgroup label="Все"><option value="all">Выбрать все</option></optgroup>',
        changeHousingAndFloor = function (e) {
            $roomsSelect.val('');
            if(!firstCall) {
                roomsSelectHtml = roomsSelectHtml.replace('selected="selected"', '');
            }
            var housing = $housingSelect.val(),
                floor = $floorSelect.val(),
                $roomsSelectHtml = $('<select>' + allOption + roomsSelectHtml + '</select>');

            $roomsSelectHtml.find('option').map(function() {
                var isChecked = (!housing|| this.getAttribute("data-housing") == housing)
                    && (!floor || this.getAttribute("data-floor") == floor) || this.value == 'all';
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

    $roomsSelect.append('<div id="hidden-inputs"></div>');


    var originalRoomsSelectName = $roomsSelect.attr('name');
    $roomsSelect.on('change', function() {
        $('#hidden-inputs').empty();
        $roomsSelect.attr('name', originalRoomsSelectName);
        if($(this).val() == 'all') {
            var inputs = '';
            $roomsSelect.find('option').each(function(){
                if(this.value != 'all')
                    inputs += '<input type="hidden" name="' + originalRoomsSelectName + '" value="'+ this.value +'">'
            });
            $roomsSelect.attr('name', 'fake');//.attr('disabled', 'disabled');
            $('#hidden-inputs').append(inputs);
        }
    });
});