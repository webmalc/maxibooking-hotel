///<reference path="DataManager.ts"/>
declare let moment;
declare let $;
declare let mbh;
declare let canCreatePackage;
declare let Translator;
declare let styleConfigs;
declare let currentStyleConfigNumber;
declare let colors;
declare let subtrahend;
declare let isMobileDevice;
declare let maxSliderSize;
declare let leftRoomsData;
declare let noAccommodationIntervals;
declare let noAccommodationCounts;

class ChessBoardManager {
    private static PACKAGE_FONT_SIZE_WIDTH = 8;
    private static POPOVER_MIN_WIDTH = 250;
    private static SCROLL_BAR_WIDTH = 16;
    private static LATE_CHECKOUT_EARLY_CHECKIN_COLOR = '#65619b';

    public dataManager;
    public actionManager;
    private templateRemoveButton;
    private templateDivideButton;
    private tableStartDate;
    private tableEndDate;
    private canMoveAccommodation = true;
    private currentSizeConfigNumber;
    private colors;
    private distanceBetweenAccommodationElements = 0;
    private distanceByHovering = 4;
    private arrowWidth;

    public static deletePackageElement(packageId) {
        let packageElement = document.getElementById(packageId);
        if (packageElement) {
            packageElement.parentElement.removeChild(packageElement);
        }
    }

    private getPackageToMiddayOffset() {
        let config = this.getStylesConfig();
        return Math.round(config.tableCellWidth / 2) + this.arrowWidth;
    }

    private getArrowWidth() {
        return Math.floor((this.getStylesConfig().tableCellHeight - subtrahend) / 4);
    }

    protected getStylesConfig() {
        return styleConfigs[this.currentSizeConfigNumber];
    }

    public hangHandlers() {
        this.loadTable();
        let $reportFilter = $('#accommodation-report-filter');
        $('select').select2();

        this.hangChangeNumberOfDaysButtonClick();

        $reportFilter.find('#filter-button').click(() => {
            this.loadTable();
        });

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

        $('#package-search-form').find('#s_adults').val(0);

        $('#packageModal, #package-edit-modal').on('hidden.bs.modal', () => {
            this.updateTable();
            $('#package-modal-continue-button').hide();
            $('#packageModalConfirmButton').show();
        });

        const $confirmationModal = $('#entity-delete-confirmation');
        $confirmationModal.on('hidden.bs.modal', () => {
            this.updateTable();
            $confirmationModal.find('#entity-delete-button').show();
            $confirmationModal.find('a.btn').remove();
            $('#entity-delete-button').unbind('click');
        });

        $('#modal_delete_package').on('hidden.bs.modal', () => {
            this.updateTable();
        });
        this.handleSizeSlider();
        this.onChangeScaleClick();
        document.getElementById('packageModalConfirmButton').onclick = () => {
            let data = ActionManager.getDataFromUpdateModal();
            let packageId = data.packageId;
            let accommodationId = data.accommodationId;
            let packageData = this.getPackageData($('#' + data.id));
            let isDivide = data.isDivide == 'true';
            if (isDivide) {
                this.dataManager.relocateAccommodationRequest(accommodationId, data);
                this.dataManager.updateLocalPackageData(data, isDivide);
            } else {
                this.dataManager.updatePackageRequest(packageId, data);
                this.dataManager.updateLocalPackageData(packageData, isDivide);
            }
            ActionManager.showLoadingIndicator();
        };

        this.onAddGuestClick();
        this.hangOnHideFieldButtonClick();
        this.updateChessboardDataWithoutActions();
    }

    loadTable() {
        const $chessboardTable = $('#chessboardTable');
        let filterData = ChessBoardManager.getFilterData($('#accommodation-report-filter'));
        $chessboardTable.html('<br/>' + mbh.loader.html);
        $.get(Routing.generate('chessboard_table') + '?' + filterData, (response) => {
            $chessboardTable.html(response);
            this.initChessboardTable();
        });
    }

    public static getTableStartDate() {
        return moment((<HTMLInputElement>document.getElementById('accommodation-report-begin')).value, "DD.MM.YYYY");
    }

    public static getTableEndDate() {
        return moment((<HTMLInputElement>document.getElementById('accommodation-report-end')).value, "DD.MM.YYYY");
    }

    protected handleSizeSlider() {
        let $slider = $('#ex1');
        $slider.slider({tooltip: 'hide', reverseed: true});
        $slider.on('slideStop', () => {
            let sliderValue = $('#ex1').slider('getValue');
            this.changeScale(sliderValue);
        });
    }

    private initParams() {
        this.currentSizeConfigNumber = currentStyleConfigNumber;
        this.colors = colors;
        this.arrowWidth = this.getArrowWidth();
        this.dataManager = new DataManager(packages, leftRoomsData, noAccommodationCounts, noAccommodationIntervals, this);
        this.actionManager = new ActionManager(this.dataManager);
        this.updateNoAccommodationPackageCounts();
        this.templateDivideButton = ChessBoardManager.getTemplateDivideButton();
        this.templateRemoveButton = ChessBoardManager.getTemplateRemoveButton();
        this.tableStartDate = ChessBoardManager.getTableStartDate();
        this.tableEndDate = ChessBoardManager.getTableEndDate();
        this.updateTable();
    }

