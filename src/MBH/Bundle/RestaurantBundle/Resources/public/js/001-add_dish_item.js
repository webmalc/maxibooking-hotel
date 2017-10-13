/* global $, jQUERY, document, Routing */

$(function () {
    "use strict";
// tariff service
    var noAdd = false;
    if('undefined' != typeof(dishes) && dishes == null) {
        noAdd = true;
    }


    var $addDishItemButton = $('.dish-item-ingredients a.add'),
        prototype = $addDishItemButton.data('prototype'),
        $servicesList = $('.dish-item-ingredients ul'),
        serviceIndex = $servicesList.find('li').length;
    prototype = '<li>' + prototype + '</li>';

    $servicesList.on('click', 'a.delete', function () {
        $(this).closest('li').remove();
        $(document).trigger('prototypeRemoved');
    });

    if(noAdd) {
        var link = Routing.generate('restaurant_dishmenu_category');
        $addDishItemButton.popover({
            trigger: 'click focus',
            html: true,
            content: Translator.trans("001-add_dish_item.first_add_dishes", {"hrefTagStart" : '<a href = \"' + link + '\">'})
        });
    }

    $addDishItemButton.on('click', function (e) {
        e.preventDefault();
        if(noAdd) {
            return false;
        }
        var newPrototype = prototype.replace(/__name__/g, serviceIndex);
        var $prototype = $(newPrototype);
        $servicesList.append($prototype);
        $(document).trigger('prototypeAdded', $prototype);
        ++serviceIndex;
    });

});


