/**
 * Created by zalex on 24.06.16.
 */
$(function () {
// tariff service
    var $addServiceButton = $('.dish-item-ingredients a');
    var prototype = $addServiceButton.data('prototype');
    var $servicesList = $('.dish-item-ingredients ul');
    var serviceIndex = $servicesList.find('li').length;
    prototype = '<li>'+prototype+'</li>';

    $servicesList.on('click', '.fa-times', function () {
        $(this).closest('li').remove();
    });

    $addServiceButton.on('click', function(e){
        var newPrototype = prototype.replace(/__name__/g, serviceIndex);
        e.preventDefault();
        var $prototype = $(newPrototype);
        $servicesList.append($prototype);
        //На событие подписан спиннер для количества
        $(document).trigger('prototypeAdded', $prototype);
        // $prototype.find('select').select2();
        // var viewService = new ViewService($prototype, serviceIndex);
        // viewService.init();
        ++serviceIndex;
    });

});



// var ViewService = function($liContainer, index) {
//     this.$liContainer = $liContainer;
//     this.index = index;
//     this.$serviceSelect = this.$liContainer.find('#mbh_price_tariff_promotions_defaultServices_'+index+'_service');
//     this.$personsInput = this.$liContainer.find('#mbh_price_tariff_promotions_defaultServices_'+index+'_persons');
//     this.$nightsInput = this.$liContainer.find('#mbh_price_tariff_promotions_defaultServices_'+index+'_nights');
//     //this.$amountInput = this.$liContainer.find('#mbh_price_tariff_promotions_defaultServices_'+index+'_amount');
//     this.calcType = this.$serviceSelect.find('option[value=' + this.$serviceSelect.val() + ']').data('type');
// }
//
// ViewService.prototype.init = function() {
//     this.update();
//     this.bindEventHandlers();
// }
//
// ViewService.prototype.update = function() {
//     this.$personsInput.addClass('hide').attr('required', false);
//     this.$nightsInput.addClass('hide').attr('required', false);
//     if(this.calcType == 'per_stay') { //за весь срок
//         //this.$personsInput.val(this.$personsInput.val());// || services.package_guests);
//         this.$personsInput.removeClass('hide').attr('required', true);
//     }
//     if (this.calcType == 'per_night') { //за cутки
//         //this.$nightsInput.val(this.$nightsInput.val());// || services.package_guests);
//         this.$nightsInput.removeClass('hide').attr('required', false);
//         this.$personsInput.removeClass('hide').attr('required', false);
//     }
//     if (this.calcType == 'day_percent') { // за услугу (% от цены за сутки)
//     }
//     if (this.calcType == 'not_applicable') { //за услугу
//     }
// }
//
// ViewService.prototype.bindEventHandlers = function() {
//     var that = this;
//     this.$serviceSelect.on('change', function() {
//         var value = that.$serviceSelect.val();
//         var $selectedOption = that.$serviceSelect.find('option[value=' + value + ']');
//         that.calcType = $selectedOption.data('type');
//         that.update();
//     });
// }

// $servicesList.find('li').each(function(index, value){
//     var $li = $(this);
//     var viewService = new ViewService($li, index);
//     viewService.init();
// });
