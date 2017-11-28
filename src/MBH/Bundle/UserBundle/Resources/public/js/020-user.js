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

    initSelect2TextForBilling('mbh_document_relation_authorityOrganId', BILLING_API_SETTINGS.fms);
    initSelect2TextForBilling('mbh_address_object_decomposed_countryTld', BILLING_API_SETTINGS.countries);
    initSelect2TextForBilling('mbh_address_object_decomposed_regionId', BILLING_API_SETTINGS.regions);
});