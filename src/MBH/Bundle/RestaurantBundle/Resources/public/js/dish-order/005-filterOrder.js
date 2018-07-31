/*global window, $, services, document, datepicker, deleteLink, Routing, mbh */
$(document).ready(function () {
    'use strict';
    var $dishOrderForm = $('#dishorder-form'),
        $dishOrderTable = $('#dishorder-table');

    $dishOrderTable.dataTable({
        language    : mbh.datatablesOptions.language,
        pageLength  : mbh.datatablesOptions.pageLength,
        "drawCallback": function (settings) {
            var html;
            if (settings.json.restaurant_order_total) {
                html = $.number(settings.json.restaurant_order_total, 2) + ' ';
            } else {
                html = false;
            }
            $('#order-summary-total').html( html || '-');
        },
        processing: true,
        serverSide: true,
        ordering: true,
        ajax: {
            method: "POST",
            url: Routing.generate('restaurant_json'),
            data: function (requestData) {
                requestData.form = {
                    begin: $dishOrderForm.find("#restaurant_dishorder_filter_type_begin").val(),
                    end: $dishOrderForm.find("#restaurant_dishorder_filter_type_end").val(),
                    // money_begin: $dishOrderForm.find("#restaurant_dishorder_filter_type_money_begin").val(),
                    // money_end: $dishOrderForm.find("#restaurant_dishorder_filter_type_money_end").val(),
                    is_freezed: $dishOrderForm.find("#restaurant_dishorder_filter_type_is_freezed").val(),
                    _token: $dishOrderForm.find("#restaurant_dishorder_filter_type__token").val()
                };
                return requestData;
            }
        },
        columns: [
            {"orderable": false},
            {"name": 'id'},
            {"orderable": false, "className": "text-center"},
            {"name": 'createdAt'},
            {"orderable": false, "className": "text-right"},
            {"name": 'isFreezed'},
            {"orderable": false}
        ]


    });

    $dishOrderTable.dataTable().fnSetFilteringDelay();

    $dishOrderForm.find('input, select').on('change', function () {
        $dishOrderTable.dataTable().fnDraw();
    });

});