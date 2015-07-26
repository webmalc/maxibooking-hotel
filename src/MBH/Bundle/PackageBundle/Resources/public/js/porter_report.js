/*jslint todo: true */
/*global window, $, document, Routing */
$(document).ready(function () {
    'use strict';

    //Show table
    var porterProcessing = false,
        showTable = function () {
            var wrapper = $('#porter-report-table-wrapper'),
                begin = $('#porter-report-filter-begin'),
                end = $('#porter-report-filter-end'),
                data = {
                    'begin': begin.val(),
                    'end': end.val()
                };
            if (wrapper.length === 0) {
                return false;
            }
            wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');

            if (!porterProcessing) {
                $.ajax({
                    url: Routing.generate('report_porter_table'),
                    data: data,
                    beforeSend: function () {
                        porterProcessing = true;
                    },
                    success: function (data) {
                        wrapper.html(data);
                        porterProcessing = false;
                        $("[data-toggle='tooltip']").tooltip();
                    },
                    dataType: 'html'
                });
            }
        };

    showTable();
    $('.porter-report-filter').change(function () {
        showTable();
    });

});

