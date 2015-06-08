/*global document, window, $ */
$(document).ready(function () {
    'use strict';
    $('#documents-table').dataTable({
        pageLength: 10,
        order: [[4, 'desc']],
        info: true,
        stateSave: true,
        "bLengthChange": false
    });
});