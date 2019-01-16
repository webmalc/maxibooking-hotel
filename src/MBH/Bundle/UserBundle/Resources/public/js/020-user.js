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
    initPaymentPage();
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
        $('#mbhuser_bundle_payer_type_country').change(function () {
            if ($('#mbhuser_bundle_payer_type_defaultCountry').val() !== this.value) {
                $('#change-country-modal').modal('show');
            }
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
        'ru_legal': ['mbhuser_bundle_payer_type_organizationName', 'mbhuser_bundle_payer_type_surname', 'mbhuser_bundle_payer_type_checkingAccount', 'mbhuser_bundle_payer_type_orgState'],
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

function initPaymentPage() {
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
        $('#card-data-modal-button').click(function () {
            $('#card-data-modal').modal('show');
        });
        handlePaymentCardForm();
        $('#payments-list-modal').on('hidden.bs.modal', function () {
            $('#payments-list-modal iFrame').prop('src', 'about:blank');
        })
    }
}

function handlePaymentCardForm() {
    var $validatedFields = $('#cardNumber, #cvc, #expiration-year, #expiration-month');
    var $submitButton = $('#subscribe-button');
    var isCardDataSaved = localStorage.getItem('is-card-data-saved') === 'true';

    if (!isCardDataSaved) {
        var validateField = function (field) {
            var fieldId = field.getAttribute('id');
            var $errorLabel = $('label[for="' + fieldId + '"]');

            var hasErrors = false;
            var isFieldChanged = !field.classList.contains('untouched') && !field.classList.contains('pristine');
            var value = field.value;
            if (fieldId === 'cardNumber' && (value.length !== 16 || !$.isNumeric(value))) {
                hasErrors = true;
            } else if (fieldId === 'cvc' && (value.length !== 3 || !$.isNumeric(value))) {
                hasErrors = true;
            } else if (fieldId === 'expiration-month' && (value === '' || ($('#expiration-year').val() === '2018' && parseInt(value, 10) < 3))) {
                hasErrors = true;
            } else if (fieldId === 'expiration-year' && value === '') {
                hasErrors = true;
            }

            if (hasErrors && (isFieldChanged || $validatedFields.not(field).filter('.invalid').length === 0)) {
                $errorLabel.show();
            } else {
                $errorLabel.hide();
            }

            if (hasErrors) {
                field.classList.remove('valid');
                field.classList.add('invalid');
            } else {
                field.classList.add('valid');
                field.classList.remove('invalid');
            }

            $validatedFields.filter('.invalid').length === 0 ? $submitButton.removeAttr('disabled') : $submitButton.attr('disabled', true);
        };

        $validatedFields.each(function (fieldIndex, field) {
            validateField(field);
            $(field).focusout(function () {
                if (!field.classList.contains('pristine')) {
                    field.classList.remove('untouched');
                }
                validateField(field);
            }).on('keyup change', (function () {
                field.classList.remove('pristine');
                validateField(field);
                if (field.id === 'expiration-year') {
                    validateField(document.getElementById('expiration-month'));
                }
            }));
        });

        $submitButton.click(function () {
            $('#payment-card-modal-body').html(mbh.loader.html);
            setTimeout(function () {
                $('#payment-card-modal-body').find('.alert.alert-warning').html('Delighted to see your interest. No payment required during Free Trial');
                localStorage.setItem('is-card-data-saved', true);
            }, 1000);
        });
    } else {
        $('#payment-card-modal-body').html(mbh.loader.html);
        $('#payment-card-modal-body').find('.alert.alert-warning').html('Delighted to see your interest. No payment required during Free Trial');
    }
}

function WaitingSpinner() {
    this._$list = $('#waiting-spinner-payments-list-modal');
    this._$details = $('#waiting-spinner-payment-details-modal');
}

WaitingSpinner.prototype._create = function($container) {
    mbh.loader.acceptTo($container);
};

WaitingSpinner.prototype._clear = function($container) {
    $container.html('');
};

WaitingSpinner.prototype.createForList = function() {
    this._create(this._$list);
};

WaitingSpinner.prototype.clearList = function() {
    this._clear(this._$list);
};

WaitingSpinner.prototype.createForDetails = function() {
    this._create(this._$details);
};

WaitingSpinner.prototype.clearDetails = function() {
    this._clear(this._$details);
};

var waitingSpinner = new WaitingSpinner();

function hangOnPayButtonHandler() {
    var $paymentListModal = $('.show-payments-list');
    $paymentListModal.click(function () {
        var $modalBody = $('#payments-list-modal-body');

        waitingSpinner.createForList();
        $('#payments-list-modal').modal('show');
        $modalBody.hide();
        var orderId = this.getAttribute('data-order-id');

        $.get(Routing.generate('order_payment_systems', {orderId: orderId}), function (response) {
            handleBillButton();
            waitingSpinner.clearList();
            var order = response['order'];
            var orderInfo = Translator.trans('view.pay_order_modal.payment_sum', {'price': order.price + ' ' + order.currency, 'orderNumber': order.id});
            $('#payment-order-block').html(orderInfo);

            var $paymentTypesSelect = $('#payment-types-select');
            $paymentTypesSelect.find('option').remove();
            response['paymentTypes'].forEach(function (paymentType) {
                if (paymentType.id !== 'rbk') {
                    var newState = new Option(paymentType.name, paymentType.id);
                    $paymentTypesSelect.append(newState);
                    if (paymentType.id === 'bill') {
                        $('#bill-content').val(paymentType.html);
                    }
                }
            });

            var $selectPaymentSystemButton = $('#select-payment-system-button'),
                $paymentDetailsModal = $('#payment-details-modal');
            $selectPaymentSystemButton.unbind('click').click(function () {
                waitingSpinner.createForDetails();
                var url = Routing.generate('payment_system_details', {paymentSystemName: $paymentTypesSelect.val(), orderId: order.id});
                $paymentDetailsModal.find('iFrame').prop('src', url).attr('onload', 'waitingSpinner.clearDetails()');
                $paymentDetailsModal.find('h4.modal-title').html(orderInfo);
                $paymentDetailsModal.modal('show');
            });

            $paymentTypesSelect.trigger('change');

            var $billButton = $('#download-bill-button');
            $paymentTypesSelect.change(function () {
                $paymentDetailsModal.modal('hide');
                $paymentDetailsModal.find('iFrame').prop('src', 'about:blank');
                if ($paymentTypesSelect.val() === 'bill') {
                    $selectPaymentSystemButton.hide();
                    $billButton.show();
                } else {
                    $selectPaymentSystemButton.show();
                    $billButton.hide();
                }
            });

            $modalBody.show();
        })

    });
}

function handleBillButton() {
    var billButton = document.getElementById('download-bill-button');
    if (billButton) {
        $(billButton).hide();
        billButton.onclick = function () {
            var billContent = document.getElementById('bill-content').value;
            var billWindow = window.open('about:blank');
            setTimeout(function () {
                billWindow.document.body.innerHTML += billContent;
            }, 100);
        };
    }
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
    var setPrice = function (priceString, isCorrect) {
        isCorrect = isCorrect === undefined ? true : isCorrect;
        $('#mbhuser_bundle_client_tariff_type_price').val(priceString);
        var $errorBlock = $('#tariff-error-block');
        isCorrect ? $errorBlock.hide() : $errorBlock.show();
    };
    var setNewTariffPrice = function () {
        var quantity = document.getElementById('mbhuser_bundle_client_tariff_type_rooms').value;
        var period = document.getElementById('mbhuser_bundle_client_tariff_type_period').value;
        if (quantity && period) {
            var url = mbh['billing_host'] + document.documentElement.lang
                + '/services/calc/?quantity=' + quantity
                + '&country=' + mbh['client_country']
                + '&period=' + period;
            $.ajax({
                url: url,
                headers: {
                    Authorization: "Token " + mbh['front_token']
                },
                success: function (response) {
                    if (response.status === true) {
                        setPrice(response.price + ' ' + response['price_currency']);
                    } else {
                        setPrice('', false)
                    }
                },
                error: function () {
                    setPrice('', false)
                }
            });
        } else {
            setPrice('');
        }
    };

    var $changeTariffShowModalButton = $('#change-tariff-modal-show');
    if ($changeTariffShowModalButton.length === 1) {
        var $changeTariffFormWrapper = $('#change-tariff-form-wrapper');

        $changeTariffShowModalButton.click(function () {
            $('#change-tariff-modal').modal('show');
            $changeTariffFormWrapper.html(mbh.loader.html);
            $.get(Routing.generate("update_tariff_modal")).done(function (modalBody) {
                $changeTariffFormWrapper.html(modalBody);
                var $roomsInput = $('#mbhuser_bundle_client_tariff_type_rooms');
                var $priceInput = $('#mbhuser_bundle_client_tariff_type_price');
                var $periodSelect = $('#mbhuser_bundle_client_tariff_type_period');
                $periodSelect.select2();
                $periodSelect.on("select2:select", function(e) {
                    setNewTariffPrice($priceInput);
                });
                $roomsInput.on('keyup', function (e) {
                    setNewTariffPrice($priceInput);
                })
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