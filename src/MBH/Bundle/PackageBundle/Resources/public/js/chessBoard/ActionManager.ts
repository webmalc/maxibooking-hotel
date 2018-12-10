///<reference path="DataManager.ts"/>
///<reference path="ChessBoardManager.ts"/>
/*global $ */
declare let canBookWithoutPayer;
declare let roomTypes;
declare let rooms;

class ActionManager {

    private dataManager;

    constructor(dataManager) {
        this.dataManager = dataManager;
    }

    public callRemoveConfirmationModal(packageId) {
        let self = this;
        let $packageDeleteModal = $('#modal_delete_package');
        const packageTitle = this.dataManager.getPackageDataById(packageId).number;
        $('#modal_delete_package .modal-title').html(Translator.trans('package.remove_package', {'title' : packageTitle}));
        $packageDeleteModal.modal('show');
        const $modalContainer = $('#delete-modal-form-container');
        $modalContainer.html(mbh.loader.html);

        $.ajax({
            url: Routing.generate('package_delete', {'id': packageId}),
            type: "GET",
            success: function (modalBodyHTML) {
                $modalContainer.html(modalBodyHTML);
                $('select#mbh_bundle_packagebundle_delete_reason_type_deleteReason').select2();
                let $removeButton = $packageDeleteModal.find('#package-delete-modal-button');
                $removeButton.attr('type', 'button');
                const clickEventType = ChessBoardManager.getClickEventType();
                $removeButton.unbind(clickEventType);
                $removeButton.on(clickEventType, function () {
                    self.dataManager.deletePackageRequest(packageId, $modalContainer, $packageDeleteModal);
                })
            }
        });
    }

    public static callUnblockModal(packageId) {
        let $unblockModal = $('#entity-delete-confirmation');
        $unblockModal.find('.modal-title').text(Translator.trans('action_manager.modal.package_blocked.title') + '!');
        $unblockModal.find('#entity-delete-modal-text').text(Translator.trans('action_manager.modal.package_blocked.text') + '.');
        $unblockModal.find('#entity-delete-button').hide();
        ActionManager.addEditButton($unblockModal, packageId);
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
        this.dataManager.getPackageDataRequest(accommodationId);
    }

