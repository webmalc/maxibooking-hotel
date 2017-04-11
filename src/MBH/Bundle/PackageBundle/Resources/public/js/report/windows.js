/*global window, document, $, Routing, console, mbh */

$(document).ready(function ($) {
    'use strict';

    var specialSwitch = $("[name='do_specials']").bootstrapSwitch(),
        specialRoomType = null,
        specialRoom = null,
        specialBegin = null,
        specialEnd = null,
        specBeginTd = null,
        isSpecials = function () {
            return specialSwitch.bootstrapSwitch('state');
        },
        specialBind = function () {
            $('.window, td:has(a.window-end, a.window-begin)')
                .click(function (event) {
                    if (!isSpecials()) {
                        return false;
                    }
                    event.preventDefault();
                    var $spec = $(this);
                    if (!specialBegin) {
                        specialRoomType = $spec.parent('tr').data('roomtypeid');
                        specialBegin = $spec.data('fulldate');
                        specialRoom = $spec.data('roomid');
                        specBeginTd = $spec;
                        specBeginTd.addClass('special_selected');
                    } else if (specialBegin) {
                        var room = $spec.data('roomid'),
                            date = $spec.data('fulldate');
                        if (room !== specialRoom || specialBegin === date) {
                            specialRoomType = null;
                            specialRoom = null;
                            specBeginTd.removeClass('special_selected');
                            specialBegin = null;
                            specBeginTd = null;
                            return false;
                        } else {
                            specialEnd = date;
                            if($spec.index() < specBeginTd.index()) {
                                var oldBegin = specialBegin;
                                specialBegin = specialEnd;
                                specialEnd = oldBegin;
                            }
                            var url = Routing.generate('special_new', {
                                room: specialRoomType,
                                virtual: specialRoom,
                                begin: specialBegin,
                                end: specialEnd
                            });
                            specialRoomType = null;
                            specialRoom = null;
                            specialBegin = null;
                            specialEnd = null;
                            specBeginTd.removeClass('special_selected');
                            specBeginTd = null;
                            window.open(url, '_blank');
                            return false;
                        }
                    }
                })
        };

    var form = $('#windows-report-filter'),
        table = $('#windows-report-content'),
        modal = $('#package-info-modal'),

        processLinks = function (element, event) {
            event.preventDefault();
            if (isSpecials()) {
                return false;
            }

            modal.find('.modal-body').html(mbh.loader.html);
            modal.modal();
            setModalContent(modal, element);
        },

        update = function (data) {
            $.ajax({
                url: Routing.generate('report_windows_table'),
                data: data,
                success: function (response) {
                    table.html(response);
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
                                $(this).find("a.windows-package-info-link").on('click', function (event) {
                                    processLinks(this, event);
                                });
                            }

                        });

                        $(this).find('a').tooltip();
                        // processLinks();

                    }, function () {
                        $(this).children('td').each(function () {
                            var linkOld = $(this).find('a').clone();
                            $(this).find('a').tooltip('hide');
                            $(this).find('.pos').remove();
                            $(this).append(linkOld);
                            $(this).find("a.windows-package-info-link").off('click');
                        });
                    });

                    $('.descr').readmore({
                        moreLink: '<div class="more-link"><a href="#">' + $('#expand-window').text() + ' <i class="fa fa-caret-right"></i></a></div>',
                        lessLink: '<div class="less-link"><a href="#">' + $('#turn-window').text() + ' <i class="fa fa-caret-up"></i></a></div>',
                        collapsedHeight: 35
                    });

                    specialBind();
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

function setModalContent($modal, packageElem, isChain) {
    var isChainCarried = !!isChain;
    $.get(Routing.generate('report_windows_package', {
        'id': packageElem.getAttribute('data-id'),
        isChain: isChainCarried
    }), function (html) {
        $modal.find('.modal-body').html(html);
        var $checkbox = $modal.find("#mbh_bundle_packagebundle_package_virtual_room_type_isChainMoved");
        listenToCheckbox($modal, packageElem, $checkbox, isChainCarried);
        if (isChainCarried) {
            $('#mbh_bundle_packagebundle_package_virtual_room_type_virtualRoom').find('option').each(function (index, elem) {
                if (!elem.value) {
                    elem.parentNode.removeChild(elem);
                }
            });
        }

        $('#modal-submit').click(function () {
            var change_form = $modal.find('.modal-body > form');
            if (change_form.length) {
                change_form.submit();
            } else {
                $modal.modal('hide');
            }
        });
        //$('#mbh_bundle_packagebundle_package_virtual_room_type_virtualRoom').select2();
    }).fail(function () {
        $modal.find('.modal-body').html(mbh.error.html);
    });
}

function listenToCheckbox($modal, packageElem, $checkbox, isChainCarried) {
    $checkbox.bootstrapSwitch({
        'size': 'small',
        'onText': 'да',
        'offText': 'нет',
        'onColor': 'success'
    });
    $checkbox.on('switchChange.bootstrapSwitch', function () {
        var isChainCarried = $checkbox.bootstrapSwitch('state');
        $modal.find('.modal-body').html(mbh.loader.html);
        setModalContent($modal, packageElem, isChainCarried);
    });
}