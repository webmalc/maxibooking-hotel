/*jslint todo: true */
/*global window, $, document, Routing */

var REPORT_SETTINGS = {
    reservation: {
        routeName: 'reservation_report_table',
        getDataFunction: function () {
            return {
                periodBegin: $('#reservation-report-filter-begin').val(),
                periodEnd: $('#reservation-report-filter-end').val(),
                date: $('#reservation-report-date').val(),
                roomTypes: $('#reservation-report-filter-rooms').val()
            }
        },
        isScrollable: true
    }
};

$(document).ready(function () {
    'use strict';

    //table
    var packageData = null,
        choosePackages = function () {

            $('.tile-bookable').find('.date').hover(function () {
                $(this).children('div').show();
            }, function () {
                if (!$(this).hasClass('selected-date-row')) {
                    $(this).children('div').hide();
                }
            });
            $('.tile-bookable').click(function () {
                var td = $(this),
                    roomId = td.attr('data-room-id'),
                    date =  td.attr('data-date');
                if (packageData && roomId === packageData.room.id && packageData.dateOne !== date) {
                    // create packages
                    packageData.dataTwo = date;
                    //TODO: create packages
                    packageData = null;
                } else {

                    $('.date').removeClass('selected-date-row').children('div').hide();
                    td.find('.date').addClass('selected-date-row').children('div').show();
                    td.siblings('.tile-bookable').find('.date').addClass('selected-date-row').children('div').show();

                    packageData = {
                        'dateOne': date,
                        'roomType': {
                            'id': td.attr('data-room-type-id'),
                            'name': td.attr('data-room-type-name')
                        },
                        'room': {
                            'id': roomId,
                            'name': td.attr('data-room-name')
                        }
                    };
                }
            });
        },
        // get accommodation report content
        accommodationReportProcessing = false,
        accommodationReportGet = function (page) {
            var form = $('#accommodation-report-filter'),
                wrapper = $('#accommodation-report-content')
                ;

            page = typeof page !== 'undefined' ? page : 1;

            if (wrapper.length === 0) {
                return false;
            }
            wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');

            if (!accommodationReportProcessing) {
                var data = form.serializeObject();
                data.page = page;

                $.ajax({
                    url: Routing.generate('report_accommodation_table'),
                    data: data,
                    beforeSend: function () {
                        accommodationReportProcessing = true;
                    },
                    success: function (data) {
                        wrapper.html(data);
                        $('#accommodation-report-filter-begin').val($('#accommodation-report-begin').val());
                        $('#accommodation-report-filter-end').val($('#accommodation-report-end').val());
                        accommodationReportProcessing = false;
                        $('[data-toggle="popover"]').popover({html: true});
                        choosePackages();
                        $('.accommodation-report-pagination').find('a').click(function (e) {
                            e.preventDefault();
                            accommodationReportGet($(this).text());
                        });
                    },
                    dataType: 'html'
                });
            }
        };
    if (!$('#accommodation-report-submit-button').length) {
        accommodationReportGet();
        $('.accommodation-report-filter').change(function () {
            accommodationReportGet();
        });
    }
    $('#accommodation-report-filter').on('submit', function (e) {
        e.preventDefault();
        accommodationReportGet();
    });
    initMBHReport();
});

function initMBHReport() {
    var $updateButton = $('.report-update-button');
    setDefaultRangePickerDates();
    if ($updateButton.length === 1) {
        updateReportTable();
        $updateButton.click(function () {
            updateReportTable();
        });
    }
}

function setDefaultRangePickerDates() {
    var $reportWrapper = $('.report-wrapper');
    var reportId = $reportWrapper.attr('data-report-id');
    var reportSettings = REPORT_SETTINGS[reportId];
    if (reportSettings) {
        var $rangePickerInput = $('.daterangepicker-input');
        var $beginInput = $('.begin-datepicker');
        var $endInput = $('.end-datepicker');
        if (!$beginInput.val() || !$endInput.val()) {
            var beginDate = moment().subtract(20, 'days');
            $rangePickerInput.data('daterangepicker').setStartDate(beginDate.toDate());
            $beginInput.val(beginDate.format("DD.MM.YYYY"));
            var endDate = moment();
            $rangePickerInput.data('daterangepicker').setEndDate(endDate.toDate());
            $endInput.val(endDate.format("DD.MM.YYYY"));
        }
    }
}

function updateReportTable() {
    var $reportWrapper = $('.report-wrapper');
    var reportId = $reportWrapper.attr('data-report-id');
    var reportSettings = REPORT_SETTINGS[reportId];
    $reportWrapper.html(mbh.loader.html);
    $.ajax({
        url: Routing.generate(reportSettings.routeName),
        success: function (response) {
            $reportWrapper.html(response);
            if (reportSettings.isScrollable) {
                setScrollable($reportWrapper.get(0));
            }
        },
        data: reportSettings.getDataFunction()
    });
}
