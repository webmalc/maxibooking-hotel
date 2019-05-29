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

            _this.setSelect2();
            var prevUser = jQuery.cookie('mbh.user');
            var prevPackage = jQuery.cookie('mbh.package');

            if (prevUser) {
                prevUser = JSON.parse(prevUser);
                jQuery('#mbh-user-form-firstName').val(prevUser.firstName);
                jQuery('#mbh-user-form-lastName').val(prevUser.lastName);
                jQuery('#mbh-user-form-phone').val(prevUser.phone);
                jQuery('#mbh-user-form-email').val(prevUser.email);
                jQuery('#mbh-user-form-birthday').val(prevUser.birthday);
            }

            if (prevPackage) {
                prevPackage = JSON.parse(prevPackage);
                jQuery('#mbh-user-form-note').val(prevPackage.note)
            }

            jQuery('#mbh-user-form-birthday').mask("99.99.9999");

            jQuery('#mbh-user-form-previous').click(function() {
                window.location.reload();
            });

            var getUser = function() {
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
            var getPackageInfo = function() {
                return {
                    'note': jQuery('#mbh-user-form-note').val()
                };
            };

            //save cookies
            (function() {
                jQuery('#mbh-user-form-form input, #mbh-user-form-form textarea').bind('propertychange change click keyup input paste', function() {
                    jQuery.cookie('mbh.user', JSON.stringify(getUser()));
                    jQuery.cookie('mbh.package', JSON.stringify(getPackageInfo()), {
                        expires: 1
                    });
                });
                jQuery('#mbh-user-form-form select').change(function() {
                    jQuery.cookie('mbh.package', JSON.stringify(getPackageInfo()), {
                        expires: 1
                    });
                });
            }());

            // validate user form
            (function() {

                var inputs = jQuery('#mbh-user-form input:required'),
                    validate = function() {
                        var nextButton = jQuery('#mbh-user-form-next');
                        nextButton.prop('disabled', false);
                        inputs.each(function() {
                            if (!jQuery(this).val() || (this.type === 'checkbox' && !$(this).is(':checked'))) {
                                nextButton.prop('disabled', true);
                                return false;
                            }
                        });
                        return true;
                    };
                validate();
                inputs.bind("propertychange change click keyup input paste", function() {
                    validate();
                })
            }());

            // validate user form email
            (function () {
                var validateEmail = function () {
                    var emailInput = $('#mbh-user-form-email');
                    var nextButton = $('#mbh-user-form-next');
                    if (emailInput.val() && !emailInput.val().match('^[a-z0-9._%+-]+@[a-z0-9._%+-]+\\.\\w{2,4}$')) {
                        nextButton.prop('disabled', true);
                        emailInput.css('border', '1px solid red');
                    } else {
                        nextButton.prop('disabled', false);
                        emailInput.css('border', '');
                    }
                };
                validateEmail();
                $('#mbh-user-form-email').bind('propertychange change keyup input paste blur', function () {
                    validateEmail();
                })
            }());

            // user form next button
            jQuery('#mbh-user-form-next').click(function() {
                window.parent.postMessage({
                    type: 'form-event',
                    purpose: 'contacts'
                }, "*");
                servicesCount = jQuery('select.mbh-results-services-count')

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
                _this._requestParams.user = getUser();
                _this._requestParams = jQuery.extend(_this._requestParams, getPackageInfo());

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
                _this._requestParams.formConfigId = _this.formConfigId;
                _this.waiting();

                // ------------------------------ STEP 3: load payment type -------------------------
                _this.stepThree();
            });
        }
    });
};
