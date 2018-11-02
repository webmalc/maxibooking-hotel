/*global window, $, document, mbh, Routing, datepicker, Translator */
function docReadyAccommodations() {
    'use strict';
    (function () {
        var newAccommodationLinks = $('.accommodation-new-link'),
            editAccommodationLinks = $('.accommodation-edit-link'),
            modal = $('#accommodation-form-modal'),
            modalBody = modal.find('.modal-body');
        if (newAccommodationLinks.length) {
            setOnClickHandler(newAccommodationLinks, false, modal, modalBody);
        }
        if (editAccommodationLinks.length) {
            setOnClickHandler(editAccommodationLinks, true, modal, modalBody);
        }
    }());
}

function setOnClickHandler($buttonsLinks, isEdit, modal, modalBody) {
    var $hiddenBlocks = $('#accommodation-change-room-type-button, #room-type-warning, #modal-continue-button, #modal-chessboard-button');
    $('#accommodation-change-room-type-button').click(function () {
        modal.modal('hide');
    });
    var packageRoomTypeId = $('#package-room-type').val();

    modal.on('hidden.bs.modal', function () {
        removeWarningAttributes(modal, $hiddenBlocks);
    });

    $buttonsLinks.click(function (event) {
        event.preventDefault();
        var modalTitleId = isEdit ? 'accommodation.modal_title.change_accommodation' : 'accommodation.modal_title.add_accommodation';
        modal.find('.modal-title').html(Translator.trans(modalTitleId) + '?');
        modal.modal();
        var $accommodateButton = $(this);

        if (!isEdit && packageRoomTypeId !== $accommodateButton.attr('data-roomType-id')) {
            var isAccommodationWithCurrentRoomTypeExists =  $('.accommodation-edit-link[data-roomType-id="'+ packageRoomTypeId + '"]').length > 0;
            var warningMessageId = isAccommodationWithCurrentRoomTypeExists
                ? 'package_bundle.accommodations.warning_before_setting_accoommodation_with_existed_accommodation'
                : 'package_bundle.accommodations.warning_before_setting_accoommodation';

            var warningMessage = Translator.trans(warningMessageId, {
                'accommodationRoomTypeName' : $accommodateButton.attr('data-roomType-name'),
                'packageRoomType' : $('#package-room-type-name').val(),
                'chessboard_route' : Routing.generate('chess_board_home'),
                'packages_route' : Routing.generate('package')
            });
            $('#room-type-warning').html(warningMessage);
            document.getElementById('accommodation-form-content').innerHTML = '';
            modal.find('.modal-content').addClass('modal-danger');
            modal.find('#modal-submit').hide();
            $hiddenBlocks.removeClass('hidden');
            $('#modal-continue-button').click(function () {
                removeWarningAttributes(modal, $hiddenBlocks);
                loadModalContent(modal, modalBody, isEdit, $accommodateButton);
                $(this).unbind('click');
                $('#room-type-warning').html('');
            });
        } else {
            loadModalContent(modal, modalBody, isEdit, $accommodateButton);
        }
    });
}

function removeWarningAttributes(modal, $hiddenBlocks) {
    modal.find('.modal-content').removeClass('modal-danger');
    $hiddenBlocks.addClass('hidden');
    modal.find('#modal-submit').show();
}

function loadModalContent(modal, modalBody, isEdit, $accommodateButton) {
    var $formContent = modalBody.find('#accommodation-form-content');
    var packageId = $($accommodateButton).attr('data-package-id');
    var url = isEdit
        ? Routing.generate('package_accommodation_edit', {'id' : $accommodateButton.attr('data-accommodation-id')})
        : Routing.generate('package_accommodation_new', {
            'id': packageId,
            'room': $accommodateButton.attr('data-room-id')
        });

    $formContent.html(mbh.loader.html);
    $.get(url, function(modalHtml) {
        $formContent.html(modalHtml);
        $formContent.find('.datepicker').datepicker({
            autoclose: true
        });
        $formContent.find('input[type="checkbox"]').bootstrapSwitch(mbh.bootstrapSwitchConfig);

        if (!isEdit && document.getElementById('interval-begin-date').value) {
            $formContent.find('#package_accommodation_room_begin').datepicker('update', document.getElementById('interval-begin-date').value);
            $formContent.find('#package_accommodation_room_end').datepicker('update', document.getElementById('interval-end-date').value);
        }

        $('#modal-submit').click(function () {
            var change_form = modalBody.find('form');

            if (change_form.length) {
                var formData = change_form.serialize();
                $formContent.html(mbh.loader.html);
                $.post(url, formData, function (html) {

                    $formContent.html(html);
                }).fail(function (xhr) {
                    if (xhr.status === 302) {
                        window.location.href = Routing.generate('package_accommodation', {id: packageId});
                    } else {
                        $formContent.html(mbh.error.html);
                        $formContent.find('.datepicker').datepicker({
                            language: "ru",
                            autoclose: true
                        });
                    }
                });
            }
        });
    }).fail(function () {
        $formContent.html(mbh.error.html);
    });
}

$(document).ready(function () {
    'use strict';
    docReadyAccommodations();
    $('#accommodation-select-table').on('draw.dt', function() {
        docReadyAccommodations();
    })
});