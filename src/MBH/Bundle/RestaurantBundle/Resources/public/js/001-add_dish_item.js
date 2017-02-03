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
            content: "<span>Сначала добавьте блюда в <a href = \""+link+"\">соответствующем разделе</a></span>"
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
        //На событие подписан спиннер для количества
        $(document).trigger('prototypeAdded', $prototype);
        ++serviceIndex;
    });

});


