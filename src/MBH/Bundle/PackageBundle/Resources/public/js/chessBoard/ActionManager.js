///<reference path="DataManager.ts"/>
///<reference path="ChessBoardManager.ts"/>
var ActionManager = /** @class */ (function () {
    function ActionManager(dataManager) {
        this.dataManager = dataManager;
    }
    ActionManager.prototype.callRemoveConfirmationModal = function (packageId) {
        var self = this;
        var $packageDeleteModal = $('#modal_delete_package');
        $packageDeleteModal.modal('show');
        var $modalContainer = $('#delete-modal-form-container');
        $modalContainer.html(mbh.loader.html);
        $.ajax({
            url: Routing.generate('package_delete', { 'id': packageId }),
            type: "GET",
            success: function (modalBodyHTML) {
                $modalContainer.html(modalBodyHTML);
                $('select#mbh_bundle_packagebundle_delete_reason_type_deleteReason').select2();
                var $removeButton = $packageDeleteModal.find('#package-delete-modal-button');
                $removeButton.attr('type', 'button');
                var clickEventType = ChessBoardManager.getClickEventType();
                $removeButton.unbind(clickEventType);
                $removeButton.on(clickEventType, function () {
                    self.dataManager.deletePackageRequest(packageId, $modalContainer, $packageDeleteModal);
                });
            }
        });
    };
    ActionManager.callUnblockModal = function (packageId) {
        var $unblockModal = $('#entity-delete-confirmation');
        $unblockModal.find('.modal-title').text(Translator.trans('action_manager.modal.package_blocked.title') + '!');
        $unblockModal.find('#entity-delete-modal-text').text(Translator.trans('action_manager.modal.package_blocked.text') + '.');
        $unblockModal.find('#entity-delete-button').hide();
        ActionManager.addEditButton($unblockModal, packageId);
        $unblockModal.modal('show');
    };
    ActionManager.showLoadingIndicator = function () {
        $('#dimmer').show();
        $('#loading-indicator').show();
    };
    ActionManager.hideLoadingIndicator = function () {
        $('#dimmer').hide();
        $('#loading-indicator').hide();
    };
    ActionManager.prototype.callPackageInfoModal = function (accommodationId) {
        this.dataManager.getPackageDataRequest(accommodationId);
        this.dataManager.getPackageDataRequest(accommodationId);
    };
    ActionManager.prototype.handleSearchOptionsModal = function (packageData, searchData) {
        var self = this;
        var editBody = $('#package-new-results');
        editBody.html(searchData);
        editBody.find('.search-room-select').val(packageData.accommodation);
        editBody.find('td:nth-child(4)').remove();
        editBody.find('thead th:nth-child(4)').remove();
        editBody.find('thead th').css('text-align', 'center');
        editBody.find('select').not("s[tourist]").select2();
        var editModal = $('#package-edit-modal');
        $('.btn.package-search-book').each(function (index, element) {
            self.modifyBookButton(packageData, element, editModal);
        });
        $('.search-special-apply').each(function (index, element) {
            self.modifySpecialButton(packageData, element, editModal);
        });
        self.modifyButtonsByGuest(editModal);
        $('#s_tourist').change(function () {
            self.modifyButtonsByGuest(editModal);
        });
        $('.package-search-table').find('tr').not(':first').not(':first').each(function (index, row) {
            var $row = $(row);
            ActionManager.showResultPrices($row);
            $row.find('.search-tourists-select').change(function () {
                ActionManager.showResultPrices($row);
            });
        });
        editBody.find('.search-room-select').prop('disabled', true);
        editModal.find('input.modalPackageId').val(packageData.id);
        editModal.modal('show');
        editModal.on('shown.bs.modal', function () {
            $('.findGuest').mbhGuestSelectPlugin();
        });
    };
    ActionManager.showResultPrices = function ($row) {
        if ($row.hasClass('info')) {
            return;
        }
        var $searchTouristsSelect = $row.find('.search-tourists-select');
        var $packageSearchBook = $row.find('.package-search-book');
        var touristVal = $searchTouristsSelect.val();
        var touristArr = touristVal.split('_');
        var ulPrices = $row.find('ul.package-search-prices');
        ulPrices.hide();
        ulPrices.find('li').hide();
        ulPrices.find('li.' + touristVal + '_price').show();
        ulPrices.show();
        var isNullAmount = parseInt($("#s_adults").val() || $("#s_children").val());
        if (!isNullAmount) {
            var oldHref = $packageSearchBook.attr('data-url')
                .replace(/&adults=.*?(?=(&|$))/, '')
                .replace(/&children=.*?(?=(&|$))/, '');
            $packageSearchBook.attr('data-url', oldHref + '&adults=' + touristArr[0] + '&children=' + touristArr[1]);
        }
        else {
            $searchTouristsSelect.attr("disabled", true);
        }
    };
    ActionManager.prototype.modifySpecialButton = function (packageData, element, editModal) {
        var self = this;
        $(element).on(ChessBoardManager.getClickEventType(), function () {
            event.preventDefault();
            var $searchPackageForm = $('#package-search-form');
            var specialId = element.classList.contains('cancel') ? null : element.getAttribute('data-id');
            var newPackageRequestData = ChessBoardManager.getNewPackageRequestData($searchPackageForm, specialId);
            editModal.modal('hide');
            setTimeout(function () {
                self.dataManager.getPackageOptionsRequest(newPackageRequestData, packageData);
            }, 250);
        });
    };
    ActionManager.prototype.modifyButtonsByGuest = function ($editModal) {
        var touristVal = $('#s_tourist').val();
        $editModal.find('.package-search-book').each(function (index, element) {
            var title;
            if (!touristVal && !canBookWithoutPayer) {
                element.setAttribute('disabled', true);
                title = Translator.trans('action_manager.modal.disabled_book_button.title');
            }
            else {
                var leftRoomsCount = $(element).parent().parent().find('.package-search-book-count').eq(0).text();
                title = Translator.trans('action_manager.modal.book_button.title', { 'roomsCount': leftRoomsCount });
                element.removeAttribute('disabled');
            }
            element.setAttribute('title', title);
            element.setAttribute('data-original-title', title);
            var url = element.getAttribute('data-url');
            url = url.replace(/&(s%5Btourist|tourist).*?(?=(&|$))/, '');
            if (touristVal) {
                url = url + '&tourist=' + touristVal;
            }
            element.setAttribute('data-url', url);
        });
    };
    ActionManager.prototype.modifyBookButton = function (packageData, element, editModal) {
        'use strict';
        var self = this;
        var newPackageCreateUrl = element.href;
        $(element).find('.package-search-book-reservation-text').hide();
        $(element).find('.package-search-book-accommodation-text').show();
        element.removeAttribute('href');
        var accommodationValue = document.getElementsByClassName('search-room-select')[0].value;
        if (accommodationValue) {
            newPackageCreateUrl += '&accommodation=' + accommodationValue;
        }
        element.setAttribute('data-url', newPackageCreateUrl);
        $(element).on(ChessBoardManager.getClickEventType(), function () {
            if (!element.getAttribute('disabled')) {
                var url = element.getAttribute('data-url');
                editModal.modal('hide');
                self.dataManager.createPackageRequest(url, packageData);
            }
        });
    };
    ActionManager.callIntervalBeginOutOfRangeModal = function (side) {
        var $alertModal = $('#entity-delete-confirmation');
        if (side == 'begin') {
            $alertModal.find('.modal-title').text(Translator.trans('action_manager.modal.package_begin_date_abroad.title') + '!');
            $alertModal.find('#entity-delete-modal-text').text(Translator.trans('action_manager.modal.package_begin_date_abroad.text') + '.');
        }
        else if (side == 'end') {
            $alertModal.find('.modal-title').text(Translator.trans('action_manager.modal.package_end_date_abroad.title') + '!');
            $alertModal.find('#entity-delete-modal-text').text(Translator.trans('action_manager.modal.package_end_date_abroad.text') + '.');
        }
        else if (side == 'both') {
            $alertModal.find('.modal-title').text(Translator.trans('action_manager.modal.package_begin_and_end_date_abroad.title') + '!');
            $alertModal.find('#entity-delete-modal-text').text(Translator.trans('action_manager.modal.package_begin_and_end_date_abroad.text') + '.');
        }
        $alertModal.find('#entity-delete-button').hide();
        $alertModal.modal('show');
    };
    ActionManager.callIntervalToLargeModal = function (packageId) {
        var $unblockModal = $('#entity-delete-confirmation');
        $unblockModal.find('.modal-title').text(Translator.trans('action_manager.modal.package_to_large.title') + '!');
        $unblockModal.find('#entity-delete-modal-text').text(Translator.trans('action_manager.modal.package_to_large.text') + '.');
        $unblockModal.find('#entity-delete-button').hide();
        ActionManager.addEditButton($unblockModal, packageId);
        $unblockModal.modal('show');
    };
    ActionManager.addEditButton = function ($modal, packageId) {
        var editButton = $modal.find('#package-info-modal-edit').eq(0);
        if (editButton.length == 0) {
            editButton = $('#package-info-modal-edit').clone();
            editButton.css('background-color', 'transparent');
            editButton.css('border', '1px solid #fff');
            editButton.css('color', '#fff');
            editButton.on(ChessBoardManager.getClickEventType(), function () {
                $modal.modal('hide');
            });
            editButton.appendTo($modal.find('.modal-footer'));
        }
        editButton.attr('href', Routing.generate('package_edit', { id: packageId }));
    };
    ActionManager.prototype.showPackageInfoModal = function (packageId, data) {
        var self = this;
        var packageInfoModal = $('#package-info-modal');
        var intervalData = this.dataManager.getPackageDataById(packageId);
        if (intervalData) {
            var $deleteButton = packageInfoModal.find('#package-info-modal-delete');
            if (intervalData.removePackage) {
                $deleteButton.on(ChessBoardManager.getClickEventType(), function () {
                    self.callRemoveConfirmationModal(packageId);
                    packageInfoModal.modal('hide');
                });
            }
            else {
                $deleteButton.hide();
            }
            var $editButton_1 = packageInfoModal.find('#package-info-modal-edit');
            $editButton_1.on(ChessBoardManager.getClickEventType(), function () {
                $editButton_1.attr('href', Routing.generate('package_edit', { id: packageId }));
            });
            packageInfoModal.find('#package-info-modal-body').html(data);
            packageInfoModal.modal('show');
        }
    };
    ActionManager.showResultMessages = function (response) {
        response.success.forEach(function (message) {
            ActionManager.showMessage(true, message);
        });
        response.errors.forEach(function (message) {
            ActionManager.showMessage(false, message);
        });
    };
    ActionManager.showInternalErrorMessage = function () {
        ActionManager.showMessage(false, Translator.trans('action_manager.message.unexpected_error') + '.');
        ActionManager.hideLoadingIndicator();
    };
    ActionManager.showMessage = function (isSuccess, message, messageBlockId) {
        if (messageBlockId === void 0) { messageBlockId = 'chessboard-messages'; }
        var messageDiv = document.createElement('div');
        messageDiv.className = 'alert alert-dismissable autohide';
        messageDiv.classList.add(isSuccess ? 'alert-success' : 'alert-danger');
        messageDiv.innerHTML = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
        messageDiv.innerHTML += message;
        messageDiv.style.width = getComputedStyle(document.getElementsByClassName('box')[0]).width;
        setTimeout(function () {
            if (messageDiv.parentElement) {
                messageDiv.parentElement.removeChild(messageDiv);
            }
        }, 12000);
        document.getElementById(messageBlockId).appendChild(messageDiv);
    };
    ActionManager.prototype.callUpdatePackageModal = function (packageElement, intervalData, changedSide, isDivide) {
        if (changedSide === void 0) { changedSide = null; }
        if (isDivide === void 0) { isDivide = false; }
        var $updateForm = $('#concise_package_update');
        $updateForm.show();
        var modalAlertDiv = document.getElementById('package-modal-change-alert');
        modalAlertDiv.innerHTML = '';
        var newIntervalData = this.dataManager.chessBoardManager.getPackageData(packageElement);
        var isAccommodationInAnotherRoomType = ActionManager.isAccommodationInAnotherRoomType(newIntervalData, intervalData);
        if (isAccommodationInAnotherRoomType || (intervalData && changedSide)) {
            var alertMessageData = this.getAlertMessage(newIntervalData, intervalData, isAccommodationInAnotherRoomType);
            if (alertMessageData) {
                ActionManager.showAlertMessage(alertMessageData, $updateForm);
            }
        }
        ActionManager.showEditedUpdateModal(intervalData, newIntervalData, isDivide, changedSide);
    };
    ActionManager.isNewAccommodationInAnotherRoomType = function (accommodationData, intervalData) {
        var isNewAccommodation = !intervalData.isAccommodationInterval
            || ActionManager.isPackageBeginChanged(accommodationData, intervalData)
            || ActionManager.isPackageEndChanged(accommodationData, intervalData);
        return ActionManager.isAccommodationInAnotherRoomType(accommodationData, intervalData) && isNewAccommodation;
    };
    ActionManager.isAccommodationInAnotherRoomType = function (accommodationData, intervalData) {
        return accommodationData.roomType !== intervalData.packageRoomTypeId;
    };
    ActionManager.prototype.getAlertMessage = function (newIntervalData, intervalData, isAccommodationInAnotherRoomType) {
        if (isAccommodationInAnotherRoomType) {
            var packageAccommodations = this.dataManager.getPackageAccommodations(intervalData.packageId);
            var existsAccommodationWithCurrentRoomType = packageAccommodations.some(function (accommodationData) {
                return accommodationData.packageRoomTypeId === accommodationData.roomTypeId;
            });
            var warningMessageId = existsAccommodationWithCurrentRoomType
                ? 'package_bundle.accommodations.warning_before_setting_accoommodation_with_existed_accommodation'
                : 'package_bundle.accommodations.warning_before_setting_accoommodation';
            return {
                message: Translator.trans(warningMessageId, {
                    accommodationRoomTypeName: roomTypes[newIntervalData.roomType],
                    packageRoomType: roomTypes[intervalData.packageRoomTypeId],
                    chessboard_route: Routing.generate('chess_board_home'),
                    packages_route: Routing.generate('package')
                }),
                resolved: true,
                modalContentClass: 'modal-danger'
            };
        }
        var packageBeginChanged = ActionManager.isPackageBeginChanged(newIntervalData, intervalData);
        var packageEndChanged = ActionManager.isPackageEndChanged(newIntervalData, intervalData);
        var packageBeginAndEndChanged = packageBeginChanged && packageEndChanged;
        var canUpdatePackage = intervalData.updatePackage;
        if (packageBeginAndEndChanged) {
            if (newIntervalData.accommodation) {
                if (canUpdatePackage) {
                    return {
                        message: Translator.trans('aciton_manager.modal.change_package_begin_and_end.confirmation') + '?',
                        resolved: true
                    };
                }
                else {
                    return {
                        message: Translator.trans('action_manager.modal.need_changepackage_begin_and_end') + '. '
                            + Translator.trans('action_manager.modal.have_not_rights') + '.',
                        resolved: false
                    };
                }
            }
        }
        else if (packageBeginChanged) {
            if (canUpdatePackage) {
                return {
                    message: Translator.trans('aciton_manager.modal.change_package_begin.confirmation') + '?',
                    resolved: true
                };
            }
            else {
                return {
                    message: Translator.trans('action_manager.modal.need_changepackage_begin.title') + '. '
                        + Translator.trans('action_manager.modal.have_not_rights') + '.',
                    resolved: false
                };
            }
        }
        else if (packageEndChanged) {
            if (canUpdatePackage) {
                return {
                    message: Translator.trans('aciton_manager.modal.change_package_end.confirmation') + '?',
                    resolved: true
                };
            }
            else {
                return {
                    message: Translator.trans('action_manager.modal.need_changepackage_end.title') + '. '
                        + Translator.trans('action_manager.modal.have_not_rights') + '.',
                    resolved: false
                };
            }
        }
    };
    ActionManager.isPackageEndChanged = function (newIntervalData, intervalData) {
        var intervalEndDate = ChessBoardManager.getMomentDate(intervalData.end);
        var newIntervalEndDate;
        if (intervalEndDate.isAfter(ChessBoardManager.getTableEndDate())) {
            newIntervalEndDate = ChessBoardManager.getMomentDate(intervalData.end);
        }
        else {
            newIntervalEndDate = ChessBoardManager.getMomentDate(newIntervalData.end);
        }
        var packageEndDate = ChessBoardManager.getMomentDate(intervalData.packageEnd);
        return ((intervalData.position == 'full' || intervalData.position == 'right')
            && newIntervalEndDate.isAfter(packageEndDate)
            || (intervalEndDate.isSame(packageEndDate) && newIntervalEndDate.isBefore(packageEndDate)));
    };
    ActionManager.isPackageBeginChanged = function (newIntervalData, intervalData) {
        var newIntervalStartDate = ChessBoardManager.getMomentDate(newIntervalData.begin);
        if (newIntervalStartDate.isBefore(ChessBoardManager.getTableStartDate())) {
            newIntervalStartDate = ChessBoardManager.getMomentDate(intervalData.begin);
        }
        var packageStartDate = ChessBoardManager.getMomentDate(intervalData.packageBegin);
        return ((intervalData.position == 'left' || intervalData.position == 'full')
            && !newIntervalStartDate.isSame(packageStartDate));
    };
    ActionManager.showAlertMessage = function (alertMessageData, $updateForm) {
        var $continueButton = $('#package-modal-continue-button');
        if (alertMessageData.resolved) {
            $continueButton.show();
        }
        var $modalAlertDiv = $('#package-modal-change-alert');
        $modalAlertDiv.html(alertMessageData.message);
        if (alertMessageData.modalContentClass) {
            var $modalContent_1 = $updateForm.closest('.modal-content');
            $modalContent_1.addClass(alertMessageData.modalContentClass);
            $('#package-modal-change-alert').removeClass('text-center');
            var onWithModalClassWindowClosed_1 = function () {
                $modalContent_1.removeClass(alertMessageData.modalContentClass);
                $('#package-modal-change-alert').addClass('text-center');
            };
            $continueButton.on(ChessBoardManager.getClickEventType(), function () {
                onWithModalClassWindowClosed_1();
            });
            $('#packageModal').on('hidden.bs.modal', function () {
                onWithModalClassWindowClosed_1();
            });
        }
        $modalAlertDiv.show();
        var $confirmButton = $('#packageModalConfirmButton');
        $confirmButton.hide();
        $updateForm.hide();
        $continueButton.on(ChessBoardManager.getClickEventType(), function () {
            ActionManager.onContinueButtonClick($modalAlertDiv, $confirmButton, $continueButton, $updateForm);
        });
    };
    ActionManager.showEditedUpdateModal = function (intervalData, newIntervalData, isDivide, changedSide) {
        var intervalBegin = newIntervalData.begin;
        var intervalEnd = newIntervalData.end;
        var newPackageBegin = ActionManager.isPackageBeginChanged(newIntervalData, intervalData) && !isDivide
            ? newIntervalData.begin : intervalData.packageBegin;
        var newPackageEnd = ActionManager.isPackageEndChanged(newIntervalData, intervalData) && !isDivide
            ? newIntervalData.end : intervalData.packageEnd;
        if ((changedSide == 'right')
            && ChessBoardManager.getMomentDate(intervalData.begin).isBefore(ChessBoardManager.getTableStartDate())) {
            //Если размещение расширяется вправо и левый край брони не помещается в таблицу
            intervalBegin = intervalData.begin;
            newPackageBegin = intervalData.packageBegin;
        }
        else if (changedSide == 'left'
            && ChessBoardManager.getMomentDate(intervalData.end).isAfter(ChessBoardManager.getTableEndDate())) {
            //Если размещение расширяется влево и правый край брони не помещается в таблицу
            newPackageEnd = intervalData.packageEnd;
            intervalEnd = intervalData.end;
        }
        else if (changedSide == 'both' && !newIntervalData.accommodation) {
            //Если удаляется единственное размещение брони
            newPackageBegin = intervalData.begin;
            newPackageEnd = intervalData.end;
            intervalBegin = intervalData.begin;
            intervalEnd = intervalData.end;
        }
        var modal = $('#packageModal');
        var packageId = intervalData.packageId;
        var intervalId = intervalData.accommodation ? intervalData.id : '';
        var payerText = intervalData.payer ? intervalData.payer : Translator.trans('action_manager.update_modal.not_specified');
        modal.find('input.modalBlockId').val(intervalData.id);
        modal.find('input.isDivide').val(isDivide);
        modal.find('input.modalPackageId').val(packageId);
        modal.find('input.modalAccommodationId').val(intervalId);
        modal.find('#modal-package-number').text(intervalData.number);
        modal.find('#modal-package-payer').text(payerText);
        modal.find('#modal-package-begin').text(newPackageBegin);
        modal.find('#modal-package-end').text(newPackageEnd);
        modal.find('#modal-begin-date').text(intervalBegin);
        modal.find('#modal-end-date').text(intervalEnd);
        modal.find('#modal-room-id').text(newIntervalData.accommodation);
        modal.find('#modal-room-type-name').text(roomTypes[newIntervalData.roomType]);
        modal.find('#modal-room-type-id').text(newIntervalData.roomType);
        modal.find('#modal-room-name').text(newIntervalData.accommodation
            ? rooms[newIntervalData.accommodation] : Translator.trans('action_manager.update_modal.without_accommodation'));
        modal.modal('show');
    };
    ActionManager.getDataFromUpdateModal = function () {
        var modal = $('#packageModal');
        var payerText;
        var payerName = modal.find('#modal-package-payer').text();
        if (payerName != Translator.trans('action_manager.update_modal.not_specified')) {
            payerText = payerName;
        }
        else {
            payerText = modal.find('#modal-package-payer').text();
        }
        return {
            'id': modal.find('input.modalBlockId').val(),
            'packageId': modal.find('input.modalPackageId').val(),
            'accommodationId': modal.find('input.modalAccommodationId').val(),
            'begin': modal.find('#modal-begin-date').text(),
            'end': modal.find('#modal-end-date').text(),
            'roomId': modal.find('#modal-room-id').text(),
            'roomTypeId': modal.find('#modal-room-type-id').text(),
            'isDivide': modal.find('input.isDivide').val(),
            'payer': payerText
        };
    };
    ActionManager.onContinueButtonClick = function ($modalAlertDiv, $confirmButton, $continueButton, $updateForm) {
        $continueButton.hide();
        $modalAlertDiv.hide();
        $confirmButton.show();
        $updateForm.show();
    };
    ActionManager.hidePackageUpdateModal = function () {
        $('#packageModal').modal('hide');
    };
    return ActionManager;
}());
//# sourceMappingURL=ActionManager.js.map