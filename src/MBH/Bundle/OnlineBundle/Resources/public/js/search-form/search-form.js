function SearchForm(widthIframeWithDatepiker) {
    this.widthIframeWithDatepiker = parseInt(widthIframeWithDatepiker.replace('px', '')) || 310;

    this.query = (function() {
        var vars = [],
            tempChildrenAges = [],
            hash,
            hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');

        for (var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            if (/children-ages/.test(hash[0])) {
                tempChildrenAges.push(parseInt(hash[1]))
            } else {
                vars.push(hash[0]);
                vars[hash[0]] = (hash[1] !== '' && !isNaN(hash[1])) ? parseInt(hash[1]) : hash[1];
            }

        }

        vars['children-ages'] = [];
        if (vars['children'] > 0 && tempChildrenAges.length === vars['children'] ) {
            vars['children-ages'] = tempChildrenAges;
        }

        return vars;
    })();

    this.adults = jQuery('#mbh-form-adults');
    this.children = jQuery('#mbh-form-children');

    this.begin = jQuery('#mbh-form-begin');
    this.end = jQuery('#mbh-form-end');
    this.nights = jQuery('#mbh-form-nights');
    this.button = jQuery('#mbh-form-submit');
    this.locale = jQuery('#mbh-form-locale');
    this.roomType = jQuery('#mbh-form-roomType');
    this.last = null;
    this.options = {
            minDate: 0
        };

    this.additionalFormDataInit();
}

SearchForm.prototype.additionalFormWriteAmountGuest = function(adults, children) {
    if (adults === undefined) {
        return;
    }
    children = children || 0;

    this.additionalFormStepper.val(parseInt(adults) + parseInt(children));
};

SearchForm.prototype.additionalFormDataInit = function () {

    this.additionalFormWrapper = jQuery('.mbh-form-additional-form-amount-guest-wrapper');
    this.additionalFormUseIt = this.additionalFormWrapper.length > 0 ? {} : null;

    this.additionalFormStepper = this.additionalFormWrapper.find('.additional-input');

    this.additionalFormWriteAmountGuest(this.query.adults, this.query.children);

    var dataForForm = (function(query) {

        return {
            adults: parseInt(query.adults),
            children: parseInt(query.children),
            roomType: query.roomType,
            'children-ages': query['children-ages']
        }

    })(this.query);

    window.parent.postMessage({
        type: 'mbh',
        target: 'additionalFromDataForParentForm',
        form: dataForForm
    }, "*");

};

SearchForm.prototype.setValue = function(field, val) {
    if (val !== undefined && field.length) {
        field.val(val);
    }
};

SearchForm.prototype.additionalDataSetValueFromIframe = function (e) {
    if (this.additionalFormUseIt === null) {
        return;
    }

    if (e.data.name !== 'additionalFromDataForIframe' || e.data.form === {}) {
        return;
    }

    this.additionalFormUseIt = e.data.form;

    this.additionalFormWriteAmountGuest(this.additionalFormUseIt.adults, this.additionalFormUseIt.children);

    this.setValue(this.adults, this.additionalFormUseIt.adults);
    this.setValue(this.children, this.additionalFormUseIt.children);
    this.roomType.val(this.additionalFormUseIt.roomType);

    this.setChildAgeForms(this.additionalFormUseIt.children,this.additionalFormUseIt);
};

SearchForm.prototype.processMessage = function(e) {
    if (e.data.type !== 'mbh') {
        return;
    }

    this.additionalDataSetValueFromIframe(e);

    if (e.data.date && this.last) {
        this.last.val(e.data.date).trigger('change');
        window.parent.postMessage({
            type: 'mbh',
            action: 'hideCalendar'
        }, "*");
    }
};

SearchForm.prototype.addEventListeners = function () {
    var _this = this;

    if (window.addEventListener) {
        window.addEventListener("message", function (e) {_this.processMessage(e);}, false);
    } else {
        window.attachEvent("onmessage", function (e) {_this.processMessage(e);});
    }

    var $mbhForm = $("#mbh-form");
    $mbhForm.on('submit', function(e) {
        window.parent.postMessage({
            type: 'form-event',
            purpose: 'search'
        }, "*");
        window.sessionStorage.setItem('MBHSearchData',$(this).serialize());
    });
};

