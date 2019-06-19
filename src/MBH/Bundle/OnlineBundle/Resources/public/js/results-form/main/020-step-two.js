MbhResultForm.prototype.getPackageInfo = function () {
    return {
        'note': jQuery('#mbh-user-form-note').val()
    };
};

MbhResultForm.prototype.calcServices = function () {

    var _this = this,
        totalServiceTemp,
        totalServiceElement = document.querySelector('#mbh-package-info-total-services'),
        totalSumElement = document.querySelector('#mbh-package-info-total'),
        totalPackage = Number(document.querySelector('#mbh-package-info-total-packages').innerHTML.replace(/\s/g, ''));

    this.serviceListData = {};

    var calc = function() {
         totalServiceTemp = 0;

        for (serviceId in _this.serviceListData) {
            totalServiceTemp += _this.serviceListData[serviceId].price * _this.serviceListData[serviceId].amount;
        }

        totalServiceElement.innerHTML = _this.priceSeparator(totalServiceTemp.toFixed(1));
        totalSumElement.innerHTML = _this.priceSeparator((totalPackage + totalServiceTemp).toFixed(1));
    };

    document.querySelectorAll('.mbh-service-item').forEach(function(service) {
        var serviceId = service.querySelector('.mbh-results-services-name').dataset.id;
        var price = service.querySelector('.mbh-results-services-prices').dataset.value;

        _this.serviceListData[serviceId] = {
            price : Number(price),
            amount: 0
        };

        // jq for select2
        jQuery(service).find('.mbh-results-services-count').on('change', function() {
            _this.serviceListData[serviceId].amount = +this.value;
            calc();
        });
    });

};

MbhResultForm.prototype.prevData = function () {
    var prevUser = jQuery.cookie('mbh.user');
    if (prevUser) {
        prevUser = JSON.parse(prevUser);
        jQuery('#mbh-user-form-firstName').val(prevUser.firstName);
        jQuery('#mbh-user-form-lastName').val(prevUser.lastName);
        jQuery('#mbh-user-form-phone').val(prevUser.phone);
        jQuery('#mbh-user-form-email').val(prevUser.email);
        jQuery('#mbh-user-form-birthday').val(prevUser.birthday);
    }

    var prevPackage = jQuery.cookie('mbh.package');
    if (prevPackage) {
        prevPackage = JSON.parse(prevPackage);
        jQuery('#mbh-user-form-note').val(prevPackage.note)
    }
};

MbhResultForm.prototype.getUser = function () {
    var $innInput = jQuery('#mbh-user-form-inn');
    var $documentNumber = jQuery('#mbh-user-form-document-number');
    var $patronymicInput = jQuery('#mbh-user-form-patronymic');

    return {
        firstName: jQuery('#mbh-user-form-firstName').val(),
        lastName: jQuery('#mbh-user-form-lastName').val(),
        phone: jQuery('#mbh-user-form-phone').val(),
        email: jQuery('#mbh-user-form-email').val(),
        birthday: jQuery('#mbh-user-form-birthday').val(),
        inn: $innInput.length > 0 ? $innInput.val() : null,
        documentNumber: $documentNumber.length > 0 ? $documentNumber.val() : null,
        patronymic: $patronymicInput.length > 0 ? $patronymicInput.val() : null
    };
};

MbhResultForm.prototype.saveCookies = function () {
    var _this = this;

    jQuery('#mbh-user-form-form input, #mbh-user-form-form textarea').bind('propertychange change click keyup input paste', function() {
        jQuery.cookie('mbh.user', JSON.stringify(_this.getUser()));
        jQuery.cookie('mbh.package', JSON.stringify(_this.getPackageInfo()), {
            expires: 1
        });
    });
    jQuery('#mbh-user-form-form select').change(function() {
        jQuery.cookie('mbh.package', JSON.stringify(_this.getPackageInfo()), {
            expires: 1
        });
    });
};

MbhResultForm.prototype.changeStateNextBtn = function (isDisabled) {
    this.nextButtonInStepTwo.prop('disabled', isDisabled);
};

MbhResultForm.prototype.validateUserForm = function () {
    var _this = this,
        $inputs = jQuery('#mbh-user-form-form input:required'),
        $emailInput = jQuery('#mbh-user-form-email'),
        validateEmail = function () {
            return $emailInput.val().match('^[a-z0-9._%+-]+@[a-z0-9._%+-]+\\.\\w{2,4}$');
        },
        validate = function() {
            _this.changeStateNextBtn(false);
            $inputs.each(function() {
                if (!jQuery(this).val() || (this.type === 'checkbox' && !jQuery(this).is(':checked'))) {
                    _this.changeStateNextBtn(true);
                    return false;
                }
            });
            if ($emailInput.val()) {
                if (!validateEmail()) {
                    _this.changeStateNextBtn(true);
                    $emailInput.css('border', '1px solid red');
                    return false;
                } else {
                    $emailInput.css('border', '');
                    return true;
                }
            } else {
                $emailInput.css('border', '');
            }
        };

    validate();
    $inputs.bind("propertychange change click keyup input paste", function() {
        validate();
    })
};

MbhResultForm.prototype.prepareAndGoStepThree = function () {
    var _this = this;
    jQuery('#mbh-user-form-next').click(function() {
        window.parent.postMessage({
            type: 'form-event',
            purpose: 'contacts'
        }, "*");

        _this._requestParams.user = _this.getUser();
        _this._requestParams = jQuery.extend(_this._requestParams, _this.getPackageInfo());

        var totalServices = 0;

        var service;
        for (var serviceId in _this.serviceListData) {
            service = _this.serviceListData[serviceId];
            if (service.amount > 0) {
                _this._requestParams.services.push({
                    id: serviceId,
                    amount: service.amount
                });

                totalServices += service.amount * service.price
            }
        }

        var total = _this._requestParams.total;

        total += totalServices;
        _this._requestParams.total = total;
        _this._requestParams.totalServices = totalServices;

        var $personalDataCheckbox = jQuery('#mbh-form-personal-data');
        _this._requestParams.isConfrmWithPersDataProcessing
            = $personalDataCheckbox.length === 1 && $personalDataCheckbox.is(':checked');

        _this.waiting();

        _this.stepThree();
    });
};

MbhResultForm.prototype.stepTwo = function() {
    var _this = this;

    jQuery.ajax({
        url: this._urls.stepTwo,
        type: 'POST',
        data: JSON.stringify(this._requestParams),
        dataType: 'html',
        crossDomain: true,
        success: function(data) {
            _this.wrapper.html(data);

            _this.resize();

            _this.scrollToTopIframe();

            _this.wrapper.trigger('user-form-load-event');

            _this.nextButtonInStepTwo = jQuery('#mbh-user-form-next');

            _this.setSelect2();

            _this.prevData();

            _this.calcServices();

            jQuery('#mbh-user-form-birthday').mask("99.99.9999");

            _this.addEventReloadPage('#mbh-user-form-previous');

            _this.saveCookies();

            _this.validateUserForm();

            _this._requestParams.useServices = document.querySelector('#mbh-package-info-total-services') !== null;

            _this.prepareAndGoStepThree();
        }
    });
};
