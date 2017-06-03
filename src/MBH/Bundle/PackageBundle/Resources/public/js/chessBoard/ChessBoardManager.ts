///<reference path="DataManager.ts"/>
declare let moment;
declare let $;
declare let mbh;
declare let canCreatePackage;
declare let Translator;
declare let styleConfigs;
declare let currentStyleConfigNumber;

class ChessBoardManager {
    private static PACKAGE_FONT_SIZE_WIDTH = 8;
    private static POPOVER_MIN_WIDTH = 250;
    private static SCROLL_BAR_WIDTH = 16;
    private static ACCOMMODATION_ELEMENT_ZINDEX = 100;

    public dataManager;
    public actionManager;
    private templateRemoveButton;
    private templateDivideButton;
    private tableStartDate;
    private tableEndDate;
    private canMoveAccommodation = true;
    private currentSizeConfigNumber = currentStyleConfigNumber;

    public static deletePackageElement(packageId) {
        let packageElement = document.getElementById(packageId);
        if (packageElement) {
            packageElement.parentElement.removeChild(packageElement);
        }
    }

    private getPackageToMiddayOffset() {
        return Math.round(styleConfigs[this.currentSizeConfigNumber].tableCellWidth / 2);
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
        this.setContentWidth(chessBoardContentBlock);
        $('.sidebar-toggle').click(function () {
            setTimeout(function () {
                self.setContentWidth(chessBoardContentBlock);
            }, 1000)
        });
        this.addAccommodationElements();
        $('#s_tourist').change(function () {
            $('#select2-s_tourist-results').val(this.value);
        });

        $('.tile-bookable').find('.date').hover(function () {
            $(this).children('div').show();
        }, function () {
            if (!$(this).hasClass('selected-date-row')) {
                $(this).children('div').hide();
            }
        });

        $('.pagination-sm').find('a').each(function () {
            let filterData = $('#accommodation-report-filter').serialize() + '&page=' + $(this).text();
            let route = Routing.generate('chess_board_home') + '?' + filterData;
            this.setAttribute('href', route);
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

        $('#modal_delete_package').on('hidden.bs.modal', function () {
            self.updateTable();
        });
        self.handleSizeSlider();
        document.getElementById('packageModalConfirmButton').onclick = function () {
            let data = ActionManager.getDataFromUpdateModal();
            let packageId = data.packageId;
            let accommodationId = data.accommodationId;
            let packageData = self.getPackageData($('#' + data.id));
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

        this.onAddGuestClick();

        //Фиксирование верхнего и левого блоков таблицы
        chessBoardContentBlock.onscroll = function () {
            ChessBoardManager.onContentTableScroll(chessBoardContentBlock);
        };

        let templatePackageElement = self.getTemplateElement();
        //Создание брони
        let dateElements = $('.date, .leftRooms');
        if (canCreatePackage) {
            dateElements.mousedown(function (event) {
                let startXPosition = event.pageX;
                let startLeftScroll = chessBoardContentBlock.scrollLeft;
                let newPackage = <HTMLElement>templatePackageElement.cloneNode(true);
                let dateJqueryObject = $(this.parentNode);
                let currentRoomDateElements = dateJqueryObject.parent().children();
                let startDateNumber = currentRoomDateElements.index(dateJqueryObject);
                let startDate = moment(self.tableStartDate).add(startDateNumber, 'day');
                newPackage = self.setPackageOffset(newPackage, startDate, dateJqueryObject.parent().parent(), wrapper);
                newPackage.id = 'newPackage' + packages.length;
                newPackage.style.width = styleConfigs[self.currentSizeConfigNumber].tableCellWidth + 'px';
                let newPackageStartXOffset = parseInt(newPackage.style.left, 10);
                document.onmousemove = function (event) {
                    let scrollOffset = chessBoardContentBlock.scrollLeft - startLeftScroll;
                    let mouseXOffset = startXPosition - event.pageX;
                    let isLeftMouseShift = mouseXOffset > 0;
                    let packageLengthRestriction = self.getPackageLengthRestriction(startDate, isLeftMouseShift, self.tableStartDate, self.tableEndDate);
                    let griddedOffset = self.getGriddedOffset(mouseXOffset, scrollOffset, packageLengthRestriction);
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
                        let packageData = self.getPackageData($(newPackage));
                        self.saveNewPackage(packageData);
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
        return moment((<HTMLInputElement>document.getElementById('accommodation-report-begin')).value, "DD.MM.YYYY");
    }

    public static getTableEndDate() {
        return moment((<HTMLInputElement>document.getElementById('accommodation-report-end')).value, "DD.MM.YYYY");
    }

    private handleSizeSlider() {
        let $slider = $('#ex1');
        $slider.slider({tooltip : 'hide', reverseed: true});
        $slider.on('slideStop', () => {
            let sliderValue = $('#ex1').slider('getValue');
            ChessBoardManager.setCookie('chessboardSizeNumber', sliderValue);
            this.currentSizeConfigNumber = sliderValue;
            window.location.reload();
            // this.setStyles(sliderValue);
        });
    }

    private static setCookie(name, value, options = {}) {

        value = encodeURIComponent(value);

        let updatedCookie = name + "=" + value;

        for (let propName in options) {
            updatedCookie += "; " + propName;
            let propValue = options[propName];
            if (propValue !== true) {
                updatedCookie += "=" + propValue;
            }
        }

        document.cookie = updatedCookie;
    }

    private setContentWidth(chessBoardContentBlock) {
        let contentWidth = parseInt($('#months-and-dates').css('width'), 10)
            + styleConfigs[this.currentSizeConfigNumber].headerWidth + ChessBoardManager.SCROLL_BAR_WIDTH;

        if (parseInt($(chessBoardContentBlock).css('width'), 10) > contentWidth) {
            chessBoardContentBlock.style.width = contentWidth + 'px';
        } else {
            chessBoardContentBlock.style.width = 'auto';
        }
    }

    private saveNewPackage(packageData) {
        'use strict';
        let $searchPackageForm = $('#package-search-form');

        $searchPackageForm.find('#s_roomType').val(packageData.roomType);
        $searchPackageForm.find('#s_begin').val(packageData.begin);
        $searchPackageForm.find('#s_end').val(packageData.end);
        $searchPackageForm.find('#s_range').val('0');
        let newPackageRequestData = ChessBoardManager.getNewPackageRequestData($searchPackageForm);

        this.dataManager.getPackageOptionsRequest(newPackageRequestData, packageData);
    }

    public static getNewPackageRequestData($searchPackageForm, specialId = null) {
        let newPackageRequestData = ChessBoardManager.getFilterData($searchPackageForm);
        if (specialId) {
            let specialString = 'special%5D=';
            let specialPosition = newPackageRequestData.indexOf(specialString);
            let specialValuePosition = specialPosition + specialString.length;
            newPackageRequestData = newPackageRequestData.slice(0, specialValuePosition) + specialId
                + newPackageRequestData.slice(specialValuePosition, newPackageRequestData.length);
        }

        return newPackageRequestData;
    }

    public static getFilterData($searchPackageForm) {
        let searchData = $searchPackageForm.serialize();
        let pageNumber = (<HTMLInputElement>document.getElementById('pageNumber')).value;

        return searchData + '&page=' + pageNumber;
    }

    private onAddGuestClick() {
        $('#add-guest').on('click', function (e) {
            let guestModal = $('#add-guest-modal'),
                form = guestModal.find('form'),
                button = $('#add-guest-modal-submit'),
                errors = $('#add-guest-modal-errors');

            e.preventDefault();
            guestModal.modal('show');
            button.click(function () {
                errors.hide();
                $.ajax({
                    method: "POST",
                    url: form.prop('action'),
                    data: form.serialize(),
                    success: function (data) {
                        if (data.error) {
                            errors.html(data.text).show();
                        } else {
                            $('.findGuest').append($("<option/>", {
                                value: data.id,
                                text: data.text
                            })).val(data.id).trigger('change');
                            form.trigger('reset');
                            guestModal.modal('hide');
                            form.find('select').select2('data', null);
                            return 1;
                        }
                    }
                })
            });
        });
    }

    private getGriddedOffset(mouseXOffset, scrollOffset, packageLengthRestriction) {
        'use strict';
        let griddedOffset = Math.ceil((Math.abs(mouseXOffset) + scrollOffset) / styleConfigs[this.currentSizeConfigNumber].tableCellWidth)
            * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;

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

    private getPackageLengthRestriction(startDate, isLeftMouseShift, tableStartDate, tableEndDate) {
        'use strict';
        if (isLeftMouseShift) {
            return startDate.diff(tableStartDate, 'days') * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
        }

        return tableEndDate.diff(startDate, 'days') * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
    }

    public addAccommodationElements() {
        let wrapper = $('#calendarWrapper');
        let templatePackageElement = this.getTemplateElement();
        let packages = document.createElement('div');

        //iterate packages
        let accommodationsData = this.dataManager.getAccommodations();
        let lastAddedElement = null;
        for (let accommodationId in accommodationsData) {
            if (accommodationsData.hasOwnProperty(accommodationId)) {
                let accommodationData = accommodationsData[accommodationId];
                if (accommodationData.accommodation) {
                    let packageDiv = this.createPackageElementWithOffset(templatePackageElement,
                        accommodationData, wrapper, lastAddedElement);
                    lastAddedElement = packageDiv;
                    packages.appendChild(packageDiv);
                }
            }
        }
        wrapper.append(packages);
        this.addListeners('.package');
    }


    private getTemplateElement() {
        let templateDiv: HTMLElement = document.createElement('div');
        templateDiv.style.position = 'absolute';
        templateDiv.style.height = styleConfigs[this.currentSizeConfigNumber].tableCellHeight + 1 + 'px';
        templateDiv.classList.add('package');

        let buttonsDiv = document.createElement('div');
        buttonsDiv.classList.add('package-action-buttons');
        templateDiv.appendChild(buttonsDiv);

        return templateDiv;
    }

    public createPackageElement(packageItem, templatePackageElement = null, hasButtons = true, lastAddedElement = null) {
        if (!templatePackageElement) {
            templatePackageElement = this.getTemplateElement();
        }

        let packageStartDate = ChessBoardManager.getMomentDate(packageItem.begin);
        let packageEndDate = ChessBoardManager.getMomentDate(packageItem.end);

        let packageCellCount = packageEndDate.diff(packageStartDate, 'days');
        let packageWidth = packageCellCount * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;

        let packageDiv = templatePackageElement.cloneNode(true);
        packageDiv.style.width = packageWidth + 'px';
        packageDiv.id = packageItem.id;
        let description = document.createElement('div');
        let packageName = (packageItem.payer) ? packageItem.payer : packageItem.number;
        let descriptionText = packageName ? packageName.substr(0, packageCellCount * 5 - 1) : '';
        packageDiv.setAttribute('data-description', descriptionText);
        packageDiv.setAttribute('data-package-id', packageItem.packageId);
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

        if (lastAddedElement) {
            if (lastAddedElement.getAttribute('data-package-id') == packageItem.packageId) {
                let lastElementIndex = lastAddedElement.style.zIndex != ''
                    ? lastAddedElement.style.zIndex : ChessBoardManager.ACCOMMODATION_ELEMENT_ZINDEX;
                packageDiv.style.zIndex = lastElementIndex - 1;
            }
        }

        return packageDiv;
    }

    private createPackageElementWithOffset(templatePackageElement, packageItem, wrapper, lastAddedElement) {
        let packageDiv = this.createPackageElement(packageItem, templatePackageElement, true, lastAddedElement);
        let packageStartDate = ChessBoardManager.getMomentDate(packageItem.begin);
        let packageEndDate = ChessBoardManager.getMomentDate(packageItem.end);
        let roomDatesListElement = $('#' + packageItem.accommodation);

        packageDiv = this.setPackageOffset(packageDiv, packageStartDate, roomDatesListElement, wrapper);
        packageDiv = this.editAccommodationElement(packageDiv, packageStartDate, packageEndDate);

        return packageDiv;
    }

    private setPackageOffset(packageElement, startDate, roomLineElement, wrapper) {
        'use strict';
        let wrapperTopOffset = parseInt(wrapper.offset().top, 10);
        let roomLineTopOffset = parseInt(roomLineElement.offset().top, 10);
        packageElement.style.left = this.getPackageLeftOffset(startDate) + 'px';
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
                - (differenceInDays - 1) * styleConfigs[this.currentSizeConfigNumber].tableCellWidth
                - this.getPackageToMiddayOffset() + 'px';
        }

        let descriptionElement = element.querySelector('.package-description');
        let elementWidth = parseInt(element.style.width, 10);
        let descriptionWidth = parseInt(descriptionElement.style.width, 10);
        if (descriptionWidth > elementWidth) {
            descriptionElement.style.width = elementWidth + 'px';
        }

        if (elementWidth < styleConfigs[this.currentSizeConfigNumber].tableCellWidth) {
            let divideButton = element.querySelector('.divide-package-button');
            if (divideButton) {
                divideButton.parentNode.removeChild(divideButton);
            }
            descriptionElement.parentNode.removeChild(descriptionElement);
        }

        return element;
    }

    private getNearestTableLineTopOffset(yCoordinate) {
        let tableLines = [].slice.call(document.getElementsByClassName('roomDates'));
        let topOffset = this.getNearestTableLineToYOffset(yCoordinate, tableLines);
        if (!topOffset) {
            let leftRoomsLines = [].slice.call(document.getElementsByClassName('leftRoomsLine'));
            topOffset = this.getNearestTableLineToYOffset(yCoordinate, leftRoomsLines);
            if (!topOffset) {
                topOffset = tableLines[1].getBoundingClientRect().top;
            }
        }

        return topOffset;
    }

    private getNearestTableLineToYOffset(yCoordinate, lines) {
        let topOffset = null;
        lines.some((line) => {
            let lineTopOffset = line.getBoundingClientRect().top;
            if (yCoordinate >= lineTopOffset && yCoordinate <= (lineTopOffset + styleConfigs[this.currentSizeConfigNumber].tableCellHeight)) {
                topOffset = lineTopOffset;
                return true;
            } else {
                return false;
            }
        });
    }

    private getPackageLeftOffset(startDate) {
        let tableStartDate = ChessBoardManager.getTableStartDate();
        let packageDateOffset = startDate.diff(tableStartDate, 'days') * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;

        return packageDateOffset + this.getPackageToMiddayOffset();
    }

    public static getMomentDate(dateString) {
        return moment(dateString, "DD.MM.YYYY");
    }

    private addListeners(identifier) {
        let jQueryObj = $(identifier);
        let self = this;

        this.addDraggable(jQueryObj);
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
            $element.find('.divide-package-button').click((event) => {
                self.canMoveAccommodation = false;
                let scissorIcon = event.target;
                if (intervalData.viewPackage) {
                    scissorIcon.onclick = function () {
                        self.updatePackagesData();
                    };
                    let accommodationWidth = parseInt(element.style.width, 10);
                    let tableCellWidth = styleConfigs[self.currentSizeConfigNumber].tableCellWidth;
                    if (accommodationWidth == tableCellWidth * 2) {
                        $('.divide-package-button').tooltip('hide');
                        self.divide(element, accommodationWidth / 2);
                    } else {
                        let packageLeftCoordinate = element.getBoundingClientRect().left;
                        let line: HTMLElement = document.createElement('div');
                        line.classList.add('dividing-line');

                        let accommodationElementWidth = parseInt(getComputedStyle(element).width, 10);
                        let isAccommodationAbroadTable = (accommodationElementWidth % tableCellWidth) != 0
                            && ((accommodationElementWidth + 1) % tableCellWidth) != 0;
                        let packageToMiddayOffset = self.getPackageToMiddayOffset();
                        let defaultLeftValue = isAccommodationAbroadTable
                            ? tableCellWidth + packageToMiddayOffset
                            : tableCellWidth;
                        line.style.left = defaultLeftValue + 'px';
                        element.appendChild(line);

                        element.onmousemove = function (event) {
                            let offset = event.clientX - packageLeftCoordinate;
                            let griddedOffset;
                            if (isAccommodationAbroadTable) {
                                griddedOffset = Math.floor(Math.abs(offset) / tableCellWidth)
                                    * tableCellWidth
                                    + packageToMiddayOffset;
                            } else {
                                griddedOffset = Math.floor(Math.abs(offset + packageToMiddayOffset)
                                        / tableCellWidth) * tableCellWidth;
                            }

                            if (griddedOffset == 0) {
                                griddedOffset += defaultLeftValue;
                            } else if (griddedOffset == accommodationWidth) {
                                griddedOffset -= tableCellWidth;
                            }

                            line.style.left = griddedOffset + 'px';
                            element.onclick = function () {
                                element.onmousemove = null;
                                element.removeChild(line);
                                self.divide(this, griddedOffset);
                            }
                        };
                    }
                }
            });
        });
    }

    private divide(packageElement, firstAccommodationWidth) {
        this.canMoveAccommodation = true;
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
                    grid: [styleConfigs[self.currentSizeConfigNumber].tableCellWidth, 1],
                    scroll: true,
                    drag: function (event, ui) {
                        if (!self.isIntervalAvailable(intervalData, isDivide) || !self.canMoveAccommodation) {
                            ui.position.left = ui.originalPosition.left;
                            ui.position.top = ui.originalPosition.top;
                        } else {
                            ui.position.top = self.getGriddedHeightValue(ui.position.top + styleConfigs[self.currentSizeConfigNumber].tableCellHeight / 2);
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
                                self.actionManager.callUpdatePackageModal($(this), intervalData, changedSide, isDivide);
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
            && !this.isAbroadTable(packageElement, packageOffset)
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
    private isAbroadTable(packageElement, packageOffset) {
        let lastDateElementLeftOffset = parseInt($('.roomDates:eq(0)').children().children().last().offset().left, 10)
            + styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
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
        let packageData = this.getPackageData($packageElement);
        let intervalsData = this.dataManager.getAccommodations();
        return Object.getOwnPropertyNames(intervalsData).some(function (intervalId) {
            let intervalData = intervalsData[intervalId];
            return !(intervalData.id === packageData.id)
                && intervalData.accommodation === packageData.accommodation
                && ChessBoardManager.getMomentDate(intervalData.begin).isBefore(moment(packageData.end, "DD.MM.YYYY"))
                && ChessBoardManager.getMomentDate(intervalData.end).isAfter(moment(packageData.begin, "DD.MM.YYYY"));
        });
    }

    private getGriddedHeightValue(height) {
        //1px - border
        let packageElementHeight = styleConfigs[this.currentSizeConfigNumber].tableCellHeight + 1;

        return Math.floor(height / packageElementHeight) * packageElementHeight - 1;
    }

    private isAbroadLeftTableSide(intervalMomentBegin) {
        return intervalMomentBegin.isBefore(this.tableStartDate);
    }

    private isAbroadRightTableSide(intervalMomentEnd) {
        return intervalMomentEnd.isAfter(this.tableEndDate);
    }

    /**
     * Getting the line, containing first letters of sides in which enable widening (e - east, w - west)
     * @param intervalData
     * @returns {string}
     */
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

    /**
     *
     * @param $element
     * @param intervalData
     * @returns {any}
     */
    private addResizable($element, intervalData) {
        let elementStartBackground;
        let self = this;

        let resizableHandlesValue = this.getResizableHandlesValue(intervalData);
        if (intervalData.updateAccommodation && resizableHandlesValue) {
            $element.resizable({
                aspectRatio: false,
                handles: resizableHandlesValue,
                grid: [styleConfigs[self.currentSizeConfigNumber].tableCellWidth, 1],
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
                            self.actionManager.callUpdatePackageModal($(this), intervalData, changedSide);
                        }
                    }
                }
            });
        }

        return $element;
    }

    /**
     * Получение данных о брони на основании данных о текущем положении элемента, отображающего бронь.
     * @param $packageElement
     * @returns {{id, accommodation: any, roomType: string, begin: string, end: string, payer: any}}
     */
    public getPackageData($packageElement) {
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

        let startDateLeftOffset = packageOffset.left - this.getPackageToMiddayOffset();
        let startDate = this.getDateStringByLeftOffset(dateElements, startDateLeftOffset);

        let endDateLeftOffset = packageOffset.left + parseInt($packageElement.get(0).style.width, 10) - this.getPackageToMiddayOffset();
        let endDate = this.getDateStringByLeftOffset(dateElements, endDateLeftOffset);
        let paidStatus;
        if ($packageElement.hasClass('warning')) {
            paidStatus = 'warning';
        } else if ($packageElement.hasClass('success')) {
            paidStatus = 'success';
        } else {
            paidStatus = 'danger';
        }
        return {
            id: $packageElement.get(0).id,
            accommodation: accommodationId,
            roomType: roomTypeId,
            begin: startDate,
            end: endDate,
            payer: description,
            paidStatus: paidStatus
        };
    }

    private static saveOffsetCompare(firstOffset, secondOffset) {
        let firstIntOffset = parseInt(firstOffset, 10);
        let secondIntOffset = parseInt(secondOffset, 10);
        return (firstIntOffset === secondIntOffset)
            || (firstIntOffset === secondIntOffset + 1)
            || (firstIntOffset === secondIntOffset - 1);
    }

    private getDateStringByLeftOffset(dateElements, leftOffset) {
        let dateElement = dateElements.filter(function () {
            return ChessBoardManager.saveOffsetCompare($(this).offset().left, leftOffset);
        });
        let dateNumber = dateElements.index(dateElement);
        let momentDate = moment((<HTMLInputElement>document.getElementById('accommodation-report-begin')).value, "DD.MM.YYYY")
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

        let $popoverElements = $('.no-accommodation-date.achtung');

        $popoverElements.unbind('shown.bs.popover');
        $popoverElements.on('shown.bs.popover', function () {
            let lastPackage = $('.package').last();
            if (lastPackage.attr('unplaced')) {
                lastPackage.remove();
            }

            let openedPopovers = $('.popover');
            openedPopovers.not(':last').remove();

            let roomTypeId = this.parentNode.parentNode.parentNode.parentNode.id;
            let currentDate = moment(this.getAttribute('data-date'), "DD.MM.YYYY");
            let templatePackageElement = self.getTemplateElement();
            let packageElementsContainer = document.createElement('div');

            let packagesByCurrentDate = self.dataManager.getNoAccommodationPackagesByDate(currentDate, roomTypeId);

            let isDragged = false;
            let relocatablePackage;
            let relocatablePackageData;

            let $wrapper = $('#calendarWrapper');
            let wrapperTopOffset = parseInt($wrapper.offset().top, 10);
            let $popover = openedPopovers.last();

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
                        if (self.isIntervalAvailable(packageData)) {
                            relocatablePackage = this;
                            $wrapper.append(this);
                            this.style.position = 'absolute';
                            this.setAttribute('unplaced', true);
                            relocatablePackageData = self.dataManager.getNoAccommodationIntervalById(this.id);
                            let intervalStartDate = ChessBoardManager.getMomentDate(relocatablePackageData.begin);
                            this.style.left = self.getPackageLeftOffset(intervalStartDate) + 'px';
                            this.style.top = self.getNearestTableLineTopOffset(event.pageY - document.body.scrollTop)
                                + document.body.scrollTop - wrapperTopOffset + 'px';
                            if (!self.isPackageLocationCorrect(relocatablePackage)) {
                                relocatablePackage.classList.add('red-package');
                            }
                            $popover.popover('hide');
                        }
                        document.body.onmouseup = function () {
                            document.body.onmouseup = null;
                            if (!isDragged && relocatablePackage) {
                                if (self.isPackageLocationCorrect(relocatablePackage)) {
                                    self.actionManager.callUpdatePackageModal($(relocatablePackage), relocatablePackageData);
                                }
                            }
                        };
                    });
                }
            });

            //Корректируем смещение по ширине
            let currentPopover = $popover.get(0);
            let popoverOffset = currentPopover.offsetWidth - ChessBoardManager.POPOVER_MIN_WIDTH;
            if (popoverOffset !== 0) {
                currentPopover.style.left = (parseInt(currentPopover.style.left, 10) - popoverOffset / 2) + 'px';
            }
        });
    }

