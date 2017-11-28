/*global window, $, document, Routing, mbhGridCopy*/

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
    $('.room-cache-overview-filter').on('change switchChange.bootstrapSwitch', function () {
        showTable();
    });

    //generator
    (function () {
        var rooms = $('input.delete-rooms'),
            quotas = $('#mbh_bundle_pricebundle_room_cache_generator_type_quotas'),
            showMessage = function () {
                rooms.each(function () {
                    var text = parseInt($(this).val(), 10) === -1 ? Translator.trans("004-roomCache.days_will_be_removed") : '';
                    $(this).closest('.col-sm-6').
                        next('.col-sm-4').
                        html('<span class="text-danger text-left input-errors">' + text +  '</span>');
                });
            },
            showTariffs = function () {
                var tariffs = $('#mbh_bundle_pricebundle_room_cache_generator_type_tariffs').closest('div.form-group');
                tariffs.toggle(quotas.prop('checked'));
            };

        showTariffs();
        showMessage();
        rooms.change(showMessage);
        quotas.on('change switchChange.bootstrapSwitch', showTariffs);
    }());
});
