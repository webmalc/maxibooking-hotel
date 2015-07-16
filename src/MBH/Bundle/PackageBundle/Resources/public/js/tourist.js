/*global window, $ */
$(document).ready(function () {
    'use strict';

    //roomType rooms datatables
    $('#tourist-table').each(function() {
        $(this).dataTable({
            "processing": true,
            "serverSide": true,
            "ordering": false,
            "ajax": Routing.generate('tourist_json')
        });
    });

    $('#mbh_bundle_packagebundle_touristtype_birthday, #mbh_bundle_packagebundle_package_guest_type_birthday, .guestBirthday, #mbh_tourist_extended_documentRelation_issued').datepicker({
        language: "ru",
        autoclose: true,
        startView: 2
    });


    //payer select2
    $('#mbh_bundle_packagebundle_package_guest_type_tourist, .findGuest').on("select2-selecting", function(e) {
        var userInfo = (e.object.text).split(' '),
            lastName = userInfo[0],
            firstName = userInfo[1],
            patronymic = null,
            birthday = null
        ;

        if (userInfo.length == 4) {
            patronymic = userInfo[2];
            birthday = userInfo[3].replace(/[^0-9\.]/ig, '');
        }

        if (userInfo.length == 3) {
            if (userInfo[2].match(/[\(|\)]+/ig) != null && userInfo[2].match(/[\(|\)]+/ig).length) {
                birthday = userInfo[2].replace(/[^0-9\.]/ig, '');
            } else {
                patronymic = userInfo[2];
            }
        }

        $('#mbh_bundle_packagebundle_package_guest_type_lastName, .guestLastName').val(lastName);
        $('#mbh_bundle_packagebundle_package_guest_type_firstName, .guestFirstName').val(firstName);
        $('#mbh_bundle_packagebundle_package_guest_type_patronymic, .guestPatronymic').val(patronymic);
        $('#mbh_bundle_packagebundle_package_guest_type_birthday, .guestBirthday').val(birthday);
    });


    var details = {};
    var array_values = function (input) {
        var tmp_arr = new Array(), cnt = 0;
        for (var key in input ){
            tmp_arr[cnt] = input[key];
            cnt++;
        }

        return tmp_arr;
    }
    $('#organization_organization').select2({
        minimumInputLength: 3,
        ajax: {
            url: Routing.generate('organization_json_list'),
            dataType: 'json',
            data: function (term) {
                return {
                    query: term // search term
                };
            },
            results: function (data) {
                details = data.details;

                var detailArray = array_values(details);
                $.each(data.list, function(k, v){
                    data.list[k].text = v.text + ' ' + '(ИНН ' + detailArray[k]['inn'] +')' + (detailArray[k]['fio'] ? ' ' + detailArray[k]['fio'] : '')
                });

                console.log(data.list)

                return { results: data.list };
            }
        },
        initSelection: function(element, callback) {
            var id = $(element).val();
            if (id !== "") {
                $.ajax(Routing.generate('organization_json_list') + '/' + id, {
                    dataType: "json"
                }).done(function(data) { callback(data); });
            }
        },
        dropdownCssClass: "bigdrop"
    }).on('change', function(){
        var value = $(this).val();
        var detail = details[value];
        $.each(detail, function(key, value){
            $('#organization_' + key).val(value);
        })
        $('#organization_city').select2("val", detail.city)
    });

    $('#mbh_document_relation_authorityOrgan').select2({
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
            results: function (data) {
                var results = [];
                $.each(data, function(k, v) {
                    results.push({id: k, text: v});
                });

                console.log(results);
                return { results: results };
            }
        },
        initSelection: function(element, callback) {
            var id = $(element).val();
            if (id !== "") {
                $.ajax(Routing.generate('ajax_authority_organ', {id: id}), {
                    dataType: "json"
                }).done(function(data) { callback(data); });
            }
        },
        dropdownCssClass: "bigdrop"
    })
});

