$(document).ready(function () {
    var $keyWordsList = $('#key-words-list');

    var initRemoveButtons = function() {
        $keyWordsList.find('.fa-times').click(function () {
            $(this).closest('li').remove();
        });
    };

    $('#add-key-word-button').click(function () {
        var prototype = $(this).data('prototype');
        var keyNumber = parseInt($keyWordsList.find('li').last().data('number')) + 1;
        if (isNaN(keyNumber)) {
            keyNumber = 1
        }
        var newField = prototype.replace(/__name__/g, keyNumber);
        $keyWordsList.append('<li data-number="' + keyNumber + '">' + newField + '</li>');
        initRemoveButtons();
    });
    initRemoveButtons();
});
