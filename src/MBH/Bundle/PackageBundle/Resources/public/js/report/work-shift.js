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
                $workShiftReportWrapper.empty();
            }
        });
    };

    var updateTableById = function(id) {
        $.ajax(workShiftTableUrl, {
            data: {id: id},
            success: function(response){
                $workShiftReportWrapper.html(response)
            }
        });
    }

    $workShiftReportForm.find('#form_begin,#form_end').on('changeDate', updateTable);
    $workShiftReportForm.find('#form_user,#form_status').on('change', updateTable);

    updateTable();

    $workShiftTableWrapper.on('click', 'table tr', function(e) {
        e.preventDefault();
        var $this = $(this);
        if ($this.hasClass('active')) {
            return;
        }
        $workShiftTableWrapper.find('table tr').removeClass('active');
        $this.addClass('active');
        var id = $this.data('id');
        updateTableById(id);
    })

    $workShiftTableWrapper.on('click', '.work-shift-confirm' , function(e) {
        e.preventDefault();
        var $this = $(this);
        var $row = $this.closest('tr');
        var id = $row.data('id');
        var electronicCashIncomeTotal = $row.data('electronicCashIncomeTotal');
        var confirmText = 'Завершить смену' + (electronicCashIncomeTotal ? ' и подтвердить N платежей на сумму '+electronicCashIncomeTotal : '') + '?';
        mbh.alert.show(null, 'Завершить смену?', confirmText, 'Подтвердить', 'fa fa-check', 'success', function() {
            $.ajax(Routing.generate('work_shift_ajax_close'), {
                data: {id: id},
                success: function(response) {
                    mbh.alert.hide();
                    updateTable();
                }
            })
        });
    })
});