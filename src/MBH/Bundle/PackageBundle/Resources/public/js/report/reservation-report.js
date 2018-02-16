/*global window, document, $, Routing, console */
$(document).ready(function ($) {
    var $updateButton = $('#reservation-report-update-table-button');
    if ($updateButton.length === 1) {
        updateReservationReportTable();
        $updateButton.click(function() {
            updateReservationReportTable();
        });
    }
});

function updateReservationReportTable() {
    var $distributionReportWrapper = $('#reservation-report-table');
    $distributionReportWrapper.html(mbh.loader.html);
    $.ajax({
        url: Routing.generate('reservation_report_table'),
        success: function (response) {
            $distributionReportWrapper.html(response);
            // setScrollable();
        },
        data: {
            periodBegin: $('reservation-report-filter-begin').val(),
            periodEnd: $('reservation-report-filter-end').val(),
            date: $('reservation-report-date').val()
        }
    });
}
