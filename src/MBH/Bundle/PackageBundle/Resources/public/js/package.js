/*global window, $, services, document, datepicker, deleteLink, Routing, mbh */
var initAccommodationTab = function () {
    'use strict'

    //Package relocation
    var packageRelocationDate = $('#package-relocation-date');
    if (packageRelocationDate.length) {
        packageRelocationDate.datepicker('setStartDate', packageRelocationDate.attr('data-begin'));
        packageRelocationDate.datepicker('setEndDate', packageRelocationDate.attr('data-end'));

        $('#package-relocation-form').submit(function (event) {
            event.preventDefault();
            window.location = Routing.generate(
                'package_relocation',
                {'id': $(this).attr('data-package-id'), 'date': packageRelocationDate.val()},
                true
            );
        });
    }

    var $form = $('form[name=mbh_bundle_packagebundle_package_accommodation_type]');

    var userConfirmation = false;
    var lateEarlyDateChecker = new LateEarlyDateChecker(function () {
    }, function () {
    });

    var $checkIn = $('#mbh_bundle_packagebundle_package_accommodation_type_isCheckIn'),
        $checkOut = $('#mbh_bundle_packagebundle_package_accommodation_type_isCheckOut'),
        $arrivalDate = $('#mbh_bundle_packagebundle_package_accommodation_type_arrivalTime_date'),
        $departureDate = $('#mbh_bundle_packagebundle_package_accommodation_type_departureTime_date'),
        $arrival = $('#mbh_bundle_packagebundle_package_accommodation_type_arrivalTime_time'),
        $departure = $('#mbh_bundle_packagebundle_package_accommodation_type_departureTime_time'),
        datepickerOptions = {
            autoclose: true,
            format: 'dd.mm.yyyy'
        };

    $arrivalDate.on('change', function () {
        userConfirmation = false
    });
    $arrival.on('change', function () {
        userConfirmation = false
    });

    $departureDate.on('change', function () {
        userConfirmation = false
    });
    $departure.on('change', function () {
        userConfirmation = false
    });

    var show = function () {
        if ($checkIn.is(':checked')) {
            $arrival.closest('.form-group ').show();
        } else {
            $arrival.closest('.form-group ').hide();
        }
        if ($checkOut.is(':checked')) {
            $departure.closest('.form-group').show();
        } else {
            $departure.closest('.form-group').hide();
        }
    }
    var showOut = function () {
        if ($checkIn.is(':checked')) {
            $checkOut.closest('.form-group ').show();
        } else {
            $checkOut.prop('checked', false);
            $checkOut.closest('.form-group ').hide();
        }
    };

    if (!$checkIn.length) {
        return;
    }
    $arrival.timepicker({showMeridian: false});
    $departure.timepicker({showMeridian: false});
    $arrivalDate.datepicker(datepickerOptions);
    $departureDate.datepicker(datepickerOptions);
    show();
    showOut();
    $checkIn.on('switchChange.bootstrapSwitch', show);
    $checkIn.on('switchChange.bootstrapSwitch', showOut);
    $checkOut.on('switchChange.bootstrapSwitch', show);


    var getConfirmText = function () {
        var pattern = /(\d{2})\.(\d{2})\.(\d{4})/;

        var arrivalDate = $arrivalDate.val();
        var arrivalTime = $arrival.val();
        var arrivalDate = new Date(arrivalDate.replace(pattern, '$3-$2-$1') + 'T' + arrivalTime + ':00');
        arrivalDate.setHours(arrivalDate.getHours() - mbh.UTCHoursOffset);
        var isSuitArrival = !$checkIn.is(':checked') || arrivalDate && arrivalTime && lateEarlyDateChecker.checkLateArrival(Package.begin, arrivalDate);

        var departureDate = $departureDate.val();
        var departureTime = $departure.val();
        var departureDate = new Date(departureDate.replace(pattern, '$3-$2-$1') + 'T' + departureTime + ':00');
        departureDate.setHours(departureDate.getHours() - mbh.UTCHoursOffset);
        var isSuitDeparture = !$checkOut.is(':checked') || departureDate && departureTime && lateEarlyDateChecker.checkEarlyDeparture(Package.end, departureDate);

        lateEarlyDateChecker.status = null;
        if (earlyCheckInServiceIsEnabled && lateCheckOutServiceIsEnabled && !isSuitArrival && !isSuitDeparture) {
            lateEarlyDateChecker.status = LateEarlyDateChecker.STATUS_BOTH;
        } else if (earlyCheckInServiceIsEnabled && !isSuitArrival) {
            lateEarlyDateChecker.status = LateEarlyDateChecker.STATUS_ARRIVAL;
        } else if (lateCheckOutServiceIsEnabled && !isSuitDeparture) {
            lateEarlyDateChecker.status = LateEarlyDateChecker.STATUS_DEPARTURE;
        }

        if (lateEarlyDateChecker.status) {
            return lateEarlyDateChecker.statusTexts[lateEarlyDateChecker.status];
        }
        return null;
    }

    var confirmed = false;
    var formHandler = function (e) {
        if (!confirmed) {
            var text = [];
            if ($checkOut.is(':checked') && Package.debt > 0) {
                text.push(Translator.trans("package.order_is_not_paid"));
            }
            var confirmText = getConfirmText();
            if (confirmText) {
                text.push(confirmText);
            }
            if (text.length > 0) {
                e.preventDefault();
                mbh.alert.show(null, Translator.trans("package.confirmation"), text.join('<br>'), Translator.trans("package.continue"), null, 'danger', function () {
                    mbh.alert.hide();
                    confirmed = true;
                    $('button[type=submit][name=save]').trigger('click');
                })
            }
        }
    }

    $form.on('submit', function (e) {
        formHandler(e);
    });
}

