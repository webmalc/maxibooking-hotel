MbhResultForm.prototype.stepFour = function() {
    var _this = this;

    jQuery.ajax({
        url: this._urls.results,
        type: 'POST',
        data: JSON.stringify(this._requestParams),
        dataType: 'json',
        crossDomain: true,
        success: function(data) {
            if (data.success) {
                _this.wrapper.trigger('booking-result-load-event');
                jQuery.removeCookie('mbh.package');

                _this.wrapper.html('<div class="mbh-results-info alert alert-info"><i class="fa fa-check-circle-o"></i> ' + data.message + '</div>');

                if (data.form) {
                    _this.wrapper.append(data.form);
                }
            } else {
                _this.wrapper.html('<div class="mbh-results-error alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + data.message + '</div>');
            }
        }
    });
};
