/*global window, $, services, document, datepicker, deleteLink, Routing, mbh, Translator */
var PASSPORT_DOCUMENT_TYPE_CODE = "103008";
var LIMIT_OF_EXPORTED_TO_FMS_TOURISTS = 100;

var docReadyTourists = function () {
    'use strict';

    var $touristForm = $('#tourist-form');
    //roomType rooms datatables
    var $touristTable = $('#tourist-table');
    var $citizenshipSelect = $touristForm.find('#mbhpackage_bundle_tourist_filter_form_citizenship');
    var touristFilterFormCallback = function () {
        return $.param({'form': getTouristFilterFormData($touristForm, $citizenshipSelect)});
    };
    $touristTable.dataTable({
        dom: "12<'row'<'col-sm-6'Bl><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            getExportButtonSettings('tourist', 'csv', touristFilterFormCallback)
        ],
        "processing": true,
        "serverSide": true,
        "ordering": false,
        "ajax": {
            "method": "POST",
            "url": Routing.generate('tourist_json'),
            "data": function (requestData) {
                requestData.form = getTouristFilterFormData($touristForm, $citizenshipSelect);
                return requestData;
            }
        },
        "drawCallback": function (settings) {
            var $popover = $touristTable.find('[data-toggle="popover"]');
            $popover.popover({html: true});

            var value = $citizenshipSelect.val();
            if (value === 'native') {
                $('#tourist-table').find('.show-on-print').addClass('hide')
                //$touristTable.find('.show-on-print').addClass('hide');
                //add print column
            } else {
                $('#tourist-table').find('.show-on-print').removeClass('hide')
                //$touristTable.find('.show-on-print').removeClass('hide');
                //hide print column
            }
            deleteLink();
        },
        "columnDefs": [
            {className: "hide-on-print", "targets": [6, 11]},
            {className: "show-on-print", "targets": [3, 4, 5, 9, 10]}
        ]
    });
    $touristTable.dataTable().fnSetFilteringDelay();

    $touristForm.find('input, select').on('change', function () {
        $touristTable.dataTable().fnDraw();
    });

    $('#mbh_bundle_packagebundle_touristtype_birthday, #mbh_bundle_packagebundle_package_guest_type_birthday, .guestBirthday').datepicker({
        language: "ru",
        autoclose: true,
        startView: 2
    });

    var $guestForm = $('form[name=mbh_bundle_packagebundle_package_order_tourist_type]');
    var fillGuestForm = function (data) {
        $guestForm.find('.guestLastName').val(data.lastName);
        $guestForm.find('.guestFirstName').val(data.firstName);
        $guestForm.find('.guestPatronymic').val(data.patronymic);
        $guestForm.find('.guestBirthday').val(data.birthday);
        $guestForm.find('.guestPhone').val(data.phone);
        $guestForm.find('.guestEmail').val(data.email);
        $guestForm.find('select.guestCommunicationLanguage').select2('val', [data.communicationLanguage]);
    };
    var $guestSelect = $guestForm.find('.findGuest');
    $guestSelect.change(function () {
        if (!$(this).val()) {
            return null;
        }
        $.getJSON(Routing.generate('json_tourist', {'id': $(this).val()}), fillGuestForm);
    });

    $guestSelect.mbhGuestSelectPlugin();

    var $guestOverflowAlert = $('#guest-overflow-alert');
    if ($guestOverflowAlert.length === 1) {
        $guestOverflowAlert.children('.btn').on('click', function () {
            $guestForm.removeClass('hide');
            $(this).addClass('hide');
            $guestSelect.mbhGuestSelectPlugin();
        });
    }

    var details = {};
    //payer select2
    (function () {
        var $organization = $('#organization_organization');
        if ($organization.length !== 1) {
            return;
        }

        var details;
        $organization.mbhOrganizationSelectPlugin();
        select2Text($organization).select2({
            minimumInputLength: 3,
            ajax: {
                url: Routing.generate('organization_json_list'),
                dataType: 'json',
                data: function (params) {
                    return {
                        query: params.term // search term
                    };
                },
                processResults: function (data) {
                    details = data.details;
                    $.each(data.list, function (k, v) {
                        var d = details[v.id];
                        data.list[k].text = v.text + ' ' + '(' + Translator.trans('tourist.inn') + ' ' + d['inn'] + ')' + (d['fio'] ? ' ' + d['fio'] : '')
                    });

                    return {results: data.list};
                }
            },
            dropdownCssClass: "bigdrop"
        });

        $('#organization_organization').on('change', function () {
            var value = $(this).val();
            var detail = details[value];
            $.each(detail, function (key, value) {
                $('#organization_' + key).val(value);
            });
            $('#organization_city').select2("val", [detail.city])
            $('#organization_city').append('<option value="' + detail.city + '">' + detail.city_name + '</option>').val(detail.city).trigger('change');
        });
    }());

    $('#mbh_address_object_decomposed_structure').TouchSpin({
        min: 1,
        max: 999,
        step: 1,
        stepinterval: 1
    });

    new RangeInputs($('#form_visa_issued'), $('#form_visa_expiry'));
    new RangeInputs($('#form_visa_arrivalTime'), $('#form_visa_departureTime'));
    handleAuthOrganFieldVisibility();
    hangOnExportToKonturButtonClick();
};

/*global document, window, Routing, $ */
$(document).ready(function () {
    'use strict';
    docReadyTourists();
    hangOnExportToKonturButtonClick();
});

function handleAuthOrganFieldVisibility() {
    var $documentRelationField = $('#mbh_document_relation_type');
    if ($documentRelationField.length > 0) {
        switchAuthOrganFieldsVisibility();
        $documentRelationField.change(function () {
            switchAuthOrganFieldsVisibility();
        });
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

function getTouristFilterFormData($touristForm, $citizenshipSelect) {
    return {
        begin: $touristForm.find('#mbhpackage_bundle_tourist_filter_form_begin').val(),
        end: $touristForm.find('#mbhpackage_bundle_tourist_filter_form_end').val(),
        citizenship: $citizenshipSelect.val(),
        _token: $touristForm.find('#mbhpackage_bundle_tourist_filter_form__token').val(),
        search: $('#tourist-table_filter').find('input[type="search"]').val()
    }
}

function hangOnExportToKonturButtonClick() {
    var $touristForm = $('#tourist-form');
    var $citizenshipSelect = $('#mbhpackage_bundle_tourist_filter_form_citizenship');

    $('.fms-export-button:not(.disabled)').click(function () {
        var exportUrl = Routing.generate('export_to_fms_system', {'system' : this.getAttribute('data-system')});
        var numberOfExportedTourists = $('#tourist-table').DataTable().page.info().recordsTotal;
        if (numberOfExportedTourists > LIMIT_OF_EXPORTED_TO_FMS_TOURISTS) {
            $('#fms-export-confirmation').modal('show');
        } else {
            window.open(exportUrl + '?' + $.param({'form': getTouristFilterFormData($touristForm, $citizenshipSelect)}));
        }
    });
}