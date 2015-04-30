/*global window, $ */
$(document).ready(function() {
    'use strict';

    Highcharts.setOptions({
        lang: {
            shortMonths:    ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
            months:         ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
            weekdays:       ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
            downloadJPEG:   "Сохранить как JPEG",
            downloadPNG:    "Сохранить как PNG",
            downloadPDF:    "Сохранить как PDF",
            downloadSVG:    "Сохранить как SVG",
            drillUpText:    "",
            loading:        "Загрузка",
            printChart:     "Версия для печати",
            resetZoom:      "Сбросить приближение",
            resetZoomTitle: "Размер 1:1"
        }
    });

    var highchartsTooltip = function (name, x, y) {
        return '<span style="font-size:10px;">' + Highcharts.dateFormat('%A, %b. %d', x) +'</span><br/>'+ name +': <b>'+ Highcharts.numberFormat(y,2,'.',',') + '</b>';
    }

    var chartGet = function () {
        var wrapper = $('#analytics_filter_content')
            var hideFilters = function () {
                    if(jQuery.inArray($('#analytics-filter-type').val(), ['hotel_occupancy']) !== -1) {
                        $('#analytics-filter-cumulative-wrapper, #analytics-filter-months-wrapper').hide();
                    } else {
                        $('#analytics-filter-cumulative-wrapper, #analytics-filter-months-wrapper').show();
                    }

                }
            ;

        if (!wrapper.length) {
            return ;
        }

        hideFilters();
        wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');
        $.ajax({
            url: Routing.generate('analytics_choose'),
            data: $('#analytics-filter').serialize(),
            success: function (data) {
                if (data.error !== null) {
                    wrapper.html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Произошла ошибка при постороении отчета</div>');
                } else {
                    data.html = data.html.replace(/"@/g,'').replace(/@"/g,'');
                    eval(data.html);
                    $('text:contains("Highcharts.com")').hide();
                }
            }
        });

    };
    chartGet();
    $('.analytics-filter').change(function () { chartGet() });
    $('#analytics-filter-cumulative').on('switchChange', function() {
        chartGet();
    });
    $('#analytics-filter-months').on('switchChange', function() {
        chartGet();
    });
});
