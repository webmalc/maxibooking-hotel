/*global window, document, $, Routing, mbh, Highcharts, Translator */

$(document).ready(function ($) {
    'use strict';
    $('#dynamic-sales-filter-begin2').val('');
    $('#dynamic-sales-filter-begin3').val('');
    var $roomTypeOptionsSelect = $('#dynamic-sales-filter-roomType');

    var wasTotalValuesSelected = false;
    $roomTypeOptionsSelect.on("change", (function () {
        var selectedOptions = $roomTypeOptionsSelect.val();
        var isTotalValueSelected = selectedOptions && selectedOptions.indexOf('total') > -1;
        if (!wasTotalValuesSelected && isTotalValueSelected && selectedOptions.length > 1) {
            $roomTypeOptionsSelect.val('total').trigger('change');
            wasTotalValuesSelected = true;
        } else if (wasTotalValuesSelected && isTotalValueSelected) {
            selectedOptions.splice(selectedOptions.indexOf('total'), 1);
            $roomTypeOptionsSelect.val(selectedOptions[0]).trigger('change');
            wasTotalValuesSelected = false;
        } else {
            wasTotalValuesSelected = isTotalValueSelected;
        }
    }));

    var pricesProcessing = false,
        showTable = function () {
            var wrapper = $('#dynamic-sales-table-wrapper'),
                begin = [],
                end = [];
            $.each($('.dynamic-sales-filter'), function (i) {

                if ($(this).val().length) {
                    begin[i] = $(this).data('daterangepicker').startDate.format('DD.MM.YYYY');
                    end[i] = $(this).data('daterangepicker').endDate.format('DD.MM.YYYY');
                }
            });

            var data = {
                'begin': begin,
                'end': end,
                'roomTypes': $roomTypeOptionsSelect.val(),
                'optionsShow': $('#dynamic-sales-show-filter-roomType').val()
            };

            if (wrapper.length === 0) {
                return false;
            }
            wrapper.html(mbh.loader.html);
            if (!pricesProcessing) {
                $.ajax({
                    url: Routing.generate('dynamic_sales_table'),
                    data: data,
                    beforeSend: function () {
                        pricesProcessing = true;
                    },
                    success: function (data) {
                        wrapper.html(data);
                        if ($('.dynamic-sales-table').length > 0) {
                            if (!isMobileDevice()) {
                              onTableScroll();
                            }
                            updateTables();
                        }
                        pricesProcessing = false;
                    },
                    dataType: 'html'
                });
            }
        };

    var updateTables = function () {

        var headerTable = document.getElementById('headerTable');
        var headerTableHeight = parseInt(getComputedStyle(headerTable).height, 10);
        $('.dynamic-sales-table:lt(1)').css('margin-top', headerTableHeight);
        var lastTableHeight = 0;
        var lastTableTopOffset = headerTableHeight;
        var roomTypeTitleLineHeight = parseInt($('.mbh-grid-header2').first().css('height'), 10);
        $('.rightTable').each(function (index, element) {
            var tableTopOffset = lastTableTopOffset + lastTableHeight + roomTypeTitleLineHeight;
            var isTableCompared = element.classList.contains('rightTable-comparison');
            if (isTableCompared) {
                //Убираю смещение бордера
                tableTopOffset -= 1;
            }
            element.style.top = tableTopOffset + 'px';
            lastTableTopOffset = tableTopOffset;
            lastTableHeight = parseInt(getComputedStyle(element).height, 10);
        });

        $('.rightTableHeader').css('height', headerTableHeight);

        var $dynamicSalesTables = $('.dynamic-sales-table');
        var $dynamicSalesTableRows = $dynamicSalesTables.find('tr');
        $dynamicSalesTableRows.each(function (index, element) {
            var rightTableRowIdentifier = element.getAttribute('data-class');
            var $appropriateRow = $('.rightTable').find('[data-class = ' + rightTableRowIdentifier + ']').eq(0);
            element.style.height = $appropriateRow.css('height');
        });

        var $headerTable = $(headerTable);
        $headerTable.find('tr:lt(1)').eq(0).find('.date-td').each(function (cellNumber) {
            var widestWidth = getWidestCellWidth(cellNumber, $dynamicSalesTables, $headerTable);
            setWidestCellWidth(cellNumber, widestWidth, $headerTable, $dynamicSalesTables);
        });

        $('.table-title').each(function (index, element) {
            element.style.minWidth = parseInt(getComputedStyle(element).width, 10) + 40 + 'px';
        });

        var $dateRows = $('.dates');
        $dynamicSalesTables.find('tr:not(:first)').dblclick(function () {
            var row = this;
            var data = [];
            var currentTable = row.parentNode.parentNode;
            var currentDataClass = row.getAttribute('data-class');
            var isComparative = currentDataClass.indexOf('-compare') > -1;
            var $associatedPeriods = $(currentTable).find('[data-class="' + currentDataClass + '"]');
            $associatedPeriods.each(function (index, element) {
                var dateRowIndex = isComparative ? index + 1 : index;
                var isFirstRow = !isComparative && index === 0;
                data.push(collectGraphData($(element), $dateRows.eq(dateRowIndex), isFirstRow));
            });
            var optionData = getOptionData(currentDataClass);
            showDynamicSalesGraph(data, optionData);
        });
    };

    var setWidestCellWidth = function (number, widestWidth, $headerTable, $dynamicSalesTables) {
        $headerTable.find('tr:lt(1)').children().eq(number + 1).css('min-width', widestWidth);
        $dynamicSalesTables.each(function (tableNumber, table) {
            $(table).find('tr:lt(2)').eq(1).children().eq(number + 1).css('min-width', widestWidth);
        });
    };

    var getWidestCellWidth = function (cellNumber, $dynamicSalesTables, $headerTable) {
        var widestCellWidth = parseInt($headerTable.find('tr:lt(1)').children().eq(cellNumber + 1).css('width'), 10);

        $dynamicSalesTables.each(function (tableNumber, table) {
            var cellWidth = $(table).find('tr:lt(2)').eq(1).children().eq(cellNumber + 1).css('width');
            var cellWidthInt = parseInt(cellWidth, 10);
            if (widestCellWidth < cellWidthInt) {
                widestCellWidth = cellWidthInt;
            }
        });

        return widestCellWidth;
    };

    var onTableScroll = function () {
        var tableWrapper = document.getElementById('dynamic-sales-table-wrapper');
        tableWrapper.onscroll = null;
        tableWrapper.onscroll = function () {
            $('.table-title').css('left', tableWrapper.scrollLeft);
            $('#headerTable').css('top', tableWrapper.scrollTop);
            $('.rightTable').css('left', tableWrapper.scrollLeft);
            var $leftTopPanel = $('#left-top-scrollable');
            $leftTopPanel.css('top', tableWrapper.scrollTop);
            $leftTopPanel.css('left', tableWrapper.scrollLeft);
        };
    };

    //handle datepickers
    var firstRangePickerOptions = mbh.datarangepicker.options;
    firstRangePickerOptions.startDate = moment().subtract(45, 'days');
    firstRangePickerOptions.endDate = moment();
    $('#dynamic-sales-filter-begin').daterangepicker(firstRangePickerOptions);
    var restRangePickersOptions = mbh.datarangepicker.options;
    restRangePickersOptions.autoUpdateInput = false;
    var $optionalDatePickers = $('#dynamic-sales-filter-begin2, #dynamic-sales-filter-begin3');
    $optionalDatePickers.daterangepicker(restRangePickersOptions);

    $optionalDatePickers.on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
    });
    $optionalDatePickers.on('cancel.daterangepicker', function () {
        $(this).val('');
    });

    showTable();

    $('#dynamic-sales-submit-button').click(function (event) {
        event.preventDefault();
        showTable();
    });
});

