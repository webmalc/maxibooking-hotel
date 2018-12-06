/*global Translator */

/**
 * @author Arofikin Alexandr
 * @param {function} yesHandler
 * @param {function} noHandler
 * @constructor
 */
var LateEarlyDateChecker = function(yesHandler, noHandler) {
    this.$modal = $('#late-early-check');

    if(this.$modal.length != 1) {
        return;
        //throw new Error('LateEarlyChecker modal window is not found!'); // see late-early-check-modal.html.twig
    }

    this.$yes = $('#late-early-check-yes');
    this.$no = $('#late-early-check-no');
    this.arrivalHours = parseInt(this.$modal.data('arrival-hours'));
    this.yesHandler = yesHandler;
    this.noHandler = noHandler;
    this.formData = {
        amount: 0
    };
    this.status = null;
    this.bindEventListeners();
}
//constants
LateEarlyDateChecker.STATUS_ARRIVAL = 'arrival';
LateEarlyDateChecker.STATUS_DEPARTURE = 'departure';
LateEarlyDateChecker.STATUS_BOTH = 'both';

LateEarlyDateChecker.prototype.statusTexts = {};
LateEarlyDateChecker.prototype.statusTexts[LateEarlyDateChecker.STATUS_ARRIVAL] = Translator.trans("late_early_date_checker.guests_arrived_earlier");
LateEarlyDateChecker.prototype.statusTexts[LateEarlyDateChecker.STATUS_DEPARTURE] = Translator.trans("late_early_date_checker.guests_departured_earlier");
LateEarlyDateChecker.prototype.statusTexts[LateEarlyDateChecker.STATUS_BOTH] = Translator.trans("late_early_date_checker.guests_check_in_earlier");

LateEarlyDateChecker.prototype.bindEventListeners = function() {
    var that = this;
    this.$yes.on('click', function() {
        that.updateFormData();
        that.$modal.modal('hide');
        that.yesHandler.call(that);
    });
    this.$no.on('click', function() {
        that.$modal.modal('hide');
        that.noHandler.call(that)
    });
}
LateEarlyDateChecker.prototype.updateFormData = function() {
    this.formData.amount = this.$modal.find('form input[name=amount]').val();
}
LateEarlyDateChecker.prototype.show = function() {
    this.updateView();
    this.$modal.modal('show');
}
LateEarlyDateChecker.prototype.updateView = function() {
    if (this.status) {
        var text = this.statusTexts[this.status];
        this.$modal.find('.modal-body p').text(text);
    } else {
        throw new Error('Status is not exits');
    }
}
LateEarlyDateChecker.prototype.checkLateArrival = function(beginDate, arrivalDate, arrivalHour) {
    var beginDate = new Date(beginDate.getTime()); // clone object
    var arrivalDate = new Date(arrivalDate.getTime());
    if (arrivalHour > 0) {
        arrivalHour =- 1;//time of tourist's waiting
    }
    beginDate.setHours(arrivalHour);

    return arrivalDate.getTime() >= beginDate.getTime();
};
LateEarlyDateChecker.prototype.checkEarlyDeparture = function(endDate, departureDate, departureHour) {
    var endDate = new Date(endDate.getTime()); // clone object
    var departureDate = new Date(departureDate.getTime());
    if (departureHour > 0) {
        departureHour =+ 1;//time of tourist's waiting
    }
    endDate.setHours(departureHour);

    return departureDate.getTime() <= endDate.getTime();
};