    private initChessboardTable() {
        this.initParams();
        let chessBoardContentBlock = document.getElementById('accommodation-chessBoard-content');
        this.setContentWidth(chessBoardContentBlock);

        $('.sidebar-toggle').click(() => {
            setTimeout(() => {
                this.setContentWidth(chessBoardContentBlock);
            }, 1000)
        });

        $('.pagination-sm a').click((event) => {
            $('#pageNumber').val(event.target.getAttribute('data-value'));
            this.loadTable();
        });

        const $numberOfRoomsSelect = $('#nuber-of-rooms-select');
        $numberOfRoomsSelect.select2().on("select2:select", () => {
            window.location.href = Routing.generate('change_number_of_rooms', {numberOfRooms: $numberOfRoomsSelect.val()});
        });

        if (!isMobileDevice()) {
            //Фиксирование верхнего и левого блоков таблицы
            chessBoardContentBlock.onscroll = function () {
                ChessBoardManager.onContentTableScroll(chessBoardContentBlock);
            };
        }

        let templatePackageElement = ChessBoardManager.getTemplateElement();
        //Создание брони
        let dateElements = $('.date, .leftRooms');
        const $document = $(document);
        let wrapper = $('#calendarWrapper');
        if (canCreatePackage) {
            const eventName = isMobileDevice() ? 'contextmenu' : 'mousedown';
            dateElements.on(eventName, (event) => {
                chessBoardContentBlock.style.overflow = 'hidden';
                event.preventDefault();
                const startXPosition = event.pageX;
                const startLeftScroll = chessBoardContentBlock.scrollLeft;
                let newPackage = <HTMLElement>templatePackageElement.cloneNode(true);
                newPackage.classList.add('success');

                const dateJqueryObject = $(event.target.parentNode);
                const currentRoomDateElements = dateJqueryObject.parent().children();
                const startDateNumber = currentRoomDateElements.index(dateJqueryObject);
                const startDate = moment(this.tableStartDate).add(startDateNumber, 'day');
                newPackage = this.setPackageOffset(newPackage, startDate, dateJqueryObject.parent().parent(), wrapper);
                newPackage.id = 'newPackage' + packages.length;
                newPackage.style.zIndex = '999';
                newPackage.style.width = styleConfigs[this.currentSizeConfigNumber].tableCellWidth - (this.arrowWidth * 2) + 'px';
                const newPackageStartXOffset = parseInt(newPackage.style.left, 10);

                $(document).on('touchmove mousemove', (event) => {
                    const isMouseMoveEvent = event.type === 'mousemove';
                    const scrollOffset = chessBoardContentBlock.scrollLeft - startLeftScroll;
                    const mouseXOffset = startXPosition - (isMouseMoveEvent ? event.pageX : event.originalEvent.touches[0].pageX);
                    const isLeftMouseShift = mouseXOffset > 0;
                    const packageLengthRestriction = this.getPackageLengthRestriction(startDate, isLeftMouseShift, this.tableStartDate, this.tableEndDate);
                    const griddedOffset = this.getGriddedOffset(mouseXOffset, scrollOffset, packageLengthRestriction);
                    const leftMouseOffset = isLeftMouseShift ? griddedOffset : 0;
                    const packageWidth = griddedOffset - 2 * this.arrowWidth;

                    if (this.isPackageLocationCorrect(newPackage)) {
                        newPackage.classList.add('success');
                        newPackage.classList.remove('danger');
                        newPackage.style.backgroundColor = '';
                    } else {
                        newPackage.classList.remove('success');
                        newPackage.style.setProperty('background-color', this.colors['danger_add'], 'important');
                        newPackage.classList.add('danger');
                    }
                    newPackage.style.left = newPackageStartXOffset - leftMouseOffset + 'px';
                    newPackage.style.width = packageWidth + 'px';
                });
                $(document).on('mouseup touchend', () => {
                    chessBoardContentBlock.style.overflow = 'auto';
                    $document.unbind('mousemove  mouseup touchend');
                    if ((newPackage.style.width) && this.isPackageLocationCorrect(newPackage) && newPackage.id) {
                        const packageData = this.getPackageData($(newPackage));
                        this.saveNewPackage(packageData);
                    }
                    this.updateTable();
                });
                event.target.ondragstart = function () {
                    return false;
                };
                wrapper.append(newPackage);
            });
        }
    }

    private changeScale(sliderValue) {
        if (this.currentSizeConfigNumber !== sliderValue && sliderValue >= 0 && sliderValue <= maxSliderSize) {
            ChessBoardManager.setCookie('chessboardSizeNumber', sliderValue);
            this.currentSizeConfigNumber = sliderValue;
            window.location.reload();
        }
    }

    private onChangeScaleClick() {
        $('.reduce-scale-button, .increase-scale-button').on(ChessBoardManager.getClickEventType(), (event) => {
            const sliderValue = $('#ex1').slider('getValue');
            const buttonClassList = event.target.classList;
            const newSliderValue = buttonClassList.contains('increase-scale-button') ? (sliderValue + 1) : (sliderValue - 1);
            this.changeScale(newSliderValue);
        });
    }

