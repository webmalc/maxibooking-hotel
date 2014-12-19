/*global window, $ */
$(document).ready(function() {
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

    //spinners
    $('#mbh_bundle_packagebundle_package_service_type_amount').TouchSpin({
        min: 1,
        max: 9007199254740992,
        step: 1,
        boostat: 5,
        maxboostedstep: 10,
    });

    //spinners
    $('.discount-spinner').TouchSpin({
        min: 1,
        max: 100,
        step: 1,
        boostat: 10,
        maxboostedstep: 20,
        postfix: '%'
    });

    //package filter select 2
    (function () {

        var format = function (icon) {
            var originalOption = icon.element;
            return '<span class="text-' + $(originalOption).data('class') + '"><i class="fa fa fa-paper-plane-o"></i> ' + icon.text + '</span>';
        };

        $('#package-filter-status').each(function () {
            $(this).select2({
                placeholder: $(this).prop('data-placeholder'),
                allowClear: true,
                width: 'element',
                formatResult: format,
                formatSelection: format
            });
        });
    }());

    //package datatable
    $('#package-table').dataTable({
        "processing": true,
        "serverSide": true,
        "ordering": true,
        "ajax": {
            "url": Routing.generate('package_json'),
            "data": function ( d ) {
                d.begin = $('#package-filter-begin').val();
                d.end = $('#package-filter-end').val();
                d.roomType = $('#package-filter-roomType').val();
                d.status = $('#package-filter-status').val();
                d.deleted = ($('#package-filter-deleted').is(':checked')) ? 1 : 0;
                d.dates = $('#package-filter-dates').val();
                d.paid = $('#package-filter-paid').val();
                d.confirmed = $('#package-filter-confirmed').val();
                d.quick_link = $('#package-filter-quick-link').val();
            }
        },
        "order": [[2, 'desc']],
        "aoColumns": [
            { "bSortable": false }, // icon
            null, // prefix
            null, // created
            null, // room
            null, //dates
            null, //tourists
            null, // price
            { "bSortable": false } // actions
        ],
        "drawCallback": function(settings, json) {
            $('a[data-toggle="tooltip"], li[data-toggle="tooltip"], span[data-toggle="tooltip"]').tooltip();
            deleteLink();
            $('.deleted-entry').closest('tr').addClass('danger');
        }
    });

    // package datatable filter
    (function () {
        $('#package-table-filter').sayt();

        $('#package-table-quick-links li').each(function () {
             if (parseInt($(this).find('.package-table-quick-links-count').text(), 10) === 0) {
                 $(this).find('a').addClass('disabled');
             }
        });

        if ($('#package-filter-quick-link').val()) {
            $('#package-table-quick-links a[data-value="' + $('#package-filter-quick-link').val() + '"]')
                .removeClass('btn-default').addClass('btn-info');
        }

        $('.package-filter').change(function(){
            $('#package-table').dataTable().fnDraw();
        });
        $('#package-filter-deleted').on('switchChange', function() {
            $('#package-table').dataTable().fnDraw();
        });
        $('#package-table-quick-links a').click(function () {
            var input = $('#package-filter-quick-link');
            $('#package-table-quick-links a').removeClass('btn-info').addClass('btn-default');
            input.val(null);

            if ($(this).attr('id') == 'package-table-quick-reset') {
                $('#package-table').dataTable().fnDraw();
                return;
            }

            $(this).removeClass('label-default').addClass('btn-info');
            input.val($(this).attr('data-value'));
            $('#package-table').dataTable().fnDraw();
        });
    }());

});

