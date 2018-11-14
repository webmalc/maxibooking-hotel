/*global window, $, services, document, select2, mbh, Translator, currentService */

var docReadyServices = function() {
    "use strict";

    // package service form
    (function() {
        var nightsInput = $('#mbh_bundle_packagebundle_package_service_type_nights');
        if (!nightsInput.length) {
            return;
        }
        var priceInput = $('#mbh_bundle_packagebundle_package_service_type_price'),
            nightsDiv = nightsInput.closest('div.form-group'),
            personsInput = $('#mbh_bundle_packagebundle_package_service_type_persons'),
            personsDiv = personsInput.closest('div.form-group'),
            recalcCausedByGuestsNumberChangeInput = $('#mbh_bundle_packagebundle_package_service_type_recalcCausedByTouristsNumberChange'),
            recalcCausedByGuestsNumberChangeDiv = recalcCausedByGuestsNumberChangeInput.closest('div.form-group'),
            dateInput = $('#mbh_bundle_packagebundle_package_service_type_begin'),
            dateDiv = dateInput.closest('div.form-group'),
            endInput = $('#mbh_bundle_packagebundle_package_service_type_end'),
            endDiv = endInput.closest('div.form-group'),
            dateDefault = dateInput.val(),
            serviceInput = $('#mbh_bundle_packagebundle_package_service_type_service'),
            serviceHelp = serviceInput.next('span.help-block'),
            amountInput = $('#mbh_bundle_packagebundle_package_service_type_amount'),
            timeInput = $('#mbh_bundle_packagebundle_package_service_type_time'),
            timeDiv = timeInput.closest('div.form-group'),
            recalcInput = $('#mbh_bundle_packagebundle_package_service_type_recalcWithPackage'),
            arrivalInput = $('#mbh_bundle_packagebundle_package_service_type_includeArrival'),
            departureInput = $('#mbh_bundle_packagebundle_package_service_type_includeDeparture'),
            recalcDiv = $('.toggle-date').closest('div.form-group'),
            form = recalcInput.closest('form[name="mbh_bundle_packagebundle_package_service_type"]'),
            hide = function() {
                recalcCausedByGuestsNumberChangeDiv.hide();
                nightsDiv.hide();
                personsDiv.hide();
                recalcDiv.hide();
                dateDiv.hide();
                endDiv.hide();
                timeDiv.hide();
                dateInput.val(dateInput.val() || dateDefault);
                personsInput.val(personsInput.val() || 1);
                nightsInput.val(nightsInput.val() || 1);
                amountInput.closest('div.input-group').next('span.help-block').html('');
                serviceHelp.html('<small>' + Translator.trans('service.service_for_addtition_to_package') + '</small>');
                serviceHelp.html('<small>' + Translator.trans('service.service_for_addtition_to_package') + '</small>');
            },
            calc = function() {

                var info = services[serviceInput.val()];
                amountInput.closest('div.input-group').next('span.help-block').html('');
                if (serviceInput.val() !== null && typeof info !== 'undefined') {
                    var nights = nightsInput.val(),
                        price = priceInput.val() * amountInput.val() * nights * personsInput.val();
                    amountInput.closest('div.input-group').next('span.help-block').html($.number(price, 2) + ' ' + mbh.currency.text + ' ' + Translator.trans('service.price_for_amount', {'amount' : amountInput.val()}));
                }
            },
            show = function(info) {
                hide();
                if (info.calcType === 'per_night' || info.calcType === 'per_stay') {
                    personsInput.val(personsInput.val() || services.package_guests);
                    personsDiv.show();
                    recalcCausedByGuestsNumberChangeDiv.show();
                    var isRecalcWithGuests = typeof currentService !== 'undefined' && currentService.serviceId === serviceInput.val()
                        ? currentService.isRecalcWithGuests
                        : info.isRecalcWithGuests;
                    recalcCausedByGuestsNumberChangeInput.bootstrapSwitch('state', isRecalcWithGuests);
                }
                if (info.calcType === 'per_night') {
                    nightsInput.val(nightsInput.val() || services.package_duration);
                    nightsDiv.show();
                }
                priceInput.show();
                //amountInput.val(services.service_amount);
                amountInput.show();
                if (info.date) {
                    dateDiv.show();
                }
                if (info.time) {
                    timeDiv.show();
                }
                if (info.calcType === 'per_stay') {
                    dateDiv.show();
                    endDiv.show();
                    recalcDiv.show();
                }

                var peoplesStr = (info.calcType === 'per_night' || info.calcType === 'per_stay') ? ' ' + Translator.trans('service.for_one_man') + ' ' : ' ';

                if (info.calcType !== 'day_percent') {
                    serviceHelp.html($.number(info.price, 2) + ' ' + mbh.currency.text + peoplesStr + info.calcTypeStr);
                } else {
                    serviceHelp.html(info.priceRaw + '% ' + info.calcTypeStr);
                }
                calc();
            },
            hideShow = function(event) {

                if (serviceInput.val() !== null) {
                    var info = services[serviceInput.val()],
                        priceNew = info.price,
                        recalcDefault = info.recalcWithPackage,
                        arrivalDefault = info.includeArrival,
                        departureDefault = info.includeDeparture
                    ;

                    if (info.calcType === 'day_percent' && services.package_prices_by_date && dateInput.val()) {
                        var dayPrice = services.package_prices_by_date[dateInput.val()];
                        if (dayPrice) {
                            priceNew = (dayPrice * info.priceRaw) / 100;
                        }
                    }
                    if (typeof info === 'undefined') return;

                    if (!priceInput.val() || event) {
                        priceInput.val(priceNew);
                    }
                    
                    if (!form.hasClass('package-service-edit')) {
                        if (event && event.target.id !== dateInput.attr('id')) {
                            dateInput.val(info.begin);
                            endInput.val(info.end);
                        }
                        recalcInput.prop('checked', parseInt(recalcDefault)).trigger('change');
                        arrivalInput.prop('checked', parseInt(arrivalDefault)).trigger('change');
                        departureInput.prop('checked', parseInt(departureDefault)).trigger('change');
                    }
                    show(info);
                } else {
                    hide();
                }
            };
        timeInput.parent().addClass('bootstrap-timepicker');
        timeInput.timepicker({
            showMeridian: false
        });
        nightsDiv.change(calc);
        personsDiv.change(calc);
        amountInput.change(calc);
        serviceInput.change(calc);
        priceInput.change(calc);
        serviceInput.change(hideShow);
        dateInput.change(hideShow);
        hideShow();

    }());

    //Service selector
    (function() {
        var catSelect = $('#select-category'),
            serviceSelect = $('#select-service'),
            servicesHtml = serviceSelect.html(),
            show = function() {
                serviceSelect.prop('disabled', true);
                serviceSelect.html(servicesHtml);

                var catId = catSelect.val();
                if (!catId) {
                    return null;
                }
                serviceSelect.children('option').each(function() {
                    if ($(this).attr('data-category') !== catId && $(this).val() !== '') {
                        $(this).remove();
                    }
                });

                if (isMobileDevice()) {
                  if (serviceSelect.children('option').length === 0) {
                    serviceSelect[0].innerHTML = '<option disabled selected>' + Translator.trace('service.not_found') + '</option>';
                  }
                } else {
                  serviceSelect.select2('destroy');
                  serviceSelect.select2({
                    allowClear: true,
                    width: 'element'
                  });
                }

                serviceSelect.prop('disabled', false);

            };
        show();
        catSelect.change(show);
    }());

    var $serviceFilterForm = $('#service-filter'),
        $serviceTable = $('#service-table'),
        processing = false;

    (function() {
        var $begin = $serviceFilterForm.find('input[name="begin"]');
        var $end = $serviceFilterForm.find('input[name="end"]');

        if (!$end.val()) {
            $end.datepicker('update', moment().toDate());
        }

        if (!$begin.val()) {
            $begin.datepicker('update', moment().subtract(7, 'days').toDate());
        }
    }());

    $serviceTable.dataTable({
        dom: "12<'row'<'col-sm-6'Bl><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
        language   : mbh.datatablesOptions.language,
        pageLength : mbh.datatablesOptions.pageLength,
          buttons       : {
            dom    : {
              container: {
                className: 'dt-buttons hidden-xs'
              }
            },
            buttons: [
              {
                extend   : 'excel',
                text     : '<i class="fa fa-table" title="Excel" data-toggle="tooltip" data-placement="bottom"></i>',
                className: 'btn btn-default btn-sm'
              }
            ]
          },
        "processing": true,
        "serverSide": true,
        "searching": false,
        "ordering": true,
        "autoWidth": false,
        "ajax": {
            "url": Routing.generate('ajax_service_list'),
            "method": 'post',
            "data": function(d) {
                d = $.extend(d, $serviceFilterForm.serializeObject());
                if (isMobileDevice()) {
                    d.service = $('#select-service').val();
                    d.category = $('#select-category').val();
                } else {
                    d.service = $('#select-service').select2('val');
                    d.category = $('#select-category').select2('val');
                }
            },
            beforeSend: function() {
                processing = true;
            }
        },
        "aoColumns": [{
                "name": "icon",
                "bSortable": false,
                "class": "td-xss text-center"
            },
            {
                "name": "number",
                "bSortable": false,
                "class": "td-xss text-center"
            },
            {
                "name": "date"
            },
            {
                "name": "service",
                "bSortable": false
            },
            {
                "name": "nights",
                "class": "td-xss text-center"
            },
            {
                "name": "persons",
                "class": "td-xss text-center"
            },
            {
                "name": "amount",
                "class": "td-xss text-center"
            },
            {
                "name": "order",
                "class": "text-right",
                "bSortable": false
            },
            {
                "name": "total",
                "class": "text-right",
                "bSortable": false
            },
            {
                "name": "payment",
                "class": "text-center",
                "bSortable": false
            },
            {
                "name": "note",
                "bSortable": false
            }
        ],
        "fnDrawCallback": function(settings) {
            processing = false;
            var $markDeleted = $serviceTable.find('.mark-deleted');
            $markDeleted.closest('tr').addClass('danger');
            var totals = settings.json.totals;
            for (var k in totals) {
                $('#service-summary-' + k).html(totals[k] + ' ');
            }
            $('.deleted-entry').closest('tr').addClass('danger');
        }
    }).fnDraw();

    var redraw = function() {
        if (!processing || 1) {
            $serviceTable.dataTable().fnDraw();
        }
    };
    $serviceFilterForm.find('input:not(.datepicker), select').on('change switchChange.bootstrapSwitch', redraw);
    $serviceFilterForm.find('input.datepicker').on('changeDate clearDate', redraw);
};

$(document).ready(function() {
    "use strict";

    docReadyServices();
});
