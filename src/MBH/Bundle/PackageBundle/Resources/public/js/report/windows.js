/*global window, document, $, Routing, console, mbh */
var PACKAGING_COMMAND_CALL_SUCCESS = 'Запущен процесс упаковки броней, после завершения которой Вам на почту придет письмо с отчётом.';
var PACKAGING_COMMAND_CALL_ERROR = 'При запуске команды упаковки броней произошла ошибка.';

$(document).ready(function ($) {
    'use strict';
    var form = $('#windows-report-filter'),
        table = $('#windows-report-content'),
        modal = $('#package-info-modal'),
        processLinks = function () {
            var links = $('.windows-package-info-link');
            links.unbind('click');
            links.click(function (event) {
                event.preventDefault();
                modal.find('.modal-body').html(mbh.loader.html);
                modal.modal();
                setModalContent(modal, this);
            });
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
                            }

                        });

                        $(this).find('a').tooltip();
                        processLinks();

                    }, function () {
                        $(this).children('td').each(function () {
                            var linkOld = $(this).find('a').clone();
                            $(this).find('a').tooltip('hide');
                            $(this).find('.pos').remove();
                            $(this).append(linkOld);
                        });
                        processLinks();
                    });

                    $('.descr').readmore({
                        moreLink: '<div class="more-link"><a href="#">' + $('#expand-window').text() + ' <i class="fa fa-caret-right"></i></a></div>',
                        lessLink: '<div class="less-link"><a href="#">' + $('#turn-window').text() + ' <i class="fa fa-caret-up"></i></a></div>',
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
    hangPackagingHandlers();
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

function hangPackagingHandlers() {
    var $packagingModal = $('#packaging-info-modal');
    $('#packaging-button').click(function () {
        $packagingModal.modal('show');
    });

    var $packagingButton = $('#packaging-modal-button');
    $packagingButton.click(function () {
        $packagingModal.find('.modal-body').html(mbh.loader.html);
        $.ajax({
            url: Routing.generate('windows_packaging'),
            datatype: 'json',
            method: 'GET',
            success: function (data) {
                if (data.success) {
                    $packagingModal.find('.modal-body').text(PACKAGING_COMMAND_CALL_SUCCESS);
                } else {
                    $packagingModal.find('.modal-body').text(PACKAGING_COMMAND_CALL_ERROR);
                }
            },
            error: function (error) {
                console.log(error);
                $packagingModal.find('.modal-body').text(PACKAGING_COMMAND_CALL_ERROR);
            }
        });
        $('#packaging-button').attr('disabled', true);
        $packagingButton.hide();
    });
}