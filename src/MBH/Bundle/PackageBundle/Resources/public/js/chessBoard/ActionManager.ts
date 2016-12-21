///<reference path="DataManager.ts"/>
///<reference path="ChessBoardManager.ts"/>
declare var $;

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

    public static showLoadingIndicator() {
        var $loadingIndicator = $('#loading-indicator');
        $('#dimmer').show();
        $loadingIndicator.show();
    }

    public static hideLoadingIndicator() {
        $('#dimmer').hide();
        var $loadingIndicator = $('#loading-indicator');
        $loadingIndicator.hide();
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

    public showPackageInfoModal(accommodationId, data) {
        let self = this;

        var packageInfoModal = $('#package-info-modal');
        packageInfoModal.find('#package-info-modal-edit').click(function () {
            let packageId = document.getElementById('package_info_package_id').value;
            packageInfoModal.find('#package-info-modal-edit').attr('href', Routing.generate('package_edit', {id: packageId}));
        });
        packageInfoModal.find('#package-info-modal-delete').click(function () {
            self.callRemoveConfirmationModal(accommodationId);
            packageInfoModal.modal('hide');
        });
        packageInfoModal.find('#package-info-modal-body').html(data);
        packageInfoModal.modal('show');
    }

    public static showResultMessages(response) {
        response.messages.forEach(function (message) {
            ActionManager.showMessage(response.success, message);
        });
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
        }, 10000);
        document.getElementById(messageBlockId).appendChild(messageDiv);
    }

    public static callUpdatePackageModal(packageElement, intervalData, changedSide = null, isDivide = false) {
        let $updateForm = $('#concise_package_update');
        $updateForm.show();
        let modalAlertDiv = document.getElementById('package-modal-change-alert');
        modalAlertDiv.innerHTML = '';
        var newIntervalData = ChessBoardManager.getPackageData(packageElement);
        if (intervalData && changedSide) {
            let alertMessage;
            if (changedSide == 'right') {
                let packageEndDate = ChessBoardManager.getMomentDateFromJson(intervalData.packageEnd.date);
                let newIntervalEndDate = moment(newIntervalData.end, "DD.MM.YYYY");
                if ((intervalData.position == 'full' && !newIntervalEndDate.isSame(packageEndDate))
                    || intervalData.position == 'right' && newIntervalEndDate.isAfter(packageEndDate)) {
                    alertMessage = 'Вы хотите изменить дату выезда брони?';
                }
            } else if (changedSide == 'left') {
                let newIntervalStartDate = moment(newIntervalData.begin, "DD.MM.YYYY");
                let packageStartDate = ChessBoardManager.getMomentDateFromJson(intervalData.begin.date);
                if (!newIntervalStartDate.isSame(packageStartDate)) {
                    alertMessage = 'Вы хотите изменить дату заезда брони?';
                }
            } else {
                //TODO: Тут бы кинуть экспешен
            }

            if (alertMessage) {
                let $continueButton = $('#package-modal-continue-button');
                $continueButton.show();
                let $modalAlertDiv = $('#package-modal-change-alert');
                $modalAlertDiv.text(alertMessage);
                $modalAlertDiv.show();
                let $confirmButton = $('#packageModalConfirmButton');
                $confirmButton.hide();
                $updateForm.hide();
                $continueButton.click(function () {
                    ActionManager.onContinueButtonClick($modalAlertDiv, $confirmButton, $continueButton, $updateForm);
                })
            }
        }

        var modal = $('#packageModal');
        let packageId = intervalData.packageId ? intervalData.packageId : intervalData.id;
        let accommodationId = intervalData.packageId ? intervalData.id : '';
        modal.find('input.isDivide').val(isDivide);
        modal.find('input.modalPackageId').val(packageId);
        modal.find('input.modalAccommodationId').val(accommodationId);
        modal.find('#modal-begin-date').text(newIntervalData.begin);
        modal.find('#modal-end-date').text(newIntervalData.end);
        modal.find('#modal-room-id').text(newIntervalData.accommodation);
        modal.find('#modal-room-name').text(newIntervalData.accommodation ? newIntervalData.accommodation : 'Без размещения');
        modal.modal('show');
    }

    public static getDataFromUpdateModal() {
        var modal = $('#packageModal');
        return {
            'packageId': modal.find('input.modalPackageId').val(),
            'accommodationId': modal.find('input.modalAccommodationId').val(),
            'begin': modal.find('#modal-begin-date').text(),
            'end': modal.find('#modal-end-date').text(),
            'roomId': modal.find('#modal-room-id').text(),
            'isDivide' : modal.find('input.isDivide').val()
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