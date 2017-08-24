///<reference path="DataManager.ts"/>
var ChessBoardManager = (function () {
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
        $reportFilter.find('#filter-button').click(function () {
            $reportFilter.submit();
        });
        this.onAddGuestClick();
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
                newPackage = self.setPackageOffset(newPackage, startDate, dateJqueryObject.parent().parent(), wrapper);
                newPackage.id = 'newPackage' + packages.length;
                newPackage.style.width = styleConfigs[self.currentSizeConfigNumber].tableCellWidth + 'px';
                var newPackageStartXOffset = parseInt(newPackage.style.left, 10);
                document.onmousemove = function (event) {
                    var scrollOffset = chessBoardContentBlock.scrollLeft - startLeftScroll;
                    var mouseXOffset = startXPosition - event.pageX;
                    var isLeftMouseShift = mouseXOffset > 0;
                    var packageLengthRestriction = self.getPackageLengthRestriction(startDate, isLeftMouseShift, self.tableStartDate, self.tableEndDate);
                    var griddedOffset = self.getGriddedOffset(mouseXOffset, scrollOffset, packageLengthRestriction);
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
                        var packageData = self.getPackageData($(newPackage));
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
            if (_this.currentSizeConfigNumber !== sliderValue) {
                ChessBoardManager.setCookie('chessboardSizeNumber', sliderValue);
                _this.currentSizeConfigNumber = sliderValue;
                window.location.reload();
            }
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
    // protected removeArrowsNearAnotherAccommodation() {
    //     $('.package').each((index, element) => {
    //         let accommodationNeighbors = this.dataManager.getAccommodationNeighbors(element.id);
    //         if (accommodationNeighbors['left']) {
    //             element.classList.remove('package-with-left-arrow');
    //             element.classList.add('near-left-element');
    //         }
    //         if (accommodationNeighbors['right']) {
    //             element.classList.remove('package-with-right-arrow');
    //             element.classList.add('near-right-element');
    //         }
    //     });
    // }
    ChessBoardManager.prototype.getPackageLengthRestriction = function (startDate, isLeftMouseShift, tableStartDate, tableEndDate) {
        'use strict';
        if (isLeftMouseShift) {
            return startDate.diff(tableStartDate, 'days') * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
        }
        return tableEndDate.diff(startDate, 'days') * styleConfigs[this.currentSizeConfigNumber].tableCellWidth;
    };
    ChessBoardManager.prototype.addAccommodationElements = function () {
        var wrapper = $('#calendarWrapper');
        var templatePackageElement = ChessBoardManager.getTemplateElement();
        var packages = document.createElement('div');
        //iterate packages
        var accommodationsData = this.dataManager.getAccommodations();
        var lastAddedElement = null;
        var accommodationElementIndex = Object.keys(accommodationsData).length + 100;
        for (var accommodationId in accommodationsData) {
            if (accommodationsData.hasOwnProperty(accommodationId)) {
                var accommodationData = accommodationsData[accommodationId];
                if (accommodationData.accommodation) {
                    var packageDiv = this.createPackageElementWithOffset(templatePackageElement, accommodationData, wrapper, accommodationElementIndex);
                    lastAddedElement = packageDiv;
                    packages.appendChild(packageDiv);
                }
            }
            accommodationElementIndex--;
        }
        wrapper.append(packages);
        this.addListeners('.package');
        this.updateAccommodationsWithNeighbors();
        // this.removeArrowsNearAnotherAccommodation();
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
        var description = document.createElement('div');
        var packageName = (packageItem.payer) ? packageItem.payer : packageItem.number;
        var descriptionText = packageName ? packageName.substr(0, packageCellCount * 5 - 4) : '';
        packageDiv.setAttribute('data-description', descriptionText);
        packageDiv.setAttribute('data-package-id', packageItem.packageId);
        description.innerHTML = descriptionText;
        description.classList.add('package-description');
        packageDiv.appendChild(description);
        description.style.width = Math.floor(descriptionText.length * ChessBoardManager.PACKAGE_FONT_SIZE_WIDTH) + 'px';
        packageDiv.classList.add(packageItem.paidStatus);
        //
        // description.setAttribute('data-toggle', 'popover');
        // description.setAttribute('data-html', "true");
        // description.setAttribute('data-container', "body");
        // description.setAttribute('title', packageItem.number);
        // description.setAttribute('data-placement', 'top');
        // let descriptionPopoverContent = '<b>Номер</b>:' + packageItem.number
        //     + (packageItem.payer ? '<br><b>Плательщик: </b>' + packageItem.payer : '')
        //     + '<br><b>Цена: </b>' + packageItem.price
        //     + '<br><b>Заезд брони: </b>' + packageItem.packageBegin
        //     + '<br><b>Выезд брони: </b>' + packageItem.packageBegin
        //     + '<br><b>Заехали: </b>' + (packageItem.isCheckIn ? 'да' : 'нет')
        //     + '<br><b>Выехали: </b>' + (packageItem.packageBegin ? 'да' : 'нет');
        // description.setAttribute('data-content', descriptionPopoverContent);
        // packageDiv.onmousemove = function () {
        //     let descriptionElement = this.getElementsByClassName('package-description')[0];
        //     let popoverId = descriptionElement.getAttribute('aria-describedby');
        //     if (popoverId == null) {
        //         $(descriptionElement).popover('show');
        //     }
        // };
        // packageDiv.onmouseleave = function () {
        //     let descriptionElement = this.getElementsByClassName('package-description')[0];
        //     $(descriptionElement).popover('hide');
        // };
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
                && !this.isAbroadRightTableSide(packageEndDate)) {
                $(packageDiv).find('.package-action-buttons').append(this.templateDivideButton.cloneNode(true));
            }
            if (packageItem.removePackage && (packageItem.position == 'full' || packageItem.position == 'right')) {
                $(packageDiv).find('.package-action-buttons').append(this.templateRemoveButton.cloneNode(true));
            }
        }
        packageDiv.style.zIndex = accommodationElementIndex;
        return packageDiv;
    };
    ChessBoardManager.prototype.createPackageElementWithOffset = function (templatePackageElement, packageItem, wrapper, accommodationElementIndex) {
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
        if (elementWidth < styleConfigs[this.currentSizeConfigNumber].tableCellWidth) {
            var divideButton = element.querySelector('.divide-package-button');
            if (divideButton) {
                divideButton.parentNode.removeChild(divideButton);
            }
            descriptionElement.parentNode.removeChild(descriptionElement);
        }
        return element;
    };
    ChessBoardManager.prototype.updateAccommodationsWithNeighbors = function () {
        var _this = this;
        var $rightResizable = $('.ui-resizable-e');
        $rightResizable.each(function (index, resizableElement) {
            var accommodationElement = resizableElement.parentNode;
            var accommodationId = accommodationElement.id;
            var accommodationNeighbors = _this.dataManager.getAccommodationNeighbors(accommodationId);
            var hasRightNeighborResizable = false;
            var hasLeftNeighborResizable = false;
            var $accommodation = $(accommodationElement);
            if (accommodationNeighbors['left']) {
                var leftNeighborData = accommodationNeighbors['left'];
                var leftNeighbor = document.getElementById(leftNeighborData.id);
                if (leftNeighbor.getElementsByClassName('ui-resizable-e')) {
                    hasLeftNeighborResizable = true;
                }
            }
            if (accommodationNeighbors['right']) {
                var rightNeighborData = accommodationNeighbors['right'];
                var rightNeighbor = document.getElementById(rightNeighborData.id);
                if (rightNeighbor.getElementsByClassName('ui-resizable-w')) {
                    hasRightNeighborResizable = true;
                }
            }
            if (hasLeftNeighborResizable) {
                accommodationElement.classList.remove('package-with-left-arrow');
                accommodationElement.classList.add('near-left-element');
                accommodationElement.style.width =
                    parseInt(accommodationElement.style.width, 10) + _this.arrowWidth + 'px';
                accommodationElement.style.left =
                    parseInt(accommodationElement.style.left, 10) - _this.arrowWidth + 'px';
            }
            if (hasRightNeighborResizable) {
                $(resizableElement).hide();
                accommodationElement.classList.add('near-right-element');
                accommodationElement.classList.remove('package-with-right-arrow');
                var accommodationWidth = $accommodation.width() + _this.arrowWidth;
                $accommodation.width(accommodationWidth);
            }
            if (hasRightNeighborResizable || hasLeftNeighborResizable) {
                var accommodationWidth_1 = $accommodation.width();
                var accommodationLeft_1 = parseInt($accommodation.css('left'), 10);
                accommodationElement.onmousemove = (function () {
                    if (hasRightNeighborResizable) {
                        $(resizableElement).show();
                    }
                    if (hasLeftNeighborResizable) {
                        $accommodation.css('left', accommodationLeft_1 + _this.distanceByHovering);
                    }
                    $accommodation.width(accommodationWidth_1
                        - (hasLeftNeighborResizable ? _this.distanceByHovering : 0)
                        - (hasRightNeighborResizable ? _this.distanceByHovering : 0));
                });
                accommodationElement.onmouseleave = (function () {
                    if (hasRightNeighborResizable) {
                        $(resizableElement).hide();
                    }
                    if (hasLeftNeighborResizable) {
                        $($accommodation).css('left', accommodationLeft_1);
                    }
                    $accommodation.width(accommodationWidth_1);
                });
            }
        });
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
            $element.find('.divide-package-button').click(function (event) {
                self.canMoveAccommodation = false;
                var scissorIcon = event.target;
                if (intervalData.viewPackage) {
                    scissorIcon.onclick = function () {
                        self.updatePackagesData();
                    };
                    var accommodationWidth_2 = parseInt(element.style.width, 10);
                    var tableCellWidth_1 = styleConfigs[self.currentSizeConfigNumber].tableCellWidth;
                    if (accommodationWidth_2 == tableCellWidth_1 * 2) {
                        $('.divide-package-button').tooltip('hide');
                        self.divide(element, accommodationWidth_2 / 2);
                    }
                    else {
                        var packageLeftCoordinate_1 = element.getBoundingClientRect().left;
                        var line_1 = document.createElement('div');
                        line_1.classList.add('dividing-line');
                        var accommodationElementWidth = parseInt(getComputedStyle(element).width, 10);
                        var isAccommodationAbroadTable_1 = (accommodationElementWidth % tableCellWidth_1) != 0
                            && ((accommodationElementWidth + 1) % tableCellWidth_1) != 0;
                        var packageToMiddayOffset_1 = self.getPackageToMiddayOffset();
                        var defaultLeftValue_1 = isAccommodationAbroadTable_1
                            ? tableCellWidth_1 + packageToMiddayOffset_1
                            : tableCellWidth_1;
                        line_1.style.left = defaultLeftValue_1 + 'px';
                        element.appendChild(line_1);
                        element.onmousemove = function (event) {
                            var offset = event.clientX - packageLeftCoordinate_1;
                            var griddedOffset;
                            if (isAccommodationAbroadTable_1) {
                                griddedOffset = Math.floor(Math.abs(offset) / tableCellWidth_1)
                                    * tableCellWidth_1
                                    + packageToMiddayOffset_1;
                            }
                            else {
                                griddedOffset = Math.floor(Math.abs(offset + packageToMiddayOffset_1)
                                    / tableCellWidth_1) * tableCellWidth_1;
                            }
                            if (griddedOffset == 0) {
                                griddedOffset += defaultLeftValue_1;
                            }
                            else if (griddedOffset == accommodationWidth_2) {
                                griddedOffset -= tableCellWidth_1;
                            }
                            line_1.style.left = griddedOffset + 'px';
                            element.onclick = function () {
                                element.onmousemove = null;
                                element.removeChild(line_1);
                                self.divide(this, griddedOffset);
                            };
                        };
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
                        }
                        else {
                            ui.position.top = self.getGriddedHeightValue(ui.position.top + styleConfigs[self.currentSizeConfigNumber].tableCellHeight / 2);
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
            return ChessBoardManager.isOffsetsEqual(packageOffset.top, $(element).offset().top);
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
                            self.actionManager.callUpdatePackageModal($(this), intervalData, changedSide);
                        }
                    }
                }
            });
        }
        if (intervalData.isLateCheckOut && resizableHandlesValue.indexOf('e') > -1) {
            this.addServicesDisplaying($element, '.ui-resizable-e', 'late-check-out-block');
        }
        if (intervalData.isEarlyCheckIn && resizableHandlesValue.indexOf('w') > -1) {
            this.addServicesDisplaying($element, '.ui-resizable-w', 'early-checkin-block');
        }
        return $element;
    };
    ChessBoardManager.prototype.addServicesDisplaying = function ($element, sideElemBlockClass, addedClass) {
        var $sideElement = $element.find(sideElemBlockClass);
        if ($sideElement.length > 0) {
            $sideElement.addClass(addedClass);
        }
        else {
            var laterCheckOutBlock = document.createElement('div');
            laterCheckOutBlock.classList.add(addedClass);
            $element.append(laterCheckOutBlock);
        }
    };
    /**
     * Получение данных о брони на основании данных о текущем положении элемента, отображающего бронь.
     * @param $packageElement
     * @returns {{id, accommodation: any, roomType: string, begin: string, end: string, payer: any}}
     */
    ChessBoardManager.prototype.getPackageData = function ($packageElement) {
        var packageOffset = $packageElement.offset();
        var roomLine = $('.roomDates, .leftRoomsLine').filter(function () {
            return ChessBoardManager.isOffsetsEqual($(this).offset().top, packageOffset.top);
        });
        var roomTypeId = roomLine.parent().get(0).id || roomLine.get(0).getAttribute('data-roomtypeid');
        var accommodationId = roomLine.children().get(0).id;
        if (accommodationId.substring(0, 16) === 'no_accommodation') {
            accommodationId = '';
        }
        var dateElements = roomLine.children().children();
        var description = $packageElement.find('.package-description').text();
        var startDateLeftOffset = packageOffset.left - this.getPackageToMiddayOffset();
        var startDate = this.getDateStringByLeftOffset(dateElements, startDateLeftOffset);
        var endDateLeftOffset = packageOffset.left + parseInt($packageElement.get(0).style.width, 10) - this.getPackageToMiddayOffset();
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
    ChessBoardManager.prototype.getDateStringByLeftOffset = function (dateElements, leftOffset) {
        var dateElement = dateElements.filter(function () {
            return ChessBoardManager.isOffsetsEqual($(this).offset().left, leftOffset);
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
        var $popoverElements = $('.no-accommodation-date.achtung');
        $popoverElements.unbind('shown.bs.popover');
        $popoverElements.on('shown.bs.popover', function () {
            var lastPackage = $('.package').last();
            if (lastPackage.attr('unplaced')) {
                lastPackage.remove();
            }
            var openedPopovers = $('.popover');
            openedPopovers.not(':last').remove();
            var roomTypeId = this.parentNode.parentNode.parentNode.parentNode.id;
            var currentDate = moment(this.getAttribute('data-date'), "DD.MM.YYYY");
            var templatePackageElement = self.getTemplateElement();
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
                    }).mousedown(function (event) {
                        if (self.isIntervalAvailable(packageData)) {
                            relocatablePackage = this;
                            $wrapper.append(this);
                            this.style.position = 'absolute';
                            this.setAttribute('unplaced', true);
                            relocatablePackageData = self.dataManager.getNoAccommodationIntervalById(this.id);
                            var intervalStartDate = ChessBoardManager.getMomentDate(relocatablePackageData.begin);
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
            var currentPopover = $popover.get(0);
            var popoverOffset = currentPopover.offsetWidth - ChessBoardManager.POPOVER_MIN_WIDTH;
            if (popoverOffset !== 0) {
                currentPopover.style.left = (parseInt(currentPopover.style.left, 10) - popoverOffset / 2) + 'px';
            }
        });
    };
    ChessBoardManager.prototype.isIntervalAvailable = function (packageData, isDivide) {
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
        var styles = getComputedStyle(document.getElementById('accommodation-chessBoard-content'));
        return parseInt(styles.width, 10) - styleConfigs[this.currentSizeConfigNumber].headerWidth;
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
                    var backgroundColor = 'yellowgreen';
                    if (dateLeftRoomsCount == 0) {
                        backgroundColor = 'rgba(243, 156, 18, 0.66)';
                    }
                    else if (dateLeftRoomsCount < 0) {
                        backgroundColor = 'rgba(221, 75, 57, 0.6)';
                    }
                    dateElements[i].children[0].style.backgroundColor = backgroundColor;
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
    return ChessBoardManager;
}());
ChessBoardManager.PACKAGE_FONT_SIZE_WIDTH = 8;
ChessBoardManager.POPOVER_MIN_WIDTH = 250;
ChessBoardManager.SCROLL_BAR_WIDTH = 16;
//# sourceMappingURL=ChessBoardManager.js.map