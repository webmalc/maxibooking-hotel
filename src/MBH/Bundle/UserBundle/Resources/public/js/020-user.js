/*global window, $, console, document, Routing */
$(document).ready(function () {
    'use strict';

    $('.password').pwstrength({
        ui: {
            showVerdictsInsideProgressBar: true,
            verdicts: [
                Translator.trans("020_user.bad"),
                Translator.trans("020_user.normal"),
                Translator.trans("020_user.good"),
                Translator.trans("020_user.excellent"),
                Translator.trans("020_user.super")
            ]
        },
        common: {
            minChar: 1
        }
    });

    var $authorityOrgan = $('#mbh_document_relation_authorityOrgan');
    select2Text($authorityOrgan).select2({
        minimumInputLength: 3,
        placeholder: Translator.trans("020_user.make_a_choice"),
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