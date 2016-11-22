/*global window, $, services, document, firstTableDate, packages, moment, Routing */

var DATE_ELEMENT_WIDTH = 47;
var PACKAGE_TO_MIDDAY_OFFSET = 20;

$(document).ready(function () {
    'use strict';
    // $.ajax({
    //     url: Routing.generate('report_accommodation_table'),
    //     data: data,
    //     success: function (data) {
    //
    //     },
    //     dataType: 'html'
    // });
    var wrapper = $('#calendarWrapper');
    var chessBoardContentBlock = document.getElementById('accommodation-chessBoard-content');
    var templatePackageElement = getTemplateElement();
    addPackages(packages);
    $('.tile-bookable').find('.date').hover(function () {
        $(this).children('div').show();
    }, function () {
        if (!$(this).hasClass('selected-date-row')) {
            $(this).children('div').hide();
        }
    });

    $('#accommodation-report-filter-begin').val($('#accommodation-report-begin').val());
    $('#accommodation-report-filter-end').val($('#accommodation-report-end').val());

    $('#packageModal').on('hidden.bs.modal', function () {
        var changedPackageId = document.getElementById('modalPackageId').value;
        deletePackageElement(changedPackageId);
        var packageData = packages.filter(function (obj) {
            return (obj.id === changedPackageId);
        })[0];
        if (packageData) {
            var packageElement = createPackageElement(templatePackageElement, packageData, wrapper, chessBoardContentBlock);
            wrapper.append(packageElement);
            addListeners($(packageElement));
        }
    });

    //Настройка кастомного класса тултипов
    $('#roomTypeColumn').find('[data-toggle="tooltip"]').tooltip({
        tooltipClass : "ui-tooltip1"
    });
    $('.accommodation-report-filter').change(function () {
        document.getElementById("accommodation-report-filter").submit();
    });
    document.getElementById('packageModalConfirmButton').onclick = function () {
        onSaveButtonClick();
    };

    //Фиксирование блока с данными о типах комнат и комнатах

    chessBoardContentBlock.onscroll = function () {
        onContentTableScroll(chessBoardContentBlock);
    };

    //Создание брони
    var dateElements = $('.date');
    dateElements.mousedown(function (event) {
        var startXPosition = event.pageX;
        var startLeftScroll = chessBoardContentBlock.scrollLeft;
        var newPackage = getTemplateElement();
        var dateJqueryObject = $(this.parentNode);
        var currentRoomDateElements = dateJqueryObject.parent().children();
        var startDateNumber = currentRoomDateElements.index(dateJqueryObject);
        var tableStartDate = moment(document.getElementById('accommodation-report-begin').value, "DD.MM.YYYY");
        var startDate = tableStartDate.add(startDateNumber, 'day');
        newPackage = setPackageOffset(newPackage, startDate, dateJqueryObject.parent().parent(), wrapper);
        newPackage.id = 'newPackage' + packages.length;
        newPackage.style.width = DATE_ELEMENT_WIDTH + 'px';
        var newPackageStartXOffset = parseInt(newPackage.style.left, 10);
        document.onmousemove = function (event) {
            var scrollOffset = chessBoardContentBlock.scrollLeft - startLeftScroll;
            var mouseXOffset = startXPosition - event.pageX;
            var griddedOffset = Math.ceil((Math.abs(mouseXOffset) + scrollOffset) / DATE_ELEMENT_WIDTH) * DATE_ELEMENT_WIDTH;
            var leftOffset = mouseXOffset > 0 ? griddedOffset : 0;
            var packageWidth = griddedOffset;
            newPackage.style.left = newPackageStartXOffset - leftOffset + 'px';
            newPackage.style.width = packageWidth + 'px';
        };
        document.onmouseup = function () {
            document.onmousemove = null;
            this.onmouseup = null;
            if (!isPackageOverlapped(newPackage) && newPackage.id) {
                callModal($(newPackage), 'Создание новой брони', 'Создать бронь');
            } else {
                wrapper.get(0).removeChild(newPackage);
            }
        };
        this.ondragstart = function () {
            return false;
        };
        wrapper.append(newPackage);
    });

});


function addPackages(packageData) {
    'use strict';
    var wrapper = $('#calendarWrapper');
    var chessBoardContentBlock = document.getElementById('accommodation-chessBoard-content');
    var templatePackageElement = getTemplateElement();

    //iterate packages
    packageData.forEach(function (item) {
        var packageDiv = createPackageElement(templatePackageElement, item, wrapper, chessBoardContentBlock);
        wrapper.append(packageDiv);
    });

    //Добавление функциональности переносов и расширений броней
    addListeners('.package');
    $('.roomDates').droppable({
        accept: '.package'
    });
}

