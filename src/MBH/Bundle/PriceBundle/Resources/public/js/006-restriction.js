/*global window, $, document, Routing, mbhGridCopy*/

$(document).ready(function () {
    'use strict';

    // set user Date (ClientConfig)
    if ($('.daterangepicker-input').prev().is('#restriction-overview-filter-begin')) {

        if (!($('#restriction-overview-filter-begin').val()) && !($('#restriction-overview-filter-end').val())) {

            $('.daterangepicker-input').data('daterangepicker').setStartDate(moment(mbh.startDatePick, "DD.MM.YYYY").toDate());
            $('.daterangepicker-input').data('daterangepicker').setEndDate(moment(mbh.startDatePick, "DD.MM.YYYY").add(45, 'days').toDate());
            $('#restriction-overview-filter-begin').val($('.daterangepicker-input').data('daterangepicker').startDate.format('DD.MM.YYYY'));
            $('#restriction-overview-filter-end').val($('.daterangepicker-input').data('daterangepicker').endDate.format('DD.MM.YYYY'));
        }
    }

    //Show table
    var pricesProcessing = false,
        showTable = function () {
            var wrapper = $('#restriction-overview-table-wrapper'),
                begin = $('#restriction-overview-filter-begin'),
                end = $('#restriction-overview-filter-end'),
                data = {
                    'begin': begin.val(),
                    'end': end.val(),
                    'roomTypes': $('#restriction-overview-filter-roomType').val(),
                    'tariffs': $('#restriction-overview-filter-tariff').val()
                },
                inputs = function () {
                    var input = $('input.mbh-grid-input, span.disabled-detector');
                    input.closest('td').click(function () {
                        var td = $("td[data-id='" + $(this).attr('data-id') + "']"),
                            field = $(this).children('span.input').children('input[disabled]');

                        td.children('span.input').children('input').removeAttr('disabled');

                        if (field.prop('type') === 'checkbox') {
                            field.prop('checked', !field.prop('checked')).css('checkbox-end');
                        } else {
                            field.focus();
                        }
                        td.children('span.input').children('span.disabled-detector').remove();
                    });
                    input.change(function () {
                        if (this.value === '') {
                            return;
                        }
                        var value = parseInt(this.value, 10);
                        if (value < 1 || isNaN(value)) {
                            this.value = 1;
                        }
                    });
                };
            if (wrapper.length === 0) {
                return false;
            }
            wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');
            if (!pricesProcessing) {
                $.ajax({
                    url: Routing.generate('restriction_overview_table'),
                    data: data,
                    beforeSend: function () { pricesProcessing = true; },
                    success: function (data) {
                        wrapper.html(data);
                        begin.val($('#restriction-overview-begin').val());
                        end.val($('#restriction-overview-end').val());
                        inputs();
                        pricesProcessing = false;
                        mbhGridCopy();
                    },
                    dataType: 'html'
                });
            }
        };

    showTable();
    $('.restriction-overview-filter').change(function () {
        showTable();
    });

    //generator
    (function () {
        var prices = $('input.delete-prices'),
            showMessage = function () {
                prices.each(function () {
                    var text = parseInt($(this).val(), 10) === -1 ? 'Цена будет удалена' : '';
                    $(this).closest('.col-md-4').
                        next('.col-md-6').
                        html('<span class="text-danger text-left input-errors">' + text +  '</span>');
                });
            };
        showMessage();
        prices.change(showMessage);
    }());
});
