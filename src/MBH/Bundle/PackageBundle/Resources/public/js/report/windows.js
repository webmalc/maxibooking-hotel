/*global window, document, $, Routing, console, mbh */

$(document).ready(function ($) {
    'use strict';
    var form = $('#windows-report-filter'),
        table = $('#windows-report-content'),
        modal = $('#package-info-modal'),
        processLinks = function () {
            $('.windows-package-info-link').click(function (event) {
                event.preventDefault();
                modal.find('.modal-body').html(mbh.loader.html);
                modal.modal();

                $.get(Routing.generate('report_windows_package', {'id': $(this).attr('data-id')}), function (html) {
                    modal.find('.modal-body').html(html);
                    $('#modal-submit').click(function () {
                        var change_form = modal.find('.modal-body > form');
                        if (change_form.length) {
                            change_form.submit();
                        } else {
                            modal.modal('hide');
                        }
                    });
                    //$('#mbh_bundle_packagebundle_package_virtual_room_type_virtualRoom').select2();
                }).fail(function () {
                    modal.find('.modal-body').html(mbh.error.html);
                });
            });
        },
        update = function (data) {
            $.ajax({
                url: Routing.generate('report_windows_table'),
                data: data,
                success: function (response) {
                    table.html(response);
                    processLinks();
                    $('tr').hover(function () {
                        $(this).children('td').each(function (index, elem) {

                            var link = $(elem).find('a');
                            if (link.length) {
                                var cloneLink = link.clone();
                            }
                            if ($(this).attr('data-date') && !($(elem).find('.pos').length)) {

                                $(this).append("<div class='pos'></div>");
                                $(this).find('.pos').prepend(cloneLink);
                                link.remove();
                                if (!($(this).find('.dates').length)) {
                                    var str = "<div class='dates'><span class='" + $(this).attr('data-class') + "'>" + $(this).attr('data-date') + " </span><div class='text-muted'>" + $(this).attr('data-room') + "</div></div>";
                                    $(this).find('a').length ? $(this).find('a').append(str) : $(this).find('.pos').append(str);
                                }
                            }

                        });
                    }, function () {
                        $(this).children('td').each(function () {
                            var linkOld = $(this).find('a').clone();
                            $(this).find('.pos').remove();
                            $(this).append(linkOld);
                        });
                    });

                    $('.descr').readmore({
                        moreLink: '<div class="more-link"><a href="#">'+$('#expand-window').text() +' <i class="fa fa-caret-right"></i></a></div>',
                        lessLink: '<div class="less-link"><a href="#">'+$('#turn-window').text() +' <i class="fa fa-caret-up"></i></a></div>',
                        collapsedHeight: 35
                    });
                }
            });
        };

    table.html(mbh.loader.html);
    update(form.serializeObject());
    $('#report-submit-button').click(function (event) {
        event.preventDefault();
        table.html(mbh.loader.html);
        update(form.serializeObject());
    });

});