/*global window, document, $, Routing, console */
$(document).ready(function ($) {
    'use strict';
    var $fillingForm = $('#filling-table-filter'),
        $filling = $('#filling-table');

    var loadHtml = '<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>';
    $fillingForm.find('input, select').on('change', function () {
        if(!inProcess) {
            $filling.html(loadHtml);
            updateRoomTypesForm($fillingForm.serializeObject());
        }
    });

    var inProcess = false;
    var updateRoomTypesForm = function (data) {
        inProcess = true;
        $.ajax({
            url: Routing.generate('report_filling_table'),
            data: data,
            success: function (response) {
                $filling.html(response);
                $fillingForm.find('[data-toggle=popover]').popover();
                inProcess = false;
            }
        });
    }
});