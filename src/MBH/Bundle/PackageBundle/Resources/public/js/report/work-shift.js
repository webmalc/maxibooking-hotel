/*global window, document, $, Routing, console */
$(document).ready(function ($) {
    'use strict';

    var $workShiftReportForm = $('#work-shift-report-form');
    if($workShiftReportForm.length == 0) {
        return;
    }

    var $workShiftReportWrapper = $('#work-shift-report-table-wrapper'),
        $workShiftReportActions = $('#work-shift-report-actions'),
        updateTableUrl = Routing.generate('report_work_shift_table');

    var updateTable = function() {
        var requestData = $workShiftReportForm.serializeObject();
        $.ajax(updateTableUrl, {
            data: requestData,
            success: function(response) {
                $workShiftReportActions.html(response.actions);
                $workShiftReportWrapper.html(response.result)
            }
        });
    };

    var updateTableById = function(id) {
        $.ajax(updateTableUrl, {
            data: {id: id},
            success: function(response){
                $workShiftReportWrapper.html(response.result)
            }
        });
    }

    $workShiftReportForm.find('#form_date').on('changeDate', updateTable);
    $workShiftReportForm.find('#form_user').on('change', updateTable);

    updateTable();

    $workShiftReportActions.on('click', 'a', function(e) {
        e.preventDefault();
        var $this = $(this);
        if ($this.hasClass('active')) {
            return;
        }
        $workShiftReportActions.find('a').removeClass('active');
        $this.addClass('active');
        var id = $this.data('id');
        updateTableById(id);
    })
});