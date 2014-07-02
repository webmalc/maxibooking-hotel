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
    
    $('#mbh_bundle_hotelbundle_room_type_type_additionalPlaces').TouchSpin({
        min: 0,
        max: 10,
        step: 1,
        boostat: 2,
        maxboostedstep: 4
    });
    
    //pick-a-color
    $("#mbh_bundle_hotelbundle_room_type_type_color").pickAColor();
});

