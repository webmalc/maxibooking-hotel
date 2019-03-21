function AdditionalForm() {
    this._PM_TARGET_PARENT_FORM = 'additionalFromDataForParentForm';
    this._PM_INPUT_DATA_FOR_PARENT = 'additionalFromDataForIframe';
    this._PM_RESIZE_IFRAME = 'additionalFormIFrameResize';

    this.wrapper = document.querySelector('#mbh-form-additional-data-wrapper');

    this.lastIframeHeight = 0;
}

AdditionalForm.prototype.eventHandler = function (self) {
    var property;

    this.iframeResize();

    var dataForm = {};

    ['adults', 'children-age', 'room-type'].forEach(function(name) {
        property = self.wrapper.querySelectorAll(`[data-form="${name}"]`);
        switch (name) {
            case 'adults':
                dataForm[name] = property[0] ? parseInt(property[0].innerHTML) : 1;
                break;
            case 'children-age':
                dataForm['children'] = property.length;
                dataForm['children-ages'] = [];
                if (dataForm['children'] > 0) {
                    property.forEach(function(value, index) {
                        dataForm['children-ages'].push(value.value);
                    })

                }
                break;
            case 'room-type':
                dataForm['roomType'] = property[0].value;
                break;
        }
    });

    window.parent.postMessage({
        type: 'mbh',
        target: 'form',
        name: this._PM_INPUT_DATA_FOR_PARENT,
        form: dataForm
    }, '*')
};

AdditionalForm.prototype.iframeResize = function () {
    var formHeight = this.wrapper.clientHeight + 33;

    if (this.lastIframeHeight !== formHeight) {
        this.lastIframeHeight = formHeight;

        window.parent.postMessage({
            type: 'mbh',
            action: this._PM_RESIZE_IFRAME,
            formHeight: formHeight
        }, '*')
    }
};

AdditionalForm.prototype.wrapperAddListener = function (self) {
    this.wrapper.addEventListener('click', function(evt) {
        self.eventHandler(self);
    });

    this.wrapper.addEventListener('keyup', function(evt) {
        self.eventHandler(self);
    });

    window.addEventListener('resize', function(ev) {
        self.iframeResize();
    });
};

AdditionalForm.prototype.selectedOption = function (selectElement, value) {
    var selectedOption = selectElement.querySelector(`option[value="${value}"]`);
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
        this.childrenAges.style.display = 'block';
    }
};

AdditionalForm.prototype.childrenAddListener = function (self) {
    this.childrenBtnSubtract.addEventListener('click', function(evt) {
        var newValue = parseInt(self.childrenStepperValue.innerHTML) - 1;
        self.childrenStepperValue.innerHTML = newValue;
        self.childrenCheckStepperValue();
        self.childrenAgeRemoveSection(newValue + 1);
    });

    this.childrenSection.querySelector('.btn-add').addEventListener('click', function(evt) {
        var newValue = parseInt(self.childrenStepperValue.innerHTML) + 1;
        self.childrenStepperValue.innerHTML = newValue;
        self.childrenCheckStepperValue();
        self.childrenAgeAddSelection(newValue, false);
    });
};

AdditionalForm.prototype.childrenSectionInit = function () {
    this.childrenSection = this.wrapper.querySelector('.mbh-form-row.children');
    this.childrenStepperValue = this.childrenSection.querySelector('.stepper-value');
    this.childrenBtnSubtract = this.childrenSection.querySelector('.btn-subtract');

    this.childrenTemplateAges = document.querySelector('#template-select-children-age');
    this.childrenAges = document.querySelector('.children-ages-wrapper');
    this.childrenAgesHolder = this.childrenAges.querySelector('.holder-children-ages');

    this.childrenAddListener(this);

    this.childrenCheckStepperValue();
};

AdditionalForm.prototype.childrenAgeRemoveSection = function (index) {
    this.childrenAgesHolder.querySelector(`[data-children-age-index="${index}"]`).remove();
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

AdditionalForm.prototype.listenerPostMessage = function (self){

    var roomType = this.wrapper.querySelector('#mbh-form-roomType');

    window.addEventListener('message', function(e) {
        if (e.data.type !== 'mbh') {
            return;
        }
        if (e.data.target !== self._PM_TARGET_PARENT_FORM) {
            return;
        }

        var form = e.data.form;

        if (form.adults) {
            self.wrapper.querySelector('.stepper-value[data-form="adults"]').innerHTML = form.adults;
        }

        if (form.children && parseInt(form.children) > 0 ) {
            self.childrenStepperValue.innerHTML = form.children;

            form['children-ages'].forEach(function(age, index) {
                self.childrenAgeAddSelection(index + 1, age);
            });

            self.childrenCheckStepperValue();
        }

        if (roomType !== null && form.roomType !== '') {
            self.selectedOption(roomType, form.roomType);
        }

        console.log(e.data);
    });
};

AdditionalForm.prototype.adultsSectionInit = function () {
    this.wrapper.querySelectorAll('.mbh-form-row.adults').forEach(function(element) {
        var stepperValue = element.querySelector('.stepper-value'),
            btnSubtract = element.querySelector('.btn-subtract'),
            checkStepperValue = function() {
                if (parseInt(stepperValue.innerHTML) <= 1) {
                    btnSubtract.disabled = true;
                } else {
                    btnSubtract.disabled = false;
                }
            };

        checkStepperValue();

        btnSubtract.addEventListener('click', function(evt) {
            stepperValue.innerHTML = parseInt(stepperValue.innerHTML) - 1;
            checkStepperValue();
        });

        element.querySelector('.btn-add').addEventListener('click', function(evt) {
            stepperValue.innerHTML = parseInt(stepperValue.innerHTML) + 1;
            checkStepperValue();
        });

    });
};

AdditionalForm.prototype.exec = function() {

    this.childrenSectionInit();
    this.adultsSectionInit();

    this.listenerPostMessage(this);
    this.wrapperAddListener(this);
};

window.addEventListener('load', function(ev) {
    var additionalForm = new AdditionalForm();
    additionalForm.exec();
});