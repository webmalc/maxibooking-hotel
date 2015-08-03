/*global window, $, services, document, select2 */
$(document).ready(function () {
    "use strict";

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
            dateInput = $('#mbh_bundle_packagebundle_package_service_type_begin'),
            dateDiv = dateInput.closest('div.form-group'),
            dateDefault = dateInput.val(),
            serviceInput = $('#mbh_bundle_packagebundle_package_service_type_service'),
            serviceHelp = serviceInput.next('span.help-block'),
            amountInput = $('#mbh_bundle_packagebundle_package_service_type_amount'),
            amountHelp = amountInput.closest('div.input-group').next('span.help-block'),
            timeInput = $('#mbh_bundle_packagebundle_package_service_type_time'),
            timeDiv = timeInput.closest('div.form-group'),

            hide = function () {
                nightsDiv.hide();
                personsDiv.hide();
                dateDiv.hide();
                timeDiv.hide();
                dateInput.val(dateInput.val() || dateDefault);
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
                priceInput.show();
                //amountInput.val(services.service_amount);
                amountInput.show();
                if (info.date) {
                    dateDiv.show();
                }
                if (info.time) {
                    timeDiv.show();
                }

                var peoplesStr = (info.calcType === 'per_night' || info.calcType === 'per_stay') ? ' за 1 человека ' : ' ';

                if (info.calcType !== 'day_percent') {
                    serviceHelp.html($.number(info.price, 2) + ' рублей' + peoplesStr + info.calcTypeStr);
                } else {
                    serviceHelp.html(info.priceRaw + '% ' + info.calcTypeStr);
                }
                calc();
            },
            hideShow = function (event) {

                if (serviceInput.val() !== null) {
                    var info = services[serviceInput.val()],
                        priceNew = info.price;

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
                    show(info);
                } else {
                    hide();
                }
            }
            ;
        timeInput.timepicker({showMeridian: false});
        nightsDiv.change(calc);
        personsDiv.change(calc);
        amountInput.change(calc);
        serviceInput.change(calc);
        priceInput.change(calc);
        serviceInput.change(hideShow);
        dateInput.change(hideShow);
        hideShow();

    }());
});

/**
 * @author Aleksandr Arofikin
 */
$(document).ready(function () {
    'use strict';

    //Service selector
    (function () {
        var catSelect = $('#select-category'),
            serviceSelect = $('#select-service'),
            servicesHtml = serviceSelect.html(),
            show = function () {
                serviceSelect.prop('disabled', true);
                serviceSelect.html(servicesHtml);

                var catId = catSelect.val();
                if (!catId) {
                    return null;
                }
                serviceSelect.children('option').each(function () {
                    if ($(this).attr('data-category') !== catId && $(this).val() !== '') {
                        $(this).remove();
                    }
                })

                serviceSelect.select2('destroy');
                serviceSelect.select2({allowClear: true, width: 'element'});
                serviceSelect.prop('disabled', false);

            };
        show();
        catSelect.change(show);
    }());

    var $serviceFilterForm = $('#service-filter'),
        $serviceTable = $('#service-table'),
        processing = false;
    $serviceTable.dataTable({
        "processing": true,
        "serverSide": true,
        "searching": false,
        "ordering": true,
        "autoWidth": false,
        "ajax": {
            "url": Routing.generate('ajax_service_list'),
            "method" : 'post',
            "data": function (d) {
                d = $.extend(d, $serviceFilterForm.serializeObject());
                d.service = $('#select-service').select2('val');
                d.category = $('#select-category').select2('val');
            },
            beforeSend: function () {processing = true;}
        },
        "aoColumns": [
            {"name": "icon", "bSortable": false, "class" : "td-xss text-center"},
            {"name": "number", "bSortable": false, "class" : "td-xss text-center"},
            {"name": "date", "class" : "text-center"},
            {"name": "service", "bSortable": false},
            {"name": "nights", "class" : "td-xss text-center"},
            {"name": "persons", "class" : "td-xss text-center"},
            {"name": "amount", "class" : "td-xss text-center"},
            {"name": "order", "class" : "text-right", "bSortable": false},
            {"name": "total"},
            {"name": "payment", "class" : "text-center", "bSortable": false},
            {"name": "note", "bSortable": false}
        ],
        "fnDrawCallback" : function (settings) {
            processing = false;
            var $markDeleted = $serviceTable.find('.mark-deleted');
            $markDeleted.closest('tr').addClass('danger');
            var totals = settings.json.totals;
            for (var k in totals) {
                $('#service-summary-' + k).html(totals[k] + ' ');
            }
        }
    }).fnDraw();

    $serviceFilterForm.find('input, select').on('change switchChange.bootstrapSwitch', function () {
        if (!processing || 1) {
            $serviceTable.dataTable().fnDraw();
        }
    });
})