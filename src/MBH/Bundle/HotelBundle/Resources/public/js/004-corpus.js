/*global window, document, console, $, Routing */
$(document).ready(function () {
    'use strict';
    $('#mbh_corpus_city').select2({
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
                return {results: data};
            }
        },
        initSelection: function (element, callback) {
            var id = $(element).val();
            if (id !== "") {
                $.ajax(Routing.generate('hotel_city') + '/' + id, {
                    dataType: "json"
                }).done(function (data) {
                    callback(data);
                });
            }
        },
        dropdownCssClass: "bigdrop"
    });
});