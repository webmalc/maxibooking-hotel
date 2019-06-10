MbhResultForm.prototype.fixedBtnBooking = function() {
    var resultAction,
        modFrameTopOffset,
        coordsResultAction;

    window['mbhFixedBtnBooking'] = function (event) {
        if (!isMobileDevice && event.data.type !== 'onScroll') {
            return;
        }

        resultAction = document.querySelector('#mbh-results-actions');

        if (resultAction === null) {
            return;
        }

        if (event.data.frameTopOffset > 0) {
            return;
        }

        coordsResultAction = 0;

        modFrameTopOffset = event.data.frameTopOffset * -1;

        if ((document.body.scrollHeight - screen.height) > modFrameTopOffset) {
            coordsResultAction = event.data.frameBottomOffset - screen.height;
        }

        resultAction.style.bottom = coordsResultAction + 'px';
    };

    window.addEventListener('message', mbhFixedBtnBooking);
};

MbhResultForm.prototype.removeEventFixedBtnBooking = function () {
    window.removeEventListener('message', mbhFixedBtnBooking);
};

MbhResultForm.prototype.descriptionToggle = function () {
    var _this = this;

    document.querySelectorAll('.mbh-results-room-type-description-wrapper').forEach(function(wrapper) {
        wrapper.querySelector('button').addEventListener('click', function(ev) {
            this.children[0].classList.toggle('fa-angle-down');
            wrapper.classList.toggle('show-description');
            _this.resize();
        })
    });
};

MbhResultForm.prototype.setFancyBoxOffset = function() {
    var frameOffset = 0;
    window.addEventListener('message', function(event) {
        var parentWindowData = event.data;
        if (parentWindowData.type === 'onScroll') {
            frameOffset = parentWindowData.frameTopOffset;
        }
    });

    if (document.body.scrollHeight > screen.height) {
        $('.fancybox').fancybox({
            'afterLoad': function () {
                var fancyTopOffset = screen.height / 2 - frameOffset - document.body.scrollHeight / 2;
                var offsetLimit = document.body.scrollHeight / 2 - screen.height / 2 + 30;
                if (fancyTopOffset > offsetLimit) {
                    fancyTopOffset = offsetLimit;
                } else if (fancyTopOffset * (-1) > offsetLimit) {
                    fancyTopOffset = (-1) * offsetLimit;
                }
                $('.fancybox-placeholder').css('top', fancyTopOffset);
            }
        });
    }
};

MbhResultForm.prototype.calcTotal = function () {
    var roomCountSelect = 0,
        $totalPackagesElement = jQuery('#mbh-results-total-packages-sum'),
        $packageInfoContainerRoom = jQuery('#mbh-results-package-info-container').find('.room-amount'),
        $packageInfoContainerGuest = jQuery('#mbh-results-package-info-container').find('.guest-amount');

    var calc = function() {
        roomCountSelect = 0;

        var totalPackages = 0,
            nextButton = jQuery('#mbh-results-next'),
            roomCount = jQuery('select.mbh-results-packages-count:not(.hidden), input.mbh-results-packages-count[type=checkbox]:checked');

        nextButton.prop('disabled', true);
        roomCount.each(function() {
            if (jQuery(this).val() > 0) {
                var priceContainer = jQuery(this).closest('.mbh-results-price-container'),
                    li = priceContainer.find('ul.mbh-results-prices li:visible');
                roomCountSelect += parseInt(jQuery(this).val());
                if (li.length) {
                    totalPackages += parseInt(li.attr('data-value')) * jQuery(this).val();
                }
            }
        });

        jQuery('#mbh-results-total-sum').html(totalPackages).digits();

        $totalPackagesElement.data('value', totalPackages);
        $totalPackagesElement.html(totalPackages).digits();

        if (totalPackages > 0) {
            nextButton.prop('disabled', false);
        }


        $packageInfoContainerRoom.text(roomCountSelect);
    };
    calc();

    jQuery('.mbh-results-tourists-select, .mbh-results-packages-count').on('click change', function() {
        calc();
    });
};

