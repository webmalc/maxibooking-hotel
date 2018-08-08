/*global window, document, Routing, fole, str, $, select2, localStorage, mbh */

var BILLING_API_SETTINGS = {
    fms: {
        url: mbh['billing_host'] + document.documentElement.lang + '/fms-fms',
        id: 'internal_id',
        text: 'name'
    },
    countries: {
        url: mbh['billing_host'] + document.documentElement.lang + '/countries',
        id: 'tld',
        text: 'name'
    },
    regions: {
        url: mbh['billing_host'] + document.documentElement.lang + '/regions',
        id: 'id',
        text: 'name',
        creationRouteName: 'create_region',
        fieldClass: 'billing-region',
        initFormFunc: function (formResponse) {
            $('#modal-with-form-body').html(formResponse['data']['html']);
            $('#mbhbilling_bundle_region_type_country').val($('.billing-country').val());
            initSelect2TextForBilling('mbhbilling_bundle_region_type_country', BILLING_API_SETTINGS.countries);
        },
        checkable: true
    },
    cities: {
        url: mbh['billing_host'] + document.documentElement.lang + '/cities',
        id: 'id',
        text: 'full_name',
        creationRouteName: 'create_city',
        fieldClass: 'billing-city',
        initFormFunc: function (response) {
            $('#modal-with-form-body').html(response['data']['html']);
            initSelect2TextForBilling('mbhbilling_bundle_city_type_country', BILLING_API_SETTINGS.countries);
            initSelect2TextForBilling('mbhbilling_bundle_city_type_region', BILLING_API_SETTINGS.regions);
        },
        checkable: true
    },
    fmsKpp: {
        url: mbh['billing_host'] + document.documentElement.lang + '/fms-kpp',
        id: 'internal_id',
        text: 'name'
    },
    services: {
        url: mbh['billing_host'] + document.documentElement.lang + '/services/',
        id: 'id',
        text: 'title'
    }
};

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
                Translator.trans("online.online-calendar.sun_abbr_min"),
                Translator.trans("online.online-calendar.mon_abbr_min"),
                Translator.trans("online.online-calendar.tue_abbr_min"),
                Translator.trans("online.online-calendar.wed_abbr_min"),
                Translator.trans("online.online-calendar.thu_abbr_min"),
                Translator.trans("online.online-calendar.fri_abbr_min"),
                Translator.trans("online.online-calendar.sat_abbr_min")
            ],
            "monthNames": [
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
            placeholder: Translator.trans("020-forms.select_guest"),
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
    });

    return this;
};

