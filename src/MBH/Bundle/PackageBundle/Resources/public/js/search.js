/*global window, $, Routing, document, mbh, Translator, canBookWithoutPayer */

var setSearchDatepickers = function(date) {
    'use strict';

    (date === 'undefined') ? date = new Date(): date = new Date(date);
    $('#s_begin').datepicker('setStartDate', date);
};
var searchProcess = false;

$(document).ready(function() {
    'use strict';

    (function() {

        $.ajax(Routing.generate('restriction_in_out_json'), {})
            .done(function(response) {

                if ($.isEmptyObject(response)) {
                    return false;
                }
                var options = mbh.datarangepicker.options;
                options.isInvalidDate = function(day) {
                    var roomType = $('#s_roomType').val();
                    if (!roomType || roomType.length !== 1) {
                        return false;
                    }
                    if (response[roomType[0]] && response[roomType[0]][day.format('DD.MM.YYYY')]) {
                        return true;
                    }
                    return false;
                };
                $('.daterangepicker-input').daterangepicker(options).on('apply.daterangepicker', function(ev, picker) {
                    mbh.datarangepicker.on($('.begin-datepicker.mbh-daterangepicker'), $('.end-datepicker.mbh-daterangepicker'), picker);
                });
            });
    }());

    $('#add-guest').on('click', function(e) {
        var guestModal = $('#add-guest-modal'),
            form = guestModal.find('form'),
            button = $('#add-guest-modal-submit'),
            errors = $('#add-guest-modal-errors');

        e.preventDefault();
        guestModal.modal('show');
        guestModal.find('.select2-container').css('width', '100%');
        button.click(function() {
            errors.hide();
            $.ajax({
                method: "POST",
                url: form.prop('action'),
                data: form.serialize(),
                success: function(data) {
                    if (data.error) {
                        errors.html(data.text).show();
                    } else {
                        $('.findGuest').append($("<option/>", {
                            value: data.id,
                            text: data.text
                        })).val(data.id).trigger('change');
                        form.trigger('reset');
                        guestModal.modal('hide');
                        form.find('select').select2('data', null);
                        return 1;
                    }
                }
            })
        });
    });

    var $wrapper = $('#package-search-results-wrapper');
    var $warning = $('#accommodation-alert');

    //ajax request

    var Row = function($row) {
        this.$row = $row;
        this.$quantitySelect = this.$row.find('.quantity-select');
        this.$searchRoomsSelect = this.$row.find('.search-room-select');
        this.$searchTouristsSelect = this.$row.find('.search-tourists-select');
        this.$packageSearchBook = this.$row.find('.package-search-book');
        this.$bookCount = this.$row.find('span.package-search-book-count');
        this.bookCount = parseInt(this.$bookCount.eq(0).text());
        if (this.bookCount < 0) {
            this.bookCount = 0;
        }
        this.updateViewBookCount();
    }

    Row.prototype.init = function() {
        var that = this;
        this.showResultPrices();
        this.$searchTouristsSelect.on('change', function() {
            that.showResultPrices();
        });

        this.$packageSearchBook.on('click', function(e) {
            e.preventDefault();
            if (canBookWithoutPayer || $('#s_tourist').val()) {
                that.packageSearchBookClickHandler();
            }
        });

        that.showAccommodation();
        this.$searchRoomsSelect.on('change', function() {
            that.showAccommodation();
        });

        this.showAccommodationAlert();
        this.$searchRoomsSelect.on('change', function() {
            that.showAccommodationAlert()
        });

        this.$quantitySelect.on('change', function() {
            that.showQuality()
        });
    };

    Row.prototype.updateViewBookCount = function() {
        this.$bookCount.text(this.bookCount);
        var that = this;
        this.$quantitySelect.mbhSelect2OptionsFilter(function() {
            return this.value <= that.bookCount;
        });
        if (this.bookCount == 0) {
            this.$packageSearchBook.addClass('disabled');
        }
    }

    Row.prototype.showResultPrices = function() {
        var touristVal = this.$searchTouristsSelect.val(),
            touristArr = touristVal.split('_');

        var ulPrices = this.$row.find('ul.package-search-prices');
        ulPrices.hide();
        ulPrices.find('li').hide();
        ulPrices.find('li.' + touristVal + '_price').show();
        ulPrices.show();

        if (!isNotNullAmount()) {
            var oldHref = this.$packageSearchBook.prop('href')
                .replace(/&adults=.*?(?=(&|$))/, '')
                .replace(/&children=.*?(?=(&|$))/, '');

            this.$packageSearchBook.prop('href', oldHref + '&adults=' + touristArr[0] + '&children=' + touristArr[1]);
        } else {
            this.$searchTouristsSelect.attr("disabled", true);
        }

    };

    Row.prototype.showQuality = function() {
        var value = this.$quantitySelect.val();
        var isMoreOne = value > 1;
        if (isMoreOne) {
            this.$searchRoomsSelect.attr('disabled', true);
        } else {
            this.$searchRoomsSelect.attr('disabled', false);
        }
        var oldHref = this.$packageSearchBook.prop('href').replace(/&quantity=.*?(?=(&|$))/, '');

        if (value) {
            this.$packageSearchBook.prop('href', oldHref + '&quantity=' + value);
        }
    };

    Row.prototype.packageSearchBookClickHandler = function() {
        var touristSelect = $('.findGuest'),
            oldHref = this.$packageSearchBook.prop('href').replace(/&tourist=.*?(?=(&|$))/, '');

        var href = touristSelect.val() ?
            oldHref + '&tourist=' + touristSelect.val() :
            oldHref;
        this.$packageSearchBook.prop('href', href);

        var win = window.open(href, '_blank');
        if (win) {
            win.focus();
        } else {
            alert('Please allow popups for this site.');
        }

        var selectionQuantity = parseInt(this.$quantitySelect.val());
        this.bookCount = this.bookCount - selectionQuantity;
        this.updateViewBookCount();

        var roomID = this.$searchRoomsSelect.val() || null;
        if (roomID) {
            this.$searchRoomsSelect.find('option[value="' + roomID + '"]').attr('disabled', 'disabled');
        }
        this.$searchRoomsSelect.select2({
            placeholder: Translator.trans("search.upon_arrival"),
            allowClear: true,
            width: 'element'
        });
        this.$searchRoomsSelect.val(null).trigger('change');
    }

    Row.prototype.showAccommodationAlert = function() {
        var date = new Date();

        date.setHours(0, 0, 0, 0);
        this.$packageSearchBook.addClass('btn-danger');
        var begin = $('#s_begin').datepicker("getDate");
        if (this.$searchRoomsSelect.val() && begin > date) {
            this.$packageSearchBook.addClass('btn-danger');
            $warning.removeClass('hide');
        } else {
            this.$packageSearchBook.removeClass('btn-danger');
            $warning.addClass('hide');
        }
    }

    Row.prototype.showAccommodation = function() {
        var bookText = this.$row.find('.package-search-book-reservation-text'),
            accText = this.$row.find('.package-search-book-accommodation-text'),
            oldHref = this.$packageSearchBook.prop('href').replace(/&accommodation=.*?(?=(&|$))/, '');

        var roomId = this.$searchRoomsSelect.val();

        if (roomId) {
            bookText.hide();
            accText.show();
            this.$packageSearchBook.removeClass('btn-success btn-danger').addClass('btn-primary');
            this.$packageSearchBook.prop('href', oldHref + '&accommodation=' + roomId);
        } else {
            bookText.show();
            accText.hide();
            this.$packageSearchBook.removeClass('btn-primary btn-danger').addClass('btn-success');
            this.$packageSearchBook.prop('href', oldHref);
        }
    }

    var $tariffSelect = $('#s_tariff');
    var $packageSearchForm = $('form[name="s"]'); //#package-search-form //.search-form
    var successCallback = function(data) {
        $wrapper.html(data);
        searchProcess = false;
        $(function() {
            $('[data-toggle="popover"]').popover()
        });

        var $quantitySelect = $wrapper.find('.quantity-select');
        var $searchRoomsSelect = $wrapper.find('.search-room-select');
        var $searchTouristsSelect = $wrapper.find('.search-tourists-select');

        $quantitySelect.select2({
            minimumResultsForSearch: -1
        });
        $searchRoomsSelect.select2({
            placeholder: Translator.trans("search.upon_arrival"),
            allowClear: true,
            templateResult: select2TemplateResult.appendIcon,
            width: 'element'
        });
        $searchTouristsSelect.select2({
            placeholder: '',
            allowClear: false,
            width: 'element',
            minimumResultsForSearch: -1
            //templateResult: format,
            //templateSelection: format
        });
        $wrapper.find('[data-toggle="tooltip"]').tooltip();

        $wrapper.find('.package-search-table tbody tr:not(.mbh-grid-header1)').each(function() {
            var row = new Row($(this));
            row.init();
        });

        if ($('#s_special').val() && !$('.search-special-apply.cancel').length) {
            $('#s_special').val('');
            sendForm();
        }

        $('.search-special-apply').click(function(e) {
            e.preventDefault();
            var special = $(this).hasClass('cancel') ? '' : $(this).attr('data-id');
            $('#s_special').val(special);
            sendForm();
        });

        $('.search-all-tariffs-link').click(function(e) {
            e.preventDefault();
            $('#s_roomType').select2("val", [$(this).attr('data-roomType')]);
            sendForm();
        });
        
        $('#package-search-special-wrapper').readmore({
            moreLink: '<div class="more-link"><a href="#">'+$('#package-search-special-wrapper').attr('data-more') +' <i class="fa fa-caret-right"></i></a></div>',
            lessLink: '<div class="less-link"><a href="#">'+$('#package-search-special-wrapper').attr('data-less') +' <i class="fa fa-caret-up"></i></a></div>',
            collapsedHeight: 230
        });

        $('#search-flashbag').readmore({
            moreLink: '<div class="more-link"><a href="#">'+$('#search-flashbag').attr('data-more') +' <i class="fa fa-caret-right"></i></a></div>',
            lessLink: '<div class="less-link"><a href="#">'+$('#search-flashbag').attr('data-less') +' <i class="fa fa-caret-up"></i></a></div>',
            collapsedHeight: 35
        });
        updateBookButtons();
        /*var $links = $('#package-search-tariffs li a');
         $links.on('click', function (e) {
         e.preventDefault();
         $tariffSelect.val($(this).attr('data-id'));
         window.location.hash = $packageSearchForm.serialize();
         $packageSearchForm.submit();
         });*/
    };

    var isNotNullAmount = function() {
        return parseInt($("#s_adults").val() || $("#s_children").val());
    };

    var send = function(query) {
        if (searchProcess) {
            return;
        }

        $.ajax({
            url: Routing.generate('package_search_results'),
            data: query,
            beforeSend: function() {
                searchProcess = true;
            },
            success: successCallback
        });
    }


    //
    var childrenInput = $('#s_children'),
        icon = $('#search-children-ages');
    icon.popover({
        html: true,
        placement: 'top',
        content: ''
    });
    var changePopover = function() {
        var num = parseInt(childrenInput.val(), 10),
            popoverContent = icon.next('div.popover').children('div.popover-content'),
            content = '';
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

    var updateBookButtons = function () {
        if (!canBookWithoutPayer) {
            var touristValue = $('#s_tourist').val();
            $('.package-search-book').each(function () {
                var title;
                if (!touristValue) {
                    this.setAttribute('disabled', true);
                    title = Translator.trans('search.disabled_book_button.title');
                } else {
                    var leftRoomsCount = $(this).parent().parent().find('.package-search-book-count').eq(0).text();
                    title = Translator.trans('search.book_button.title', {'roomsCount' : leftRoomsCount});
                    this.removeAttribute('disabled');
                }
                this.setAttribute('data-original-title', title);
            })
        }
    };

    var sendForm = function() {

        setTimeout(
            function() {
                if (!$('#s_begin').val() || !$('#s_end').val() || createDate($('#s_begin')) >= createDate($('#s_end'))) {
                    return;
                }
                //var wrapper = $('#package-search-results-wrapper');
                window.location.hash = $packageSearchForm.serialize();
                $wrapper.html(mbh.loader.html);
                send($packageSearchForm.serialize());
            }, 500);
    };

    if (window.location.hash) {
        var hashes = getHashVars();
        for (var key in hashes) {
            var name = key.replace('s[', '').replace(']', '').replace('[0]', '').replace('[]', '');
            if ($('#s_' + name).length) {
                $('#s_' + name).val(hashes[key]).trigger('change');
            }
        }
        if ($('.daterangepicker-input').length && hashes['s[begin]'] && hashes['s[end]']) {
            $('.daterangepicker-input').data('daterangepicker').setStartDate(moment(hashes['s[begin]'], 'DD.MM.YYYY'));
            $('.daterangepicker-input').data('daterangepicker').setEndDate(moment(hashes['s[end]'], 'DD.MM.YYYY'));
        }
        window.location.hash = '';
    }


    if (icon.length) {
        icon.popover({
            html: true,
            placement: 'top',
            trigger: 'manual',
            content: ''
        });
        changePopover();
    }

    childrenInput.bind('keyup mouseup', function() {
        changePopover();
        $('.children_age').change(sendForm);
    });

    if (!$('#search-submit-button').length) {
        sendForm();
        $packageSearchForm.find('input, select').not('#s_tourist').not('.daterangepicker-input').on('change switchChange.bootstrapSwitch', sendForm);
        icon.on('shown.bs.popover', function() {
            $('.children_age').change(sendForm);
        });
        icon.on('hidden.bs.popover', function() {
            sendForm();
        });
    }
    $packageSearchForm.on('submit', function(e) {
        e.preventDefault();
        sendForm()
    });
    $('#s_tourist').change(function () {
       updateBookButtons();
    });
});
