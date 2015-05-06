(function($){
    $('#organization_city').select2({
        minimumInputLength: 3,
        ajax: {
            url: Routing.generate('hotel_city'),
            dataType: 'json',
            data: function (term) {
                return {
                    query: term // search term
                };
            },
            results: function (data) {
                return { results: data };
            }
        },
        initSelection: function(element, callback) {
            var id = $(element).val();
            if (id !== "") {
                $.ajax(Routing.generate('hotel_city') + '/' + id, {
                    dataType: "json"
                }).done(function(data) { callback(data); });
            }
        },
        dropdownCssClass: "bigdrop"
    });

    $('#organization_registration_date').datepicker({
        language: "ru",
        autoclose: true,
        startView: 2,
        format: 'dd.mm.yyyy',
    });

    var $hotelsInput = $('#organization_hotels');
    var $hotelsGroup = $hotelsInput.closest('.form-group');
    var $typeInput = $('#organization_type');
    var checkDisplayHotelsGroup = function() {
        $typeInput.val() == 'my' ? $hotelsGroup.show() : $hotelsGroup.hide();
    }
    $typeInput.on('change', function(){
        checkDisplayHotelsGroup();
        $hotelsInput.select2({width: 'resolve'});
    });

    checkDisplayHotelsGroup();
})(jQuery)
