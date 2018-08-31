class ConfigSberbank {
    private readonly _cssClass: string = 'payment-system-form_sberbank';
    private _fieldToken: HTMLInputElement = document.querySelector('.sberbank-field-token');
    private _fieldUser: HTMLInputElement = document.querySelector('.sberbank-field-userName');
    private _fieldPass: HTMLInputElement = document.querySelector('.sberbank-field-password');

    init():void {
        const self = this;
        this._fieldToken.addEventListener('change', function () {
            self._checkFields();
        });
        this._fieldUser.addEventListener('change', function () {
            self._checkFields();
        });
        this._fieldPass.addEventListener('change', function () {
            self._checkFields();
        });

    }

    _changeCssClass(element: HTMLInputElement, addClass: boolean): void {
        if (addClass) {
            element.classList.add(this._cssClass);
        } else {
            element.classList.remove(this._cssClass);
        }
    }

    _changeRequired(element: HTMLInputElement, setRequired: boolean): void {
        element.required = setRequired;
    }

    _changeAllForUserAndPass(add: boolean): void {
        this._changeRequired(this._fieldUser, add);
        this._changeRequired(this._fieldPass, add);
        this._changeCssClass(this._fieldUser, add);
        this._changeCssClass(this._fieldPass, add);
    }

    _changeAllForToken(add: boolean): void {
        this._changeRequired(this._fieldToken, add);
        this._changeCssClass(this._fieldToken, add);
    }

    _checkFields() {
        let isNotEmptyToken: boolean = this._valueTokenIsNotEmpty();

        if (isNotEmptyToken) {
            this._changeAllForToken(true);
            this._changeAllForUserAndPass(false);
        } else if (!isNotEmptyToken && this._valueInUserAndPassIsNotEmpty()) {
            this._changeAllForToken(false);
            this._changeAllForUserAndPass(true);
        } else {
            this._changeAllForToken(true);
            this._changeAllForUserAndPass(true);
        }
    }

    _valueIsNotEmpty(element: HTMLInputElement): boolean {
        return element.value !== '';
    }

    _valueTokenIsNotEmpty(): boolean {
        return this._valueIsNotEmpty(this._fieldToken);
    }

    _valueInUserAndPassIsNotEmpty(): boolean {
        return this._valueIsNotEmpty(this._fieldUser) && this._valueIsNotEmpty(this._fieldPass);
    }
}

window.addEventListener('load', function () {
    let sbrf = new ConfigSberbank();
    sbrf.init();
});