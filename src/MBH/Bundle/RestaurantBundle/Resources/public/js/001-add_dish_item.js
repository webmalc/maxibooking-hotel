/* global $, jQUERY, document */

$(function () {
    "use strict";
// tariff service
    var $addDishItemButton = $('.dish-item-ingredients a.add'),
        prototype = $addDishItemButton.data('prototype'),
        $servicesList = $('.dish-item-ingredients ul'),
        serviceIndex = $servicesList.find('li').length;
    prototype = '<li>' + prototype + '</li>';

    $servicesList.on('click', 'a.delete', function () {
        $(this).closest('li').remove();
        $(document).trigger('prototypeRemoved');
    });

    $addDishItemButton.on('click', function (e) {
        var newPrototype = prototype.replace(/__name__/g, serviceIndex);
        e.preventDefault();
        var $prototype = $(newPrototype);
        $servicesList.append($prototype);
        //На событие подписан спиннер для количества
        $(document).trigger('prototypeAdded', $prototype);
        ++serviceIndex;
    });

});


