/*global $, window, document, $,  deleteLink, dangerTr, mbh */

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
        "searchDelay": 1200,
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

    $('table.table-striped').not('.not-auto-datatable').dataTable(mbh.datatablesOptions);

    /*
     *  https://www.datatables.net/plug-ins/sorting/date-euro
     *  https://github.com/DataTables/Plugins/blob/master/sorting/date-euro.js
     */
    jQuery.extend( jQuery.fn.dataTableExt.oSort, {
        "date-euro-pre": function ( a ) {
            var x;

            if ( $.trim(a) !== '' ) {
                var frDatea = $.trim(a).split(' ');
                var frTimea = frDatea[1].split(':');
                var frDatea2 = frDatea[0].split('.');
                x = (frDatea2[2] + frDatea2[1] + frDatea2[0] + frTimea[0] + frTimea[1] + frTimea[2]) * 1;
            }
            else {
                x = Infinity;
            }

            return x;
        },
        "date-euro-asc": function ( a, b ) {
            return a - b;
        },
        "date-euro-desc": function ( a, b ) {
            return b - a;
        }
    } );
    $('.entity-log-table').dataTable({
        columnDefs: [
            { type: 'date-euro', targets: 0 }
        ],
        order: [[ 1, "desc" ]]
    });
};

$(document).ready(function () {
    'use strict';
    docReadyTables();
});