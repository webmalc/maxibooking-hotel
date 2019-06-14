MbhResultForm.prototype.paymentBtnEvent = function () {
    var btn = document.querySelector('#mbh-online-payment-btn');

    if (btn === null) {
        return;
    }

    btn.addEventListener('click' , function(evt) {
        setTimeout(function() {
            evt.target.disabled = true;
        }, 1000);
    })
};

MbhResultForm.prototype.stepFour = function() {
    var _this = this;

    jQuery.ajax({
        url: this._urls.stepFour,
        type: 'POST',
        data: JSON.stringify(this._requestParams),
        dataType: 'json',
        crossDomain: true,
        success: function(data) {
            if (data.success) {
                _this.wrapper.trigger('booking-result-load-event');
                jQuery.removeCookie('mbh.package');
            }

            _this.wrapper.html(data.html);

            _this.resize();

            _this.scrollToTopIframe();

            _this.paymentBtnEvent();
        }
    });
};
