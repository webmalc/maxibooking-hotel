///<reference path="DataManager.ts"/>
declare let moment;
declare let $;
declare let mbh;
declare let canCreatePackage;
declare let Translator;

class ChessBoardManager {

    private static DATE_ELEMENT_WIDTH = 47;
    private static DATE_ELEMENT_HEIGHT = 40;
    private static PACKAGE_ELEMENT_HEIGHT = 41;
    private static PACKAGE_TO_MIDDAY_OFFSET = 20;
    private static PACKAGE_FONT_SIZE_WIDTH = 8;
    private static POPOVER_MIN_WIDTH = 250;
    private static LEFT_BAR_WIDTH = 200;
    private static SCROLL_BAR_WIDTH = 16;
    private static ACCOMMODATION_ELEMENT_ZINDEX = 100;

    public dataManager;
    public actionManager;
    private templateRemoveButton;
    private templateDivideButton;
    private tableStartDate;
    private tableEndDate;

    public static deletePackageElement(packageId) {
        let packageElement = document.getElementById(packageId);
        if (packageElement) {
            packageElement.parentElement.removeChild(packageElement);
        }
    }

    constructor(packagesData, leftRoomsData, noAccommodationCounts, noAccommodationIntervals) {
        this.dataManager = new DataManager(packagesData, leftRoomsData, noAccommodationCounts, noAccommodationIntervals, this);
        this.actionManager = new ActionManager(this.dataManager);
        this.updateNoAccommodationPackageCounts();
        this.templateDivideButton = ChessBoardManager.getTemplateDivideButton();
        this.templateRemoveButton = ChessBoardManager.getTemplateRemoveButton();
        this.tableStartDate = ChessBoardManager.getTableStartDate();
        this.tableEndDate = ChessBoardManager.getTableEndDate();
    }

