/*global window, document, $, Routing, console, mbh */

$(document).ready(function ($) {
    'use strict';
    //Show table
    $('#dynamic-sales-filter-begin2').val('');
    $('#dynamic-sales-filter-begin3').val('');
    var pricesProcessing = false,
        showTable = function () {
            var wrapper = $('#dynamic-sales-table-wrapper'),
                begin = [],
                end = [];
            $.each( $('.dynamic-sales-filter'),function (i, val) {

                if($(this).val().length){
                    begin[i] = $(this).data('daterangepicker').startDate.format('DD.MM.YYYY');
                    end[i] = $(this).data('daterangepicker').endDate.add(1, 'day').format('DD.MM.YYYY');
                }

            });

            var data = {
                'begin': begin,
                'end': end,
                'roomTypes': $('#dynamic-sales-filter-roomType').val(),
                'optionsShow': $('#dynamic-sales-show-filter-roomType').val()
            };

            if (wrapper.length === 0) {
                return false;
            }
            wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');
            if (!pricesProcessing) {
                $.ajax({
                    url: Routing.generate('dynamic_sales_table'),
                    data: data,
                    beforeSend: function () {
                        pricesProcessing = true;
                    },
                    success: function (data) {
                        wrapper.html(data);
                        onTableScroll();
                        updateTables();
                        pricesProcessing = false;
                    },
                    dataType: 'html'
                });
            }
        };

    var updateTables = function() {
        var headerTable = document.getElementById('headerTable');
        var headerTableHeight = parseInt(getComputedStyle(headerTable).height, 10);
        $('.dynamic-sales-table:lt(1)').css('margin-top', headerTableHeight);
        console.log(headerTableHeight);
        var lastTableHeight = 0;
        var lastTableTopOffset = headerTableHeight;
        var $roomTypeTitleLines = $('.mbh-grid-header2');
        $('.rightTable').each(function (index, element) {
            var roomTypeTitleLineHeight = parseInt($roomTypeTitleLines.first().css('height'), 10);
            var tableTopOffset = (lastTableTopOffset + lastTableHeight + roomTypeTitleLineHeight);
            element.style.top = tableTopOffset + 'px';
            lastTableTopOffset = tableTopOffset;
            lastTableHeight = parseInt(getComputedStyle(element).height, 10);
        });

        var comparingDatesCount = $('#headerTable').find('tr').length;
        if (comparingDatesCount === 1) {
            $('.dynamic-sales-table').find('tr').each(function (index, element) {
                var rightTableRowIdentifier = element.getAttribute('data-class');
                var appropriateRow = $('[data-class = ' + rightTableRowIdentifier + ']:lt(1)');
                element.style.height = appropriateRow.eq(0).css('height');
            });
        }

        $('.rightTableHeader').css('height', headerTableHeight);
        $('.room-type-title-string').each(function (index, element) {
            element.style.minWidth = getComputedStyle(element).width;
        });
    };

    var onTableScroll = function () {
        var tableWrapper = document.getElementById('dynamic-sales-table-wrapper');
        tableWrapper.onscroll = function () {
            $('.room-type-title-string').css('left', tableWrapper.scrollLeft);
            $('#headerTable').css('top', tableWrapper.scrollTop);
            $('.rightTable').css('left', tableWrapper.scrollLeft);
        };
    };

    var firstRangePickerOptions = mbh.datarangepicker.options;
    firstRangePickerOptions.startDate = moment().subtract(45, 'days');
    firstRangePickerOptions.endDate = moment();
    $('#dynamic-sales-filter-begin').daterangepicker(firstRangePickerOptions);
    var restRangePickersOptions = mbh.datarangepicker.options;
    restRangePickersOptions.autoUpdateInput = false;
    var $optionalDatePickers = $('#dynamic-sales-filter-begin2, #dynamic-sales-filter-begin3');
    $optionalDatePickers.daterangepicker(restRangePickersOptions);

    $optionalDatePickers.on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
    });
    $optionalDatePickers.on('cancel.daterangepicker', function() {
        $(this).val('');
    });

    showTable();

    $('#dynamic-sales-submit-button').click(function (event) {
        event.preventDefault();
        showTable();
    });

});