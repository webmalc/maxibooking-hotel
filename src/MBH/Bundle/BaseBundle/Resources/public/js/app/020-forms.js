/*global window, document, Routing, fole, str, $, select2, localStorage, mbh */

mbh.datarangepicker = {
    options: {
        'dateLimit': 365,
        'showDropdowns': true,
        'autoApply': true,
        'autoUpdateInput': true,
        "locale": {
            "format": "ll",
            "separator": " - ",
            "daysOfWeek": [
                "Вс",
                "Пн",
                "Вт",
                "Ср",
                "Чт",
                "Пт",
                "Сб"
            ],
            "monthNames": [
                "Январь",
                "Февраль",
                "Март",
                "Апрель",
                "Май",
                "Июнь",
                "Июль",
                "Август",
                "Сентябрь",
                "Октябрь",
                "Ноябрь",
                "Декабрь"
            ],
            "firstDay": 1
        }
    },
    on: function (begin, end, picker) {
        'use strict'
        begin.val(picker.startDate.format('DD.MM.YYYY'));
        end.val(picker.endDate.format('DD.MM.YYYY'));
        begin.trigger('change');
    }
};

var createDate = function (input) {
    'use strict';
    var parts = input.val().split(".");
    return new Date(parts[2], parts[1] - 1, parts[0]);
};

var select2Text = function (el) {
    'use strict';

    el.replaceWith(
        "<select name='" + el.prop('name') + "' id='" + el.prop('id') + "' class='form-control input-sm " + el.prop('class') + "'>" +
        "<option selected value='" + el.val() + "'></option></select>"
    );
    return $('#' + el.prop('id'));
};

/**
 * @param $begin
 * @param $end
 */
var RangeInputs = function ($begin, $end) {
    'use strict';
    this.$begin = $begin;
    this.$end = $end;
    this.bindEventListeners();
};

RangeInputs.prototype.bindEventListeners = function () {
    'use strict';
    var that = this;
    this.$begin.change(function () {
        if (!that.$end.val()) {
            //that.$end.focus();
        }
    });
    this.$end.change(function () {
        if (!that.$begin.val()) {
            //that.$begin.focus();
        }
    });
}

