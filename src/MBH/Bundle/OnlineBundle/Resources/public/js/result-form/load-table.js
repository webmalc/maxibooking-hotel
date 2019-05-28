MbhResultForm.prototype.loadTable = function() {
    var _this = this;

    jQuery.ajax({
        url: this._urls.table,
        data: this.searchData.url,
        dataType: 'html',
        crossDomain: true,
        success: function(data) {
            _this.wrapper.html(data);
            _this.resize();

            _this.setSelect2();
            //show full image
            // (function() {
            //     var wrapperImage = jQuery('#mbh-image-show-wrapper');
            //     jQuery('.mbh-roomType-image').each(function() {
            //         jQuery(this).hover(function(e) {
            //             var offset = {
            //                 top: jQuery(this).offset().top + 15,
            //                 left: jQuery(this).offset().left + jQuery(this).width() - 25
            //             };
            //             wrapperImage.offset(offset);
            //             wrapperImage.html('<img src="' + jQuery(this).attr('src') + '">');
            //             if (wrapperImage.find('img').width() > 100) {
            //                 wrapperImage.css('visibility', "visible");
            //             }
            //         }, function() {
            //             wrapperImage.css('visibility', "hidden");
            //             wrapperImage.find('img').remove();
            //         })
            //     });
            // }());

            // remove border
            (function() {
                jQuery('#mbh-results-table tbody tr:last td').css('border', '0px');
            }());

            //description toggle
            (function() {
                jQuery('.mbh-results-roomType-description-tr').hide(function() {
                    _this.resize();
                });
                jQuery('.mbh-results-roomType-description-link').click(function(e) {
                    e.preventDefault();
                    jQuery(this).closest('tr').find('td').toggleClass('without-border');
                    jQuery(this).closest('tr').siblings('.mbh-results-roomType-description-tr').toggle();
                    jQuery(this).children('i').toggleClass('fa-angle-down');

                    _this.resize();
                });
            }());

            //tariff links
            (function() {
                jQuery('#mbh-results-tariffs a').each(function() {
                    jQuery(this).attr('href', jQuery(location).attr('pathname') + jQuery(this).attr('href'));
                })
            }());

            // table prices
            (function() {
                var show = function(tr) {
                    var tourist = tr.find('.mbh-results-tourists-select'),
                        touristVal = tourist.select2('data').id,
                        per_person = tr.find('.mbh-results-per-person');
                    tr.find('ul.mbh-results-prices').hide();
                    tr.find('ul.mbh-results-prices li').hide();
                    tr.find('ul.mbh-results-prices li.' + touristVal).show();
                    tr.find('ul.mbh-results-prices').show();

                    if (parseInt(per_person.attr('data-change'), 10)) {
                        var touriststHash = touristVal.split('_'),
                            totalTourists = parseInt(touriststHash[0], 10) + parseInt(touriststHash[1], 10);
                        var touristsPhrase = (totalTourists === 1) ? ' человека' : ' человек';

                        per_person.html('цена за ' + totalTourists + touristsPhrase);
                    }
                };
                jQuery('.mbh-results-tourists-select').click(function() {
                    show(jQuery(this).closest('tr'));
                });
                jQuery('.mbh-results-tourists-select').each(function() {
                    show(jQuery(this).closest('tr'));
                });

                (function setFancyBoxOffset() {
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
                })();
            }());

            //colorize tr
            (function() {
                var show = function() {
                    var selected = jQuery('select.mbh-results-packages-count, input.mbh-results-packages-count[type=checkbox]:checked').filter(function() {
                        return parseInt(jQuery(this).val(), 10) > 0;
                    });
                    jQuery('#mbh-results-table').find('tr').removeClass('mbh-result-selected-tr warning');
                    if (selected.length) {
                        selected.each(function() {
                            jQuery(this).closest('tr').addClass('mbh-result-selected-tr warning');

                        });
                    }
                };
                show();
                jQuery('.mbh-results-packages-count').change(function() {
                    show();
                });
            }());

            //show services
            (function() {
                var servicesWrapper = jQuery('#mbh-results-services-wrapper'),
                    show = function() {
                        var selected = jQuery('select.mbh-results-packages-count:not(.hidden), input.mbh-results-packages-count[type=checkbox]:checked').filter(function() {
                            return parseInt(jQuery(this).val(), 10) > 0;
                        });
                        servicesWrapper.hide();
                        servicesWrapper.find('tbody tr').hide();

                        jQuery('#mbh-results-table').find('tr').removeClass('mbh-result-selected-tr warning');

                        if (selected.length) {
                            var i = 0;
                            selected.each(function() {
                                var hotelId = jQuery(this).closest('tr').find('span.mbh-results-hotel').attr('data-id');
                                jQuery(this).closest('tr').addClass('mbh-result-selected-tr warning');
                                jQuery('span.mbh-results-services-hotel').filter(function() {
                                    if (jQuery(this).attr('data-id') == hotelId) {
                                        i++;
                                        return true;
                                    } else {
                                        return false;
                                    }

                                }).closest('tr').show();
                            });
                            if (i) {
                                servicesWrapper.show();
                            }
                        }
                    };

                if (jQuery('#mbh-results-table-services-th-hotel').length) {
                    show();
                    jQuery('.mbh-results-packages-count').change(function() {
                        show();
                    });
                }
            }());

            // calc total
            (function() {
                var calc = function() {
                    var totalPackages = 0,
                        totalServices = 0,
                        total = 0,
                        nextButton = jQuery('#mbh-results-next'),
                        roomCount = jQuery('select.mbh-results-packages-count:not(.hidden), input.mbh-results-packages-count[type=checkbox]:checked'),
                        servicesCount = jQuery('select.mbh-results-services-count');
                    nextButton.prop('disabled', true);
                    roomCount.each(function() {
                        if (jQuery(this).val() > 0) {
                            var tr = jQuery(this).closest('tr'),
                                li = tr.find('ul.mbh-results-prices li:visible');
                            if (li.length) {
                                totalPackages += parseInt(li.attr('data-value')) * jQuery(this).val();
                            };
                        }
                    });
                    servicesCount.each(function() {
                        if (jQuery(this).val() > 0) {
                            var tr = jQuery(this).closest('tr'),
                                span = tr.find('span.mbh-results-services-prices');
                            if (span.length) {
                                totalServices += parseInt(span.attr('data-value')) * jQuery(this).val();
                            }
                        }
                    });

                    total = totalServices + totalPackages;

                    jQuery('#mbh-results-total-sum').html(total).digits();
                    jQuery('#mbh-results-total-packages-sum').html(totalPackages).digits();
                    jQuery('#mbh-results-total-services-sum').html(totalServices).digits();
                    if (totalPackages > 0) {
                        nextButton.prop('disabled', false);
                    }
                };
                calc();
                jQuery('.mbh-results-tourists-select, .mbh-results-packages-count, .mbh-results-services-count').on('click change', function() {
                    calc();
                });
            }());

            _this.wrapper.trigger('results-load-event');

            // results next button
            (function() {
                jQuery('#mbh-results-next').click(function() {
                    window.parent.postMessage({
                        type: 'form-event',
                        purpose: 'rooms'
                    }, "*");
                    var roomCount = jQuery('select.mbh-results-packages-count:not(.hidden), input.mbh-results-packages-count[type=checkbox]:checked'),
                        servicesCount = jQuery('select.mbh-results-services-count');
                    _this._requestParams.begin = jQuery('#mbh-results-duration-begin').text();
                    _this._requestParams.end = jQuery('#mbh-results-duration-end').text();
                    _this._requestParams.days = jQuery('#mbh-results-duration-days').text();
                    _this._requestParams.nights = jQuery('#mbh-results-duration-nights').text();
                    _this._requestParams.total = jQuery('#mbh-results-total-sum').text();
                    _this._requestParams.totalPackages = jQuery('#mbh-results-total-packages-sum').text();
                    _this._requestParams.totalServices = jQuery('#mbh-results-total-services-sum').text();
                    _this._requestParams.packages = [];
                    _this._requestParams.services = [];
                    _this._requestParams.locale = _this.getLocale();
                    _this._requestParams.configId = _this.formConfigId;
                    roomCount.each(function() {
                        if (jQuery(this).val() > 0) {
                            var tr = jQuery(this).closest('tr'),
                                pricesLi = tr.find('ul.mbh-results-prices li:visible'),
                                roomType = tr.find('span.mbh-results-roomType'),
                                hotel = tr.find('span.mbh-results-hotel'),
                                tariff = tr.find('span.mbh-results-tariff');
                            for (var i = 1; i <= jQuery(this).val(); i++) {
                                if (pricesLi.length) {

                                    var tourists = tr.find('select.mbh-results-tourists-select').val().split('_')

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
                    servicesCount.each(function() {
                        if (jQuery(this).val() > 0) {
                            var tr = jQuery(this).closest('tr'),
                                id = tr.find('span.mbh-results-services-name').attr('data-id');
                            _this._requestParams.services.push({
                                'id': id,
                                'amount': jQuery(this).val()
                            });
                        }
                    });

                    //jQuery('#mbh-results-table-wrapper').html('sdsd'); return 1;

                    _this.waiting();

                    // ----------------------- STEP2: load user form ----------------------------------------
                    _this.stepTwo()
                });
            }());
        }
    });
};
