/*global window, document, Routing, console, str, $, select2 */

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

var docReadyForms = function () {
    'use strict';

    $('form.remember input, form.remember select, form.remember textarea').phoenix();

    $(".timepicker").timepicker({
        showMeridian: false
    });

    //only int
    $('.only-int').change(function () {
        if (this.value === '') {
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
        'labelText': '<i class="fa fa-arrows-h" style="opacity: 0.6;"></i>',
    };
    $('input[type="checkbox"]')
        .not('.plain-html')
        .not('.checkbox-mini')
        .bootstrapSwitch(bootstrapSwitchConfig);

    bootstrapSwitchConfig.size = 'mini'
    bootstrapSwitchConfig.labelText = '&nbsp;'
    $('.checkbox-mini').bootstrapSwitch(bootstrapSwitchConfig);

    //Select2 configuration
    $('select').not('.plain-html').select2({
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

    //areYouSure
    //$('.form-horizontal, .form-inline, .are-you-sure').areYouSure({'message': 'Внесенные изменения не сохранены!'});

    //Datepicker configuration
    $('.datepicker').datepicker({
        language: "ru",
        todayHighlight: true,
        autoclose: true,
        format: 'dd.mm.yyyy'
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

    //datepiker select
    (function () {
        var select = $('select.datepiker-period-select'),
            begin = $('.begin-datepiker'),
            end = $('.end-datepiker'),
            setDates = function () {
                var period = begin.val() + '-' + end.val();
                if (!select.val()) {
                    select.select2("val", period);
                    return;
                }
                var dates = select.val().split('-');
                begin.val(dates[0]);
                end.val(dates[1]).trigger('change');
            };

        if (!select.length || !begin.length || !end.length) {
            return;
        }
        $('.datepiker-period-select').css('width', '130px');
        select.on('change', setDates);
        setDates();
    }());

    (function () {
        var begin = $('.begin-datepiker'),
            end = $('.end-datepiker'),
            set = function () {
                if (!begin.hasClass('not-set-date')) {
                    begin.datepicker('setEndDate', end.datepicker('getDate'));
                }
                if (!end.hasClass('not-set-date')) {
                    end.datepicker('setStartDate', begin.datepicker('getDate'));
                }
            };
        set();
        begin.change(function () {
            set();
            if (!end.val()) {
                end.focus();
            }
        });
        end.change(function () {
            set();
            if (!begin.val()) {
                begin.focus();
            }
        });
    }());
    $('.roomTypeTypeEditor').wysihtml5({
        "html": false,
        "stylesheets": false,
        "image": false
    });
    //payer select2
    $('.findGuest').select2({
        minimumInputLength: 3,
        allowClear: true,
        ajax: {
            url: Routing.generate('ajax_tourists'),
            dataType: 'json',
            data: function (term) {
                return {
                    query: term // search term
                };
            },
            results: function (data) {
                return {results: data};
            }
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
};


/**
 * tagsSelectWidget
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
(function ($) {
    var methods = {
        init : function (options) {
            return this.each(function () {
                var $widget = $(this);

                if ($widget.is("select")) {
                    $widget = $widget.wrap('<div class="tags-select-widget"></div>').closest('.tags-select-widget').prepend('<div class="list"></div>');
                }
                var $select = $widget.find('select');
                var inputName = $select.attr('name');
                var isMultiple = $select.attr('multiple');
                var isRequired = $select.attr('required');
                if (isMultiple) {
                    $select.removeAttr('multiple');
                } else {
                    inputName += '[]';
                }
                if (isRequired) {
                    $select.removeAttr('required');
                }
                $select.attr('name', inputName.replace(/(\[.*\])/g, '') + '_fake');
                $select.on('change', function () {
                    var $this = $(this),
                        value = $this.val();
                    if (value) {
                        var text = $this.find('option[value=' + value + ']').text();
                        var input = '<input type="hidden" name="' + inputName + '" value="' + value + '">';
                        var item = '<div class="btn btn-xs btn-default">' + text + ' <i class="fa fa-times"></i>' + input + '<div>';
                        $widget.find('.list').append(item);
                        $this.val('');
                        $select.select2('data', null);//select2('updateResults');
                    }
                });
                $widget.on('click', '.list .btn', function () {
                    $(this).remove();
                });
            });
        },
        clear : function () {
            return this.each(function () {
                $(this).find('.list').empty();
            });
        },
        update : function (content) {}
    };


    $.fn.tagsSelectWidget = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Метод с именем ' +  method + ' не существует');
        }
    };
})(window.jQuery);

$('.tags-select-widget').tagsSelectWidget();
$('.tags-select-input-widget').tagsSelectWidget();





$(document).ready(function () {
    'use strict';

    docReadyForms();
});