$.fn.serializeObject = function () {
    'use strict';
    var o = {},
        a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

$.fn.mbhGuestSelectPlugin = function () {
    this.each(function () {
        var $this = $(this);
        if ($this.is('input')) {
            $this = select2Text($this);
        }
        $this.select2({
            minimumInputLength: 3,
            allowClear: true,
            placeholder: 'Выберите гостя',
            ajax: {
                url: Routing.generate('ajax_tourists'),
                dataType: 'json',
                data: function (params) {
                    return {
                        query: params.term // search term
                    };
                },
                results: function (data) {
                    return {results: data};
                },
                /*processResults: function(data) {
                 console.log(data);
                 }*/
            },
            initSelection: function (element, callback) {
                var id = $(element).val();
                if (id !== "") {
                    $.ajax(Routing.generate('ajax_tourists', {id: id}), {
                        dataType: "json"
                    }).done(function (data) {
                        callback(data);
                    });
                }
            },
            dropdownCssClass: "bigdrop"
        });
    })

    return this;
}

$.fn.mbhOrganizationSelectPlugin = function () {
    this.each(function () {
        var $this = $(this);
        if ($this.is('input')) {
            $this = select2Text($this);
        }
        $this.select2({
            minimumInputLength: 3,
            allowClear: true,
            placeholder: 'Выберите организацию',
            ajax: {
                url: Routing.generate('organization_json_list'),
                dataType: 'json',
                data: function (params) {
                    return {
                        query: params.term // search term
                    };
                },
                results: function (data) {
                    return {results: data};
                },
                processResults: function (data) {
                    var details = data.details;
                    $.each(data.list, function (k, v) {
                        var d = details[v.id];
                        data.list[k].text = v.text + ' ' + '(ИНН ' + d['inn'] + ')' + (d['fio'] ? ' ' + d['fio'] : '')
                    });

                    return {results: data.list};
                }
            },
            /*initSelection: function (element, callback) {
             var id = $(element).val();
             if (id !== "") {
             $.ajax(Routing.generate('organization_json_list', {id: id}), {
             dataType: "json"
             }).done(function (data) {
             console.log(data);
             });
             }
             },*/
            dropdownCssClass: "bigdrop"
        });
    })

    return this;
}

mbh.payerSelect = function ($payerSelect, $organizationPayerInput, $touristPayerInput) {
    this.$payerSelect = $payerSelect;
    this.$organizationPayerInput = $organizationPayerInput;
    this.$touristPayerInput = $touristPayerInput;

    if (this.$organizationPayerInput.val()) {
        this.$payerSelect.val('org_' + this.$organizationPayerInput.val());
    } else if (this.$touristPayerInput.val()) {
        this.$payerSelect.val('tourist_' + this.$touristPayerInput.val());
    }

    if (this.$payerSelect.val()) {
        var value = this.$payerSelect.val().split('_');
        this.update(value[0], value[1]);
    }
    ;

    this.bindEventHandlers();
}

mbh.payerSelect.prototype.bindEventHandlers = function () {
    var that = this;
    this.$payerSelect.on('change', function () {
        /** @type String */
        var value = $(this).val();
        that.$organizationPayerInput.add(that.$touristPayerInput).val(null);
        if (value) {
            value = value.split('_');
            that.update(value[0], value[1]);
        }
    });
};

mbh.payerSelect.prototype.update = function (type, value) {
    if (type === 'org') {
        this.$organizationPayerInput.val(value);
    } else if (type === 'tourist') {
        this.$touristPayerInput.val(value);
    }
};

var docReadyForms = function () {
    'use strict';

    //select all
    (function () {
        $('select.select-all ').each(function () {
            var elements = $('<div class="text-right"><small><a href="#" class="select-all-link">выбрать всё</a></small></div>');
            var select = $(this);
            $(this)
                .closest('div.col-sm-6')
                .prepend(elements);
            $(elements).find('a').click(function (e) {
                e.preventDefault();
                select.children('option').prop('selected', true);
                select.trigger('change');
            });
        });
    }());

    $('form.remember input:not(.not-remember), form.remember select:not(.not-remember), form.remember textarea:not(.not-remember)').phoenix({
        webStorage: 'sessionStorage',
        namespace: 'phoenixStorage' + mbh.currentHotel
    });

    $(".timepicker").timepicker({
        showMeridian: false
    });

    //only int
    $('.only-int').change(function () {
        if (this.value === '' && !$(this).hasClass('not-null')) {
            return;
        }
        var value = parseInt(this.value, 10);
        if (value < 0 || isNaN(value)) {
            this.value = 0;
        }
    });

    // input size
    (function () {
        var resize = function (el, size) {
            el.closest('div.col-md-4').removeClass('col-md-4').addClass('col-md-' + size);
        };
        $('input.sm').each(function () {
            resize($(this), 2);
        });
        $('input.xs').each(function () {
            resize($(this), 1);
        });
    }());

    //Send delete form
    $('#entity-delete-button').click(function (event) {
        event.preventDefault();
        $('#entity-delete-form').submit();
    });

    //BootstrapSwitch configuration
    var bootstrapSwitchConfig = {
        'size': 'small',
        'onText': 'да',
        'offText': 'нет',
        'onColor': 'success'
    };
    $('input[type="checkbox"]')
        .not('.plain-html')
        .not('.checkbox-mini')
        .bootstrapSwitch(bootstrapSwitchConfig);


    //Red color scheme for iCheck
    $('input[type="checkbox"].flat-green, input[type="radio"].flat-green').iCheck({
        checkboxClass: 'icheckbox_flat-green',
        radioClass: 'iradio_flat-green'
    });
    $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
        checkboxClass: 'icheckbox_minimal-blue',
        radioClass: 'iradio_minimal-blue'
    });

    bootstrapSwitchConfig.size = 'mini'
    $('.checkbox-mini').bootstrapSwitch(bootstrapSwitchConfig);

    //Select2 configuration
    $('select:not(.plain-html)').addClass('select2').select2({
        placeholder: "Сделайте выбор",
        allowClear: true,
        width: 'resolve',
        formatSelection: function (item, container) {
            var optgroup = $(item.element).parent('optgroup').attr('label');
            if (!optgroup) {
                return item.text;
            } else {
                return optgroup + ': ' + item.text.toLowerCase();
            }
        }
    });

    //Datepicker configuration
    var optionForDatepicker = {
        common:function () {
            return {
                language: "ru",
                todayHighlight: true,
                autoclose: true,
                format: 'dd.mm.yyyy',
                disableTouchKeyboard: true
            };
        },
        addStartDate: function(setDate) {
            if (setDate === undefined || setDate === null) {
                return this.common();
            }

            var data = this.common();
            data.startDate = setDate;

            return data;
        }
    };

    var CssClassName = function(key) {
        this._key = key;
    };

    CssClassName.prototype = {
        _prefix: 'datepicker-group-',
        getBegin: function() {
            return this.getPrefix() + 'begin_' + this._key;
        },
        getEnd: function() {
            return this.getPrefix() + 'end_' + this._key;
        },
        getPrefix: function() {
            return this._prefix;
        }
    };

    var helperDate = {
            returnPlusOneDay: function(date) {
                if (date === undefined || date === null) {
                    return date;
                }
                var newDate = moment(date);
                newDate.add(1, 'day');

                return newDate.format('DD.MM.YYYY');
            }
        },
        changeEndDate = function(divElement, key) {
            var cssClass = new CssClassName(key),
                inputBegin = divElement.querySelector('.datepicker.begin-datepicker'),
                inputEnd = divElement.querySelector('.datepicker.end-datepicker');

            inputBegin.classList.add(cssClass.getBegin());
            inputEnd.classList.add(cssClass.getEnd());

            $(inputBegin).datepicker(optionForDatepicker.common())
            .on('changeDate', function() {
                var dateEnd = $(inputEnd).datepicker('getUTCDate'),
                    dateBegin = $(inputBegin).datepicker('getUTCDate');

                $(inputEnd).datepicker('setStartDate', inputBegin.value);

                if (dateEnd === null || dateBegin.setHours(0) > dateEnd.setHours(23)) {
                    $(inputEnd).datepicker('setDate', helperDate.returnPlusOneDay(dateBegin));
                }
            });

            $(inputEnd).datepicker(
                optionForDatepicker.addStartDate(
                    helperDate.returnPlusOneDay($(inputBegin).datepicker('getUTCDate'))
                )
            );
        };

    document.querySelectorAll('.change-date-in-end-datepicker').forEach(function(divElement, key) {
        changeEndDate(divElement, key);
    });

    $('.datepicker:not([class^="' + CssClassName.prototype.getPrefix() + '"])').datepicker(optionForDatepicker.common());

    //Datepicker configuration
    $('.datepicker-year').datepicker({
        language: "ru",
        todayHighlight: true,
        autoclose: true,
        format: 'dd.mm.yyyy',
        startView: 'decade'
    });

    $('.datepicker').keyup(function (e) {

        if (e.keyCode == 8 || e.keyCode == 46 || e.keyCode == 37) {
            return;
        }
        var str = $(this).val().replace(/[^0-9]/g, '').substr(0, 8);
        if (str.length > 1) {
            var str = [str.slice(0, 2), '.', str.slice(2)].join('');
        }
        if (str.length > 4) {
            var str = [str.slice(0, 5), '.', str.slice(5)].join('');
        }
        $(this).val(str);

        if (str.length == 10) {
            $(this).datepicker('hide');
        }

    }).attr("autocomplete", "off");

    //datepicker select
    (function () {
        var select = $('select.datepicker-period-select'),
            begin = $('.begin-datepicker'),
            end = $('.end-datepicker'),
            setDates = function () {
                var period = begin.val() + '-' + end.val();
                if (!select.val()) {
                    select.val(period);//.trigger('change');
                    return;
                }
                var dates = select.val().split('-');
                begin.val(dates[0]);
                end.val(dates[1]).trigger('change');
            };

        if (!select.length || !begin.length || !end.length) {
            return;
        }
        $('.datepicker-period-select').css('width', '130px');
        select.on('change', setDates);
        setDates();
    }());

    new RangeInputs($('.begin-datepicker'), $('.end-datepicker'));

    //Daterangepickers
    (function () {
        var begin = $('.begin-datepicker.mbh-daterangepicker'),
            wrapper = begin.parent('div'),
            end = $('.end-datepicker.mbh-daterangepicker'),
            range = $('<input type="text" required="required" class="daterangepicker-input form-control input-sm" autocomplete="off">');
        ;


        if (!begin.length || !end.length || !wrapper.length) {
            return;
        }

        begin.after(range);
        range.daterangepicker(mbh.datarangepicker.options).on('apply.daterangepicker', function (ev, picker) {
            mbh.datarangepicker.on(begin, end, picker);
        });
        if (begin.datepicker("getDate") && end.datepicker("getDate")) {
            range.data('daterangepicker').setStartDate(begin.datepicker("getDate"));
            range.data('daterangepicker').setEndDate(end.datepicker("getDate"));
        }
    }());


    //form group collapse
    (function () {
        var links = $('.form-group-collapse');

        links.each(function () {
            if (localStorage.getItem($(this).prop('id'))) {
                var box = $(this).closest('.box'),
                    boxBody = box.find('.box-body'),
                    icon = $(this).find('i');

                box.addClass('collapsed-box');
                boxBody.hide();
                icon.removeClass('fa-minus').addClass('fa-plus');
            }
        });
        links.click(function () {
            if ($(this).closest('.box').find('.box-body').is(':visible')) {
                localStorage.setItem($(this).prop('id'), 1);
            } else {
                localStorage.removeItem($(this).prop('id'));
            }
        });
    }());

    //form group expandable
    (function () {
        var links = $('.form-group-expandable');

        links.each(function () {
            var box = $(this).closest('.box'),
                boxBody = box.find('.box-body'),
                icon = $(this).find('i');

            if (localStorage.getItem($(this).prop('id')) || (boxBody.find('input[type="checkbox"]:checked').length && $(this).hasClass('show-checkboxes'))) {
                box.removeClass('collapsed-box');
                boxBody.show();
                icon.removeClass('fa-plus').addClass('fa-minus');
            }
        });
        links.click(function () {
            if ($(this).closest('.box').find('.box-body').is(':visible')) {
                localStorage.removeItem($(this).prop('id'));
            } else {
                localStorage.setItem($(this).prop('id'), 1);
            }
        });
    }());

    //city select
    (function () {
        var citySelect = $('.citySelect');
        if (citySelect.length !== 1) {
            return;
        }

        select2Text(citySelect)
            .select2({
                minimumInputLength: 3,
                allowClear: true,
                placeholder: 'Выберите город',
                ajax: {
                    url: Routing.generate('hotel_city'),
                    dataType: 'json',
                    data: function (params) {
                        return {
                            query: params.term // search term
                        };
                    },
                    results: function (data) {
                        return {results: data};
                    }
                },
                initSelection: function (element, callback) {
                    var id = $(element).val();
                    if (id !== "") {
                        $.ajax(Routing.generate('hotel_city') + '/' + id, {
                            dataType: "json"
                        }).done(function (data) {
                            callback(data);
                        });
                    }
                },

                dropdownCssClass: "bigdrop"
            });
    }());

    //order select
    (function () {
        var orderSelect = $('.order-select');

        if (orderSelect.length !== 1) {
            return;
        }

        select2Text(orderSelect)
            .select2({
                minimumInputLength: 1,
                allowClear: true,
                placeholder: 'Выберите бронь',
                ajax: {
                    url: Routing.generate('getPackageJsonSearch'),
                    dataType: 'json',
                    data: function (params) {
                        return {
                            query: params.term // search term
                        };
                    },
                    results: function (data) {
                        return {results: data};
                    }
                },
                initSelection: function (element, callback) {
                    var id = $(element).val();
                    if (id !== "") {
                        $.ajax(Routing.generate('getPackageJsonById',
                            {
                                id: id
                            }), {dataType: "json"})
                            .done(function (data) {
                                callback(data);
                            });
                    }
                },
                dropdownCssClass: "bigdrop"
            });
    })();


    //remember inputs
    (function () {
        var inputs = $('.input-remember'),
            load = function () {
                inputs.each(function () {
                    var el = $(this),
                        cookie = $.cookie('input_' + el.attr('id'));

                    if (cookie) {
                        el.val(cookie);
                    }
                });
            },
            remember = function () {
                inputs.each(function () {
                    var el = $(this);
                    if (el.val() && el.attr('id')) {
                        $.cookie('input_' + el.attr('id'), el.val(), {expires: 7});
                    }
                });
            };
        load();
        inputs.change(remember);
    }());

    $('input[type=file]').bootstrapFileInput();

    tinymce.init({
        selector: ".tinymce",
        min_width: 600,
        toolbar_items_size: 'small',
        skin_url: '/assets/vendor/tinymce/skins/lightgray',
        toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent"

    });

    //roles widget
    (function () {
        var widgets = $('.roles-widget'),
            topLinks = widgets.find('.box-title input'),
            disable = function (el) {
                el.closest('.roles-widget')
                    .find('.box-body input')
                    .prop('disabled', el.is(":checked")).iCheck('update');
            };

        if (!widgets.length) {
            return;
        }

        topLinks.each(function () {
            disable($(this));
        });
        topLinks.on('ifToggled', function () {
            disable($(this))
        });
    }());

    $('.tags-select-widget').tagsSelectWidget();

    //payer select2
    $('.findGuest').mbhGuestSelectPlugin();
};


