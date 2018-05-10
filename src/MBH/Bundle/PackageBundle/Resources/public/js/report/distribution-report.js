/*global window, document, $, Routing, console */
$(document).ready(function ($) {
    var $updateButton = $("#distribution-report-update-table-button");
    if($updateButton.length === 1) {
        updateDistributionReportTable();
        $updateButton.click(function() {
            updateDistributionReportTable();
        });
        var $filterBegin = $('#distribution-report-filter-begin');
        if (!$filterBegin.val()) {
            var $rangePickerInput = $('.daterangepicker-input');
            $rangePickerInput.data('daterangepicker').setStartDate(new Date());
            $rangePickerInput.data('daterangepicker').setEndDate(moment().add(45, 'days').toDate());
        }
    }

});

function updateDistributionReportTable() {
    var $distributionReportWrapper = $('#distribution-report-table');
    $distributionReportWrapper.html(mbh.loader.html);
    $.ajax({
        url: Routing.generate('distribution_report_table'),
        success: function(response) {
            $distributionReportWrapper.html(response);
            // setScrollable();
        },
        data: {
            group_type: $('#distribution-report-filter-group-type').val(),
            type: $('#distribution-report-filter-type').val(),
            begin: $('#distribution-report-filter-begin').val(),
            end: $('#distribution-report-filter-end').val(),
            creationBegin: $('#distribution-report-filter-creation-begin').val(),
            creationEnd: $('#distribution-report-filter-creation-end').val(),
            hotels: $('#distribution-report-filter-hotels').val()
        }
    });
}
