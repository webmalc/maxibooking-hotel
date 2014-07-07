var deleteLink = function() {
    $('.delete-link').click(function(event) {
        event.preventDefault();

        var href = ($(this).attr('href')) ? $(this).attr('href') : $(this).attr('data-href');
        $('#entity-delete-button').click(function() {
            window.location.href = href;
        });
        $('#entity-delete-confirmation').modal();
    });
};

/*global window */
$(document).ready(function() {
    'use strict';

    //Tooltips configuration
    $('a[data-toggle="tooltip"], li[data-toggle="tooltip"]').tooltip();

    //delete link
    deleteLink();

    //autohide messages
    window.setTimeout(function() {
        $(".autohide").fadeTo(400, 0).slideUp(400, function() {
            $(this).remove();
        });
    }, 5000);
});