var deleteUndaid = function () {
    $('.booking-delete-link').on('click', function (e) {
        e.preventDefault();
        $('#modal_delete_package').attr('data-order', $(this).data('order'));
        $('.modal-body').html(mbh.loader.html);

        return $.ajax({
            url: Routing.generate('package_delete', {'id': $(this).data('id')}),
            type: "GET",
            data: {},
            success: function (urlFromController) {
                $('#modal_delete_package').html(urlFromController);
                $('#mbh_bundle_packagebundle_delete_reason_type_order').val($('#modal_delete_package').attr('data-order'));
                $('select#mbh_bundle_packagebundle_delete_reason_type_deleteReason').select2();
            }
        });
    });
    $('.order-booking-delete-link').on('click', function (e) {
        e.preventDefault();
        $('.modal-body').html(mbh.loader.html);

        return $.ajax({
            url: Routing.generate('order_delete', {'id': $(this).data('id')}),
            type: "GET",
            data: {},
            success: function (urlFromController) {
                $('#modal_delete_package').html(urlFromController);
                $('#mbh_bundle_packagebundle_delete_reason_type_order').val($('#modal_delete_package').attr('data-order'));
                $('select#mbh_bundle_packagebundle_order_delete_reason_type_deleteReason').select2();
            }
        });
    });
}

