/*global window, document, $, Routing, console, mbh */
var PACKAGING_COMMAND_CALL_SUCCESS = 'Запущен процесс упаковки броней, после завершения которой Вам на почту придет письмо с отчётом.';
var PACKAGING_COMMAND_CALL_ERROR = 'При запуске команды упаковки броней произошла ошибка.';

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
                    //Костыль. Не даем возможность нажать на создание спец предложений в стыке
                    var isJoint = $(this).find('a').length === 2;
                    // Ребро спецпредложения?
                    if (!isSpecials() || isJoint || !event.ctrlKey) {
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
                            if ($spec.index() < specBeginTd.index()) {
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
                });
        };

    //пока в ящик откладываем
    var Special = function ($cell) {
        this.$cell = $cell;
        this.specialId = $cell.data('special');
        this.url = Routing.generate('special_edit', {id: this.specialId});

    };
    Special.prototype.edit = function () {

    };
    Special.prototype.init = function () {
        this.bindHandler()
    };
    Special.prototype.bindHandler = function () {
        var that = this;
        this.$cell.click(function (e) {
            window.open(that.url);
        })
    };
    Special.prototype.show = function () {
        if (this.$cell.hasClass('special-hidden')) {
            this.$cell.removeClass('special-hidden');
        }
    };

    Special.prototype.hide = function () {
        if (!this.$cell.hasClass('special-hidden')) {
            this.$cell.addClass('special-hidden');
        }
    };

    Special.prototype.checkVisible = function () {
        isSpecials() ? this.show() : this.hide();
    };


    var SpecialContainer = function () {
        this.specials = [];
    };

    SpecialContainer.prototype.init = function () {
        var that = this;
        $.each($('div.special-cell'), function () {
            var special = new Special($(this));
            special.init();
            that.add(special);
        });
        this.checkVisible();
    };
    SpecialContainer.prototype.checkVisible = function () {
        $.each(this.specials, function () {
            this.checkVisible();
        })
    };
    SpecialContainer.prototype.add = function (special) {
        this.specials.push(special);
    };


    var specialsInit = function () {
        var specContainer = new SpecialContainer();
        specContainer.init();
        specContainer.checkVisible();
        $("[name='do_specials']").off('switchChange.bootstrapSwitch').on('switchChange.bootstrapSwitch', function () {
            specContainer.checkVisible();
        });
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
                    setVerticalScrollable($('.vertical-scrollable').first(), document.getElementById('windows-report-content'));
                    $('.vertical-scrollable').closest('table').css('z-index', 200);
                    // Далее идет вызов демона, вероятнее всего. Не читайте следующий код, во избежание повреждения рассудка.
                    // $('tr').hover(function () {
                    //     $(this).children('td').each(function (index, elem) {
                    //
                    //         var link = $(elem).find('a');
                    //         if (link.length) {
                    //             var cloneLink = link.clone();
                    //         }
                    //         if ($(this).attr('data-date') && !($(elem).find('.pos').length)) {
                    //
                    //             $(this).append("<div class='pos'></div>");
                    //             $(this).find('.pos').prepend(cloneLink);
                    //             link.remove();
                    //             if (!($(this).find('.dates').length)) {
                    //                 var str = "<div class='dates'><span class='" + $(this).attr('data-class') + "'>" + $(this).attr('data-date') + " </span><div class='text-muted'>" + $(this).attr('data-room') + "</div></div>";
                    //                 $(this).find('a').length ? $(this).find('a').append(str) : $(this).find('.pos').append(str);
                    //             }
                    //             $(this).find("a.windows-package-info-link").on('click', function (event) {
                    //                 processLinks(this, event);
                    //             });
                    //         }
                    //
                    //     });
                    //
                    //     $(this).find('a').tooltip();
                    //
                    // }, function () {
                    //     $(this).children('td').each(function () {
                    //         var linkOld = $(this).find('a').clone();
                    //         $(this).find('a').tooltip('hide');
                    //         $(this).find('.pos').remove();
                    //         $(this).append(linkOld);
                    //         $(this).find("a.windows-package-info-link").off('click');
                    //     });
                    // });

                    table.find("a.windows-package-info-link").on('click', function (event) {
                        processLinks(this, event);
                    });

                    $('.descr').readmore({
                        moreLink: '<div class="more-link"><a href="#">' + $('#expand-window').text() + ' <i class="fa fa-caret-right"></i></a></div>',
                        lessLink: '<div class="less-link"><a href="#">' + $('#turn-window').text() + ' <i class="fa fa-caret-up"></i></a></div>',
                        collapsedHeight: 35
                    });
                    specialBind();
                    specialsInit();
                }
            });
        },
        getFormData = function() {
            var formData = form.serializeObject();
            formData['show-disabled-rooms'] = $('#windows-report-filter-show-disabled-rooms').bootstrapSwitch('state');

            return formData;
        };

    table.html(mbh.loader.html);
    update(getFormData());
    $('#report-submit-button').click(function (event) {
        event.preventDefault();
        table.html(mbh.loader.html);
        update(getFormData());
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