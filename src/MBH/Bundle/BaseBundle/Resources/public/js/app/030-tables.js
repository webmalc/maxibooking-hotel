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

    //prevent relocation to another page
    setTimeout(function () {
        $('table.table-striped').find('input, button, a').on('dblclick', function (e) {
            return false;
        });
    }, 200)
};

$(document).ready(function () {
    'use strict';
    docReadyTables();
});

function setVerticalScrollable($scrollableElements, wrapper) {
    var $table = $scrollableElements.parent().parent();
    var tableOffset = getTableOffset($table, wrapper);

    setSameWidthForCellsInTheSameColumn();

    var scrollableLinesHeight = 0;
    $scrollableElements.each(function (index, trElement) {
        scrollableLinesHeight += parseInt(getComputedStyle(trElement).height, 10);
    });
    var vScrollableTable = getScrollableTableTemplate($table, tableOffset);
    var tbodyElement = document.createElement('tbody');
    vScrollableTable.appendChild(tbodyElement);
    $scrollableElements.each(function (index, trElement) {
        tbodyElement.appendChild(trElement);
    });

    $table.css('margin-top', scrollableLinesHeight);
    wrapper.appendChild(vScrollableTable);
    wrapper.onscroll = function () {
        vScrollableTable.style.top = tableOffset + wrapper.scrollTop + 'px';
    };

    return vScrollableTable;
}

function setScrollable(wrapper) {
    if (isMobileDevice()) {
        return;
    }
    var $verticalScrollable = $('.vertical-scrollable');
    var $horizontalScrollable = $('.horizontal-scrollable');
    var $table = $horizontalScrollable.parent().parent().parent();
    var tableOffset = getTableOffset($table, wrapper);

    var offsetForFirstScrollable = getOffsetForFirstVerticalScrollable($table);
    var vScrollableTable = setVerticalScrollable($verticalScrollable, wrapper);
    var scrollableLinesHeight = parseInt(getComputedStyle(vScrollableTable).height, 10);

    var $tables = $horizontalScrollable.closest('table');
    var firstTableOffset;

    $tables.each(function (tableIndex, table) {
        var hScrollableTable = getScrollableTableTemplate($table, tableOffset);
        hScrollableTable.classList.add('horizontal-scrollable-table');
        var tableTopOffset = $(table).offset().top;
        if (tableIndex === 0) {
            firstTableOffset = tableTopOffset;
            hScrollableTable.style.top = scrollableLinesHeight + offsetForFirstScrollable - 1 + 'px';
        } else {
            hScrollableTable.style.top = scrollableLinesHeight + offsetForFirstScrollable - 1 + (tableTopOffset - firstTableOffset) + 'px';
        }

        hScrollableTable.style.minWidth = $horizontalScrollable.first().css('min-width');
        hScrollableTable.style.maxWidth = $horizontalScrollable.first().css('max-width');
        var hScrollableTableBody = document.createElement('tbody');
        hScrollableTable.appendChild(hScrollableTableBody);

        $(table).find('tr').each(function (rowIndex, row) {
            if (!row.classList.contains('vertical-scrollable')) {
                var hScrollableTableLine = document.createElement('tr');
                $(row).find('.horizontal-scrollable').each(function (index, tdElem) {
                    var clonedTdElement = tdElem.cloneNode(true);
                    clonedTdElement.style.backgroundColor = 'white';
                    hScrollableTableLine.appendChild(clonedTdElement);
                });
                hScrollableTableBody.appendChild(hScrollableTableLine);
            }
        });

        wrapper.appendChild(hScrollableTable);
    });

    var $verticalAndHorizontalScrollable = $verticalScrollable.find('.horizontal-scrollable');
    var bothSidesScrollable = [];
    $verticalAndHorizontalScrollable.each(function (index, element) {
        var bothSideScrollable = getScrollableTableTemplate($table, tableOffset);
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
        wrapper.appendChild(bothSideScrollable);
        bothSidesScrollable.push(bothSideScrollable);
    });

    var $horizontalTextScrollable = $('.horizontal-text-scrollable');
    $horizontalTextScrollable.each(function (index, cell) {
        cell.style.height = getComputedStyle(cell).height;
        cell.style.position = 'relative';

        var span = document.createElement('span');
        span.style.position = 'absolute';
        span.style.paddingLeft = '10px';
        span.style.left = 0;
        span.classList.add('scrollable-text-span');
        span.style.top = 9 + 'px';
        span.innerHTML = cell.innerHTML;
        cell.innerHTML = span.outerHTML;
    });

    var $bothSidesScrollable = $(bothSidesScrollable);
    var $horizontalScrollableTables = $('.horizontal-scrollable-table');
    var $scrollableTextSpan = $('.scrollable-text-span');

    wrapper.onscroll = function () {
        vScrollableTable.style.top = tableOffset + wrapper.scrollTop + 'px';
        $horizontalScrollableTables.css('left', wrapper.scrollLeft);
        $scrollableTextSpan.css('left', wrapper.scrollLeft);
        $bothSidesScrollable.css('left', wrapper.scrollLeft);
        $bothSidesScrollable.css('top', tableOffset + wrapper.scrollTop);
    };
}