    public hangHandlers() {
        let wrapper = $('#calendarWrapper');
        let self = this;
        let chessBoardContentBlock = document.getElementById('accommodation-chessBoard-content');
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

        let $confirmationModal = $('#entity-delete-confirmation');
        $confirmationModal.on('hidden.bs.modal', function () {
            self.updateTable();
            $confirmationModal.find('#entity-delete-button').show();
            $confirmationModal.find('a.btn').remove();
            $('#entity-delete-button').unbind('click');
        });

        document.getElementById('packageModalConfirmButton').onclick = function () {
            let data = ActionManager.getDataFromUpdateModal();
            let packageId = data.packageId;
            let accommodationId = data.accommodationId;
            let packageData = ChessBoardManager.getPackageData($('#' + data.id));
            let isDivide = data.isDivide == 'true';
            if (isDivide) {
                self.dataManager.relocateAccommodationRequest(accommodationId, data);
                self.dataManager.updateLocalPackageData(data, isDivide);
            } else {
                self.dataManager.updatePackageRequest(packageId, data);
                self.dataManager.updateLocalPackageData(packageData, isDivide);
            }
            ActionManager.showLoadingIndicator();
        };

        let $reportFilter = $('#accommodation-report-filter');

        $('.daterangepicker-input').daterangepicker(mbh.datarangepicker.options).on('apply.daterangepicker', function (ev, picker) {
            mbh.datarangepicker.on($reportFilter.find('.begin-datepicker.mbh-daterangepicker'), $reportFilter.find('.end-datepicker.mbh-daterangepicker'), picker);
        });

        //Удаляем второй инпут дейтпикера
        $('.daterangepicker-input.form-control.input-sm').eq(1).remove();

        let rangePicker = $reportFilter.find('.daterangepicker-input').data('daterangepicker');
        rangePicker.setStartDate(this.tableStartDate);
        rangePicker.setEndDate(this.tableEndDate);

        $reportFilter.find('#filter-button').click(function () {
            $reportFilter.submit();
        });

        //Фиксирование верхнего и левого блоков таблицы
        chessBoardContentBlock.onscroll = function () {
            ChessBoardManager.onContentTableScroll(chessBoardContentBlock);
        };

        let templatePackageElement = ChessBoardManager.getTemplateElement();
        //Создание брони
        let dateElements = $('.date, .leftRooms');
        if (canCreatePackage) {
            dateElements.mousedown(function (event) {
                let startXPosition = event.pageX;
                let startLeftScroll = chessBoardContentBlock.scrollLeft;
                let newPackage = templatePackageElement.cloneNode(true);
                let dateJqueryObject = $(this.parentNode);
                let currentRoomDateElements = dateJqueryObject.parent().children();
                let startDateNumber = currentRoomDateElements.index(dateJqueryObject);
                let startDate = moment(self.tableStartDate).add(startDateNumber, 'day');
                newPackage = ChessBoardManager.setPackageOffset(newPackage, startDate, dateJqueryObject.parent().parent(), wrapper);
                newPackage.id = 'newPackage' + packages.length;
                newPackage.style.width = this.DATE_ELEMENT_WIDTH + 'px';
                let newPackageStartXOffset = parseInt(newPackage.style.left, 10);
                document.onmousemove = function (event) {
                    let scrollOffset = chessBoardContentBlock.scrollLeft - startLeftScroll;
                    let mouseXOffset = startXPosition - event.pageX;
                    let isLeftMouseShift = mouseXOffset > 0;
                    let packageLengthRestriction = ChessBoardManager.getPackageLengthRestriction(startDate, isLeftMouseShift, self.tableStartDate, self.tableEndDate);
                    let griddedOffset = ChessBoardManager.getGriddedOffset(mouseXOffset, scrollOffset, packageLengthRestriction);
                    let leftMouseOffset = isLeftMouseShift ? griddedOffset : 0;
                    let packageWidth = griddedOffset;
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
    }

    public static getTableStartDate() {
        return moment(document.getElementById('accommodation-report-begin').value, "DD.MM.YYYY");
    }

    public static getTableEndDate() {
        return moment(document.getElementById('accommodation-report-end').value, "DD.MM.YYYY");
    }

    private static setContentWidth(chessBoardContentBlock) {
        let contentWidth = parseInt($('#months-and-dates').css('width'), 10)
            + ChessBoardManager.LEFT_BAR_WIDTH + ChessBoardManager.SCROLL_BAR_WIDTH;

        if (parseInt($(chessBoardContentBlock).css('width'), 10) > contentWidth) {
            chessBoardContentBlock.style.width = contentWidth + 'px';
        } else {
            chessBoardContentBlock.style.width = 'auto';
        }
    }

    private saveNewPackage(packageElement) {
        'use strict';
        let packageData = ChessBoardManager.getPackageData($(packageElement));
        let $searchPackageForm = $('#package-search-form');

        $searchPackageForm.find('#s_roomType').val(packageData.roomType);
        $searchPackageForm.find('#s_begin').val(packageData.begin);
        $searchPackageForm.find('#s_end').val(packageData.end);
        $searchPackageForm.find('#s_range').val('0');

        let searchData = $searchPackageForm.serialize();
        this.dataManager.getPackageOptionsRequest(searchData, packageData);
    }

    private static getGriddedOffset(mouseXOffset, scrollOffset, packageLengthRestriction) {
        'use strict';
        let griddedOffset = Math.ceil((Math.abs(mouseXOffset) + scrollOffset) / ChessBoardManager.DATE_ELEMENT_WIDTH) * ChessBoardManager.DATE_ELEMENT_WIDTH;

        griddedOffset = griddedOffset > packageLengthRestriction ? packageLengthRestriction : griddedOffset;
        return griddedOffset;
    }

    private static onContentTableScroll(chessBoardContentBlock) {
        'use strict';
        let types = document.getElementById('roomTypeColumn');
        types.style.left = chessBoardContentBlock.scrollLeft + 'px';

        let monthsAndDates = document.getElementById('months-and-dates');
        monthsAndDates.style.top = chessBoardContentBlock.scrollTop + 'px';

        let headerTitle = document.getElementById('header-title');
        headerTitle.style.top = chessBoardContentBlock.scrollTop + 'px';
        headerTitle.style.left = chessBoardContentBlock.scrollLeft + 'px';
    }

    private static getPackageLengthRestriction(startDate, isLeftMouseShift, tableStartDate, tableEndDate) {
        'use strict';
        if (isLeftMouseShift) {
            return startDate.diff(tableStartDate, 'days') * ChessBoardManager.DATE_ELEMENT_WIDTH;
        }

        return tableEndDate.diff(startDate, 'days') * ChessBoardManager.DATE_ELEMENT_WIDTH;
    }

    public addAccommodationElements() {
        let wrapper = $('#calendarWrapper');
        let templatePackageElement = ChessBoardManager.getTemplateElement();
        console.time('elements creating');
        let packages = document.createElement('div');
        // for (let i = 0; i < 100; i++) {
        //iterate packages
        let accommodationsData = this.dataManager.getAccommodations();
        for (let accommodationId in accommodationsData) {
            if (accommodationsData.hasOwnProperty(accommodationId)) {
                let accommodationData = accommodationsData[accommodationId];
                if (accommodationData.accommodation) {
                    let packageDiv = this.createPackageElementWithOffset(templatePackageElement,
                        accommodationData, wrapper, packages.childNodes);
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
    }


    private static getTemplateElement() {
        let templateDiv = document.createElement('div');
        templateDiv.style = 'position: absolute;';
        templateDiv.style.height = ChessBoardManager.PACKAGE_ELEMENT_HEIGHT + 'px';
        templateDiv.classList.add('package');

        let buttonsDiv = document.createElement('div');
        buttonsDiv.classList.add('package-action-buttons');
        templateDiv.appendChild(buttonsDiv);

        return templateDiv;
    }

    public createPackageElement(packageItem, templatePackageElement = null, hasButtons = true, elements = []) {
        if (!templatePackageElement) {
            templatePackageElement = ChessBoardManager.getTemplateElement();
        }

        let packageStartDate = ChessBoardManager.getMomentDate(packageItem.begin);
        let packageEndDate = ChessBoardManager.getMomentDate(packageItem.end);

        let packageCellCount = packageEndDate.diff(packageStartDate, 'days');
        let packageWidth = packageCellCount * ChessBoardManager.DATE_ELEMENT_WIDTH;

        let packageDiv = templatePackageElement.cloneNode(true);
        packageDiv.style.width = packageWidth + 'px';
        packageDiv.id = packageItem.id;
        let description = document.createElement('div');
        let packageName = (packageItem.payer) ? packageItem.payer : packageItem.number;
        let descriptionText = packageName ? packageName.substr(0, packageCellCount * 5 - 1) : '';
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
        } else if (packageItem.isCheckIn) {
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
            let lastElement = elements[elements.length - 1];
            if (lastElement.getAttribute('data-description') == descriptionText) {
                let lastElementIndex = lastElement.style.zIndex != ''
                    ? lastElement.style.zIndex : ChessBoardManager.ACCOMMODATION_ELEMENT_ZINDEX;
                packageDiv.style.zIndex = lastElementIndex - 1;
            }
        }

        return packageDiv;
    }

    private createPackageElementWithOffset(templatePackageElement, packageItem, wrapper, elements) {
        let packageDiv = this.createPackageElement(packageItem, templatePackageElement, true, elements);
        let packageStartDate = ChessBoardManager.getMomentDate(packageItem.begin);
        let packageEndDate = ChessBoardManager.getMomentDate(packageItem.end);
        let roomDatesListElement = $('#' + packageItem.accommodation);

        packageDiv = ChessBoardManager.setPackageOffset(packageDiv, packageStartDate, roomDatesListElement, wrapper);
        packageDiv = this.editAccommodationElement(packageDiv, packageStartDate, packageEndDate);

        return packageDiv;
    }

    private static setPackageOffset(packageElement, startDate, roomLineElement, wrapper) {
        'use strict';
        let wrapperTopOffset = parseInt(wrapper.offset().top, 10);
        let roomLineTopOffset = parseInt(roomLineElement.offset().top, 10);
        packageElement.style.left = ChessBoardManager.getPackageLeftOffset(startDate) + 'px';
        packageElement.style.top = roomLineTopOffset - wrapperTopOffset + 'px';

        return packageElement;
    }

    private editAccommodationElement(element, packageStartDate, packageEndDate) {
        if (packageStartDate.isBefore(this.tableStartDate)) {
            let extraElementWidth = Math.abs(parseInt(element.style.left, 10));
            element.style.width = parseInt(element.style.width, 10) - extraElementWidth + 'px';
            element.style.left = 0;
            $(element).removeClass('with-left-divider');
        }

        if (packageEndDate.isAfter(this.tableEndDate)) {
            let differenceInDays = packageEndDate.diff(this.tableEndDate, 'days');
            element.style.width = parseInt(element.style.width, 10)
                - (differenceInDays - 1) * ChessBoardManager.DATE_ELEMENT_WIDTH
                - ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET + 'px';
        }

        let descriptionElement = element.querySelector('.package-description');
        let elementWidth = parseInt(element.style.width, 10);
        let descriptionWidth = parseInt(descriptionElement.style.width, 10);
        if (descriptionWidth > elementWidth) {
            descriptionElement.style.width = elementWidth + 'px';
        }

        if (elementWidth < ChessBoardManager.DATE_ELEMENT_WIDTH) {
            let divideButton = element.querySelector('.divide-package-button');
            if (divideButton) {
                divideButton.parentNode.removeChild(divideButton);
            }
            descriptionElement.parentNode.removeChild(descriptionElement);
        }

        return element;
    }

    private static getNearestTableLineTopOffset(yCoordinate) {
        let topOffset = null;
        let tableLines = [].slice.call(document.getElementsByClassName('roomDates'));
        tableLines.some(function (line) {
            let lineTopOffset = line.getBoundingClientRect().top;
            if (yCoordinate >= lineTopOffset && yCoordinate <= (lineTopOffset + ChessBoardManager.DATE_ELEMENT_HEIGHT)) {
                topOffset = lineTopOffset;
                return true;
            } else {
                return false;
            }
        });
        if (!topOffset) {
            topOffset = tableLines[1].getBoundingClientRect().top;
        }

        return topOffset;
    }

    private static getPackageLeftOffset(startDate) {
        let tableStartDate = this.getTableStartDate();
        let packageDateOffset = startDate.diff(tableStartDate, 'days') * ChessBoardManager.DATE_ELEMENT_WIDTH;

        return packageDateOffset + ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET;
    }

    public static getMomentDate(dateString) {
        return moment(dateString, "DD.MM.YYYY");
    }

    private addListeners(identifier) {
        let jQueryObj = $(identifier);
        let self = this;

        console.time('draggable');
        this.addDraggable(jQueryObj);
        console.timeEnd('draggable');
        console.time('resizable etc');
        jQueryObj.each(function (index, element) {
            let intervalData = self.dataManager.getAccommodationIntervalById(this.id);
            let $element = $(element);
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
                    let scissorsElement = this.childNodes[0];
                    scissorsElement.onclick = function () {
                        self.updatePackagesData();
                    };
                    let accommodationElement = this.parentNode.parentNode;
                    let accommodationWidth = parseInt(accommodationElement.style.width, 10);
                    if (accommodationWidth == ChessBoardManager.DATE_ELEMENT_WIDTH * 2) {
                        $('.divide-package-button').tooltip('hide');
                        self.divide(accommodationElement, accommodationWidth / 2);
                    } else {
                        let packageLeftCoordinate = accommodationElement.getBoundingClientRect().left;
                        let line = document.createElement('div');
                        line.style = 'position:absolute; border: 2px dashed red; height: 41px; z-index = 250;top: 0';

                        let isAccommodationAbroadTable = (parseInt(getComputedStyle(accommodationElement).width, 10)
                            % ChessBoardManager.DATE_ELEMENT_WIDTH) != 0;
                        let defaultLeftValue = isAccommodationAbroadTable
                            ? ChessBoardManager.DATE_ELEMENT_WIDTH + ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET
                            : ChessBoardManager.DATE_ELEMENT_WIDTH;
                        line.style.left = defaultLeftValue + 'px';
                        accommodationElement.appendChild(line);

                        accommodationElement.onmousemove = function (event) {
                            let offset = event.clientX - packageLeftCoordinate;
                            let griddedOffset;
                            if (isAccommodationAbroadTable) {
                                griddedOffset = Math.floor(Math.abs(offset) / ChessBoardManager.DATE_ELEMENT_WIDTH)
                                    * ChessBoardManager.DATE_ELEMENT_WIDTH
                                    + ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET;
                            } else {
                                griddedOffset = Math.floor(Math.abs(offset + ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET)
                                        / ChessBoardManager.DATE_ELEMENT_WIDTH) * ChessBoardManager.DATE_ELEMENT_WIDTH;
                            }

                            if (griddedOffset == 0) {
                                griddedOffset += defaultLeftValue;
                            } else if (griddedOffset == accommodationWidth) {
                                griddedOffset -= ChessBoardManager.DATE_ELEMENT_WIDTH;
                            }

                            line.style.left = griddedOffset + 'px';
                            accommodationElement.onclick = function () {
                                accommodationElement.onmousemove = null;
                                accommodationElement.removeChild(line);
                                self.divide(this, griddedOffset);
                            }
                        };
                    }
                }
            });
        });
        console.timeEnd('resizable etc');
    }

    private divide(packageElement, firstAccommodationWidth) {
        if (packageElement.parentNode) {
            let packageWidth = parseInt(packageElement.style.width, 10);
            $(packageElement).find('.divide-package-button, .remove-package-button, .ui-resizable-e, .ui-resizable-w').remove();
            if (firstAccommodationWidth != 0 && firstAccommodationWidth != packageWidth) {
                let firstAccommodation = packageElement.cloneNode(true);
                firstAccommodation.style.width = firstAccommodationWidth + 'px';
                firstAccommodation = ChessBoardManager.setDividedElementDescription(firstAccommodation, firstAccommodationWidth);

                let secondAccommodation = packageElement.cloneNode(true);
                secondAccommodation = this.addDraggable($(secondAccommodation), false, true).get(0);
                let secondAccommodationWidth = packageWidth - firstAccommodationWidth;
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
        } else {
            this.updatePackagesData();
        }
    }

    private static setDividedElementDescription(element, elementWidth) {
        let descriptionElement = element.querySelector('.package-description');
        let contentWidth = descriptionElement.innerHTML.length * 8 - 3;
        let descriptionWidth = contentWidth > elementWidth ? elementWidth : contentWidth;
        descriptionElement.style.width = descriptionWidth + 'px';

        return element;
    }

    private isDraggableRevert(packageElement) {
        return !(this.isPackageLocationCorrect(packageElement));
    }

    private addDraggable(jQueryObj, noAccommodation = false, isDivide = false) {
        let self = this;
        jQueryObj.each(function (index, element) {
            let intervalData;
            if (noAccommodation) {
                intervalData = self.dataManager.getNoAccommodationIntervalById(element.id);
            } else {
                intervalData = self.dataManager.getAccommodationIntervalById(element.id);
            }

            let axisValue = ChessBoardManager.getDraggableAxisValue(intervalData, isDivide);
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
                        } else {
                            ui.position.top = ChessBoardManager.getGriddedHeightValue(ui.position.top + ChessBoardManager.DATE_ELEMENT_HEIGHT / 2);
                            if (!self.isPackageLocationCorrect(this)) {
                                this.classList.add('red-package');
                            } else {
                                this.classList.remove('red-package');
                            }
                        }
                    },
                    stop: function (event, ui) {
                        if (self.isDraggableRevert(this)) {
                            self.updatePackagesData();
                        } else {
                            let intervalData = self.dataManager.getAccommodationIntervalById(this.id);
                            if (!intervalData) {
                                intervalData = self.dataManager.getNoAccommodationIntervalById(this.id);
                            }
                            if (ui.originalPosition.left != ui.position.left
                                || ui.originalPosition.top != ui.position.top || noAccommodation) {
                                let changedSide = axisValue == 'x, y' ? 'both' : null;
                                ActionManager.callUpdatePackageModal($(this), intervalData, changedSide, isDivide);
                            }
                        }
                    },
                });
            }
        });

