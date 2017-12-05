/*global window, $, console, document, Routing */
$(document).ready(function () {
    'use strict';

    $('.password').pwstrength({
        ui: {
            showVerdictsInsideProgressBar: true,
            verdicts: [
                Translator.trans("020_user.bad"),
                Translator.trans("020_user.normal"),
                Translator.trans("020_user.good"),
                Translator.trans("020_user.excellent"),
                Translator.trans("020_user.super")
            ]
        },
        common: {
            minChar: 1
        }
    });

    var $authorityOrgan = $('#mbh_document_relation_authorityOrgan');
    select2Text($authorityOrgan).select2({
        minimumInputLength: 3,
        placeholder: Translator.trans("020_user.make_a_choice"),
        allowClear: true,
        ajax: {
            url: Routing.generate('authority_organ_json_list'),
            dataType: 'json',
            data: function (term) {
                return {
                    query: term // search term
                };
            },
            results: function (data) {
                var results = [];
                $.each(data, function (k, v) {
                    results.push({id: k, text: v});
                });
                return {results: results};
            }
        },
        initSelection: function (element, callback) {
            var id = $(element).val();
            if (id !== "") {
                $.ajax(Routing.generate('ajax_authority_organ', {id: id}), {
                    dataType: "json"
                }).done(function (data) {
                    callback(data);
                });
            }
        },
        dropdownCssClass: "bigdrop"
    });

    handleClientServiceForm();
    handlePayerForm();
    initPaymentsDataTable();
    hangOnPayButtonHandler();
});

function handleClientServiceForm() {
    setClientServiceFormFieldsValues();
    $('#mbhuser_bundle_client_service_type_service, #mbhuser_bundle_client_service_type_quantity, #mbhuser_bundle_client_service_type_period').change(function () {
        setClientServiceFormFieldsValues();
    });
}

function handlePayerForm() {
    setPayerFormVisibility();
    $('#mbhuser_bundle_payer_type_country, #mbhuser_bundle_payer_type_payerType').change(function () {
        setPayerFormVisibility();
    });
}

function setPayerFormVisibility() {
    var selectedCountry = $('#mbhuser_bundle_payer_type_country').val();
    var $payerType = $('#mbhuser_bundle_payer_type_payerType');
    var $payerTypeFormGroup = $payerType.closest('.box');
    var $payerAddressGroup = $('#mbhuser_bundle_payer_type_address').closest('.box');
    var $identificationGroup = $('#mbhuser_bundle_payer_type_documentType').closest('.box');
    var $financialInformation = $('#mbhuser_bundle_payer_type_inn').closest('.box');

    selectedCountry ? $payerTypeFormGroup.show() : $payerTypeFormGroup.hide();
    if (selectedCountry && $payerType.val()) {
        if (selectedCountry !== 'ru') {
            $payerAddressGroup.show();
            $identificationGroup.hide();
            $financialInformation.hide();
        } else {
            $identificationGroup.show();
            $payerAddressGroup.hide();
            $financialInformation.show();
        }
    } else {
        $payerAddressGroup.hide();
        $identificationGroup.hide();
        $financialInformation.hide();
    }

    $('select.select2:not(#ordeer-paid-status)').css('width', '100%');
}

function setClientServiceFormFieldsValues() {
    var periodFieldValue = $('#mbhuser_bundle_client_service_type_period').val();
    var serviceFieldValue = $('#mbhuser_bundle_client_service_type_service').val();

    if (serviceFieldValue && periodFieldValue) {
        var serviceId = parseInt(serviceFieldValue, 10);
        var periodLength = parseInt(periodFieldValue, 10);
        var selectedService;

        services.forEach(function (service) {
            if (service.id === serviceId) {
                selectedService = service;
            }
        });

        var price = parseFloat(selectedService['price']);
        var currency = selectedService['price_currency'];

        $('#mbhuser_bundle_client_service_type_price').val(price + ' ' + currency);
        $('#mbhuser_bundle_client_service_type_units').val(selectedService['period_units']);

        var quantityString = $('#mbhuser_bundle_client_service_type_quantity').val();
        if (quantityString !== '') {
            var quantity = parseInt(quantityString, 10);
            $('#mbhuser_bundle_client_service_type_cost').val(quantity * price * periodLength + ' ' + currency);
        }
    }
}

function initPaymentsDataTable() {
    var $userPaymentForm = $('#user-payment-filter');
    var $paymentsTable = $('#payments-table');
    var $updateButton = $('#filter-button');

    if ($paymentsTable.length  === 1) {
        var getFilterData = function() {
            return {
                begin: $('#user-payment-filter-begin').val(),
                end: $('#user-payment-filter-end').val(),
                paidStatus: $('#order-paid-status').val()
            };
        };
        var drawCallback = function () {
            hangOnPayButtonHandler();
        };

        initDataTableUpdatedByCallbackWithDataFromForm($paymentsTable, $userPaymentForm, Routing.generate('payments_list_json'), $updateButton, getFilterData, drawCallback);
    }
}

function hangOnPayButtonHandler() {
    $('.show-payments-list').click(function () {
        var orderId = this.getAttribute('data-order-id');
        $.ajax({
            url: Routing.generate('order_payment_systems', {orderId: orderId}),
            success: function (paymentsListModalHtml) {
                $('#payments-list-modal-body').html(paymentsListModalHtml);
                $('#payments-list-modal').modal('show');
                hangOnOpenBillButtonClick();
            }
        });
    })
}

function hangOnOpenBillButtonClick() {
    $('#bill-button').click(function () {
        window.open('about:blank').document.body.innerText +=  $('#bill-content').val();
    });
}
    initSelect2TextForBilling('mbh_document_relation_authorityOrganId', BILLING_API_SETTINGS.fms);
    initSelect2TextForBilling('mbh_address_object_decomposed_countryTld', BILLING_API_SETTINGS.countries);
    initSelect2TextForBilling('mbh_address_object_decomposed_regionId', BILLING_API_SETTINGS.regions);
});