window.addExcelButtons = function (table,index){
    window.universalAddExcelButtons(table, $('#list-export-add-'+index));
};

var docReadyTables = function () {
    //Select row
    $('table.table-striped').on('click', 'tbody tr', function() {
        $(this).siblings().removeClass('warning');
        $(this).toggleClass('warning');
    });

    //Dblclick href
    $('table.table-striped').on('dblclick', 'tbody tr:not(".disable-double-click")', function() {
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
        "drawCallback": function(settings, json) {
            deleteLink();
            dangerTr();
        }
    });

    var table = $('table.table-striped').not('.not-auto-datatable').dataTable();

    if (!$('table.table-striped').hasClass("without-fixed-header") && table.length) {
        /*new $.fn.dataTable.FixedHeader(table, {
         offsetTop: 50
         });*/
    }

    if (!$('table.table-striped').hasClass("without-table-tools") && table.length) {

        var tt = new $.fn.dataTable.TableTools(table, {
            "sSwfPath": "/bundles/mbhbase/js/vendor/datatables/swf/copy_csv_xls.swf",
            "aButtons": [
                {
                    "sExtends": "copy",
                    "sButtonText": '<i class="fa fa-files-o"></i> Скопировать'
                },
                {
                    "sExtends": "csv",
                    "sButtonText": '<i class="fa fa-file-text-o"></i> CSV'
                },
                {
                    "sExtends": "xls",
                    "sButtonText": '<i class="fa fa-table"></i> Excel'
                }
            ]
        });

        $('#list-export').append($(tt.fnContainer()));
        $('#list-export').find('a').addClass('navbar-btn');
    }
}

/**
 * @author Aleksandr Arofkin <sashaaro@gmail.com>
 * @param $table
 * @param $container
 */
window.universalAddExcelButtons = function ($table, $container) {
    var tt = new $.fn.dataTable.TableTools($table, {
        "sSwfPath": "/bundles/mbhbase/js/vendor/datatables/swf/copy_csv_xls.swf",
        "aButtons": [
            {
                "sExtends": "copy",
                "sButtonText": '<i class="fa fa-files-o"></i> Скопировать'
            },
            {
                "sExtends": "csv",
                "sButtonText": '<i class="fa fa-file-text-o"></i> CSV'
            },
            {
                "sExtends": "xls",
                "sButtonText": '<i class="fa fa-table"></i> Excel'
            }
        ]
    });

    $container.append($(tt.fnContainer()));
    $container.find('a').addClass('navbar-btn');
}

/*global window */
$(document).ready(function () {
    'use strict';
    docReadyTables();
});