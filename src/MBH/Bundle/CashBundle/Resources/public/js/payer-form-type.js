/* global $, document, window */
$(document).on('ready', function() {
    $('.select-payer').each(function() {
        var $select = $(this);
        var isAjax = $select.data('ajax');
        var select2Options = {};
        if(isAjax) {
            select2Options = {
                minimumInputLength: 3,
                allowClear: true,
                placeholder: 'Выберите гостя',
                ajax: {
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
                },
                dropdownCssClass: "bigdrop"
            }
        }
        $select.select2(select2Options)
    })
});