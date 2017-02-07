/*global window, $, document, Routing*/
/*jslint regexp: true */
$(document).ready(function () {
    'use strict';
    // set user Date (ClientConfig)
    if($('.daterangepicker-input').prev().is('#room-overview-filter-begin')){
        $('.daterangepicker-input').data('daterangepicker').setStartDate(moment(mbh.startDatePick, "DD.MM.YYYY").toDate());
        $('.daterangepicker-input').data('daterangepicker').setEndDate(moment(mbh.startDatePick, "DD.MM.YYYY").day(+45).toDate());
        $('#room-overview-filter-begin').val($('.daterangepicker-input').data('daterangepicker').startDate.format('DD.MM.YYYY'));
        $('#room-overview-filter-end').val($('.daterangepicker-input').data('daterangepicker').endDate.format('DD.MM.YYYY'));
    }
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
            wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');
            if (!pricesProcessing) {
                $.ajax({
                    url: Routing.generate('room_overview_table'),
                    data: data,
                    beforeSend: function () { pricesProcessing = true; },
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
});
