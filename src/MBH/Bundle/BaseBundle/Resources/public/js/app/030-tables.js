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

function setScrollable() {
    var $verticalScrollable = $('.vertical-scrollable');
    var $lineAfterLastScrollableLine = $verticalScrollable.first().parent().children().eq($verticalScrollable.length);
    $lineAfterLastScrollableLine.children().each(function (index, elem) {
        var $element = $(elem);
        $element.css('min-width', $element.css('width'));
        $element.css('max-width', $element.css('width'));
        $element.css('width', $element.css('width'));
    });

    var $table = $lineAfterLastScrollableLine.parent().parent();
    var scrollableLinesHeight = 0;
    var vScrollableTable = getScrollableTableTemplate($table);
    var tbodyElement = document.createElement('tbody');
    vScrollableTable.appendChild(tbodyElement);
    $verticalScrollable.each(function (index, trElement) {
        scrollableLinesHeight += parseInt(getComputedStyle(trElement).height, 10);
        $(trElement).children().each(function (index, tdElement) {
            var $tdElement = $(tdElement);
            $tdElement.css('min-width', $tdElement.css('width'));
            $tdElement.css('max-width', $tdElement.css('width'));
            if ($tdElement.css('background-color') === "rgba(0, 0, 0, 0)") {
                $tdElement.css('background-color', 'white');
            }
        });
    });
    $verticalScrollable.each(function (index, trElement) {
        tbodyElement.appendChild(trElement);
    });
    $table.css('margin-top', scrollableLinesHeight);

    var dailyReport = document.getElementById('daily-report');
    dailyReport.appendChild(vScrollableTable);

    var $horizontalScrollable = $('.horizontal-scrollable');
    var hScrollableTable = getScrollableTableTemplate($table);
    hScrollableTable.style.top = scrollableLinesHeight + 'px';
    hScrollableTable.style.minWidth = $horizontalScrollable.first().css('min-width');
    hScrollableTable.style.maxWidth = $horizontalScrollable.first().css('max-width');
    var hScrollableTableBody = document.createElement('tbody');
    hScrollableTable.appendChild(hScrollableTableBody);
    $horizontalScrollable.parent().each(function (index, trElement) {
        if (!trElement.classList.contains('vertical-scrollable')) {
            var hScrollableTableLine = document.createElement('tr');
            $(trElement).find('.horizontal-scrollable').each(function (index, tdElem) {
                var clonedTdElement = tdElem.cloneNode(true);
                clonedTdElement.style.backgroundColor = 'white';
                hScrollableTableLine.appendChild(clonedTdElement);
            });
            hScrollableTableBody.appendChild(hScrollableTableLine);
        }
    });
    dailyReport.appendChild(hScrollableTable);

    var $verticalAndHorizontalScrollable = $verticalScrollable.find('.horizontal-scrollable');
    var bothSidesScrollable = [];
    $verticalAndHorizontalScrollable.each(function (index, element) {
        var bothSideScrollable = getScrollableTableTemplate($table);
        var elementComputedStyles = getComputedStyle(element);
        bothSideScrollable.style.width = elementComputedStyles.width;
        bothSideScrollable.style.height = (parseInt(elementComputedStyles.height, 10) + 1) + 'px';
        var bothSidesScrollableBody = document.createElement('tbody');
        bothSideScrollable.appendChild(bothSidesScrollableBody);
        var bothSidesScrollableLine = document.createElement('tr');
        bothSidesScrollableBody.appendChild(bothSidesScrollableLine);
        var clonedElement = element.cloneNode(true);
        bothSidesScrollableLine.appendChild(clonedElement);
        bothSideScrollable.style.zIndex = 111;
        dailyReport.appendChild(bothSideScrollable);
        bothSidesScrollable.push(bothSideScrollable);
    });

    var $bothSidesScrollable = $(bothSidesScrollable);

    dailyReport.onscroll = function () {
        vScrollableTable.style.top = dailyReport.scrollTop + 'px';
        hScrollableTable.style.left = dailyReport.scrollLeft + 'px';
        $bothSidesScrollable.css('left', dailyReport.scrollLeft);
        $bothSidesScrollable.css('top', dailyReport.scrollTop);
    };
}

function getScrollableTableTemplate($table) {
    var templateTable = document.createElement('table');
    templateTable.style.top = 0;
    templateTable.classList = $table.get(0).classList;
    templateTable.style.position = 'absolute';

    return templateTable;
}