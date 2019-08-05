function AdditionalForm(isDisplayChildAges) {
    this.isDisplayChildAges = isDisplayChildAges;

    this._PM_TARGET_PARENT_FORM = 'additionalFromDataForParentForm';
    this._PM_INPUT_DATA_FOR_PARENT = 'additionalFromDataForIframe';
    this._PM_RESIZE_IFRAME = 'additionalFormIFrameResize';

    this.wrapper = document.querySelector('#mbh-form-additional-data-wrapper');

    this.body = document.querySelector('body');
    this.lastIframeHeight = 0;
}

AdditionalForm.prototype.eventHandler = function () {
    var _this = this,
        property;

    this.iframeResize();

    var dataForm = {};

    ['adults', 'children-age'].forEach(function(name) {
        property = _this.wrapper.querySelectorAll('[data-form="' + name + '"]');
        switch (name) {
            case 'adults':
                dataForm[name] = property[0] ? parseInt(property[0].innerHTML) : 1;
                break;
            case 'children-age':
                dataForm['children'] = property.length;
                dataForm['children-ages'] = [];
                if (_this.isDisplayChildAges && dataForm['children'] > 0) {
                    property.forEach(function(value, index) {
                        dataForm['children-ages'].push(value.value);
                    })

                }
                break;
        }
    });


    this.sendPostMessage(
        this._PM_INPUT_DATA_FOR_PARENT,
        dataForm,
        'form'
    );
};

AdditionalForm.prototype.sendPostMessage = function (action, data, target) {
    mbhSendParentPostMessage(action, data, target);
};

AdditionalForm.prototype.iframeResize = function () {
    this.currentFormHeight = this.body.clientHeight;

    if (this.lastIframeHeight !== this.currentFormHeight) {
        this.lastIframeHeight = this.currentFormHeight;

        this.sendPostMessage( this._PM_RESIZE_IFRAME, this.currentFormHeight);
    }
};

AdditionalForm.prototype.wrapperAddListener = function () {
    var _this = this;
    this.wrapper.addEventListener('click', function(evt) {
        _this.eventHandler();
    });

    this.wrapper.addEventListener('keyup', function(evt) {
        _this.eventHandler();
    });

    window.addEventListener('resize', function(ev) {
        _this.iframeResize();
    });
};

AdditionalForm.prototype.selectedOption = function (selectElement, value) {
    var selectedOption = selectElement.querySelector('option[value="' + value + '"]');
    if (selectedOption === null) {
        return;
    }

    selectedOption.selected = true;
    selectedOption.setAttribute('selected', 'selected');
};

AdditionalForm.prototype.childrenCheckStepperValue = function () {
    if (parseInt(this.childrenStepperValue.innerHTML) <= 0) {
        this.childrenBtnSubtract.disabled = true;
        this.childrenAges.style.display = 'none';
    } else {
        this.childrenBtnSubtract.disabled = false;
        if (this.isDisplayChildAges) {
            this.childrenAges.style.display = 'block';
        }
    }
};

AdditionalForm.prototype.childrenAddListener = function () {
    var _this = this;

    this.childrenBtnSubtract.addEventListener('click', function(evt) {
        var newValue = parseInt(_this.childrenStepperValue.innerHTML) - 1;
        _this.childrenStepperValue.innerHTML = newValue;
        _this.childrenCheckStepperValue();
        _this.childrenAgeRemoveSection(newValue + 1);
    });

    this.childrenSection.querySelector('.btn-add').addEventListener('click', function(evt) {
        var newValue = parseInt(_this.childrenStepperValue.innerHTML) + 1;
        _this.childrenStepperValue.innerHTML = newValue;
        _this.childrenCheckStepperValue();
        _this.childrenAgeAddSelection(newValue, false);
    });
};

