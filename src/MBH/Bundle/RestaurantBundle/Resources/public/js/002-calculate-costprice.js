/*global ingredients */
$(function () {
    var $costprice = $('#mbh_bundle_restaurantbundle_dishmenu_item_type_costPrice');
    var $price = $('#mbh_bundle_restaurantbundle_dishmenu_item_type_price');
    var $margin = $('#mbh_bundle_restaurantbundle_dishmenu_item_type_margin');
    var $switcher = $('#mbh_bundle_restaurantbundle_dishmenu_item_type_is_margin');


    if( !$costprice.length || !$price.length || !$margin.length) {
        console.error('Нет обязательного селектора!');
    }

    //Калькуляция
    $(document).on('keyup change', '.amount', function () {
        updateCostPrice();
    });

    updateCostPrice();
    checkSwitcherStatus();

    //Обработчики на существующие в коллекции ингредиенты
    $('.dish-item-ingredients').find('select').on('change.ingredients', function () {
        showIngredientPrice(this);
        updateCostPrice();
    });

    //Обработчик на добавленные ингредиенты
    $(document).on('prototypeAdded', function(event, prototype) {
        var selectField = $(prototype).find('select');
        showIngredientPrice(selectField);
        selectField.on('change.ingredients', function () {
            showIngredientPrice(this);
            updateCostPrice();
        });
    });
    $(document).on('prototypeRemoved', function() {
        updateCostPrice();
    });


    function showIngredientPrice(select) {
        var ingredientId = $(select).val();
        var priceFiled = $(select).parent().siblings().find('small');
        var ingredient = ingredients[ingredientId];
        var string = ingredient['price'] + ' ' + ingredient['currency'] + ingredient['units'];
        priceFiled.empty().append(string).hide().fadeIn(300);
    }

    function updateCostPrice() {
        $costprice.val(calculateCostPrice());
    }

    function calculateCostPrice() {
        var total = 0;
        $.each($('.amount'), function () {
            var amountValue = parseFloat($(this).val());
            var ingredientPrice = ingredients[$(this).closest('li').find('select').val()]['price'];
            if (amountValue && ingredientPrice) {
                total += amountValue*ingredientPrice;
            }
        });
        return total;
    }

    function calculateMarginPrice() {
        var costprice = calculateCostPrice();
        var margin = parseFloat($margin.val());
        var percent = parseFloat(costprice/100*margin);
        var price = costprice + percent;
        console.log(costprice + percent);
        return price;
    }

    function setPrice(price) {
        $price.val(price);
    }

    function marginPriceUpdate() {
        var price = calculateMarginPrice();
        if(!price) {
            price = 0;
        }
        setPrice(price);

    }

    function checkSwitcherStatus() {
        if ($switcher.bootstrapSwitch('state')) {
            $price.prop('disabled', true);
            $margin.prop('disabled', false);
            ingredients.dishMenuItem.price = $price.val();
            marginPriceUpdate();
            $margin.on('change.ingredients keyup.ingredients', function() {
                marginPriceUpdate();
            });
            $costprice.on('change.ingredients', function() {
                marginPriceUpdate();
            });
        } else {
            $price.prop('disabled', false);
            $margin.prop('disabled', true);
            $margin.off('.ingredients');
            $costprice.off('.ingredients');
            setPrice(ingredients.dishMenuItem.price);
        }
    }

    //Изменение типа надбавки (маржа)
    $switcher.on('switchChange.bootstrapSwitch', function () {
        checkSwitcherStatus();
    });


});
