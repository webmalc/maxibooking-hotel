/*global window, $ */
$(document).ready(function () {
    'use strict';

    //roomType rooms datatables
    $('#tourist-table').each(function() {
        $(this).dataTable({
            "processing": true,
            "serverSide": true,
            "ordering": false,
            "ajax": Routing.generate('tourist_json')
        });
    });

    $('#mbh_bundle_packagebundle_touristtype_birthday, #mbh_bundle_packagebundle_package_guest_type_birthday').datepicker({
        language: "ru",
        autoclose: true,
        startView: 2
    });

    //payer select2
    $('#mbh_bundle_packagebundle_package_guest_type_tourist').on("select2-selecting", function(e) {
        var userInfo = (e.object.text).split(' '),
            lastName = userInfo[0],
            firstName = userInfo[1],
            patronymic = null,
            birthday = null
        ;

        if (userInfo.length == 4) {
            patronymic = userInfo[2];
            birthday = userInfo[3].replace(/[^0-9\.]/ig, '');
        }

        if (userInfo.length == 3) {
            if (userInfo[2].match(/[\(|\)]+/ig) != null && userInfo[2].match(/[\(|\)]+/ig).length) {
                birthday = userInfo[2].replace(/[^0-9\.]/ig, '');
            } else {
                patronymic = userInfo[2];
            }
        }

        $('#mbh_bundle_packagebundle_package_guest_type_lastName').val(lastName);
        $('#mbh_bundle_packagebundle_package_guest_type_firstName').val(firstName);
        $('#mbh_bundle_packagebundle_package_guest_type_patronymic').val(patronymic);
        $('#mbh_bundle_packagebundle_package_guest_type_birthday').val(birthday);
    });
});