function getOptionData(optionId) {
    var compareSubstringIndex = optionId.indexOf('-compare');
    var isComparative = compareSubstringIndex > -1;
    var isRelative = false;
    if (isComparative) {
        isRelative = optionId.indexOf('-percentage') > -1;
        optionId = optionId.substring(0, compareSubstringIndex);
    }

    var options = document.getElementById('dynamic-sales-show-filter-roomType').options;
    for (var i = 0; i < options.length; i++) {
        var option = options[i];
        if (option.value === optionId) {
            return {name: option.text, isComparative: isComparative, isRelative: isRelative};
        }
    }
}

var collectGraphData = function ($row, $dateRow, isFirstRow) {
    var rowData = [];
    var $dateElements = $dateRow.children();
    if (isFirstRow) {
        $dateElements = $dateElements.not(':first, :last');
    }
    var previousDate;
    var lastDate;
    $row.children().not(':first, :last').each(function (index, element) {
        var span = element.getElementsByTagName('span');
        var innerHtml = span.length > 0 ? span[0].innerHTML : element.innerHTML;
        var hasValue = !isNaN(parseFloat(innerHtml));
        var value;
        var date;
        if (hasValue) {
            value = parseFloat(innerHtml);
            date = getDateByIndex($dateElements, index);
            previousDate = date;
        } else {
            if (!lastDate) {
                lastDate = moment(previousDate, "DD.MM.YY");
            }
            value = null;
            date = previousDate.add(1, 'days');
        }

        rowData.push([date.valueOf(), value]);
    });

    return {
        values: rowData,
        periodEnd: lastDate ? lastDate : previousDate
    };
};

var getDateByIndex = function ($dateElements, index) {
    var dateString = $dateElements.eq(index).find('.date-string').text();
    return moment(dateString, "DD.MM.YY");
};

var showDynamicSalesGraph = function (data, optionData) {
    $('#graph-modal').modal('show');
    var dates = [];
    data.forEach(function (rowData, index) {
        var values = rowData.values;
        var series = {};

        var periodBegin = values[0][0];
        var periodEnd = rowData.periodEnd;
        series.name = moment(periodBegin).format("DD.MM.YYYY") + ' - ' + periodEnd.format("DD.MM.YYYY");

        series.data = values;
        series.xAxis = index;
        series.tickPosition = 'inside';
        dates.push(series);
    });

    var graphName;
    var yAxisTitle;
    var optionName = optionData.name;

    if (optionData.isComparative) {
        graphName = optionData.isRelative
            ? Translator.trans("dynamic_sales.option_data_type.comparative_graph_name.relative", {optionName: optionName})
            : Translator.trans("dynamic_sales.option_data_type.comparative_graph_name.absolute", {optionName: optionName});
        yAxisTitle = optionData.isRelative ? (optionName + ', %') : optionName;
    } else {
        graphName = yAxisTitle = optionName;
    }

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
                text: yAxisTitle
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
};