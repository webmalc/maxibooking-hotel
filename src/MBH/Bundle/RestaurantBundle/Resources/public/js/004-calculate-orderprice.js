/**
 * Created by zalex on 05.07.16.
 */
/*global dishes, $, console, document, select2 */

$(function () {
    'use strict';
    var $price = $('#mbh_bundle_restaurantbundle_dishorder_dishorderitem_type_price'),

        calculatePrice = function () {
            var total = 0;
            $.each($('.amount'), function () {
                var amount = parseFloat($(this).val()),
                    dishprice = parseFloat(dishes[$(this).closest('li').find('select').val()].price);
                if (amount && dishprice) {
                    total += amount * dishprice;
                }
            });
            return total;
        },

        showPrice = function (price) {
            $price.val(price).number(true, 2);
        },

        updateDishPrice = function (dish) {
            var $dish = $(dish),
                dishId = $dish.val(),
                dishPriceField = $dish.parent().siblings().find('small'),
                dishData = dishes[dishId];
            if (dishData === 'undefined') {

                return false;
            }
            var html = $.number(dishData.price, 2) + ' ';
            dishPriceField.empty().append(html).hide().fadeIn(300);
        },

        select2Activate = function ($selectedField) {
            $selectedField.select2({
                placeholder: "Сделайте выбор",
                allowClear: false,
                width: 'resolve'
            });
        },

        init = function () {
            var price = calculatePrice();
            showPrice(price);

            $('.dish-item-ingredients').find('select').on('change.dish', function () {
                updateDishPrice(this);
                showPrice(calculatePrice());
            });

            $('.dish-item-ingredients').find('select').each(function () {
                select2Activate($(this));
                updateDishPrice(this);
            });

            //Калькуляция
            $(document).on('keyup change', '.amount', function () {
                showPrice(calculatePrice());
            });

            $(document).on('prototypeAdded', function (event, prototype) {
                var $selectField = $(prototype).find('select');
                select2Activate($selectField);
                updateDishPrice($selectField);
                $selectField.on('change.dish', function () {
                    updateDishPrice(this);
                    showPrice(calculatePrice());
                });
            });

            $(document).on('prototypeRemoved', function () {
                showPrice(calculatePrice());
            });

        };

    init();

});