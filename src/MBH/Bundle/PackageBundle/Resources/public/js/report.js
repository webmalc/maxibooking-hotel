/*global window, $ */
$(document).ready(function() {
    'use strict';

    // set accommodation package bg color
    var setPackageBg = function () {
        var div = $('#accommodation-report-table').find('div.package').each(function(){
            $(this).css('background-color', $(this).css('color'));
        });
    };
    setPackageBg();

    // set accommodation  roomType paging
    var accommodationPagingSet = function () {
        $('#accommodation-report-pagination').find('a').each( function() {
            $(this).click(function (e) {
                e.preventDefault();
                accommodationReportGet({'page': $(this).attr('data-page')});
            })
        });
    };

    // set accommodation  roomType month paging
    var accommodationMonthsSet = function () {
        $('#accommodation-report-table-row').find('div.arrow a').each( function() {
            $(this).click(function (e) {
                e.preventDefault();
                $('#accommodation-report-filter-begin').val($(this).attr('data-begin'));
                accommodationReportGet({'begin': $(this).attr('data-begin')});
            })
        });
    };

    // set rooms  roomType month paging
    var roomsMonthsSet = function () {
        $('#rooms-report-table-row').find('div.arrow a').each( function() {
            $(this).click(function (e) {
                e.preventDefault();
                $('#rooms-report-filter-begin').val($(this).attr('data-begin'));
                roomsReportGet({'begin': $(this).attr('data-begin')});
            })
        });
    };

    // select cells for package search
    var roomsSearchSelect = function () {
        $('td.cell-empty small').click(function() {

            var begin = $(this).closest('td').prevAll('.package-select-begin');

            if (begin.length) {
                $('td.cell-empty').removeClass('package-select-begin').addClass('black');
                //alert(begin.attr('data-roomType'));
                var query = '#s[begin]=' + begin.attr('data-date') + '&s[end]=' + $(this).closest('td').attr('data-date') + '&s[adults]=0&s[children]=0'

                if (begin.attr('data-roomType') != 'all') {
                    query += '&s[roomType][0]=' + begin.attr('data-roomType');
                }

                window.open(Routing.generate('package_search') + query);
                return true;
            }

            $('td.cell-empty').removeClass('package-select-begin').addClass('black');
            $(this).closest('td').addClass('package-select-begin');
            $(this).closest('td').prevAll().removeClass('black');
        });
    }

    // get accommodation report content
    var accommodationReportGet = function (data) {
        var data = (typeof data !== 'undefined') ? data : {};
        var wrapper = $('#accommodation-report-content');

        if (wrapper.length === 0) {
            return false;
        }

        wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');

        if (!data.begin) {
            data.begin = $('#accommodation-report-filter-begin').val();
        }
        data.roomType = $('#accommodation-report-filter-roomType').val();

        $.ajax({
            url: Routing.generate('report_accommodation_table'),
            data: data,
            success: function (data) {
                wrapper.html(data);
                setPackageBg();
                accommodationPagingSet();
                accommodationMonthsSet();
                $('a[data-toggle="tooltip"], li[data-toggle="tooltip"], span[data-toggle="tooltip"], i[data-toggle="tooltip"]').tooltip();

            },
            dataType: 'html'
        });
    }
    accommodationReportGet();
    $('.accommodation-report-filter').change(function () { accommodationReportGet() });

    // get rooms report content
    var roomsReportGet = function (data) {
        var data = (typeof data !== 'undefined') ? data : {};
        var wrapper = $('#rooms-report-content');

        if (wrapper.length === 0) {
            return false;
        }

        wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');

        if (!data.begin) {
            data.begin = $('#rooms-report-filter-begin').val();
        }
        data.roomType = $('#rooms-report-filter-roomType').val();
        data.tariff = $('#rooms-report-filter-tariff').val();

        $.ajax({
            url: Routing.generate('rooms_accommodation_table'),
            data: data,
            success: function (data) {
                wrapper.html(data);
                roomsMonthsSet();
                roomsSearchSelect();
                $('[data-toggle="tooltip"]').tooltip();
            },
            dataType: 'html'
        });
    }
    roomsReportGet();
    $('.rooms-report-filter').change(function () { roomsReportGet() });
});

