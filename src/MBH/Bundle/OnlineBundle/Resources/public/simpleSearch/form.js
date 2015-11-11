/*global window, jQuery, document */
jQuery(document).ready(function () {
    jQuery(function (a) {
        a.datepicker.regional.ru = {closeText: "Закрыть", prevText: "&#x3c;Пред", nextText: "След&#x3e;", currentText: "Сегодня", monthNames: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"], monthNamesShort: ["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"], dayNames: ["воскресенье", "понедельник", "вторник", "среда", "четверг", "пятница", "суббота"], dayNamesShort: ["вск", "пнд", "втр", "срд", "чтв", "птн", "сбт"], dayNamesMin: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"], dateFormat: "dd.mm.yy", firstDay: 1, isRTL: false};
        a.datepicker.setDefaults(a.datepicker.regional.ru)
    });
    var wrapper = jQuery('#mbh-form-wrapper');

    var begin = jQuery('#mbh-form-begin'),
        end = jQuery('#mbh-form-end'),
        nights = jQuery('#mbh-form-nights'),
        adults = jQuery('#mbh-form-adults'),
        children = jQuery('#mbh-form-children'),
        highway = jQuery('#mbh-form-highway'),
        button = jQuery('#mbh-form-submit'),
        options = {
            minDate: 0
        },
        setValue = function (field, val) {
            if(val && field.length) {
                field.val(val);
            }
        },
        getUrlVars = function () {
            var vars = [], hash;
            var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
            for (var i = 0; i < hashes.length; i++) {
                hash = hashes[i].split('=');
                vars.push(hash[0]);
                vars[hash[0]] = decodeURIComponent(hash[1]);
            }
            return vars;
        },
        query = getUrlVars()
        ;
    begin.datepicker(options);
    end.datepicker(options);

    // nights
    if (nights.length) {
        jQuery('#mbh-form-nights, #mbh-form-begin').change(function() {

            var beginDate = begin.datepicker("getDate");
            var endDate =  beginDate;
            endDate.setDate(endDate.getDate() + parseInt(nights.val(), 10));
            end.datepicker('setDate', endDate);
        });

    }
    var urlVars = getUrlVars();
    /*for(var key in urlVars) {
        var value = urlVars[key];
        wrapper.find('input[name="' + key + '"]').val(value);
        wrapper.find('select[name="' + key + '"]').val(value);
    }*/
    /*setValue(begin, query.begin);
    setValue(end, query.end);
    setValue(jQuery('#mbh-form-roomType'), query.roomType);
    setValue(highway, query.highway);
    setValue(jQuery('#mbh-form-hotel'), query.hotel);
    setValue(adults, query.adults);
    setValue(children, query.children);
    setValue(nights, query.nights);*/

    begin.change(function () {
        var beginDate = begin.datepicker("getDate"),
            endDate = end.datepicker("getDate")
            ;
        if (!beginDate) {
            return false;
        }

        if (endDate < beginDate) {
            end.val(null);
        }
        end.datepicker("option", {'minDate': beginDate});

        if (!endDate) {
            end.datepicker("option", {'minDate': beginDate});
        }
        //beginDate.setDate(beginDate.getDate() + 1);
        //end.datepicker('setDate', beginDate);
    });

    end.change(function () {
        var beginDate = begin.datepicker("getDate"),
            endDate = end.datepicker("getDate")
            ;
        if (!endDate) {
            return false;
        }

        if (beginDate > endDate) {
            begin.val(null);
        }
        begin.datepicker("option", {'maxDate': endDate});
    });

    if (adults.length) {
        adults.change(function () {
            var val = parseInt(adults.val());
            if (isNaN(val)) {
                adults.val(1);
            } else {
                adults.val(val);
            }
        });
    }
    if (children.length) {
        children.change(function () {
            var val = parseInt(children.val());
            if (isNaN(val)) {
                children.val(0);
            } else {
                children.val(val);
            }
        });
    }

    jQuery('#mbh-form-begin, #mbh-form-end').change(function () {
        if (begin.val() && end.val()) {
            button.prop('disabled', false);
        } else {
            button.prop('disabled', true);
        }
    });

    if (!begin.val() || !end.val()) {
        button.prop('disabled', true);
    }
})

