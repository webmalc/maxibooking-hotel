/*global window */
$(document).ready(function() {
    'use strict';

    //spinners
    $('#mbh_bundle_hotelbundle_hoteltype_saleDays').TouchSpin({
        min: 1,
        max: 365,
        step: 1,
        boostat: 5,
        maxboostedstep: 10
    });
    $('.spinner').TouchSpin({
        min: 0,
        max: 9007199254740992,
        step: 1,
        boostat: 5,
        maxboostedstep: 10
    });

    $("#mbh_bundle_hotelbundle_hotel_extended_type_rating").TouchSpin({
        min: 1,
        max: 5,
        step: 1,
        boostat: 1,
        maxboostedstep: 1
    });

    $('#mbh_bundle_hotelbundle_hotel_extended_type_address').select2({
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
});

