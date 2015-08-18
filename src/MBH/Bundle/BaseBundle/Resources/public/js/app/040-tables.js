/*global $, window, document, $,  deleteLink, dangerTr */

var docReadyTables = function () {
    'use strict';

    //Select row
    $('table.table-striped').on('click', 'tbody tr', function () {
        $(this).siblings().removeClass('warning');
        $(this).toggleClass('warning');
    });

    //Dblclick href
    $('table.table-striped').on('dblclick', 'tbody tr:not(".disable-double-click")', function () {
        var link = $(this).find('a[rel="main"]');
        if (link.length) {
            window.location.href = link.attr('href');
        }
    });

    //Datatables
    $.extend($.fn.dataTable.defaults, {
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
        "drawCallback": function () {
            deleteLink();
            dangerTr();
        }
    });

    $('table.table-striped').not('.not-auto-datatable').dataTable();
};

$(document).ready(function () {
    'use strict';
    docReadyTables();
});