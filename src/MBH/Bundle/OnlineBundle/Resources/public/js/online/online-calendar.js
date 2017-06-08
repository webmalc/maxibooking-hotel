/*global window, $, document */
$(document).ready(function () {
    'use strict';
    var locale = jQuery('#mbh-calendar-locale');
    var currentLocale = locale.val();
    
    var datePickerDefaultOptions = {dateFormat: "dd.mm.yy", firstDay: 1, isRTL: false};
    if (!currentLocale || currentLocale == 'ru') {
        jQuery.datepicker.regional.ru = {closeText: "Закрыть", prevText: "&#x3c;Пред", nextText: "След&#x3e;", currentText: "Сегодня", monthNames: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"], monthNamesShort: ["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"], dayNames: ["воскресенье", "понедельник", "вторник", "среда", "четверг", "пятница", "суббота"], dayNamesShort: ["вск", "пнд", "втр", "срд", "чтв", "птн", "сбт"], dayNamesMin: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"]};
        jQuery.datepicker.setDefaults(jQuery.datepicker.regional.ru);
    }

    jQuery.datepicker.setDefaults(datePickerDefaultOptions);
    $('#mbh-calendar-datepicker').datepicker({
        onSelect: function (date) {
            window.parent.postMessage({
                type: 'mbh',
                target: 'form',
                date: date
            }, "*");
        }
    });

    $('#mbh-calendar-close').click(function(){
        window.parent.postMessage({
            type: 'mbh',
            action: 'hideCalendar'
        }, "*");
        
    });

    var setDate = function (e) {
        if (e.data.type == 'mbh' && e.data.date) {
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
