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
        isScrollable: true,
        canDrawGraphs: true,
        byRows: true,
        getXAxisData: function (tableData, option) {
            var previousYearOptions = ['previous_number_of_packages'];
            var isCurrentYearOption = previousYearOptions.indexOf(option) === -1;

            var periodBegin = moment($('#reservation-report-filter-begin').val(), "DD.MM.YYYY");
            var periodEnd = moment($('#reservation-report-filter-end').val(), "DD.MM.YYYY");
            if (!isCurrentYearOption) {
                periodBegin = periodBegin.subtract(1, "year");
                periodEnd = periodEnd.subtract(1, "year");
            }

            var xAxisData = {};
            for (var iteratedDate = periodBegin; iteratedDate.isSameOrBefore(periodEnd); iteratedDate.add(1, 'days')) {
                xAxisData[iteratedDate.format("DD.MM")] = iteratedDate.valueOf();
            }

            return xAxisData;
        },
        getInterrelatedOptions: function (option) {
            var interrelatedOptions = ['number_of_packages', 'previous_number_of_packages'];
            return interrelatedOptions.indexOf(option) > -1 ? interrelatedOptions : [];
        },
        initFunc: function () {
            var $reportDateInput = $('#reservation-report-date');
            if (!$reportDateInput.val()) {
                $reportDateInput.val(moment().format('DD.MM.YYYY'))
            }
        }
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
                    date = td.attr('data-date');
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
            wrapper.html(mbh.loader.html);

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
    var reportSettings = getReportSettings();
    if ($updateButton.length === 1 && reportSettings) {
        if (reportSettings.initFunc) {
            reportSettings.initFunc();
        }
        updateReportTable(reportSettings);
        $updateButton.click(function () {
            updateReportTable(reportSettings);
        });
    }
}

function getReportSettings() {
    var $reportWrapper = $('.report-wrapper');
    var reportId = $reportWrapper.attr('data-report-id');
    
    return REPORT_SETTINGS[reportId];
}

function updateReportTable(reportSettings) {
    var $reportWrapper = $('.report-wrapper');

    $reportWrapper.html(mbh.loader.html);
    $.ajax({
        url: Routing.generate(reportSettings.routeName),
        success: function (response) {
            $reportWrapper.html(response);
            if (reportSettings.isScrollable) {
                setScrollable($reportWrapper.get(0));
                initGraphDrawing(reportSettings);
            }
        },
        data: reportSettings.getDataFunction()
    });
}

function initGraphDrawing(reportSettings) {
    if (reportSettings.canDrawGraphs) {
        $('td.graph-drawable').dblclick(function () {
            var cell = this;
            var numberOfTable = $(cell).closest('table').attr('data-table-number');
            var graphData = [];
            var graphName;

            if (reportSettings.byRows) {
                var rowOption = cell.parentNode.getAttribute('data-row-option');
                var interrelatedOptions = reportSettings.getInterrelatedOptions(rowOption);
                var tableData = jsonData['tableData'][numberOfTable];
                if (tableData) {
                    var xAxisData = reportSettings.getXAxisData(tableData, rowOption);
                    var dataOptions;
                    if (interrelatedOptions.length > 0) {
                        dataOptions = interrelatedOptions;
                        graphName = jsonData.commonRowTitles[rowOption];
                    } else {
                        dataOptions = [rowOption];
                        graphName = jsonData.rowTitles[rowOption];
                    }

                    dataOptions.forEach(function (option) {
                        var optionData = tableData[option];
                        var graphOptionData = [];
                        for (var xValue in xAxisData) {
                            if (xAxisData.hasOwnProperty(xValue)) {
                                graphOptionData.push([xAxisData[xValue], optionData[xValue]]);
                            }
                        }
                        graphData.push({values: graphOptionData, name: jsonData.rowTitles[option]});
                    });
                }
            } else {

            }

            if (graphData.length > 0) {
                showGraph(graphData, graphName);
            }
        });
    }
}

function showGraph(data, graphName) {
    $('#graph-modal').modal('show');
    var dates = [];

    data.forEach(function (rowData, index) {
        var values = rowData.values;
        var series = {};

        series.name = rowData.name;
        series.data = values;
        series.xAxis = index;
        series.tickPosition = 'inside';
        dates.push(series);
    });


    Highcharts.chart('graph-wrapper', {
        global: {
            useUTC: true
        },
        chart: {
            type: 'areaspline',
            alignTicks: false
        },
        title: {
            text: graphName
        },
        xAxis: [{
            type: 'datetime',
            showLastLabel: true,
            crosshair: true,
            tickmarkPlacement: 'on',
            labels: {
                formatter: function () {
                    return Highcharts.dateFormat('%b %d', this.value);
                },
                style: {
                    color: 'rgb(124, 181, 236)'

                }
            },
            plotBands: [{
                color: 'rgba(68, 170, 213, .2)'
            }]
        }, {
            type: 'datetime',
            showLastLabel: true,
            crosshair: true,
            tickmarkPlacement: 'on',
            labels: {
                formatter: function () {
                    return Highcharts.dateFormat('%b %d', this.value);
                }
            }
        },
            {
                type: 'datetime',
                showLastLabel: true,
                crosshair: true,
                labels: {
                    formatter: function () {
                        return Highcharts.dateFormat('%b %d', this.value);
                    }
                }
            }
        ],
        yAxis: {
            title: {
                text: graphName
            }
        },
        tooltip: {
            shared: true,
            headerFormat: '<b>{series.name}</b><br>',
            pointFormat: '{point.x:%e. %b}: <b>{point.y:.0f} </b>'
        },
        plotOptions: {
            areaspline: {
                fillOpacity: 0.5
            },
            series: {
                pointWidth: 15,
                pointInterval: 21 * 3600 * 1000 // one day
            },
            spline: {
                marker: {
                    enabled: true
                }
            }
        },
        series: dates,
        lang: mbh.highchartsOptions.lang
    });
    $('text:contains("Highcharts.com")').hide();
}