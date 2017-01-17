///<reference path="DataManager.ts"/>
var ChessBoardManager = (function () {
    function ChessBoardManager(packagesData, leftRoomsData, noAccommodationCounts, noAccommodationIntervals) {
        this.dataManager = new DataManager(packagesData, leftRoomsData, noAccommodationCounts, noAccommodationIntervals, this);
        this.actionManager = new ActionManager(this.dataManager);
        this.updateNoAccommodationPackageCounts();
        this.templateDivideButton = ChessBoardManager.getTemplateDivideButton();
        this.templateRemoveButton = ChessBoardManager.getTemplateRemoveButton();
        this.tableStartDate = ChessBoardManager.getTableStartDate();
        this.tableEndDate = ChessBoardManager.getTableEndDate();
    }
    ChessBoardManager.deletePackageElement = function (packageId) {
        var packageElement = document.getElementById(packageId);
        if (packageElement) {
            packageElement.parentElement.removeChild(packageElement);
        }
    };
    ChessBoardManager.prototype.hangHandlers = function () {
        var wrapper = $('#calendarWrapper');
        var self = this;
        var chessBoardContentBlock = document.getElementById('accommodation-chessBoard-content');
        ChessBoardManager.setContentWidth(chessBoardContentBlock);
        this.addAccommodationElements();
        document.body.style.paddingBottom = '0px';
        $('.tile-bookable').find('.date').hover(function () {
            $(this).children('div').show();
        }, function () {
            if (!$(this).hasClass('selected-date-row')) {
                $(this).children('div').hide();
            }
        });
        $('#package-search-form').find('#s_adults').val(0);
        $('#packageModal, #package-edit-modal').on('hidden.bs.modal', function () {
            self.updateTable();
            $('#package-modal-continue-button').hide();
            $('#packageModalConfirmButton').show();
        });
        var $confirmationModal = $('#entity-delete-confirmation');
        $confirmationModal.on('hidden.bs.modal', function () {
            self.updateTable();
            $confirmationModal.find('#entity-delete-button').show();
            $confirmationModal.find('a.btn').remove();
            $('#entity-delete-button').unbind('click');
        });
        document.getElementById('packageModalConfirmButton').onclick = function () {
            var data = ActionManager.getDataFromUpdateModal();
            var packageId = data.packageId;
            var accommodationId = data.accommodationId;
            var packageData = ChessBoardManager.getPackageData($('#' + data.id));
            var isDivide = data.isDivide == 'true';
            if (isDivide) {
                self.dataManager.relocateAccommodationRequest(accommodationId, data);
                self.dataManager.updateLocalPackageData(data, isDivide);
            }
            else {
                self.dataManager.updatePackageRequest(packageId, data);
                self.dataManager.updateLocalPackageData(packageData, isDivide);
            }
            ActionManager.showLoadingIndicator();
        };
        var $reportFilter = $('#accommodation-report-filter');
        $('.daterangepicker-input').daterangepicker(mbh.datarangepicker.options).on('apply.daterangepicker', function (ev, picker) {
            mbh.datarangepicker.on($reportFilter.find('.begin-datepicker.mbh-daterangepicker'), $reportFilter.find('.end-datepicker.mbh-daterangepicker'), picker);
        });
        //Удаляем второй инпут дейтпикера
        $('.daterangepicker-input.form-control.input-sm').eq(1).remove();
        var rangePicker = $reportFilter.find('.daterangepicker-input').data('daterangepicker');
        rangePicker.setStartDate(this.tableStartDate);
        rangePicker.setEndDate(this.tableEndDate);
        $reportFilter.find('#filter-button').click(function () {
            $reportFilter.submit();
        });
        //Фиксирование верхнего и левого блоков таблицы
        chessBoardContentBlock.onscroll = function () {
            ChessBoardManager.onContentTableScroll(chessBoardContentBlock);
        };
        var templatePackageElement = ChessBoardManager.getTemplateElement();
        //Создание брони
        var dateElements = $('.date, .leftRooms');
        if (canCreatePackage) {
            dateElements.mousedown(function (event) {
                var startXPosition = event.pageX;
                var startLeftScroll = chessBoardContentBlock.scrollLeft;
                var newPackage = templatePackageElement.cloneNode(true);
                var dateJqueryObject = $(this.parentNode);
                var currentRoomDateElements = dateJqueryObject.parent().children();
                var startDateNumber = currentRoomDateElements.index(dateJqueryObject);
                var startDate = moment(self.tableStartDate).add(startDateNumber, 'day');
                newPackage = ChessBoardManager.setPackageOffset(newPackage, startDate, dateJqueryObject.parent().parent(), wrapper);
                newPackage.id = 'newPackage' + packages.length;
                newPackage.style.width = this.DATE_ELEMENT_WIDTH + 'px';
                var newPackageStartXOffset = parseInt(newPackage.style.left, 10);
                document.onmousemove = function (event) {
                    var scrollOffset = chessBoardContentBlock.scrollLeft - startLeftScroll;
                    var mouseXOffset = startXPosition - event.pageX;
                    var isLeftMouseShift = mouseXOffset > 0;
                    var packageLengthRestriction = ChessBoardManager.getPackageLengthRestriction(startDate, isLeftMouseShift, self.tableStartDate, self.tableEndDate);
                    var griddedOffset = ChessBoardManager.getGriddedOffset(mouseXOffset, scrollOffset, packageLengthRestriction);
                    var leftMouseOffset = isLeftMouseShift ? griddedOffset : 0;
                    var packageWidth = griddedOffset;
                    newPackage.style.backgroundColor = !self.isPackageLocationCorrect(newPackage) ? 'rgba(232, 34, 34, 0.6)' : 'rgba(79, 230, 106, 0.6)';
                    newPackage.style.left = newPackageStartXOffset - leftMouseOffset + 'px';
                    newPackage.style.width = packageWidth + 'px';
                };
                document.onmouseup = function () {
                    document.onmousemove = null;
                    this.onmouseup = null;
                    if ((newPackage.style.width) && self.isPackageLocationCorrect(newPackage) && newPackage.id) {
                        self.saveNewPackage(newPackage);
                    }
                    self.updateTable();
                };
                this.ondragstart = function () {
                    return false;
                };
                wrapper.append(newPackage);
            });
        }
    };
    ChessBoardManager.getTableStartDate = function () {
        return moment(document.getElementById('accommodation-report-begin').value, "DD.MM.YYYY");
    };
    ChessBoardManager.getTableEndDate = function () {
        return moment(document.getElementById('accommodation-report-end').value, "DD.MM.YYYY");
    };
    ChessBoardManager.setContentWidth = function (chessBoardContentBlock) {
        var contentWidth = parseInt($('#months-and-dates').css('width'), 10)
            + ChessBoardManager.LEFT_BAR_WIDTH + ChessBoardManager.SCROLL_BAR_WIDTH;
        if (parseInt($(chessBoardContentBlock).css('width'), 10) > contentWidth) {
            chessBoardContentBlock.style.width = contentWidth + 'px';
        }
        else {
            chessBoardContentBlock.style.width = 'auto';
        }
    };
    ChessBoardManager.prototype.saveNewPackage = function (packageElement) {
        'use strict';
        var packageData = ChessBoardManager.getPackageData($(packageElement));
        var $searchPackageForm = $('#package-search-form');
        $searchPackageForm.find('#s_roomType').val(packageData.roomType);
        $searchPackageForm.find('#s_begin').val(packageData.begin);
        $searchPackageForm.find('#s_end').val(packageData.end);
        $searchPackageForm.find('#s_range').val('0');
        var searchData = $searchPackageForm.serialize();
        this.dataManager.getPackageOptionsRequest(searchData, packageData);
    };
    ChessBoardManager.getGriddedOffset = function (mouseXOffset, scrollOffset, packageLengthRestriction) {
        'use strict';
        var griddedOffset = Math.ceil((Math.abs(mouseXOffset) + scrollOffset) / ChessBoardManager.DATE_ELEMENT_WIDTH) * ChessBoardManager.DATE_ELEMENT_WIDTH;
        griddedOffset = griddedOffset > packageLengthRestriction ? packageLengthRestriction : griddedOffset;
        return griddedOffset;
    };
    ChessBoardManager.onContentTableScroll = function (chessBoardContentBlock) {
        'use strict';
        var types = document.getElementById('roomTypeColumn');
        types.style.left = chessBoardContentBlock.scrollLeft + 'px';
        var monthsAndDates = document.getElementById('months-and-dates');
        monthsAndDates.style.top = chessBoardContentBlock.scrollTop + 'px';
        var headerTitle = document.getElementById('header-title');
        headerTitle.style.top = chessBoardContentBlock.scrollTop + 'px';
        headerTitle.style.left = chessBoardContentBlock.scrollLeft + 'px';
    };
    ChessBoardManager.getPackageLengthRestriction = function (startDate, isLeftMouseShift, tableStartDate, tableEndDate) {
        'use strict';
        if (isLeftMouseShift) {
            return startDate.diff(tableStartDate, 'days') * ChessBoardManager.DATE_ELEMENT_WIDTH;
        }
        return tableEndDate.diff(startDate, 'days') * ChessBoardManager.DATE_ELEMENT_WIDTH;
    };
    ChessBoardManager.prototype.addAccommodationElements = function () {
        var wrapper = $('#calendarWrapper');
        var templatePackageElement = ChessBoardManager.getTemplateElement();
        console.time('elements creating');
        var packages = document.createElement('div');
        // for (let i = 0; i < 100; i++) {
        //iterate packages
        var accommodationsData = this.dataManager.getAccommodations();
        for (var accommodationId in accommodationsData) {
            if (accommodationsData.hasOwnProperty(accommodationId)) {
                var accommodationData = accommodationsData[accommodationId];
                if (accommodationData.accommodation) {
                    var packageDiv = this.createPackageElementWithOffset(templatePackageElement, accommodationData, wrapper, packages.childNodes);
                    packages.appendChild(packageDiv);
                }
            }
        }
        // }
        wrapper.append(packages);
        console.timeEnd('elements creating');
        console.time('handlers');
        this.addListeners('.package');
        console.timeEnd('handlers');
    };
    ChessBoardManager.getTemplateElement = function () {
        var templateDiv = document.createElement('div');
        templateDiv.style = 'position: absolute;';
        templateDiv.style.height = ChessBoardManager.PACKAGE_ELEMENT_HEIGHT + 'px';
        templateDiv.classList.add('package');
        var buttonsDiv = document.createElement('div');
        buttonsDiv.classList.add('package-action-buttons');
        templateDiv.appendChild(buttonsDiv);
        return templateDiv;
    };
    ChessBoardManager.prototype.createPackageElement = function (packageItem, templatePackageElement, hasButtons, elements) {
        if (templatePackageElement === void 0) { templatePackageElement = null; }
        if (hasButtons === void 0) { hasButtons = true; }
        if (elements === void 0) { elements = []; }
        if (!templatePackageElement) {
            templatePackageElement = ChessBoardManager.getTemplateElement();
        }
        var packageStartDate = ChessBoardManager.getMomentDate(packageItem.begin);
        var packageEndDate = ChessBoardManager.getMomentDate(packageItem.end);
        var packageCellCount = packageEndDate.diff(packageStartDate, 'days');
        var packageWidth = packageCellCount * ChessBoardManager.DATE_ELEMENT_WIDTH;
        var packageDiv = templatePackageElement.cloneNode(true);
        packageDiv.style.width = packageWidth + 'px';
        packageDiv.id = packageItem.id;
        var description = document.createElement('div');
        var packageName = (packageItem.payer) ? packageItem.payer : packageItem.number;
        var descriptionText = packageName ? packageName.substr(0, packageCellCount * 5 - 1) : '';
        packageDiv.setAttribute('data-description', descriptionText);
        description.innerHTML = descriptionText;
        description.classList.add('package-description');
        packageDiv.appendChild(description);
        description.style.width = Math.floor(descriptionText.length * ChessBoardManager.PACKAGE_FONT_SIZE_WIDTH) + 'px';
        packageDiv.classList.add(packageItem.paidStatus);
        if (packageItem.position == 'middle' || packageItem.position == 'left') {
            packageDiv.classList.add('with-right-divider');
        }
        if (packageItem.position == 'middle' || packageItem.position == 'right') {
            packageDiv.classList.add('with-left-divider');
        }
        if (packageItem.isCheckOut) {
            packageDiv.classList.add('tile-coming-out');
        }
        else if (packageItem.isCheckIn) {
            packageDiv.classList.add('tile-coming');
        }
        if (hasButtons && !packageItem.isLocked) {
            if (packageItem.updateAccommodation
                && packageEndDate.diff(packageStartDate, 'days') > 1
                && !this.isAbroadRightTableSide(packageEndDate)) {
                $(packageDiv).find('.package-action-buttons').append(this.templateDivideButton.cloneNode(true));
            }
            if (packageItem.removePackage && (packageItem.position == 'full' || packageItem.position == 'right')) {
                $(packageDiv).find('.package-action-buttons').append(this.templateRemoveButton.cloneNode(true));
            }
        }
        if (elements.length > 0) {
            var lastElement = elements[elements.length - 1];
            if (lastElement.getAttribute('data-description') == descriptionText) {
                var lastElementIndex = lastElement.style.zIndex != ''
                    ? lastElement.style.zIndex : ChessBoardManager.ACCOMMODATION_ELEMENT_ZINDEX;
                packageDiv.style.zIndex = lastElementIndex - 1;
            }
        }
        return packageDiv;
    };
    ChessBoardManager.prototype.createPackageElementWithOffset = function (templatePackageElement, packageItem, wrapper, elements) {
        var packageDiv = this.createPackageElement(packageItem, templatePackageElement, true, elements);
        var packageStartDate = ChessBoardManager.getMomentDate(packageItem.begin);
        var packageEndDate = ChessBoardManager.getMomentDate(packageItem.end);
        var roomDatesListElement = $('#' + packageItem.accommodation);
        packageDiv = ChessBoardManager.setPackageOffset(packageDiv, packageStartDate, roomDatesListElement, wrapper);
        packageDiv = this.editAccommodationElement(packageDiv, packageStartDate, packageEndDate);
        return packageDiv;
    };
    ChessBoardManager.setPackageOffset = function (packageElement, startDate, roomLineElement, wrapper) {
        'use strict';
        var wrapperTopOffset = parseInt(wrapper.offset().top, 10);
        var roomLineTopOffset = parseInt(roomLineElement.offset().top, 10);
        packageElement.style.left = ChessBoardManager.getPackageLeftOffset(startDate) + 'px';
        packageElement.style.top = roomLineTopOffset - wrapperTopOffset + 'px';
        return packageElement;
    };
    ChessBoardManager.prototype.editAccommodationElement = function (element, packageStartDate, packageEndDate) {
        if (packageStartDate.isBefore(this.tableStartDate)) {
            var extraElementWidth = Math.abs(parseInt(element.style.left, 10));
            element.style.width = parseInt(element.style.width, 10) - extraElementWidth + 'px';
            element.style.left = 0;
            $(element).removeClass('with-left-divider');
        }
        if (packageEndDate.isAfter(this.tableEndDate)) {
            var differenceInDays = packageEndDate.diff(this.tableEndDate, 'days');
            element.style.width = parseInt(element.style.width, 10)
                - (differenceInDays - 1) * ChessBoardManager.DATE_ELEMENT_WIDTH
                - ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET + 'px';
        }
        var descriptionElement = element.querySelector('.package-description');
        var elementWidth = parseInt(element.style.width, 10);
        var descriptionWidth = parseInt(descriptionElement.style.width, 10);
        if (descriptionWidth > elementWidth) {
            descriptionElement.style.width = elementWidth + 'px';
        }
        if (elementWidth < ChessBoardManager.DATE_ELEMENT_WIDTH) {
            var divideButton = element.querySelector('.divide-package-button');
            if (divideButton) {
                divideButton.parentNode.removeChild(divideButton);
            }
            descriptionElement.parentNode.removeChild(descriptionElement);
        }
        return element;
    };
    ChessBoardManager.getNearestTableLineTopOffset = function (yCoordinate) {
        var topOffset = null;
        var tableLines = [].slice.call(document.getElementsByClassName('roomDates'));
        tableLines.some(function (line) {
            var lineTopOffset = line.getBoundingClientRect().top;
            if (yCoordinate >= lineTopOffset && yCoordinate <= (lineTopOffset + ChessBoardManager.DATE_ELEMENT_HEIGHT)) {
                topOffset = lineTopOffset;
                return true;
            }
            else {
                return false;
            }
        });
        if (!topOffset) {
            topOffset = tableLines[1].getBoundingClientRect().top;
        }
        return topOffset;
    };
    ChessBoardManager.getPackageLeftOffset = function (startDate) {
        var tableStartDate = this.getTableStartDate();
        var packageDateOffset = startDate.diff(tableStartDate, 'days') * ChessBoardManager.DATE_ELEMENT_WIDTH;
        return packageDateOffset + ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET;
    };
    ChessBoardManager.getMomentDate = function (dateString) {
        return moment(dateString, "DD.MM.YYYY");
    };
    ChessBoardManager.prototype.addListeners = function (identifier) {
        var jQueryObj = $(identifier);
        var self = this;
        console.time('draggable');
        this.addDraggable(jQueryObj);
        console.timeEnd('draggable');
        console.time('resizable etc');
        jQueryObj.each(function (index, element) {
            var intervalData = self.dataManager.getAccommodationIntervalById(this.id);
            var $element = $(element);
            self.addResizable($element, intervalData);
            $element.dblclick(function () {
                if (intervalData.viewPackage) {
                    self.dataManager.getPackageDataRequest(intervalData.packageId);
                }
            });
            $element.find('.remove-package-button').click(function () {
                self.actionManager.callRemoveConfirmationModal(intervalData.packageId);
            });
            $element.find('.divide-package-button').click(function () {
                if (intervalData.viewPackage) {
                    var scissorsElement = this.childNodes[0];
                    scissorsElement.onclick = function () {
                        self.updatePackagesData();
                    };
                    var accommodationElement_1 = this.parentNode.parentNode;
                    var accommodationWidth_1 = parseInt(accommodationElement_1.style.width, 10);
                    if (accommodationWidth_1 == ChessBoardManager.DATE_ELEMENT_WIDTH * 2) {
                        $('.divide-package-button').tooltip('hide');
                        self.divide(accommodationElement_1, accommodationWidth_1 / 2);
                    }
                    else {
                        var packageLeftCoordinate_1 = accommodationElement_1.getBoundingClientRect().left;
                        var line_1 = document.createElement('div');
                        line_1.style = 'position:absolute; border: 2px dashed red; height: 41px; z-index = 250;top: 0';
                        var isAccommodationAbroadTable_1 = (parseInt(getComputedStyle(accommodationElement_1).width, 10)
                            % ChessBoardManager.DATE_ELEMENT_WIDTH) != 0;
                        var defaultLeftValue_1 = isAccommodationAbroadTable_1
                            ? ChessBoardManager.DATE_ELEMENT_WIDTH + ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET
                            : ChessBoardManager.DATE_ELEMENT_WIDTH;
                        line_1.style.left = defaultLeftValue_1 + 'px';
                        accommodationElement_1.appendChild(line_1);
                        accommodationElement_1.onmousemove = function (event) {
                            var offset = event.clientX - packageLeftCoordinate_1;
                            var griddedOffset;
                            if (isAccommodationAbroadTable_1) {
                                griddedOffset = Math.floor(Math.abs(offset) / ChessBoardManager.DATE_ELEMENT_WIDTH)
                                    * ChessBoardManager.DATE_ELEMENT_WIDTH
                                    + ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET;
                            }
                            else {
                                griddedOffset = Math.floor(Math.abs(offset + ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET)
                                    / ChessBoardManager.DATE_ELEMENT_WIDTH) * ChessBoardManager.DATE_ELEMENT_WIDTH;
                            }
                            if (griddedOffset == 0) {
                                griddedOffset += defaultLeftValue_1;
                            }
                            else if (griddedOffset == accommodationWidth_1) {
                                griddedOffset -= ChessBoardManager.DATE_ELEMENT_WIDTH;
                            }
                            line_1.style.left = griddedOffset + 'px';
                            accommodationElement_1.onclick = function () {
                                accommodationElement_1.onmousemove = null;
                                accommodationElement_1.removeChild(line_1);
                                self.divide(this, griddedOffset);
                            };
                        };
                    }
                }
            });
        });
        console.timeEnd('resizable etc');
    };
    ChessBoardManager.prototype.divide = function (packageElement, firstAccommodationWidth) {
        if (packageElement.parentNode) {
            var packageWidth = parseInt(packageElement.style.width, 10);
            $(packageElement).find('.divide-package-button, .remove-package-button, .ui-resizable-e, .ui-resizable-w').remove();
            if (firstAccommodationWidth != 0 && firstAccommodationWidth != packageWidth) {
                var firstAccommodation = packageElement.cloneNode(true);
                firstAccommodation.style.width = firstAccommodationWidth + 'px';
                firstAccommodation = ChessBoardManager.setDividedElementDescription(firstAccommodation, firstAccommodationWidth);
                var secondAccommodation = packageElement.cloneNode(true);
                secondAccommodation = this.addDraggable($(secondAccommodation), false, true).get(0);
                var secondAccommodationWidth = packageWidth - firstAccommodationWidth;
                secondAccommodation = ChessBoardManager.setDividedElementDescription(secondAccommodation, secondAccommodationWidth);
                secondAccommodation.style.width = secondAccommodationWidth + 'px';
                secondAccommodation.style.left = parseInt(packageElement.style.left, 10) + firstAccommodationWidth + 'px';
                secondAccommodation.style.zIndex = parseInt(getComputedStyle(packageElement).zIndex, 10) - 1;
                secondAccommodation.classList.add('with-left-divider');
                firstAccommodation.classList.add('with-right-divider');
                packageElement.parentNode.appendChild(firstAccommodation);
                packageElement.parentNode.appendChild(secondAccommodation);
                ChessBoardManager.deletePackageElement(packageElement.id);
            }
        }
        else {
            this.updatePackagesData();
        }
    };
    ChessBoardManager.setDividedElementDescription = function (element, elementWidth) {
        var descriptionElement = element.querySelector('.package-description');
        var contentWidth = descriptionElement.innerHTML.length * 8 - 3;
        var descriptionWidth = contentWidth > elementWidth ? elementWidth : contentWidth;
        descriptionElement.style.width = descriptionWidth + 'px';
        return element;
    };
    ChessBoardManager.prototype.isDraggableRevert = function (packageElement) {
        return !(this.isPackageLocationCorrect(packageElement));
    };
    ChessBoardManager.prototype.addDraggable = function (jQueryObj, noAccommodation, isDivide) {
        if (noAccommodation === void 0) { noAccommodation = false; }
        if (isDivide === void 0) { isDivide = false; }
        var self = this;
        jQueryObj.each(function (index, element) {
            var intervalData;
            if (noAccommodation) {
                intervalData = self.dataManager.getNoAccommodationIntervalById(element.id);
            }
            else {
                intervalData = self.dataManager.getAccommodationIntervalById(element.id);
            }
            var axisValue = ChessBoardManager.getDraggableAxisValue(intervalData, isDivide);
            if (axisValue != '') {
                $(element).draggable({
                    containment: '#calendarWrapper',
                    axis: axisValue,
                    grid: [ChessBoardManager.DATE_ELEMENT_WIDTH, 1],
                    scroll: true,
                    drag: function (event, ui) {
                        if (!ChessBoardManager.isIntervalAvailable(intervalData, isDivide)) {
                            ui.position.left = ui.originalPosition.left;
                            ui.position.top = ui.originalPosition.top;
                        }
                        else {
                            ui.position.top = ChessBoardManager.getGriddedHeightValue(ui.position.top + ChessBoardManager.DATE_ELEMENT_HEIGHT / 2);
                            if (!self.isPackageLocationCorrect(this)) {
                                this.classList.add('red-package');
                            }
                            else {
                                this.classList.remove('red-package');
                            }
                        }
                    },
                    stop: function (event, ui) {
                        if (self.isDraggableRevert(this)) {
                            self.updatePackagesData();
                        }
                        else {
                            var intervalData_1 = self.dataManager.getAccommodationIntervalById(this.id);
                            if (!intervalData_1) {
                                intervalData_1 = self.dataManager.getNoAccommodationIntervalById(this.id);
                            }
                            if (ui.originalPosition.left != ui.position.left
                                || ui.originalPosition.top != ui.position.top || noAccommodation) {
                                var changedSide = axisValue == 'x, y' ? 'both' : null;
                                ActionManager.callUpdatePackageModal($(this), intervalData_1, changedSide, isDivide);
                            }
                        }
                    }
                });
            }
        });
        return jQueryObj;
    };
    ChessBoardManager.getDraggableAxisValue = function (intervalData, isDivide) {
        if (intervalData.updateAccommodation && !isDivide && intervalData.position == 'full'
            && ChessBoardManager.isAccommodationOnFullPackage(intervalData) && intervalData.updatePackage) {
            return 'x, y';
        }
        else if (intervalData.updateAccommodation
            || (intervalData.updateAccommodation == undefined) && intervalData.viewPackage) {
            return 'y';
        }
        return '';
    };
    ChessBoardManager.prototype.isPackageLocationCorrect = function (packageElement) {
        var $packageElement = $(packageElement);
        var packageOffset = $packageElement.offset();
        return (this.isOnRoomDatesLine(packageOffset) || this.isOnLeftRoomsLine(packageOffset))
            && !ChessBoardManager.isAbroadTable(packageElement, packageOffset)
            && !this.isPackageOverlapped($packageElement);
    };
    ChessBoardManager.isAccommodationOnFullPackage = function (intervalData) {
        return intervalData.position !== undefined
            && intervalData.position === 'full'
            && intervalData.packageEnd == intervalData.end
            && intervalData.packageBegin == intervalData.begin;
    };
    /**
     * Проверяет не выходит ли бронь за правую границу таблицы
     *
     * @param packageElement
     * @param packageOffset
     * @returns {boolean}
     */
    ChessBoardManager.isAbroadTable = function (packageElement, packageOffset) {
        var lastDateElementLeftOffset = parseInt($('.roomDates:eq(0)').children().children().last().offset().left, 10)
            + ChessBoardManager.DATE_ELEMENT_WIDTH;
        var packageEndLeftOffset = packageOffset.left + parseInt(packageElement.style.width, 10);
        return lastDateElementLeftOffset < packageEndLeftOffset;
    };
    /**
     * Проверяет находится ли бронь на одной из линий, указывающих размещение брони
     *
     * @param packageOffset
     * @returns {boolean}
     */
    ChessBoardManager.prototype.isOnRoomDatesLine = function (packageOffset) {
        return this.isPackageOnSpecifiedLine('roomDates', packageOffset);
    };
    ChessBoardManager.prototype.isOnLeftRoomsLine = function (packageOffset) {
        return this.isPackageOnSpecifiedLine('leftRoomsLine', packageOffset);
    };
    ChessBoardManager.prototype.isPackageOnSpecifiedLine = function (lineClass, packageOffset) {
        var specifiedLine = document.getElementsByClassName(lineClass);
        return Array.prototype.some.call(specifiedLine, function (element) {
            return ChessBoardManager.saveOffsetCompare(packageOffset.top, $(element).offset().top);
        });
    };
    /**
     * Проверяет, пересекется ли период размещения брони с другими бронями, имеющими такой же тип размещения
     *
     * @param $packageElement
     * @returns {boolean}
     */
    ChessBoardManager.prototype.isPackageOverlapped = function ($packageElement) {
        var packageData = ChessBoardManager.getPackageData($packageElement);
        var intervalsData = this.dataManager.getAccommodations();
        return Object.getOwnPropertyNames(intervalsData).some(function (intervalId) {
            var intervalData = intervalsData[intervalId];
            return !(intervalData.id === packageData.id)
                && intervalData.accommodation === packageData.accommodation
                && ChessBoardManager.getMomentDate(intervalData.begin).isBefore(moment(packageData.end, "DD.MM.YYYY"))
                && ChessBoardManager.getMomentDate(intervalData.end).isAfter(moment(packageData.begin, "DD.MM.YYYY"));
        });
    };
    ChessBoardManager.getGriddedHeightValue = function (height) {
        //1 - бордер
        return Math.floor(height / ChessBoardManager.PACKAGE_ELEMENT_HEIGHT) * ChessBoardManager.PACKAGE_ELEMENT_HEIGHT - 1;
    };
    ChessBoardManager.prototype.isAbroadLeftTableSide = function (intervalMomentBegin) {
        return intervalMomentBegin.isBefore(this.tableStartDate);
    };
    ChessBoardManager.prototype.isAbroadRightTableSide = function (intervalMomentEnd) {
        return intervalMomentEnd.isAfter(this.tableEndDate);
    };
    ChessBoardManager.prototype.getResizableHandlesValue = function (intervalData) {
        var resizableHandlesValue;
        switch (intervalData.position) {
            case 'left':
                if (this.isAbroadLeftTableSide(ChessBoardManager.getMomentDate(intervalData.begin))) {
                    resizableHandlesValue = '';
                }
                else {
                    resizableHandlesValue = 'w';
                }
                break;
            case 'right':
                if (this.isAbroadRightTableSide(ChessBoardManager.getMomentDate(intervalData.end))) {
                    resizableHandlesValue = '';
                }
                else {
                    resizableHandlesValue = 'e';
                }
                break;
            case 'middle':
                resizableHandlesValue = '';
                break;
            case 'full':
                var isAbroadLeftTableSide = ChessBoardManager.getMomentDate(intervalData.begin).isBefore(this.tableStartDate);
                var isAbroadRightTableSide = ChessBoardManager.getMomentDate(intervalData.end).isAfter(this.tableEndDate);
                //Проверяем занимает находится ли данное размещение с начала(конца) брони,
                // и имеет ли в этом случае права на изменение брони
                var canChangeLeftSide = !(intervalData.packageBegin == intervalData.begin && !intervalData.updatePackage)
                    && !isAbroadLeftTableSide;
                var canChangeRightSide = !(intervalData.packageEnd == intervalData.end && !intervalData.updatePackage)
                    && !isAbroadRightTableSide;
                if (canChangeLeftSide && canChangeRightSide) {
                    resizableHandlesValue = 'w, e';
                }
                else if (canChangeLeftSide && !canChangeRightSide) {
                    resizableHandlesValue = 'w';
                }
                else {
                    resizableHandlesValue = 'e';
                }
                break;
        }
        return resizableHandlesValue;
    };
    ChessBoardManager.prototype.addResizable = function ($element, intervalData) {
        var elementStartBackground;
        var self = this;
        var resizableHandlesValue = this.getResizableHandlesValue(intervalData);
        if (intervalData.updateAccommodation && resizableHandlesValue) {
            $element.resizable({
                aspectRatio: false,
                handles: resizableHandlesValue,
                grid: [ChessBoardManager.DATE_ELEMENT_WIDTH, 1],
                containment: '.rooms',
                start: function () {
                    if (intervalData.isLocked) {
                        ActionManager.callUnblockModal(intervalData.packageId);
                    }
                    elementStartBackground = this.style.backgroundColor;
                },
                resize: function (event, ui) {
                    if (intervalData.isLocked) {
                        ui.position.left = ui.originalPosition.left;
                        ui.size.width = ui.originalSize.width;
                    }
                    else {
                        if (self.isPackageOverlapped($(this))) {
                            this.style.backgroundColor = 'rgba(232, 34, 34, 0.6)';
                        }
                        else {
                            this.style.backgroundColor = elementStartBackground;
                        }
                    }
                },
                stop: function (event, ui) {
                    this.style.backgroundColor = elementStartBackground;
                    if (!self.isPackageLocationCorrect(this)) {
                        ui.element.css(ui.originalPosition);
                        ui.element.css(ui.originalSize);
                    }
                    else {
                        var isSizeChanged = parseInt(this.style.width, 10) != ui.originalSize.width;
                        if (isSizeChanged) {
                            var changedSide = parseInt(this.style.left, 10) == ui.originalPosition.left ? 'right' : 'left';
                            ActionManager.callUpdatePackageModal($(this), intervalData, changedSide);
                        }
                    }
                }
            });
        }
        return $element;
    };
    ChessBoardManager.getPackageData = function ($packageElement) {
        var packageOffset = $packageElement.offset();
        var roomLine = $('.roomDates, .leftRoomsLine').filter(function () {
            return ChessBoardManager.saveOffsetCompare($(this).offset().top, packageOffset.top);
        });
        var roomTypeId = roomLine.parent().get(0).id || roomLine.get(0).getAttribute('data-roomtypeid');
        var accommodationId = roomLine.children().get(0).id;
        if (accommodationId.substring(0, 16) === 'no_accommodation') {
            accommodationId = '';
        }
        var dateElements = roomLine.children().children();
        var description = $packageElement.find('.package-description').text();
        var startDateLeftOffset = packageOffset.left - ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET;
        var startDate = this.getDateStringByLeftOffset(dateElements, startDateLeftOffset);
        var endDateLeftOffset = packageOffset.left + parseInt($packageElement.get(0).style.width, 10) - this.PACKAGE_TO_MIDDAY_OFFSET;
        var endDate = this.getDateStringByLeftOffset(dateElements, endDateLeftOffset);
        return {
            id: $packageElement.get(0).id,
            accommodation: accommodationId,
            roomType: roomTypeId,
            begin: startDate,
            end: endDate,
            payer: description
        };
    };
    ChessBoardManager.saveOffsetCompare = function (firstOffset, secondOffset) {
        var firstIntOffset = parseInt(firstOffset, 10);
        var secondIntOffset = parseInt(secondOffset, 10);
        return (firstIntOffset === secondIntOffset)
            || (firstIntOffset === secondIntOffset + 1)
            || (firstIntOffset === secondIntOffset - 1);
    };
    ChessBoardManager.getDateStringByLeftOffset = function (dateElements, leftOffset) {
        var dateElement = dateElements.filter(function () {
            return ChessBoardManager.saveOffsetCompare($(this).offset().left, leftOffset);
        });
        var dateNumber = dateElements.index(dateElement);
        var momentDate = moment(document.getElementById('accommodation-report-begin').value, "DD.MM.YYYY")
            .add(dateNumber, 'day');
        return momentDate.format("DD.MM.YYYY");
    };
    ChessBoardManager.prototype.updateTable = function () {
        this.updatePackagesData();
        this.updateLeftRoomCounts();
        this.updateNoAccommodationPackageCounts();
    };
    ChessBoardManager.prototype.updateNoAccommodationPackageCounts = function () {
        var self = this;
        $('.roomTypeRooms').each(function (index, noAccommodationLine) {
            var roomTypeNoAccommodationCounts = self.dataManager.getNoAccommodationCounts()[noAccommodationLine.id];
            var noAccommodationDayElements = noAccommodationLine.children[0].children[0].children;
            for (var i = 0; i < noAccommodationDayElements.length; i++) {
                var innerText = '';
                var dayElement = noAccommodationDayElements[i].children[0];
                if (roomTypeNoAccommodationCounts[i] !== 0) {
                    innerText = roomTypeNoAccommodationCounts[i];
                    dayElement.classList.add('achtung');
                }
                else {
                    dayElement.classList.remove('achtung');
                }
                dayElement.innerHTML = innerText;
            }
        });
        this.hangPopover();
    };
    ChessBoardManager.prototype.hangPopover = function () {
        var self = this;
        var $noAccommodationElements = $('.no-accommodation-date');
        $noAccommodationElements.popover('destroy');
        var $popoverElements = $('.no-accommodation-date.achtung');
        $popoverElements.popover();
        //     $('.popover').popover('hide');
        // });
        $popoverElements.on('shown.bs.popover', function () {
            self.updatePackagesData();
            var roomTypeId = this.parentNode.parentNode.parentNode.parentNode.id;
            var currentDate = moment(this.getAttribute('data-date'), "DD.MM.YYYY");
            var templatePackageElement = ChessBoardManager.getTemplateElement();
            var packageElementsContainer = document.createElement('div');
            var packagesByCurrentDate = self.dataManager.getNoAccommodationPackagesByDate(currentDate, roomTypeId);
            var isDragged = false;
            var relocatablePackage;
            var relocatablePackageData;
            var $wrapper = $('#calendarWrapper');
            var wrapperTopOffset = parseInt($wrapper.offset().top, 10);
            var $popover = $('.popover').last();
            var $popoverContent = $popover.find('.popover-content');
            packagesByCurrentDate.forEach(function (packageData) {
                var packageElement = self.createPackageElement(packageData, templatePackageElement, false);
                packageElement.style.position = '';
                packageElement.style.display = 'inline-block';
                packageElement.style.zIndex = 150;
                var packageContainer = document.createElement('div');
                packageContainer.classList.add('popover-package-container');
                packageContainer.appendChild(self.getInfoButton(packageData.packageId));
                packageContainer.appendChild(ChessBoardManager.getEditButton(packageData.packageId));
                packageContainer.appendChild(packageElement);
                packageElementsContainer.innerHTML += packageContainer.outerHTML;
                $popoverContent.append(packageContainer);
            });
            $popoverContent.find('.package').each(function (index, element) {
                var $packageElement = $(element);
                var packageData = self.dataManager.getNoAccommodationIntervalById(element.id);
                if (ChessBoardManager.getDraggableAxisValue(packageData, false) != '') {
                    self.addDraggable($packageElement, true).draggable({
                        scroll: false,
                        snap: 'calendarRow',
                        start: function () {
                            isDragged = true;
                        }
                    }).mousedown(function (event) {
                        if (ChessBoardManager.isIntervalAvailable(packageData)) {
                            relocatablePackage = this;
                            $wrapper.append(this);
                            this.style.position = 'absolute';
                            relocatablePackageData = self.dataManager.getNoAccommodationIntervalById(this.id);
                            var intervalStartDate = ChessBoardManager.getMomentDate(relocatablePackageData.begin);
                            this.style.left = ChessBoardManager.getPackageLeftOffset(intervalStartDate) + 'px';
                            this.style.top = ChessBoardManager.getNearestTableLineTopOffset(event.pageY - document.body.scrollTop)
                                + document.body.scrollTop - wrapperTopOffset + 'px';
                            if (!self.isPackageLocationCorrect(relocatablePackage)) {
                                relocatablePackage.classList.add('red-package');
                            }
                        }
                        $popover.popover('hide');
                    });
                }
            });
            document.body.onmouseup = function () {
                document.body.onmouseup = null;
                $popoverElements.popover();
                self.hangPopover();
                if (!isDragged && relocatablePackage) {
                    if (self.isPackageLocationCorrect(relocatablePackage)) {
                        ActionManager.callUpdatePackageModal($(relocatablePackage), relocatablePackageData);
                    }
                }
                // $popoverElements.popover('hide');
            };
            //Корректируем смещение по ширине
            var currentPopover = $popover.get(0);
            var popoverOffset = currentPopover.offsetWidth - ChessBoardManager.POPOVER_MIN_WIDTH;
            if (popoverOffset !== 0) {
                currentPopover.style.left = (parseInt(currentPopover.style.left, 10) - popoverOffset / 2) + 'px';
            }
        });
    };
    ChessBoardManager.isIntervalAvailable = function (packageData, isDivide) {
        if (isDivide === void 0) { isDivide = false; }
        if (packageData.isLocked) {
            ActionManager.callUnblockModal(packageData.packageId);
            event.preventDefault();
            return false;
        }
        if (!isDivide) {
            var intervalOutOfTableSide = ChessBoardManager.getIntervalOutOfTableSide(packageData);
            if (intervalOutOfTableSide) {
                ActionManager.callIntervalBeginOutOfRangeModal(intervalOutOfTableSide);
                event.preventDefault();
                return false;
            }
            if (ChessBoardManager.getIntervalWidth(packageData) > ChessBoardManager.getTableWidth()) {
                ActionManager.callIntervalToLargeModal(packageData.packageId);
                event.preventDefault();
                return false;
            }
        }
        return true;
    };
    ChessBoardManager.getIntervalWidth = function (intervalData) {
        var packageStartDate = ChessBoardManager.getMomentDate(intervalData.begin);
        var packageEndDate = ChessBoardManager.getMomentDate(intervalData.end);
        var packageCellCount = packageEndDate.diff(packageStartDate, 'days');
        return packageCellCount * ChessBoardManager.DATE_ELEMENT_WIDTH;
    };
    ChessBoardManager.getTableWidth = function () {
        var styles = getComputedStyle(document.getElementById('accommodation-chessBoard-content'));
        return parseInt(styles.width, 10) - ChessBoardManager.LEFT_BAR_WIDTH;
    };
    ChessBoardManager.getIntervalOutOfTableSide = function (intervalData) {
        var tableBeginDate = ChessBoardManager.getTableStartDate();
        var intervalBeginDate = ChessBoardManager.getMomentDate(intervalData.begin);
        var isIntervalBeginOutOfTableBegin = intervalBeginDate.isBefore(tableBeginDate);
        var tableEndDate = ChessBoardManager.getTableEndDate();
        var intervalEndDate = ChessBoardManager.getMomentDate(intervalData.end);
        var isIntervalEndOutOfTableBegin = intervalEndDate.isAfter(tableEndDate);
        if (isIntervalBeginOutOfTableBegin && isIntervalEndOutOfTableBegin) {
            return 'both';
        }
        if (isIntervalBeginOutOfTableBegin) {
            return 'begin';
        }
        if (isIntervalEndOutOfTableBegin) {
            return 'end';
        }
    };
    ChessBoardManager.prototype.updateLeftRoomCounts = function () {
        var self = this;
        var leftRoomCounts = self.dataManager.getLeftRoomCounts();
        $('.leftRoomsLine').each(function (index, item) {
            var roomTypeId = item.getAttribute('data-roomtypeid');
            if (leftRoomCounts[roomTypeId]) {
                var dateElements = item.children[0].children;
                for (var i = 0; i < dateElements.length; i++) {
                    dateElements[i].children[0].innerHTML = leftRoomCounts[roomTypeId][i];
                }
            }
        });
    };
    ChessBoardManager.prototype.updatePackagesData = function () {
        ChessBoardManager.deleteAllPackages();
        this.addAccommodationElements();
    };
    ChessBoardManager.deleteAllPackages = function () {
        var packages = document.getElementsByClassName('package');
        while (packages[0]) {
            packages[0].parentNode.removeChild(packages[0]);
        }
    };
    ChessBoardManager.getTemplateRemoveButton = function () {
        var removeButton = document.createElement('button');
        removeButton.setAttribute('type', 'button');
        removeButton.setAttribute('title', Translator.trans('chessboard_manager.remove_button.popup'));
        removeButton.setAttribute('data-toggle', 'tooltip');
        removeButton.classList.add('remove-package-button');
        removeButton.innerHTML = '<i class="fa fa-times" aria-hidden="true"></i>';
        return removeButton;
    };
    ChessBoardManager.getTemplateDivideButton = function () {
        var divideButton = document.createElement('button');
        divideButton.setAttribute('type', 'button');
        divideButton.setAttribute('title', Translator.trans('chessboard_manager.divide_button.popup'));
        divideButton.setAttribute('data-toggle', 'tooltip');
        divideButton.classList.add('divide-package-button');
        divideButton.innerHTML = '<i class="fa fa-scissors" aria-hidden="true"></i>';
        return divideButton;
    };
    ChessBoardManager.prototype.getInfoButton = function (packageId) {
        var infoButton = document.createElement('button');
        infoButton.setAttribute('title', Translator.trans('chessboard_manager.info_button.popup.title'));
        infoButton.setAttribute('type', 'button');
        infoButton.setAttribute('data-toggle', 'tooltip');
        infoButton.setAttribute('data-placement', "right");
        infoButton.classList.add('popover-info-button');
        infoButton.innerHTML = '<i class="fa fa-info-circle" aria-hidden="true"></i>';
        var self = this;
        infoButton.onclick = function () {
            self.dataManager.getPackageDataRequest(packageId);
        };
        return infoButton;
    };
    ChessBoardManager.getEditButton = function (packageId) {
        var editButton = document.createElement('a');
        editButton.setAttribute('href', Routing.generate('package_edit', { id: packageId }));
        editButton.setAttribute('target', '_blank');
        editButton.setAttribute('title', Translator.trans('chessboard_manager.edit_button.popup.title'));
        editButton.setAttribute('data-toggle', 'tooltip');
        editButton.setAttribute('data-placement', "right");
        editButton.classList.add('popover-edit-button');
        editButton.innerHTML = '<i class="fa fa-pencil-square-o" aria-hidden="true"></i>';
        return editButton;
    };
    ChessBoardManager.DATE_ELEMENT_WIDTH = 47;
    ChessBoardManager.DATE_ELEMENT_HEIGHT = 40;
    ChessBoardManager.PACKAGE_ELEMENT_HEIGHT = 41;
    ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET = 20;
    ChessBoardManager.PACKAGE_FONT_SIZE_WIDTH = 8;
    ChessBoardManager.POPOVER_MIN_WIDTH = 250;
    ChessBoardManager.LEFT_BAR_WIDTH = 200;
    ChessBoardManager.SCROLL_BAR_WIDTH = 16;
    ChessBoardManager.ACCOMMODATION_ELEMENT_ZINDEX = 100;
    return ChessBoardManager;
}());
//# sourceMappingURL=ChessBoardManager.js.map