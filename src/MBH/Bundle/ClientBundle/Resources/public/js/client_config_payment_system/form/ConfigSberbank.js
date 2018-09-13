var ConfigSberbank = /** @class */ (function () {
    function ConfigSberbank() {
        this._cssClass = 'payment-system-form_sberbank';
        this._fieldToken = document.querySelector('.sberbank-field-token');
        this._fieldUser = document.querySelector('.sberbank-field-userName');
        this._fieldPass = document.querySelector('.sberbank-field-password');
    }
    ConfigSberbank.prototype.init = function () {
        var self = this;
        this._fieldToken.addEventListener('change', function () {
            self._checkFields();
        });
        this._fieldUser.addEventListener('change', function () {
            self._checkFields();
        });
        this._fieldPass.addEventListener('change', function () {
            self._checkFields();
        });
    };
    ConfigSberbank.prototype._changeCssClass = function (element, addClass) {
        if (addClass) {
            element.classList.add(this._cssClass);
        }
        else {
            element.classList.remove(this._cssClass);
        }
    };
    ConfigSberbank.prototype._changeRequired = function (element, setRequired) {
        element.required = setRequired;
    };
    ConfigSberbank.prototype._changeAllForUserAndPass = function (add) {
        this._changeRequired(this._fieldUser, add);
        this._changeRequired(this._fieldPass, add);
        this._changeCssClass(this._fieldUser, add);
        this._changeCssClass(this._fieldPass, add);
    };
    ConfigSberbank.prototype._changeAllForToken = function (add) {
        this._changeRequired(this._fieldToken, add);
        this._changeCssClass(this._fieldToken, add);
    };
    ConfigSberbank.prototype._checkFields = function () {
        var isNotEmptyToken = this._valueTokenIsNotEmpty();
        if (isNotEmptyToken) {
            this._changeAllForToken(true);
            this._changeAllForUserAndPass(false);
        }
        else if (!isNotEmptyToken && this._valueInUserAndPassIsNotEmpty()) {
            this._changeAllForToken(false);
            this._changeAllForUserAndPass(true);
        }
        else {
            this._changeAllForToken(true);
            this._changeAllForUserAndPass(true);
        }
    };
    ConfigSberbank.prototype._valueIsNotEmpty = function (element) {
        return element.value !== '';
    };
    ConfigSberbank.prototype._valueTokenIsNotEmpty = function () {
        return this._valueIsNotEmpty(this._fieldToken);
    };
    ConfigSberbank.prototype._valueInUserAndPassIsNotEmpty = function () {
        return this._valueIsNotEmpty(this._fieldUser) && this._valueIsNotEmpty(this._fieldPass);
    };
    return ConfigSberbank;
}());
window.addEventListener('load', function () {
    var sbrf = new ConfigSberbank();
    sbrf.init();
});
