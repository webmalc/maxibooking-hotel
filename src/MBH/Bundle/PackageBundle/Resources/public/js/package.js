/*global window, $, services, document */
$(document).ready(function () {
    'use strict';

    //spinners
    $('#mbh_bundle_cashbundle_cashdocumenttype_total').TouchSpin({
        min: 1,
        max: 9007199254740992,
        boostat: 5,
        step: 0.1,
        decimals: 2,
        maxboostedstep: 10,
        postfix: '<i class="fa fa-ruble"></i>'
    });
    $('#mbh_bundle_packagebundle_package_service_type_amount, \n\
       #mbh_bundle_packagebundle_package_service_type_nights, \n\
       #mbh_bundle_packagebundle_package_service_type_persons').TouchSpin({
        min: 1,
        max: 9007199254740992,
        step: 1,
        boostat: 5,
        maxboostedstep: 10
    });
    $('.discount-spinner').TouchSpin({
        min: 1,
        max: 100,
        step: 1,
        boostat: 10,
        maxboostedstep: 20,
        postfix: '%'
    });
    $('.price-spinner').TouchSpin({
        min: 0,
        max: 9999999999999999,
        step: 0.1,
        decimals: 2,
        boostat: 10,
        maxboostedstep: 20,
        postfix: '<i class="fa fa-rub"></i>'
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
    var pTable = $('#package-table').dataTable({
        "processing": true,
        "serverSide": true,
        "ordering": true,
        "ajax": {
            "url": Routing.generate('package_json'),
            "data": function (d) {
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
            {"bSortable": false}, // icon
            null, // prefix
            null, // order
            null, // created
            null, // room
            null, //dates
            null, //tourists
            null, // price
            {"bSortable": false} // actions
        ],
        "drawCallback": function (settings, json) {
            $('a[data-toggle="tooltip"], li[data-toggle="tooltip"], span[data-toggle="tooltip"]').tooltip();
            deleteLink();
            $('.deleted-entry').closest('tr').addClass('danger');
            $('.not-confirmed-entry').closest('tr').addClass('info');
        }
    });

    if ($('#package-table').length) {
        var ptt = new $.fn.dataTable.TableTools(pTable, {
            "sSwfPath": "/bundles/mbhbase/js/vendor/datatables/swf/copy_csv_xls.swf",
            "aButtons": [
                {
                    "sExtends": "copy",
                    "sButtonText": '<i class="fa fa-files-o"></i> Скопировать'
                },
                {
                    "sExtends": "csv",
                    "sButtonText": '<i class="fa fa-file-text-o"></i> CSV'
                },
                {
                    "sExtends": "xls",
                    "sButtonText": '<i class="fa fa-table"></i> Excel'
                }
            ]
        });

        $('#list-export').append($(ptt.fnContainer()));
        $('#list-export').find('a').addClass('navbar-btn');
    }

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

        $('.package-filter').change(function () {
            $('#package-table').dataTable().fnDraw();
        });
        $('#package-filter-deleted').on('switchChange', function () {
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

    // package service form
    (function () {
        if (!$('#mbh_bundle_packagebundle_package_service_type_nights').length) {
            return;
        }
        var priceInput = $('#mbh_bundle_packagebundle_package_service_type_price'),
            nightsInput = $('#mbh_bundle_packagebundle_package_service_type_nights'),
            nightsDiv = nightsInput.closest('div.form-group'),
            personsInput = $('#mbh_bundle_packagebundle_package_service_type_persons'),
            personsDiv = personsInput.closest('div.form-group'),
            dateInput = $('#mbh_bundle_packagebundle_package_service_type_date'),
            dateDiv = dateInput.closest('div.form-group'),
            dateDefault = dateInput.val(),
            serviceInput = $('#mbh_bundle_packagebundle_package_service_type_service'),
            serviceHelp = serviceInput.next('span.help-block'),
            amountInput = $('#mbh_bundle_packagebundle_package_service_type_amount'),
            amountHelp = amountInput.closest('div.input-group').next('span.help-block'),
            hide = function () {
                nightsDiv.hide();
                personsDiv.hide();
                dateDiv.hide();
                dateInput.val(dateDefault);
                personsInput.val(1);
                nightsInput.val(1);
                amountHelp.html('');
                serviceHelp.html('<small>Услуга для добавления к броне</small>');
            },
            calc = function () {

                var info = services[serviceInput.val()];

                amountHelp.html('');
                if (serviceInput.val() !== null && typeof info !== 'undefined') {
                    var nights = nightsInput.val(),
                        price = priceInput.val() * amountInput.val() * nights * personsInput.val();
                    amountHelp.html($.number(price, 2) + ' руб. за ' + amountInput.val() + ' шт.');
                }
            },
            show = function (info) {
                hide();
                if (info.calcType === 'per_night' || info.calcType === 'per_stay') {
                    personsInput.val(services.package_guests);
                    personsDiv.show();
                }
                if (info.calcType === 'per_night') {
                    nightsInput.val(services.package_duration);
                    nightsDiv.show();
                }
                if (info.date) dateDiv.show();

                var peoplesStr = (info.calcType === 'per_night' || info.calcType === 'per_stay') ? ' за 1 человека ' : ' ';
                serviceHelp.html($.number(info.price, 2) + ' рублей' + peoplesStr + info.calcTypeStr);
                calc();
            },
            hideShow = function () {
                if (serviceInput.val() !== null) {
                    var info = services[serviceInput.val()];
                    if (typeof info === 'undefined') return;
                    priceInput.val(info.price);
                    show(info);
                } else {
                    hide();
                }
            }
            ;
        nightsDiv.change(calc);
        personsDiv.change(calc);
        amountInput.change(calc);
        serviceInput.change(calc);
        priceInput.change(calc);
        serviceInput.change(hideShow);
        hideShow();

    }());

    //order-packages panel
    (function () {
        var panel = $('.order-packages-panel'),
            link = panel.find('.panel-title');

        if ($.cookie('order-packages-panel') === undefined) {
            $.cookie('order-packages-panel', true);
        } else {
            if ($.cookie('order-packages-panel') === 'false') {
                panel.children('.panel-body').hide();
                link.children('i.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
            }
        }
        link.click(function () {
            $(this).closest('.panel-heading').next('.panel-body').toggle(150);
            $(this).children('i.fa').toggleClass('fa-caret-up').toggleClass('fa-caret-down');
            var val = $.cookie('order-packages-panel') === 'false' ? 'true' : 'false';
            $.cookie('order-packages-panel', val);
        });
    } ());

});

