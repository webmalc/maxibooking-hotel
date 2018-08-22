/*global window, document, $, Routing, console */
$(document).ready(function ($) {
    'use strict';
    var $fillingForm = $('#filling-table-filter'),
        $fillingTableWrapper = $('#filling-table-wrapper');

    $('#filling-filter-button').click(function () {
        if(!inProcess) {
            $fillingTableWrapper.html(mbh.loader.html);
            var filterData = $fillingForm.serializeObject();
            filterData['isEnabled'] = $fillingForm.find('#filling-report-filter-isEnabled').bootstrapSwitch('state');
            filterData['recalculate-accommodation'] = $fillingForm.find('#filling-report-filter-recalculate-accommodation').bootstrapSwitch('state');
            updateRoomTypesForm(filterData);
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