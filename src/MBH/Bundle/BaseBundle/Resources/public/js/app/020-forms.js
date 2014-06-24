/*global window */
$(document).ready(function () {
    'use strict';

    //Send delete form
    $('#entity-delete-button').click(function (event) {
        event.preventDefault();
        $('#entity-delete-form').submit();
    });
});

