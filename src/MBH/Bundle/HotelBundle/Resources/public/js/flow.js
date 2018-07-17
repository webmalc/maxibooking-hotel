$(document).ready(function () {
    var $addImageInput = $('#mbhhotel_bundle_hotel_image_type_imageFile');
    $addImageInput.change(function () {
        $addImageInput.closest('form').submit();
        console.log('ПЫЩ');
    });
});