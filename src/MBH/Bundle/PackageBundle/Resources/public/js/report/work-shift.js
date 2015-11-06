/*global window, document, $, Routing, console */
$(document).on('ready', function () {
    'use strict';

    var $workShiftReportForm = $('#work-shift-report-form');
    if($workShiftReportForm.length == 0) {
        return;
    }

    var $workShiftReportWrapper = $('#work-shift-report-table-wrapper'),
        $workShiftTableWrapper = $('#work-shift-table-wrapper'),
        workShiftListUrl = Routing.generate('report_work_shift_list'),
        workShiftTableUrl = Routing.generate('report_work_shift_table')
        ;

    var updateTable = function() {
        var requestData = $workShiftReportForm.serializeObject();
        $.ajax(workShiftListUrl, {
            data: requestData,
            success: function(response) {
                $workShiftTableWrapper.html(response);
            }
        });
    };

    var updateTableById = function(id) {
        $.ajax(workShiftTableUrl, {
            data: {id: id},
            success: function(response){
                $workShiftReportWrapper.html(response.result)
            }
        });
    }

    $workShiftReportForm.find('#form_begin,#form_end').on('changeDate', updateTable);
    $workShiftReportForm.find('#form_user').on('change', updateTable);

    updateTable();

    $workShiftTableWrapper.on('click', 'a', function(e) {
        e.preventDefault();
        var $this = $(this);
        if ($this.hasClass('active')) {
            return;
        }
        $workShiftTableWrapper.find('a').removeClass('active');
        $this.addClass('active');
        var id = $this.data('id');
        updateTableById(id);
    })
});