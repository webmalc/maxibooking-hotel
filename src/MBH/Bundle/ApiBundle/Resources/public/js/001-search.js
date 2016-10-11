/*global window, $, document, selectpicker */
$(document).ready(function () {
    'use strict';
    $('.mbh-search-form select').selectpicker();

    //begin and end dates
    (function () {
        var begin = $('#mbh_api_search_type_begin'),
            end = $('#mbh_api_search_type_end');
        end.datepicker({startDate: new Date(Date.now() + 24 * 60 * 60 * 1000)})
            .inputmask('dd.mm.yyyy', {"placeholder": "ДД.ММ.ГГГГ"});
        begin.datepicker({startDate: new Date()}).inputmask('dd.mm.yyyy', {"placeholder": "ДД.ММ.ГГГГ"});

        begin.on('change', function () {
            if (end.datepicker('getDate') && begin.datepicker('getDate') && begin.datepicker('getDate') >= end.datepicker('getDate')) {
                end.datepicker('setDate', new Date(begin.datepicker('getDate').getTime() + 24 * 60 * 60 * 1000));
            }
        });
        end.on('change', function () {
            if (end.datepicker('getDate') && begin.datepicker('getDate') && end.datepicker('getDate') <= begin.datepicker('getDate')) {
                begin.datepicker('setDate', new Date(end.datepicker('getDate').getTime() - 24 * 60 * 60 * 1000));
            }
        });
    }());
});
