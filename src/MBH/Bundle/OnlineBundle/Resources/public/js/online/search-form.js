function searchFormActions() {
    var begin = jQuery('#mbh-form-begin'),
        end = jQuery('#mbh-form-end'),
        nights = jQuery('#mbh-form-nights'),
        adults = jQuery('#mbh-form-adults'),
        children = jQuery('#mbh-form-children'),
        button = jQuery('#mbh-form-submit'),
        locale = jQuery('#mbh-form-locale'),
        last = null,
        options = {
            minDate: 0
        };

    var processMessage = function(e) {
        if (e.data.type !== 'mbh') {
            return;
        }
        if (e.data.date && last) {
            last.val(e.data.date).trigger('change');
            window.parent.postMessage({
                type: 'mbh',
                action: 'hideCalendar'
            }, "*");
        }
    };

    var $mbhForm = $("#mbh-form");
    $mbhForm.on('submit', function(e) {
        window.parent.postMessage({
            type: 'form-event',
            purpose: 'search'
        }, "*");
        window.sessionStorage.setItem('MBHSearchData',$(this).serialize());
    });

    var resizeHandler = function () {
        var formHeight = document.getElementById('mbh-form-wrapper').clientHeight;
        window.parent.postMessage({
            type: 'mbh',
            action: 'formResize',
            formHeight: formHeight
        }, '*')
    };
    setInterval(function() {
        resizeHandler();
    }, 300);

    if (window.addEventListener) {
        window.addEventListener("message", processMessage, false);
    } else {
        window.attachEvent("onmessage", processMessage);
    }

    var needChangePaddingLeft = (function() {
        var need = false;

        if (window['isMobileDevice'] !== undefined && isMobileDevice()) {
            need = true
        }

        return function() {
            return need;
        }
    })();

    var paddingLeft = function(left) {
        var widthDatePicker = 310;

        return (function() {
            if (needChangePaddingLeft()) {
                var halfWidthScreen = window.screen.width / 2;

                if (halfWidthScreen - widthDatePicker < 30 ) {
                    return halfWidthScreen - (widthDatePicker / 2 );
                }
            }

            return left;
        })();
    };

    var showCalendar = function() {
        var el = jQuery(this);
        last = el;
        window.parent.postMessage({
            type: 'mbh',
            action: 'showCalendar',
            top: el.offset().top + el.outerHeight(),
            left: paddingLeft(el.offset().left),
            date: el.val()
        }, "*");
    };

    var hideCalendar = function(e, exception) {
        if ((exception && !jQuery(e.target).hasClass(exception)) || !exception) {
            calendarIframe.css('display', 'none');
        }
    };

    begin.on('focus', showCalendar);
    end.on('focus', showCalendar);
    jQuery('html').click(function(e) {
        if (!jQuery(e.target).hasClass('mbh-calendar-input')) {
            window.parent.postMessage({
                type: 'mbh',
                action: 'hideCalendar'
            }, "*");
        }
    });

    var currentLocale = locale.val();

    var setValue = function(field, val) {
        if (val && field.length) {
            field.val(val);
        }
    };

    var query = (function() {
        var vars = [],
            tempChildrenAges = [],
            hash,
            hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');

        vars['children-ages'] = [];

        for (var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            if (/children-ages/.test(hash[0])) {
                tempChildrenAges.push(hash[1])
            } else {
                vars.push(hash[0]);
                vars[hash[0]] = hash[1];
            }

        }

        if (parseInt(vars['children']) > 0) {
            vars['children-ages'] = tempChildrenAges;
        }

        return vars;
    })();

    if (!begin.val() || !end.val()) {
        button.prop('disabled', true);
    }
    // nights
    if (nights.length) {
        jQuery('#mbh-form-nights, #mbh-form-begin').change(function() {

            var beginDate = jQuery.datepicker.parseDate('dd.mm.yy', begin.val());
            if (!beginDate) {
                return;
            }
            var endDate = beginDate;
            endDate.setDate(endDate.getDate() + parseInt(nights.val(), 10));
            end.val(jQuery.datepicker.formatDate( "dd.mm.yy", endDate));
        });

    }

    setValue(begin, query.begin);
    setValue(end, query.end);
    setValue(jQuery('#mbh-form-roomType'), query.roomType);
    setValue(jQuery('#mbh-form-hotel'), query.hotel);
    setValue(adults, query.adults);
    setValue(children, query.children);
    setValue(nights, query.nights);

    begin.change(function() {
        var beginDate = jQuery.datepicker.parseDate('dd.mm.yy', begin.val()),
            endDate = jQuery.datepicker.parseDate('dd.mm.yy', end.val());

        if (!beginDate) {
            return false;
        }

        if (endDate < beginDate) {
            end.val(null);
        }
    });

    end.change(function() {
        var beginDate = jQuery.datepicker.parseDate('dd.mm.yy', begin.val()),
            endDate = jQuery.datepicker.parseDate('dd.mm.yy', end.val());

        if (!endDate) {
            return false;
        }

        if (beginDate > endDate) {
            begin.val(null);
        }
    });

    if (adults.length) {
        adults.change(function() {
            var val = parseInt(adults.val());
            if (isNaN(val)) {
                adults.val(1);
            } else {
                adults.val(val);
            }
        });
    }
    if (children.length) {
        children.bind('keyup mouseup change', function() {
            var val = parseInt(children.val());
            if (isNaN(val)) {
                children.val(0);
            } else {
                setChildAgeForms(val);
                children.val(val);
            }
        });
    }

    jQuery('#mbh-form-begin, #mbh-form-end').change(function() {
        if (begin.val() && end.val()) {
            button.prop('disabled', false);
        } else {
            button.prop('disabled', true);
        }
    });

    setChildAgeForms(children.val(), query);
}

function setChildAgeForms(childrenCount, query) {
    var $childAgesBlock = jQuery('.children-ages');

    if (isDisplayChildAges) {
        var childrenAges = (query !== undefined ? query['children-ages'] : false) || [];

        if (childrenCount > 0) {
            $childAgesBlock.show();
        } else if ($childAgesBlock) {
            $childAgesBlock.hide();
            $childAgesBlock.find('select').val(0);
        }
        var selectFormCount = $childAgesBlock.find('select').size();
        var difference = childrenCount - selectFormCount;
        if (difference > 0) {
            for (var i = selectFormCount; i < childrenCount; i++) {
                var $childrenAgeForm = jQuery('#children-age-1').clone(),
                    selectFormName = 'children-age-' + (i + 1);
                $childrenAgeForm.attr('id', selectFormName);
                $childAgesBlock.append($childrenAgeForm);
            }
        } else if (difference < 0) {
            while (childrenCount != selectFormCount && $childAgesBlock.find('select').size() > 1) {
                $childAgesBlock.find('select').last().remove();
                selectFormCount--;
            }
        }

        if (childrenAges.length !== 0) {
            $childAgesBlock.find('select').each(function(index, select) {
                jQuery(select).val(childrenAges[index]);
            });
        }
        
    } else {
        $childAgesBlock.hide();
    }
}