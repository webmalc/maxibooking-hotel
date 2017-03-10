/*global window, document, $, Routing, console */
$(document).ready(function () {
    'use strict';
    var $taskTable = $('#task-table'),
        $taskTableFilterForm = $('#task-table-filter'),
        processing = false,
        columns = [
            {"name": "id", "class": "text-center"},
            {"name": "status", "class": "text-center"},
            {"name": "task", "bSortable": false},
            {"name": "priority", "class": "text-center"},
            {"name": "room", "bSortable": false},
            {"name": "assign", "bSortable": false},
            {"name": "period", "bSortable": false},
            {"name": "createdAt"},
            {"bSortable": false}
        ],
        ajax = {
            "url": Routing.generate("task_json"),
            "method": "POST",
            "beforeSend": function () {
                processing = true;
            },
            "data": function (data) {
                data = $.extend({}, data, $taskTableFilterForm.serializeObject());
                return data;
            }
        },
        customize = function ( win ) {
            var table = win.content[1].table.body;
            for(var i = 0; i<table.length; i++)
            {
                //getting date substring
                if (i>0) {
                    var dateString = table[i][7].text;
                    var index = dateString.indexOf('до');
                    table[i][7].text = dateString.substr(index,100);
                }
                //removal "Период" column
                table[i].splice(6, 1);
            }
        },
        buttons = [
        {
            extend: 'excel',
            text: '<i class="fa fa-table" title="Excel" data-toggle="tooltip" data-placement="bottom"></i>',
            className: 'btn btn-default btn-sm'
        },
        {
            extend: 'pdf',
            exportOptions: {
                stripNewlines: true
            },
            customize: customize,
            text: '<i class="fa fa-file-pdf-o" title="PDF" data-toggle="tooltip" data-placement="bottom"></i>',
            className: 'btn btn-default btn-sm'
        }],

        dataTableOptions = {
            dom: "12<'row'<'col-sm-6'Bl><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
            "processing": true,
            "serverSide": true,
            "ordering": true,
            "searching": false,
            "buttons": buttons,
            "ajax": ajax,
            "aoColumns": columns,
            "drawCallback": function (settings) {
                processing = false;
                $taskTable.find('tr a[data-row-class]').each(function () {
                    var $this = $(this),
                        rowClass = $this.data('row-class');

                    $this.closest('tr').addClass(rowClass);
                });
            },
            "order": [[7, "desc"]]
        };
    $taskTable.dataTable(dataTableOptions);


    var updateTaskTable = function () {
        if (!processing) {
            $('#task-table').dataTable().fnDraw();
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
        resetRoomsSelectHtml = $roomsSelect.html();

    //var firstCall = true;
    var changeHousingAndFloor = function (e) {
        if (!$roomsSelect.length) {
            return;
        }
        $roomsSelect.val('');
        /*if(!firstCall) {
         roomsSelectHtml = roomsSelectHtml.replace('selected="selected"', '');
         }*/
        var housing = $housingSelect.val(),
            floor = $floorSelect.val()
            //$roomsSelectHtml = $('<select>' + roomsSelectHtml + '</select>')
            ;

        /*$roomsSelectHtml.find('option').map(function() {
         var isChecked = (!housing|| this.getAttribute("data-housing") == housing)
         && (!floor || this.getAttribute("data-floor") == floor);
         if(!isChecked)
         $(this).remove();
         });
         var newHtml = $roomsSelectHtml.html();*/

        $roomsSelect.mbhSelect2OptionsFilter(function () {
            return (!housing || this.getAttribute("data-housing") == housing)
                && (!floor || this.getAttribute("data-floor") == floor);
        }, resetRoomsSelectHtml);

        //$roomsSelect.select2('destroy').html(newHtml).select2();

        //firstCall = false;
    };

    $housingSelect.on('change', changeHousingAndFloor);
    $floorSelect.on('change', changeHousingAndFloor);
    changeHousingAndFloor();
    $roomsSelect.parent().append(
        '<div class="btn-group pull-right" style="margin-top: 3px" role="group">' +
        '<div class="btn btn-xs btn-default clickable" id="select-all-rooms">Выбрать все</div>' +
        '<div class="btn btn-xs btn-default clickable" id="clear-rooms">Очистить</div>' +
        '<div>'
    );
    $('#select-all-rooms').on('click', function () {
        $roomsSelect.find("option").prop("selected", "selected");
        $roomsSelect.trigger("change");
    });
    $('#clear-rooms').on('click', function () {
        $roomsSelect.select2('val', null);
    });

    var $taskInfoModal = $('#task-info-modal'),
        $table = $taskInfoModal.find('.modal-body table'),
        $button = $taskInfoModal.find('#task-info-modal-action'),
        $ownedTaskTable = $('.owned-tasks-table'),
        $taskId = $taskInfoModal.find('.modal-title em');

    var showTaskModal = function (id) {
        $.ajax({
            url: Routing.generate('ajax_task_details', {id: id}),
            dataType: 'json',
            data: {id: id},
            success: function (response) {
                $taskId.html(response.id);
                $.each(response, function (k, v) {
                    $table.find('tr[data-property=' + k + '] td:nth-child(2)').html(v ? v : ' - ');
                });

                var status,
                    buttonClass,
                    buttonTitle;
                if (response.status === 'Открыта') {
                    status = 'process';
                    buttonClass = 'btn-primary';
                    buttonTitle = 'Взять в работу';
                } else {
                    status = 'closed';
                    buttonClass = 'btn-success';
                    buttonTitle = 'Завершить';
                }

                $button.removeClass('btn-primary btn-success').addClass(buttonClass);
                $button.attr('href', Routing.generate('task_change_status', {id: id, status: status}));
                $button.html(buttonTitle);

                $taskInfoModal.modal('show');
            }
        });
    };

    $ownedTaskTable.find('tr').on('click', function (e) {
        if (e.target.tagName != 'A' && $(e.target.tagName).closest('a').length == 0) {
            e.preventDefault();

            var id = $(this).data('id');
            showTaskModal(id);
        }
    });

    $ownedTaskTable.find('.show-task-details').on('click', function (e) {
        e.preventDefault();
        var id = $(this).closest('tr').data('id');
        showTaskModal(id);
    })
});


var $categoryInput = $('#mbh_bundle_hotelbundle_tasktype_category')
var href = $('#task-category-tabs li.active a').attr('href');
if (href) {
    $categoryInput.val(href.substring(1));
}
$('#task-category-tabs a').on('click', function () {
    var id = this.getAttribute('href').substring(1);
    $categoryInput.val(id);
});