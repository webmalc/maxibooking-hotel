/*global window, $, alert, document, Routing, deleteLink, mbh */
var cashDocumentConfirmation = function (link) {
    'use strict';
    var icon = link.find('i');
    $('#entity-delete-confirmation').modal('hide');
    icon.attr('class', 'fa fa-spin fa-spinner');
    $.ajax({
        url: link.attr('href'),
        success: function (response) {
            if (!response.error) {
                if ($('#cash-table').length) {
                    $('#cash-table').dataTable().fnDraw();
                } else {
                    link.hide();
                    link.closest('tr').removeClass('info');
                }
            } else {
                alert('Error: ' + response.message);
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

    var paidDate = $('#entity-delete-confirmation').find('input[name=paidDate]').val();

    $.ajax({
        url: link.attr('href'),
        data: {paidDate: paidDate},
        success: function (response) {
            if (!response.error) {
                $('#cash-table').dataTable().fnDraw();
            } else {
                alert('Error: ' + response.message);
            }
        },
        dataType: 'json'
    });
};

$(document).ready(function () {
    'use strict';
    var eventDispatcher = null;
    //spinners
    $('#mbh_bundle_cashbundle_cashdocumenttype_total').TouchSpin({
        min: 0.1,
        max: 9007199254740992,
        step: 0.1,
        decimals: 2,
        boostat: 5,
        maxboostedstep: 10,
        postfix: '<i class="' + mbh.currency.icon + '"></i>'
    });

    var $filterSelectElement = $('#filter'),
        $methodSelectElement = $('#method'),
        $articleSelectElement = $('#article'),
        $begin = $('#begin'),
        defaultBeginValue = $begin.val(),
        $cashTable = $('#cash-table'),
        $cashTableByDay = $('#cash-table-by-day'),
        $showNoPaidCheckbox = $('#show_no_paid'),
        $showNoConfirmed = $('#show_no_confirmed'),
        $deletedCheckbox = $('#deleted-checkbox'),
        $byDayCheckbox = $('#by_day'),
        $showCommission = $('#show_commission'),
        $user = $('#user'),
        $typeSelect = $('#cash-type'),
        getFormData = function () {
            if (!$begin.val() && defaultBeginValue) {
                $begin.val(defaultBeginValue);
            }

            /* возможно это лишнее, и достаточно будет всегда использовать jq */
            var valueDataTable = {filter: null, method:null, article:null};
            if (isMobileDevice()) {
              valueDataTable.filter = $filterSelectElement.val();
              valueDataTable.method = $methodSelectElement.val();
              valueDataTable.article = $articleSelectElement.val();
            } else {
              valueDataTable.filter = $filterSelectElement.select2('val');
              valueDataTable.method = $methodSelectElement.select2('val');
              valueDataTable.article = $articleSelectElement.select2('val');
            }

            return {
                begin: $begin.val(),
                end: $('#end').val(),
                filter: valueDataTable.filter,
                method: valueDataTable.method,
                article: valueDataTable.article,
                show_no_paid: $showNoPaidCheckbox.prop("checked") ? 1 : 0,
                show_no_confirmed: $showNoConfirmed.prop("checked") ? 1 : 0,
                by_day: $byDayCheckbox.prop("checked") ? 1 : 0,
                show_commission: $showCommission.bootstrapSwitch('state'),
                deleted: $deletedCheckbox.prop("checked") ? 1 : 0,
                user: $user.val(),
                type: $typeSelect.val()
            };
        },
        drawCallback = function (settings) {
            $('a[data-toggle="tooltip"], li[data-toggle="tooltip"], span[data-toggle="tooltip"]').tooltip();
            $('.deleted-entry').closest('tr').addClass('danger disable-double-click');
            $('.not-confirmed-entry').closest('tr').addClass('info');
            $('.not-paid-entry').closest('tr').addClass('transparent-tr');
            deleteLink();
            $('.cash-table-total-in').html(settings.json.totalIn);
            $('.cash-table-total-out').html(settings.json.totalOut);
            $('.cash-table-total').html(settings.json.total);

            if (parseInt(settings.json.noConfirmedTotalIn) > 0) {
                $('.cash-table-no-confirmed-total-in').html(Translator.trans("cash.not_confirmed") + ': ' + settings.json.noConfirmedTotalIn).show();
            } else
                $('.cash-table-no-confirmed-total-in').html(Translator.trans("cash.not_confirmed") + ' ' + settings.json.noConfirmedTotalIn).hide();
            if (parseInt(settings.json.noConfirmedTotalOut) > 0) {
                $('.cash-table-no-confirmed-total-out').html(Translator.trans("cash.not_confirmed") + ' ' + settings.json.noConfirmedTotalOut).show();
            } else
                $('.cash-table-no-confirmed-total-out').html(Translator.trans("cash.not_confirmed") + ' ' + settings.json.noConfirmedTotalOut).hide();
        };

    var dataTableOptions = {
        language    : mbh.datatablesOptions.language,
        pageLength  : mbh.datatablesOptions.pageLength,
        "processing": true,
        "serverSide": true,
        "ordering": true,
        "autoWidth": false,
        "searchDelay": 2500,
        "ajax": {
            "url": Routing.generate('cash_json'),
            "data": function (d) {
                d = $.extend(d, getFormData());
            }
        },
        "aoColumns": [
            {"bSortable": false}, // icon
            null, // prefix
            {"class": "text-center"},
            null, // in
            null, // out
            null, //operation
            null, //article
            {"bSortable": false}, // payer
            null, // date
            null, // isPaid
            null, // user
            null, // deletedAt
            {"class": "show-on-print"}, // note
            {"bSortable": false, "class": "table-actions-td"} // actions
        ],
        "drawCallback": drawCallback
    };

    var tableSwitcher = function () {
        this.byDay = $byDayCheckbox.bootstrapSwitch('state');
        this.initCashTable = false;
        this.initTableByDay = false;
    };
    tableSwitcher.prototype.currentTable = function () {
        return this.byDay ? $cashTableByDay : $cashTable;
    };

    tableSwitcher.prototype.draw = function () {
        $cashTable.closest('.cash-table-item').css('display', !this.byDay ? 'block' : 'none');
        $cashTableByDay.closest('.cash-table-item').css('display', this.byDay ? 'block' : 'none');
        if (this.byDay) {
            $showNoPaidCheckbox.bootstrapSwitch('toggleDisabled');
            $deletedCheckbox.bootstrapSwitch('toggleDisabled');
            if (!this.initTableByDay) {
                var options = jQuery.extend({}, dataTableOptions);
                options.aoColumns = [
                    {"bSortable": false},
                    {"bSortable": false},
                    {"bSortable": false},
                    {"bSortable": false},
                    {"bSortable": false}
                ];
                $cashTableByDay.dataTable(options);
                this.initTableByDay = true;
                return true;
            }
        } else {
            if (!this.initCashTable) {
                $cashTable.dataTable(dataTableOptions);
                this.initCashTable = true;
                return true;
            }
            if ($showNoPaidCheckbox.prop('disabled')) {
                $showNoPaidCheckbox.bootstrapSwitch('toggleDisabled');
            }
            if ($deletedCheckbox.prop('disabled')) {
                $deletedCheckbox.bootstrapSwitch('toggleDisabled');
            }
        }
        this.currentTable().dataTable().fnDraw();
    };

    tableSwitcher.prototype.switch = function () {
        this.byDay = !this.byDay;
        this.draw();
    };

    var sw = new tableSwitcher();
    sw.draw();

    $('#cash-filter-form input,select').not('#by_day, #begin, #end').on('switchChange.bootstrapSwitch change', function (e) {
        if (eventDispatcher) clearTimeout(eventDispatcher);
        eventDispatcher = setTimeout(function () {
            sw.currentTable().dataTable().fnDraw();
        }, 500);

    });
    $("#begin, #end").on('changeDate', function (e) {
        if (eventDispatcher) clearTimeout(eventDispatcher);
        eventDispatcher = setTimeout(function () {
            sw.currentTable().dataTable().fnDraw();
        }, 500);
    });

    /**
     * @TODO create tableProcessing = false, too much ajax request
     */
    var isAutoSwitchDeletedCheckbox = false;
    $filterSelectElement.on('change', function () {
        var value = $filterSelectElement.select2('val');
        var isDeletedValue = value == 'deletedAt';

        if ($deletedCheckbox.prop('disabled')) {
            $deletedCheckbox.bootstrapSwitch('toggleDisabled'); //enable
        }

        if (isDeletedValue) {
            if (!$deletedCheckbox.bootstrapSwitch('state')) {
                $deletedCheckbox.bootstrapSwitch('state', true);
                isAutoSwitchDeletedCheckbox = true;
            } else {
                isAutoSwitchDeletedCheckbox = false;
            }
        } else if ($deletedCheckbox.bootstrapSwitch('state') && isAutoSwitchDeletedCheckbox) {
            $deletedCheckbox.bootstrapSwitch('state', false);
        }

        if (isDeletedValue === !$deletedCheckbox.prop('disabled')) {
            $deletedCheckbox.bootstrapSwitch('toggleDisabled');
        }
    });

    $byDayCheckbox.on('switchChange.bootstrapSwitch', function (event, state) {
        if (state.value) {
            $showNoPaidCheckbox.bootstrapSwitch('state', false, true);
            $deletedCheckbox.bootstrapSwitch('state', false, true);
        }
        sw.switch();
    });

    $('#1c-export').on('click', function (e) {
        e.preventDefault();
        var data = getFormData();
        var href = $(this).attr('href');
        window.open(href + '?' + jQuery.param(data));
    });
});