function callModal(packageElement, title, confirmButtonText) {
    'use strict';
    title = title || 'Изменение брони';
    confirmButtonText = confirmButtonText || 'Сохранить';

    var packageData = getPackageData(packageElement);
    var roomTypeId = packageData.roomTypeId;

    var modal = $('#packageModal');
    modal.find('#modalPackageId').val(packageData.id);
    modal.find('#modalCheckinDate').val(packageData.startDate);
    modal.find('#modalCheckoutDate').val(packageData.endDate);
    modal.find('#packageModalTitle').text(title);
    modal.find('#packageModalConfirmButton').text(confirmButtonText);
    modal.find('#modalRoomTypeName option[value=' + roomTypeId + ']').prop('selected', true);
    modal.find("#modalRoomTypeName").change();
    modal.find('#modalTableLine').val(packageData.accommodationId);
    modal.modal('show');
}

function getPackageData(packageElement) {
    'use strict';
    var packageOffset = packageElement.offset();
    var roomLine = $('.roomDates').filter(function () {
        return ($(this).offset().top === packageOffset.top) || ($(this).offset().top === packageOffset.top + 1);
    });
    var roomTypeId = roomLine.parent().get(0).id;
    var accommodationId = roomLine.children().get(0).id;
    var dateElements = roomLine.children().children();

    var startDateLeftOffset = packageOffset.left - PACKAGE_TO_MIDDAY_OFFSET;
    var startDate = getDateStringByLeftOffset(dateElements, startDateLeftOffset);

    var endDateLeftOffset = packageOffset.left + parseInt(packageElement.get(0).style.width, 10) - PACKAGE_TO_MIDDAY_OFFSET;
    var endDate = getDateStringByLeftOffset(dateElements, endDateLeftOffset);
    return {
        id : packageElement.get(0).id,
        accommodationId : accommodationId,
        roomTypeId : roomTypeId,
        startDate : startDate,
        endDate : endDate
    };
}

function getDateStringByLeftOffset(dateElements, leftOffset) {
    'use strict';
    var dateElement = getDateObjectByLeftOffset(dateElements, leftOffset);
    return getDateStringByDateElement(dateElement, dateElements);
}

function getDateObjectByDateElement(dateElement, dateElements) {
    'use strict';
    var dateNumber = dateElements.index(dateElement);
    return moment(document.getElementById('accommodation-report-begin').value, "DD.MM.YYYY")
        .add(dateNumber, 'day');
}

function getDateStringByDateElement(dateElement, dateElements, dateStringFormat) {
    'use strict';
    dateStringFormat = dateStringFormat || 'YYYY-MM-DD';
    return getDateObjectByDateElement(dateElement, dateElements).format(dateStringFormat);
}

function getDateObjectByLeftOffset(dateElements, leftOffset) {
    'use strict';
    return dateElements.filter(function () {
        return $(this).offset().left === leftOffset;
    });
}

//TODO: Узнать как прервать при нахождении одного
//TODO: Переписать на основания объектов данных о бронях
function isPackageOverlapped(packageElement) {
    'use strict';
    var overlappedPackages = $('.package').filter(function () {

        var isCurrentPackage = this.id === packageElement.id;
        if (isCurrentPackage) {
            return false;
        }

        var packageLeftEdge = parseInt(packageElement.style.left, 10);
        var packageRightEdge = packageLeftEdge + parseInt(packageElement.style.width, 10);
        var packageTopOffset = parseInt(packageElement.style.top, 10);

        var iteratedPackageLeftEdge = parseInt(this.style.left, 10);
        var iteratedPackageRightEdge = iteratedPackageLeftEdge + parseInt(this.style.width, 10);
        var iteratedPackageTopOffset = parseInt(this.style.top, 10);

        //TODO: Костыль! Исправить!
        var isOnOneLine = (iteratedPackageTopOffset === packageTopOffset) || (iteratedPackageTopOffset === packageTopOffset + 1);

        if (isOnOneLine
            && iteratedPackageLeftEdge < packageRightEdge
            && iteratedPackageRightEdge > packageLeftEdge) {
            return true;
        }
        return false;
    });

    return overlappedPackages.length > 0;
}

function adjustElementOffset(packageElement) {
    'use strict';
    var packageTopOffset = $(packageElement).offset().top;
    var roomLineTopOffset;
    $('.roomDates').each(function (index, obj) {
        var currentLineTopOffset = $(obj).offset().top;
        if (packageTopOffset > (currentLineTopOffset - 10) && packageTopOffset < (currentLineTopOffset + 20)) {
            roomLineTopOffset = currentLineTopOffset;
        }
    });
    if (roomLineTopOffset) {
        return roomLineTopOffset - $('#calendarWrapper').offset().top;
    }
}

