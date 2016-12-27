///<reference path="DataManager.ts"/>
///<reference path="ChessBoardManager.ts"/>
var ActionManager = (function () {
    function ActionManager(dataManager) {
        this.dataManager = dataManager;
    }
    ActionManager.prototype.callRemoveConfirmationModal = function (packageId) {
        var self = this;
        var $deleteConfirmationModal = $('#entity-delete-confirmation');
        $deleteConfirmationModal.find('.modal-title').text('Подтверждение удаления');
        $deleteConfirmationModal.find('#entity-delete-modal-text').text('Вы действительно хотите удалить эту бронь?');
        $deleteConfirmationModal.find('#entity-delete-button').click(function () {
            self.dataManager.deletePackageRequest(packageId);
            $deleteConfirmationModal.modal('hide');
        });
        $deleteConfirmationModal.modal('show');
    };
    ActionManager.prototype.callUnblockModal = function (packageId) {
        var $unblockModal = $('#entity-delete-confirmation');
        $unblockModal.find('.modal-title').text('Бронь заблокирована для изменений!');
        $unblockModal.find('#entity-delete-modal-text').text('Если вы хотите разблокировать эту бронь, перейдите в раздел редактирования брони.');
        $unblockModal.find('#entity-delete-button').hide();
        var editButton = $('#package-info-modal-edit').clone();
        editButton.css('background-color', 'transparent');
        editButton.css('border', '1px solid #fff');
        editButton.css('color', '#fff');
        editButton.attr('href', Routing.generate('package_edit', { id: packageId }));
        editButton.appendTo($unblockModal.find('.modal-footer'));
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
    };
    ActionManager.prototype.handleSearchOptionsModal = function (packageData, searchData) {
        var self = this;
        var editBody = $('#package-edit-body');
        editBody.html(searchData);
        editBody.find('.search-room-select').val(packageData.accommodation);
        editBody.find('td:nth-child(4)').remove();
        editBody.find('thead th:nth-child(4)').remove();
        editBody.find('thead th').css('text-align', 'center');
        editBody.find('select').select2();
        var editModal = $('#package-edit-modal');
        $('.btn.package-search-book').each(function (index, element) {
            self.modifyBookButton(packageData, element, editModal);
        });
        $('.package-search-table').find('tr').not(':first').not(':first').each(function (index, row) {
            var $row = $(row);
            ActionManager.showResultPrices($row);
            $row.find('.search-tourists-select').change(function () {
                ActionManager.showResultPrices($row);
            });
        });
        editModal.find('input.modalPackageId').val(packageData.id);
        editModal.modal('show');
    };
    ActionManager.showResultPrices = function ($row) {
        var $searchTouristsSelect = $row.find('.search-tourists-select');
        var $packageSearchBook = $row.find('.package-search-book');
        var touristVal = $searchTouristsSelect.val(), touristArr = touristVal.split('_');
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
    ActionManager.prototype.modifyBookButton = function (packageData, element, editModal) {
        'use strict';
        var self = this;
        var newPackageCreateUrl = element.href;
        element.removeAttribute('href');
        if (packageData.accommodation) {
            newPackageCreateUrl += '&accommodation=' + packageData.accommodation;
        }
        element.setAttribute('data-url', newPackageCreateUrl);
        element.onclick = function () {
            var url = element.getAttribute('data-url');
            self.dataManager.createPackageRequest(url, packageData);
            editModal.modal('hide');
        };
    };
    ActionManager.prototype.showPackageInfoModal = function (accommodationId, data) {
        var self = this;
        var packageInfoModal = $('#package-info-modal');
        packageInfoModal.find('#package-info-modal-edit').click(function () {
            var packageId = document.getElementById('package_info_package_id').value;
            packageInfoModal.find('#package-info-modal-edit').attr('href', Routing.generate('package_edit', { id: packageId }));
        });
        packageInfoModal.find('#package-info-modal-delete').click(function () {
            self.callRemoveConfirmationModal(accommodationId);
            packageInfoModal.modal('hide');
        });
        packageInfoModal.find('#package-info-modal-body').html(data);
        packageInfoModal.modal('show');
    };
    ActionManager.showResultMessages = function (response) {
        response.success.forEach(function (message) {
            ActionManager.showMessage(true, message);
        });
        response.errors.forEach(function (message) {
            ActionManager.showMessage(false, message);
        });
    };
    ActionManager.showHappenedUnWaitedMessage = function () {
        ActionManager.showMessage(false, 'Произошла непредвиденная ошибка');
        ActionManager.hideLoadingIndicator();
    };
    ActionManager.showMessage = function (isSuccess, message, messageBlockId) {
        if (messageBlockId === void 0) { messageBlockId = 'messages'; }
        var messageDiv = document.createElement('div');
        messageDiv.className = 'alert alert-dismissable autohide';
        messageDiv.classList.add(isSuccess ? 'alert-success' : 'alert-danger');
        messageDiv.innerHTML = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
        messageDiv.innerHTML += message;
        setTimeout(function () {
            if (messageDiv.parentElement) {
                messageDiv.parentElement.removeChild(messageDiv);
            }
        }, 12000);
        document.getElementById(messageBlockId).appendChild(messageDiv);
    };
    ActionManager.callUpdatePackageModal = function (packageElement, intervalData, changedSide, isDivide) {
        if (changedSide === void 0) { changedSide = null; }
        if (isDivide === void 0) { isDivide = false; }
        var $updateForm = $('#concise_package_update');
        $updateForm.show();
        var modalAlertDiv = document.getElementById('package-modal-change-alert');
        modalAlertDiv.innerHTML = '';
        var newIntervalData = ChessBoardManager.getPackageData(packageElement);
        if (intervalData && changedSide) {
            var alertMessage = void 0;
            if (changedSide == 'right') {
                var packageEndDate = ChessBoardManager.getMomentDate(intervalData.packageEnd);
                var intervalEndDate = ChessBoardManager.getMomentDate(intervalData.end);
                var newIntervalEndDate = moment(newIntervalData.end, "DD.MM.YYYY");
                if (intervalData.position == 'full' || intervalData.position == 'right') {
                    if (newIntervalEndDate.isAfter(packageEndDate)
                        || (intervalEndDate.isSame(packageEndDate) && newIntervalEndDate.isBefore(packageEndDate))) {
                        alertMessage = 'Вы действительно хотите изменить дату выезда брони?';
                    }
                }
                else {
                    //TODO: Текст ошибки
                    throw new Error('');
                }
            }
            else if (changedSide == 'left') {
                var newIntervalStartDate = moment(newIntervalData.begin, "DD.MM.YYYY");
                var packageStartDate = ChessBoardManager.getMomentDate(intervalData.begin);
                if (intervalData.position == 'left' || intervalData.position == 'full') {
                    if (!newIntervalStartDate.isSame(packageStartDate)) {
                        alertMessage = 'Вы хотите изменить дату заезда брони?';
                    }
                }
                else {
                }
            }
            else {
            }
            if (alertMessage) {
                var $continueButton_1 = $('#package-modal-continue-button');
                $continueButton_1.show();
                var $modalAlertDiv_1 = $('#package-modal-change-alert');
                $modalAlertDiv_1.text(alertMessage);
                $modalAlertDiv_1.show();
                var $confirmButton_1 = $('#packageModalConfirmButton');
                $confirmButton_1.hide();
                $updateForm.hide();
                $continueButton_1.click(function () {
                    ActionManager.onContinueButtonClick($modalAlertDiv_1, $confirmButton_1, $continueButton_1, $updateForm);
                });
            }
        }
        var modal = $('#packageModal');
        var packageId = intervalData.packageId ? intervalData.packageId : intervalData.id;
        var accommodationId = intervalData.packageId ? intervalData.id : '';
        var payerText = intervalData.payer ? intervalData.payer : 'Не указан';
        modal.find('input.isDivide').val(isDivide);
        modal.find('input.modalPackageId').val(packageId);
        modal.find('input.modalAccommodationId').val(accommodationId);
        modal.find('#modal-package-number').text(intervalData.number);
        modal.find('#modal-package-payer').text(payerText);
        modal.find('#modal-package-begin').text(ChessBoardManager.getMomentDate(intervalData.packageBegin).format("DD.MM.YYYY"));
        modal.find('#modal-package-end').text(ChessBoardManager.getMomentDate(intervalData.packageEnd).format("DD.MM.YYYY"));
        modal.find('#modal-begin-date').text(newIntervalData.begin);
        modal.find('#modal-end-date').text(newIntervalData.end);
        modal.find('#modal-room-id').text(newIntervalData.accommodation);
        modal.find('#modal-room-type-name').text(roomTypes[newIntervalData.roomType]);
        modal.find('#modal-room-name').text(newIntervalData.accommodation ? rooms[newIntervalData.accommodation] : 'Без размещения');
        modal.modal('show');
    };
    ActionManager.getDataFromUpdateModal = function () {
        var modal = $('#packageModal');
        return {
            'packageId': modal.find('input.modalPackageId').val(),
            'accommodationId': modal.find('input.modalAccommodationId').val(),
            'begin': modal.find('#modal-begin-date').text(),
            'end': modal.find('#modal-end-date').text(),
            'roomId': modal.find('#modal-room-id').text(),
            'isDivide': modal.find('input.isDivide').val()
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