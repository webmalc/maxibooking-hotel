MbhResultForm.prototype.backgroundImageRoomForTablet = function () {
    var isTablet = 700 < screen.availWidth && screen.availWidth < 1023;
    document.querySelectorAll('.image-room-link').forEach(function(image) {
        image.style.backgroundImage = isTablet ? image.dataset.backgroundTablet : image.dataset.background;
    });
};

MbhResultForm.prototype.createImageContainer = function (roomTypeId) {
    this.sendPostMessage('createImageContainer', {roomTypeId: roomTypeId});
};

MbhResultForm.prototype.subscribeForHideAllImage = function () {
    var _this = this;
    this._hideAllImageFunction = function(ev) {
        if (ev.code === 'Escape') {
            _this.sendPostMessage('hideAllImage');
        }
    };

    window.addEventListener('keyup', this._hideAllImageFunction);
};

MbhResultForm.prototype.unsubscribeForHideAllImage = function () {
    window.removeEventListener('keyup', this._hideAllImageFunction);
};

MbhResultForm.prototype.initSwipeStepOne = function () {
    var _this = this;
    this._swiperListStepOne = [];

    document.querySelectorAll('.swiper-container').forEach(function(container) {
        var roomTypeId = container.dataset.roomTypeId,
            imageLinkList = container.querySelectorAll('.image-room-link');

        if (imageLinkList.length > 0) {
            _this.createImageContainer(roomTypeId);
            imageLinkList.forEach(function(imageLink) {
                imageLink.addEventListener('click', function(event) {
                    event.preventDefault();
                    _this.sendPostMessage('openImage', {roomTypeId: roomTypeId, imageId: this.dataset.imageId});
                });
            });
        }

        if (container.dataset.useSwipe !== 'true') {
            return;
        }
        var swipe = new Swiper (container, {
            loop: true,
            navigation: {
                nextEl: '.button-next-' + roomTypeId,
                prevEl: '.button-prev-' + roomTypeId
            }
        });

        _this._swiperListStepOne.push(swipe);
    });
};

MbhResultForm.prototype.destroySwipeStepOne = function () {
    this._swiperListStepOne.forEach(function(swipe) {
        swipe.destroy(true, true)
    });
};

MbhResultForm.prototype.descriptionToggle = function () {
    var _this = this;

    document.querySelectorAll('.mbh-results-room-type-description-wrapper').forEach(function(descriptionWrapper) {
        var btn = descriptionWrapper.querySelector('button');
        if (btn === null) {
            return;
        }

        var btnTitleShow = btn.innerText;

        btn.addEventListener('click', function(ev) {
            btn.innerText = descriptionWrapper.classList.toggle('show-description') ? _this._text.roomDesc.btnHide : btnTitleShow;
            _this.resize();
        })
    });
};

MbhResultForm.prototype.calcTotal = function () {
    var _this = this,
        tempRoomCount,
        tempAmountGuest,
        roomCountSelect = 0,
        amountGuest = 0;

    var calc = function() {
        roomCountSelect = 0;
        amountGuest = 0;

        var totalPackages = 0,
            nextButton = jQuery('#mbh-results-next'),
            roomCount = jQuery('select.mbh-results-packages-count:not(.hidden), input.mbh-results-packages-count[type=checkbox]:checked');

        var $selectGuest;

        nextButton.prop('disabled', true);
        roomCount.each(function() {
            tempRoomCount = 0;
            tempAmountGuest = 0;
            if (jQuery(this).val() > 0) {
                var priceContainer = jQuery(this).closest('.mbh-results-price-container'),
                    li = priceContainer.find('ul.mbh-results-prices li:visible');

                $selectGuest = jQuery(this).closest('.mbh-results-container').find('select.mbh-results-tourists-select');

                tempRoomCount = parseInt(jQuery(this).val());
                tempAmountGuest = $selectGuest.val()
                                    .split('_')
                                    .map(function(value) { return parseInt(value); })
                                    .reduce(function(previousValue, currentValue) {
                                        return previousValue + currentValue;
                                    });

                amountGuest += tempRoomCount * tempAmountGuest;

                roomCountSelect += tempRoomCount;
                if (li.length) {
                    totalPackages += Number(li.attr('data-value')) * tempRoomCount;
                }
            }
        });

        _this._totalPackage = Number(totalPackages.toFixed(1));

        _this.sendDataStepOneButton('selectPackage', {
            amountRoom: roomCountSelect,
            amountGuest: amountGuest,
            totalPackage: _this._totalPackage
        });
    };
    calc();

    jQuery('.mbh-results-tourists-select, .mbh-results-packages-count').on('click change', function() {
        calc();
    });
};

