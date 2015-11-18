/*global window, $, services, document, datepicker, deleteLink, Routing, mbh */

var docReadyTourists = function () {
    'use strict';

    var $touristForm = $('#tourist-form');
    //roomType rooms datatables
    var $touristTable = $('#tourist-table');
    $touristTable.dataTable({
        "processing": true,
        "serverSide": true,
        "ordering": false,
        "ajax": {
            "method": "POST",
            "url": Routing.generate('tourist_json'),
            "data": function (requestData) {
                requestData.form = {
                    begin: $touristForm.find('#form_begin').val(),
                    end: $touristForm.find('#form_end').val(),
                    citizenship: $touristForm.find('#form_citizenship').val(),
                    _token: $touristForm.find('#form__token').val()
                };
                return requestData;
            }
        },
        "drawCallback": function( settings ) {
            var $popover = $touristTable.find('[data-toggle="popover"]');
            $popover.popover({ html : true });
        }
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
        $guestForm.find('select.guestCommunicationLanguage').select2('val', data.communicationLanguage);
    }
    var $guestSelect = $guestForm.find('.findGuest');
    $guestSelect.change(function () {
        if (!$(this).val()) {
            return null;
        }
        $.getJSON(Routing.generate('json_tourist', {'id': $(this).val()}), fillGuestForm);
    });

    $guestSelect.mbhGuestSelectPlugin();

    var $guestOverflowAlert = $('#guest-overflow-alert');
    if ($guestOverflowAlert.length == 1) {
        $guestOverflowAlert.children('.btn').on('click', function () {
            $guestForm.removeClass('hide');
            $(this).addClass('hide');
            $guestSelect.mbhGuestSelectPlugin();
        });
    }

    var details = {};
    var array_values = function (input) {
        var tmp_arr = new Array(), cnt = 0;
        for (var key in input) {
            tmp_arr[cnt] = input[key];
            cnt++;
        }

        return tmp_arr;
    }

        //payer select2
    (function () {
        var org = $('#organization_organization');
        if (org.length !== 1) {
            return;
        }

        select2Text(org).select2({
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
                        data.list[k].text = v.text + ' ' + '(ИНН ' + d['inn'] + ')' + (d['fio'] ? ' ' + d['fio'] : '')
                    });

                    return {results: data.list};
                }
            },
            /*initSelection: function (element, callback) {
             var id = $(element).val();
             if (id !== "") {
             $.ajax(Routing.generate('organization_json_list') + '/' + id, {
             dataType: "json"
             }).done(function (data) {
             callback(data);
             });
             }
             },*/
            dropdownCssClass: "bigdrop"
        }).on('change', function () {
            var value = $(this).val();
            var detail = details[value];
            $.each(detail, function (key, value) {
                $('#organization_' + key).val(value);
            });
            $('#organization_city').select2("val", detail.city)
            $('#organization_city').append('<option value="' + detail.city + '">' + detail.city_name + '</option>').val(detail.city).trigger('change');
        });
    }());

    var $authorityOrganTextInput = $('#mbh_document_relation_authorityOrganText');
    var $authorityOrganCodeInput = $('#mbh_document_relation_authorityOrganCode');
    select2Text($('#mbh_document_relation_authorityOrgan')).select2({
        minimumInputLength: 3,
        placeholder: "Сделайте выбор",
        allowClear: true,
        ajax: {
            url: Routing.generate('authority_organ_json_list'),
            dataType: 'json',
            data: function (term) {
                return {
                    query: term // search term
                };
            },
            processResults: function (data, request) {
                if (data.results.length == 0) {
                    data.results.push({
                        id: request.term,
                        text: request.term
                    });
                }
                //console.log(data.results);
                return data;
            }
        },
        initSelection: function (element, callback) {
            var id = $(element).val();
            if (id.length == 24) { //mongoID
                $.ajax(Routing.generate('ajax_authority_organ', {id: id}), {
                    dataType: "json"
                }).done(function (data) {
                    callback(data);
                    $authorityOrganTextInput.val('');

                    $authorityOrganCodeInput.val(data.code);
                    $authorityOrganCodeInput.attr('disabled', true);
                });
            } else if ($authorityOrganTextInput.val()) {
                callback({
                    id: $authorityOrganTextInput.val(),
                    text: $authorityOrganTextInput.val()
                });
                $authorityOrganCodeInput.attr('disabled', false);
            } else {
                $authorityOrganCodeInput.attr('disabled', false);
            }

        },
        dropdownCssClass: "bigdrop"
    }).on('change', function (name, evt) {
        var $this = $(this);
        var value = $this.select2('val');
        if (value && value.length == 24) { //mongoID
            $authorityOrganCodeInput.attr('disabled', true);
            $.ajax(Routing.generate('ajax_authority_organ', {id: value}), {
                dataType: "json"
            }).done(function (data) {
                $authorityOrganCodeInput.val(data.code);
            });
        } else {
            $authorityOrganCodeInput.attr('disabled', false);
        }
    })

    new RangeInputs($('#form_visa_issued'), $('#form_visa_expiry'));
    new RangeInputs($('#form_visa_arrivalTime'), $('#form_visa_departureTime'));
}

/*global document, window, Routing, $ */
$(document).ready(function () {
    'use strict';

    docReadyTourists();
});