    private isIntervalAvailable(packageData, isDivide = false) {
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

            if (this.getIntervalWidth(packageData) > this.getTableWidth()) {
                ActionManager.callIntervalToLargeModal(packageData.packageId);
                event.preventDefault();
                return false;
            }
        }

        return true;
    }

    private getIntervalWidth(intervalData) {
        let packageStartDate = ChessBoardManager.getMomentDate(intervalData.begin);
        let packageEndDate = ChessBoardManager.getMomentDate(intervalData.end);

        let packageCellCount = packageEndDate.diff(packageStartDate, 'days');

        return packageCellCount * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
    }

    private getTableWidth() {
        let styles = getComputedStyle(document.getElementById('accommodation-chessBoard-content'));

        return parseInt(styles.width, 10) - styleConfigs[this.currentSizeConfigNumber].headerWidth;
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
                    let dateLeftRoomsCount = leftRoomCounts[roomTypeId][i];
                    let backgroundColor = 'yellowgreen';
                    if (dateLeftRoomsCount == 0) {
                        backgroundColor = 'rgba(243, 156, 18, 0.66)'
                    } else if (dateLeftRoomsCount < 0) {
                        backgroundColor = 'rgba(221, 75, 57, 0.6)';
                    }
                    dateElements[i].children[0].style.backgroundColor = backgroundColor;
                    dateElements[i].children[0].innerHTML = dateLeftRoomsCount;
                    dateElements[i].setAttribute('data-toggle', "tooltip");

                    let toolTipTitle = Translator.trans('chessboard_manager.left_rooms_count.tooltip_title', {'count': dateLeftRoomsCount});
                    dateElements[i].setAttribute('data-original-title', toolTipTitle);
                    dateElements[i].setAttribute('data-placement', "bottom");
                    dateElements[i].setAttribute('data-container', 'body');
                }
            }
        })
    }

    public updatePackagesData() {
        this.canMoveAccommodation = true;
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
        removeButton.setAttribute('data-container', 'body');
        removeButton.classList.add('remove-package-button');
        removeButton.innerHTML = '<i class="fa fa-times" aria-hidden="true"></i>';

        return removeButton;
    }

    private static getTemplateDivideButton() {
        let divideButton = document.createElement('button');
        divideButton.setAttribute('type', 'button');
        divideButton.setAttribute('title', Translator.trans('chessboard_manager.divide_button.popup'));
        divideButton.setAttribute('data-toggle', 'tooltip');
        divideButton.setAttribute('data-container', 'body');
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