/*global window, $, console, document, Routing */
var PASSPORT_DOCUMENT_TYPE_CODE = "103008";

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

    handleClientServiceForm();
    handlePayerForm();
    initPaymentsDataTable();
    hangOnPayButtonHandler();
    handleAuthOrganFieldVisibility();
    handleVisibilityOfBossBaseRelatedFields();
    initTariffPage();
    $('.select2-container').css('width', '100%');
});

function handleClientServiceForm() {
    setClientServiceFormFieldsValues();
    $('#mbhuser_bundle_client_service_type_service, #mbhuser_bundle_client_service_type_quantity, #mbhuser_bundle_client_service_type_period').change(function () {
        setClientServiceFormFieldsValues();
    });
}

function handleAuthOrganFieldVisibility() {
    var $documentRelationField = $('#mbh_document_relation_type');
    if ($documentRelationField.length > 0) {
        switchAuthOrganFieldsVisibility();
        $documentRelationField.change(function () {
            switchAuthOrganFieldsVisibility();
        });
    }
}

function handlePayerForm() {
    if ($('#client-payer-type').length === 1) {
        setPayerFormVisibility();
        $('#mbhuser_bundle_payer_type_country, #mbhuser_bundle_payer_type_payerType').change(function () {
            setPayerFormVisibility();
        });
    }
}

function setPayerFormVisibility() {
    var $countryField = $('#mbhuser_bundle_payer_type_country');
    var selectedCountry = $countryField.val();
    var $countryBox = $countryField.closest('.box');

    var $payerType = $('#mbhuser_bundle_payer_type_payerType');
    var $payerTypeBox = $payerType.closest('.box');

    var firstFieldsOfGroupsByCategories = {
        'ru_legal': ['mbhuser_bundle_payer_type_organizationName', 'mbhuser_bundle_payer_type_surname', 'mbhuser_bundle_payer_type_checkingAccount'],
        'ru_natural': ['mbhuser_bundle_payer_type_documentType', 'mbhuser_bundle_payer_type_financeInn'],
        'en_legal': ['mbhuser_bundle_payer_type_organizationName', 'mbhuser_bundle_payer_type_checkingAccount'],
        'en_natural': ['mbhuser_bundle_payer_type_address']
    };

    var specificFieldsFromCommonCategories = {
        'en_legal': ['mbhuser_bundle_payer_type_swift'],
        'ru_legal': [
            'mbhuser_bundle_payer_type_form',
            'mbhuser_bundle_payer_type_inn',
            'mbhuser_bundle_payer_type_ogrn',
            'mbhuser_bundle_payer_type_kpp',
            'mbhuser_bundle_payer_type_bik',
            'mbhuser_bundle_payer_type_correspondentAccount'
        ]
    };

    $('.box').not($countryBox).hide();
    if (selectedCountry) {
        $payerTypeBox.show();
        if ($payerType.val()) {
            var categoryAbbr = (selectedCountry === 'ru' ? 'ru' : 'en') + '_' + $payerType.val();
            var firstFieldsOfShownGroups = firstFieldsOfGroupsByCategories[categoryAbbr];
            firstFieldsOfShownGroups.forEach(function (fieldId) {
                $('#' + fieldId).closest('.box').show();
            });

            for (var iteratedCategory in specificFieldsFromCommonCategories) {
                if (specificFieldsFromCommonCategories.hasOwnProperty(iteratedCategory)) {
                    specificFieldsFromCommonCategories[iteratedCategory].forEach(function (fieldId) {
                        var $iteratedFieldFormGroup = $('#' + fieldId).closest('.form-group');
                        if (categoryAbbr === iteratedCategory) {
                            $iteratedFieldFormGroup.show();
                        } else {
                            $iteratedFieldFormGroup.hide();
                        }
                    })
                }
            }
        }
    }

    $('.select2-container').css('width', '100%');
}

function handleVisibilityOfBossBaseRelatedFields() {
    var $bossBaseField = $('#mbhuser_bundle_payer_type_base');
    if ($bossBaseField.length > 0) {
        setVisibilityOfBossBaseRelatedFields();
        $bossBaseField.change(function () {
            setVisibilityOfBossBaseRelatedFields();
        });
    }
}

function setVisibilityOfBossBaseRelatedFields() {
    var $hideShowFieldsGroup = $('#mbhuser_bundle_payer_type_proxy, #mbhuser_bundle_payer_type_proxyDate').closest('.form-group');
    if ($('#mbhuser_bundle_payer_type_base').val() === 'proxy') {
        $hideShowFieldsGroup.show();
    } else {
        $hideShowFieldsGroup.hide()
    }
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

    if ($paymentsTable.length === 1) {
        var getFilterData = function () {
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
        var $modalBody = $('#payments-list-modal-body');
        $('#payments-list-modal').modal('show');

        var orderId = this.getAttribute('data-order-id');
        $modalBody.find('iFrame').prop('src', Routing.generate('order_payment_systems', {orderId: orderId}));
    })
}

function switchAuthOrganFieldsVisibility() {
    var isPassportSelected = $('#mbh_document_relation_type').val() === PASSPORT_DOCUMENT_TYPE_CODE;
    var $authorityOrganIdFormGroup = $('#mbh_document_relation_authorityOrganId').parent().parent();
    var $authorityOrganTextFormGroup = $('#mbh_document_relation_authorityOrganText').parent().parent();
    if (isPassportSelected) {
        $authorityOrganIdFormGroup.show();
        $authorityOrganIdFormGroup.find('.select2-container').css('width', '100%');
        $authorityOrganTextFormGroup.hide();
    } else {
        $authorityOrganIdFormGroup.hide();
        $authorityOrganTextFormGroup.show();
    }
}

function initTariffPage() {
    var $changeTariffShowModalButton = $('#change-tariff-modal-show');
    var $changeTariffFormWrapper = $('#change-tariff-form-wrapper');
    if ($changeTariffShowModalButton.length = 1) {
        $changeTariffShowModalButton.click(function () {
            $('#change-tariff-modal').modal('show');
            $changeTariffFormWrapper.html(mbh.loader.html);
            $.get(Routing.generate("update_tariff_modal")).done(function (modalBody) {
                $changeTariffFormWrapper.html(modalBody);
            });
        });

        $('#change-tariff-button').click(function () {
            var newTariffData = $('#change-tariff-form').serialize();
            $changeTariffFormWrapper.html(mbh.loader.html);
            $.ajax({
                url: Routing.generate("update_tariff_modal"),
                method: "POST",
                data: newTariffData,
                success: function (result) {
                    $changeTariffFormWrapper.html(result);
                },
                error: function (response) {
                    if (response.status === 302) {
                        window.location.href = Routing.generate('user_tariff');
                    } else {
                        $changeTariffFormWrapper.html(mbh.error.html);
                    }
                }
            });
        });
        $('.select2-container').css('width', '150px');
    }
}