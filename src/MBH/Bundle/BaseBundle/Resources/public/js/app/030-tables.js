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


    $.extend($.fn.dataTable.defaults, {
        "searchDelay": 2500,
        "pageLength": 50,
        "stateSave": true,
        "language": {
            "sProcessing": Translator.trans("list.sProcessing") + "...",
            "sLengthMenu": Translator.trans('list.sLengthMenu', {"menu": "_MENU_"}),
            "sZeroRecords": Translator.trans("list.sZeroRecords"),
            "sInfo": Translator.trans("list.sInfo", {'start': '_START_', 'end': '_END_', 'total': "_TOTAL_"}),
            "sInfoEmpty": Translator.trans("list.sInfoEmpty"),
            "sInfoFiltered": "(" + Translator.trans("list.sInfoFiltered", {"max": "_MAX_"}) + ")",
            "sEmptyTable": Translator.trans("list.sEmptyTable"),
            "sInfoPostFix": "",
            "sSearch": Translator.trans("list.sSearch") + " ",
            "sUrl": "",
            "oPaginate": {
                "sFirst": Translator.trans("list.sFirst"),
                "sPrevious": Translator.trans("list.sPrevious"),
                "sNext": Translator.trans("list.sNext"),
                "sLast": Translator.trans("list.sLast")
            },
            "oAria": {
                "sSortAscending": ": " + Translator.trans("list.sSortAscending"),
                "sSortDescending": ": " + Translator.trans("list.sSortDescending")
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
    jQuery.extend(jQuery.fn.dataTableExt.oSort, {
        "date-euro-pre": function (a) {
            var x;

            if ($.trim(a) !== '') {
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
        "date-euro-asc": function (a, b) {
            return a - b;
        },
        "date-euro-desc": function (a, b) {
            return b - a;
        }
    });
    $('.entity-log-table').dataTable({
        columnDefs: [
            {type: 'date-euro', targets: 0}
        ],
        order: [[1, "desc"]]
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
    hScrollableTable.style.width = $horizontalScrollable.first().css('min-width');
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