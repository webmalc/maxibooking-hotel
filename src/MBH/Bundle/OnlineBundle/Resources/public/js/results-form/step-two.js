MbhResultForm.prototype.getPackageInfo = function () {
    return {
        'note': jQuery('#mbh-user-form-note').val()
    };
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

MbhResultForm.prototype.changeStateNextBtn = function (state) {
    this.nextButtonInStepTwo.prop('disabled', state);
};

MbhResultForm.prototype.validateUserForm = function () {
    var _this = this,
        inputs = jQuery('#mbh-user-form input:required'),
        validate = function() {
            _this.changeStateNextBtn(false);
            inputs.each(function() {
                if (!jQuery(this).val() || (this.type === 'checkbox' && !$(this).is(':checked'))) {
                    _this.changeStateNextBtn(true);
                    return false;
                }
            });
            return true;
        };
    validate();
    inputs.bind("propertychange change click keyup input paste", function() {
        validate();
    })
};

MbhResultForm.prototype.validateUserFormEmail = function () {
    var _this = this,
        emailInput = jQuery('#mbh-user-form-email'),
        emailIsRequired = emailInput.prop('required');

    var validateEmail = function () {

        if (emailInput.val() && !emailInput.val().match('^[a-z0-9._%+-]+@[a-z0-9._%+-]+\\.\\w{2,4}$')) {
            if (emailIsRequired) {
                _this.changeStateNextBtn(true);
            }
            emailInput.css('border', '1px solid red');
        } else {
            if (emailIsRequired) {
                _this.changeStateNextBtn(false);
            }
            emailInput.css('border', '');
        }
    };
    validateEmail();
    emailInput.bind('propertychange change keyup input paste blur', function () {
        validateEmail();
    })
};

MbhResultForm.prototype.prepareAndGoStepThree = function () {
    var _this = this;
    jQuery('#mbh-user-form-next').click(function() {
        window.parent.postMessage({
            type: 'form-event',
            purpose: 'contacts'
        }, "*");
        servicesCount = jQuery('select.mbh-results-services-count');

        servicesCount.each(function() {
            if (jQuery(this).val() > 0) {
                var tr = jQuery(this).closest('.mbh-service-item'),
                    id = tr.find('span.mbh-results-services-name').attr('data-id');
                _this._requestParams.services.push({
                    'id': id,
                    'amount': jQuery(this).val()
                });
            }
        });
        _this._requestParams.user = _this.getUser();
        _this._requestParams = jQuery.extend(_this._requestParams, _this.getPackageInfo());

        var totalServices = 0;
        servicesCount.each(function() {
            if (jQuery(this).val() > 0) {
                var tr = jQuery(this).closest('tr'),
                    span = tr.find('span.mbh-results-services-prices');
                if (span.length) {
                    totalServices += parseInt(span.attr('data-value')) * jQuery(this).val();
                }
            }
        });
        var total = parseInt(_this._requestParams.total.replace(/,/g, ''));

        total += totalServices;
        _this._requestParams.total = String(total);
        _this._requestParams.totalServices = String(totalServices);

        var $personalDataCheckbox = $('#mbh-form-personal-data');
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

            _this.wrapper.trigger('user-form-load-event');

            _this.nextButtonInStepTwo = jQuery('#mbh-user-form-next');

            _this.setSelect2();

            _this.prevData();

            jQuery('#mbh-user-form-birthday').mask("99.99.9999");

            _this.addEventReloadPage('#mbh-user-form-previous');

            _this.saveCookies();

            _this.validateUserForm();

            _this.validateUserFormEmail();

            _this._requestParams.useServices = document.querySelector('#mbh-package-info-total-services') !== null;

            _this.prepareAndGoStepThree();
        }
    });
};
