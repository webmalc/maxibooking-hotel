/*global window */
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

    $('#mbh_bundle_packagebundle_touristtype_birthday').datepicker({
        language: "ru",
        autoclose: true,
        startView: 2
    });
});

