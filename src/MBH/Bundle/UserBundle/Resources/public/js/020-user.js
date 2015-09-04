/*global window, $, console, document, Routing */
$(document).ready(function () {
    'use strict';

    $('.password').pwstrength({
        ui: {
            showVerdictsInsideProgressBar: true,
            verdicts: ["Плохой", "Обычный", "Хороший", "Отличный", "Супер"]
        },
        common: {
            minChar: 8
        }
    });

    $('#mbh_document_relation_authorityOrgan').select2({
        minimumInputLength: 3,
        placeholder: "Сделайте выбор",
        allowClear: true,
        ajax: {
            url: Routing.generate('authority_organ_json_list'),
            dataType: 'json',
            data: function (term) {
                return {
                    query: term // search term
                };
            },
            results: function (data) {
                var results = [];
                $.each(data, function (k, v) {
                    results.push({id: k, text: v});
                });
                return {results: results};
            }
        },
        initSelection: function (element, callback) {
            var id = $(element).val();
            if (id !== "") {
                $.ajax(Routing.generate('ajax_authority_organ', {id: id}), {
                    dataType: "json"
                }).done(function (data) {
                    callback(data);
                });
            }
        },
        dropdownCssClass: "bigdrop"
    });
})