MbhResultForm.prototype.prepareAndGoStepTwo = function () {
    var _this = this;

    jQuery('#mbh-results-next').click(function() {
        window.parent.postMessage({
            type: 'form-event',
            purpose: 'rooms'
        }, "*");

        _this.removeEventFixedBtnBooking();

        var $totalPackagedSum = jQuery('#mbh-results-total-packages-sum'),
            roomCount = jQuery('select.mbh-results-packages-count:not(.hidden), input.mbh-results-packages-count[type=checkbox]:checked');
        _this._requestParams.begin = jQuery('#mbh-results-duration-begin').text();
        _this._requestParams.end = jQuery('#mbh-results-duration-end').text();
        _this._requestParams.days = jQuery('#mbh-results-duration-days').text();
        _this._requestParams.nights = jQuery('#mbh-results-duration-nights').text();
        _this._requestParams.total = jQuery('#mbh-results-total-sum').text();
        _this._requestParams.totalPackages = $totalPackagedSum.text();
        _this._requestParams.totalPackagesRaw = $totalPackagedSum.data('value');
        _this._requestParams.totalServices = 0;
        _this._requestParams.packages = [];
        _this._requestParams.services = [];
        _this._requestParams.locale = _this.getLocale();
        roomCount.each(function() {
            if (jQuery(this).val() > 0) {
                var resultsContainer = jQuery(this).closest('.mbh-results-container'),
                    pricesLi = resultsContainer.find('ul.mbh-results-prices li:visible'),
                    roomType = resultsContainer.find('span.mbh-results-room-type-name'),
                    hotel = resultsContainer.find('span.mbh-results-hotel'),
                    tariff = resultsContainer.find('span.mbh-results-tariff');
                for (var i = 1; i <= jQuery(this).val(); i++) {
                    if (pricesLi.length) {

                        var tourists = resultsContainer.find('select.mbh-results-tourists-select').val().split('_');

                        _this._requestParams.packages.push({
                            'price': parseInt(pricesLi.attr('data-value')),
                            'roomType': {
                                id: roomType.attr('data-id'),
                                'title': roomType.text()
                            },
                            'hotel': {
                                id: hotel.attr('data-id'),
                                'title': hotel.text()
                            },
                            'tariff': {
                                id: tariff.attr('data-id'),
                                'title': tariff.text()
                            },
                            'adults': tourists[0],
                            'children': tourists[1]
                        });
                    }
                }
            }
        });

        _this.waiting();

        _this.stepTwo()
    });
};

MbhResultForm.prototype.tablePrices = function () {
    var show = function(resultsContainer) {
        var tourist = resultsContainer.find('.mbh-results-tourists-select'),
            touristVal = tourist.select2('data').id,
            per_person = resultsContainer.find('.mbh-results-per-person');
        resultsContainer.find('ul.mbh-results-prices').hide();
        resultsContainer.find('ul.mbh-results-prices li').hide();
        resultsContainer.find('ul.mbh-results-prices li.' + touristVal).show();
        resultsContainer.find('ul.mbh-results-prices').show();

        if (parseInt(per_person.attr('data-change'), 10)) {
            var touriststHash = touristVal.split('_'),
                totalTourists = parseInt(touriststHash[0], 10) + parseInt(touriststHash[1], 10);
            var touristsPhrase = (totalTourists === 1) ? ' человека' : ' человек';

            per_person.html('цена за ' + totalTourists + touristsPhrase);
        }
    };

    jQuery('.mbh-results-tourists-select').each(function() {
        show(jQuery(this).closest('.mbh-results-container'));
    });

    this.setFancyBoxOffset();
};

MbhResultForm.prototype.tariffsAction = function () {
    var tariffsWrapper = jQuery('#mbh-results-tariffs');

    if (tariffsWrapper.length > 0) {

        tariffsWrapper.find('a').each(function() {
            jQuery(this).attr('href', jQuery(location).attr('pathname') + jQuery(this).attr('href'));
        });

        var textShow = this._text.tariffDesc.show,
            textHide = this._text.tariffDesc.hide;

        tariffsWrapper[0].querySelectorAll('.mbh-results-tariff-wrapper').forEach(function(wrapper) {
            var btn = wrapper.querySelector('.mbh-results-tariffs-description-toggle');

            if (btn === null) {
                return;
            }

            btn.addEventListener('click', function() {
                if (wrapper.classList.toggle('show-description')) {
                    btn.innerHTML = textHide;
                } else {
                    btn.innerHTML = textShow;
                }
            })
        })
    }
};

MbhResultForm.prototype.stepOne = function() {
    var _this = this;

    jQuery.ajax({
        url: this._urls.stepOne,
        data: this.searchData.url,
        dataType: 'html',
        crossDomain: true,
        success: function(data) {
            _this.wrapper.html(data);

            _this.resize();

            _this.fixedBtnBooking();

            _this.setSelect2();

            _this.descriptionToggle();

            _this.tariffsAction();

            _this.tablePrices();

            _this.calcTotal();

            _this.wrapper.trigger('results-load-event');

            _this.prepareAndGoStepTwo();
        }
    });
};
