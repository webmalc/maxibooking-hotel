/*global document, window, $, Highcharts, analytics_filter_content */
$(document).ready(function () {
    'use strict';

    if(!window['Highcharts']) {
        return;
    }

    Highcharts.setOptions({
        lang: {
            shortMonths: [
                Translator.trans("analytics.months.jan_abbr"),
                Translator.trans("analytics.months.feb_abbr"),
                Translator.trans("analytics.months.mar_abbr"),
                Translator.trans("analytics.months.apr_abbr"),
                Translator.trans("analytics.months.may_abbr"),
                Translator.trans("analytics.months.jun_abbr"),
                Translator.trans("analytics.months.jul_abbr"),
                Translator.trans("analytics.months.aug_abbr"),
                Translator.trans("analytics.months.sep_abbr"),
                Translator.trans("analytics.months.okt_abbr"),
                Translator.trans("analytics.months.nov_abbr"),
                Translator.trans("analytics.months.dec_abbr")
            ],
            months: [
                Translator.trans("analytics.months.jan"),
                Translator.trans("analytics.months.feb"),
                Translator.trans("analytics.months.mar"),
                Translator.trans("analytics.months.apr"),
                Translator.trans("analytics.months.may"),
                Translator.trans("analytics.months.jun"),
                Translator.trans("analytics.months.jul"),
                Translator.trans("analytics.months.aug"),
                Translator.trans("analytics.months.sep"),
                Translator.trans("analytics.months.okt"),
                Translator.trans("analytics.months.nov"),
                Translator.trans("analytics.months.dec")
            ],
            weekdays: [
                Translator.trans("analytics.days_of_week.sun"),
                Translator.trans("analytics.days_of_week.mon"),
                Translator.trans("analytics.days_of_week.tue"),
                Translator.trans("analytics.days_of_week.wed"),
                Translator.trans("analytics.days_of_week.thu"),
                Translator.trans("analytics.days_of_week.fri"),
                Translator.trans("analytics.days_of_week.sat")
            ],
            downloadJPEG: Translator.trans("analytics.downloadJPEG"),
            downloadPNG: Translator.trans("analytics.downloadPNG"),
            downloadPDF: Translator.trans("analytics.downloadPDF"),
            downloadSVG: Translator.trans("analytics.downloadSVG"),
            drillUpText: "",
            loading: Translator.trans("analytics.loading"),
            printChart: Translator.trans("analytics.printChart"),
            resetZoom: Translator.trans("analytics.resetZoom"),
            resetZoomTitle: Translator.trans("analytics.resetZoomTitle")
        }
    });
    

    var highchartsTooltip = function (name, x, y) {
        return '<span style="font-size:10px;">' + Highcharts.dateFormat('%A, %b. %d', x) + '</span><br/>' + name + ': <b>' + Highcharts.numberFormat(y, 2, '.', ',') + '</b>';
    };

    var chartGet = function () {
        var wrapper = $('#analytics_filter_content')
        var hideFilters = function () {
                if (jQuery.inArray($('#analytics-filter-type').val(), ['hotel_occupancy']) !== -1) {
                    $('#analytics-filter-cumulative-wrapper, #analytics-filter-months-wrapper').hide();
                } else {
                    $('#analytics-filter-cumulative-wrapper, #analytics-filter-months-wrapper').show();
                }

            };

        if (!wrapper.length) {
            return;
        }

        hideFilters();
        wrapper.html(mbh.loader.html);
        $.ajax({
            url: Routing.generate('analytics_choose'),
            data: $('#analytics-filter').serialize(),
            success: function (data) {
                if (data.error !== null) {
                    wrapper.html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '+ Translator.trans("analytics.error_occurred") +'</div>');
                } else {
                    data.html = data.html.replace(/"@/g, '').replace(/@"/g, '');
                    eval(data.html);
                    $('text:contains("Highcharts.com")').hide();

                    $('.highcharts-line-series').dblclick(function (event) {
                        var seriesClassNameBeginning = 'highcharts-series-';
                        var seriesNumber = parseInt(this.classList[3].substring(seriesClassNameBeginning.length), 10);
                        $(analytics_filter_content).highcharts().series[seriesNumber].hide();
                        var $chart = $(analytics_filter_content).highcharts();
                        var series = $chart.series;

                        var numberOfVisibleSeries = getNumberOfVisibleSeries(series);
                        var showAllSeries = numberOfVisibleSeries === 0 || numberOfVisibleSeries === 1;
                        series.forEach(function (elem, index) {
                            if (index === seriesNumber || showAllSeries) {
                                elem.setVisible(true, false);
                            } else {
                                elem.setVisible(false, false);
                            }
                        });
                        $chart.redraw();
                    })
                }
            }
        });
    };
    chartGet();

    var numberOfRequests = 0;
    var numberOfPendingRequests = 0;
    $('.analytics-filter').on('change switchChange.bootstrapSwitch', function () {
        numberOfRequests++;
        setTimeout(function () {
            numberOfPendingRequests++;
            if (numberOfRequests === numberOfPendingRequests) {
                chartGet();
            }
        }, 500);
    });
    
    $('#analytics-filter-cumulative').on('switchChange', function () {
        chartGet();
    });
    $('#analytics-filter-months').on('switchChange', function () {
        chartGet();
    });
});

function getNumberOfVisibleSeries(series) {
    var numberOfVisibleSeries = 0;
    series.forEach(function (line) {
        if (line.visible) {
            numberOfVisibleSeries++;
        }
    });

    return numberOfVisibleSeries;
}