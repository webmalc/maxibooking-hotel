/*global window, $, alert, document, Routing, deleteLink */
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
                    $('#cash-table').dataTable().fnDraw();
                }
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

    var $filterSelectElement = $('#filter'),
        $methodSelectElement = $('#method'),
        defaultBeginValue = $('#begin').val(),
        $cashTable = $('#cash-table'),
        $cashTableByDay = $('#cash-table-by-day'),
        $showNoPaidCheckbox = $('#show_no_paid'),
        $byDayCheckbox = $('#by_day'),
        getFormData = function () {
            var data = {};
            if (!$('#begin').val() && defaultBeginValue) {
                $('#begin').val(defaultBeginValue);
            }
            data.begin = $('#begin').val();
            data.end = $('#end').val();
            data.filter = $filterSelectElement.select2('val');
            data.methods = $methodSelectElement.select2('val');
            data.show_no_paid = $showNoPaidCheckbox.prop("checked") ? 1 : 0;
            data.by_day = $byDayCheckbox.prop("checked") ? 1 : 0;

            return data;
        },
        drawCallback = function (settings) {
            $('a[data-toggle="tooltip"], li[data-toggle="tooltip"], span[data-toggle="tooltip"]').tooltip();
            $('.deleted-entry').closest('tr').addClass('danger');
            $('.not-confirmed-entry').closest('tr').addClass('info');
            $('.not-paid-entry').closest('tr').addClass('transparent-tr');
            deleteLink();
            $('.cash-table-total-in').html(settings.json.totalIn);
            $('.cash-table-total-out').html(settings.json.totalOut);
            $('.cash-table-total').html(settings.json.total);

            if (parseInt(settings.json.noConfirmedTotalIn) > 0) {
                $('.cash-table-no-confirmed-total-in').html('Не подтверждено: ' + settings.json.noConfirmedTotalIn).show();
            } else
                $('.cash-table-no-confirmed-total-in').html('Не подтверждено: ' + settings.json.noConfirmedTotalIn).hide();
            if (parseInt(settings.json.noConfirmedTotalOut) > 0) {
                $('.cash-table-no-confirmed-total-out').html('Не подтверждено: ' + settings.json.noConfirmedTotalOut).show();
            } else
                $('.cash-table-no-confirmed-total-out').html('Не подтверждено: ' + settings.json.noConfirmedTotalOut).hide();
        }

    var dataTableOptions = {
        "processing": true,
        "serverSide": true,
        "ordering": true,
        "autoWidth": false,
        "ajax": {
            "url": Routing.generate('cash_json'),
            "data": function (d) {
                d = $.extend(d, getFormData());
            }
        },
        "aoColumns": [
            {"bSortable": false}, // icon
            null, // prefix
            {"bSortable": false}, // payer
            {"bSortable": false}, // in
            null, // out
            {"bSortable": false}, //operation
            null, // date
            null, // isPaid
            null, // deletedAt
            {"bSortable": false, "class": "table-actions-td"} // actions
        ],
        "drawCallback": drawCallback
    }

    $cashTable.dataTable(dataTableOptions);


    var tableSwitcher = function () {
        this.byDay = false;
        this.initTableByDay = false;
    }
    tableSwitcher.prototype.currentTable = function () {
        return this.byDay ? $cashTableByDay : $cashTable;
    }
    tableSwitcher.prototype.switch = function () {
        this.byDay = !this.byDay
        $cashTable.parent().css('display', !this.byDay ? 'block' : 'none')
        $cashTableByDay.parent().css('display', this.byDay ? 'block' : 'none')

        if (this.byDay) {
            $showNoPaidCheckbox.bootstrapSwitch('toggleDisabled');
            if (!this.initTableByDay) {

                dataTableOptions.aoColumns = [
                    {"bSortable": false},
                    {"bSortable": false},
                    {"bSortable": false},
                    {"bSortable": false},
                    {"bSortable": false}
                ];
                $cashTableByDay.dataTable(dataTableOptions);
                this.initTableByDay = true;
            }
        } else if ($showNoPaidCheckbox.prop('disabled')) {
            $showNoPaidCheckbox.bootstrapSwitch('toggleDisabled');
        }

        this.currentTable().dataTable().fnDraw();
    }

    var sw = new tableSwitcher();

    $('#cash-filter-form input,select').not('#by_day').on('switchChange.bootstrapSwitch change', function () {
        //$('#cash-filter-form').sayt({'savenow': true});
        sw.currentTable().dataTable().fnDraw();
    });

    $byDayCheckbox.on('switchChange.bootstrapSwitch', function (event, state) {
        if (state.value) {
            $showNoPaidCheckbox.bootstrapSwitch('state', false, true);
        }
        sw.switch();
    });
});

