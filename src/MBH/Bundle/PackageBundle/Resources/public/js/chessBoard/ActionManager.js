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
        $deleteConfirmationModal.find('#entity-delete-modal-text').text('Точно удалить эту бронь?');
        $deleteConfirmationModal.find('#entity-delete-button').click(function () {
            self.dataManager.deletePackageRequest(packageId);
            $deleteConfirmationModal.modal('hide');
        });
        $deleteConfirmationModal.modal('show');
    };
    ActionManager.showLoadingIndicator = function () {
        var $loadingIndicator = $('#loading-indicator');
        $('#dimmer').toggle();
        $loadingIndicator.show();
    };
    ActionManager.hideLoadingIndicator = function () {
        $('#dimmer').toggle();
        var $loadingIndicator = $('#loading-indicator');
        $loadingIndicator.hide();
    };
    ActionManager.prototype.callPackageInfoModal = function (packageId) {
        this.dataManager.getPackageDataRequest(packageId);
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
        editModal.find('input.modalPackageId').val(packageData.id);
        $('.btn.package-search-book').each(function (index, element) {
            self.modifyBookButton(packageData, element, editModal);
        });
        editModal.modal('show');
    };
    ActionManager.prototype.modifyBookButton = function (packageData, element, editModal) {
        'use strict';
        var self = this;
        var newPackageCreateUrl = element.href;
        element.removeAttribute('href');
        if (packageData.accommodation) {
            newPackageCreateUrl += '&accommodation=' + packageData.accommodation;
        }
        element.onclick = function () {
            self.dataManager.createPackageRequest(newPackageCreateUrl, packageData);
            editModal.modal('hide');
        };
    };
    ActionManager.prototype.showPackageInfoModal = function (packageId, data) {
        var self = this;
        var packageInfoModal = $('#package-info-modal');
        packageInfoModal.find('#package-info-modal-edit').attr('href', Routing.generate('package_edit', { id: packageId }));
        packageInfoModal.find('#package-info-modal-delete').click(function () {
            self.callRemoveConfirmationModal(packageId);
            packageInfoModal.modal('hide');
        });
        packageInfoModal.find('#package-info-modal-body').html(data);
        packageInfoModal.modal('show');
    };
    ActionManager.showResultMessages = function (response) {
        response.messages.forEach(function (message) {
            ActionManager.showMessage(response.success, message);
        });
    };
    ActionManager.showMessage = function (isSuccess, message) {
        var messageDiv = document.createElement('div');
        messageDiv.className = 'alert alert-dismissable autohide';
        messageDiv.classList.add(isSuccess ? 'alert-success' : 'alert-danger');
        messageDiv.innerHTML = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
        messageDiv.innerHTML += message;
        document.getElementById('messages').appendChild(messageDiv);
    };
    ActionManager.callUpdatePackageModal = function (packageElement) {
        var packageData = ChessBoardManager.getPackageData(packageElement);
        var modal = $('#packageModal');
        modal.find('input.modalPackageId').val(packageData.id);
        modal.find('#modalCheckinDate').val(packageData.begin);
        modal.find('#modalCheckoutDate').val(packageData.end);
        modal.find('#modalRoomTypeName option[value=' + packageData.roomType + ']').prop('selected', true);
        modal.find("#modalRoomTypeName").change();
        modal.find('#modalTableLine').val(packageData.accommodation);
        modal.modal('show');
    };
    ActionManager.hidePackageUpdateModal = function () {
        $('#packageModal').modal('hide');
    };
    return ActionManager;
}());
//# sourceMappingURL=ActionManager.js.map