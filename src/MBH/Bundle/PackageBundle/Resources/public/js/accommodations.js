/*global window, $, document, mbh, Routing, datepicker */
function docReadyAccommodations() {
    'use strict';

    //modal
    (function () {
        var links = $('.accommodation-new-link'),
            modal = $('#accommodation-form-modal'),
            modalBody = modal.find('.modal-body');
        if (!links.length) {
            return;
        }

        links.click(function (event) {
            event.preventDefault();
            var url = Routing.generate('package_accommodation_new', {
                'id': $(this).attr('data-package-id'),
                'room': $(this).attr('data-room-id')
            });
            modalBody.html(mbh.loader.html);
            modal.modal();
            $.get(url, function(modalHtml) {
                modalBody.html(modalHtml);
                modalBody.find('.datepicker').datepicker({
                    language: "ru",
                    autoclose: true
                });
                $('#modal-submit').click(function () {
                    var change_form = modal.find('.modal-body > form');
                    if (change_form.length) {
                        var formData = change_form.serialize();
                        modalBody.html(mbh.loader.html);
                        $.post(url, formData, function (html) {
                            modalBody.html(html);
                        }).fail(function (xhr) {
                            if (xhr.status === 302) {
                                location.reload();
                            } else {
                                modalBody.html(mbh.error.html);
                            }
                        });
                    }
                });
            }).fail(function () {
                modalBody.html(mbh.error.html);
            });
        });
    }());
}

$(document).ready(function () {
    'use strict';
    docReadyAccommodations();
});