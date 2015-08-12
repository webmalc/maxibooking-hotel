/*global $, window, document, $ */

var closePopovers = function () {
    'use strict';
    $('body').on('click', function (e) {
        //only buttons
        if ($(e.target).data('toggle') !== 'popover' && $(e.target).parents('.popover.in').length === 0) {
            $('[data-toggle="popover"]').popover('hide');
        }
    });
};

var getUrlVars = function () {
    'use strict';
    var vars = [], hash,
        hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
};

var getHashVars = function () {
    'use strict';
    var vars = [], hash,
        hashes = window.location.hash.slice(window.location.hash.indexOf('#') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = decodeURIComponent(hashes[i]).split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
};

var dangerTr = function () {
    'use strict';
    $('span.danger-tr').closest('tr').addClass('danger');
}

var deleteLink = function () {
    'use strict';
    $('.delete-link').click(function (event) {
        event.preventDefault();

        var href = ($(this).attr('href')) ? $(this).attr('href') : $(this).attr('data-href');
        var action = $(this).attr('data-action');
        var link = $(this);

        $("#entity-delete-button").unbind("click");

        $('#entity-delete-button').click(function (e) {
            e.preventDefault();
            if (action) {
                eval(action + '(link)');
            } else {
                window.location.href = href;
            }
        });

        if ($(this).attr('data-header')) {
            $('#entity-delete-modal-header').html($(this).attr('data-header'));
        } else {
            $('#entity-delete-modal-header').html($('#entity-delete-modal-header').attr('data-default'));
        }
        if ($(this).attr('data-text')) {
            $('#entity-delete-modal-text').html($(this).attr('data-text'));
        } else {
            $('#entity-delete-modal-text').html($('#entity-delete-modal-text').attr('data-default'));
        }
        if ($(this).attr('data-button')) {
            $('#entity-delete-button-text').html($(this).attr('data-button'));
        } else {
            $('#entity-delete-button-text').html($('#entity-delete-button-text').attr('data-default'));
        }

        if ($(this).attr('data-button-icon')) {
            $('#entity-delete-button-icon').attr('class', 'fa ' + $(this).attr('data-button-icon'));
        } else {
            $('#entity-delete-button-icon').attr('class', 'fa ' + $('#entity-delete-button-icon').attr('data-default'));
        }

        if ($(this).attr('data-button-class')) {
            $('#entity-delete-button').attr('class', 'btn btn-' + $(this).attr('data-button-class'));
        } else {
            $('#entity-delete-button').attr('class', 'btn btn-' + $('#entity-delete-button').attr('data-default'));
        }

        $('.datepicker').datepicker({
            language: "ru",
            todayHighlight: true,
            autoclose: true
        });

        $('#entity-delete-confirmation').modal();
    });
};

$(document).ready(function () {
    'use strict';

    //scrolling height
    (function () {
        if (!$('.scrolling').length) {
            return null;
        }
        var h = function () {
            $('.scrolling').height(function (index, height) {
                return $(window).height() - $(this).offset().top - 60;
            });
        };
        h();
        setInterval(h, 500);
    }());

    //Tooltips configuration
    $('[data-toggle="tooltip"]').tooltip();

    //delete link
    deleteLink();

    //autohide messages
    window.setTimeout(function () {
        $(".autohide").fadeTo(400, 0).slideUp(400, function () {
            $(this).remove();
        });
    }, 5000);

    //fancybox
    $('.fancybox').fancybox();
    $('.image-fancybox').fancybox({'type': 'image'});

    //popovers
    $('[data-toggle="popover"]').popover();
    closePopovers();
});