var select2TemplateResult = {
    _iconHtml: function (state) {
        if (!state.id) {
            return state.text;
        }
        var rawIcons = state.element.getAttribute('data-icon'),
            result = '';
        if (rawIcons) {
            var icons = rawIcons.split(';');

            $.each(icons, function (key, icon) {
                if (icon) {
                    result += '<i class="fa ' + icon + '"></i>';
                }
            });
        }

        return result ? result : null;
    },
    appendIcon: function (state) {
        var iconHtml = select2TemplateResult._iconHtml(state);
        var html = iconHtml ?
            state.text + ' ' + iconHtml :
            state.text;

        return $('<div>' + html + '</div>');
    },
    prependIcon: function (state) {
        var iconHtml = select2TemplateResult._iconHtml(state);
        var html = iconHtml ?
            iconHtml + ' ' + state.text :
            state.text;

        return $('<div>' + html + '</div>');
    },
    icon: function (state) {
        var iconHtml = select2TemplateResult._iconHtml(state);
        return $('<div>' + iconHtml + '</div>');
    }
};

/**
 * tagsSelectWidget

 */
(function ($) {
    var mainClass = 'tags-select-widget';
    var defaultOption = {
        value: null,
        emptyHelp: null,
        select2Options: {
            width: 'resolve',
            closeOnSelect: false,
            templateResult: select2TemplateResult.prependIcon
        }
    };

    function TagsSelectWidget($wrapper, options) {
        var that = this,
            $select = $wrapper.find('select'),
            $list = $wrapper.find('.list'),
            inputName = $select.attr('name'),
            isMultiple = $select.attr('multiple'),
            isRequired = $select.attr('required'),
            hasSelect2 = $select.data('select2'),
            value = options.value;

        this.items = {
            add: function (value, text, title) {
                var input = '<input type="hidden" name="' + inputName + '" value="' + value + '">';
                var item = '<div class="btn btn-xs btn-default" data-toggle="tooltip" data-original-title="' + title + '">' + text + input + '<div>';
                $list.append(item);
            },
            addIcon: function (value, title, icon) {
                var text = '<i class="fa fa-2x ' + icon + '"></i>';// + text;
                this.add(value, text, title);
            },
            clear: function () {
                $list.find('.btn').remove();
            },
            isEmpty: function () {
                return $list.find('.btn').length == 0;
            }
        }

        this.help = {
            inited: false,
            init: function () {
                this.text = options.emptyHelp;
                this.$help = $('<small class="hide">' + this.text + '</small>');
                $list.append(this.$help);
                this.inited = true;
            },
            show: function () {
                if (this.inited) {
                    this.$help.removeClass('hide');
                }
            },
            hide: function () {
                if (this.inited) {
                    this.$help.addClass('hide');
                }
            },
            update: function () {
                if (this.inited) {
                    that.items.isEmpty() ? this.show() : this.hide();
                }
            }
        }

        this.init = function () {
            if (isMultiple) {
                if ($select.val() && options.value === false) {
                    value = $select.val();
                }
                $select.removeAttr('multiple');
            } else {
                inputName += '[]';
                if (!$.isArray(value)) {
                    value = [];
                }
            }
            if (isRequired) {
                $select.removeAttr('required');
            }

            if ($list.find('.btn').length == 0 && value.length > 0) {
                value.forEach(function (value) {
                    var $option = $select.find('option[value=' + value + ']');
                    if ($option.length == 1) {
                        that.items.addIcon(value, $option.text(), $option.data('icon'));
                    }
                });
            }

            $select.val(null);
            $select.attr('name', inputName.replace(/(\[.*\])/g, '') + '_fake');
            if (!hasSelect2) {
                var select2Options = options.select2Options;
                select2Options.placeholder = $select.attr('placeholder');
                $select.select2(select2Options);
            }

            $select.on('select2:selecting', function (event) {
                var element = event.params.args.data.element;
                that.items.addIcon(element.value, element.text, element.getAttribute('data-icon'));
                that.help.hide();
                event.preventDefault();
            });

            $list.on('click.tagsSelectWidget', '.btn', function () {
                $list.find('[data-toggle=tooltip]').tooltip('hide');
                $(this).remove();
                that.help.update();
            });

            if (options.emptyHelp) {
                this.help.init();
            }

            this.help.update();
        }
    }

    var methods = {
        init: function (options) {
            options = $.extend({}, defaultOption, options);
            return this.each(function () {
                var $this = $(this);
                var $wrapper;

                if ($this.is("select")) {
                    $wrapper = $this.wrap('<div class="' + mainClass + '"></div>').closest('.' + mainClass);
                    $wrapper.prepend('<div class="list"></div>');
                } else if ($this.hasClass(mainClass)) {
                    $wrapper = $this;
                    if ($wrapper.data('tagsSelectWidget')) {
                        return; //plugin is already init for this element
                    }
                } else {
                    throw new Error();
                }

                var widget = new TagsSelectWidget($wrapper, options);
                widget.init();
                $wrapper.data('tagsSelectWidget', widget);
            });
        },
        clear: function () {
            return this.each(function () {
                var widget = $(this).data('tagsSelectWidget');
                widget.items.clear();
            });
        },
        update: function (values) {
            return this.each(function () {
                //todo
                //var widget = $(this).data('tagsSelectWidget');
                //widget.items.update(values);
            });
        }
    };

    $.fn.tagsSelectWidget = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Метод с именем ' + method + ' не существует');
        }
    };
})(window.jQuery);


