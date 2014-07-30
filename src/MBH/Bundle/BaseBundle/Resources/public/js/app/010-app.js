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

var checkMessages = function() {
    $.getJSON(Routing.generate('message'), function(data) {
        var container = $('#messages');
        $('#messages').find('.message').remove();

        if (!data.length) {
            return;
        }

        $.each(data, function(index, value) {
            var autohide = (value.autohide) ? 'autohide' : '';
            $('#messages').prepend('<div class="' + autohide + ' message alert alert-' + value.type + '"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + value.text + '</div>');
        });
    });
};

/*global window */
$(document).ready(function() {
    'use strict';

    //get messages
    checkMessages();
    window.setInterval(function() {
        checkMessages();
    }, 10000);

    //Tooltips configuration
    $('a[data-toggle="tooltip"], li[data-toggle="tooltip"], span[data-toggle="tooltip"], i[data-toggle="tooltip"]').tooltip();

    //delete link
    deleteLink();

    //autohide messages
    window.setTimeout(function() {
        $(".autohide").fadeTo(400, 0).slideUp(400, function() {
            $(this).remove();
        });
    }, 5000);
});

