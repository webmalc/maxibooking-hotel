/*global window, $, document, Routing*/
/*jslint regexp: true */
$(document).ready(function () {
    'use strict';
    //Show table
    var processing = false,
        showTable = function () {
            var wrapper = $('#room-cache-overview-table-wrapper'),
                begin = $('#room-cache-overview-filter-begin'),
                end = $('#room-cache-overview-filter-end'),
                data = {
                    'begin': begin.val(),
                    'end': end.val(),
                    'roomTypes': $('#room-cache-overview-filter-roomType').val()
                },
                inputs = function () {
                    var input = $('input.mbh-grid-input');
                    input.closest('td').click(function () {
                        $(this).children('input').removeAttr('disabled').focus().select();
                    });

                    input.change(function () {
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
            if (!processing) {
                $.ajax({
                    url: Routing.generate('room_cache_overview_table'),
                    data: data,
                    beforeSend: function () { processing = true; },
                    success: function (data) {
                        wrapper.html(data);
                        begin.val($('#room-cache-overview-begin').val());
                        end.val($('#room-cache-overview-end').val());
                        inputs();

                    },
                    dataType: 'html'
                });
            }
        };

    showTable();
    $('.room-cache-overview-filter').change(function () {
        showTable();
    });
});
