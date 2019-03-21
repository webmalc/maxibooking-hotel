/*global window, $, document */
window.addEventListener('load', function () {
    'use strict';
    // var locale = jQuery('#mbh-calendar-locale');
    // var currentLocale = locale.val();
    var currentLocale = document.documentElement.lang;
    var datePickerDefaultOptions = {dateFormat: "dd.mm.yy", firstDay: 1, isRTL: false};
    if (!currentLocale || currentLocale === 'ru') {
        Translator.locale = 'ru';
        jQuery.datepicker.regional.ru =
            {
                closeText: Translator.trans("online.online-calendar.close"),
                prevText: "&#x3c;" + Translator.trans("online.online-calendar.prev_abbr"),
                nextText: Translator.trans("online.online-calendar.next_abbr") + "&#x3e;",
                currentText: Translator.trans("online.online-calendar.today"),
                monthNames: [
                    Translator.trans("analytics.months.jan"),
                    Translator.trans("analytics.months.feb"),
                    Translator.trans("analytics.months.mar"),
                    Translator.trans("analytics.months.apr"),
                    Translator.trans("analytics.months.may"),
                    Translator.trans("analytics.months.jun"),
                    Translator.trans("analytics.months.jul"),
                    Translator.trans("analytics.months.aug"),
                    Translator.trans("analytics.months.sep"),
                    Translator.trans("analytics.months.okt"),
                    Translator.trans("analytics.months.nov"),
                    Translator.trans("analytics.months.dec")
                ],
                monthNamesShort: [
                    Translator.trans("analytics.months.jan_abbr"),
                    Translator.trans("analytics.months.feb_abbr"),
                    Translator.trans("analytics.months.mar_abbr"),
                    Translator.trans("analytics.months.apr_abbr"),
                    Translator.trans("analytics.months.may_abbr"),
                    Translator.trans("analytics.months.jun_abbr"),
                    Translator.trans("analytics.months.jul_abbr"),
                    Translator.trans("analytics.months.aug_abbr"),
                    Translator.trans("analytics.months.sep_abbr"),
                    Translator.trans("analytics.months.okt_abbr"),
                    Translator.trans("analytics.months.nov_abbr"),
                    Translator.trans("analytics.months.dec_abbr")
                ],
                dayNames: [
                    Translator.trans("analytics.days_of_week.sun"),
                    Translator.trans("analytics.days_of_week.mon"),
                    Translator.trans("analytics.days_of_week.tue"),
                    Translator.trans("analytics.days_of_week.wed"),
                    Translator.trans("analytics.days_of_week.thu"),
                    Translator.trans("analytics.days_of_week.fri"),
                    Translator.trans("analytics.days_of_week.sat")
                ],
                dayNamesShort: [
                    Translator.trans("online.online-calendar.sun_abbr"),
                    Translator.trans("online.online-calendar.mon_abbr"),
                    Translator.trans("online.online-calendar.tue_abbr"),
                    Translator.trans("online.online-calendar.wed_abbr"),
                    Translator.trans("online.online-calendar.thu_abbr"),
                    Translator.trans("online.online-calendar.fri_abbr"),
                    Translator.trans("online.online-calendar.sat_abbr")
                ],
                dayNamesMin: [
                    Translator.trans("online.online-calendar.sun_abbr_min"),
                    Translator.trans("online.online-calendar.mon_abbr_min"),
                    Translator.trans("online.online-calendar.tue_abbr_min"),
                    Translator.trans("online.online-calendar.wed_abbr_min"),
                    Translator.trans("online.online-calendar.thu_abbr_min"),
                    Translator.trans("online.online-calendar.fri_abbr_min"),
                    Translator.trans("online.online-calendar.sat_abbr_min")
                ]
            };
        jQuery.datepicker.setDefaults(jQuery.datepicker.regional.ru);
    }

    jQuery.datepicker.setDefaults(datePickerDefaultOptions);
    $('#mbh-calendar-datepicker').datepicker({
        minDate: new Date(),
        onSelect: function (date) {
            window.parent.postMessage({
                type: 'mbh',
                target: 'form',
                date: date
            }, "*");
        }
    });

    $('#mbh-calendar-close').click(function () {
        window.parent.postMessage({
            type: 'mbh',
            action: 'hideCalendar'
        }, "*");

    });

    var setDate = function (e) {
        if (e.data.type === 'mbh' && e.data.date) {
            $('#mbh-calendar-datepicker').datepicker('setDate', e.data.date);
        }
    };
    if (window.addEventListener) {
        window.addEventListener("message", setDate, false);
    }
    else {
        window.attachEvent("onmessage", setDate);
    }


});