AdditionalForm.prototype.childrenSectionInit = function () {
    this.childrenSection = this.wrapper.querySelector('.mbh-form-row.children');
    this.childrenStepperValue = this.childrenSection.querySelector('.stepper-value');
    this.childrenBtnSubtract = this.childrenSection.querySelector('.btn-subtract');

    this.childrenTemplateAges = document.querySelector('#template-select-children-age');
    this.childrenAges = document.querySelector('.children-ages-wrapper');
    this.childrenAgesHolder = this.childrenAges.querySelector('.holder-children-ages');

    this.childrenAddListener();

    this.childrenCheckStepperValue();
};

AdditionalForm.prototype.childrenAgeRemoveSection = function (index) {
    this.childrenAgesHolder.querySelector('[data-children-age-index="' + index + '"]').remove();
};

AdditionalForm.prototype.childrenAgeAddSelection = function (index, age) {
    if ('content' in document.createElement('template')) {

        // Instantiate the table with the existing HTML tbody and the row with the template
        var select = this.childrenTemplateAges.content.querySelector('select');

        select.dataset.childrenAgeIndex = index;
        // клонируем новую строку и вставляем её в таблицу
        var cloneSelect = document.importNode(this.childrenTemplateAges.content, true);

        if (age) {
            this.selectedOption(cloneSelect, age);
        }

        this.childrenAgesHolder.appendChild(cloneSelect);
    } else {

        console.error('not document.createElement(\'template\')')
        // необходимо найти другой способ добавить строку в таблицу т.к.
        // тег <template> не поддерживатся браузером
    }
};

AdditionalForm.prototype.listenerPostMessage = function (){
    var _this = this;

    window.addEventListener('message', function(e) {
        if (e.data.type !== 'mbh') {
            return;
        }
        if (e.data.target !== _this._PM_TARGET_PARENT_FORM) {
            return;
        }

        var form = e.data.data.form;
        if (form.adults !== undefined && form.adults > 0) {
            _this.wrapper.querySelector('.stepper-value[data-form="adults"]').innerHTML = form.adults;
            _this.adultsCheckStepperValue();
        }

        if (form.children !== undefined && form.children > 0 ) {
            _this.childrenStepperValue.innerHTML = form.children;

            if (form['children-ages'].length === form.children) {
                form['children-ages'].forEach(function(age, index) {
                    _this.childrenAgeAddSelection(index + 1, age);
                });
            } else {
                for (var index = 1; index <= form.children; index++) {
                    _this.childrenAgeAddSelection(index, false);
                }
            }

            _this.childrenCheckStepperValue();
        }
    });
};

AdditionalForm.prototype.adultsCheckStepperValue = function () {
    this.adultsBtnSubtract.disabled = parseInt(this.adultsStepperValue.innerHTML) <= 1;
};

AdditionalForm.prototype.adultsSectionInit = function () {

    var adults = this.wrapper.querySelector('.mbh-form-row.adults');

    this.adultsStepperValue = adults.querySelector('.stepper-value');
    this.adultsBtnSubtract = adults.querySelector('.btn-subtract');

    this.adultsCheckStepperValue();

    var _this = this;

    this.adultsBtnSubtract.addEventListener('click', function(evt) {
        _this.adultsStepperValue.innerHTML = parseInt(_this.adultsStepperValue.innerHTML) - 1;
        _this.adultsCheckStepperValue();
    });

    adults.querySelector('.btn-add').addEventListener('click', function(evt) {
        _this.adultsStepperValue.innerHTML = parseInt(_this.adultsStepperValue.innerHTML) + 1;
        _this.adultsCheckStepperValue();
    });
};

AdditionalForm.prototype.exec = function() {
    var _this = this;
    this.childrenSectionInit();
    this.adultsSectionInit();

    this.listenerPostMessage();
    this.wrapperAddListener();

    document.querySelector('.close-button button').addEventListener('click', function(e) {
        _this.sendPostMessage('hideAdditionalForm');
    });
};

window.addEventListener('load', function(ev) {
    var additionalForm = new AdditionalForm(isDisplayChildAges);
    additionalForm.exec();
});