    protected static setCookie(name, value, options = {}) {

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

    protected setContentWidth(chessBoardContentBlock) {
        let contentWidth = parseInt($('#months-and-dates').css('width'), 10)
            + styleConfigs[this.currentSizeConfigNumber].headerWidth + ChessBoardManager.SCROLL_BAR_WIDTH;

        if (parseInt($(chessBoardContentBlock).css('width'), 10) > contentWidth) {
            chessBoardContentBlock.style.width = contentWidth + 'px';
        } else {
            chessBoardContentBlock.style.width = 'auto';
        }
    }

    protected saveNewPackage(packageData) {
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
        const pager = (<HTMLInputElement>document.getElementById('pageNumber'));
        if (pager) {
            searchData += '&page=' + pager.value;
        }

        return searchData;
    }

    protected onAddGuestClick() {
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

    protected getGriddedOffset(mouseXOffset, scrollOffset, packageLengthRestriction) {
        'use strict';
        let griddedOffset = Math.ceil((Math.abs(mouseXOffset) + scrollOffset) / styleConfigs[this.currentSizeConfigNumber].tableCellWidth)
            * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;

        griddedOffset = griddedOffset > packageLengthRestriction ? packageLengthRestriction : griddedOffset;
        return griddedOffset;
    }

    protected static onContentTableScroll(chessBoardContentBlock) {
        'use strict';
        let types = document.getElementById('roomTypeColumn');
        types.style.left = chessBoardContentBlock.scrollLeft + 'px';

        let monthsAndDates = document.getElementById('months-and-dates');
        monthsAndDates.style.top = chessBoardContentBlock.scrollTop + 'px';

        let headerTitle = document.getElementById('header-title');
        headerTitle.style.top = chessBoardContentBlock.scrollTop + 'px';
        headerTitle.style.left = chessBoardContentBlock.scrollLeft + 'px';
    }

    protected getPackageLengthRestriction(startDate, isLeftMouseShift, tableStartDate, tableEndDate) {
        'use strict';
        if (isLeftMouseShift) {
            return startDate.diff(tableStartDate, 'days') * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
        }

        return tableEndDate.diff(startDate, 'days') * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
    }

    private hangChangeNumberOfDaysButtonClick() {
        const getNewDate = function (changeButton, $dateField) {
            const changeDaysFormat = parseInt(changeButton.getAttribute('data-number-of-days'), 10);
            const isAddition = changeButton.getAttribute('data-change-type') === 'add';
            const date = moment($dateField.val(), 'DD.MM.YYYY');

            return isAddition ? date.add(changeDaysFormat, 'days') : date.subtract(changeDaysFormat, 'days');
        };

        $('.change-days-button').on(ChessBoardManager.getClickEventType(), function () {
            const $rangePicker = $('.daterangepicker-input').data('daterangepicker');
            const $beginDateField = $('#accommodation-report-filter-begin');
            const $endDateField = $('#accommodation-report-filter-end');

            const beginDate = getNewDate(this, $beginDateField);
            const endDate = getNewDate(this, $endDateField);

            $beginDateField.val(beginDate.format('DD.MM.YYYY'));
            $endDateField.val(endDate.format('DD.MM.YYYY'));
            $rangePicker.setStartDate(beginDate.toDate());
            $rangePicker.setEndDate(endDate.toDate());
        });
    }

    public addAccommodationElements() {
        let wrapper = $('#calendarWrapper');
        let templatePackageElement = ChessBoardManager.getTemplateElement();
        let packages = document.createElement('div');

        let accommodationsData = this.dataManager.getAccommodations();
        let lastAddedElement = null;
        for (let accommodationId in accommodationsData) {
            if (accommodationsData.hasOwnProperty(accommodationId)) {
                let accommodationData = accommodationsData[accommodationId];
                if (accommodationData.accommodation) {
                    let packageDiv = this.createPackageElementWithOffset(templatePackageElement,
                        accommodationData, wrapper);
                    lastAddedElement = packageDiv;
                    packages.appendChild(packageDiv);
                }
            }
        }
        wrapper.append(packages);
        this.addListeners('.package');
        this.updateAccommodationsWithNeighbors();
        if (isMobileDevice()) {
            $('.package .package-action-buttons').show();
        }
    }

    protected static getTemplateElement() {
        let templateDiv: HTMLElement = document.createElement('div');
        templateDiv.style.position = 'absolute';
        templateDiv.classList.add('package');
        templateDiv.classList.add('package-with-left-arrow');
        templateDiv.classList.add('package-with-right-arrow');

        let buttonsDiv = document.createElement('div');
        buttonsDiv.classList.add('package-action-buttons');
        templateDiv.appendChild(buttonsDiv);

        return templateDiv;
    }

    public createPackageElement(packageItem, templatePackageElement = null, hasButtons = true, accommodationElementIndex = 100) {
        if (!templatePackageElement) {
            templatePackageElement = ChessBoardManager.getTemplateElement();
        }
        let config = styleConfigs[this.currentSizeConfigNumber];
        let packageStartDate = ChessBoardManager.getMomentDate(packageItem.begin);
        let packageEndDate = ChessBoardManager.getMomentDate(packageItem.end);
        let accommodationStartDate = ChessBoardManager.getMomentDate(packageItem.begin);
        let accommodationEndDate = ChessBoardManager.getMomentDate(packageItem.end);

        let packageCellCount = packageEndDate.diff(packageStartDate, 'days');
        let packageWidth = packageCellCount * config.tableCellWidth - this.distanceBetweenAccommodationElements;

        let packageDiv = templatePackageElement.cloneNode(true);
        packageDiv.id = packageItem.id;

        let packageName = (packageItem.payer) ? packageItem.payer : packageItem.number;
        let description = document.createElement('div');
        let descriptionText = packageName ? packageName.substr(0, packageCellCount * 5) : '';

        packageDiv.setAttribute('data-description', descriptionText);
        packageDiv.setAttribute('data-package-id', packageItem.packageId);
        description.innerHTML = descriptionText;
        description.classList.add('package-description');
        packageDiv.appendChild(description);
        description.style.width = Math.floor(descriptionText.length * ChessBoardManager.PACKAGE_FONT_SIZE_WIDTH) + 'px';
        packageDiv.classList.add(packageItem.paidStatus);

        description.setAttribute('data-toggle', 'popover');
        description.setAttribute('data-html', "true");
        description.setAttribute('data-container', "body");
        description.setAttribute('title', packageItem.number);
        description.setAttribute('data-placement', 'top');
        let descriptionPopoverContent = '<b>' + Translator.trans("chessboard_manager.package_tooltip.package_number") + '</b>:' + packageItem.number
            + (packageItem.payer ? '<br><b>' + Translator.trans("chessboard_manager.package_tooltip.payer") + ': </b>' + packageItem.payer : '')
            + '<br><b>' + Translator.trans("chessboard_manager.package_tooltip.price") + ': </b>' + packageItem.price
            + '<br><b>' + Translator.trans("chessboard_manager.package_tooltip.package_begin") + ': </b>' + packageItem.packageBegin
            + '<br><b>' + Translator.trans("chessboard_manager.package_tooltip.package_end") + ': </b>' + packageItem.packageEnd
            + '<br><b>' + Translator.trans("chessboard_manager.package_tooltip.is_checkin") + ': </b>' + Translator.trans(packageItem.isCheckIn ? 'package.yes' : 'package.no')
            + '<br><b>' + Translator.trans("chessboard_manager.package_tooltip.is_checkout") + ': </b>' + Translator.trans((packageItem.isCheckOut ? 'package.yes' : 'package.no'));
        description.setAttribute('data-content', descriptionPopoverContent);

        const $packageDiv = $(packageDiv);
        if (!isMobileDevice()) {
            packageDiv.onmousemove = function () {
                const isElementInPopoverWindow = packageDiv.parentNode.classList.contains('popover-package-container');
                if (!isElementInPopoverWindow) {
                    $packageDiv.find('.package-action-buttons').show();
                    let $descriptionElement = $(this).find('.package-description');
                    if ($descriptionElement.length > 0) {
                        let popoverId = $descriptionElement.attr('aria-describedby');
                        if (popoverId == null) {
                            $('.popover').popover('hide');
                            $descriptionElement.popover('show');
                        }
                    }
                }
            };

            packageDiv.onmouseleave = function () {
                $packageDiv.find('.package-action-buttons').hide();
                $packageDiv.find('.package-description').popover('hide');
            };
        }

        if (packageItem.position == 'middle' || packageItem.position == 'left') {
            packageDiv.classList.add('with-right-divider');
            packageDiv.classList.remove('package-with-right-arrow');
            let rightNeighbor = this.dataManager.getAccommodationNeighbors(packageItem.id)['right'];
            if (rightNeighbor) {
                if (rightNeighbor.position == 'middle' || rightNeighbor.position == 'right') {
                    packageWidth += this.distanceBetweenAccommodationElements;
                } else {
                    packageWidth -= this.arrowWidth;
                }
            }
        } else {
            packageWidth -= this.arrowWidth;
        }

        if (packageItem.position == 'middle' || packageItem.position == 'right') {
            packageDiv.classList.add('with-left-divider');
            packageDiv.classList.remove('package-with-left-arrow');
        } else {
            packageWidth -= this.arrowWidth;
        }
        packageDiv.style.width = packageWidth + 'px';
        if (this.isAbroadLeftTableSide(accommodationStartDate)) {
            packageDiv.classList.remove('package-with-left-arrow');
        }
        if (this.isAbroadRightTableSide(accommodationEndDate)) {
            packageDiv.classList.remove('package-with-right-arrow');
        }

        if (packageItem.isCheckOut) {
            packageDiv.classList.add('tile-coming-out');
        } else if (packageItem.isCheckIn) {
            packageDiv.classList.add('tile-coming');
        }

        if (hasButtons && !packageItem.isLocked) {
            if (packageItem.updateAccommodation
                && packageEndDate.diff(packageStartDate, 'days') > 1
                && !this.isAbroadRightTableSide(packageEndDate)
                && !isMobileDevice()) {
                $(packageDiv).find('.package-action-buttons').append(this.templateDivideButton.cloneNode(true));
            }
            if (packageItem.removePackage && (packageItem.position == 'full' || packageItem.position == 'right')) {
                $(packageDiv).find('.package-action-buttons').append(this.templateRemoveButton.cloneNode(true));
            }
        }
        packageDiv.style.zIndex = accommodationElementIndex;

        return packageDiv;
    }

    private createPackageElementWithOffset(templatePackageElement, packageItem, wrapper) {
        let differenceInDays = ChessBoardManager.getMomentDate(packageItem.begin).diff(this.tableStartDate, 'days');
        const accommodationElementIndex = 150 - differenceInDays;
        let packageDiv = this.createPackageElement(packageItem, templatePackageElement, true, accommodationElementIndex);
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
        packageElement.style.left = this.getPackageLeftOffset(startDate, packageElement) + 'px';
        packageElement.style.top = roomLineTopOffset - wrapperTopOffset + (subtrahend / 2) + 'px';

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

        const cellWidth = styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
        if ((elementWidth < cellWidth - this.arrowWidth) || (elementWidth < cellWidth && element.classList.contains('with-left-divider'))) {

            let divideButton = element.querySelector('.divide-package-button');
            if (divideButton) {
                divideButton.parentNode.removeChild(divideButton);
            }
        }

        return element;
    }

    protected updateAccommodationsWithNeighbors() {
        let $resizableElements = $('.ui-resizable-handle').parent();
        $resizableElements.each((index, accommodationElement) => {
            let accommodationId = accommodationElement.id;
            let accommodationNeighbors = this.dataManager.getAccommodationNeighbors(accommodationId);

            let hasRightNeighborResizable = false;
            let hasLeftNeighborResizable = false;
            let hasLeftResizable = false;
            let $accommodation = $(accommodationElement);
            const $rightResizable = $accommodation.find('.ui-resizable-e');
            const $leftResizable = $accommodation.find('.ui-resizable-w');

            if (accommodationNeighbors['left']) {
                let leftNeighborData = accommodationNeighbors['left'];
                let leftNeighbor = document.getElementById(leftNeighborData.id);
                if (accommodationElement.getElementsByClassName('ui-resizable-w').length > 0) {
                    hasLeftResizable = true;
                }
                if (leftNeighbor.getElementsByClassName('ui-resizable-e').length > 0) {
                    hasLeftNeighborResizable = true;
                }
            }

            if (accommodationNeighbors['right']) {
                let rightNeighborData = accommodationNeighbors['right'];
                let rightNeighbor = document.getElementById(rightNeighborData.id);
                if (rightNeighbor.getElementsByClassName('ui-resizable-w').length > 0) {
                    hasRightNeighborResizable = true;
                }
            }

            const hasEarlyCheckin = accommodationElement.classList.contains('early-checkin');
            if (hasLeftNeighborResizable && $accommodation.hasClass('package-with-left-arrow')) {
                accommodationElement.classList.remove('package-with-left-arrow');
                accommodationElement.classList.add('near-left-element');

                accommodationElement.style.width =
                    parseInt(accommodationElement.style.width, 10) + this.arrowWidth + 'px';
                accommodationElement.style.left =
                    parseInt(accommodationElement.style.left, 10) - this.arrowWidth + 'px';

                if (hasEarlyCheckin) {
                    this.setImportantStyle($accommodation.find('.ui-resizable-w'), 'background-color');
                }
            }

            const hasLateCheckout = accommodationElement.classList.contains('late-checkout');
            const $leftNeighbor = hasLeftNeighborResizable ? $('#' + accommodationNeighbors['left'].id) : null;
            const $rightNeighbor = hasRightNeighborResizable ? $('#' + accommodationNeighbors['right'].id) : null;
            const hasLeftNeighborLateCheckout = hasLeftNeighborResizable && $leftNeighbor.hasClass('late-checkout');
            const hasRightNeighborEarlyCheckin = hasRightNeighborResizable && $rightNeighbor.hasClass('early-checkin');

            if (hasRightNeighborResizable && $accommodation.hasClass('package-with-right-arrow')) {
                $rightResizable.hide();
                accommodationElement.classList.add('near-right-element');
                accommodationElement.classList.remove('package-with-right-arrow');
                let accommodationWidth = $accommodation.width() + this.arrowWidth;
                $accommodation.width(accommodationWidth);

                if (hasLateCheckout) {
                    this.setImportantStyle($accommodation.find('.ui-resizable-e'), 'background-color');
                    this.setImportantStyle($rightNeighbor.find('.ui-resizable-w'), 'background-color');
                }
            }

            if (hasRightNeighborResizable || hasLeftNeighborResizable) {
                let leftNeighborWidth;
                let rightNeighborWidth;
                let rightNeighborLeft;

                let isMoved = false;
                const $descriptionElement = $accommodation.find('.package-description');

                accommodationElement.onmousemove = (() => {
                    if (!isMoved) {
                        $accommodation.find('.package-action-buttons').show();
                        if ($descriptionElement.length > 0) {
                            let popoverId = $descriptionElement.attr('aria-describedby');
                            if (popoverId == null) {
                                $('.popover').popover('hide');
                                $descriptionElement.popover('show');
                            }
                        }

                        if (!hasEarlyCheckin) {
                            $leftResizable.css('background-color', '');
                        }

                        if (!$accommodation.hasClass('in-resize')) {
                            if (hasRightNeighborResizable) {
                                rightNeighborWidth = $rightNeighbor.width();
                                rightNeighborLeft = parseInt($rightNeighbor.css('left'), 10);
                                $rightResizable.show();
                                $rightNeighbor.width(rightNeighborWidth - this.distanceByHovering);
                                $rightNeighbor.css('left', rightNeighborLeft + this.distanceByHovering);
                                if (hasLateCheckout && !hasRightNeighborEarlyCheckin) {
                                    this.setImportantStyle($rightNeighbor.find('.ui-resizable-w'), 'background-color', '');
                                }
                            }

                            if (hasLeftNeighborResizable && hasLeftResizable) {
                                leftNeighborWidth = $leftNeighbor.width();
                                $('#' + accommodationNeighbors['left'].id).width(leftNeighborWidth - this.distanceByHovering);
                            }
                        }
                    }

                    isMoved = true;
                });

                accommodationElement.onmouseleave = (() => {
                    $descriptionElement.popover('hide');
                    $accommodation.find('.package-action-buttons').hide();
                    if (!$accommodation.hasClass('in-resize')) {
                        if (hasLateCheckout) {
                            this.setImportantStyle($rightNeighbor.find('.ui-resizable-w'), 'background-color');
                        }
                        if (hasLeftNeighborLateCheckout) {
                            this.setImportantStyle($leftResizable, 'background-color');
                        }
                        if (hasRightNeighborResizable) {
                            $rightNeighbor.width(rightNeighborWidth);
                            $rightNeighbor.css('left', rightNeighborLeft);
                            $rightResizable.hide();
                        }
                        if (hasLeftNeighborResizable && hasLeftResizable) {
                            $('#' + accommodationNeighbors['left'].id).width(leftNeighborWidth);
                        }
                    }

                    isMoved = false;
                });
            }
        });
    }

    private setImportantStyle($element, property, value = ChessBoardManager.LATE_CHECKOUT_EARLY_CHECKIN_COLOR) {
        $element.get(0).style.setProperty(property, value, 'important');
    }

    protected getNearestTableLineTopOffset(yCoordinate) {
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

        return topOffset;
    }

    private getPackageLeftOffset(startDate, element) {
        let tableStartDate = ChessBoardManager.getTableStartDate();
        let packageDateOffset = startDate.diff(tableStartDate, 'days') * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
        if ($(element).hasClass('with-left-divider')) {
            packageDateOffset -= this.arrowWidth;
        }

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
            let intervalData = self.dataManager.getAccommodationIntervalById(element.id);
            let $element = $(element);
            self.addResizable($element, intervalData);
            if (isMobileDevice()) {
                let touchTime;
                let isTouchEnd = false;
                $element.on('touchstart', () => {
                    if (isTouchEnd && touchTime && moment().diff(touchTime) < 500) {
                        if (intervalData.viewPackage) {
                            self.dataManager.getPackageDataRequest(intervalData.packageId);
                        }
                        touchTime = null;
                    } else {
                        touchTime = new moment();
                        isTouchEnd = false;
                    }
                });
                $element.on('touchend', () => {
                    if (touchTime) {
                        isTouchEnd = true;
                    }
                });
            } else {
                $element.dblclick(function () {
                    if (intervalData.viewPackage) {
                        self.dataManager.getPackageDataRequest(intervalData.packageId);
                    }
                });
            }

            $element.find('.remove-package-button').on('click touchstart', function () {
                self.actionManager.callRemoveConfirmationModal(intervalData.packageId);
            });
            $element.find('.divide-package-button').on('click', (event) => {
                self.canMoveAccommodation = false;
                let $scissorIcon = $(event.target);
                if (intervalData.viewPackage) {
                    $scissorIcon.on('click touchstart', function () {
                        self.updatePackagesData();
                    });
                    let accommodationWidth = parseInt(element.style.width, 10);
                    let tableCellWidth = styleConfigs[self.currentSizeConfigNumber].tableCellWidth;
                    if (accommodationWidth > tableCellWidth && accommodationWidth <= tableCellWidth * 2) {
                        $('.divide-package-button').tooltip('hide');
                        self.divide(element, accommodationWidth / 2);
                    } else {
                        let packageLeftCoordinate = element.getBoundingClientRect().left;
                        let line: HTMLElement = document.createElement('div');
                        line.classList.add('dividing-line');

                        const isAccommodationAbroadTable = self.isAbroadRightTableSide(ChessBoardManager.getMomentDate(intervalData.end))
                            || self.isAbroadLeftTableSide(ChessBoardManager.getMomentDate(intervalData.begin));

                        let packageToMiddayOffset = self.getPackageToMiddayOffset();
                        const hasLeftArrow = element.classList.contains('package-with-left-arrow');
                        let defaultLeftValue = isAccommodationAbroadTable
                            ? packageToMiddayOffset
                            : (hasLeftArrow ? tableCellWidth - self.arrowWidth : tableCellWidth);

                        line.style.left = defaultLeftValue + 'px';
                        element.appendChild(line);

                        $element.on('mousemove', function (event) {
                            let offset = event.clientX - packageLeftCoordinate;
                            let griddedOffset;
                            if (isAccommodationAbroadTable) {
                                griddedOffset = Math.floor(Math.abs(offset) / tableCellWidth)
                                    * tableCellWidth
                                    + packageToMiddayOffset;
                            } else {
                                griddedOffset = Math.round(Math.abs(offset) / tableCellWidth) * tableCellWidth
                                    - (hasLeftArrow ? self.arrowWidth : 0);
                            }

                            if (griddedOffset < defaultLeftValue * 1.5) {
                                griddedOffset = defaultLeftValue;
                            } else if (griddedOffset > accommodationWidth - tableCellWidth) {
                                griddedOffset = accommodationWidth - tableCellWidth + (hasLeftArrow ? self.arrowWidth : 0);
                            }

                            line.style.left = griddedOffset + 'px';
                            $element.off('click');
                            $element.on('click', function () {
                                $element.off('mousemove touchmove');
                                $('.dividing-line').remove();
                                self.divide(this, griddedOffset);
                            })
                        });
                    }
                }
            });
        });
    }

