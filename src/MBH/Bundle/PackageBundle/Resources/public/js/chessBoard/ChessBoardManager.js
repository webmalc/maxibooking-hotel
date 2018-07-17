///<reference path="DataManager.ts"/>
var ChessBoardManager = /** @class */ (function () {
    function ChessBoardManager(packagesData, leftRoomsData, noAccommodationCounts, noAccommodationIntervals) {
        this.canMoveAccommodation = true;
        this.currentSizeConfigNumber = currentStyleConfigNumber;
        this.colors = colors;
        this.distanceBetweenAccommodationElements = 0;
        this.distanceByHovering = 4;
        this.arrowWidth = this.getArrowWidth();
        this.dataManager = new DataManager(packagesData, leftRoomsData, noAccommodationCounts, noAccommodationIntervals, this);
        this.actionManager = new ActionManager(this.dataManager);
        this.updateNoAccommodationPackageCounts();
        this.templateDivideButton = ChessBoardManager.getTemplateDivideButton();
        this.templateRemoveButton = ChessBoardManager.getTemplateRemoveButton();
        this.tableStartDate = ChessBoardManager.getTableStartDate();
        this.tableEndDate = ChessBoardManager.getTableEndDate();
        this.updateChessboardDataWithoutActions();
    }
    ChessBoardManager.deletePackageElement = function (packageId) {
        var packageElement = document.getElementById(packageId);
        if (packageElement) {
            packageElement.parentElement.removeChild(packageElement);
        }
    };
    ChessBoardManager.prototype.getPackageToMiddayOffset = function () {
        var config = this.getStylesConfig();
        return Math.round(config.tableCellWidth / 2) + this.arrowWidth;
    };
    ChessBoardManager.prototype.getArrowWidth = function () {
        return Math.floor((this.getStylesConfig().tableCellHeight - subtrahend) / 4);
    };
    ChessBoardManager.prototype.getStylesConfig = function () {
        return styleConfigs[this.currentSizeConfigNumber];
    };
    ChessBoardManager.prototype.hangHandlers = function () {
        var wrapper = $('#calendarWrapper');
        var self = this;
        var chessBoardContentBlock = document.getElementById('accommodation-chessBoard-content');
        this.setContentWidth(chessBoardContentBlock);
        $('.sidebar-toggle').click(function () {
            setTimeout(function () {
                self.setContentWidth(chessBoardContentBlock);
            }, 1000);
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
        var $numberOfRoomsSelect = $('#nuber-of-rooms-select');
        $numberOfRoomsSelect.on("select2:select", function () {
            window.location.href = Routing.generate('change_number_of_rooms', { numberOfRooms: $numberOfRoomsSelect.val() });
        });
        $('.pagination-sm').find('a').each(function () {
            var filterData = $('#accommodation-report-filter').serialize() + '&page=' + $(this).text();
            var route = Routing.generate('chess_board_home') + '?' + filterData;
            this.setAttribute('href', route);
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
        $('#modal_delete_package').on('hidden.bs.modal', function () {
            self.updateTable();
        });
        self.handleSizeSlider();
        self.onChangeScaleClick();
        document.getElementById('packageModalConfirmButton').onclick = function () {
            var data = ActionManager.getDataFromUpdateModal();
            var packageId = data.packageId;
            var accommodationId = data.accommodationId;
            var packageData = self.getPackageData($('#' + data.id));
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
        this.hangChangeNumberOfDaysButtonClick();
        $reportFilter.find('#filter-button').click(function () {
            $reportFilter.submit();
        });
        this.onAddGuestClick();
        this.hangOnHideFieldButtonClick();
        if (!isMobileDevice()) {
            //Фиксирование верхнего и левого блоков таблицы
            chessBoardContentBlock.onscroll = function () {
                ChessBoardManager.onContentTableScroll(chessBoardContentBlock);
            };
        }
        var templatePackageElement = ChessBoardManager.getTemplateElement();
        //Создание брони
        var dateElements = $('.date, .leftRooms');
        var $document = $(document);
        if (canCreatePackage) {
            var eventName = isMobileDevice() ? 'contextmenu' : 'mousedown';
            dateElements.on(eventName, function (event) {
                chessBoardContentBlock.style.overflow = 'hidden';
                event.preventDefault();
                var startXPosition = event.pageX;
                var startLeftScroll = chessBoardContentBlock.scrollLeft;
                var newPackage = templatePackageElement.cloneNode(true);
                newPackage.classList.add('success');
                var dateJqueryObject = $(this.parentNode);
                var currentRoomDateElements = dateJqueryObject.parent().children();
                var startDateNumber = currentRoomDateElements.index(dateJqueryObject);
                var startDate = moment(self.tableStartDate).add(startDateNumber, 'day');
                newPackage = self.setPackageOffset(newPackage, startDate, dateJqueryObject.parent().parent(), wrapper);
                newPackage.id = 'newPackage' + packages.length;
                newPackage.style.zIndex = '999';
                newPackage.style.width = styleConfigs[self.currentSizeConfigNumber].tableCellWidth - (self.arrowWidth * 2) + 'px';
                var newPackageStartXOffset = parseInt(newPackage.style.left, 10);
                $(document).on('touchmove mousemove', function (event) {
                    var isMouseMoveEvent = event.type === 'mousemove';
                    var scrollOffset = chessBoardContentBlock.scrollLeft - startLeftScroll;
                    var mouseXOffset = startXPosition - (isMouseMoveEvent ? event.pageX : event.originalEvent.touches[0].pageX);
                    var isLeftMouseShift = mouseXOffset > 0;
                    var packageLengthRestriction = self.getPackageLengthRestriction(startDate, isLeftMouseShift, self.tableStartDate, self.tableEndDate);
                    var griddedOffset = self.getGriddedOffset(mouseXOffset, scrollOffset, packageLengthRestriction);
                    var leftMouseOffset = isLeftMouseShift ? griddedOffset : 0;
                    var packageWidth = griddedOffset - 2 * self.arrowWidth;
                    if (self.isPackageLocationCorrect(newPackage)) {
                        newPackage.classList.add('success');
                        newPackage.classList.remove('danger');
                        newPackage.style.backgroundColor = '';
                    }
                    else {
                        newPackage.classList.remove('success');
                        newPackage.style.setProperty('background-color', self.colors['danger_add'], 'important');
                        newPackage.classList.add('danger');
                    }
                    newPackage.style.left = newPackageStartXOffset - leftMouseOffset + 'px';
                    newPackage.style.width = packageWidth + 'px';
                });
                $(document).on('mouseup touchend', function () {
                    chessBoardContentBlock.style.overflow = 'auto';
                    $document.unbind('mousemove  mouseup touchend');
                    if ((newPackage.style.width) && self.isPackageLocationCorrect(newPackage) && newPackage.id) {
                        var packageData = self.getPackageData($(newPackage));
                        self.saveNewPackage(packageData);
                    }
                    self.updateTable();
                });
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
    ChessBoardManager.prototype.handleSizeSlider = function () {
        var _this = this;
        var $slider = $('#ex1');
        $slider.slider({ tooltip: 'hide', reverseed: true });
        $slider.on('slideStop', function () {
            var sliderValue = $('#ex1').slider('getValue');
            _this.changeScale(sliderValue);
        });
    };
    ChessBoardManager.prototype.changeScale = function (sliderValue) {
        if (this.currentSizeConfigNumber !== sliderValue && sliderValue >= 0 && sliderValue <= maxSliderSize) {
            ChessBoardManager.setCookie('chessboardSizeNumber', sliderValue);
            this.currentSizeConfigNumber = sliderValue;
            window.location.reload();
        }
    };
    ChessBoardManager.prototype.onChangeScaleClick = function () {
        var _this = this;
        $('.reduce-scale-button, .increase-scale-button').on(ChessBoardManager.getClickEventType(), function (event) {
            var sliderValue = $('#ex1').slider('getValue');
            var buttonClassList = event.target.classList;
            var newSliderValue = buttonClassList.contains('increase-scale-button') ? (sliderValue + 1) : (sliderValue - 1);
            _this.changeScale(newSliderValue);
        });
    };
    ChessBoardManager.setCookie = function (name, value, options) {
        if (options === void 0) { options = {}; }
        value = encodeURIComponent(value);
        var updatedCookie = name + "=" + value;
        for (var propName in options) {
            updatedCookie += "; " + propName;
            var propValue = options[propName];
            if (propValue !== true) {
                updatedCookie += "=" + propValue;
            }
        }
        document.cookie = updatedCookie;
    };
    ChessBoardManager.prototype.setContentWidth = function (chessBoardContentBlock) {
        var contentWidth = parseInt($('#months-and-dates').css('width'), 10)
            + styleConfigs[this.currentSizeConfigNumber].headerWidth + ChessBoardManager.SCROLL_BAR_WIDTH;
        if (parseInt($(chessBoardContentBlock).css('width'), 10) > contentWidth) {
            chessBoardContentBlock.style.width = contentWidth + 'px';
        }
        else {
            chessBoardContentBlock.style.width = 'auto';
        }
    };
    ChessBoardManager.prototype.saveNewPackage = function (packageData) {
        'use strict';
        var $searchPackageForm = $('#package-search-form');
        $searchPackageForm.find('#s_roomType').val(packageData.roomType);
        $searchPackageForm.find('#s_begin').val(packageData.begin);
        $searchPackageForm.find('#s_end').val(packageData.end);
        $searchPackageForm.find('#s_range').val('0');
        var newPackageRequestData = ChessBoardManager.getNewPackageRequestData($searchPackageForm);
        this.dataManager.getPackageOptionsRequest(newPackageRequestData, packageData);
    };
    ChessBoardManager.getNewPackageRequestData = function ($searchPackageForm, specialId) {
        if (specialId === void 0) { specialId = null; }
        var newPackageRequestData = ChessBoardManager.getFilterData($searchPackageForm);
        if (specialId) {
            var specialString = 'special%5D=';
            var specialPosition = newPackageRequestData.indexOf(specialString);
            var specialValuePosition = specialPosition + specialString.length;
            newPackageRequestData = newPackageRequestData.slice(0, specialValuePosition) + specialId
                + newPackageRequestData.slice(specialValuePosition, newPackageRequestData.length);
        }
        return newPackageRequestData;
    };
    ChessBoardManager.getFilterData = function ($searchPackageForm) {
        var searchData = $searchPackageForm.serialize();
        var pageNumber = document.getElementById('pageNumber').value;
        return searchData + '&page=' + pageNumber;
    };
    ChessBoardManager.prototype.onAddGuestClick = function () {
        $('#add-guest').on('click', function (e) {
            var guestModal = $('#add-guest-modal'), form = guestModal.find('form'), button = $('#add-guest-modal-submit'), errors = $('#add-guest-modal-errors');
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
                        }
                        else {
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
                });
            });
        });
    };
    ChessBoardManager.prototype.getGriddedOffset = function (mouseXOffset, scrollOffset, packageLengthRestriction) {
        'use strict';
        var griddedOffset = Math.ceil((Math.abs(mouseXOffset) + scrollOffset) / styleConfigs[this.currentSizeConfigNumber].tableCellWidth)
            * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
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
    ChessBoardManager.prototype.getPackageLengthRestriction = function (startDate, isLeftMouseShift, tableStartDate, tableEndDate) {
        'use strict';
        if (isLeftMouseShift) {
            return startDate.diff(tableStartDate, 'days') * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
        }
        return tableEndDate.diff(startDate, 'days') * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
    };
    ChessBoardManager.prototype.hangChangeNumberOfDaysButtonClick = function () {
        var getNewDate = function (changeButton, $dateField) {
            var changeDaysFormat = parseInt(changeButton.getAttribute('data-number-of-days'), 10);
            var isAddition = changeButton.getAttribute('data-change-type') === 'add';
            var date = moment($dateField.val(), 'DD.MM.YYYY');
            return isAddition ? date.add(changeDaysFormat, 'days') : date.subtract(changeDaysFormat, 'days');
        };
        $('.change-days-button').on(ChessBoardManager.getClickEventType(), function () {
            var $rangePicker = $('.daterangepicker-input').data('daterangepicker');
            var $beginDateField = $('#accommodation-report-filter-begin');
            var $endDateField = $('#accommodation-report-filter-end');
            var beginDate = getNewDate(this, $beginDateField);
            var endDate = getNewDate(this, $endDateField);
            $beginDateField.val(beginDate.format('DD.MM.YYYY'));
            $endDateField.val(endDate.format('DD.MM.YYYY'));
            $rangePicker.setStartDate(beginDate.toDate());
            $rangePicker.setEndDate(endDate.toDate());
        });
    };
    ChessBoardManager.prototype.addAccommodationElements = function () {
        var wrapper = $('#calendarWrapper');
        var templatePackageElement = ChessBoardManager.getTemplateElement();
        var packages = document.createElement('div');
        var accommodationsData = this.dataManager.getAccommodations();
        var lastAddedElement = null;
        for (var accommodationId in accommodationsData) {
            if (accommodationsData.hasOwnProperty(accommodationId)) {
                var accommodationData = accommodationsData[accommodationId];
                if (accommodationData.accommodation) {
                    var packageDiv = this.createPackageElementWithOffset(templatePackageElement, accommodationData, wrapper);
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
    };
    ChessBoardManager.getTemplateElement = function () {
        var templateDiv = document.createElement('div');
        templateDiv.style.position = 'absolute';
        templateDiv.classList.add('package');
        templateDiv.classList.add('package-with-left-arrow');
        templateDiv.classList.add('package-with-right-arrow');
        var buttonsDiv = document.createElement('div');
        buttonsDiv.classList.add('package-action-buttons');
        templateDiv.appendChild(buttonsDiv);
        return templateDiv;
    };
    ChessBoardManager.prototype.createPackageElement = function (packageItem, templatePackageElement, hasButtons, accommodationElementIndex) {
        if (templatePackageElement === void 0) { templatePackageElement = null; }
        if (hasButtons === void 0) { hasButtons = true; }
        if (accommodationElementIndex === void 0) { accommodationElementIndex = 100; }
        if (!templatePackageElement) {
            templatePackageElement = ChessBoardManager.getTemplateElement();
        }
        var config = styleConfigs[this.currentSizeConfigNumber];
        var packageStartDate = ChessBoardManager.getMomentDate(packageItem.begin);
        var packageEndDate = ChessBoardManager.getMomentDate(packageItem.end);
        var accommodationStartDate = ChessBoardManager.getMomentDate(packageItem.begin);
        var accommodationEndDate = ChessBoardManager.getMomentDate(packageItem.end);
        var packageCellCount = packageEndDate.diff(packageStartDate, 'days');
        var packageWidth = packageCellCount * config.tableCellWidth - this.distanceBetweenAccommodationElements;
        var packageDiv = templatePackageElement.cloneNode(true);
        packageDiv.id = packageItem.id;
        var packageName = (packageItem.payer) ? packageItem.payer : packageItem.number;
        var description = document.createElement('div');
        var descriptionText = packageName ? packageName.substr(0, packageCellCount * 5) : '';
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
        var descriptionPopoverContent = '<b>' + Translator.trans("chessboard_manager.package_tooltip.package_number") + '</b>:' + packageItem.number
            + (packageItem.payer ? '<br><b>' + Translator.trans("chessboard_manager.package_tooltip.payer") + ': </b>' + packageItem.payer : '')
            + '<br><b>' + Translator.trans("chessboard_manager.package_tooltip.price") + ': </b>' + packageItem.price
            + '<br><b>' + Translator.trans("chessboard_manager.package_tooltip.package_begin") + ': </b>' + packageItem.packageBegin
            + '<br><b>' + Translator.trans("chessboard_manager.package_tooltip.package_end") + ': </b>' + packageItem.packageEnd
            + '<br><b>' + Translator.trans("chessboard_manager.package_tooltip.is_checkin") + ': </b>' + Translator.trans(packageItem.isCheckIn ? 'package.yes' : 'package.no')
            + '<br><b>' + Translator.trans("chessboard_manager.package_tooltip.is_checkout") + ': </b>' + Translator.trans((packageItem.isCheckOut ? 'package.yes' : 'package.no'));
        description.setAttribute('data-content', descriptionPopoverContent);
        var $packageDiv = $(packageDiv);
        if (!isMobileDevice()) {
            packageDiv.onmousemove = function () {
                var isElementInPopoverWindow = packageDiv.parentNode.classList.contains('popover-package-container');
                if (!isElementInPopoverWindow) {
                    $packageDiv.find('.package-action-buttons').show();
                    var $descriptionElement = $(this).find('.package-description');
                    if ($descriptionElement.length > 0) {
                        var popoverId = $descriptionElement.attr('aria-describedby');
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
            var rightNeighbor = this.dataManager.getAccommodationNeighbors(packageItem.id)['right'];
            if (rightNeighbor) {
                if (rightNeighbor.position == 'middle' || rightNeighbor.position == 'right') {
                    packageWidth += this.distanceBetweenAccommodationElements;
                }
                else {
                    packageWidth -= this.arrowWidth;
                }
            }
        }
        else {
            packageWidth -= this.arrowWidth;
        }
        if (packageItem.position == 'middle' || packageItem.position == 'right') {
            packageDiv.classList.add('with-left-divider');
            packageDiv.classList.remove('package-with-left-arrow');
        }
        else {
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
        }
        else if (packageItem.isCheckIn) {
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
    };
    ChessBoardManager.prototype.createPackageElementWithOffset = function (templatePackageElement, packageItem, wrapper) {
        var differenceInDays = ChessBoardManager.getMomentDate(packageItem.begin).diff(this.tableStartDate, 'days');
        var accommodationElementIndex = 150 - differenceInDays;
        var packageDiv = this.createPackageElement(packageItem, templatePackageElement, true, accommodationElementIndex);
        var packageStartDate = ChessBoardManager.getMomentDate(packageItem.begin);
        var packageEndDate = ChessBoardManager.getMomentDate(packageItem.end);
        var roomDatesListElement = $('#' + packageItem.accommodation);
        packageDiv = this.setPackageOffset(packageDiv, packageStartDate, roomDatesListElement, wrapper);
        packageDiv = this.editAccommodationElement(packageDiv, packageStartDate, packageEndDate);
        return packageDiv;
    };
    ChessBoardManager.prototype.setPackageOffset = function (packageElement, startDate, roomLineElement, wrapper) {
        'use strict';
        var wrapperTopOffset = parseInt(wrapper.offset().top, 10);
        var roomLineTopOffset = parseInt(roomLineElement.offset().top, 10);
        packageElement.style.left = this.getPackageLeftOffset(startDate, packageElement) + 'px';
        packageElement.style.top = roomLineTopOffset - wrapperTopOffset + (subtrahend / 2) + 'px';
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
                - (differenceInDays - 1) * styleConfigs[this.currentSizeConfigNumber].tableCellWidth
                - this.getPackageToMiddayOffset() + 'px';
        }
        var descriptionElement = element.querySelector('.package-description');
        var elementWidth = parseInt(element.style.width, 10);
        var descriptionWidth = parseInt(descriptionElement.style.width, 10);
        if (descriptionWidth > elementWidth) {
            descriptionElement.style.width = elementWidth + 'px';
        }
        var cellWidth = styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
        if ((elementWidth < cellWidth - this.arrowWidth) || (elementWidth < cellWidth && element.classList.contains('with-left-divider'))) {
            var divideButton = element.querySelector('.divide-package-button');
            if (divideButton) {
                divideButton.parentNode.removeChild(divideButton);
            }
        }
        return element;
    };
    ChessBoardManager.prototype.updateAccommodationsWithNeighbors = function () {
        var _this = this;
        var $resizableElements = $('.ui-resizable-handle').parent();
        $resizableElements.each(function (index, accommodationElement) {
            var accommodationId = accommodationElement.id;
            var accommodationNeighbors = _this.dataManager.getAccommodationNeighbors(accommodationId);
            var hasRightNeighborResizable = false;
            var hasLeftNeighborResizable = false;
            var hasLeftResizable = false;
            var $accommodation = $(accommodationElement);
            var $rightResizable = $accommodation.find('.ui-resizable-e');
            var $leftResizable = $accommodation.find('.ui-resizable-w');
            if (accommodationNeighbors['left']) {
                var leftNeighborData = accommodationNeighbors['left'];
                var leftNeighbor = document.getElementById(leftNeighborData.id);
                if (accommodationElement.getElementsByClassName('ui-resizable-w').length > 0) {
                    hasLeftResizable = true;
                }
                if (leftNeighbor.getElementsByClassName('ui-resizable-e').length > 0) {
                    hasLeftNeighborResizable = true;
                }
            }
            if (accommodationNeighbors['right']) {
                var rightNeighborData = accommodationNeighbors['right'];
                var rightNeighbor = document.getElementById(rightNeighborData.id);
                if (rightNeighbor.getElementsByClassName('ui-resizable-w').length > 0) {
                    hasRightNeighborResizable = true;
                }
            }
            var hasEarlyCheckin = accommodationElement.classList.contains('early-checkin');
            if (hasLeftNeighborResizable && $accommodation.hasClass('package-with-left-arrow')) {
                accommodationElement.classList.remove('package-with-left-arrow');
                accommodationElement.classList.add('near-left-element');
                accommodationElement.style.width =
                    parseInt(accommodationElement.style.width, 10) + _this.arrowWidth + 'px';
                accommodationElement.style.left =
                    parseInt(accommodationElement.style.left, 10) - _this.arrowWidth + 'px';
                if (hasEarlyCheckin) {
                    _this.setImportantStyle($accommodation.find('.ui-resizable-w'), 'background-color');
                }
            }
            var hasLateCheckout = accommodationElement.classList.contains('late-checkout');
            var $leftNeighbor = hasLeftNeighborResizable ? $('#' + accommodationNeighbors['left'].id) : null;
            var $rightNeighbor = hasRightNeighborResizable ? $('#' + accommodationNeighbors['right'].id) : null;
            var hasLeftNeighborLateCheckout = hasLeftNeighborResizable && $leftNeighbor.hasClass('late-checkout');
            var hasRightNeighborEarlyCheckin = hasRightNeighborResizable && $rightNeighbor.hasClass('early-checkin');
            if (hasRightNeighborResizable && $accommodation.hasClass('package-with-right-arrow')) {
                $rightResizable.hide();
                accommodationElement.classList.add('near-right-element');
                accommodationElement.classList.remove('package-with-right-arrow');
                var accommodationWidth = $accommodation.width() + _this.arrowWidth;
                $accommodation.width(accommodationWidth);
                if (hasLateCheckout) {
                    _this.setImportantStyle($accommodation.find('.ui-resizable-e'), 'background-color');
                    _this.setImportantStyle($rightNeighbor.find('.ui-resizable-w'), 'background-color');
                }
            }
            if (hasRightNeighborResizable || hasLeftNeighborResizable) {
                var leftNeighborWidth_1;
                var rightNeighborWidth_1;
                var rightNeighborLeft_1;
                var isMoved_1 = false;
                var $descriptionElement_1 = $accommodation.find('.package-description');
                accommodationElement.onmousemove = (function () {
                    if (!isMoved_1) {
                        $accommodation.find('.package-action-buttons').show();
                        if ($descriptionElement_1.length > 0) {
                            var popoverId = $descriptionElement_1.attr('aria-describedby');
                            if (popoverId == null) {
                                $('.popover').popover('hide');
                                $descriptionElement_1.popover('show');
                            }
                        }
                        if (!hasEarlyCheckin) {
                            $leftResizable.css('background-color', '');
                        }
                        if (!$accommodation.hasClass('in-resize')) {
                            if (hasRightNeighborResizable) {
                                rightNeighborWidth_1 = $rightNeighbor.width();
                                rightNeighborLeft_1 = parseInt($rightNeighbor.css('left'), 10);
                                $rightResizable.show();
                                $rightNeighbor.width(rightNeighborWidth_1 - _this.distanceByHovering);
                                $rightNeighbor.css('left', rightNeighborLeft_1 + _this.distanceByHovering);
                                if (hasLateCheckout && !hasRightNeighborEarlyCheckin) {
                                    _this.setImportantStyle($rightNeighbor.find('.ui-resizable-w'), 'background-color', '');
                                }
                            }
                            if (hasLeftNeighborResizable && hasLeftResizable) {
                                leftNeighborWidth_1 = $leftNeighbor.width();
                                $('#' + accommodationNeighbors['left'].id).width(leftNeighborWidth_1 - _this.distanceByHovering);
                            }
                        }
                    }
                    isMoved_1 = true;
                });
                accommodationElement.onmouseleave = (function () {
                    $descriptionElement_1.popover('hide');
                    $accommodation.find('.package-action-buttons').hide();
                    if (!$accommodation.hasClass('in-resize')) {
                        if (hasLateCheckout) {
                            _this.setImportantStyle($rightNeighbor.find('.ui-resizable-w'), 'background-color');
                        }
                        if (hasLeftNeighborLateCheckout) {
                            _this.setImportantStyle($leftResizable, 'background-color');
                        }
                        if (hasRightNeighborResizable) {
                            $rightNeighbor.width(rightNeighborWidth_1);
                            $rightNeighbor.css('left', rightNeighborLeft_1);
                            $rightResizable.hide();
                        }
                        if (hasLeftNeighborResizable && hasLeftResizable) {
                            $('#' + accommodationNeighbors['left'].id).width(leftNeighborWidth_1);
                        }
                    }
                    isMoved_1 = false;
                });
            }
        });
    };
    ChessBoardManager.prototype.setImportantStyle = function ($element, property, value) {
        if (value === void 0) { value = ChessBoardManager.LATE_CHECKOUT_EARLY_CHECKIN_COLOR; }
        $element.get(0).style.setProperty(property, value, 'important');
    };
    ChessBoardManager.prototype.getNearestTableLineTopOffset = function (yCoordinate) {
        var tableLines = [].slice.call(document.getElementsByClassName('roomDates'));
        var topOffset = this.getNearestTableLineToYOffset(yCoordinate, tableLines);
        if (!topOffset) {
            var leftRoomsLines = [].slice.call(document.getElementsByClassName('leftRoomsLine'));
            topOffset = this.getNearestTableLineToYOffset(yCoordinate, leftRoomsLines);
            if (!topOffset) {
                topOffset = tableLines[1].getBoundingClientRect().top;
            }
        }
        return topOffset;
    };
    ChessBoardManager.prototype.getNearestTableLineToYOffset = function (yCoordinate, lines) {
        var _this = this;
        var topOffset = null;
        lines.some(function (line) {
            var lineTopOffset = line.getBoundingClientRect().top;
            if (yCoordinate >= lineTopOffset && yCoordinate <= (lineTopOffset + styleConfigs[_this.currentSizeConfigNumber].tableCellHeight)) {
                topOffset = lineTopOffset;
                return true;
            }
            else {
                return false;
            }
        });
        return topOffset;
    };
    ChessBoardManager.prototype.getPackageLeftOffset = function (startDate, element) {
        var tableStartDate = ChessBoardManager.getTableStartDate();
        var packageDateOffset = startDate.diff(tableStartDate, 'days') * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
        if ($(element).hasClass('with-left-divider')) {
            packageDateOffset -= this.arrowWidth;
        }
        return packageDateOffset + this.getPackageToMiddayOffset();
    };
    ChessBoardManager.getMomentDate = function (dateString) {
        return moment(dateString, "DD.MM.YYYY");
    };
    ChessBoardManager.prototype.addListeners = function (identifier) {
        var jQueryObj = $(identifier);
        var self = this;
        this.addDraggable(jQueryObj);
        jQueryObj.each(function (index, element) {
            var intervalData = self.dataManager.getAccommodationIntervalById(element.id);
            var $element = $(element);
            self.addResizable($element, intervalData);
            if (isMobileDevice()) {
                var touchTime_1;
                var isTouchEnd_1 = false;
                $element.on('touchstart', function () {
                    if (isTouchEnd_1 && touchTime_1 && moment().diff(touchTime_1) < 500) {
                        if (intervalData.viewPackage) {
                            self.dataManager.getPackageDataRequest(intervalData.packageId);
                        }
                        touchTime_1 = null;
                    }
                    else {
                        touchTime_1 = new moment();
                        isTouchEnd_1 = false;
                    }
                });
                $element.on('touchend', function () {
                    if (touchTime_1) {
                        isTouchEnd_1 = true;
                    }
                });
            }
            else {
                $element.dblclick(function () {
                    if (intervalData.viewPackage) {
                        self.dataManager.getPackageDataRequest(intervalData.packageId);
                    }
                });
            }
            $element.find('.remove-package-button').on('click touchstart', function () {
                self.actionManager.callRemoveConfirmationModal(intervalData.packageId);
            });
            $element.find('.divide-package-button').on('click', function (event) {
                self.canMoveAccommodation = false;
                var $scissorIcon = $(event.target);
                if (intervalData.viewPackage) {
                    $scissorIcon.on('click touchstart', function () {
                        self.updatePackagesData();
                    });
                    var accommodationWidth_1 = parseInt(element.style.width, 10);
                    var tableCellWidth_1 = styleConfigs[self.currentSizeConfigNumber].tableCellWidth;
                    if (accommodationWidth_1 > tableCellWidth_1 && accommodationWidth_1 <= tableCellWidth_1 * 2) {
                        $('.divide-package-button').tooltip('hide');
                        self.divide(element, accommodationWidth_1 / 2);
                    }
                    else {
                        var packageLeftCoordinate_1 = element.getBoundingClientRect().left;
                        var line_1 = document.createElement('div');
                        line_1.classList.add('dividing-line');
                        var isAccommodationAbroadTable_1 = self.isAbroadRightTableSide(ChessBoardManager.getMomentDate(intervalData.end))
                            || self.isAbroadLeftTableSide(ChessBoardManager.getMomentDate(intervalData.begin));
                        var packageToMiddayOffset_1 = self.getPackageToMiddayOffset();
                        var hasLeftArrow_1 = element.classList.contains('package-with-left-arrow');
                        var defaultLeftValue_1 = isAccommodationAbroadTable_1
                            ? packageToMiddayOffset_1
                            : (hasLeftArrow_1 ? tableCellWidth_1 - self.arrowWidth : tableCellWidth_1);
                        line_1.style.left = defaultLeftValue_1 + 'px';
                        element.appendChild(line_1);
                        $element.on('mousemove', function (event) {
                            var offset = event.clientX - packageLeftCoordinate_1;
                            var griddedOffset;
                            if (isAccommodationAbroadTable_1) {
                                griddedOffset = Math.floor(Math.abs(offset) / tableCellWidth_1)
                                    * tableCellWidth_1
                                    + packageToMiddayOffset_1;
                            }
                            else {
                                griddedOffset = Math.round(Math.abs(offset) / tableCellWidth_1) * tableCellWidth_1
                                    - (hasLeftArrow_1 ? self.arrowWidth : 0);
                            }
                            if (griddedOffset < defaultLeftValue_1 * 1.5) {
                                griddedOffset = defaultLeftValue_1;
                            }
                            else if (griddedOffset > accommodationWidth_1 - tableCellWidth_1) {
                                griddedOffset = accommodationWidth_1 - tableCellWidth_1 + (hasLeftArrow_1 ? self.arrowWidth : 0);
                            }
                            line_1.style.left = griddedOffset + 'px';
                            $element.off('click');
                            $element.on('click', function () {
                                $element.off('mousemove touchmove');
                                $('.dividing-line').remove();
                                self.divide(this, griddedOffset);
                            });
                        });
                    }
                }
            });
        });
    };
    ChessBoardManager.prototype.divide = function (packageElement, firstAccommodationWidth) {
        this.canMoveAccommodation = true;
        if (packageElement.parentNode) {
            var packageWidth = parseInt(packageElement.style.width, 10);
            $(packageElement).find('.divide-package-button, .remove-package-button, .ui-resizable-e, .ui-resizable-w, .right-inner-resizable-triangle').remove();
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
                secondAccommodation.classList.remove('package-with-left-arrow');
                firstAccommodation.classList.add('with-right-divider');
                firstAccommodation.classList.remove('package-with-right-arrow');
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
        var contentWidth = descriptionElement.innerHTML.length * 8;
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
            var elementStartZIndex = element.style.zIndex;
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
                        }
                        else {
                            ui.position.top = self.getGriddedHeightValue(ui.position.top + styleConfigs[self.currentSizeConfigNumber].tableCellHeight / 2) + subtrahend / 2 - 1;
                            if (!self.isPackageLocationCorrect(this)) {
                                this.classList.add('red-package');
                            }
                            else {
                                this.classList.remove('red-package');
                            }
                        }
                    },
                    stop: function (event, ui) {
                        element.style.zIndex = elementStartZIndex;
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
                                self.actionManager.callUpdatePackageModal($(this), intervalData_1, changedSide, isDivide);
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
            //Если интервал не имеет размещения, но имеет права на создание размещения(просмотр брони)
            || (intervalData.updateAccommodation == undefined) && intervalData.viewPackage) {
            return 'y';
        }
        return '';
    };
    ChessBoardManager.prototype.isPackageLocationCorrect = function (packageElement) {
        var $packageElement = $(packageElement);
        var packageOffset = $packageElement.offset();
        return (this.isOnRoomDatesLine(packageOffset) || this.isOnLeftRoomsLine(packageOffset))
            && !this.isAbroadTable(packageElement, packageOffset)
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
    ChessBoardManager.prototype.isAbroadTable = function (packageElement, packageOffset) {
        var lastDateElementLeftOffset = parseInt($('.roomDates:eq(0)').children().children().last().offset().left, 10)
            + styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
        var packageEndLeftOffset = packageOffset.left + parseInt(packageElement.style.width, 10);
        return lastDateElementLeftOffset < packageEndLeftOffset;
    };
    /**
     * Проверяет находится ли бронь на одной из линий, укёазывающих размещение брони
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
            return ChessBoardManager.isOffsetsEqual(packageOffset.top, $(element).offset().top + subtrahend / 2);
        });
    };
    /**
     * Проверяет, пересекется ли период размещения брони с другими бронями, имеющими такой же тип размещения
     *
     * @param $packageElement
     * @returns {boolean}
     */
    ChessBoardManager.prototype.isPackageOverlapped = function ($packageElement) {
        var packageData = this.getPackageData($packageElement);
        var intervalsData = this.dataManager.getAccommodations();
        return Object.getOwnPropertyNames(intervalsData).some(function (intervalId) {
            var intervalData = intervalsData[intervalId];
            return !(intervalData.id === packageData.id)
                && intervalData.accommodation === packageData.accommodation
                && ChessBoardManager.getMomentDate(intervalData.begin).isBefore(moment(packageData.end, "DD.MM.YYYY"))
                && ChessBoardManager.getMomentDate(intervalData.end).isAfter(moment(packageData.begin, "DD.MM.YYYY"));
        });
    };
    ChessBoardManager.prototype.getGriddedHeightValue = function (height) {
        //1 - бордер
        var packageElementHeight = styleConfigs[this.currentSizeConfigNumber].tableCellHeight + 1;
        return Math.floor(height / packageElementHeight) * packageElementHeight;
    };
    ChessBoardManager.prototype.isAbroadLeftTableSide = function (intervalMomentBegin) {
        return intervalMomentBegin.isBefore(this.tableStartDate);
    };
    ChessBoardManager.prototype.isAbroadRightTableSide = function (intervalMomentEnd) {
        return intervalMomentEnd.isAfter(this.tableEndDate);
    };
    /**
     * Getting the line, containing first letters of sides in which enable widening (e - east, w - west)
     * @param intervalData
     * @returns {string}
     */
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
    /**
     * @param $element
     * @param intervalData
     * @returns {any}
     */
    ChessBoardManager.prototype.addResizable = function ($element, intervalData) {
        var elementStartBackground;
        var elementStartZIndex = $element.css('z-index');
        var self = this;
        var resizableHandlesValue = this.getResizableHandlesValue(intervalData);
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
                    }
                    else {
                        if (self.isPackageOverlapped($element)) {
                            self.setImportantStyle($element, 'background-color', self.colors['danger_add']);
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
                            self.actionManager.callUpdatePackageModal($(this), intervalData, changedSide);
                        }
                        else {
                            $element.removeClass('in-resize');
                            $element.css('z-index', elementStartZIndex);
                        }
                    }
                }
            });
        }
        if (intervalData.isLateCheckOut) {
            if (resizableHandlesValue.indexOf('e') > -1) {
                this.setImportantStyle($element.find('.ui-resizable-e'), 'border-left-color');
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
    };
    /**
     * Получение данных о брони на основании данных о текущем положении элемента, отображающего бронь.
     * @param $packageElement
     * @returns
     */
    ChessBoardManager.prototype.getPackageData = function ($packageElement) {
        var packageOffset = $packageElement.offset();
        var roomLine = $('.roomDates, .leftRoomsLine').filter(function () {
            return ChessBoardManager.isOffsetsEqual($(this).offset().top + subtrahend / 2, packageOffset.top);
        });
        var roomTypeId = roomLine.parent().get(0).id || roomLine.get(0).getAttribute('data-roomtypeid');
        var accommodationId = roomLine.children().get(0).id;
        if (accommodationId.substring(0, 16) === 'no_accommodation') {
            accommodationId = '';
        }
        var dateElements = roomLine.children().children();
        var description = $packageElement.find('.package-description').text();
        var startDateLeftOffset = packageOffset.left - this.getPackageToMiddayOffset();
        var endDateLeftOffset = packageOffset.left
            + parseInt($packageElement.get(0).style.width, 10)
            - this.getPackageToMiddayOffset();
        var startDate = this.getDateStringByLeftOffset(dateElements, startDateLeftOffset);
        var endDate = this.getDateStringByLeftOffset(dateElements, endDateLeftOffset);
        var paidStatus;
        if ($packageElement.hasClass('warning')) {
            paidStatus = 'warning';
        }
        else if ($packageElement.hasClass('success')) {
            paidStatus = 'success';
        }
        else {
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
    };
    ChessBoardManager.isOffsetsEqual = function (firstOffset, secondOffset) {
        var firstIntOffset = parseInt(firstOffset, 10);
        var secondIntOffset = parseInt(secondOffset, 10);
        return (firstIntOffset === secondIntOffset)
            || (firstIntOffset === secondIntOffset + 1)
            || (firstIntOffset === secondIntOffset - 1);
    };
    ChessBoardManager.prototype.getDateStringByLeftOffset = function ($dateElements, leftOffset) {
        var _this = this;
        var dateElement = $dateElements.filter(function (index, cell) {
            var cellOffset = $(cell).offset().left;
            var difference = cellOffset - leftOffset;
            return difference <= (_this.arrowWidth * 2) && difference >= 0;
        });
        var dateNumber = $dateElements.index(dateElement);
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
        var $popoverElements = $('.no-accommodation-date.achtung');
        $popoverElements.unbind('shown.bs.popover');
        $popoverElements.on('shown.bs.popover', function () {
            var lastPackage = $('.package').last();
            if (lastPackage.attr('unplaced')) {
                lastPackage.remove();
            }
            var openedPopovers = $('.popover');
            openedPopovers.not(':last').remove();
            var roomTypeId = $(this).closest('.roomTypeRooms').attr('id');
            var currentDate = moment(this.getAttribute('data-date'), "DD.MM.YYYY");
            var templatePackageElement = ChessBoardManager.getTemplateElement();
            var packageElementsContainer = document.createElement('div');
            var packagesByCurrentDate = self.dataManager.getNoAccommodationPackagesByDate(currentDate, roomTypeId);
            var isDragged = false;
            var relocatablePackage;
            var relocatablePackageData;
            var $wrapper = $('#calendarWrapper');
            var wrapperTopOffset = parseInt($wrapper.offset().top, 10);
            var $popover = openedPopovers.last();
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
                    }).on('mousedown touchstart', function (event) {
                        if (self.isIntervalAvailable(packageData, event)) {
                            relocatablePackage = this;
                            $wrapper.append(this);
                            this.style.position = 'absolute';
                            this.setAttribute('unplaced', true);
                            relocatablePackageData = self.dataManager.getNoAccommodationIntervalById(this.id);
                            var intervalStartDate = ChessBoardManager.getMomentDate(relocatablePackageData.begin);
                            this.style.left = self.getPackageLeftOffset(intervalStartDate, this) + 'px';
                            var pageY = event.type === 'touchstart' ? event.originalEvent.touches[0].pageY : event.pageY;
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
            var currentPopover = $popover.get(0);
            var popoverOffset = currentPopover.offsetWidth - ChessBoardManager.POPOVER_MIN_WIDTH;
            if (popoverOffset !== 0) {
                currentPopover.style.left = (parseInt(currentPopover.style.left, 10) - popoverOffset / 2) + 'px';
            }
        });
    };
    ChessBoardManager.prototype.isIntervalAvailable = function (packageData, event, isDivide) {
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
            if (this.getIntervalWidth(packageData) > this.getTableWidth()) {
                ActionManager.callIntervalToLargeModal(packageData.packageId);
                event.preventDefault();
                return false;
            }
        }
        return true;
    };
    ChessBoardManager.prototype.getIntervalWidth = function (intervalData) {
        var packageStartDate = ChessBoardManager.getMomentDate(intervalData.begin);
        var packageEndDate = ChessBoardManager.getMomentDate(intervalData.end);
        var packageCellCount = packageEndDate.diff(packageStartDate, 'days');
        return packageCellCount * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
    };
    ChessBoardManager.prototype.getTableWidth = function () {
        var chessboardStyles = getComputedStyle(document.getElementById('accommodation-chessBoard-content'));
        var chessboardWidth = parseInt(chessboardStyles.width, 10);
        return chessboardWidth - (!isMobileDevice() ? styleConfigs[this.currentSizeConfigNumber].headerWidth : 0);
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
                    var dateLeftRoomsCount = leftRoomCounts[roomTypeId][i];
                    var backgroundColor = self.colors['leftRoomsPositive'];
                    if (dateLeftRoomsCount == 0) {
                        backgroundColor = self.colors['leftRoomsZero'];
                    }
                    else if (dateLeftRoomsCount < 0) {
                        backgroundColor = self.colors['leftRoomsNegative'];
                    }
                    dateElements[i].style.backgroundColor = backgroundColor;
                    dateElements[i].children[0].innerHTML = dateLeftRoomsCount;
                    dateElements[i].setAttribute('data-toggle', "tooltip");
                    var toolTipTitle = Translator.trans('chessboard_manager.left_rooms_count.tooltip_title', { 'count': dateLeftRoomsCount });
                    dateElements[i].setAttribute('data-original-title', toolTipTitle);
                    dateElements[i].setAttribute('data-placement', "bottom");
                    dateElements[i].setAttribute('data-container', 'body');
                }
            }
        });
    };
    ChessBoardManager.prototype.updatePackagesData = function () {
        this.canMoveAccommodation = true;
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
        removeButton.setAttribute('data-container', 'body');
        removeButton.classList.add('remove-package-button');
        removeButton.innerHTML = '<i class="fa fa-times" aria-hidden="true"></i>';
        return removeButton;
    };
    ChessBoardManager.getTemplateDivideButton = function () {
        var divideButton = document.createElement('button');
        divideButton.setAttribute('type', 'button');
        divideButton.setAttribute('title', Translator.trans('chessboard_manager.divide_button.popup'));
        divideButton.setAttribute('data-toggle', 'tooltip');
        divideButton.setAttribute('data-container', 'body');
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
    ChessBoardManager.prototype.updateChessboardDataWithoutActions = function () {
        var _this = this;
        var time = 0;
        $(document).on('click', function () {
            time = 0;
        });
        setInterval(function () {
            time++;
            if (time > 30) {
                ActionManager.showLoadingIndicator();
                _this.dataManager.updatePackagesData();
                ActionManager.hideLoadingIndicator();
                time = 0;
            }
        }, 1000);
    };
    ChessBoardManager.getClickEventType = function () {
        return isMobileDevice() ? 'touchstart' : 'click';
    };
    ChessBoardManager.prototype.hangOnHideFieldButtonClick = function () {
        var _this = this;
        var changeVisibilityFunc = function (element) {
            var $select2Elements = $(element.parentNode).find('span.select2-container, select');
            var isVisible = $select2Elements.eq(0).css('display') !== 'none';
            $select2Elements.each(function (index, selectElement) {
                _this.setImportantStyle($(selectElement), 'display', isVisible ? 'none' : 'inline-block');
            });
            element.style.color = !isVisible ? 'inherit' : 'red';
        };
        var $hideFieldButtons = $('.hide-field-button');
        if (isMobileDevice()) {
            $hideFieldButtons.each(function (index, element) {
                changeVisibilityFunc(element);
            });
        }
        $hideFieldButtons.on(ChessBoardManager.getClickEventType(), function (event) {
            changeVisibilityFunc(event.target);
            //prevent touch on filter button after element is hidden
            event.preventDefault();
        });
    };
    ChessBoardManager.PACKAGE_FONT_SIZE_WIDTH = 8;
    ChessBoardManager.POPOVER_MIN_WIDTH = 250;
    ChessBoardManager.SCROLL_BAR_WIDTH = 16;
    ChessBoardManager.LATE_CHECKOUT_EARLY_CHECKIN_COLOR = '#65619b';
    return ChessBoardManager;
}());
//# sourceMappingURL=ChessBoardManager.js.map