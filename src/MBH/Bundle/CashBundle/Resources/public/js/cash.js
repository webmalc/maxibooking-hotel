/*global window, $ */
$(document).ready(function () {
    'use strict';

    //spinners
    $('#mbh_bundle_cashbundle_cashdocumenttype_total').TouchSpin({
        min: 1,
        max: 9007199254740992,
        step: 1,
        boostat: 5,
        maxboostedstep: 10,
        postfix: '<i class="fa fa-ruble"></i>'
    });

    //cash datatable
    $('#cash-table').each(function() {
        $(this).dataTable({
            "processing": true,
            "serverSide": true,
            "ordering": true,
            "ajax": {
                "url": Routing.generate('cash_json'),
                "data": function ( d ) {
                    d.begin = $('#begin').val();
                    d.end = $('#end').val();
                }
            },
            "aoColumns": [
                   { "bSortable": false }, // icon
                   { "bSortable": false }, // prefix
                   null, // in
                   null, // out
                   { "bSortable": false }, //method
                   { "bSortable": false }, //operation
                   null, // date
                   { "bSortable": false } // actions
            ],
            "drawCallback": function(settings) {
                $('a[data-toggle="tooltip"], li[data-toggle="tooltip"], span[data-toggle="tooltip"]').tooltip();
                deleteLink();
                $('#cash-table-total-in').html(settings.json.totalIn);
                $('#cash-table-total-out').html(settings.json.totalOut);
            }
        });
    });
    
    $('#begin, #end').change(function(){
        $('#cash-table').dataTable().fnDraw();
    });
});

