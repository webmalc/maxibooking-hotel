/*global window, $, document, Routing*/
/*jslint regexp: true */
$(document).ready(function () {

    'use strict';
    //Show table
    var usersProcessing = false,
        showTable = function () {
            var wrapper = $('#users-report-table-wrapper'),
                begin = $('#users-report-filter-begin'),
                end = $('#users-report-filter-end'),
                data = {
                    'begin': begin.val(),
                    'end': end.val(),
                    'roomTypes': $('#users-report-filter-roomType').val(),
                    'tariffs': $('#users-report-filter-tariff').val(),
                    'users': $('#users-report-filter-users').val(),
                };
            if (wrapper.length === 0) {
                return false;
            }
            wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');

            if (!usersProcessing) {
                $.ajax({
                    url: Routing.generate('report_users_table'),
                    data: data,
                    beforeSend: function () {
                        usersProcessing = true;
                    },
                    success: function (data) {
                        wrapper.html(data);
                        usersProcessing = false;
                    },
                    dataType: 'html'
                });
            }
        };

    showTable();
    $('.users-report-filter').change(function () {
        showTable();
    });
});