var docReadyPackages = function () {
    'use strict';
    deleteUndaid();

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
        step: 0.01,
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
    var pTable = $('#package-table')
        .on('init.dt', function () {
            var  timeout = 0;
            var $input = $('.dataTables_filter input');
            $input.unbind();
            $input.on('keyup keydown', function (event) {
                clearTimeout(timeout);
                var that = this;
                timeout = setTimeout(function () {
                    searchTable(event, $(that))
                }, 500);
            });

        })
        .dataTable({
        searchDelay: 350,
        dom: "12<'row'<'col-sm-6'Bl><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fa fa-table" title="Excel" data-toggle="tooltip" data-placement="bottom"></i>',
                className: 'btn btn-default btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fa fa-file-pdf-o" title="PDF" data-toggle="tooltip" data-placement="bottom"></i>',
                className: 'btn btn-default btn-sm',
                exportOptions: {
                    stripNewlines: false
                }
            },
            {
                text: '<i class="fa fa-file-excel-o" title="CSV" data-toggle="tooltip" data-placement="bottom"></i>',
                className: 'btn btn-default btn-sm',
                action: function (e, dt, button, config) {
                    $.ajax({
                        url: Routing.generate('package_csv'),
                        type: 'POST',
                        data: {},
                        success: function (response) {

                            $('<div id="template-document-csv-modal" class="modal"> </div> ').insertAfter($('.content-wrapper'));
                            var $modal = $('#template-document-csv-modal');
                            var $body = $modal.find('.modal-body');

                            $modal.html(response);
                            $modal.modal('show');

                            $modal.find('input[type=checkbox]').bootstrapSwitch({
                                'size': 'mini',
                                'onColor': 'success',
                                'onText': Translator.trans("package.yes"),
                                'offText': Translator.trans("package.no")
                            });
                            var form = $modal.find("form");

                            form.submit(function () {
                                $('#mbh_bundle_packagebundle_package_csv_type_roomType').val($('#package-filter-roomType').val())
                                $('#mbh_bundle_packagebundle_package_csv_type_status').val($('#package-filter-status').val())
                                $('#mbh_bundle_packagebundle_package_csv_type_deleted').val($('#package-filter-deleted').val())
                                $('#mbh_bundle_packagebundle_package_csv_type_begin').val($('#package-filter-begin').val())
                                $('#mbh_bundle_packagebundle_package_csv_type_end').val($('#package-filter-end').val())
                                $('#mbh_bundle_packagebundle_package_csv_type_dates').val($('#package-filter-dates').val())
                                $('#mbh_bundle_packagebundle_package_csv_type_paid').val($('#package-filter-paid').val())
                                $('#mbh_bundle_packagebundle_package_csv_type_confirmed').val($('#package-filter-confirmed').val())
                                $('#mbh_bundle_packagebundle_package_csv_type_deleted').val(($('#package-filter-deleted').is(':checked')) ? 1 : 0)
                                $('#mbh_bundle_packagebundle_package_csv_type_quick_link').val($('#package-table-quick-links .btn-primary').attr('data-value'))
                                $('.modal.in').modal('hide')
                            });

                        }
                    });

                }
            }
        ],
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
            null, // created
            null, // room
            null, //dates
            null, //tourists
            null, // price
            {"bSortable": false} // actions
        ],
        "drawCallback": function (settings, json) {

            $('a[data-toggle="tooltip"], li[data-toggle="tooltip"], span[data-toggle="tooltip"]').tooltip();
            $('.deleted-entry').closest('tr').addClass('danger');
            $('.not-confirmed-entry').closest('tr').addClass('info');
            $('.not-paid-entry').closest('tr').addClass('transparent-tr');
            $('.booking-delete-link').attr('data-toggle', 'modal');

            deleteUndaid();

            //summary
            $('#package-summary-total').html(settings.json.package_summary_total || '-');
            $('#package-summary-paid').html(settings.json.package_summary_paid || '-');
            $('#package-summary-debt').html(settings.json.package_summary_debt || '-');
            $('#package-summary-nights').html(settings.json.package_summary_nights || '-');
            $('#package-summary-guests').html(settings.json.package_summary_guests || '-');
        }
    });

    var searchTable = function (event, $search) {
        var value = $search.val();
        if (value.length >= 4 || event.keyCode === 13 || value.length === 0) {
            pTable.api().search(value).draw();
        }
    };

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

    initAccommodationTab();


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
            'success': function (response) {
                $body.html(response.html);

                var $em = $modalTitle.find('em');
                $em.text(response.name);

                $submitButton.attr('disabled', response.error);
                if (!response.error) {
                    $body.find("input[type=checkbox]").bootstrapSwitch({
                        'size': 'small',
                        'onText': Translator.trans("package.yes"),
                        'offText': Translator.trans("package.no"),
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


    discountInit($('#mbh_bundle_packagebundle_package_main_type_discount'), $('#mbh_bundle_packagebundle_package_main_type_isPercentDiscount'))
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
            $('.tab-pane').html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i>' + Translator.trans("package.processing") + '...</div>');
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
                docReadyAccommodations();
            });
        });
    }());
});

