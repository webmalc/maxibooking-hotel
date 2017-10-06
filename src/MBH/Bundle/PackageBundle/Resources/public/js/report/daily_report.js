/*global window, document, $, Routing, console */
$(document).ready(function ($) {
    var $updateButton = $('#daily-report-update-table-button');
    if ($updateButton.length === 1) {
        updateDailyReportTable();
        $updateButton.click(function() {
            updateDailyReportTable();
        });
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