/*global window, $, Routing, document */

var setSearchDatepickers = function (date) {
    (date === 'undefined') ? date = new Date() : date = new Date(date);
    $('#s_begin').datepicker('setStartDate', date);
};

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


$(document).ready(function () {
    'use strict';

    $('#add-guest').on('click', function (e) {
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
                    $('.findGuest').append($("<option/>", {value: data.id, text: data.text})).val(data.id).trigger('change');
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

    var searchProcess = false;

    var $wrapper = $('#package-search-results-wrapper');


    //accommodation
    var showAccommodation = function (tr) {
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
    //search result prices
    var showResultPrices = function (tr) {
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
    };

    var packageSearchBookClickHandler = function (e) {
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
    }


    var showQuality = function ($tr) {
        var $quantitySelect = $tr.find('.quantity-select');
        var value = $quantitySelect.val();
        var isMoreOne = value > 1;
        $tr.find('.search-room-select').select2({disabled: isMoreOne});

        var link = $tr.find('.package-search-book'),
            oldHref = link.prop('href').replace(/&quantity=.*?(?=(&|$))/, '');

        if (value) {
            link.prop('href', oldHref + '&quantity=' + value);
        }
    }

    var showAccommodationAlert = function () {
        var isAlert = false,
            date = new Date();

        date.setHours(0, 0, 0, 0);
        $('select.search-room-select').each(function () {
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
            $warning.removeClass('hide');
            $warning.show();
        } else {
            $warning.hide();
        }
    };

    var $warning = $('#accommodation-alert');
    //ajax request
    var successCallback = function (data) {
        searchProcess = false;
        $wrapper.html(data);

        var $quantitySelect = $wrapper.find('.quantity-select');
        var $searchRoomsSelect = $wrapper.find('.search-room-select');
        var $searchTouristsSelect = $wrapper.find('.search-tourists-select');

        $searchTouristsSelect.select2({
            placeholder: '',
            allowClear: false,
            width: 'element',
            minimumResultsForSearch: -1,
            //templateResult: format,
            //templateSelection: format
        });
        $searchRoomsSelect.select2({
            placeholder: 'при заезде',
            allowClear: true,
            templateResult: select2TemplateResult.appendIcon,
            width: 'element'
        });
        $wrapper.find('[data-toggle="tooltip"]').tooltip();


        $searchRoomsSelect.on('change', function () {
            showAccommodation($(this).closest('tr'));
        });
        $searchRoomsSelect.each(function () {
            showAccommodation($(this).closest('tr'));
        });

        //accommodation alert
        var select = $('select.search-room-select');

        showAccommodationAlert();
        select.on('change', showAccommodationAlert);

        $searchTouristsSelect.on('change', function () {
            showResultPrices($(this).closest('tr'));
        });
        $searchTouristsSelect.each(function () {
            showResultPrices($(this).closest('tr'));
        });

        //tariff chooser
        var $links = $('#package-search-tariffs li a'),
            $select = $('#s_tariff'),
            form = $('form[name="s"]')
            ;
        $links.on('click', function (e) {
            e.preventDefault();
            $select.val($(this).attr('data-id'));
            window.location.hash = form.serialize();
            form.submit();
        });

        //book link actions
        $wrapper.find('.package-search-book').on('click', packageSearchBookClickHandler);
        $quantitySelect.on('change', function() {
            showQuality($(this).closest('tr'))
        });
    }

    var send = function (query) {
        if (searchProcess) {
            return;
        }

        $.ajax({
            url: Routing.generate('package_search_results'),
            data: query,
            beforeSend: function () {
                searchProcess = true;
            },
            success: successCallback
        });
    }

    var $form = $('.search-form'),
        sendForm = function () {
            if (!$('#s_begin').val() || !$('#s_end').val()) {
                return;
            }
            //var wrapper = $('#package-search-results-wrapper');
            window.location.hash = $form.serialize();
            $wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');
            send($form.serialize());
        }

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

    $form.find('input, select').on('change', sendForm);

    $form.on('submit', function (e) {
        e.preventDefault();
        sendForm()
    });
});