SearchForm.prototype.searchFormActions  = function () {
    var _this = this;

    this.viewChange();
    this.addEventListeners();

    if (!this.begin.val() || !this.end.val()) {
        this.button.prop('disabled', true);
    }
    // nights
    if (this.nights.length) {
        jQuery('#mbh-form-nights, #mbh-form-begin').change(function() {

            var beginDate = jQuery.datepicker.parseDate('dd.mm.yy', _this.begin.val());
            if (!beginDate) {
                return;
            }
            var endDate = beginDate;
            endDate.setDate(endDate.getDate() + parseInt(_this.nights.val(), 10));
            _this.end.val(jQuery.datepicker.formatDate( "dd.mm.yy", endDate));
        });

    }

    this.setValue(this.begin, this.query.begin);
    this.setValue(this.end, this.query.end);
    this.setValue(this.adults, this.query.adults);
    this.setValue(this.children, this.query.children);
    this.setValue(this.nights, this.query.nights);

    this.roomType.val(this.query.roomType);
    jQuery('#mbh-form-hotel').val(this.query.hotel);

    this.begin.change(function() {
        var beginDate = jQuery.datepicker.parseDate('dd.mm.yy', _this.begin.val()),
            endDate = jQuery.datepicker.parseDate('dd.mm.yy', _this.end.val());

        if (!beginDate) {
            return false;
        }

        if (endDate < beginDate) {
            _this.end.val(null);
        }
    });

    this.end.change(function() {
        var beginDate = jQuery.datepicker.parseDate('dd.mm.yy', _this.begin.val()),
            endDate = jQuery.datepicker.parseDate('dd.mm.yy', _this.end.val());

        if (!endDate) {
            return false;
        }

        if (beginDate > endDate) {
            _this.begin.val(null);
        }
    });

    if (this.adults.length) {
        this.adults.change(function() {
            var val = parseInt(_this.adults.val());
            if (isNaN(val)) {
                _this.adults.val(1);
            } else {
                _this.adults.val(val);
            }
        });
    }

    if (this.children.length) {
        this.children.bind('keyup mouseup change', function() {
            var val = parseInt(_this.children.val());
            if (isNaN(val)) {
                _this.children.val(0);
            } else {
                _this.setChildAgeForms(val);
                _this.children.val(val);
            }
        });
    }

    jQuery('#mbh-form-begin, #mbh-form-end').change(function() {
        if (_this.begin.val() && _this.end.val()) {
            _this.button.prop('disabled', false);
        } else {
            _this.button.prop('disabled', true);
        }
    });

    this.setChildAgeForms(this.children.val(), this.query);
};

SearchForm.prototype.setChildAgeForms = function(childrenCount, query) {

    var $childAgesBlock = jQuery('.children-ages');

    if (!isDisplayChildAges) {
        $childAgesBlock.hide();

        return;
    }

    var childrenAges = (query !== undefined ? query['children-ages'] : false) || [];

    if (childrenCount > 0) {
        $childAgesBlock.show();
    } else if ($childAgesBlock) {
        $childAgesBlock.hide();
        $childAgesBlock.find('select').val(0);
    }
    var selectFormCount = $childAgesBlock.find('select').length;
    var difference = childrenCount - selectFormCount;
    if (difference > 0) {
        for (var i = selectFormCount; i < childrenCount; i++) {
            var $childrenAgeForm = jQuery('#children-age-1').clone(),
                selectFormName = 'children-age-' + (i + 1);
            $childrenAgeForm.attr('id', selectFormName);
            $childAgesBlock.append($childrenAgeForm);
        }
    } else if (difference < 0) {
        while (childrenCount != selectFormCount && $childAgesBlock.find('select').length > 1) {
            $childAgesBlock.find('select').last().remove();
            selectFormCount--;
        }
    }

    if (childrenAges.length !== 0) {
        $childAgesBlock.find('select').each(function(index, select) {
            jQuery(select).val(childrenAges[index]);
        });
    }
};

SearchForm.prototype.viewChange = function() {
    var _this = this;

    var resizeHandler = function () {
        var formHeight = document.getElementById('mbh-form-wrapper').clientHeight;
        window.parent.postMessage({
            type: 'mbh',
            action: 'formResize',
            formHeight: formHeight
        }, '*')
    };

    window.addEventListener('resize', resizeHandler);

    var resizeIntervalId = setInterval(function() {
        resizeHandler();
    }, 300);

    setTimeout(function() {
        clearInterval(resizeIntervalId);
    }, 1500);


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
        if (needChangePaddingLeft()) {
            var halfWidthScreen = window.screen.width / 2;

            if (halfWidthScreen - _this.widthIframeWithDatepiker < 30 ) {
                return halfWidthScreen - (_this.widthIframeWithDatepiker / 2 );
            }
        }

        return left;
    };

    var showIFrame = function(event) {
        var el = jQuery(this);
        _this.last = el;
        window.parent.postMessage({
            type: 'mbh',
            action: (event.data !== undefined && event.data.action !== undefined) ? event.data.action : 'showCalendar',
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

    this.begin.on('focus', showIFrame);
    this.end.on('focus', showIFrame);
    this.additionalFormWrapper.on('click', {action: 'showAdditionalForm'}, showIFrame);

    var eTarget, needHideAdditionalData;
    jQuery('html').click(function(e) {
        eTarget = jQuery(e.target);
        needHideAdditionalData = !eTarget.hasClass('mbh-form-additional-form-amount-guest-wrapper')
            && !eTarget.hasClass('additional-form-label')
            && !eTarget.hasClass('additional-input');

        if (!eTarget.hasClass('mbh-calendar-input')) {
            window.parent.postMessage({
                type: 'mbh',
                action: 'hideCalendar'
            }, "*");
        }

        if (needHideAdditionalData) {
            window.parent.postMessage({
                type: 'mbh',
                action: 'hideAdditionalForm'
            }, "*");
        }
    });
};

function changeColorMBLogo() {
    var label = document.querySelector('label[for="mbh-form-begin"]');
    if (!label) {
        return;
    }
    var color = getComputedStyle(label).color;

    if (!color) {
        return;
    }
    document.documentElement.style.setProperty('--background-mb-logo', color);
}
