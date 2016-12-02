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
        $deleteConfirmationModal.find('#entity-delete-modal-text').text('Точно удалить эту бронь?');
        $deleteConfirmationModal.find('#entity-delete-button').click(function () {
            self.dataManager.deletePackageRequest(packageId);
            $deleteConfirmationModal.modal('hide');
        });
        $deleteConfirmationModal.modal('show');
    }

    public static showLoadingIndicator() {
        var $loadingIndicator = $('#loading-indicator');
        $('#dimmer').toggle();
        $loadingIndicator.show();
    }

    public static hideLoadingIndicator() {
        $('#dimmer').toggle();
        var $loadingIndicator = $('#loading-indicator');
        $loadingIndicator.hide();
    }

    public callPackageInfoModal(packageId) {
        this.dataManager.getPackageDataRequest(packageId);
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
        editModal.find('input.modalPackageId').val(packageData.id);

        $('.btn.package-search-book').each(function (index, element) {
            self.modifyBookButton(packageData, element, editModal);
        });

        editModal.modal('show');
    }

    private modifyBookButton(packageData, element, editModal) {
        'use strict';
        let self = this;
        var newPackageCreateUrl = element.href;
        element.removeAttribute('href');
        if (packageData.accommodation) {
            newPackageCreateUrl += '&accommodation=' + packageData.accommodation;
        }

        element.onclick = function () {
            self.dataManager.createPackageRequest(newPackageCreateUrl, packageData);
            editModal.modal('hide');
        };
    }

    public showPackageInfoModal(packageId, data) {
        let self = this;
        var packageInfoModal = $('#package-info-modal');
        packageInfoModal.find('#package-info-modal-edit').attr('href', Routing.generate('package_edit', {id: packageId}));
        packageInfoModal.find('#package-info-modal-delete').click(function () {
            self.callRemoveConfirmationModal(packageId);
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

    private static showMessage(isSuccess, message) {
        var messageDiv = document.createElement('div');
        messageDiv.className = 'alert alert-dismissable autohide';
        messageDiv.classList.add(isSuccess ? 'alert-success' : 'alert-danger');
        messageDiv.innerHTML = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
        messageDiv.innerHTML += message;
        document.getElementById('messages').appendChild(messageDiv);
    }

    public static callUpdatePackageModal(packageElement) {
        var packageData = ChessBoardManager.getPackageData(packageElement);

        var modal = $('#packageModal');
        modal.find('input.modalPackageId').val(packageData.id);
        modal.find('#modalCheckinDate').val(packageData.begin);
        modal.find('#modalCheckoutDate').val(packageData.end);
        modal.find('#modalRoomTypeName option[value=' + packageData.roomType + ']').prop('selected', true);
        modal.find("#modalRoomTypeName").change();
        modal.find('#modalTableLine').val(packageData.accommodation);
        modal.modal('show');
    }

    public static hidePackageUpdateModal()
    {
        $('#packageModal').modal('hide');
    }
}