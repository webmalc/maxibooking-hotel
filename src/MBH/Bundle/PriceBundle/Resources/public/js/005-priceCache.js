/*global window, $, document, Routing, mbhGridCopy */

$(document).ready(function () {
    'use strict';

    //Show table
    var pricesProcessing = false,
        showTable = function () {
            var wrapper = $('#price-cache-overview-table-wrapper'),
                begin = $('#price-cache-overview-filter-begin'),
                end = $('#price-cache-overview-filter-end'),
                data = {
                    'begin': begin.val(),
                    'end': end.val(),
                    'roomTypes': $('#price-cache-overview-filter-roomType').val(),
                    'tariffs': $('#price-cache-overview-filter-tariff').val()
                },
                inputs = function () {
                    var input = $('input.mbh-grid-input, span.disabled-detector');
                    input.closest('td').click(function () {
                        var td = $("td[data-id='" + $(this).attr('data-id') + "']"),
                            field = $(this).children('span.input').children('input[disabled]');

                        td.children('span.input').children('input').removeAttr('disabled');

                        if (field.prop('type') === 'checkbox') {
                            field.prop('checked', !field.prop('checked')).css('checkbox-end');
                        } else {
                            field.focus();
                        }
                        td.children('span.input').children('span.disabled-detector').remove();
                    });
                    input.change(function () {
                        if (this.value === '') {
                            return;
                        }
                        var value = parseFloat(this.value);
                        if (value < 0 || isNaN(value)) {
                            this.value = 0;
                        }
                    });
                };
            if (wrapper.length === 0) {
                return false;
            }
            wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');
            if (!pricesProcessing) {
                $.ajax({
                    url: Routing.generate('price_cache_overview_table'),
                    data: data,
                    beforeSend: function () {
                        pricesProcessing = true;
                    },
                    success: function (data) {
                        wrapper.html(data);
                        begin.val($('#price-cache-overview-begin').val());
                        end.val($('#price-cache-overview-end').val());
                        inputs();
                        pricesProcessing = false;
                        mbhGridCopy();
                    },
                    dataType: 'html'
                });
            }
        };

    showTable();
    $('.price-cache-overview-filter').change(function () {
        showTable();
    });

    //generator
    (function () {
        var prices = $('input.delete-prices'),
            showMessage = function () {
                prices.each(function () {
                    var text = parseInt($(this).val(), 10) === -1 ? 'Цена будет удалена' : '';
                    $(this).closest('.col-sm-6').
                        next('.col-sm-4').
                        html('<span class="text-danger text-left input-errors">' + text + '</span>');
                });
            };
        showMessage();
        prices.change(showMessage);
    }());

    (function () {
        var percentRegexPattern = /^(\d{1,3}(\.\d{1,3})?)%$/i,
            inputs = [];

        var $hiddenPrices = $('.hidden-price');
        var $textPrices = $('.text-price');

        $hiddenPrices.each(function(index, hiddenInput) {
            var $hiddenInput = $(hiddenInput);
            var $fakeInput = $($textPrices[index]);
            inputs.push([$fakeInput, $hiddenInput]);
        })

        var $priceInput = $('#mbh_price_bundle_price_cache_generator_price'),
            getPrice = function () {
                var value = parseFloat($priceInput.val());
                return isNaN(value) ? 0 : value;
            },
            updatePriceList = function () {
                inputs.forEach(function (inputs) {
                    updatePrice(inputs[0], inputs[1]);
                });
            },
            updatePriceViewList = function () {
                inputs.forEach(function (inputs) {
                    updatePriceView(inputs[0], inputs[1]);
                });
            },
            updatePriceView = function ($fakeInput, $input) {
                var $formGroup = $fakeInput.closest('.form-group');
                $formGroup.find('.help-block.dynamic').remove();
                $formGroup.find('.price').remove();
                if ($fakeInput.val() == $input.val()) {
                    //$formGroup.removeClass('has-error');
                    //$formGroup.addClass('has-error');
                } else if ($fakeInput.val()) {
                    var $helpBlock = $fakeInput.siblings('.help-block');
                    if($helpBlock.length == 0) {
                        $helpBlock = $('<div class="help-block dynamic"></div>');
                        $fakeInput.after($helpBlock);
                    }
                    $helpBlock.append('<small class="price"> ' + (parseFloat($input.val())).toFixed(2) + ' <i class="' + mbh.currency.icon + '"></i> </small>');
                }
            },
            updatePrice = function ($fakeInput, $input) {
                var value = $fakeInput.val(),
                    percent = null,
                    price = null;
                if (percentRegexPattern.test(value)) {
                    percent = value.replace(percentRegexPattern, '$1'); //extract percent
                    percent = parseFloat(percent);
                    price = getPrice() * percent / 100;
                    $input.val(price);
                } else if (value && /^\d+$/i.test(value)) {
                    $input.val(parseInt(value));
                } else {
                    $fakeInput.val('');
                    return false;
                }
                return true;
            },
            bindEventListener = function ($fakeInput, $input) {
                $fakeInput.on('change', function () {
                    updatePrice($fakeInput, $input);
                    updatePriceView($fakeInput, $input);
                })
            }

        $priceInput.on('change', function (e) {
            updatePriceList();
            updatePriceViewList();
        });

        inputs.forEach(function (inputs) {
            bindEventListener(inputs[0], inputs[1]);
        });

        updatePriceViewList();
    })()
});