        return jQueryObj;
    }

    private static getDraggableAxisValue(intervalData, isDivide) {
        if (intervalData.updateAccommodation && !isDivide && intervalData.position == 'full'
            && ChessBoardManager.isAccommodationOnFullPackage(intervalData) && intervalData.updatePackage) {
            return 'x, y';
        } else if (intervalData.updateAccommodation
            //Если интервал не имеет размещения, но имеет права на создание размещения(просмотр брони)
            || (intervalData.updateAccommodation == undefined) && intervalData.viewPackage) {
            return 'y';
        }
        return '';
    }

    private isPackageLocationCorrect(packageElement) {
        let $packageElement = $(packageElement);
        let packageOffset = $packageElement.offset();

        return (this.isOnRoomDatesLine(packageOffset) || this.isOnLeftRoomsLine(packageOffset))
            && !ChessBoardManager.isAbroadTable(packageElement, packageOffset)
            && !this.isPackageOverlapped($packageElement);
    }

    private static isAccommodationOnFullPackage(intervalData) {
        return intervalData.position !== undefined
            && intervalData.position === 'full'
            && intervalData.packageEnd == intervalData.end
            && intervalData.packageBegin == intervalData.begin;
    }

    /**
     * Проверяет не выходит ли бронь за правую границу таблицы
     *
     * @param packageElement
     * @param packageOffset
     * @returns {boolean}
     */
    private static isAbroadTable(packageElement, packageOffset) {
        let lastDateElementLeftOffset = parseInt($('.roomDates:eq(0)').children().children().last().offset().left, 10)
            + ChessBoardManager.DATE_ELEMENT_WIDTH;
        let packageEndLeftOffset = packageOffset.left + parseInt(packageElement.style.width, 10);

        return lastDateElementLeftOffset < packageEndLeftOffset;
    }

    /**
     * Проверяет находится ли бронь на одной из линий, указывающих размещение брони
     *
     * @param packageOffset
     * @returns {boolean}
     */
    private isOnRoomDatesLine(packageOffset) {
        return this.isPackageOnSpecifiedLine('roomDates', packageOffset);
    }

    private isOnLeftRoomsLine(packageOffset) {
        return this.isPackageOnSpecifiedLine('leftRoomsLine', packageOffset);
    }

    private isPackageOnSpecifiedLine(lineClass, packageOffset) {
        let specifiedLine = document.getElementsByClassName(lineClass);
        return Array.prototype.some.call(specifiedLine, function (element) {
            return ChessBoardManager.saveOffsetCompare(packageOffset.top, $(element).offset().top);
        });
    }

    /**
     * Проверяет, пересекется ли период размещения брони с другими бронями, имеющими такой же тип размещения
     *
     * @param $packageElement
     * @returns {boolean}
     */
    private isPackageOverlapped($packageElement) {
        let packageData = ChessBoardManager.getPackageData($packageElement);
        let intervalsData = this.dataManager.getAccommodations();
        return Object.getOwnPropertyNames(intervalsData).some(function (intervalId) {
            let intervalData = intervalsData[intervalId];
            return !(intervalData.id === packageData.id)
                && intervalData.accommodation === packageData.accommodation
                && ChessBoardManager.getMomentDate(intervalData.begin).isBefore(moment(packageData.end, "DD.MM.YYYY"))
                && ChessBoardManager.getMomentDate(intervalData.end).isAfter(moment(packageData.begin, "DD.MM.YYYY"));
        });
    }

    private static getGriddedHeightValue(height) {
        //1 - бордер
        return Math.floor(height / ChessBoardManager.PACKAGE_ELEMENT_HEIGHT) * ChessBoardManager.PACKAGE_ELEMENT_HEIGHT - 1;
    }

    private isAbroadLeftTableSide(intervalMomentBegin) {
        return intervalMomentBegin.isBefore(this.tableStartDate);
    }

    private isAbroadRightTableSide(intervalMomentEnd) {
        return intervalMomentEnd.isAfter(this.tableEndDate);
    }

    private getResizableHandlesValue(intervalData) {
        let resizableHandlesValue;
        switch (intervalData.position) {
            case 'left':
                if (this.isAbroadLeftTableSide(ChessBoardManager.getMomentDate(intervalData.begin))) {
                    resizableHandlesValue = '';
                } else {
                    resizableHandlesValue = 'w';
                }
                break;
            case 'right':
                if (this.isAbroadRightTableSide(ChessBoardManager.getMomentDate(intervalData.end))) {
                    resizableHandlesValue = '';
                } else {
                    resizableHandlesValue = 'e';
                }
                break;
            case 'middle':
                resizableHandlesValue = '';
                break;
            case 'full':
                let isAbroadLeftTableSide = ChessBoardManager.getMomentDate(intervalData.begin).isBefore(this.tableStartDate);
                let isAbroadRightTableSide = ChessBoardManager.getMomentDate(intervalData.end).isAfter(this.tableEndDate);
                //Проверяем занимает находится ли данное размещение с начала(конца) брони,
                // и имеет ли в этом случае права на изменение брони
                let canChangeLeftSide = !(intervalData.packageBegin == intervalData.begin && !intervalData.updatePackage)
                    && !isAbroadLeftTableSide;
                let canChangeRightSide = !(intervalData.packageEnd == intervalData.end && !intervalData.updatePackage)
                    && !isAbroadRightTableSide;

                if (canChangeLeftSide && canChangeRightSide) {
                    resizableHandlesValue = 'w, e';
                } else if (canChangeLeftSide && !canChangeRightSide) {
                    resizableHandlesValue = 'w';
                } else {
                    resizableHandlesValue = 'e';
                }
                break;
        }

        return resizableHandlesValue;
    }

    private addResizable($element, intervalData) {
        let elementStartBackground;
        let self = this;

        let resizableHandlesValue = this.getResizableHandlesValue(intervalData);
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
                    } else {
                        if (self.isPackageOverlapped($(this))) {
                            this.style.backgroundColor = 'rgba(232, 34, 34, 0.6)';
                        } else {
                            this.style.backgroundColor = elementStartBackground;
                        }
                    }
                },
                stop: function (event, ui) {
                    this.style.backgroundColor = elementStartBackground;
                    if (!self.isPackageLocationCorrect(this)) {
                        ui.element.css(ui.originalPosition);
                        ui.element.css(ui.originalSize);
                    } else {
                        let isSizeChanged = parseInt(this.style.width, 10) != ui.originalSize.width;
                        if (isSizeChanged) {
                            let changedSide = parseInt(this.style.left, 10) == ui.originalPosition.left ? 'right' : 'left';
                            ActionManager.callUpdatePackageModal($(this), intervalData, changedSide);
                        }
                    }
                }
            });
        }

        return $element;
    }

    public static getPackageData($packageElement) {
        let packageOffset = $packageElement.offset();
        let roomLine = $('.roomDates, .leftRoomsLine').filter(function () {
            return ChessBoardManager.saveOffsetCompare($(this).offset().top, packageOffset.top);
        });
        let roomTypeId = roomLine.parent().get(0).id || roomLine.get(0).getAttribute('data-roomtypeid');
        let accommodationId = roomLine.children().get(0).id;
        if (accommodationId.substring(0, 16) === 'no_accommodation') {
            accommodationId = '';
        }
        let dateElements = roomLine.children().children();

        let description = $packageElement.find('.package-description').text();

        let startDateLeftOffset = packageOffset.left - ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET;
        let startDate = this.getDateStringByLeftOffset(dateElements, startDateLeftOffset);

        let endDateLeftOffset = packageOffset.left + parseInt($packageElement.get(0).style.width, 10) - this.PACKAGE_TO_MIDDAY_OFFSET;
        let endDate = this.getDateStringByLeftOffset(dateElements, endDateLeftOffset);
        return {
            id: $packageElement.get(0).id,
            accommodation: accommodationId,
            roomType: roomTypeId,
            begin: startDate,
            end: endDate,
            payer: description
        };
    }

    private static saveOffsetCompare(firstOffset, secondOffset) {
        let firstIntOffset = parseInt(firstOffset, 10);
        let secondIntOffset = parseInt(secondOffset, 10);
        return (firstIntOffset === secondIntOffset)
            || (firstIntOffset === secondIntOffset + 1)
            || (firstIntOffset === secondIntOffset - 1);
    }

    private static getDateStringByLeftOffset(dateElements, leftOffset) {
        let dateElement = dateElements.filter(function () {
            return ChessBoardManager.saveOffsetCompare($(this).offset().left, leftOffset);
        });
        let dateNumber = dateElements.index(dateElement);
        let momentDate = moment(document.getElementById('accommodation-report-begin').value, "DD.MM.YYYY")
            .add(dateNumber, 'day');

        return momentDate.format("DD.MM.YYYY");
    }

    public updateTable() {
        this.updatePackagesData();
        this.updateLeftRoomCounts();
        this.updateNoAccommodationPackageCounts();
    }

    private updateNoAccommodationPackageCounts() {
        let self = this;
        $('.roomTypeRooms').each(function (index, noAccommodationLine) {
            let roomTypeNoAccommodationCounts = self.dataManager.getNoAccommodationCounts()[noAccommodationLine.id];
            let noAccommodationDayElements = noAccommodationLine.children[0].children[0].children;
            for (let i = 0; i < noAccommodationDayElements.length; i++) {
                let innerText = '';
                let dayElement = noAccommodationDayElements[i].children[0];
                if (roomTypeNoAccommodationCounts[i] !== 0) {
                    innerText = roomTypeNoAccommodationCounts[i];
                    dayElement.classList.add('achtung');
                } else {
                    dayElement.classList.remove('achtung');
                }
                dayElement.innerHTML = innerText;
            }
        });

        this.hangPopover();
    }

    private hangPopover() {
        let self = this;

        let $noAccommodationElements = $('.no-accommodation-date');
        $noAccommodationElements.popover('destroy');

        let $popoverElements = $('.no-accommodation-date.achtung');
        $popoverElements.popover();

        //     $('.popover').popover('hide');
        // });

        $popoverElements.on('shown.bs.popover', function () {
            self.updatePackagesData();
            let roomTypeId = this.parentNode.parentNode.parentNode.parentNode.id;
            let currentDate = moment(this.getAttribute('data-date'), "DD.MM.YYYY");
            let templatePackageElement = ChessBoardManager.getTemplateElement();
            let packageElementsContainer = document.createElement('div');

            let packagesByCurrentDate = self.dataManager.getNoAccommodationPackagesByDate(currentDate, roomTypeId);

            let isDragged = false;
            let relocatablePackage;
            let relocatablePackageData;

            let $wrapper = $('#calendarWrapper');
            let wrapperTopOffset = parseInt($wrapper.offset().top, 10);
            let $popover = $('.popover').last();

            let $popoverContent = $popover.find('.popover-content');

            packagesByCurrentDate.forEach(function (packageData) {
                let packageElement = self.createPackageElement(packageData, templatePackageElement, false);
                packageElement.style.position = '';
                packageElement.style.display = 'inline-block';
                packageElement.style.zIndex = 150;

                let packageContainer = document.createElement('div');
                packageContainer.classList.add('popover-package-container');

                packageContainer.appendChild(self.getInfoButton(packageData.packageId));
                packageContainer.appendChild(ChessBoardManager.getEditButton(packageData.packageId));
                packageContainer.appendChild(packageElement);

                packageElementsContainer.innerHTML += packageContainer.outerHTML;

                $popoverContent.append(packageContainer);
            });

            $popoverContent.find('.package').each(function (index, element) {
                let $packageElement = $(element);
                let packageData = self.dataManager.getNoAccommodationIntervalById(element.id);

                if (ChessBoardManager.getDraggableAxisValue(packageData, false) != '') {
                    self.addDraggable($packageElement, true).draggable({
                        scroll: false,
                        snap: 'calendarRow',
                        start: function () {
                            isDragged = true;
                        },
                    }).mousedown(function (event) {
                        if (ChessBoardManager.isIntervalAvailable(packageData)) {
                            relocatablePackage = this;
                            $wrapper.append(this);
                            this.style.position = 'absolute';
                            relocatablePackageData = self.dataManager.getNoAccommodationIntervalById(this.id);
                            let intervalStartDate = ChessBoardManager.getMomentDate(relocatablePackageData.begin);
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
            let currentPopover = $popover.get(0);
            let popoverOffset = currentPopover.offsetWidth - ChessBoardManager.POPOVER_MIN_WIDTH;
            if (popoverOffset !== 0) {
                currentPopover.style.left = (parseInt(currentPopover.style.left, 10) - popoverOffset / 2) + 'px';
            }
        });
    }

    private static isIntervalAvailable(packageData, isDivide = false) {
        if (packageData.isLocked) {
            ActionManager.callUnblockModal(packageData.packageId);
            event.preventDefault();
            return false;
        }

        if (!isDivide) {
            let intervalOutOfTableSide = ChessBoardManager.getIntervalOutOfTableSide(packageData);
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
    }

    private static getIntervalWidth(intervalData) {
        let packageStartDate = ChessBoardManager.getMomentDate(intervalData.begin);
        let packageEndDate = ChessBoardManager.getMomentDate(intervalData.end);

        let packageCellCount = packageEndDate.diff(packageStartDate, 'days');

        return packageCellCount * ChessBoardManager.DATE_ELEMENT_WIDTH;
    }

    private static getTableWidth() {
        let styles = getComputedStyle(document.getElementById('accommodation-chessBoard-content'));
        return parseInt(styles.width, 10) - ChessBoardManager.LEFT_BAR_WIDTH;
    }

    private static getIntervalOutOfTableSide(intervalData) {
        let tableBeginDate = ChessBoardManager.getTableStartDate();
        let intervalBeginDate = ChessBoardManager.getMomentDate(intervalData.begin);
        let isIntervalBeginOutOfTableBegin = intervalBeginDate.isBefore(tableBeginDate);

        let tableEndDate = ChessBoardManager.getTableEndDate();
        let intervalEndDate = ChessBoardManager.getMomentDate(intervalData.end);
        let isIntervalEndOutOfTableBegin = intervalEndDate.isAfter(tableEndDate);

        if (isIntervalBeginOutOfTableBegin && isIntervalEndOutOfTableBegin) {
            return 'both';
        }
        if (isIntervalBeginOutOfTableBegin) {
            return 'begin';
        }
        if (isIntervalEndOutOfTableBegin) {
            return 'end';
        }
    }

    private  updateLeftRoomCounts() {
        let self = this;
        let leftRoomCounts = self.dataManager.getLeftRoomCounts();
        $('.leftRoomsLine').each(function (index, item) {
            let roomTypeId = item.getAttribute('data-roomtypeid');
            if (leftRoomCounts[roomTypeId]) {
                let dateElements = item.children[0].children;
                for (let i = 0; i < dateElements.length; i++) {
                    dateElements[i].children[0].innerHTML = leftRoomCounts[roomTypeId][i];
                }
            }
        })
    }

    private updatePackagesData() {
        ChessBoardManager.deleteAllPackages();
        this.addAccommodationElements();
    }

    private static deleteAllPackages() {
        let packages = document.getElementsByClassName('package');
        while (packages[0]) {
            packages[0].parentNode.removeChild(packages[0]);
        }​
    }

    private static getTemplateRemoveButton() {
        let removeButton = document.createElement('button');
        removeButton.setAttribute('type', 'button');
        removeButton.setAttribute('title', Translator.trans('chessboard_manager.remove_button.popup'));
        removeButton.setAttribute('data-toggle', 'tooltip');
        removeButton.classList.add('remove-package-button');
        removeButton.innerHTML = '<i class="fa fa-times" aria-hidden="true"></i>';

        return removeButton;
    }

    private static getTemplateDivideButton() {
        let divideButton = document.createElement('button');
        divideButton.setAttribute('type', 'button');
        divideButton.setAttribute('title', Translator.trans('chessboard_manager.divide_button.popup'));
        divideButton.setAttribute('data-toggle', 'tooltip');
        divideButton.classList.add('divide-package-button');
        divideButton.innerHTML = '<i class="fa fa-scissors" aria-hidden="true"></i>';

        return divideButton;
    }

    private getInfoButton(packageId) {
        let infoButton = document.createElement('button');
        infoButton.setAttribute('title', Translator.trans('chessboard_manager.info_button.popup.title'));
        infoButton.setAttribute('type', 'button');
        infoButton.setAttribute('data-toggle', 'tooltip');
        infoButton.setAttribute('data-placement', "right");
        infoButton.classList.add('popover-info-button');
        infoButton.innerHTML = '<i class="fa fa-info-circle" aria-hidden="true"></i>';

        let self = this;
        infoButton.onclick = function () {
            self.dataManager.getPackageDataRequest(packageId);
        };

        return infoButton;
    }

    private static getEditButton(packageId) {
        let editButton = document.createElement('a');
        editButton.setAttribute('href', Routing.generate('package_edit', {id: packageId}));
        editButton.setAttribute('target', '_blank');
        editButton.setAttribute('title', Translator.trans('chessboard_manager.edit_button.popup.title'));
        editButton.setAttribute('data-toggle', 'tooltip');
        editButton.setAttribute('data-placement', "right");
        editButton.classList.add('popover-edit-button');
        editButton.innerHTML = '<i class="fa fa-pencil-square-o" aria-hidden="true"></i>';

        return editButton;
    }
}