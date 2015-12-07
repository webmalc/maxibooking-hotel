/* global $, document, window */
$(document).on('ready', function() {
    $('select.select-payer').each(function() {
        var $select = $(this);
        var isAjax = $select.data('ajax');

        if(isAjax) {
            var select2Options = {
                //placeholder: 'Выберите плательщика',
            };
            select2Options.allowClear = true;
            select2Options.minimumInputLength = 3;
            select2Options.dropdownCssClass=  "bigdrop";
            select2Options.ajax = {
                url: Routing.generate('get_payers'),
                dataType: 'json',
                data: function (params) {
                    return {
                        query: params.term // search term
                    };
                },
                processResults: function (data) {
                    console.log(data);
                    return {results: [{id: 1, text: 12}]};
                    return data;
                }
            };
            $select.select2(select2Options);
            console.log(1);
        }

        var uniqud = $select.data('uniqud');
        var $touristInput = $('.tourist-hidden[data-uniqud='+uniqud+']');
        var $organizationInput = $('.organization-hidden[data-uniqud='+uniqud+']');

        var typeInputs = {
            tourist: $touristInput,
            organization: $organizationInput
        }

        for(var type in typeInputs) {
            var $input = typeInputs[type];
            if($input.val()) {
                if(isAjax) {
                    $select.mbhSelect2OptionsFilter(function(){}, '<option selected value="'+1+'" data-type="'+type+'">1</option>');
                } else {
                    $select.find('option[value='+value+']').attr('selected', 'selected');
                }
                $select.trigger('change');
            }
        }

        $select.on('change', function() {
            var value = $select.val();
            $touristInput.val('');
            $organizationInput.val('');
            if(value) {
                var $selectedOption = $select.find('option[value=' + value + ']');
                var type = $selectedOption.data('type');
                console.log(type);
                typeInputs[type].val(value);
            }
        })
    })
});