$.fn.mbhOrganizationSelectPlugin = function () {
    this.each(function () {
        var $this = $(this);
        if ($this.is('input')) {
            $this = select2Text($this);
        }
        $this.select2({
            minimumInputLength: 3,
            allowClear: true,
            placeholder: Translator.trans("020-forms.chose_organization"),
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
                        data.list[k].text = v.text + ' ' + '(' + Translator.trans("020-forms.inn") + ' ' + d['inn'] + ')' + (d['fio'] ? ' ' + d['fio'] : '')
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
}

mbh.payerSelect.prototype.update = function (type, value) {
    if (type === 'org') {
        this.$organizationPayerInput.val(value);
    } else if (type === 'tourist') {
        this.$touristPayerInput.val(value);
    } else {
        //throw new Error("");
    }
}

var docReadyForms = function () {
    'use strict';

    //select all
    (function () {
        $('select.select-all ').each(function () {
            var elements = $('<div class="text-right"><small><a href="#" class="select-all-link">' + Translator.trans("020-forms.chose_all") + '</a></small></div>');
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
        'onText': Translator.trans("020-forms.yes"),
        'offText': Translator.trans("020-forms.no"),
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
    if (isMobileDevice()) {
        $('select:not(.not-change-if-mobile)').each(function() {
          var placeholder = $(this).data('placeholder');

          if (placeholder !== undefined) {
            $(this).find('option:first-child')
            .prop('disabled', true)
            .text(placeholder);
          }
        });
    } else {
      $('select:not(.plain-html)').addClass('select2').select2({
        placeholder: Translator.trans("020-forms.make_choice"),
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
    }

    //Datepicker configuration
    $('.datepicker').datepicker({
        language: "ru",
        todayHighlight: true,
        autoclose: true,
        format: 'dd.mm.yyyy',
        disableTouchKeyboard: true
    });

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
    if (!isMobileDevice()) {
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
    }

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

    (function () {
        var citySelect = $('.citySelect');
        if (citySelect.length !== 1) {
            return;
        }
        initSelect2TextForBilling(citySelect.get(0).id, BILLING_API_SETTINGS.cities);
    }());

    (function () {
        var $billingTextSelect = $('.billing-text-select');
        $billingTextSelect.each(function (index, element) {
            var endpointName = element.getAttribute('data-endpoint-name');
            initSelect2TextForBilling(element.id, BILLING_API_SETTINGS[endpointName]);
        });
    }());

    (function () {
        $('.mbh-spinner').each(function (index, element) {
            var min = element.getAttribute('spinner-min') ? parseFloat(element.getAttribute('spinner-min')) : 0;
            var max = element.getAttribute('spinner-max') ? parseFloat(element.getAttribute('spinner-max')) : 100000000;
            var step = element.getAttribute('step') ? parseFloat(element.getAttribute('step')) : 1;
            var decimals = element.getAttribute('decimals') ? parseInt(element.getAttribute('decimals'), 10) : 0;

            $(element).TouchSpin({
                min: min,
                max: max,
                step: step,
                decimals: decimals
            });
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
                placeholder: Translator.trans("020-forms.select_package"),
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
            $.error('Method named "' + method + ' does not exist');
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
        min: -9999999999999999,
        max: 9999999999999999,
        step: 0.1,
        decimals: 2,
        postfix: '%'
    });
    var $discountTypeInputPostfix = $discountInput.siblings('span.bootstrap-touchspin-postfix');

    var discountInputUpdate = function (state) {
        if (state) { //$isPercentDiscountCheckbox.is(':checked')
            $discountInput.trigger("touchspin.updatesettings", {max: 100, min: -1000});
            $discountTypeInputPostfix.html('%');
        } else {
            $discountInput.trigger("touchspin.updatesettings", {max: 100000000, min: -100000000});
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
            var beginDate = moment(($('form').is('.mbh-start-date-search') ? mbh.searchBeginDate : mbh.startDatePick), "DD.MM.YYYY");
            $('.daterangepicker-input').data('daterangepicker').setStartDate(beginDate.toDate());
            $('.daterangepicker-input').data('daterangepicker').setEndDate(beginDate.add(($('form').is('.mbh-start-date-search')) ? 1 : 45, 'days').toDate());
            $('.begin-datepicker').val($('.daterangepicker-input').data('daterangepicker').startDate.format('DD.MM.YYYY'));
            $('.end-datepicker').val($('.daterangepicker-input').data('daterangepicker').endDate.format('DD.MM.YYYY'));
        }
    }
};

var disableCheckboxListen = function () {
    var $disableCheckBox = $('#mbh-disable');
    $disableCheckBox.is(':checked');
    $disableCheckBox.on('switchChange.bootstrapSwitch', function () {
        var disableMode = !$disableCheckBox.bootstrapSwitch('state') ? 'true' : 'false';
        var routeName = $disableCheckBox.attr('data-route-name');
        window.location.href = Routing.generate('change_room_type_enableable_mode', {
            disableMode: disableMode,
            route: routeName
        });
    });
};

function getExportButtonSettings(entityName, format, filterDataCallback) {
    var exportUrl = Routing.generate('export_entities', {entityName: entityName, format: format});
    return {
        text: '<i class="fa fa-file-excel-o" title="' + format + '" data-toggle="tooltip" data-placement="bottom"></i>',
        className: 'btn btn-default btn-sm',
        action: function () {
            $.ajax({
                url: exportUrl,
                type: 'GET',
                success: function (response) {
                    $('<div id="template-document-csv-modal" class="modal"></div>').insertAfter($('.content-wrapper'));
                    var $modal = $('#template-document-csv-modal');
                    $modal.html(response);
                    $modal.find('select').css('width', '100%').select2();
                    $modal.modal('show');

                    var $form = $modal.find("form");
                    $form.find('#export-send-button').click(function () {
                        window.open(exportUrl + '?' + filterDataCallback() + '&' + $form.serialize());
                        $modal.modal('hide');
                    });
                }
            });
        }
    }
}

function onHideCheckboxChange() {
    var $boxHideableCheckbox = $('.box-full-visibility-checkbox, .box-specified-visibility-checkbox');

    var setVisibility = function (checkbox) {
        var $checkbox = $(checkbox);
        var isAllFormGroups = $checkbox.hasClass('box-full-visibility-checkbox');
        var $boxFormGroups;
        if (isAllFormGroups) {
            $boxFormGroups = $checkbox.closest('.box').find('.form-group');
        } else {
            $boxFormGroups = $checkbox.closest('.box').find('.visibility-changeable').closest('.form-group');
        }

        var $hideFormGroups = $boxFormGroups.not($checkbox.closest('.form-group'));
        var isOn = $checkbox.bootstrapSwitch('state');
        isOn ? $hideFormGroups.show() : $hideFormGroups.hide();
    };

    $boxHideableCheckbox.each(function () {
        setVisibility(this)
    });

    $boxHideableCheckbox.on('switchChange.bootstrapSwitch', function () {
        setVisibility(this);
    });
}

function addAndSetSelect2Option($select2input, value, text) {
    $select2input
        .append('<option value="' + value + '">' + text + '</option>')
        .val(value)
        .trigger('change');
}

function initSelect2TextForBilling(inputId, apiSettings) {
    var $select2Field = select2Text($('#' + inputId));
    var selectedValue = $select2Field.val();

    $select2Field.select2({
        minimumInputLength: 3,
        placeholder: Translator.trans('tourist.make_a_choice'),
        allowClear: true,
        ajax: {
            headers: {
                Authorization: "Token " + mbh['front_token']
            },
            url: apiSettings['url'] + '/',
            dataType: 'json',
            data: function (params) {
                var queryParams = {
                    search: params.term
                };
                if (apiSettings['checkable']) {
                    queryParams['is_enabled'] = true;
                    queryParams['is_checked'] = true;
                }
                return queryParams;
            },
            processResults: function (data) {
                var options = [];

                data.results.forEach(function (option) {
                    var optionId = option[apiSettings['id']];

                    //fix error because of empty text in default option
                    if (optionId === selectedValue) {
                        $select2Field.find('option[value="' + optionId + '"]').first().remove();
                    }

                    options.push({
                        id: optionId,
                        text: option[apiSettings['text']]
                    });
                });

                return {results: options};
            }
        },
        initSelection: function (element, callback) {
            var id = $(element).val();
            if (id) {
                $.ajax(apiSettings['url'] + '/' + id + '/', {
                    dataType: "json",
                    headers: {
                        Authorization: "Token " + mbh['front_token']
                    }
                }).done(function (data) {
                    var optionId = data[apiSettings['id']];
                    var optionTitle = data[apiSettings['text']];

                    if (apiSettings['checkable'] && data['is_checked'] === false) {
                        optionTitle += ' (' + Translator.trans('020-forms.on_moderation') + ')';
                    }

                    var selectedOrgan = {
                        id: optionId,
                        text: optionTitle
                    };
                    callback(selectedOrgan);
                });
            }
        },
        dropdownCssClass: "bigdrop"
    });
}

function initLabelTips() {
    if (mbh.showLabelTips) {
        var currentLang = document.documentElement.lang;
        var languageSettings = (currentLang === 'ru' || (tips_en === undefined) ? tips_ru : tips_en);
        for (var formName in languageSettings) {
            var $form = $('form[name="' + formName + '"]');
            $form.find('label').each(function (index, label) {
                var fieldId = label.getAttribute('for');
                var tipText = languageSettings[formName][fieldId];
                if (tipText) {
                    var span = document.createElement('span');
                    span.classList.add('dotted-bottom-border');
                    span.innerHTML = label.innerHTML;
                    span.setAttribute('data-toggle', "tooltip");
                    span.setAttribute('title', tipText);
                    label.innerHTML = span.outerHTML;
                }
            });
        }
    }
}

function initAddTipModal() {
    if (mbh.canAddTips) {
        $('label').dblclick(function () {
            var $tipsModal = $('#add-tips-modal');
            var $form = $(this).closest('form');
            var formName = $form.attr('name');
            var inputId = this.nodeName === 'input' ? this.id : this.getAttribute('for');
            if (!inputId) {
                alert('Невозможно добавить подсказку к этому полю.');
            } else {
                $tipsModal.find('#tips-form-id').val(formName);
                $tipsModal.find('#tips-field-id').val(inputId);
                $tipsModal.modal('show');
                setTipText(formName, inputId);
                $('#tips-tip-lang').change(function () {
                    setTipText(formName, inputId);
                });
                $('#tips-modal-save-button').click(function () {
                    $.ajax({
                        url: Routing.generate('add_tip'),
                        data: $tipsModal.find('form').serialize(),
                        success: function () {
                            $('#tips-tip-text').val('');
                            $tipsModal.modal('hide');
                        },
                        error: function () {
                            alert(mbh.error.html);
                        }
                    });
                });
            }
        });
    }
}

function setTipText(formName, inputId) {
    var lang = $('#tips-tip-lang').val();
    var languageSettings = (lang === 'ru' || (typeof tips_en !== 'undefined') ? tips_ru : null);
    var text;
    if (languageSettings && languageSettings[formName] && languageSettings[formName][inputId]) {
        text = languageSettings[formName][inputId];
    } else {
        text = '';
    }

    $('#tips-tip-text').val(text);
}

function initDataTableUpdatedByCallbackWithDataFromForm($table, $form, url, $updateButton, filterDataCallback, drawCallback) {
    var process = false;
    $table.dataTable({
        serverSide: true,
        processing: true,
        ordering: false,
        "drawCallback": function () {
            process = false;
            if (drawCallback) {
                drawCallback();
            }
        },
        "ajax": {
            "method": "POST",
            "url": url,
            "data": function (requestData) {
                process = true;
                requestData.form = filterDataCallback ? filterDataCallback() : $form.serializeObject();

                return requestData;
            }
        }
    });

    if ($updateButton) {
        $updateButton.click(function () {
            if (!process) {
                $table.dataTable().fnDraw();
            }
        });
    } else {
        $form.find('input, select').on('change switchChange.bootstrapSwitch', function () {
            if (!process) {
                $table.dataTable().fnDraw();
            }
        });
    }
}

function handleAddingNewBillingEntity() {
    $('.add-billing-entity-button').click(function () {
        var saveButton = this;
        var $formModal = $("#modal-with-form");
        $formModal.modal('show');
        var $modalBody = $formModal.find('#modal-with-form-body');
        $modalBody.html(mbh.loader.html);

        var entityType = saveButton.getAttribute('data-entity-type');
        var entitySettings = BILLING_API_SETTINGS[entityType];
        var entityRoute = Routing.generate(entitySettings['creationRouteName']);
        var initFormFunc = entitySettings['initFormFunc'];

        $.ajax({
            url: entityRoute,
            method: 'GET',
            error: function () {
                $modalBody.html(mbh.error.html);
            },
            success: function (response) {
                initFormFunc(response);
                saveButton.removeAttribute('disabled');
                $('#modal-with-form-save-button').click(function () {
                    var saveButton = this;
                    saveButton.setAttribute('disabled', true);
                    var entityData = $modalBody.find('form').serialize();
                    $modalBody.html(mbh.loader.html);
                    $.ajax({
                        url: entityRoute,
                        method: "POST",
                        data: entityData,
                        success: function (result) {
                            saveButton.removeAttribute('disabled');
                            if (result.success) {
                                var entity = result['data'];
                                var newEntitySelectOptionTitle = entity[entitySettings['text']];
                                if (entitySettings['checkable'] !== undefined && entity['is_checked'] !== true) {
                                    newEntitySelectOptionTitle += ' (' + Translator.trans('020-forms.on_moderation') + ')';
                                }

                                addAndSetSelect2Option($('.' + entitySettings['fieldClass']), entity[entitySettings['id']], newEntitySelectOptionTitle);
                                $formModal.modal('hide');
                            } else {
                                initFormFunc(result);
                            }
                        },
                        error: function () {
                            $modalBody.html(mbh.error.html);
                        }
                    });
                });
            }
        })
    });
}

function handleMultiLanguageFields() {
    var changeMultiLanguagesFieldsVisibility = function(language, $fieldsGroup) {
        var $multiLanguagesFields = $fieldsGroup ? $fieldsGroup.find('.multi-languages-field') : $('.multi-languages-field');
        var $displayField = $multiLanguagesFields.filter('[data-language="' + language +'"]');
        $displayField.show();
        $multiLanguagesFields.not($displayField).hide();
    };

    var $languageOptions = $('.multi-language-select-option');
    if ($languageOptions.length > 0) {
        changeMultiLanguagesFieldsVisibility(mbh.language);
        $languageOptions.click(function () {
            var $fieldsGroup = $(this).closest('.form-group').parent();
            changeMultiLanguagesFieldsVisibility(this.getAttribute('data-language'), $fieldsGroup);
        });
    }
}

//to prevent opening of keyboard
function makeDateRangepickerReadonlyForMobileDevices() {
    if (isMobileDevice()) {
        $('.daterangepicker-input').attr('readonly', true);
    }
}

$(document).ready(function () {
    'use strict';
    docReadyForms();

    mbhStartDate();
    onHideCheckboxChange();
    disableCheckboxListen();
    initLabelTips();
    initAddTipModal();
    runGuides();
    handleAddingNewBillingEntity();
    makeDateRangepickerReadonlyForMobileDevices();
    handleMultiLanguageFields();
});
