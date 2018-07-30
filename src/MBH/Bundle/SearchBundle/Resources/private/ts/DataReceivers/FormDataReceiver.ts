class FormDataReceiver implements DataReceiverInterface {
    private readonly $form: JQuery;
    private readonly formName: string;
    private $children: JQuery;
    private $childrenAgeHolder: JQuery;
    private agesTemplate: string;
    private $addTouristButton: JQuery;

    constructor(formName: string) {
        this.$form = $(`form[name="${formName}"]`);
        this.formName = formName;
        this.$children = $('input#search_conditions_children');
        this.$childrenAgeHolder = $('#search_conditions_childrenAges');
        this.agesTemplate = this.$childrenAgeHolder.data('prototype');
        this.$addTouristButton = $('#add-tourist');
        this.bindHandlers();
        this.checkChildrenAges();
    }

    private bindHandlers(): void {
        this.$children.on('input', (e) => {
            this.checkMaxValue($(e.target));
            this.updateChildrenAges();
            this.checkChildrenAges();
        });
        this.$addTouristButton.on('click', (e) => {
            this.initGuestModal(e);
        });
    }

    private initGuestModal(e): void {
        let guestModal = $('#add-guest-modal'),
            form = guestModal.find('form'),
            button = $('#add-guest-modal-submit'),
            errors = $('#add-guest-modal-errors');

        e.preventDefault();
        guestModal.modal('show');
        button.click(function () {
            errors.hide();
            $.post(form.prop('action'), form.serialize(), function (data) {
                if (data.error) {
                    errors.html(data.text).show();
                } else {
                    $('.findGuest').append($("<option/>", {
                        value: data.id,
                        text: data.text
                    })).val(data.id).trigger('change');
                    form.trigger('reset');
                    //form.find('select').select2('data', null);
                    guestModal.modal('hide');
                    form.find('select').select2('data', null);
                    //form.find('input').select2('data', null);
                    return 1;
                }
            });
        });
    }

    private checkMaxValue($childrenField): void {
        const currentValue = $childrenField.val();
        const maxValue = Number($childrenField.attr('max'));
        const minValue = Number($childrenField.attr('min'));
        if (currentValue > maxValue) {
            $childrenField.val(maxValue).trigger('input');
        }
        if (currentValue < minValue) {
            $childrenField.val(minValue).trigger('input');
        }
    }

    private checkChildrenAges(): void {
        if (this.getChildrenCount()) {
            $('.children_age_holder').fadeIn();
        } else {
            $('.children_age_holder').fadeOut();
        }
    }

    private updateChildrenAges(): void {
        const currentAgesCount = this.getChildrenAgesIndex();
        const currentChildrenCount = this.getChildrenCount();
        if (currentAgesCount > currentChildrenCount) {
            this.removeAges(currentChildrenCount, currentAgesCount);
        }
        if (currentAgesCount < currentChildrenCount) {
            this.addAges(currentChildrenCount, currentAgesCount);
        }
    }

    private removeAges(children: number, ages: number): void {
        for (let index = ages; index > children; index--) {
            const selector = `select#search_conditions_childrenAges_${index - 1}`;
            let $ageInput = $(selector).parent('div');
            $ageInput.remove();
        }
    }

    private addAges(children: number, ages: number): void {
        for (let index = ages; index < children; index++) {
            let ageHTML = this.agesTemplate.replace(/__name__/g, String(index));
            this.$childrenAgeHolder.append(ageHTML);
        }
    }


    private getChildrenAgesIndex(): number {
        return this.$childrenAgeHolder.find(':input').length;
    }

    private getChildrenCount(): number {
        return Number(this.$children.val());
    }

    public getSearchConditionsData(): SearchDataType {
        let data: SearchDataType;
        data = {
            begin: String(this.getFormField('begin')),
            end: String(this.getFormField('end')),
            adults: Number(this.getFormField('adults')),
            additionalBegin: Number(this.getFormField('additionalBegin')),
            additionalEnd: Number(this.getFormField('additionalEnd')),
            tariffs: this.getFormField('tariffs'),
            roomTypes: this.getFormField('roomTypes'),
            hotels: this.getFormField('hotels'),
            children: Number(this.getFormField('children')),
            childrenAges: this.getChildrenAges(),
            order: Number(this.getFormField('order'))

        };

        return data;
    }

    private getFormField(fieldName: string): number | string | string[] | number[] {
        let field = this.$form.find(`#${this.formName}_${fieldName}`);

        return field.val();
    }

    private getChildrenAges() {
        let data: number[] = [];
        $.each(this.$childrenAgeHolder.find('select'), function () {
            data.push(Number($(this).val()));
        });

        return data;
    }

}