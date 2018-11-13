/*global window, $, document, Routing, console, select2Text */

var MODAL_BODY_PADDING = 15;
var PANORAMA_WIDTH_TO_HEIGHT_RELATION = 3.2;
var PANORAMA_IMG_ID = 'panorana-image';

$(document).ready(function () {
    'use strict';

    //spinners
    $('#mbh_bundle_hotelbundle_hoteltype_saleDays').TouchSpin({
        min: 1,
        max: 365,
        step: 1,
        boostat: 5,
        maxboostedstep: 10
    });
    $('.spinner').TouchSpin({
        min: 0,
        max: 9007199254740992,
        step: 1,
        boostat: 5,
        maxboostedstep: 10
    });

    $("#mbh_bundle_hotelbundle_hotel_extended_type_rating").TouchSpin({
        min: 1,
        max: 5,
        step: 1,
        boostat: 1,
        maxboostedstep: 1
    });

    handlePanoramaCreation();
});


function handlePanoramaCreation() {
    var uploadCrop;

    var $uploadImageButton = $('#upload-image-button');
    $uploadImageButton.click(function () {
        $(this).closest('form').submit();
    });

    $uploadImageButton.attr('disabled', !this.value);
    $('#mbhhotel_bundle_hotel_image_type_imageFile').change(function () {
        $uploadImageButton.attr('disabled', !this.value);
    });

    var $panoramaButton = $("#panorama-button");
    $panoramaButton.attr('disabled', true);
    var $panoramaInput = $panoramaButton.closest('form').find('#mbhhotel_bundle_hotel_image_type_imageFile');
    $panoramaInput.change(function () {
        if (!this.value) {
            $panoramaButton.attr('disabled', true);
            $panoramaButton.unbind('click');
        } else {
            $panoramaButton.attr('disabled', false);
            $panoramaButton.click(function () {
                $('#upload-image-details-modal').modal('show');
                $('.croppie-container').remove();

                var $modalBody = $('#upload-image-details-modal-body');

                var imgEl = document.createElement('img');
                imgEl.id = PANORAMA_IMG_ID;
                $modalBody.append(imgEl);

                readURL($panoramaInput[0]);
                setTimeout(function () {
                    var modalWidth = $modalBody.css('width');
                    var modalWidthWithoutPadding = Math.round(parseFloat(modalWidth)) - 2 * MODAL_BODY_PADDING;
                    uploadCrop = new Croppie(document.getElementById(PANORAMA_IMG_ID), {
                        viewport: {
                            width: modalWidthWithoutPadding,
                            height: modalWidthWithoutPadding / PANORAMA_WIDTH_TO_HEIGHT_RELATION,
                            type: 'square'
                        },
                        boundary: {
                            width: modalWidthWithoutPadding,
                            height: 300
                        }
                    });
                }, 500);
            });
        }
    });

    $('#upload-image-details-modal-save-button').on('click', function (ev) {
        ev.preventDefault();
        uploadCrop.result({
            type: 'canvas',
            size: 'original'
        }).then(function (resp) {
            $('#imagebase64').val(resp);
            $('#panorama-image-form').submit();
        });
    });
}

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#' + PANORAMA_IMG_ID).attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
    }
}
