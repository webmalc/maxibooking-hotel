///<reference path="DataManager.ts"/>
///<reference path="ChessBoardManager.ts"/>
declare var $;
declare var roomTypes;
declare var rooms;

class ActionManager {

    private dataManager;

    constructor(dataManager) {
        this.dataManager = dataManager;
    }

    public callRemoveConfirmationModal(packageId) {
        let self = this;
        var $deleteConfirmationModal = $('#entity-delete-confirmation');
        $deleteConfirmationModal.find('.modal-title').text('Подтверждение удаления');
        $deleteConfirmationModal.find('#entity-delete-modal-text').text('Вы действительно хотите удалить эту бронь?');
        $deleteConfirmationModal.find('#entity-delete-button').click(function () {
            self.dataManager.deletePackageRequest(packageId);
            $deleteConfirmationModal.modal('hide');
        });
        $deleteConfirmationModal.modal('show');
    }

    public static callUnblockModal(packageId) {
        var $unblockModal = $('#entity-delete-confirmation');
        $unblockModal.find('.modal-title').text('Бронь заблокирована для изменений!');
        $unblockModal.find('#entity-delete-modal-text').text('Если вы хотите разблокировать эту бронь, перейдите в раздел редактирования брони.');
        $unblockModal.find('#entity-delete-button').hide();
        let editButton = $('#package-info-modal-edit').clone();
        editButton.css('background-color', 'transparent');
        editButton.css('border', '1px solid #fff');
        editButton.css('color', '#fff');
        editButton.attr('href', Routing.generate('package_edit', {id: packageId}));
        editButton.appendTo($unblockModal.find('.modal-footer'));
        $unblockModal.modal('show');
    }

    public static showLoadingIndicator() {
        $('#dimmer').show();
        $('#loading-indicator').show();
    }

    public static hideLoadingIndicator() {
        $('#dimmer').hide();
        $('#loading-indicator').hide();
    }

    public callPackageInfoModal(accommodationId) {
        this.dataManager.getPackageDataRequest(accommodationId);
    }

    public handleSearchOptionsModal(packageData, searchData) {
        let self = this;

        var editBody = $('#package-edit-body');
        editBody.html(searchData);
        editBody.find('.search-room-select').val(packageData.accommodation);
        editBody.find('td:nth-child(4)').remove();
        editBody.find('thead th:nth-child(4)').remove();
        editBody.find('thead th').css('text-align', 'center');
        editBody.find('select').select2();

        let editModal = $('#package-edit-modal');

        $('.btn.package-search-book').each(function (index, element) {
            self.modifyBookButton(packageData, element, editModal);
        });

        $('.package-search-table').find('tr').not(':first').not(':first').each(function (index, row) {
            let $row = $(row);
            ActionManager.showResultPrices($row);
            $row.find('.search-tourists-select').change(function () {
                ActionManager.showResultPrices($row);
            })
        });

        editModal.find('input.modalPackageId').val(packageData.id);
        editModal.modal('show');
    }

    private static showResultPrices($row) {
        let $searchTouristsSelect = $row.find('.search-tourists-select');
        let $packageSearchBook = $row.find('.package-search-book');
        let touristVal = $searchTouristsSelect.val(),
            touristArr = touristVal.split('_');

        let ulPrices = $row.find('ul.package-search-prices');
        ulPrices.hide();
        ulPrices.find('li').hide();
        ulPrices.find('li.' + touristVal + '_price').show();
        ulPrices.show();

        let isNullAmount = parseInt($("#s_adults").val() || $("#s_children").val());

        if (!isNullAmount) {
            var oldHref = $packageSearchBook.attr('data-url')
                    .replace(/&adults=.*?(?=(&|$))/, '')
                    .replace(/&children=.*?(?=(&|$))/, '')
                ;

            $packageSearchBook.attr('data-url', oldHref + '&adults=' + touristArr[0] + '&children=' + touristArr[1]);
        } else {
            $searchTouristsSelect.attr("disabled", true);
        }
    }

