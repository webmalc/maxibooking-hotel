/*global window, $, Routing, document */

var setSearchDatepickers = function (date) {
    'use strict';

    (date === 'undefined') ? date = new Date() : date = new Date(date);
    $('#s_begin').datepicker('setStartDate', date);
};

$(document).ready(function () {
    'use strict';

    (function () {
        $('#add-guest').click(function (e) {
            var guestModal = $('#add-guest-modal'),
                form = guestModal.find('form'),
                button = $('#add-guest-modal-submit'),
                errors = $('#add-guest-modal-errors');

            e.preventDefault();
            guestModal.modal('show');
            button.click(function () {
                errors.hide();
                $.post(form.prop('action'), form.serialize(), function (data) {
                    if (data.error) {
                        errors.html(data.text).show();
                    } else {
                        $('.findGuest').append($("<option/>", {
                            value: data.id,
                            text: data.text
                        })).val(data.id).trigger('change');
                        form.trigger('reset');
                        form.find('select').select2('data', null);
                        guestModal.modal('hide');
                        form.find('select').select2('data', null);
                        form.find('input').select2('data', null);
                        return 1;
                    }
                });
            });
        });
    }());

    var searchProcess = false;

    //ajax request
    (function () {

        var send = function (query) {
            var wrapper = $('#package-search-results-wrapper');

            if (searchProcess) {
                return;
            }

            $.ajax({
                url: Routing.generate('package_search_results'),
                data: query,
                beforeSend: function () {
                    searchProcess = true;
                },
                success: function (data) {
                    searchProcess = false;
                    wrapper.html(data);

                    // select2
                    (function () {
                        var format = function (icon) {
                            if (icon.id === undefined) {
                                return;
                            }

                            var arr = icon.id.split('_'),
                                text = '';
                            ;
                            for (var i = 1; i <= arr[0]; i++) {
                                text += '<i class="fa fa-male"></i> ';
                            }
                            for (var i = 1; i <= arr[1]; i++) {
                                text += '<small><i class="fa fa-child"></i></small>';
                            }
                            return $(text);

                        };

                        $('.search-tourists-select').each(function () {
                            $(this).select2({
                                placeholder: '',
                                allowClear: false,
                                width: 'element',
                                minimumResultsForSearch: -1,
                                //templateResult: format,
                                //templateSelection: format
                            });
                        });
                        $('.search-room-select').select2({
                            placeholder: 'при заезде',
                            allowClear: true,
                            templateResult: select2TemplateResult.appendIcon,
                            width: 'element'
                        });
                        $('[data-toggle="tooltip"]').tooltip();
                    }());

                    //accommodation
                    (function () {
                        var show = function (tr) {
                            var room = tr.find('.search-room-select'),
                                roomId = null,
                                bookText = tr.find('.package-search-book-reservation-text'),
                                accText = tr.find('.package-search-book-accommodation-text'),
                                link = tr.find('.package-search-book'),
                                oldHref = link.prop('href').replace(/&accommodation=.*?(?=(&|$))/, '');

                            if (room.val()) {
                                roomId = room.val();
                            }
                            if (roomId) {
                                bookText.hide();
                                accText.show();
                                link.removeClass('btn-success btn-danger').addClass('btn-primary');
                                link.prop('href', oldHref + '&accommodation=' + roomId);
                            } else {
                                bookText.show();
                                accText.hide();
                                link.removeClass('btn-primary btn-danger').addClass('btn-success');
                                link.prop('href', oldHref);
                            }
                        }
                        $('.search-room-select').change(function () {
                            show($(this).closest('tr'));
                        });
                        $('.search-room-select').each(function () {
                            show($(this).closest('tr'));
                        });
                    }());

                    //accommodation alert
                    (function () {
                        var select = $('select.search-room-select'),
                            warning = $('#accommodation-alert'),
                            date = new Date(),
                            show = function () {
                                var isAlert = false;

                                date.setHours(0, 0, 0, 0);
                                select.each(function () {
                                    var link = $(this).closest('tr').find('.package-search-book').addClass('btn-danger'),
                                        begin = $('#s_begin').datepicker("getDate");

                                    if ($(this).val() && begin > date) {
                                        link.addClass('btn-danger');
                                        isAlert = true;
                                    } else {
                                        link.removeClass('btn-danger');
                                    }
                                });

                                if (isAlert) {
                                    warning.removeClass('hide');
                                    warning.show();
                                } else {
                                    warning.hide();
                                }
                            };
                        show();
                        select.change(show);
                    }());

                    //search result prices
                    (function () {
                        var show = function (tr) {
                                var tourist = tr.find('.search-tourists-select'),
                                    touristVal = tourist.val(),
                                    touristArr = touristVal.split('_')
                                    ;
                                tr.find('ul.package-search-prices').hide();
                                tr.find('ul.package-search-prices li').hide();
                                tr.find('ul.package-search-prices li.' + touristVal + '_price').show();
                                tr.find('ul.package-search-prices').show();
                                var bookLink = tr.find('a.package-search-book'),
                                    oldHref = bookLink.prop('href')
                                        .replace(/&adults=.*?(?=(&|$))/, '')
                                        .replace(/&children=.*?(?=(&|$))/, '')
                                    ;

                                bookLink.prop('href', oldHref + '&adults=' + touristArr[0] + '&children=' + touristArr[1]);
                            }
                            ;
                        $('.search-tourists-select').change(function () {
                            show($(this).closest('tr'));
                        });
                        $('.search-tourists-select').each(function () {
                            show($(this).closest('tr'));
                        });
                    }());

                    //tariff chooser
                    (function () {
                        var links = $('#package-search-tariffs li a'),
                            select = $('#s_tariff'),
                            form = $('form[name="s"]')
                            ;
                        links.click(function (e) {
                            e.preventDefault();
                            select.val($(this).attr('data-id'));
                            window.location.hash = form.serialize();
                            form.submit();
                        });
                    }());

                    //book link actions
                    $('.package-search-book').click(function (e) {
                        e.preventDefault();

                        var touristSelect = $('.findGuest'),
                            oldHref = $(this).prop('href').replace(/&tourist=.*?(?=(&|$))/, ''),
                            id = null;

                        if (touristSelect.val()) {
                            id = touristSelect.val();
                        }

                        if (id) {
                            $(this).prop('href', oldHref + '&tourist=' + id);
                        } else {
                            $(this).prop('href', oldHref);
                        }

                        var win = window.open($(this).prop('href'), '_blank');
                        if (win) {
                            win.focus();
                        } else {
                            alert('Please allow popups for this site.');
                        }

                        var numWrapper = $(this).closest('tr').find('span.package-search-book-count'),
                            roomSelect = $(this).closest('tr').find('.search-room-select'),
                            roomId = null
                            ;
                        var num = parseInt(numWrapper.text()) - 1;
                        (num <= 0) ? num = 0 : num;
                        numWrapper.text(num);

                        if (roomSelect.val()) {
                            roomId = roomSelect.val();
                        }
                        roomSelect.find('option[value="' + roomId + '"]').attr('disabled', 'disabled');
                        roomSelect.select2({
                            placeholder: 'при заезде',
                            allowClear: true,
                            width: 'element',
                        });
                        roomSelect.val(null).trigger('change');
                    });
                }
            });
        }

        var form = $('.search-form'),
            sendForm = function () {
                if (!$('#s_begin').val() || !$('#s_end').val()) {
                    return;
                }
                var wrapper = $('#package-search-results-wrapper');
                window.location.hash = form.serialize();
                wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');
                send(form.serialize());
            };

        if (window.location.hash) {
            var hashes = getHashVars();
            for (var key in hashes) {
                var name = key.replace('s[', '').replace(']', '').replace('[0]', '').replace('[]', '');
                if ($('#s_' + name).length) {
                    $('#s_' + name).val(hashes[key]).trigger('change');
                }
            }
            window.location.hash = '';
        }

        sendForm();

        form.find('input, select').change(sendForm);

        form.submit(function (e) {
            e.preventDefault();
            sendForm()
        });


        //
        var childrenInput = $('#s_children'),
            icon = $('#search-children-ages');
        icon.popover({
            html: true,
            placement: 'top',
            content: ''
        });
        var changePopover = function () {
            var num = parseInt(childrenInput.val(), 10),
                popoverContent = icon.next('div.popover').children('div.popover-content'),
                content = ''
                ;
            if (num < 1) {
                icon.hide();
            } else {
                icon.show();
            }

            for (var i = 0; i < num; i++) {
                content += '<input type="number" id="children_age_' + i + '" name="s[children_age][]" class="children_age input-xxs form-control input-sm" min="0" max="18">'
            }

            var popover = icon.data('bs.popover');
            popover.options.content = content;

            if (popoverContent.length && content === '') {
                icon.trigger('click').hide();
            }
            if (popoverContent.length) {
                popoverContent.html(content);
            }
        };

        icon.popover({
            html: true,
            placement: 'top',
            trigger: 'manual',
            content: ''
        });
        icon.on('shown.bs.popover', function () {
            $('.children_age').change(sendForm);
        });
        icon.on('hidden.bs.popover', function () {
            sendForm();
        });
        childrenInput.change(function (){
            changePopover();
            $('.children_age').change(sendForm);
        });
        changePopover();

    }());


});

