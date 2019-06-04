MbhResultForm.prototype.togglePaymentSystemVisibility = function () {
    var _this = this,
        paymentSystemsBlock = document.querySelector('#mbh-payment-systems');
        listPaymentRow = document.querySelectorAll('.mbh-payment-type-row');

    listPaymentRow.forEach(function(wrapper) {
        var input = wrapper.querySelector('.mbh-payment-types-radio');
        wrapper.addEventListener('click', function() {
            listPaymentRow.forEach(function(wrapperSecond){
                wrapperSecond.classList.remove('selected');
            });
            input.checked = true;
            paymentSystemsBlock.hidden = _this.paymentTypes.onlines.indexOf(input.value) === -1;

            wrapper.classList.add('selected');
            _this.resize();
        })
    });
};

MbhResultForm.prototype.validateAndCalc = function () {
    var _this = this,
        total = this._requestParams.total.replace(/,/g, ''),
        totalPackages = this._requestParams.totalPackages.replace(/,/g, ''),
        totalServices = this._requestParams.totalServices.replace(/,/g, '');

    var validate = function() {
        var $selectedPaymentType = jQuery('.mbh-payment-types-radio:checked');
        var isFormNotValid = !$selectedPaymentType.length
            || (_this.paymentTypes.onlines.indexOf($selectedPaymentType.val()) > -1 &&
                !jQuery('#mbh-form-payment-system').val());

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

        if (type.length) {
            if (type.val() === _this.paymentTypes.online.firstDay || type.val() === _this.paymentTypes.receipt.firstDay) {
                sumPackages = Math.round(totalPackages / _this._requestParams.nights);
                sum = parseInt(totalPackages, 10) + parseInt(totalServices, 10);
                sumHidden = parseInt(sumPackages, 10) + parseInt(totalServices, 10);
            }

            if (type.val() === _this.paymentTypes.online.half || type.val() === _this.paymentTypes.receipt.half) {
                sumPackages = Math.round(totalPackages / 2);
                sum = total / 2;
                sumHidden = total / 2;
            }
        }

        totalWrapper.html(sum);
        totalHidden.html(sumHidden);
        totalPackagesWrapper.html(sumPackages);

    };

    validate();
    jQuery('.mbh-payment-type-row').click(function() {
        validate();
        calc();
    });
};

MbhResultForm.prototype.prepareAndGoStepFour = function () {
    var _this = this;
    jQuery('#mbh-payment-types-next').click(function() {
        window.parent.postMessage({
            type: 'form-event',
            purpose: 'choose'
        }, "*");
        _this._requestParams.total = jQuery('#mbh-package-info-total-hidden').text();
        _this._requestParams.paymentType = jQuery('.mbh-payment-types-radio:checked').val();
        _this._requestParams.paymentSystem = jQuery('#mbh-form-payment-system').val();

        _this.waiting();

        _this.stepFour();
    });
};

MbhResultForm.prototype.stepThree = function() {
    var _this = this;

    jQuery.ajax({
        url: this._urls.stepThree,
        type: 'POST',
        data: JSON.stringify(this._requestParams),
        dataType: 'html',
        crossDomain: true,
        success: function(data) {
            _this.wrapper.trigger('payment-form-load-event');

            _this.wrapper.html(data);

            _this.resize();

            _this.togglePaymentSystemVisibility();

            _this.addEventReloadPage('#mbh-payment-types-previous');

            _this.validateAndCalc();

            _this.prepareAndGoStepFour();
        }
    });
};