    private modifyBookButton(packageData, element, editModal) {
        'use strict';
        let self = this;
        var newPackageCreateUrl = element.href;
        element.removeAttribute('href');
        if (packageData.accommodation) {
            newPackageCreateUrl += '&accommodation=' + packageData.accommodation;
        }
        element.setAttribute('data-url', newPackageCreateUrl);

        element.onclick = function () {
            let url = element.getAttribute('data-url');
            self.dataManager.createPackageRequest(url, packageData);
            editModal.modal('hide');
        };
    }

    public showPackageInfoModal(packageId, data) {
        let self = this;
        var packageInfoModal = $('#package-info-modal');
        let intervalData = this.dataManager.getPackageDataById(packageId);
        let $deleteButton = packageInfoModal.find('#package-info-modal-delete');
        if (intervalData.removePackage) {
            $deleteButton.click(function () {
                self.callRemoveConfirmationModal(packageId);
                packageInfoModal.modal('hide');
            });
        } else {
            $deleteButton.hide();
        }
        let $editButton = packageInfoModal.find('#package-info-modal-edit');
        $editButton.click(function () {
            // let packageId = document.getElementById('package_info_package_id').value;
            $editButton.attr('href', Routing.generate('package_edit', {id: packageId}));
        });

        packageInfoModal.find('#package-info-modal-body').html(data);
        packageInfoModal.modal('show');
    }

    public static showResultMessages(response) {
        response.success.forEach(function (message) {
            ActionManager.showMessage(true, message);
        });
        response.errors.forEach(function (message) {
            ActionManager.showMessage(false, message);
        });
    }

    public static showInternalErrorMessage() {
        ActionManager.showMessage(false, 'Произошла непредвиденная ошибка');
        ActionManager.hideLoadingIndicator();
    }

    private static showMessage(isSuccess, message, messageBlockId = 'messages') {
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
    }

    public static callUpdatePackageModal(packageElement, intervalData, changedSide = null, isDivide = false) {
        let $updateForm = $('#concise_package_update');
        $updateForm.show();
        let modalAlertDiv = document.getElementById('package-modal-change-alert');
        modalAlertDiv.innerHTML = '';
        var newIntervalData = ChessBoardManager.getPackageData(packageElement);
        if (intervalData && changedSide) {
            let alertMessageData = ActionManager.getAlertMessage(changedSide, intervalData, newIntervalData);
            if (alertMessageData) {
                ActionManager.showAlertMessage(alertMessageData, $updateForm);
            }
        }

        ActionManager.showEditedUpdateModal(intervalData, newIntervalData, isDivide);
    }

    private static getAlertMessage(changedSide, intervalData, newIntervalData) {
        if (changedSide == 'right') {
            let packageEndDate = ChessBoardManager.getMomentDate(intervalData.packageEnd);
            let intervalEndDate = ChessBoardManager.getMomentDate(intervalData.end);
            let newIntervalEndDate = ChessBoardManager.getMomentDate(newIntervalData.end);
            if (intervalData.position == 'full' || intervalData.position == 'right') {
                if (newIntervalEndDate.isAfter(packageEndDate)
                    || (intervalEndDate.isSame(packageEndDate) && newIntervalEndDate.isBefore(packageEndDate))) {
                    if (intervalData.updatePackage) {
                        return { message: 'Вы действительно хотите изменить дату выезда брони?', resolved : true};
                    } else {
                        return { message: 'Для выполнения данного действия необходимо изменить дату выезда. У Вас недостаточно прав для редактирование брони', resolved: false };
                    }
                }
            }
        } else if (changedSide == 'left') {
            let newIntervalStartDate = moment(newIntervalData.begin, "DD.MM.YYYY");
            let packageStartDate = ChessBoardManager.getMomentDate(intervalData.packageBegin);
            if ((intervalData.position == 'left' || intervalData.position == 'full')
                && !newIntervalStartDate.isSame(packageStartDate)) {
                if (intervalData.updatePackage) {
                    return { message: 'Вы действительно хотите изменить дату заезда брони?', resolved : true};
                } else {
                    return { message: 'Для выполнения данного действия необходимо изменить дату заезда. У Вас недостаточно прав для редактирование брони', resolved: false };
                }
            }
        } else if (changedSide == 'both') {
            if (!ChessBoardManager.isDatesEqual(newIntervalData.begin, intervalData.packageBegin)
            && !ChessBoardManager.isDatesEqual(newIntervalData.end, intervalData.packageEnd)) {
                if (intervalData.updatePackage) {
                    return { message: 'Вы действительно хотите изменить дату заезда и выезда брони?', resolved : true};
                } else {
                    return { message: 'Для выполнения данного действия необходимо изменить даты заезда и выезда. У Вас недостаточно прав для редактирование брони', resolved: false };
                }
            }
        }
    }