    private divide(packageElement, firstAccommodationWidth) {
        this.canMoveAccommodation = true;
        if (packageElement.parentNode) {
            let packageWidth = parseInt(packageElement.style.width, 10);
            $(packageElement).find('.divide-package-button, .remove-package-button, .ui-resizable-e, .ui-resizable-w, .right-inner-resizable-triangle').remove();
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
                secondAccommodation.classList.remove('package-with-left-arrow');
                firstAccommodation.classList.add('with-right-divider');
                firstAccommodation.classList.remove('package-with-right-arrow');

                packageElement.parentNode.appendChild(firstAccommodation);
                packageElement.parentNode.appendChild(secondAccommodation);
                ChessBoardManager.deletePackageElement(packageElement.id);
            }
        } else {
            this.updatePackagesData();
        }
    }

    protected static setDividedElementDescription(element, elementWidth) {
        let descriptionElement = element.querySelector('.package-description');
        let contentWidth = descriptionElement.innerHTML.length * 8;
        let descriptionWidth = contentWidth > elementWidth ? elementWidth : contentWidth;
        descriptionElement.style.width = descriptionWidth + 'px';

        return element;
    }

    protected isDraggableRevert(packageElement) {
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
            const elementStartZIndex = element.style.zIndex;
            if (axisValue != '') {
                $(element).draggable({
                    containment: '#calendarWrapper',
                    axis: axisValue,
                    grid: [styleConfigs[self.currentSizeConfigNumber].tableCellWidth, 1],
                    scroll: true,
                    drag: function (event, ui) {
                        element.style.zIndex = 200;
                        if (!self.isIntervalAvailable(intervalData, event, isDivide) || !self.canMoveAccommodation) {
                            ui.position.left = ui.originalPosition.left;
                            ui.position.top = ui.originalPosition.top;
                        } else {
                            ui.position.top = self.getGriddedHeightValue(ui.position.top + styleConfigs[self.currentSizeConfigNumber].tableCellHeight / 2) + subtrahend / 2 - 1;
                            if (!self.isPackageLocationCorrect(this)) {
                                this.classList.add('red-package');
                            } else {
                                this.classList.remove('red-package');
                            }
                        }
                    },
                    stop: function (event, ui) {
                        element.style.zIndex = elementStartZIndex;
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

    protected static getDraggableAxisValue(intervalData, isDivide) {
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

    protected isPackageLocationCorrect(packageElement) {
        let $packageElement = $(packageElement);
        let packageOffset = $packageElement.offset();

        return (this.isOnRoomDatesLine(packageOffset) || this.isOnLeftRoomsLine(packageOffset))
            && !this.isAbroadTable(packageElement, packageOffset)
            && !this.isPackageOverlapped($packageElement);
    }

    protected static isAccommodationOnFullPackage(intervalData) {
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
    protected isAbroadTable(packageElement, packageOffset) {
        let lastDateElementLeftOffset = parseInt($('.roomDates:eq(0)').children().children().last().offset().left, 10)
            + styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
        let packageEndLeftOffset = packageOffset.left + parseInt(packageElement.style.width, 10);

        return lastDateElementLeftOffset < packageEndLeftOffset;
    }

    /**
     * Проверяет находится ли бронь на одной из линий, укёазывающих размещение брони
     *
     * @param packageOffset
     * @returns {boolean}
     */
    protected isOnRoomDatesLine(packageOffset) {
        return this.isPackageOnSpecifiedLine('roomDates', packageOffset);
    }

    protected isOnLeftRoomsLine(packageOffset) {
        return this.isPackageOnSpecifiedLine('leftRoomsLine', packageOffset);
    }

    protected isPackageOnSpecifiedLine(lineClass, packageOffset) {
        let specifiedLine = document.getElementsByClassName(lineClass);
        return Array.prototype.some.call(specifiedLine, function (element) {
            return ChessBoardManager.isOffsetsEqual(packageOffset.top, $(element).offset().top + subtrahend / 2);
        });
    }

    /**
     * Проверяет, пересекется ли период размещения брони с другими бронями, имеющими такой же тип размещения
     *
     * @param $packageElement
     * @returns {boolean}
     */
    protected isPackageOverlapped($packageElement) {
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

    protected getGriddedHeightValue(height) {
        //1 - бордер
        let packageElementHeight = styleConfigs[this.currentSizeConfigNumber].tableCellHeight + 1;

        return Math.floor(height / packageElementHeight) * packageElementHeight;
    }

    protected isAbroadLeftTableSide(intervalMomentBegin) {
        return intervalMomentBegin.isBefore(this.tableStartDate);
    }

    protected isAbroadRightTableSide(intervalMomentEnd) {
        return intervalMomentEnd.isAfter(this.tableEndDate);
    }

    /**
     * Getting the line, containing first letters of sides in which enable widening (e - east, w - west)
     * @param intervalData
     * @returns {string}
     */
    protected getResizableHandlesValue(intervalData) {
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
     * @param $element
     * @param intervalData
     * @returns {any}
     */
    protected addResizable($element, intervalData) {
        let elementStartBackground;
        const elementStartZIndex = $element.css('z-index');
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
                    $element.addClass('in-resize');
                    $element.css('z-index', 999);
                    if (intervalData.isLocked) {
                        ui.position.left = ui.originalPosition.left;
                        ui.size.width = ui.originalSize.width;
                    } else {
                        if (self.isPackageOverlapped($element)) {
                            self.setImportantStyle($element, 'background-color', self.colors['danger_add']);
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
                        } else {
                            $element.removeClass('in-resize');
                            $element.css('z-index', elementStartZIndex);
                        }
                    }
                }
            });
        }

        if (intervalData.isLateCheckOut) {
            if (resizableHandlesValue.indexOf('e') > -1) {
                this.setImportantStyle($element.find('.ui-resizable-e'), 'border-left-color')
            }
            $element.addClass('late-checkout');
        }
        if (intervalData.isEarlyCheckIn) {
            if (resizableHandlesValue.indexOf('w') > -1) {
                this.setImportantStyle($element.find('.ui-resizable-w'), 'border-right-color');
            }
            $element.addClass('early-checkin');
        }

        return $element;
    }

    /**
     * Получение данных о брони на основании данных о текущем положении элемента, отображающего бронь.
     * @param $packageElement
     * @returns
     */
    public getPackageData($packageElement) {
        let packageOffset = $packageElement.offset();
        let roomLine = $('.roomDates, .leftRoomsLine').filter(function () {
            return ChessBoardManager.isOffsetsEqual($(this).offset().top + subtrahend / 2, packageOffset.top);
        });
        let roomTypeId = roomLine.parent().get(0).id || roomLine.get(0).getAttribute('data-roomtypeid');
        let accommodationId = roomLine.children().get(0).id;
        if (accommodationId.substring(0, 16) === 'no_accommodation') {
            accommodationId = '';
        }
        let dateElements = roomLine.children().children();

        let description = $packageElement.find('.package-description').text();

        let startDateLeftOffset = packageOffset.left - this.getPackageToMiddayOffset();

        let endDateLeftOffset = packageOffset.left
            + parseInt($packageElement.get(0).style.width, 10)
            - this.getPackageToMiddayOffset()
        ;

        let startDate = this.getDateStringByLeftOffset(dateElements, startDateLeftOffset);
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

    private static isOffsetsEqual(firstOffset, secondOffset) {
        let firstIntOffset = parseInt(firstOffset, 10);
        let secondIntOffset = parseInt(secondOffset, 10);
        return (firstIntOffset === secondIntOffset)
            || (firstIntOffset === secondIntOffset + 1)
            || (firstIntOffset === secondIntOffset - 1);
    }

    private getDateStringByLeftOffset($dateElements, leftOffset) {
        let dateElement = $dateElements.filter((index, cell) => {
            let cellOffset = $(cell).offset().left;
            const difference = cellOffset - leftOffset;

            return difference <= (this.arrowWidth * 2) && difference >= 0;
        });

        let dateNumber = $dateElements.index(dateElement);
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

            let roomTypeId = $(this).closest('.roomTypeRooms').attr('id');
            let currentDate = moment(this.getAttribute('data-date'), "DD.MM.YYYY");
            let templatePackageElement = ChessBoardManager.getTemplateElement();
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
                    }).on('mousedown touchstart', function (event) {
                        if (self.isIntervalAvailable(packageData, event)) {
                            relocatablePackage = this;
                            $wrapper.append(this);
                            this.style.position = 'absolute';
                            this.setAttribute('unplaced', true);
                            relocatablePackageData = self.dataManager.getNoAccommodationIntervalById(this.id);
                            let intervalStartDate = ChessBoardManager.getMomentDate(relocatablePackageData.begin);
                            this.style.left = self.getPackageLeftOffset(intervalStartDate, this) + 'px';
                            const pageY = event.type === 'touchstart' ? event.originalEvent.touches[0].pageY : event.pageY;
                            this.style.top = self.getNearestTableLineTopOffset(pageY - document.body.scrollTop)
                                + document.body.scrollTop - wrapperTopOffset + subtrahend / 2 + 'px';
                            if (!self.isPackageLocationCorrect(relocatablePackage)) {
                                relocatablePackage.classList.add('red-package');
                            }
                            $popover.popover('hide');
                        }
                        $(document.body).on('mouseup touchend', function () {
                            $(document.body).off('mouseup touchend');
                            if (!isDragged && relocatablePackage) {
                                if (self.isPackageLocationCorrect(relocatablePackage)) {
                                    self.actionManager.callUpdatePackageModal($(relocatablePackage), relocatablePackageData);
                                }
                            }
                        });
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

    private isIntervalAvailable(packageData, event, isDivide = false) {
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
        const chessboardStyles = getComputedStyle(document.getElementById('accommodation-chessBoard-content'));
        const chessboardWidth = parseInt(chessboardStyles.width, 10);

        return chessboardWidth - (!isMobileDevice() ? styleConfigs[this.currentSizeConfigNumber].headerWidth : 0);
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

    private updateLeftRoomCounts() {
        let self = this;
        let leftRoomCounts = self.dataManager.getLeftRoomCounts();
        $('.leftRoomsLine').each(function (index, item) {
            let roomTypeId = item.getAttribute('data-roomtypeid');
            if (leftRoomCounts[roomTypeId]) {
                let dateElements = item.children[0].children;
                for (let i = 0; i < dateElements.length; i++) {
                    let dateLeftRoomsCount = leftRoomCounts[roomTypeId][i];
                    let backgroundColor = self.colors['leftRoomsPositive'];
                    if (dateLeftRoomsCount == 0) {
                        backgroundColor = self.colors['leftRoomsZero'];
                    } else if (dateLeftRoomsCount < 0) {
                        backgroundColor = self.colors['leftRoomsNegative'];
                    }
                    dateElements[i].style.backgroundColor = backgroundColor;
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

    private updateChessboardDataWithoutActions() {
        if (!isMobileDevice()) {
            let time = 0;
            $(document).on('click', () => {
                time = 0;
            });

            setInterval(() => {
                time++;
                if (time > 30) {
                    ActionManager.showLoadingIndicator();
                    this.dataManager.updatePackagesData();
                    ActionManager.hideLoadingIndicator();
                    time = 0;
                }
            }, 1000);
        }
    }

    public static getClickEventType() {
        return isMobileDevice() ? 'touchstart' : 'click';
    }

    private hangOnHideFieldButtonClick() {
        const changeVisibilityFunc = (element) => {
            const $select2Elements = $(element.parentNode).find('span.select2-container, select');
            const isVisible = $select2Elements.eq(0).css('display') !== 'none';
            $select2Elements.each((index, selectElement) => {
                this.setImportantStyle($(selectElement), 'display', isVisible ? 'none' : 'inline-block');
            });
            element.style.color = !isVisible ? 'inherit' : 'red';
        };

        const $hideFieldButtons = $('.hide-field-button');
        if (isMobileDevice()) {
            $hideFieldButtons.each((index, element) => {
                changeVisibilityFunc(element);
            });
        }
        $hideFieldButtons.on(ChessBoardManager.getClickEventType(), (event) => {
            changeVisibilityFunc(event.target);
            //prevent touch on filter button after element is hidden
            event.preventDefault();
        });
    }
}