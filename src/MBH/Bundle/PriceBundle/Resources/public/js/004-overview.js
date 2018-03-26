/*global window, $, document, Routing, mbh*/
/*jslint regexp: true */
$(document).ready(function () {
    'use strict';

    //Show table
    var pricesProcessing = false,
        showTable = function () {
            var wrapper = $('#room-overview-table-wrapper'),
                begin = $('#room-overview-filter-begin'),
                end = $('#room-overview-filter-end'),
                data = {
                    'begin': begin.val(),
                    'end': end.val(),
                    'roomTypes': $('#room-overview-filter-roomType').val(),
                    'tariffs': $('#room-overview-filter-tariff').val()
                };
            if (wrapper.length === 0) {
                return false;
            }
            wrapper.html(mbh.loader.html);
            if (!pricesProcessing) {
                $.ajax({
                    url: Routing.generate('room_overview_table'),
                    data: data,
                    beforeSend: function () {
                        pricesProcessing = true;
                    },
                    success: function (data) {
                        wrapper.html(data);
                        pricesProcessing = false;
                        $('td.alert').each(function () {
                            $("td[data-id='" + $(this).attr('data-id') + "']").addClass('alert');
                        });
                    },
                    dataType: 'html'
                });
            }
        };

    showTable();
    $('.room-overview-filter').change(function () {
        showTable();
    });
    if (document.getElementById('total-overview-table-wrapper')) {
        handleTotalRoomsOverview();
    }
});

function handleTotalRoomsOverview() {
    var $filterBegin = $('#total-overview-filter-begin');
    var $filterEnd = $('#total-overview-filter-end');
    var $wrapper = $('#total-overview-table-wrapper');
    $filterBegin.add($filterEnd).on('changeDate', function () {
        $wrapper.html(mbh.loader.html);
        $.ajax({
            url: Routing.generate('total_rooms_overview_table', {
                begin: $filterBegin.val(),
                end: $filterEnd.val()
            }),
            success: function (response) {
                $wrapper.html(response);
                handleFloatingHeaders($wrapper);
            }
        });
    });
    handleFloatingHeaders($wrapper);
}

function handleFloatingHeaders($wrapper) {
    var $floatingHeaders = $('.floating-header');
    var $firstFloatingHeader = $floatingHeaders.first();
    var headerSpanLeftOffset = ($(window).width() - $firstFloatingHeader.offset().left - parseInt($firstFloatingHeader.css('width'), 10)) / 2;

    var setLeftScroll = function () {
        var leftScroll = $wrapper.scrollLeft();
        $floatingHeaders.css('left', headerSpanLeftOffset + leftScroll);
    };
    setLeftScroll();
    $floatingHeaders.css('visibility', 'visible');
    $wrapper.scroll(function() {
        setLeftScroll();
    });
}
