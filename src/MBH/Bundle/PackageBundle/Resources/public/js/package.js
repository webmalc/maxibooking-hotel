/*global window, $, services, document, datepicker, deleteLink, Routing, mbh */

var docReadyPackages = function () {
    'use strict';

    //spinners
    $('#mbh_bundle_cashbundle_cashdocumenttype_total').TouchSpin({
        min: 1,
        max: 9007199254740992,
        boostat: 5,
        step: 0.1,
        decimals: 2,
        maxboostedstep: 10,
        postfix: '<i class="' + mbh.currency.icon + '"></i>'
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
        postfix: '<i class="' + mbh.currency.icon + '"></i>'
    });

    //package filter select 2
    (function () {

        var format = function (icon) {
            var originalOption = icon.element;
            return '<span><i class="' + $(originalOption).data('icon') + '"></i> ' + icon.text + '</span>';
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
        buttons: [
            'copy', 'excel', 'pdf'
        ],
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
            $('.not-paid-entry').closest('tr').addClass('transparent-tr');

            //summary
            $('#package-summary-total').html(settings.json.package_summary_total ||  '-');
            $('#package-summary-paid').html(settings.json.package_summary_paid ||  '-');
            $('#package-summary-debt').html(settings.json.package_summary_debt ||  '-');
            $('#package-summary-nights').html(settings.json.package_summary_nights ||  '-');
            $('#package-summary-guests').html(settings.json.package_summary_guests ||  '-');
        }
    });

    // package datatable filter
    (function () {

        $('#package-table-quick-links li').each(function () {
            if (parseInt($(this).find('.package-table-quick-links-count').text(), 10) === 0) {
                $(this).find('a').addClass('disabled');
            }
        });

        if ($('#package-filter-quick-link').val()) {
            $('#package-table-quick-links a[data-value="' + $('#package-filter-quick-link').val() + '"]')
                .removeClass('btn-default').addClass('btn-primary');
        }

        $('.package-filter').on('change switchChange.bootstrapSwitch', function () {
            $('#package-table').dataTable().fnDraw();
        });
        $('#package-table-quick-links a').click(function () {
            var input = $('#package-filter-quick-link');
            $('#package-table-quick-links a').removeClass('btn-primary').addClass('btn-default');
            input.val(null);

            if ($(this).attr('id') === 'package-table-quick-reset') {
                $('#package-table').dataTable().fnDraw();
                return;
            }

            $(this).removeClass('label-default').addClass('btn-primary');
            input.val($(this).attr('data-value'));
            $('#package-table').dataTable().fnDraw();
        });
    }());

    //order-packages panel
    (function () {
        var panel = $('.order-packages-panel'),
            link = panel.find('.panel-title'),
            deleted = $('#order-packages-panel-filter-deleted');

        if (!panel.length) {
            return;
        }
        if ($.cookie('order-packages-panel') !== undefined) {
            panel.children('.panel-body').hide();
            link.children('i.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
        }
        link.unbind("click");
        link.click(function () {
            $(this).closest('.panel-heading').next('.panel-body').toggle(150);
            $(this).children('i.fa').toggleClass('fa-caret-up').toggleClass('fa-caret-down');

            if ($.cookie('order-packages-panel') === undefined) {
                $.cookie('order-packages-panel', 1, {path: '/'});
            } else {
                $.removeCookie('order-packages-panel', {path: '/'});
            }

        });

        deleted.on('switchChange.bootstrapSwitch', function () {

            if ($.cookie('order-packages-panel-deleted') === undefined) {
                $.cookie('order-packages-panel-deleted', 1, {path: '/'});
            } else {
                $.removeCookie('order-packages-panel-deleted', {path: '/'});
            }

            location.reload();
        });
    }());

    //prices by day
    (function () {
        var href = $('#package-price-by-day-href'),
            prices = $('#package-price-by-day');

        if (!href.length) {
            return;
        }
        href.click(function () {
            prices.toggle(100);
            $(this).find('i').toggleClass('fa-caret-up');
        });

    }());

    //accommodation tab
    (function () {
        var checkIn = $('#mbh_bundle_packagebundle_package_accommodation_type_isCheckIn'),
            checkOut = $('#mbh_bundle_packagebundle_package_accommodation_type_isCheckOut'),
            arrivalDate = $('#mbh_bundle_packagebundle_package_accommodation_type_arrivalTime_date'),
            departureDate = $('#mbh_bundle_packagebundle_package_accommodation_type_departureTime_date'),
            arrival = $('#mbh_bundle_packagebundle_package_accommodation_type_arrivalTime_time'),
            departure = $('#mbh_bundle_packagebundle_package_accommodation_type_departureTime_time'),
            datepickerOptions = {
                language: "ru",
                autoclose: true,
                format: 'dd.mm.yyyy'
            },
            show = function () {
                if (checkIn.is(':checked')) {
                    arrival.closest('.form-group ').show();
                } else {
                    arrival.closest('.form-group ').hide();
                }
                if (checkOut.is(':checked')) {
                    departure.closest('.form-group').show();
                } else {
                    departure.closest('.form-group').hide();
                }
            },
            showOut = function () {
                if (checkIn.is(':checked')) {
                    checkOut.closest('.form-group ').show();
                } else {
                    checkOut.prop('checked', false);
                    checkOut.closest('.form-group ').hide();
                }
            };

        if (!checkIn.length) {
            return;
        }
        arrival.timepicker({showMeridian: false});
        departure.timepicker({showMeridian: false});
        arrivalDate.datepicker(datepickerOptions);
        departureDate.datepicker(datepickerOptions);
        show();
        showOut();
        checkIn.on('switchChange.bootstrapSwitch', show);
        checkIn.on('switchChange.bootstrapSwitch', showOut);
        checkOut.on('switchChange.bootstrapSwitch', show);
    }());



    // Template Document form modal
    var $modal = $('#template-document-modal'),
        $body = $modal.find('.modal-body'),
        $submitButton = $modal.find('.btn.btn-primary'),
        $modalTitle = $modal.find('.modal-title');
    $submitButton.on('click', function () {
        $body.find('form').trigger('submit');
    });
    $modal.on('show.bs.modal', function (event) {
        var $button = $(event.relatedTarget),
            type = $button.data('type'),
            entityId = $button.closest('ul').data('id');

        $.ajax(Routing.generate('document_modal_form', {id: entityId, type: type}), {
            'success' : function (response) {
                $body.html(response.html);

                var $em = $modalTitle.find('em');
                $em.text(response.name);

                $submitButton.attr('disabled', response.error);
                if(!response.error) {
                    $body.find("input[type=checkbox]").bootstrapSwitch({
                        'size': 'small',
                        'onText': 'да',
                        'offText': 'нет',
                        'labelText': '<i class="fa fa-arrows-h" style="opacity: 0.6;"></i>'
                    });
                    $body.find("select").select2();
                }
            }
        });
    }).on('close.bs.modal', function () {
        var $modal = $(this);
        var $body = $modal.find('.modal-body');
        $body.empty();
    });

    $('#mbh_bundle_packagebundle_package_accommodation_type_accommodation').select2({
        templateResult: select2TemplateResult.appendIcon
    });
}

$(document).ready(function () {
    'use strict';
    docReadyPackages();

    //package ajax tabs
    (function () {
        var tabs = $('#package-tabs');
        if (!tabs.length) {
            return null;
        }
        tabs.find('li > a').click(function (e) {
            e.preventDefault();
            $('.tab-pane').html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');
            tabs.find('li').removeClass('active');
            $(this).closest('li').addClass('active');
            var href = $(this).attr('href');
            $.get(href, function (content) {
                if (typeof window.history.pushState == 'function') {
                    window.history.pushState(null, null, href);
                }
                $('.tab-content').replaceWith(content);
                docReadyForms();
                docReadyTables();
                docReadyTourists();
                docReadyServices();
                docReadyPackages();
                docReadyCash();
                docReadyDocs();

            });
        });
    }());
});

