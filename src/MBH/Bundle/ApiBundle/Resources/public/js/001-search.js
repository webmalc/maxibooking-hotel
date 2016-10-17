/*global window, $, document, selectpicker, Translator, mbh */
$(document).ready(function () {
    'use strict';
    $('.mbh-search-form select:not(.manual)').selectpicker({});

    //hotels & roomTypes
    (function () {
        var hotels = $('#mbh_api_search_type_hotels'),
            roomTypes = $('#mbh_api_search_type_roomTypes'),
            begin = $('#mbh_api_search_type_begin'),
            end = $('#mbh_api_search_type_end'),
            setRestrictions = function () {
                var prefix = false,
                    ids = [],
                    result = false;
                if (!$.isEmptyObject(roomTypes.val())) {
                    prefix = roomTypes.attr('data-type');
                    ids = roomTypes.val();
                } else if (!$.isEmptyObject(hotels.val())) {
                    prefix = 'allrooms_';
                    ids = hotels.val();
                }
                if (prefix === false) {
                    ids=[];
                }

                $.each(ids, function (key, id) {
                    var dates = mbh.restrictions[prefix + id];
                    if (dates) {
                        dates  = $.map(dates, function (val) { return val; });
                        result = result === false ? dates : result;
                        result = $.map(dates, function (val) {
                            return $.inArray(val, result) < 0 ? null : val;
                        });
                    } else {
                        result = [];
                        return;
                    }
                });

                begin.datepicker('setDatesDisabled', result);
                end.datepicker('setDatesDisabled', result);
            },
            setRoomTypes = function () {
                var hotelNames = $.map(hotels.find('option:selected'), function (val) {
                    return val.text;
                })
                roomTypes.find('optgroup').each(function () {
                    if (!$.isEmptyObject(hotelNames) && $.inArray($(this).prop('label'), hotelNames) === -1) {
                        $(this).find('option').prop('disabled', true);
                    } else {
                        $(this).find('option').prop('disabled', false);
                    }
                });
                roomTypes.selectpicker('refresh');
            };

        hotels.selectpicker({
            noneSelectedText: Translator.trans('api.search.form.all_hotels')
        });
        roomTypes.selectpicker({
            noneSelectedText: Translator.trans('api.search.form.all_rooms'),
            hideDisabled: true
        });
        hotels.change(function () {
            setRoomTypes();
            setRestrictions();
        });
        roomTypes.change(setRestrictions);
        setRestrictions();
        setRoomTypes();
    }());

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
