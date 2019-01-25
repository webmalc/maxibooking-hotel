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

function setVerticalScrollable($scrollableElements, wrapper) {
    var $table = $scrollableElements.parent().parent();
    var tableOffset = getTableOffset($table, wrapper);

    var $lineAfterLastScrollableLine = $scrollableElements.first().parent().children().eq($scrollableElements.length);
    if ($lineAfterLastScrollableLine.length === 0) {
        $lineAfterLastScrollableLine = $table.find('tbody').children().eq(0);
    }
    $lineAfterLastScrollableLine.children().each(function (index, elem) {
        var $element = $(elem);
        $element.css('min-width', $element.css('width'));
        $element.css('max-width', $element.css('width'));
        $element.css('width', $element.css('width'));
    });

    var scrollableLinesHeight = 0;
    var vScrollableTable = getScrollableTableTemplate($table, tableOffset);
    var tbodyElement = document.createElement('tbody');
    vScrollableTable.appendChild(tbodyElement);
    $scrollableElements.each(function (index, trElement) {
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

function setScrollable(reportWrapperId) {
    var $verticalScrollable = $('.vertical-scrollable');
    var wrapper = document.getElementById(reportWrapperId);
    var $horizontalScrollable = $('.horizontal-scrollable');
    var $table = $horizontalScrollable.parent().parent().parent();
    var tableOffset = getTableOffset($table, wrapper);
    var vScrollableTable = setVerticalScrollable($verticalScrollable, wrapper);
    var scrollableLinesHeight = parseInt(getComputedStyle(vScrollableTable).height, 10);

    var hScrollableTable = getScrollableTableTemplate($table, tableOffset);
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
    wrapper.appendChild(hScrollableTable);

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

    var $bothSidesScrollable = $(bothSidesScrollable);

    wrapper.onscroll = function () {
        vScrollableTable.style.top = tableOffset + wrapper.scrollTop + 'px';
        hScrollableTable.style.left = wrapper.scrollLeft + 'px';
        $bothSidesScrollable.css('left', wrapper.scrollLeft);
        $bothSidesScrollable.css('top', tableOffset + wrapper.scrollTop);
    };
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

function UnicodeToWin1251() {
    this._INDEX_BY_CODE_POINT = {
        "152": 24,
        "160": 32,
        "164": 36,
        "166": 38,
        "167": 39,
        "169": 41,
        "171": 43,
        "172": 44,
        "173": 45,
        "174": 46,
        "176": 48,
        "177": 49,
        "181": 53,
        "182": 54,
        "183": 55,
        "187": 59,
        "1025": 40,
        "1026": 0,
        "1027": 1,
        "1028": 42,
        "1029": 61,
        "1030": 50,
        "1031": 47,
        "1032": 35,
        "1033": 10,
        "1034": 12,
        "1035": 14,
        "1036": 13,
        "1038": 33,
        "1039": 15,
        "1040": 64,
        "1041": 65,
        "1042": 66,
        "1043": 67,
        "1044": 68,
        "1045": 69,
        "1046": 70,
        "1047": 71,
        "1048": 72,
        "1049": 73,
        "1050": 74,
        "1051": 75,
        "1052": 76,
        "1053": 77,
        "1054": 78,
        "1055": 79,
        "1056": 80,
        "1057": 81,
        "1058": 82,
        "1059": 83,
        "1060": 84,
        "1061": 85,
        "1062": 86,
        "1063": 87,
        "1064": 88,
        "1065": 89,
        "1066": 90,
        "1067": 91,
        "1068": 92,
        "1069": 93,
        "1070": 94,
        "1071": 95,
        "1072": 96,
        "1073": 97,
        "1074": 98,
        "1075": 99,
        "1076": 100,
        "1077": 101,
        "1078": 102,
        "1079": 103,
        "1080": 104,
        "1081": 105,
        "1082": 106,
        "1083": 107,
        "1084": 108,
        "1085": 109,
        "1086": 110,
        "1087": 111,
        "1088": 112,
        "1089": 113,
        "1090": 114,
        "1091": 115,
        "1092": 116,
        "1093": 117,
        "1094": 118,
        "1095": 119,
        "1096": 120,
        "1097": 121,
        "1098": 122,
        "1099": 123,
        "1100": 124,
        "1101": 125,
        "1102": 126,
        "1103": 127,
        "1105": 56,
        "1106": 16,
        "1107": 3,
        "1108": 58,
        "1109": 62,
        "1110": 51,
        "1111": 63,
        "1112": 60,
        "1113": 26,
        "1114": 28,
        "1115": 30,
        "1116": 29,
        "1118": 34,
        "1119": 31,
        "1168": 37,
        "1169": 52,
        "8211": 22,
        "8212": 23,
        "8216": 17,
        "8217": 18,
        "8218": 2,
        "8220": 19,
        "8221": 20,
        "8222": 4,
        "8224": 6,
        "8225": 7,
        "8226": 21,
        "8230": 5,
        "8240": 9,
        "8249": 11,
        "8250": 27,
        "8364": 8,
        "8470": 57,
        "8482": 25
    };
}

UnicodeToWin1251.prototype.change = function (str) {
    var object = {},
        hasOwnProperty = object.hasOwnProperty;
    var length = str.length;
    var index = -1;
    var codePoint;
    var pointer;
    var result = '';
    while (++index < length) {
        codePoint = str.charCodeAt(index);
        if (codePoint >= 0x00 && codePoint <= 0x7F) {
            result += '%' + (codePoint<16?'0':'') + codePoint.toString(16);
            continue;
        }
        if (hasOwnProperty.call(this._INDEX_BY_CODE_POINT, codePoint)) {
            pointer = this._INDEX_BY_CODE_POINT[codePoint];
            result += '%' + (codePoint<16?'0':'') + (pointer + 0x80).toString(16);
        } else {
            result += error(codePoint, mode);
        }
    }

    return result;
};

var unicodeToWin1251 = new UnicodeToWin1251();

jQuery.fn.tableToCSV = function (unicodeToWin) {

    unicodeToWin = typeof unicodeToWin !== 'undefined' ? unicodeToWin : false;

    var clean_text = function (text, unicodeToWin) {
        text = text.replace(/"/g, '""').replace(/(\r\n\t|\n|\r\t|\s{2})/gm,"");
        if (unicodeToWin) {
            text = unicodeToWin1251.change(text);
        }

        return '"' + text + '"';
    };

    $(this).each(function () {
        var $table = $(this);
        var caption = $table.find('caption').text();
        var title = [];
        var rows = [];

        $table.find('tr').each(function () {
            var data = [];
            $(this).find('th').each(function () {
                var text = clean_text($(this).text(), unicodeToWin);
                title.push(text);
            });
            $(this).find('td').each(function () {
                var text = clean_text($(this).text(), unicodeToWin);
                data.push(text);
            });
            data = data.join(";");
            rows.push(data);
        });
        title = title.join(";");
        rows = rows.join("\n");

        var csv = title + rows,
            uri = 'data:text/csv;charset=' + (unicodeToWin ? 'windows-1251' : 'utf-8') +',';

        uri += unicodeToWin ? csv : encodeURIComponent(csv);

        var download_link = document.createElement('a');
        download_link.href = uri;
        var ts = moment().format('DD.MM.YYYY HH:mm');
        if (caption === "") {
            download_link.download = ts + ".csv";
        } else {
            download_link.download = caption + "-" + ts + ".csv";
        }
        document.body.appendChild(download_link);
        download_link.click();
        document.body.removeChild(download_link);

    });
};

