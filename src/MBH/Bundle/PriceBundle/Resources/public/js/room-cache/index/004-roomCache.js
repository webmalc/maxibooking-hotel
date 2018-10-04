/*global window, $, document, Routing, mbhGridCopy*/
var mbh_restrictForDateRangePicker = true;
$(document).ready(function () {
    'use strict';

    //Show table
    var roomProcessing = false,
        showTable = function () {
            var wrapper = $('#room-cache-overview-table-wrapper'),
                begin = $('#room-cache-overview-filter-begin'),
                end = $('#room-cache-overview-filter-end'),
                graph = $('#room-cache-overview-filter-graph'),
                route = graph.is(':checked') ? 'room_cache_overview_graph' : 'room_cache_overview_table',
                data = {
                    'begin': begin.val(),
                    'end': end.val(),
                    'roomTypes': $('#room-cache-overview-filter-roomType').val(),
                    'tariffs': $('#room-cache-overview-filter-tariff').val()
                },
                inputs = function () {
                    var input = $('input.mbh-grid-input, span.disabled-detector');
                    input.closest('tr').click(function (event) {
                        var elementTd = $(event.target);

                        if (event.target.localName !== 'td') {
                            elementTd = $(event.target).closest('td');
                        }

                        var tr = $("tr[data-edit-col='" + $(this).attr('data-edit-col') + "']"),
                            td = $(tr).find("td[data-id='" + $(elementTd).attr('data-id') + "']"),
                            field = $(elementTd).children('span.input').find('input[disabled]');

                        td.children('span.input').find('input').removeAttr('disabled');

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
            wrapper.html(mbh.loader.html);
            if (!roomProcessing) {
                $.ajax({
                    url: Routing.generate(route),
                    data: data,
                    beforeSend: function () {roomProcessing = true; },
                    success: function (data) {
                        wrapper.html(data);
                        begin.val($('#room-cache-overview-begin').val());
                        end.val($('#room-cache-overview-end').val());
                        inputs();
                        roomProcessing = false;
                        mbhGridCopy();
                    },
                    dataType: 'html'
                });
            }
        };

    showTable();
    $('.room-cache-overview-filter').not('.select2').on('change switchChange.bootstrapSwitch', function () {
        showTable();
    });
    $('select.room-cache-overview-filter').on('select2:unselect  select2:select', function () {
        setTimeout(function () {
            showTable();
        }, 100);
    });
});