/**
 * @author Alexandr Arofikin <sashaaro@gmail.com>
 * @param filter function
 */
$.fn.mbhSelect2OptionsFilter = function (filter, resetOptionsHtml) {
    var html = resetOptionsHtml || this.html();
    html = html.replace('selected="selected"', '');
    var $selectHtml = $('<select>' + html + '</select>');
    $selectHtml.html($selectHtml.find('option').filter(filter));
    var select2Instance = this.data('select2');
    var resetOptions = select2Instance.options.options;
    this.select2('destroy').html($selectHtml.html()).select2(resetOptions);
}


var discountInit = function ($discountInput, $isPercentDiscountCheckbox) {
    $discountInput.TouchSpin({
        min: 0.01,
        max: 9999999999999999,
        step: 0.05,
        decimals: 2,
        postfix: '%',
        boostat: 5,
        forcestepdivisibility: 'none'
    });
    var $discountTypeInputPostfix = $discountInput.siblings('span.bootstrap-touchspin-postfix');

    var discountInputUpdate = function (state) {
        if (state) { //$isPercentDiscountCheckbox.is(':checked')
            $discountInput.trigger("touchspin.updatesettings", {max: 100});
            $discountTypeInputPostfix.html('%');
        } else {
            $discountInput.trigger("touchspin.updatesettings", {max: 100000000});
            $discountTypeInputPostfix.html('<i class="fa fa-money"></i>')
        }
    }

    $isPercentDiscountCheckbox.on('switchChange.bootstrapSwitch', function (event, state) {
        discountInputUpdate(state)
    })

    discountInputUpdate($isPercentDiscountCheckbox.is(':checked'));
}
/**
 * @author Alexandr Arofikin <sashaaro@gmail.com>
 * @link http://www.datatables.net/plug-ins/api/fnSetFilteringDelay
 *
 * @param oSettings
 * @param iDelay
 * @returns {jQuery.fn.dataTableExt.oApi}
 */
