/*global window, document, $, Routing, console */
$(document).ready(function ($) {
    'use strict';
    var $fillingForm = $('#filling-table-filter'),
        $fillingTableWrapper = $('#filling-table-wrapper');

    $fillingForm.find('input, select').on('change', function () {
        if(!inProcess) {
            $fillingTableWrapper.html(mbh.loader.html);
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
                $fillingTableWrapper.html(response);
                $fillingForm.find('[data-toggle=popover]').popover();
                inProcess = false;
            }
        });
    }
});