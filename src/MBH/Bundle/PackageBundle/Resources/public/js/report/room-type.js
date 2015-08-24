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

    var inProcess = false;
    var updateRoomTypesForm = function (data) {
        inProcess = true;
        $.ajax({
            url: Routing.generate('report_room_types_table'),
            data: data,
            success: function (response) {
                $roomTypes.html(response);
                inProcess = false;
            }
        });
    }
});