jQuery.fn.dataTableExt.oApi.fnSetFilteringDelay = function (oSettings, iDelay) {
    var _that = this;

    if (iDelay === undefined) {
        iDelay = 250;
    }

    this.each(function (i) {
        $.fn.dataTableExt.iApiIndex = i;
        var
            oTimerId = null,
            sPreviousSearch = null,
            anControl = $('input', _that.fnSettings().aanFeatures.f);

        anControl.unbind('keyup search input').bind('keyup search input', function () {

            if (sPreviousSearch === null || sPreviousSearch != anControl.val()) {
                window.clearTimeout(oTimerId);
                sPreviousSearch = anControl.val();
                oTimerId = window.setTimeout(function () {
                    $.fn.dataTableExt.iApiIndex = i;
                    _that.fnFilter(anControl.val());
                }, iDelay);
            }
        });

        return this;
    });
    return this;
};

var mbhStartDate = function (e) {
    if ($('form').is('.mbh-start-date')) {
        if (!($('.begin-datepicker').val()) && !($('.end-datepicker').val())) {
            console.log('loaded');
            $('.daterangepicker-input').data('daterangepicker').setStartDate(moment(mbh.startDatePick, "DD.MM.YYYY").toDate());
            $('.daterangepicker-input').data('daterangepicker').setEndDate(moment(mbh.startDatePick, "DD.MM.YYYY").add(($('form').is('.mbh-start-date-search')) ? 1 : 45, 'days').toDate());
            $('.begin-datepicker').val($('.daterangepicker-input').data('daterangepicker').startDate.format('DD.MM.YYYY'));
            $('.end-datepicker').val($('.daterangepicker-input').data('daterangepicker').endDate.format('DD.MM.YYYY'));
        }
    }
};


$(document).ready(function () {
    'use strict';

    docReadyForms();

    mbhStartDate();
});