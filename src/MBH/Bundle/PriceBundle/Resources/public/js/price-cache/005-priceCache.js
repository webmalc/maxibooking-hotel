/*global window, $, document, Routing, mbhGridCopy */
var mbh_restrictForDateRangePicker = true;
$(document).ready(function () {
    'use strict';
    var $displayedDateInput = $('#displayed-prices-date').eq(0);
    var $displayedTimeInput = $('#displayed-prices-time');
    var $irrelevantPricesAlert = $('#irrelevant-prices-alert');
    var $irrelevantPricesAlertDate = $('#irrelevant-prices-alert-date');
    $displayedTimeInput.timepicker({
        showMeridian: false,
        minuteStep: 5,
        disableFocus: true
    });

    if (isMobileDevice()) {
      document.querySelector('.bootstrap-timepicker table').classList.add('custom-mobile-style');
    }

    if ($displayedDateInput.val()) {
        $irrelevantPricesAlert.show();
        $irrelevantPricesAlertDate.text($displayedDateInput.val() + ' ' + $displayedTimeInput.val())
    } else {
        $displayedTimeInput.val('');
        $displayedTimeInput.attr('disabled', true);
    }

    $displayedDateInput.change(function () {
        if (!this.value) {
            $irrelevantPricesAlert.hide();
            $displayedTimeInput.val('');
            $displayedTimeInput.attr('disabled', true);
        } else {
            $irrelevantPricesAlert.show();
            $irrelevantPricesAlertDate.text(this.value + ' ' + $displayedTimeInput.val());
            $displayedTimeInput.removeAttr('disabled');
            if (!$displayedTimeInput.val()) {
                $displayedTimeInput.val('00:00');
            }
        }
    });
    $irrelevantPricesAlert.find('.close').click(function () {
        $displayedDateInput.val('').trigger("change");
    });

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
                    'tariffs': $('#price-cache-overview-filter-tariff').val(),
                    'displayed-prices-date' : $displayedDateInput.val(),
                    'displayed-prices-time' : $('#displayed-prices-time').val()
                },
                inputs = function () {
                    var input = $('input.mbh-grid-input, span.disabled-detector');
                    if (!$displayedDateInput.val()) {
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
                    }
                };
            if (wrapper.length === 0) {
                return false;
            }
            wrapper.html(mbh.loader.html);
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
    $displayedTimeInput.on('hide.timepicker', function() {
        $irrelevantPricesAlertDate.text($displayedDateInput.val() + ' ' + $displayedTimeInput.val());
        showTable();
    });

    //generator
    (function () {
        var prices = $('input.delete-prices'),
            showMessage = function () {
                prices.each(function () {
                    var text = parseInt($(this).val(), 10) === -1 ? Translator.trans("005-priceCache.price_will_be_removed") : '';
                    $(this).closest('.col-sm-6').
                        next('.col-sm-4').
                        html('<span class="text-danger text-left input-errors">' + text + '</span>');
                });
            };
        showMessage();
        prices.change(showMessage);
        setGeneratorData();
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
        });

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
            };

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
