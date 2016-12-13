///<reference path="DataManager.ts"/>
declare var moment;
declare var $;
declare var mbh;

class ChessBoardManager {

    private static DATE_ELEMENT_WIDTH = 47;
    private static DATE_ELEMENT_HEIGHT = 40;
    private static PACKAGE_ELEMENT_HEIGHT = 41;
    private static PACKAGE_TO_MIDDAY_OFFSET = 20;
    private static POPOVER_MIN_WIDTH = 250;
    private static LEFT_BAR_WIDTH = 200;
    private static SCROLL_BAR_WIDTH = 16;

    public dataManager;
    public actionManager;

    public static deletePackageElement(packageId) {
        var packageElement = document.getElementById(packageId);
        if (packageElement) {
            packageElement.parentElement.removeChild(packageElement);
        }
    }

    constructor(packagesData, leftRoomsData, noAccommodationCounts) {
        this.dataManager = new DataManager(packagesData, leftRoomsData, noAccommodationCounts, this);
        this.actionManager = new ActionManager(this.dataManager);
        this.updateNoAccommodationPackageCounts();
    }

    public hangHandlers() {
        let wrapper = $('#calendarWrapper');
        let self = this;
        let chessBoardContentBlock = document.getElementById('accommodation-chessBoard-content');

        ChessBoardManager.setContentWidth(chessBoardContentBlock);

        let templatePackageElement = ChessBoardManager.getTemplateElement();
        this.addPackages();
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
        });

        $('#entity-delete-confirmation').on('hidden.bs.modal', function () {
            self.updateTable();
            $('#entity-delete-button').unbind('click');
        });

        document.getElementById('packageModalConfirmButton').onclick = function () {
            var modal = $('#packageModal');
            var packageId = modal.find('input.modalPackageId').val();
            var data = $('#concise_package_update').serialize();
            let packageData = ChessBoardManager.getPackageData($('#' + packageId));
            self.dataManager.updateLocalPackageData(packageData);
            self.dataManager.updatePackageRequest(packageId, data);
        };
        let $reportFilter = $('#accommodation-report-filter');

        $('.daterangepicker-input').daterangepicker(mbh.datarangepicker.options).on('apply.daterangepicker', function (ev, picker) {
            mbh.datarangepicker.on($reportFilter.find('.begin-datepicker.mbh-daterangepicker'), $reportFilter.find('.end-datepicker.mbh-daterangepicker'), picker);
        });

        //Удаляем второй инпут дейтпикера
        $('.daterangepicker-input.form-control.input-sm').eq(1).remove();

        let rangePicker = $reportFilter.find('.daterangepicker-input').data('daterangepicker');
        rangePicker.setStartDate(ChessBoardManager.getTableStartDate());
        rangePicker.setEndDate(ChessBoardManager.getTableEndDate());

        $reportFilter.change(function () {
            $reportFilter.submit();
        });

        //Фиксирование верхнего и левого блоков таблицы
        chessBoardContentBlock.onscroll = function () {
            ChessBoardManager.onContentTableScroll(chessBoardContentBlock);
        };

        //Создание брони
        var dateElements = $('.date, .leftRooms');
        dateElements.mousedown(function (event) {
            var startXPosition = event.pageX;
            var startLeftScroll = chessBoardContentBlock.scrollLeft;
            var newPackage = templatePackageElement.cloneNode(false);
            var dateJqueryObject = $(this.parentNode);
            var currentRoomDateElements = dateJqueryObject.parent().children();
            var startDateNumber = currentRoomDateElements.index(dateJqueryObject);
            var tableStartDate = ChessBoardManager.getTableStartDate();
            var tableEndDate = ChessBoardManager.getTableEndDate();
            var startDate = moment(tableStartDate).add(startDateNumber, 'day');
            newPackage = ChessBoardManager.setPackageOffset(newPackage, startDate, dateJqueryObject.parent().parent(), wrapper);
            newPackage.id = 'newPackage' + packages.length;
            newPackage.style.width = this.DATE_ELEMENT_WIDTH + 'px';
            var newPackageStartXOffset = parseInt(newPackage.style.left, 10);
            document.onmousemove = function (event) {
                var scrollOffset = chessBoardContentBlock.scrollLeft - startLeftScroll;
                var mouseXOffset = startXPosition - event.pageX;
                var isLeftMouseShift = mouseXOffset > 0;
                var packageLengthRestriction = ChessBoardManager.getPackageLengthRestriction(startDate, isLeftMouseShift, tableStartDate, tableEndDate);
                var griddedOffset = self.getGriddedOffset(mouseXOffset, scrollOffset, packageLengthRestriction);
                var leftMouseOffset = isLeftMouseShift ? griddedOffset : 0;
                var packageWidth = griddedOffset;
                newPackage.style.backgroundColor = !self.isPackageLocationCorrect(newPackage) ? 'rgba(232, 34, 34, 0.6' : 'rgba(79, 230, 106, 0.6)';
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

    private static getTableStartDate() {
        return moment(document.getElementById('accommodation-report-begin').value, "DD.MM.YYYY");
    }

    private static getTableEndDate() {
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
        let self = this;
        let packageData = ChessBoardManager.getPackageData($(packageElement));
        let $searchPackageForm = $('#package-search-form');

        $searchPackageForm.find('#s_roomType').val(packageData.roomType);
        $searchPackageForm.find('#s_begin').val(packageData.begin);
        $searchPackageForm.find('#s_end').val(packageData.end);
        $searchPackageForm.find('#s_range').val('0');

        let searchData = $searchPackageForm.serialize();
        this.dataManager.getPackageOptionsRequest(searchData, packageData);
    }

    private getGriddedOffset(mouseXOffset, scrollOffset, packageLengthRestriction) {
        'use strict';
        var griddedOffset = Math.ceil((Math.abs(mouseXOffset) + scrollOffset) / ChessBoardManager.DATE_ELEMENT_WIDTH) * ChessBoardManager.DATE_ELEMENT_WIDTH;

        griddedOffset = griddedOffset > packageLengthRestriction ? packageLengthRestriction : griddedOffset;
        return griddedOffset;
    }

    private static onContentTableScroll(chessBoardContentBlock) {
        'use strict';
        var types = document.getElementById('roomTypeColumn');
        types.style.left = chessBoardContentBlock.scrollLeft + 'px';

        var monthsAndDates = document.getElementById('months-and-dates');
        monthsAndDates.style.top = chessBoardContentBlock.scrollTop + 'px';

        var headerTitle = document.getElementById('header-title');
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

    public addPackages() {
        var wrapper = $('#calendarWrapper');
        var templatePackageElement = ChessBoardManager.getTemplateElement();
        var packages = document.createElement('div');
        //iterate packages
        this.dataManager.getPackages().forEach(function (item) {
            if (!ChessBoardManager.isPackageWithoutAccommodation(item)) {
                var packageDiv = ChessBoardManager.createPackageElementWithOffset(templatePackageElement, item, wrapper);
                packages.appendChild(packageDiv);
            }
        });
        wrapper.append(packages);
        this.addListeners(packages.children);
        $('.roomDates').droppable({
            accept: '.package'
        });
    }


    private static getTemplateElement() {
        var templateDiv = document.createElement('div');
        templateDiv.style = 'z-index: 100; position: absolute;';
        templateDiv.style.height = ChessBoardManager.PACKAGE_ELEMENT_HEIGHT + 'px';
        templateDiv.classList.add('package');

        var removeButton = document.createElement('button');
        removeButton.setAttribute('type', 'button');
        removeButton.setAttribute('title', 'Удалить');
        removeButton.setAttribute('data-toggle', 'tooltip');
        removeButton.classList.add('remove-package-button');
        removeButton.innerHTML = '<i class="fa fa-times" aria-hidden="true"></i>';
        templateDiv.appendChild(removeButton);

        var divideButton = document.createElement('button');
        divideButton.setAttribute('type', 'button');
        divideButton.setAttribute('title', 'Переселить');
        divideButton.setAttribute('data-toggle', 'tooltip');
        divideButton.classList.add('divide-package-button');
        divideButton.innerHTML = '<i class="fa fa-scissors" aria-hidden="true"></i>';
        templateDiv.appendChild(divideButton);

        return templateDiv;
    }

    public static createPackageElement(packageItem, templatePackageElement = null, hasButtons = true) {
        if (!templatePackageElement) {
            templatePackageElement = ChessBoardManager.getTemplateElement();
        }

        var packageStartDate = ChessBoardManager.getMomentDate(packageItem.begin.date);
        var packageEndDate = ChessBoardManager.getMomentDate(packageItem.end.date);

        var packageCellCount = packageEndDate.diff(packageStartDate, 'days');
        var packageWidth = packageCellCount * ChessBoardManager.DATE_ELEMENT_WIDTH;

        var packageDiv = templatePackageElement.cloneNode(hasButtons);
        packageDiv.style.width = packageWidth + 'px';
        packageDiv.id = packageItem.id;
        var description = document.createElement('div');
        description.classList.add('package-description');
        description.innerText = packageItem.payer ? packageItem.payer.substr(0, packageCellCount * 5 - 5) : '';
        packageDiv.appendChild(description);
        packageDiv.classList.add(packageItem.paidStatus);

        return packageDiv;
    }

    private static createPackageElementWithOffset(templatePackageElement, packageItem, wrapper) {
        let packageDiv = ChessBoardManager.createPackageElement(packageItem, templatePackageElement);
        var packageStartDate = ChessBoardManager.getMomentDate(packageItem.begin.date);
        var roomDatesListElement = $('#' + packageItem.accommodation);

        return ChessBoardManager.setPackageOffset(packageDiv, packageStartDate, roomDatesListElement, wrapper);
    }

    private static setPackageOffset(packageElement, startDate, roomLineElement, wrapper) {
        'use strict';
        var wrapperOffset = wrapper.offset();
        var roomLineOffset = roomLineElement.offset();
        packageElement.style.left = ChessBoardManager.getPackageLeftOffset(startDate) + 'px';
        packageElement.style.top = roomLineOffset.top - wrapperOffset.top + 'px';

        return packageElement;
    }

    private static getNearestTableLineTopOffset(yCoordinate) {
        let topOffset = null;
        var tableLines = [].slice.call(document.getElementsByClassName('roomDates'));
        tableLines.some(function (line) {
            let lineTopOffset = line.getBoundingClientRect().top;
            if (yCoordinate >= lineTopOffset && yCoordinate <= (lineTopOffset + ChessBoardManager.DATE_ELEMENT_HEIGHT)) {
                topOffset = lineTopOffset;
                return true;
            } else {
                return false;
            }
        });

        return topOffset;
    }

    private static getPackageLeftOffset(startDate) {
        let tableStartDate = this.getTableStartDate();
        let packageDateOffset = startDate.diff(tableStartDate, 'days') * ChessBoardManager.DATE_ELEMENT_WIDTH;

        return packageDateOffset + ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET;
    }

    private static getMomentDate(dateString) {
        return moment(dateString, "YYYY-MM-DD");
    }

    private addListeners(identifier) {
        var jQueryObj = $(identifier);
        let self = this;

        this.addDraggable(jQueryObj);
        this.addResizable(jQueryObj);

        jQueryObj.dblclick(function () {
            self.actionManager.callPackageInfoModal(this.id);
        }).find('.remove-package-button').click(function () {
            let packageId = this.parentNode.id;
            self.actionManager.callRemoveConfirmationModal(packageId);
        }).parent().find('.divide-package-button').click(function () {
            let packageElement = this.parentNode;
            let packageLeftCoordinate = packageElement.getBoundingClientRect().left;
            let line = document.createElement('div');
            line.style = 'position:absolute; border: 2px dashed red; height: 41px; z-index = 250;top: 0';
            packageElement.appendChild(line);
            packageElement.onmousemove = function (event) {
                let offset = event.x - packageLeftCoordinate;
                let griddedOffset = Math.floor(Math.abs(offset) / ChessBoardManager.DATE_ELEMENT_WIDTH) * ChessBoardManager.DATE_ELEMENT_WIDTH;
                line.style.left = griddedOffset + 'px';
                packageElement.onclick = function () {
                    packageElement.onmousemove = null;
                    packageElement.removeChild(line);
                    self.divide(packageElement, griddedOffset);
                }
            };
        });
    }

    private divide(packageElement, firstAccommodationWidth) {
        var packageWidth = parseInt(packageElement.style.width, 10);

        if (firstAccommodationWidth != 0 && firstAccommodationWidth != packageWidth) {
            var firstAccommodation = packageElement.cloneNode(true);
            firstAccommodation.style.width = firstAccommodationWidth + 'px';

            var secondAccommodation = packageElement.cloneNode(true);
            secondAccommodation = this.addDraggable($(secondAccommodation)).draggable({axis: "y"}).get(0);
            secondAccommodation.style.width = packageWidth - firstAccommodationWidth + 'px';
            secondAccommodation.style.left = parseInt(packageElement.style.left, 10) + firstAccommodationWidth + 'px';

            packageElement.parentNode.appendChild(firstAccommodation);
            packageElement.parentNode.appendChild(secondAccommodation);
            ChessBoardManager.deletePackageElement(packageElement.id);
        }
    }

    private isDraggableRevert($packageElement, isValidDrop) {
        return !(isValidDrop && this.isPackageLocationCorrect($packageElement.get(0)));
    }

    private addDraggable(jQueryObj) {
        let self = this;
        jQueryObj.draggable({
            containment: '#calendarWrapper',
            revert: function (is_valid_drop) {
                if (self.isDraggableRevert(this, is_valid_drop)) {
                    this.css('background-color', this.css('background-color'));
                    self.updatePackagesData();
                    return true;
                } else {
                    ActionManager.callUpdatePackageModal(this);
                    return false;
                }
            },
            start: function () {
                this.style.zIndex = 101;
            },
            scroll: true,
            drag: function (event, ui) {
                ui.position.left = self.getGriddedWidthValue(ui.position.left);
                //1 - бордер
                ui.position.top = self.getGriddedHeightValue(ui.position.top);
                if (!self.isPackageLocationCorrect(this)) {
                    this.classList.add('red-package');
                } else {
                    this.classList.remove('red-package');
                }
            },
            stop: function () {
                this.style.zIndex = 100;
            },
        });

        return jQueryObj;
    }

    private isPackageLocationCorrect(packageElement) {
        var $packageElement = $(packageElement);
        var packageOffset = $packageElement.offset();

        return (this.isOnRoomDatesLine(packageOffset) || this.isOnLeftRoomsLine(packageOffset))
            && !ChessBoardManager.isAbroadTable(packageElement, packageOffset)
            && !this.isPackageOverlapped($packageElement);
    }

    /**
     * Проверяет не выходит ли бронь за правую границу таблицы
     *
     * @param packageElement
     * @param packageOffset
     * @returns {boolean}
     */
    private static isAbroadTable(packageElement, packageOffset) {
        var lastDateElementLeftOffset = $('.roomDates:eq(0)').children().children().last().offset().left + ChessBoardManager.DATE_ELEMENT_WIDTH;
        var packageEndLeftOffset = packageOffset.left + parseInt(packageElement.style.width, 10);

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
            return packageOffset.top === $(element).offset().top;
        });
    }

    /**
     * Проверяет, пересекется ли период размещения брони с другими бронями, имеющими такой же тип размещения
     *
     * @param $packageElement
     * @returns {boolean}
     */
    private isPackageOverlapped($packageElement) {
        var packageData = ChessBoardManager.getPackageData($packageElement);

        return this.dataManager.getPackages().some(function (element) {
            return !(element.id === packageData.id)
                && element.accommodation === packageData.accommodation
                && ChessBoardManager.getMomentDate(element.begin.date).isBefore(moment(packageData.end, "DD.MM.YYYY"))
                && ChessBoardManager.getMomentDate(element.end.date).isAfter(moment(packageData.begin, "DD.MM.YYYY"));
        });
    }

    private getGriddedWidthValue(width) {
        return Math.floor(width / ChessBoardManager.DATE_ELEMENT_WIDTH) * ChessBoardManager.DATE_ELEMENT_WIDTH
            + ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET;
    }

    private getGriddedHeightValue(height) {
        //1 - бордер
        return Math.floor(height / ChessBoardManager.PACKAGE_ELEMENT_HEIGHT) * ChessBoardManager.PACKAGE_ELEMENT_HEIGHT - 1;
    }

    private addResizable(jQueryObj) {
        var elementStartBackground;
        let self = this;
        jQueryObj.resizable({
            aspectRatio: false,
            handles: 'e, w',
            grid: [ChessBoardManager.DATE_ELEMENT_WIDTH, 1],
            containment: '.rooms',
            start: function () {
                elementStartBackground = this.style.backgroundColor;
                this.style.zIndex = 101;
            },
            resize: function () {
                if (self.isPackageOverlapped($(this))) {
                    this.style.backgroundColor = 'rgba(232, 34, 34, 0.6';
                } else {
                    this.style.backgroundColor = elementStartBackground;
                }
            },
            stop: function (event, ui) {
                this.style.zIndex = 100;
                this.style.backgroundColor = elementStartBackground;
                if (!self.isPackageLocationCorrect(this)) {
                    ui.element.css(ui.originalPosition);
                    ui.element.css(ui.originalSize);
                } else {
                    ActionManager.callUpdatePackageModal($(this));
                }
            }
        });

        return jQueryObj;
    }

    public static getPackageData(packageElement) {
        let packageOffset = packageElement.offset();
        let roomLine = $('.roomDates, .leftRoomsLine').filter(function () {
            return $(this).offset().top === packageOffset.top;
        });
        let roomTypeId = roomLine.parent().get(0).id || roomLine.get(0).getAttribute('data-roomtypeid');
        let accommodationId = roomLine.children().get(0).id;
        if (accommodationId.substring(0, 16) === 'no_accommodation') {
            accommodationId = '';
        }
        let dateElements = roomLine.children().children();

        let startDateLeftOffset = packageOffset.left - ChessBoardManager.PACKAGE_TO_MIDDAY_OFFSET;
        let startDate = this.getDateStringByLeftOffset(dateElements, startDateLeftOffset);

        let endDateLeftOffset = packageOffset.left + parseInt(packageElement.get(0).style.width, 10) - this.PACKAGE_TO_MIDDAY_OFFSET;
        let endDate = this.getDateStringByLeftOffset(dateElements, endDateLeftOffset);
        return {
            id: packageElement.get(0).id,
            accommodation: accommodationId,
            roomType: roomTypeId,
            begin: startDate,
            end: endDate
        };
    }

    private static getDateStringByLeftOffset(dateElements, leftOffset) {
        var dateElement = ChessBoardManager.getDateObjectByLeftOffset(dateElements, leftOffset);

        return ChessBoardManager.getDateObjectByDateElement(dateElement, dateElements).format("DD.MM.YYYY");
    }

    private static getDateObjectByDateElement(dateElement, dateElements) {
        var dateNumber = dateElements.index(dateElement);
        return moment(document.getElementById('accommodation-report-begin').value, "DD.MM.YYYY")
            .add(dateNumber, 'day');
    }

    private static getDateObjectByLeftOffset(dateElements, leftOffset) {
        return dateElements.filter(function () {
            return $(this).offset().left === leftOffset;
        });
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
                dayElement.innerText = innerText;
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

        //Скрываем открытые popover-ы
        $popoverElements.on('show.bs.popover', function () {
            $('.popover').popover('hide');
        });

        $popoverElements.on('shown.bs.popover', function () {
            self.updatePackagesData();
            let roomTypeId = this.parentNode.parentNode.parentNode.parentNode.id;
            let currentDate = moment(this.getAttribute('data-date'), "DD.MM.YYYY");
            let templatePackageElement = ChessBoardManager.getTemplateElement();
            let packageElementsContainer = document.createElement('div');

            let packagesByCurrentDate = self.dataManager.getPackages().filter(function (packageData) {
                if (ChessBoardManager.isPackageWithoutAccommodation(packageData)
                    && packageData.roomTypeId === roomTypeId) {
                    let packageBeginDate = ChessBoardManager.getMomentDate(packageData.begin.date);
                    let packageEndDate = ChessBoardManager.getMomentDate(packageData.end.date);

                    let beginAndCurrentDiff = currentDate.diff(packageBeginDate, 'days');
                    let endAndCurrentDiff = packageEndDate.diff(currentDate, 'days');

                    return beginAndCurrentDiff >= 0 && endAndCurrentDiff > 0;
                }

                return false;
            });

            packagesByCurrentDate.forEach(function (packageData) {
                let packageElement = ChessBoardManager.createPackageElement(packageData, templatePackageElement, false);
                packageElement.style.position = '';
                packageElement.style.display = 'inline-block';

                let packageContainer = document.createElement('div');
                packageContainer.style.margin = '10px 0';
                packageContainer.appendChild(packageElement);
                packageElementsContainer.innerHTML += packageContainer.outerHTML;
            });

            let $wrapper = $('#calendarWrapper');
            let wrapperOffset = $wrapper.offset();
            let $popover = $('.popover').last();
            let popoverContent = $popover.find('.popover-content').get(0);
            popoverContent.innerHTML = packageElementsContainer.innerHTML;
            let isDragged = false;
            let relocatablePackage = null;
            self.addDraggable($popover.find('.package')).draggable({
                axis: "y",
                scroll: false,
                snap: 'calendarRow',
                start: function () {
                    this.style.zIndex = 101;
                    isDragged = true;
                },
            }).mousedown(function (event) {
                relocatablePackage = this;
                $wrapper.append(this);
                this.style.position = 'absolute';
                let packageData = self.dataManager.getPackageDataById(this.id);
                let packageStartDate = ChessBoardManager.getMomentDate(packageData.begin.date);
                this.style.left = ChessBoardManager.getPackageLeftOffset(packageStartDate) + 'px';
                this.style.top = ChessBoardManager.getNearestTableLineTopOffset(event.pageY - document.body.scrollTop)
                    + document.body.scrollTop - wrapperOffset.top + 'px';
                if (!self.isPackageLocationCorrect(relocatablePackage)) {
                    relocatablePackage.classList.add('red-package');
                }
                $popover.popover('hide');
            });
            document.body.onmouseup = function () {
                $popoverElements.popover();
                self.hangPopover();
                // document.body.onmouseup = null;
                if (!isDragged && relocatablePackage) {
                    if (self.isPackageLocationCorrect(relocatablePackage)) {
                        ActionManager.callUpdatePackageModal($(relocatablePackage));
                    }
                }
                $popoverElements.popover('hide');
            };
            //Корректируем смещение по ширине
            let currentPopover = $popover.get(0);
            let popoverOffset = currentPopover.offsetWidth - ChessBoardManager.POPOVER_MIN_WIDTH;
            if (popoverOffset !== 0) {
                currentPopover.style.left = (parseInt(currentPopover.style.left, 10) - popoverOffset / 2) + 'px';
            }
        });
    }

    private  updateLeftRoomCounts() {
        let self = this;
        $('.leftRoomsLine').each(function (index, item) {
            let roomTypeId = item.getAttribute('data-roomtypeid');
            let dateElements = item.children[0].children;
            for (let i = 0; i < dateElements.length; i++) {
                dateElements[i].children[0].innerText = self.dataManager.getLeftRoomCounts()[roomTypeId][i];
            }
        })
    }

    private static isPackageWithoutAccommodation(packageData) {
        return packageData.accommodation.startsWith("no_accommodation") || packageData.accommodation === "";
    }

    private updatePackagesData() {
        ChessBoardManager.deleteAllPackages();
        this.addPackages();
    }

    private static deleteAllPackages() {
        var packages = document.getElementsByClassName('package');
        while (packages[0]) {
            packages[0].parentNode.removeChild(packages[0]);
        }​
    }

}