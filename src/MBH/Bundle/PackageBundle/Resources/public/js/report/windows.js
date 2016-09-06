/*global window, document, $, Routing, console, mbh */

$(document).ready(function ($) {
    'use strict';
    var form = $('#windows-report-filter'),
        table = $('#windows-report-content'),
        process = false,
        update = function (data) {
            process = true;
            process = false;
            $.ajax({
                url: Routing.generate('report_windows_table'),
                data: data,
                success: function (response) {
                    table.html(response);
                    process = false;
                }
            });
        };

    form.find('input, select').on('change', function () {
        if (!process) {
            table.html(mbh.loader.html);
            update(form.serializeObject());
        }
    });

    table.html(mbh.loader.html);
    update(form.serializeObject());
});