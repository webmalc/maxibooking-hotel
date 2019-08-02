MbhResultForm.prototype.togglePaymentSystemVisibility = function () {
    var _this = this,
        paymentSystemsBlock = document.querySelector('#mbh-payment-systems'),
        listPaymentRow = document.querySelectorAll('.mbh-payment-type-row'),
        useOnlyOneSystem = paymentSystemsBlock.dataset.onlyOneSystem === '1';

    listPaymentRow.forEach(function(wrapper) {
        var input = wrapper.querySelector('.mbh-payment-types-radio');
        wrapper.addEventListener('click', function() {
            listPaymentRow.forEach(function(wrapperSecond){
                wrapperSecond.classList.remove('selected');
            });
            input.checked = true;

            if (!useOnlyOneSystem) {
                paymentSystemsBlock.hidden = _this.paymentTypes.onlines.indexOf(input.value) === -1;
            }

            wrapper.classList.add('selected');
            _this.resize();
        })
    });
};

MbhResultForm.prototype.copyPriceToPay = function (element) {
    document.querySelector('#total-to-pay').innerHTML = element.querySelector('.price-wrapper').innerText;
};

MbhResultForm.prototype.checkSelectedPaymentType = function () {
  var _this = this;

  document.querySelectorAll('.mbh-payment-type-row.selected').forEach(function(element) {
      _this.copyPriceToPay(element);
  });
};

MbhResultForm.prototype.validateAndCalc = function () {
    var _this = this;

    var validate = function() {
        var $selectedPaymentType = jQuery('.mbh-payment-types-radio:checked');
        var isFormNotValid = !$selectedPaymentType.length
            || (_this.paymentTypes.onlines.indexOf($selectedPaymentType.val()) > -1 &&
                !jQuery('#mbh-form-payment-system').val());

        jQuery('#mbh-payment-types-next').prop('disabled', isFormNotValid);
    };

    validate();
    jQuery('.mbh-payment-type-row').click(function() {
        validate();
        _this.copyPriceToPay(this);
    });
};

MbhResultForm.prototype.prepareAndGoStepFour = function () {
    var _this = this;
    document.querySelector('#mbh-payment-types-next').addEventListener('click', function() {
        window.parent.postMessage({
            type: 'form-event',
            purpose: 'choose'
        }, "*");
        _this._requestParams.totalToPay = document.querySelector('#total-to-pay').innerHTML.replace(/\s/g,'');
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

            _this.checkSelectedPaymentType();

            _this.scrollToTopIframe();

            _this.togglePaymentSystemVisibility();

            _this.addEventReloadPage('#mbh-payment-types-previous');

            _this.validateAndCalc();

            _this.calcAndSetHeightPackageInfo();

            _this.prepareAndGoStepFour();
        }
    });
};