    public handleSearchOptionsModal(packageData, searchData) {
        let self = this;

        let editBody = $('#package-new-results');
        editBody.html(searchData);
        editBody.find('.search-room-select').val(packageData.accommodation);
        editBody.find('td:nth-child(4)').remove();
        editBody.find('thead th:nth-child(4)').remove();
        editBody.find('thead th').css('text-align', 'center');
        editBody.find('select').not("s[tourist]").select2();

        let editModal = $('#package-edit-modal');

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
            let $row = $(row);
            ActionManager.showResultPrices($row);
            $row.find('.search-tourists-select').change(function () {
                ActionManager.showResultPrices($row);
            })
        });
        editBody.find('.search-room-select').prop('disabled', true);

        editModal.find('input.modalPackageId').val(packageData.id);
        editModal.modal('show');
        editModal.on('shown.bs.modal', function () {
            $('.findGuest').mbhGuestSelectPlugin();
        });

        let tableResult = editBody.find('#package-search-special-wrapper');
        /** TODO сделать без таймаута*/
        setTimeout(function () {
            tableResult.readmore({
                moreLink: '<div class="more-link"><a href="#">'+tableResult.attr('data-more') +' <i class="fa fa-caret-right"></i></a></div>',
                lessLink: '<div class="less-link"><a href="#">'+tableResult.attr('data-less') +' <i class="fa fa-caret-up"></i></a></div>',
                collapsedHeight: isMobileDevice() ? 0 : 100
              });
        },400);
    }

    protected static showResultPrices($row) {
        if ($row.hasClass('info')) {
            return;
        }

        let $searchTouristsSelect = $row.find('.search-tourists-select');
        let $packageSearchBook = $row.find('.package-search-book');
        let touristVal = $searchTouristsSelect.val();
        let touristArr = touristVal.split('_');

        let ulPrices = $row.find('ul.package-search-prices');
        ulPrices.hide();
        ulPrices.find('li').hide();
        ulPrices.find('li.' + touristVal + '_price').show();
        ulPrices.show();

        let isNullAmount = parseInt($("#s_adults").val() || $("#s_children").val());

        if (!isNullAmount) {
            let oldHref = $packageSearchBook.attr('data-url')
                    .replace(/&adults=.*?(?=(&|$))/, '')
                    .replace(/&children=.*?(?=(&|$))/, '')
                ;

            $packageSearchBook.attr('data-url', oldHref + '&adults=' + touristArr[0] + '&children=' + touristArr[1]);
        } else {
            $searchTouristsSelect.attr("disabled", true);
        }
    }

    private modifySpecialButton(packageData, element, editModal) {
        let self = this;
        $(element).on(ChessBoardManager.getClickEventType(), function () {
            event.preventDefault();
            const $searchPackageForm = $('#package-search-form');
            const specialId = element.classList.contains('cancel') ? null : element.getAttribute('data-id');
            const newPackageRequestData = ChessBoardManager.getNewPackageRequestData($searchPackageForm, specialId);
            editModal.modal('hide');
            setTimeout(() => {
                self.dataManager.getPackageOptionsRequest(newPackageRequestData, packageData);
            }, 250);

        })
    }

    private modifyButtonsByGuest($editModal) {
        let touristVal = $('#s_tourist').val();
        $editModal.find('.package-search-book').each(function (index, element) {
            let title;
            if (!touristVal && !canBookWithoutPayer) {
                element.setAttribute('disabled', true);
                title = Translator.trans('action_manager.modal.disabled_book_button.title');
            } else {
                let leftRoomsCount = $(element).parent().parent().find('.package-search-book-count').eq(0).text();
                title = Translator.trans('action_manager.modal.book_button.title', {'roomsCount' : leftRoomsCount});
                element.removeAttribute('disabled');
            }
            element.setAttribute('title', title);
            element.setAttribute('data-original-title', title);
            let url = element.getAttribute('data-url');
            url = url.replace(/&(s%5Btourist|tourist).*?(?=(&|$))/, '');
            if (touristVal) {
                url = url + '&tourist=' + touristVal
            }

            element.setAttribute('data-url', url);
        });
    }

    private modifyBookButton(packageData, element, editModal) {
        'use strict';
        let self = this;
        let newPackageCreateUrl = element.href;
        $(element).find('.package-search-book-reservation-text').hide();
        $(element).find('.package-search-book-accommodation-text').show();
        element.removeAttribute('href');
        let accommodationValue = (<HTMLInputElement>document.getElementsByClassName('search-room-select')[0]).value;
        if (accommodationValue) {
            newPackageCreateUrl += '&accommodation=' + accommodationValue;
        }
        element.setAttribute('data-url', newPackageCreateUrl);

        $(element).on(ChessBoardManager.getClickEventType(), function () {
            if (!element.getAttribute('disabled')) {
                let url = element.getAttribute('data-url');
                editModal.modal('hide');
                self.dataManager.createPackageRequest(url, packageData);
            }
        });
    }

    public static callIntervalBeginOutOfRangeModal(side) {
        let $alertModal = $('#entity-delete-confirmation');
        if (side == 'begin') {
            $alertModal.find('.modal-title').text(Translator.trans('action_manager.modal.package_begin_date_abroad.title') + '!');
            $alertModal.find('#entity-delete-modal-text').text(Translator.trans('action_manager.modal.package_begin_date_abroad.text') + '.');
        } else if (side == 'end') {
            $alertModal.find('.modal-title').text(Translator.trans('action_manager.modal.package_end_date_abroad.title') + '!');
            $alertModal.find('#entity-delete-modal-text').text(Translator.trans('action_manager.modal.package_end_date_abroad.text') + '.');
        } else if (side == 'both') {
            $alertModal.find('.modal-title').text(Translator.trans('action_manager.modal.package_begin_and_end_date_abroad.title') + '!');
            $alertModal.find('#entity-delete-modal-text').text(Translator.trans('action_manager.modal.package_begin_and_end_date_abroad.text') + '.');
        }
        $alertModal.find('#entity-delete-button').hide();
        $alertModal.modal('show');
    }

    public static callIntervalToLargeModal(packageId) {
        let $unblockModal = $('#entity-delete-confirmation');
        $unblockModal.find('.modal-title').text(Translator.trans('action_manager.modal.package_to_large.title') + '!');
        $unblockModal.find('#entity-delete-modal-text').text(Translator.trans('action_manager.modal.package_to_large.text') + '.');
        $unblockModal.find('#entity-delete-button').hide();
        ActionManager.addEditButton($unblockModal, packageId);

        $unblockModal.modal('show');
    }

    private static addEditButton($modal, packageId) {
        let editButton = $modal.find('#package-info-modal-edit').eq(0);
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

        editButton.attr('href', Routing.generate('package_edit', {id: packageId}));
    }

    public showPackageInfoModal(packageId, data) {
        let self = this;
        let packageInfoModal = $('#package-info-modal');
        let intervalData = this.dataManager.getPackageDataById(packageId);
        if (intervalData) {
            let $deleteButton = packageInfoModal.find('#package-info-modal-delete');
            if (intervalData.removePackage) {
                $deleteButton.on(ChessBoardManager.getClickEventType(), function () {
                    self.callRemoveConfirmationModal(packageId);
                    packageInfoModal.modal('hide');
                });
            } else {
                $deleteButton.hide();
            }
            let $editButton = packageInfoModal.find('#package-info-modal-edit');
            $editButton.on(ChessBoardManager.getClickEventType(), function () {
                $editButton.attr('href', Routing.generate('package_edit', {id: packageId}));
            });

            packageInfoModal.find('#package-info-modal-body').html(data);
            packageInfoModal.modal('show');
        }
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
        ActionManager.showMessage(false, Translator.trans('action_manager.message.unexpected_error') + '.');
        ActionManager.hideLoadingIndicator();
    }

    public static showMessage(isSuccess, message, messageBlockId = 'chessboard-messages') {
        let messageDiv = document.createElement('div');
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
    }

    public callUpdatePackageModal(packageElement, intervalData, changedSide = null, isDivide = false) {
        let $updateForm = $('#concise_package_update');
        $updateForm.show();
        let modalAlertDiv = document.getElementById('package-modal-change-alert');
        modalAlertDiv.innerHTML = '';
        let newIntervalData = this.dataManager.chessBoardManager.getPackageData(packageElement);
        let isAccommodationInAnotherRoomType = ActionManager.isAccommodationInAnotherRoomType(newIntervalData, intervalData);
        if (isAccommodationInAnotherRoomType || (intervalData && changedSide)) {
            let alertMessageData = this.getAlertMessage(newIntervalData, intervalData, isAccommodationInAnotherRoomType);
            if (alertMessageData) {
                ActionManager.showAlertMessage(alertMessageData, $updateForm);
            }
        }

        ActionManager.showEditedUpdateModal(intervalData, newIntervalData, isDivide, changedSide);
    }

    protected static isNewAccommodationInAnotherRoomType(accommodationData, intervalData) {
        let isNewAccommodation = !intervalData.isAccommodationInterval
            || ActionManager.isPackageBeginChanged(accommodationData, intervalData)
            || ActionManager.isPackageEndChanged(accommodationData, intervalData);

        return ActionManager.isAccommodationInAnotherRoomType(accommodationData, intervalData) && isNewAccommodation;
    }

    protected static isAccommodationInAnotherRoomType(accommodationData, intervalData) {
        return accommodationData.roomType !== intervalData.packageRoomTypeId;
    }

    private getAlertMessage(newIntervalData, intervalData, isAccommodationInAnotherRoomType) {
        if (isAccommodationInAnotherRoomType) {
            let packageAccommodations = this.dataManager.getPackageAccommodations(intervalData.packageId);
            let existsAccommodationWithCurrentRoomType = packageAccommodations.some((accommodationData) => {
                return accommodationData.packageRoomTypeId === accommodationData.roomTypeId;
            });
            let warningMessageId = existsAccommodationWithCurrentRoomType
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
            }
        }

        let packageBeginChanged = ActionManager.isPackageBeginChanged(newIntervalData, intervalData);
        let packageEndChanged = ActionManager.isPackageEndChanged(newIntervalData, intervalData);
        let packageBeginAndEndChanged = packageBeginChanged && packageEndChanged;
        let canUpdatePackage = intervalData.updatePackage;
        if (packageBeginAndEndChanged) {
            if (newIntervalData.accommodation) {
                if (canUpdatePackage) {
                    return {
                        message: Translator.trans('aciton_manager.modal.change_package_begin_and_end.confirmation') + '?',
                        resolved: true
                    };
                } else {
                    return {
                        message: Translator.trans('action_manager.modal.need_changepackage_begin_and_end') + '. '
                        + Translator.trans('action_manager.modal.have_not_rights') + '.',
                        resolved: false
                    };
                }
            }
        } else if (packageBeginChanged) {
            if (canUpdatePackage) {
                return {
                    message: Translator.trans('aciton_manager.modal.change_package_begin.confirmation') + '?',
                    resolved: true
                }
            } else {
                return {
                    message: Translator.trans('action_manager.modal.need_changepackage_begin.title') + '. '
                    + Translator.trans('action_manager.modal.have_not_rights') + '.',
                    resolved: false
                };
            }
        } else if (packageEndChanged) {
            if (canUpdatePackage) {
                return {
                    message: Translator.trans('aciton_manager.modal.change_package_end.confirmation') + '?',
                    resolved: true
                }
            } else {
                return {
                    message: Translator.trans('action_manager.modal.need_changepackage_end.title') + '. '
                    + Translator.trans('action_manager.modal.have_not_rights') + '.',
                    resolved: false,
                };
            }
        }
    }

    private static isPackageEndChanged(newIntervalData, intervalData) {
        let intervalEndDate = ChessBoardManager.getMomentDate(intervalData.end);
        let newIntervalEndDate;
        if (intervalEndDate.isAfter(ChessBoardManager.getTableEndDate())) {
            newIntervalEndDate = ChessBoardManager.getMomentDate(intervalData.end);
        } else {
            newIntervalEndDate = ChessBoardManager.getMomentDate(newIntervalData.end);
        }
        let packageEndDate = ChessBoardManager.getMomentDate(intervalData.packageEnd);
        return ((intervalData.position == 'full' || intervalData.position == 'right')
            && newIntervalEndDate.isAfter(packageEndDate)
            || (intervalEndDate.isSame(packageEndDate) && newIntervalEndDate.isBefore(packageEndDate)));
    }

    private static isPackageBeginChanged(newIntervalData, intervalData) {
        let newIntervalStartDate = ChessBoardManager.getMomentDate(newIntervalData.begin);
        if (newIntervalStartDate.isBefore(ChessBoardManager.getTableStartDate())) {
            newIntervalStartDate = ChessBoardManager.getMomentDate(intervalData.begin);
        }
        let packageStartDate = ChessBoardManager.getMomentDate(intervalData.packageBegin);
        return ((intervalData.position == 'left' || intervalData.position == 'full')
            && !newIntervalStartDate.isSame(packageStartDate));
    }

    private static showAlertMessage(alertMessageData, $updateForm) {
        let $continueButton = $('#package-modal-continue-button');
        if (alertMessageData.resolved) {
            $continueButton.show();
        }
        let $modalAlertDiv = $('#package-modal-change-alert');
        $modalAlertDiv.html(alertMessageData.message);

        if (alertMessageData.modalContentClass) {
            let $modalContent = $updateForm.closest('.modal-content');
            $modalContent.addClass(alertMessageData.modalContentClass);
            $modalAlertDiv.removeClass('text-center');
            let onWithModalClassWindowClosed = function () {
                $modalContent.removeClass(alertMessageData.modalContentClass);
                $('#package-modal-change-alert').addClass('text-center');
            };
            $continueButton.on(ChessBoardManager.getClickEventType(), function () {
                onWithModalClassWindowClosed();
            });
            $('#packageModal').on('hidden.bs.modal', function () {
                onWithModalClassWindowClosed();
            });
        }

        $modalAlertDiv.show();
        let $confirmButton = $('#packageModalConfirmButton');
        $confirmButton.hide();
        $updateForm.hide();
        $continueButton.on(ChessBoardManager.getClickEventType(), function () {
            ActionManager.onContinueButtonClick($modalAlertDiv, $confirmButton, $continueButton, $updateForm);
        })
    }

    private static showEditedUpdateModal(intervalData, newIntervalData, isDivide, changedSide) {
        let intervalBegin = newIntervalData.begin;
        let intervalEnd = newIntervalData.end;

        let newPackageBegin = ActionManager.isPackageBeginChanged(newIntervalData, intervalData) && !isDivide
            ? newIntervalData.begin : intervalData.packageBegin;
        let newPackageEnd = ActionManager.isPackageEndChanged(newIntervalData, intervalData) && !isDivide
            ? newIntervalData.end : intervalData.packageEnd;

        if ((changedSide == 'right')
            && ChessBoardManager.getMomentDate(intervalData.begin).isBefore(ChessBoardManager.getTableStartDate())) {
            //Если размещение расширяется вправо и левый край брони не помещается в таблицу
            intervalBegin = intervalData.begin;
            newPackageBegin = intervalData.packageBegin;

        } else if (changedSide == 'left'
            && ChessBoardManager.getMomentDate(intervalData.end).isAfter(ChessBoardManager.getTableEndDate())) {
            //Если размещение расширяется влево и правый край брони не помещается в таблицу
            newPackageEnd = intervalData.packageEnd;
            intervalEnd = intervalData.end;
        } else if (changedSide == 'both' && !newIntervalData.accommodation) {
            //Если удаляется единственное размещение брони
            newPackageBegin = intervalData.begin;
            newPackageEnd = intervalData.end;
            intervalBegin = intervalData.begin;
            intervalEnd = intervalData.end;
        }

        let modal = $('#packageModal');
        let packageId = intervalData.packageId;
        let intervalId = intervalData.accommodation ? intervalData.id : '';
        let payerText = intervalData.payer ? intervalData.payer : Translator.trans('action_manager.update_modal.not_specified');
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
    }

    public static getDataFromUpdateModal() {
        let modal = $('#packageModal');
        let payerText;
        let payerName = modal.find('#modal-package-payer').text();

        if (payerName != Translator.trans('action_manager.update_modal.not_specified')) {
            payerText = payerName;
        } else {
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