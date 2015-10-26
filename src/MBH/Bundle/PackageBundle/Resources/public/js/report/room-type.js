/*global window, document, $, Routing, console */
$(document).ready(function ($) {
    'use strict';
    var $roomTypesForm = $('#room-types-table-filter'),
        $roomTypes = $('#room-types-table');

    var loadHtml = '<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>';
    $roomTypesForm.find('input, select').on('change', function () {
        if(!inProcess) {
            $roomTypes.html(loadHtml);
            updateRoomTypesForm($roomTypesForm.serializeObject());
        }
    });


    var selectStatusOptions = {
        templateResult: select2TemplateResult.icon,
        templateSelection: select2TemplateResult.icon,
        minimumResultsForSearch: -1
    };
    $roomTypes.find('select.plain-html.select-status').select2(selectStatusOptions);

    var inProcess = false;
    var updateRoomTypesForm = function (data) {
        inProcess = true;
        $.ajax({
            url: Routing.generate('report_room_types_table'),
            data: data,
            success: function (response) {
                $roomTypes.html(response);
                $roomTypes.find('select.plain-html.select-status').select2(selectStatusOptions);
                //$roomTypes.find('[data-toggle=popover]').popover();
                inProcess = false;
            }
        });
    }

    var setStatusUrl = Routing.generate('report_set_room_status');
    $roomTypes.on('change', 'select.plain-html.select-status', function(e) {
        var $this = $(this);
        var data = {
            value: $this.val(),
            roomID: $this.data('room')
        }

        $.ajax({url: setStatusUrl, data: data});
    })
});