/*global window, document, $ */
$(document).ready(function () {
    'use strict';

    (function () {

        var icon = $('#mbh_bundle_pricebundle_service_type_price').next('span.bootstrap-touchspin-postfix'),
            calcType = $('#mbh_bundle_pricebundle_service_type_calcType'),
            iconChange = function () {
                if (calcType.val() === 'day_percent') {
                    icon.html('%');
                } else {
                    icon.html('<i class="fa fa-ruble"></i>');
                }
            };

        iconChange();
        calcType.change(iconChange);
    }());

    var counter = 1;
    //roomType rooms datatables
    $('.service-tables').each(function() {

        window.addExcelButtons($(this).dataTable({
            "pageLength": 50,
            "stateSave": true,
            "language": {
                "sProcessing": "Подождите...",
                "sLengthMenu": "Показать _MENU_ записей",
                "sZeroRecords": "Записи отсутствуют.",
                "sInfo": "Записи с _START_ до _END_ из _TOTAL_ записей",
                "sInfoEmpty": "Записи с 0 до 0 из 0 записей",
                "sInfoFiltered": "(отфильтровано из _MAX_ записей)",
                "sEmptyTable": "Ничего не найдено",
                "sInfoPostFix": "",
                "sSearch": "Поиск ",
                "sUrl": "",
                "oPaginate": {
                    "sFirst": "Первая",
                    "sPrevious": "Назад",
                    "sNext": "Вперед",
                    "sLast": "Последняя"
                },
                "oAria": {
                    "sSortAscending": ": активировать для сортировки столбца по возрастанию",
                    "sSortDescending": ": активировать для сортировки столбцов по убыванию"
                }
            },
            "drawCallback": function(settings, json) {
                deleteLink();
                dangerTr();
            }
        }), counter);
        ++counter
    });
    $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
        var counter = 1;
        $('.service-tables').each(function() {
            $(this).dataTable().fnDestroy();
            window.addExcelButtons($(this).dataTable({
                "pageLength": 50,
                "stateSave": true,
                "language": {
                    "sProcessing": "Подождите...",
                    "sLengthMenu": "Показать _MENU_ записей",
                    "sZeroRecords": "Записи отсутствуют.",
                    "sInfo": "Записи с _START_ до _END_ из _TOTAL_ записей",
                    "sInfoEmpty": "Записи с 0 до 0 из 0 записей",
                    "sInfoFiltered": "(отфильтровано из _MAX_ записей)",
                    "sEmptyTable": "Ничего не найдено",
                    "sInfoPostFix": "",
                    "sSearch": "Поиск ",
                    "sUrl": "",
                    "oPaginate": {
                        "sFirst": "Первая",
                        "sPrevious": "Назад",
                        "sNext": "Вперед",
                        "sLast": "Последняя"
                    },
                    "oAria": {
                        "sSortAscending": ": активировать для сортировки столбца по возрастанию",
                        "sSortDescending": ": активировать для сортировки столбцов по убыванию"
                    }
                },
                "drawCallback": function(settings, json) {
                    deleteLink();
                    dangerTr();
                }
            }), counter);
            ++counter
        });
    });
});