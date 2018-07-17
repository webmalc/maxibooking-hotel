/*global window, $, document, Routing */
$(document).ready(function () {
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
    $("#mbh_bundle_hotelbundle_room_type_type_color").colorpicker();

    // isHostel switch
    (function () {
        var hostel = $('#mbh_bundle_hotelbundle_room_type_type_isHostel'),
            places = $('.room-type-places'),
            show = function () {
                if (hostel.is(':checked')) {
                    places.closest('.form-group ').hide();
                } else {
                    places.closest('.form-group ').show();
                }
            };

        if (!hostel.length) {
            return false;
        }
        show();
        hostel.on('switchChange.bootstrapSwitch', show);
    }());

    var initDataTable = function (roomTypeId, isFirstInit) {
        var $table = $('.rooms-table[data-room-type-id="' + roomTypeId + '"]');
        if (!isFirstInit) {
            $table.dataTable().fnDestroy();
        }

        $table.dataTable({
            "processing": true,
            "serverSide": true,
            "ordering": false,
            "bAutoWidth": false,
            "ajax": Routing.generate('room_type_room_json', {'id': roomTypeId})
        });
    };

    //roomType rooms datatables
    (function () {
        if ($('.rooms-table').length > 0) {
            var openedRoomTypeId = $('ul.nav-tabs li.active a').attr('href').substring(1);
            initDataTable(openedRoomTypeId, true);
        }
    }());

    $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function () {
        var roomTypeId = this.getAttribute('href').substring(1);
        initDataTable(roomTypeId, false);
    });

    var $taskForm = $('#mbh_hotel_bundle_room_type_tasks');
    var $addDailyBtn = $taskForm.find('.daily .btn');
    var $dailyList = $('#daily-in-list');
    var prototype = '<div class="form-inline">' + $addDailyBtn.data('prototype') + '<i class="fa fa-times"></i></div>';
    var index = $dailyList.find('.form-inline').length;
    var updateListenerOnCloseButtons = function () {
        $dailyList.find('.fa-times').on('click', function () {
            $(this).closest('.form-inline').remove();
        });
    };
    $addDailyBtn.on('click', function () {
        var newPrototype = prototype.replace(/__name__/g, index);
        $dailyList.append(newPrototype);
        $dailyList.find('select:visible').select2({
            allowClear: true
            //width: 'resolve'
        });
        ++index;
        updateListenerOnCloseButtons();
    });
    updateListenerOnCloseButtons();
});

