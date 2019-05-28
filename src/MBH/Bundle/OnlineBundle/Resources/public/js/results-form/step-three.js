MbhResultForm.prototype.stepThree = function() {
    var _this = this;

    jQuery.ajax({
        url: this._urls.paymentType,
        type: 'POST',
        data: JSON.stringify(this._requestParams),
        dataType: 'html',
        crossDomain: true,
        success: function(data) {
            _this.wrapper.trigger('payment-form-load-event');

            var total = _this._requestParams.total.replace(/,/g, ''),
                totalPackages = _this._requestParams.totalPackages.replace(/,/g, ''),
                totalServices = _this._requestParams.totalServices.replace(/,/g, '');
            _this.wrapper.html(data);

            _this.resize();
            jQuery('#mbh-payment-types-previous').click(function() {
                window.location.reload();
            });

            // user payment form validate & calc total
            (function() {
                var validate = function() {
                    var $selectedPaymentType = jQuery('.mbh-payment-types-radio:checked');
                    var isFormNotValid = !$selectedPaymentType.length
                        || (_this.paymentTypes.onlines.indexOf($selectedPaymentType.val()) > -1 && !jQuery('#mbh-form-payment-system').val());
                    jQuery('#mbh-payment-types-next').prop('disabled', isFormNotValid);
                };

                var calc = function() {
                    var type = jQuery('.mbh-payment-types-radio:checked'),
                        totalWrapper = jQuery('#mbh-package-info-total'),
                        totalHidden = jQuery('#mbh-package-info-total-hidden'),
                        totalPackagesWrapper = jQuery('#mbh-package-info-total-packages'),
                        sum = total,
                        sumHidden = total,
                        sumPackages = totalPackages;
                    if (type.length
                        && (type.val() === _this.paymentTypes.online.firstDay || type.val() === _this.paymentTypes.receipt.firstDay)
                    ) {
                        sumPackages = Math.round(totalPackages / _this._requestParams.nights);
                        sum = parseInt(totalPackages, 10) + parseInt(totalServices, 10);
                        sumHidden = parseInt(sumPackages, 10) + parseInt(totalServices, 10);
                    }
                    if (type.length
                        && (type.val() === _this.paymentTypes.online.full || type.val() === _this.paymentTypes.receipt.full)
                    ) {
                        sumPackages = Math.round(totalPackages / 2);
                        sum = total / 2;
                        sumHidden = total / 2;
                    }
                    totalWrapper.html(sum);
                    totalHidden.html(sumHidden);
                    totalPackagesWrapper.html(sumPackages);

                };
                var togglePaymentSystemVisibility = function () {
                    var selectedPaymentType = jQuery('.mbh-payment-types-radio:checked').val();
                    var $paymentSystemsBlock = $('#mbh-payment-systems');
                    if (_this.paymentTypes.onlines.indexOf(selectedPaymentType) > -1) {
                        $paymentSystemsBlock.show();
                    } else {
                        $paymentSystemsBlock.hide();
                    }
                };
                validate();
                jQuery('.mbh-payment-types-radio, #mbh-form-payment-system').change(function() {
                    validate();
                    calc();
                    togglePaymentSystemVisibility();
                });
            }());

            //user payment types next button
            jQuery('#mbh-payment-types-next').click(function() {
                window.parent.postMessage({
                    type: 'form-event',
                    purpose: 'choose'
                }, "*");
                _this._requestParams.total = jQuery('#mbh-package-info-total-hidden').text();
                _this._requestParams.paymentType = jQuery('.mbh-payment-types-radio:checked').val();
                _this._requestParams.paymentSystem = jQuery('#mbh-form-payment-system').val();

                _this.waiting();

                // STEP 4: results
                _this.stepFour();
            });
        }
    });
};
