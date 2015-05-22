/*global window, $, alert, document */
var cashDocumentConfirmation = function (link) {
    'use strict';
    var icon = link.find('i'),
        number = link.closest('tr').find('div.cash-number');
    $('#entity-delete-confirmation').modal('hide');
    icon.attr('class', 'fa fa-spin fa-spinner');

    $.ajax({
        url: link.attr('href'),
        success: function (response) {
            if (!response.error) {
                if (number.length) {
                    number.removeClass('text-danger');
                    number.find('br').remove();
                    number.find('small').remove();
                }
                link.remove();
            } else {
                alert(response.message);
            }
        },
        dataType: 'json'
    });
};

var cashDocumentPay = function (link) {
    'use strict';
    var icon = link.find('i');
    $('#entity-delete-confirmation').modal('hide');
    icon.attr('class', 'fa fa-spin fa-spinner');

    $.ajax({
        url: link.attr('href'),
        success: function (response) {
            if (!response.error) {
                location.reload();
            } else {
                alert(response.message);
            }
        },
        dataType: 'json'
    });
};

$(document).ready(function () {
    'use strict';
    $('#cash-filter-form').sayt({'recover': true});

    //spinners
    $('#mbh_bundle_cashbundle_cashdocumenttype_total').TouchSpin({
        min: 0.1,
        max: 9007199254740992,
        step: 0.1,
        decimals: 2,
        boostat: 5,
        maxboostedstep: 10,
        postfix: '<i class="fa fa-ruble"></i>'
    });

    //cash datatable
    $('#cash-table').each(function () {
        $(this).dataTable({
            "processing": true,
            "serverSide": true,
            "ordering": true,
            "autoWidth": false,
            "ajax": {
                "url": Routing.generate('cash_json'),
                "data": function (d) {
                    d.begin = $('#begin').val();
                    d.end = $('#end').val();
                }
            },
            "aoColumns": [
                {"bSortable": false}, // icon
                null, // prefix
                null, // in
                null, // out
                {"bSortable": false}, //operation
                null, // date
                null, // isPaid
                null, // deletedAt
                {"bSortable": false} // actions
            ],
            "drawCallback": function (settings) {
                $('a[data-toggle="tooltip"], li[data-toggle="tooltip"], span[data-toggle="tooltip"]').tooltip();
                $('.deleted-entry').closest('tr').addClass('danger');
                $('.not-confirmed-entry').closest('tr').addClass('info');
                $('.not-confirmed-entry').closest('tr').addClass('info');
                deleteLink();
                $('#cash-table-total-in').html(settings.json.totalIn);
                $('#cash-table-total-out').html(settings.json.totalOut);

            }
        });
    });

    $('#begin, #end').change(function () {
        $('#cash-filter-form').sayt({'savenow': true});
        $('#cash-table').dataTable().fnDraw();
    });

    //payer select2
    $('#mbh_bundle_cashbundle_cashdocumenttype_payer_select, #mbh_bundle_packagebundle_package_guest_type_tourist, .findGuest').select2({
        minimumInputLength: 3,
        allowClear: true,
        ajax: {
            url: Routing.generate('cash_payer'),
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
                $.ajax(Routing.generate('cash_payer') + '/' + id, {
                    dataType: "json"
                }).done(function (data) {
                    callback(data);
                });
            }
        },
        dropdownCssClass: "bigdrop"
    });

});

