/*global window, $, services, document, datepicker, deleteLink, Routing, mbh */

var docReadyTariff = function () {
    'use strict';

    var $tariffForm = $('#mbh_filter_form');

    var $tariffTable = $('#tariff-table');

    $tariffTable.dataTable( {
        serverSide: true,
        processing: true,
        ordering: false,
        ajax: {
            url: Routing.generate('tariff_json'),
            "method": "POST",
            data: function (requestData) {
                requestData.form = {
                    begin: $tariffForm.find('#form_begin').val(),
                    end: $tariffForm.find('#form_end').val(),
                    isOnline: $tariffForm.find('#mbh_filter_form_isOnline').val(),
                    isEnabled: $tariffForm.find('#mbh_filter_form_isEnabled').val(),
                    _token: $tariffForm.find('#mbh_filter_form__token').val()
                };
                return requestData;
            }
        }
    } );

    $tariffTable.dataTable().fnSetFilteringDelay();

    $tariffForm.find('input, select').on('change', function () {
        $tariffTable.dataTable().fnDraw();
    });
}
