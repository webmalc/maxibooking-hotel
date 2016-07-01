/* global $ */
/**
 *
 * Created by zalex on 24.06.16.
 */
$(function () {
    "use strict"
// tariff service
    var $addDishItemButton = $('.dish-item-ingredients a');
    var prototype = $addDishItemButton.data('prototype');
    var $servicesList = $('.dish-item-ingredients ul');
    var serviceIndex = $servicesList.find('li').length;
    prototype = '<li>'+prototype+'</li>';

    $servicesList.on('click', '.fa-times', function () {
        $(this).closest('li').remove();
        $(document).trigger('prototypeRemoved');
    });

    $addDishItemButton.on('click', function(e){
        var newPrototype = prototype.replace(/__name__/g, serviceIndex);
        e.preventDefault();
        var $prototype = $(newPrototype);
        $servicesList.append($prototype);
        //На событие подписан спиннер для количества
        $(document).trigger('prototypeAdded', $prototype);
        ++serviceIndex;
    });

});


