/*jslint todo: true */
/*global window, $, document, Routing */
$(document).ready(function () {
    'use strict';

    //Show table
    var porterProcessing = false,
        $wrapper = $('#porter-report-table-wrapper'),
        $begin = $('#porter-report-filter-begin'),
        $end = $('#porter-report-filter-end'),
        showTable = function () {
            var data = {
                //begin: $begin.val(),
                //end: $end.val()
                type: $('#input-type').val()
            };
            if ($wrapper.length === 0) {
                return false;
            }
            $wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');

            if (!porterProcessing) {
                $.ajax({
                    url: Routing.generate('report_porter_table'),
                    data: data,
                    beforeSend: function () {
                        porterProcessing = true;
                    },
                    success: function (data) {
                        $wrapper.html(data);
                        porterProcessing = false;
                        $("[data-toggle='tooltip']").tooltip();
                    },
                });
            }
        };

    showTable();
    /*$('.porter-report-filter').on('change', function () {
        showTable();
    });*/

});

