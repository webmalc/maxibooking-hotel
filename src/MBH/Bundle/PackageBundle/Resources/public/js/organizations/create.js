/*
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
/*global $, window,console, document*/
(function ($) {
    var $registrationDate = $('#organization_registration_date');
    if ($registrationDate) {
        $registrationDate.datepicker({
            language: "ru",
            autoclose: true,
            startView: 2,
            format: 'dd.mm.yyyy',
        });
    }

    var $hotelsInput = $('#organization_hotels');
    var $hotelsGroup = $hotelsInput.closest('.form-group');
    var $typeInput = $('#organization_type');
    var checkDisplayHotelsGroup = function () {
        $typeInput.val() == 'my' ? $hotelsGroup.show() : $hotelsGroup.hide();
    }
    if ($typeInput.length > 0) {
        $typeInput.on('change', function(){
            checkDisplayHotelsGroup();
            $hotelsInput.select2({width: 'resolve'});
        });

        checkDisplayHotelsGroup();
    } else {
        $hotelsGroup.show()
    }

    $("#organization_inn").mask("9999999999?99");
    $("#organization_kpp").mask("999999999");

})(jQuery);
