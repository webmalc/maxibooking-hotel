/*global window, $, document, mbh, Routing, datepicker */
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

function setOnClickHandler($selector, isEdit, modal, modalBody) {
    $selector.click(function (event) {
        event.preventDefault();
        var url;
        var packageId = $(this).attr('data-package-id');
        if (isEdit) {
            url = Routing.generate('package_accommodation_edit', {
                'id' : $(this).attr('data-accommodation-id')
            });
        } else {
            url = Routing.generate('package_accommodation_new', {
                'id': packageId,
                'room': $(this).attr('data-room-id')
            });
        }
        modalBody.html(mbh.loader.html);
        modal.modal();
        $.get(url, function(modalHtml) {
            modalBody.html(modalHtml);
            modalBody.find('.datepicker').datepicker({
                language: "ru",
                autoclose: true
            });

            if (!isEdit && document.getElementById('interval-begin-date').value) {

                modalBody.find('#package_accommodation_room_begin').datepicker('update', document.getElementById('interval-begin-date').value);
                modalBody.find('#package_accommodation_room_end').datepicker('update', document.getElementById('interval-end-date').value);
            }

            $('#modal-submit').click(function () {
                var change_form = modal.find('.modal-body > form');
                if (change_form.length) {
                    var formData = change_form.serialize();
                    modalBody.html(mbh.loader.html);
                    $.post(url, formData, function (html) {
                        modalBody.html(html);
                        modalBody.find('.datepicker').datepicker({
                            language: "ru",
                            autoclose: true
                        });
                    }).fail(function (xhr) {
                        if (xhr.status === 302) {
                            window.location.href = Routing.generate('package_accommodation', {id: packageId});
                        } else {
                            modalBody.html(mbh.error.html);
                            modalBody.find('.datepicker').datepicker({
                                language: "ru",
                                autoclose: true
                            });
                        }
                    });
                }
            });
        }).fail(function () {
            modalBody.html(mbh.error.html);
        });
    });
}

function edit() {

}

$(document).ready(function () {
    'use strict';
    docReadyAccommodations();
});