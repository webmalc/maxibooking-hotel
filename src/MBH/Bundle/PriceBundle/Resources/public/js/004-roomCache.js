/*global window, $, document, Routing*/
/*jslint regexp: true */
$(document).ready(function () {
    'use strict';
    //Show table
    var roomProcessing = false,
        showTable = function () {
            var wrapper = $('#room-cache-overview-table-wrapper'),
                begin = $('#room-cache-overview-filter-begin'),
                end = $('#room-cache-overview-filter-end'),
                data = {
                    'begin': begin.val(),
                    'end': end.val(),
                    'roomTypes': $('#room-cache-overview-filter-roomType').val(),
                    'tariffs': $('#room-cache-overview-filter-tariff').val()
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
                        if (value < 0 || isNaN(value)) {
                            this.value = 0;
                        }
                    });
                };

            if (wrapper.length === 0) {
                return false;
            }
            wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');
            if (!roomProcessing) {
                $.ajax({
                    url: Routing.generate('room_cache_overview_table'),
                    data: data,
                    beforeSend: function () {roomProcessing = true; },
                    success: function (data) {
                        wrapper.html(data);
                        begin.val($('#room-cache-overview-begin').val());
                        end.val($('#room-cache-overview-end').val());
                        inputs();
                        roomProcessing = false;

                    },
                    dataType: 'html'
                });
            }
        };

    showTable();
    $('.room-cache-overview-filter').change(function () {
        showTable();
    });
    //generator
    (function () {
        var rooms = $('input.delete-rooms'),
            showMessage = function () {
                rooms.each(function () {
                    var text = parseInt($(this).val(), 10) === -1 ? 'Дни будет удалены' : '';
                    $(this).closest('.col-md-4').
                        next('.col-md-6').
                        html('<span class="text-danger text-left input-errors">' + text +  '</span>');
                });
            };
        showMessage();
        rooms.change(showMessage);
    }());
});