MbhResultForm.prototype.prepareAndGoStepTwo = function () {
    var _this = this;

    window.addEventListener('message', function(e) {
        if (e.data.type !== 'mbh') {
            return;
        }

        if (e.data.action === 'clickNextButton') {
            window.parent.postMessage({
                type: 'form-event',
                purpose: 'rooms'
            }, "*");

            var roomCount = jQuery('select.mbh-results-packages-count:not(.hidden), input.mbh-results-packages-count[type=checkbox]:checked');
            _this._requestParams.begin = jQuery('#mbh-results-duration-begin').text();
            _this._requestParams.end = jQuery('#mbh-results-duration-end').text();
            _this._requestParams.days = jQuery('#mbh-results-duration-days').text();
            _this._requestParams.nights = jQuery('#mbh-results-duration-nights').text();
            _this._requestParams.total = _this._totalPackage;
            _this._requestParams.totalPackages = _this._totalPackage;
            _this._requestParams.totalServices = 0;
            _this._requestParams.packages = [];
            _this._requestParams.services = [];
            _this._requestParams.dataPackageInfo = {};
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

                            var tourists = resultsContainer.find('select.mbh-results-tourists-select').val().split('_'),
                                roomTypeId = roomType.attr('data-id'),
                                roomTitle = roomType.text(),
                                hotelTitle = hotel.text();

                            if (typeof _this._requestParams.dataPackageInfo[roomTypeId] === 'undefined') {
                                _this._requestParams.dataPackageInfo[roomTypeId] = {
                                    count: 1,
                                    package: {
                                        roomTitle: roomTitle,
                                        hotelTitle: hotelTitle
                                    }
                                }
                            } else {
                                _this._requestParams.dataPackageInfo[roomTypeId].count++;
                            }

                            _this._requestParams.packages.push({
                                'price': Number(pricesLi.attr('data-value')),
                                'roomType': {
                                    id: roomTypeId,
                                    'title': roomTitle
                                },
                                'hotel': {
                                    id: hotel.attr('data-id'),
                                    'title': hotelTitle
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

            _this.sendPostMessage('hideStepOneButton');

            _this.destroySwipeStepOne();

            _this.unsubscribeForHideAllImage();

            _this.waiting();

            _this.stepTwo()
        }
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

MbhResultForm.prototype.sendDataStepOneButton = function (action, data) {
    this.sendPostMessage(action, data, 'stepOneButton');
};

MbhResultForm.prototype.stepOne = function() {
    var _this = this;
    this._totalPackage = 0;

    jQuery.ajax({
        url: this._urls.stepOne,
        data: this.searchData.url + '&screenWidth=' + screen.availWidth,
        dataType: 'html',
        crossDomain: true,
        success: function(data) {
            _this.wrapper.html(data);

            _this.backgroundImageRoomForTablet();

            _this.resize();

            _this.sendDataStepOneButton('packageDate', {
                begin: jQuery('#mbh-results-duration-begin').text(),
                end: jQuery('#mbh-results-duration-end').text()
            });

            _this.sendPostMessage('showStepOneButton');

            _this.setSelect2();

            _this.descriptionToggle();

            _this.tariffsAction();

            _this.tablePrices();

            _this.initSwipeStepOne();

            _this.subscribeForHideAllImage();

            _this.calcTotal();

            _this.wrapper.trigger('results-load-event');

            _this.prepareAndGoStepTwo();
        }
    });
};
