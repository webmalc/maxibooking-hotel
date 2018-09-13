/*jslint todo: true */
/*global window, $, document, Routing, Highcharts */

var REPORT_SETTINGS = {
    reservation: {
        routeName: 'reservation_report_table',
        getDataFunction: function () {
            return {
                periodBegin: $('#reservation-report-filter-begin').val(),
                periodEnd: $('#reservation-report-filter-end').val(),
                date: $('#reservation-report-date').val(),
                roomTypes: $('#reservation-report-filter-rooms').val(),
                comparedPeriod: $('#compared-period-length').val()
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
            $('#compared-period-length').change(function () {
                var comparedPeriod = this.value;
                if (comparedPeriod === '1 month' || comparedPeriod === '1 week') {
                    
                }
            });
        }
    },
    salesChannels: {
        routeName: 'sales_channels_report_table',
        getDataFunction: function () {
            return {
                begin: $('#sales-channels-report-filter-begin').val(),
                end: $('#sales-channels-report-filter-end').val(),
                filterType: $('#sales-channels-report-filter-type').val(),
                isRelative: $('#sales-channels-report-filter-is-relative').bootstrapSwitch('state'),
                sources: $('#sales-channels-report-filter-sources').val(),
                roomTypes: $('#sales-channels-report-filter-room-types').val(),
                hotels: $('#sales-channels-report-filter-hotels').val(),
                dataType: $('#sales-channels-report-filter-data-type').val()
            }
        },
        initFunc: function () {
            var changeFieldsVisibility = function () {
                var filterType = $('#sales-channels-report-filter-type').val();
                var $sourceFilterDiv = $('#sales-channels-report-filter-sources').parent();
                filterType === 'source' ? $sourceFilterDiv.show() : $sourceFilterDiv.hide();
            };
            changeFieldsVisibility();
            $('#sales-channels-report-filter-type').change(changeFieldsVisibility);
        },
        isScrollable: true,
        canDrawGraphs: true,
        byRows: true,
        getXAxisData: function () {
            var periodBegin = moment($('#sales-channels-report-filter-begin').val(), "DD.MM.YYYY");
            var periodEnd = moment($('#sales-channels-report-filter-end').val(), "DD.MM.YYYY");

            var xAxisData = {};
            for (var iteratedDate = periodBegin; iteratedDate.isSameOrBefore(periodEnd); iteratedDate.add(1, 'days')) {
                xAxisData[iteratedDate.format("DD.MM.YYYY")] = iteratedDate.valueOf();
            }

            return xAxisData;
        },
        getInterrelatedOptions: function (rowOption) {
            var excludedOptions = ['dates', 'total'];
            if (excludedOptions.indexOf(rowOption) > -1) {
                return [rowOption];
            }

            var interrelatedOptions = [];
            for (var iteratedOption in jsonData['rowTitles']) {
                if (jsonData['rowTitles'].hasOwnProperty(iteratedOption) && excludedOptions.indexOf(iteratedOption) === -1) {
                    interrelatedOptions.push(iteratedOption);
                }
            }

            return interrelatedOptions;
        },
        canDrawTotalGraphs: true,
        excludedRowOptionsInTotal: ['total', 'dates']
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
                initTotalChart(reportSettings);
            }
        },
        error: function () {
            $reportWrapper.html(mbh.error.html);
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

function showGraphModal(modalTitle) {
    var $graphModal = $('#graph-modal');
    $graphModal.modal('show');
    $graphModal.find('h4.modal-title').html(modalTitle);
}

var showTotalGraph = function (graphName, series) {
    showGraphModal(graphName);
    Highcharts.chart('graph-wrapper', {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: ''
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}% / {point.y:.1f}</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} % / {point.y:.1f}',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: series
    });
    $('text:contains("Highcharts.com")').hide();
};

function initTotalChart(reportSettings) {
    if (reportSettings.canDrawTotalGraphs) {
        $('td.total-graph-drawable').dblclick(function () {
            var $cell = $(this);
            var $table = $cell.closest('table');
            var numberOfTable = $table.attr('data-table-number');
            var graphData = [];
            var tableName = $table.find('.scrollable-text-span').html();
            var graphName = jsonData.commonRowTitles[Object.getOwnPropertyNames(jsonData.commonRowTitles)[0]]
                + ' ' + $('#sales-channels-report-filter-begin').val() + ' - ' + $('#sales-channels-report-filter-end').val()
                + ' (' + tableName + ') '
            ;
            if (reportSettings.byRows) {
                var tableData = jsonData['tableData'][numberOfTable];
                for (var rowOption in tableData) {
                    if (tableData.hasOwnProperty(rowOption) && reportSettings.excludedRowOptionsInTotal.indexOf(rowOption) === -1) {
                        var rowOptionTitle = jsonData['rowTitles'][rowOption];
                        var rowTotalValue = tableData[rowOption]['total_column'];
                        if (rowTotalValue > 0) {
                            graphData.push({name: rowOptionTitle, y: rowTotalValue});
                        }
                    }
                }
            }

            var series = [{name: graphName, colorByPoint: true, data: graphData}];
            showTotalGraph(graphName, series);
        });
    }
}

function showGraph(data, graphName) {
    showGraphModal('');
    var seriesList = [];

    data.forEach(function (rowData, index) {
        var values = rowData.values;
        var series = {};

        series.name = rowData.name;
        series.data = values;
        // series.xAxis = index;
        series.tickPosition = 'inside';
        seriesList.push(series);
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
        xAxis: {
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
        },
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
                pointInterval: 24 * 3600 * 1000 // one day
            },
            spline: {
                marker: {
                    enabled: true
                }
            }
        },
        series: seriesList,
        lang: mbh.highchartsOptions.lang
    });
    $('text:contains("Highcharts.com")').hide();
}