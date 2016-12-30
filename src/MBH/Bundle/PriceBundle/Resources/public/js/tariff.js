/*global window, $, services, document, datepicker, deleteLink, Routing, mbh */

var docReadyTariff = function () {
    'use strict';

    var $tariffForm = $('#tariff-form');

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
                    _token: $tariffForm.find('#form__token').val()
                };
                console.log(requestData);
                return requestData;
            }
        }
    } );

    $tariffTable.dataTable().fnSetFilteringDelay();

    $tariffForm.find('input, select').on('change', function () {
        $tariffTable.dataTable().fnDraw();
    });
}