function getOffsetForFirstVerticalScrollable($table) {
    var $tableRows = $table.find('tr');
    var offsetForFirstScrollableRow = 0;
    for (var rowIndex = 0; rowIndex < $tableRows.length; rowIndex++) {
        var $currentRow = $tableRows.eq(rowIndex);
        if ($currentRow.hasClass('vertical-scrollable')) {
            break;
        }
        offsetForFirstScrollableRow += parseInt($currentRow.css('height'), 10);
    }

    return offsetForFirstScrollableRow;
}

function setSameWidthForCellsInTheSameColumn() {
    var $reportTables = $('table.mbh-report-table');
    var $firstTableScrollableRows = $reportTables.first().find('tr.vertical-scrollable');
    var numberOfFirstScrollable = $firstTableScrollableRows.first().index();
    var numberOfHorizontalScrollableLines = $firstTableScrollableRows.length;

    var widestCellWidths = {};
    $reportTables.each(function (tableIndex, table) {
        $(table).find('tr').each(function (rowIndex, row) {
            if (rowIndex >= numberOfFirstScrollable && rowIndex <= numberOfHorizontalScrollableLines) {
                if (!widestCellWidths[rowIndex]) {
                    widestCellWidths[rowIndex] = {};
                }

                $(row).children().each(function (cellIndex, cell) {
                    var cellWidth = parseInt(getComputedStyle(cell).width, 10);
                    if (!widestCellWidths[rowIndex] || !widestCellWidths[rowIndex][cellIndex]) {
                        widestCellWidths[rowIndex][cellIndex] = cellWidth;
                    } else {
                        if (widestCellWidths[rowIndex][cellIndex] > cellWidth) {
                            widestCellWidths[rowIndex][cellIndex] = cellWidth;
                        }
                    }
                });
            }
        });
    });

    $reportTables.each(function (tableIndex, table) {
        var isCellForHeaderTable = tableIndex === 0;
        $(table).find('tr').each(function (rowIndex, row) {
            var isRowAfterLastScrollable = isCellForHeaderTable && rowIndex === (numberOfHorizontalScrollableLines + 1);
            if (rowIndex >= numberOfFirstScrollable && (rowIndex <= numberOfHorizontalScrollableLines || isRowAfterLastScrollable)) {
                $(row).children().each(function (cellIndex, cell) {
                    var minWidth = isRowAfterLastScrollable
                        ? widestCellWidths[rowIndex - 1][cellIndex] + 'px'
                        : widestCellWidths[rowIndex][cellIndex] + 'px';
                    cell.style.minWidth = minWidth;
                    cell.style.maxWidth = minWidth;
                    cell.style.width = minWidth;

                    if (isCellForHeaderTable && !isRowAfterLastScrollable && getComputedStyle(cell).backgroundColor === "rgba(0, 0, 0, 0)") {
                        cell.style.backgroundColor = 'white';
                    }
                });
            }
        });
    });
}

function getTableOffset($table, wrapper) {
    var tableTopMargin = parseInt($table.css('margin-top'), 10) +
        ($table.parent().id === wrapper.id ? 0 : parseInt($table.parent().css('margin-top'), 10));
    var tableTopPadding = parseInt($table.css('padding-top'), 10) +
        ($table.parent().id === wrapper.id ? 0 : parseInt($table.parent().css('padding-top'), 10));

    return $table.offset().top - $(wrapper).offset().top - tableTopMargin - tableTopPadding;
}

function getScrollableTableTemplate($table, tableOffset) {
    var templateTable = document.createElement('table');
    templateTable.style.top = tableOffset + 'px';
    templateTable.classList = $table.get(0).classList;
    templateTable.style.position = 'absolute';

    return templateTable;
}