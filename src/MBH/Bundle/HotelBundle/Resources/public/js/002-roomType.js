/*global window */
$(document).ready(function() {
    'use strict';

    //spinners
    $('#mbh_bundle_hotelbundle_room_type_type_places').TouchSpin({
        min: 1,
        max: 20,
        step: 1,
        boostat: 2,
        maxboostedstep: 4
    });

    $('#mbh_bundle_hotelbundle_room_type_type_additionalPlaces, .spinner').TouchSpin({
        min: 0,
        max: 10,
        step: 1,
        boostat: 2,
        maxboostedstep: 4
    });

    //pick-a-color
    $("#mbh_bundle_hotelbundle_room_type_type_color").pickAColor();

    //roomType rooms datatables
    $('.rooms-table').each(function() {
        $(this).dataTable({
            "processing": true,
            "serverSide": true,
            "ordering": false,
            "bAutoWidth": false,
            "ajax": Routing.generate('room_type_room_json', {'id': $(this).attr('data-room-type-id')})
        });
    });

    //hostel
    if ($('#hotel_is_hostel').length) {
        (function(){
            var calcType = $('#mbh_bundle_hotelbundle_room_type_type_calculationType'),
                places = $('#mbh_bundle_hotelbundle_room_type_type_places'),
                addPlaces = $('#mbh_bundle_hotelbundle_room_type_type_additionalPlaces'),
                showHide = function () {
                    places.closest('.form-group').show();
                    addPlaces.closest('.form-group').show();
                    if (calcType.val() == 'customPrices') {
                        places.val(1).closest('.form-group').hide();
                        addPlaces.val(0).closest('.form-group').hide();
                    }
                }
            showHide();
            calcType.change(function(){showHide()});
        }());
    }
});

