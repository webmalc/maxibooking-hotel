/*global window, document, $, Routing, console, mbh */
$(document).ready(function ($) {
    'use strict';
    $('#dynamic-schedule-filter-begin2').val('');
    $('#dynamic-schedule-filter-begin3').val('');

    function findMaxCount(data) {
        var countDay = 0;
        $.each(data, function (i, val) {
            countDay < val.length ? countDay = val.length : null;
        });

        return countDay;
    }

    function addDays(data, countDay) {
        $.each(data, function (i, val) {
            var diffirence = countDay - val.length;

            if (diffirence > 0) {
                var last = val[val.length - 1];
                for (var j = 1; j < (diffirence + 1); j++) {
                    var obj = {};
                    obj.day = moment(last.day).add(j, 'days').format('MM.DD.YYYY');
                    obj.amount = null;
                    val.push(obj);
                }

            }

        });

        return data;
    }

    //Show schedule
    var pricesProcessing = false,
        showTable = function () {
            var wrapper = $('#dynamic-sales-schedule_filter_content'),
                begin = [],
                end = [];
            $.each($('.dynamic-schedule-filter'), function (i, val) {

                if ($(this).val().length) {
                    begin[i] = $(this).data('daterangepicker').startDate.format('DD.MM.YYYY');
                    end[i] = $(this).data('daterangepicker').endDate.add(1, 'day').format('DD.MM.YYYY');
                }

            });

            var data = {
                'begin': begin,
                'end': end,
                'roomTypes': $('#dynamic-schedule-filter-roomType').val(),
                'optionsShow': $('#dynamic-schedule-show-filter-roomType').val(),
                'growth': $('#schedule-filter-cumulative').is(':checked')
            };

            if (wrapper.length === 0) {
                return false;
            }

            wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');
            if (!pricesProcessing) {
                $.ajax({

                    url: Routing.generate('dynamic_sales_schedule'),
                    data: data,
                    beforeSend: function () {
                        pricesProcessing = true;
                    },

                    success: function (data) {

                        var nameYaxis = data.pop(),
                            nameGraph = data.pop();

                        data = data.pop();

                        var dates = [],
                            countDay = findMaxCount(data);

                        data = addDays(data, countDay);

                        $.each(data, function (i, val) {
                            var Schedule = {};
                            Schedule.name = 'Период ' + (i + 1);
                            Schedule.data = [];
                            Schedule.xAxis = i;
                            Schedule.tickPosition = 'inside';

                            $.each(val, function (index, value) {

                                Schedule.data.push(
                                    [
                                        moment(value.day).add(3, 'hours').valueOf(),
                                        value.amount,
                                    ]);
                            });

                            dates.push(Schedule);
                        });

                        //config hightCharts
                        Highcharts.chart('dynamic-sales-schedule_filter_content', {
                            global: {
                                useUTC: true
                            },
                            chart: {
                                type: 'line',
                                alignTicks: false
                            },
                            title: {
                                text: nameGraph
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
                                },
                            ],

                            yAxis: {
                                title: {
                                    text: nameYaxis
                                },
                                min: 0
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

                            series: dates

                        });

                        pricesProcessing = false;
                    }

                });
            }
        };

    $('#dynamic-schedule-filter-begin').daterangepicker({
        startDate: moment().add(-45, 'days'),
        endDate: moment(),
        "autoApply": true
    });

    $('#dynamic-schedule-filter-begin2').daterangepicker({
        autoUpdateInput: false,
        "autoApply": true
    });

    $('#dynamic-schedule-filter-begin3').daterangepicker({
        autoUpdateInput: false,
        "autoApply": true
    });

    $('#dynamic-schedule-filter-begin2').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
    });

    $('#dynamic-schedule-filter-begin2').on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
    });

    $('#dynamic-schedule-filter-begin3').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
    });

    $('#dynamic-schedule-filter-begin3').on('cancel.daterangepicker', function (ev, picker) {
        $(this).val('');
    });

    showTable();


    $('#dynamic-sales-schedule-submit-button').click(function (event) {
        event.preventDefault();
        showTable();
    });

});