/*global ingredients, $, console, document */
$(function () {
    'use strict';
    var $price = $('#mbh_bundle_restaurantbundle_dishmenu_item_type_price'),

        $costprice = $('#mbh_bundle_restaurantbundle_dishmenu_item_type_costPrice'),

        $margin = $('#mbh_bundle_restaurantbundle_dishmenu_item_type_margin'),

        $switcher = $('#mbh_bundle_restaurantbundle_dishmenu_item_type_is_margin'),

    //Подсчет себестоимости
        calculateCostPrice = function () {
            var total = 0;
            $.each($('.amount'), function () {
                var amountValue = parseFloat($(this).val()),
                    ingredientPrice = parseFloat(ingredients[$(this).closest('li').find('select').val()].price);
                if (amountValue && ingredientPrice) {
                    total += amountValue * ingredientPrice;
                }
            });
            return total;
        },

    //Вывести себестоимость
        showCostPrice = function (price) {
            $costprice.val(price).number(true, 2);
            $costprice.trigger('change');
        },

    //Вывести себестоимость
        updateCostPrice = function () {
            showCostPrice(calculateCostPrice);
        },

    //Вывод цены под списком ингредиентов
        updateIngredientPrice = function (ingredient) {
            var $ingredient = $(ingredient),
                ingredientId = $ingredient.val(),
                ingredientPriceField = $ingredient.parent().siblings().find('small'),
                ingredientData = ingredients[ingredientId],
                html = ingredientData.price + ' ' + ingredientData.currency + ingredientData.units;
            ingredientPriceField.empty().append(html).hide().fadeIn(300);
        },

    //Подсчет цены с маржой
        calculateMarginPrice = function () {
            var costprice = calculateCostPrice(),
                margin = parseFloat($margin.val()),
                percent = parseFloat(costprice / 100 * margin)

            return costprice + percent;
        },

        marginPriceUpdate = function () {
            var price = calculateMarginPrice() || 0;
            $price.val($.number(price, 2));
        },

        checkSwitcherStatus = function () {
            if ($switcher.bootstrapSwitch('state')) {
                $price.prop('disabled', true);
                $margin.prop('disabled', false);
                ingredients.dishMenuItem.price = $price.val();
                marginPriceUpdate();
                $costprice.on('change.ingredients', function () {
                    marginPriceUpdate();
                });
            } else {
                $price.prop('disabled', false);
                $margin.prop('disabled', true);
                $costprice.off('.ingredients');
                //Возращаем сохраненное значение
                $price.val(ingredients.dishMenuItem.price);
            }
        },

        init = function () {
            if (!$costprice.length || !$price.length || !$margin.length) {
                console.error('Нет обязательного селектора!');
            }

            //Обработчики на существующие в коллекции ингредиенты
            $('.dish-item-ingredients').find('select').on('change.ingredients', function () {
                updateIngredientPrice(this);
                updateCostPrice();
            });

            //Калькуляция
            $(document).on('keyup change', '.amount', function () {
                updateCostPrice();
            });

            //Обработчик на добавленные ингредиенты
            $(document).on('prototypeAdded', function (event, prototype) {
                var selectField = $(prototype).find('select');
                updateIngredientPrice(selectField);
                selectField.on('change.ingredients', function () {
                    updateIngredientPrice(this);
                    updateCostPrice();
                });
            });
            $(document).on('prototypeRemoved', function () {
                updateCostPrice();
            });

            $margin.on('change.ingredients keyup.ingredients', function () {
                marginPriceUpdate();
            });

            $switcher.on('switchChange.bootstrapSwitch', function () {
                checkSwitcherStatus();
            });


        };

    init();
    checkSwitcherStatus();
    updateCostPrice();
});