function setPackageOffset(packageElement, startDate, roomLineElement, wrapper) {
    'use strict';
    var wrapperOffset = wrapper.offset();
    var tableStartDate = moment(document.getElementById('accommodation-report-begin').value, "DD.MM.YYYY");
    var roomLineOffset = roomLineElement.offset();
    var packageDateOffset = startDate.diff(tableStartDate, 'days') * DATE_ELEMENT_WIDTH;
    packageElement.style.left = packageDateOffset + PACKAGE_TO_MIDDAY_OFFSET + 'px';
    packageElement.style.top = roomLineOffset.top - wrapperOffset.top + 'px';
    return packageElement;
}

function createPackageElement(templatePackageElement, packageItem, wrapper) {
    'use strict';
    var packageDiv = templatePackageElement.cloneNode(true);

    var packageStartDate = moment(packageItem.begin, "DD.MM.YYYY");
    var packageEndDate = moment(packageItem.end, "DD.MM.YYYY");
    var packageCellCount = packageEndDate.diff(packageStartDate, 'days');
    var packageWidth = packageCellCount * DATE_ELEMENT_WIDTH;

    var roomDatesListElement = $('#' + packageItem.tableLineId);

    packageDiv.style.width = packageWidth + 'px';
    packageDiv.id = packageItem.id;
    var description = document.createElement('div');
    description.style = 'margin: auto; height: 15px; text-align: center; padding: 0.3em 13px;font-size: 0.8em;';
    description.innerText = packageItem.payer.substr(0, packageCellCount * 5);
    packageDiv.appendChild(description);
    return setPackageOffset(packageDiv, packageStartDate, roomDatesListElement, wrapper);
}

function getTemplateElement() {
    'use strict';
    var templateDiv = document.createElement('div');
    templateDiv.style = 'z-index: 100; background-color: rgba(79, 230, 106, 0.6); position: absolute; height: 41px;';
    templateDiv.classList.add('package');
    return templateDiv;
}

function onContentTableScroll(chessBoardContentBlock) {
    'use strict';
    var types = document.getElementById('roomTypeColumn');
    types.style.left = chessBoardContentBlock.scrollLeft + 'px';

    var monthsAndDates = document.getElementById('months-and-dates');
    monthsAndDates.style.top = chessBoardContentBlock.scrollTop + 'px';

    var headerTitle = document.getElementById('header-title');
    headerTitle.style.top = chessBoardContentBlock.scrollTop + 'px';
    headerTitle.style.left = chessBoardContentBlock.scrollLeft + 'px';
}

function onSaveButtonClick() {
    'use strict';
    var modal = $('#packageModal');
    var packageId = modal.find('#modalPackageId').val();
    var endDate = moment(modal.find('#modalCheckoutDate').val()).format("DD.MM.YYYY");
    var startDate = moment(modal.find('#modalCheckinDate').val()).format("DD.MM.YYYY");
    var tableLineId = modal.find('#modalTableLine').val();
    var roomTypeId = $('#modalRoomTypeName').val();
    var packageItem;
    packages.forEach(function (item, index) {
        if (item.id === packageId) {
            packageItem = item;
            packages.splice(index, 1);
        }
    });
    var savedPackage = {
        'id': packageId,
        "begin": startDate,
        "end": endDate,
        "roomTypeId": roomTypeId,
        "tableLineId": tableLineId,
        "payer": packageItem ? packageItem.payer : 'Имя плательщика',
        "price": packageItem ? packageItem.price : 0
    };
    packages.push(savedPackage);
    modal.modal('hide');
}

function deletePackageElement(packageId) {
    'use strict';
    var packageElement = document.getElementById(packageId);
    if (packageElement) {
        packageElement.parentElement.removeChild(packageElement);
    }
}

function addListeners(identifier) {
    'use strict';
    $(identifier).draggable({
        grid: [DATE_ELEMENT_WIDTH, 41],
        revert: function (is_valid_drop) {
            if (is_valid_drop && !isPackageOverlapped(this.get(0))) {
                callModal(this);
                return false;
            } else {
                return true;
            }
        },
        scroll: true
    }).resizable({
        aspectRatio: false,
        handles: 'e, w',
        grid: [DATE_ELEMENT_WIDTH, 1],
        containment: '.rooms',
        stop: function (event, ui) {
            if (isPackageOverlapped(this)) {
                ui.element.css(ui.originalPosition);
                ui.element.css(ui.originalSize);
            } else {
                callModal($(this));
            }
        }
    }).dblclick(function () {
        callModal($(this));
    });
}