    private static showAlertMessage(alertMessageData, $updateForm) {
        let $continueButton = $('#package-modal-continue-button');
        if (alertMessageData.resolved) {
            $continueButton.show();
        }
        let $modalAlertDiv = $('#package-modal-change-alert');
        $modalAlertDiv.text(alertMessageData.message);
        $modalAlertDiv.show();
        let $confirmButton = $('#packageModalConfirmButton');
        $confirmButton.hide();
        $updateForm.hide();
        $continueButton.click(function () {
            ActionManager.onContinueButtonClick($modalAlertDiv, $confirmButton, $continueButton, $updateForm);
        })
    }

    private static showEditedUpdateModal(intervalData, newIntervalData, isDivide) {
        let newPackageBegin = ChessBoardManager.getMomentDate(intervalData.packageBegin).isAfter(ChessBoardManager.getMomentDate(newIntervalData.begin))
            ? newIntervalData.begin : intervalData.packageBegin;
        let newPackageEnd = ChessBoardManager.getMomentDate(intervalData.packageEnd).isBefore(ChessBoardManager.getMomentDate(newIntervalData.end))
            ? newIntervalData.end : intervalData.packageEnd;
        var modal = $('#packageModal');
        let packageId = intervalData.packageId;
        let intervalId = intervalData.accommodation ? intervalData.id : '';
        let payerText = intervalData.payer ? intervalData.payer : 'Не указан';
        modal.find('input.modalBlockId').val(intervalData.id);
        modal.find('input.isDivide').val(isDivide);
        modal.find('input.modalPackageId').val(packageId);
        modal.find('input.modalAccommodationId').val(intervalId);
        modal.find('#modal-package-number').text(intervalData.number);
        modal.find('#modal-package-payer').text(payerText);
        modal.find('#modal-package-begin').text(newPackageBegin);
        modal.find('#modal-package-end').text(newPackageEnd);
        modal.find('#modal-begin-date').text(newIntervalData.begin);
        modal.find('#modal-end-date').text(newIntervalData.end);
        modal.find('#modal-room-id').text(newIntervalData.accommodation);
        modal.find('#modal-room-type-name').text(roomTypes[newIntervalData.roomType]);
        modal.find('#modal-room-name').text(newIntervalData.accommodation
            ? rooms[newIntervalData.accommodation] : 'Без размещения');
        modal.modal('show');
    }

    public static getDataFromUpdateModal() {
        var modal = $('#packageModal');
        let payerText;
        let payerName = modal.find('#modal-package-payer').text();
        if (payerName != 'Не указан') {
            payerText = payerName;
        } else {
            payerText = modal.find('#modal-package-payer').text();
        }

        return {
            'id' : modal.find('input.modalBlockId').val(),
            'packageId': modal.find('input.modalPackageId').val(),
            'accommodationId': modal.find('input.modalAccommodationId').val(),
            'begin': modal.find('#modal-begin-date').text(),
            'end': modal.find('#modal-end-date').text(),
            'roomId': modal.find('#modal-room-id').text(),
            'isDivide': modal.find('input.isDivide').val(),
            'payer' : payerText
        }
    }

    private static onContinueButtonClick($modalAlertDiv, $confirmButton, $continueButton, $updateForm) {
        $continueButton.hide();
        $modalAlertDiv.hide();
        $confirmButton.show();
        $updateForm.show();
    }

    public static hidePackageUpdateModal() {
        $('#packageModal').modal('hide');
    }
}