/*global window, document, $, Routing, console, mbh */
$(document).on('ready', function () {
    'use strict';

    var $workShiftReportForm = $('#work-shift-report-form');
    if($workShiftReportForm.length == 0) {
        return;
    }

    var $workShiftReportWrapper = $('#work-shift-report-table-wrapper'),
        $workShiftTableWrapper = $('#work-shift-table-wrapper'),
        workShiftListUrl = Routing.generate('report_work_shift_list'),
        workShiftTableUrl = Routing.generate('report_work_shift_table'),
        $comeBackToListButton = $('#come-back-to-list'),
        $workShiftDetailHeader = $('#work-shift-detail-header')
        ;

    var updateTable = function() {
        var requestData = $workShiftReportForm.serializeObject();
        mbh.loader.acceptTo($workShiftTableWrapper);
        $.ajax(workShiftListUrl, {
            data: requestData,
            success: function(response) {
                $workShiftTableWrapper.html(response);
                $workShiftReportWrapper.empty();
            }
        });
    };

    var updateTableById = function(id) {
        mbh.loader.acceptTo($workShiftReportWrapper);
        $.ajax(workShiftTableUrl, {
            data: {id: id},
            success: function(response){
                $workShiftReportWrapper.html(response)
            }
        });
    }

    var comeBackToList = function() {
        $workShiftTableWrapper.removeClass('active');
        $workShiftTableWrapper.find('table tr').show();
        $workShiftReportWrapper.empty();
        $workShiftReportForm.closest('.box').show();
        $workShiftDetailHeader.hide();
    }

    $workShiftReportForm.find('#form_begin,#form_end').on('changeDate', updateTable);
    $workShiftReportForm.find('#form_user,#form_status').on('change', updateTable);

    $workShiftDetailHeader.hide();

    //comeBackToList();
    updateTable();

    $workShiftTableWrapper.on('dblclick', 'table tbody tr', function(e) {
        e.preventDefault();
        var $this = $(this);
        var id = $this.data('id');
        updateTableById(id);

        $workShiftReportForm.closest('.box').hide();

        var header = $this.data('detail-header');
        $workShiftDetailHeader.find('.header').html(header);
        $workShiftDetailHeader.show();

        $workShiftTableWrapper.find('table tr').hide();
        $this.show();

        return false;
    });

    $comeBackToListButton.on('click', function(e) {
        e.preventDefault();
        comeBackToList();
    });

    $workShiftTableWrapper.on('click', '.work-shift-confirm' , function(e) {
        e.preventDefault();
        var $this = $(this);
        var $row = $this.closest('tr');
        var id = $row.data('id');
        var electronicCashIncomeTotal = $row.data('electronicCashIncomeTotal');
        var confirmText = Translator.trans('work_shift.close_work_shift') + (electronicCashIncomeTotal ? ' ' + Translator.trans('work_shift.and_confirm_payments') + ' ' + electronicCashIncomeTotal : '') + '?';
        mbh.alert.show(null, Translator.trans('work_shift.close_work_shift') + '?', confirmText, Translator.trans('work_shift.take_shift'), 'fa fa-check', 'success', function() {
            mbh.alert.hide();
            $.ajax(Routing.generate('work_shift_ajax_close'), {
                data: {id: id},
                success: function(response) {
                    comeBackToList();
                    updateTable();
                }
            })
        });
    });
});