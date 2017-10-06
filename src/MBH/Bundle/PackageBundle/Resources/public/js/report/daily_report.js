/*global window, document, $, Routing, console */
$(document).ready(function ($) {
    var $updateButton = $('#daily-report-update-table-button');
    if ($updateButton.length === 1) {
        updateDailyReportTable();
        $updateButton.click(function() {
            updateDailyReportTable();
        });
    }
    var $filterBegin = $('#daily-report-filter-begin');
    if (!$filterBegin.val()) {
        var $rangePickerInput = $('.daterangepicker-input');
        $rangePickerInput.data('daterangepicker').setStartDate(moment(mbh.startDatePick, "DD.MM.YYYY").toDate());
        $rangePickerInput.data('daterangepicker').setEndDate(moment(mbh.startDatePick, "DD.MM.YYYY").add(45, 'days').toDate());
    }
    var $calcBegin = $('#daily-report-filter-calc-begin');
    if (!$calcBegin.val()) {
        $calcBegin.val(moment().startOf('year').format('DD.MM.YYYY'));
    }
    var $calcEnd = $('#daily-report-filter-calc-end');
    if (!$calcEnd.val()) {
        $calcEnd.val(moment().startOf('year').add(1, 'year').format('DD.MM.YYYY'));
    }
});

function updateDailyReportTable() {
    var $dailyReportWrapper = $('#daily-report');
    $dailyReportWrapper.html(mbh.loader.html);
    $.ajax({
        url: Routing.generate('packages_daily_report_table'),
        success: function(response) {
            $dailyReportWrapper.html(response);
            setScrollable();
        },
        data: {
            begin: $('#daily-report-filter-begin').val(),
            end: $('#daily-report-filter-end').val(),
            calcBegin: $('#daily-report-filter-calc-begin').val(),
            calcEnd: $('#daily-report-filter-calc-end').val(),
            hotels: $('#daily-report-filter-hotels').val()
        }
    });
}