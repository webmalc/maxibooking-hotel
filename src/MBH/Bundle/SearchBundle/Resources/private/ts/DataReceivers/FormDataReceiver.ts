class FormDataReceiver implements DataReceiverInterface {
    private readonly $form: JQuery;
    private readonly formName: string;
    private $children: JQuery;
    private $childrenAgeHolder: JQuery;
    private $childrenAges: JQuery[] = [];
    private agesTemplate:string;

    constructor(formName: string) {
        this.$form = $(`form[name="${formName}"]`);
        this.formName = formName;
        this.$children = $('input#search_conditions_children');
        this.$childrenAgeHolder = $('#search_conditions_childrenAges');
        this.agesTemplate = this.$childrenAgeHolder.data('prototype');
        this.bindHandlers();
    }

    private bindHandlers(): void {
        this.$children.on('change', (e) => {
            this.updateChildrenAges();
        })
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
        for (let index = ages; index > children;  index --) {
            const selector = `select#search_conditions_childrenAges_${index-1}`;
            let $ageInput = $(selector).parent('div');
            $ageInput.remove();
        }
    }

    private addAges(children: number, ages: number): void {
        for (let index = ages; index < children; index++) {
            console.log('asdf');
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
            childrenAges: this.getChildrenAges()

        };

        return data;
    }

    private getFormField(fieldName: string): number|string|string[]|number[] {
        let field = this.$form.find(`#${this.formName}_${fieldName}`);

        return field.val();
    }

    private getChildrenAges() {
        let data:number[] = [];
        $.each(this.$childrenAgeHolder.find('select'), function () {
            data.push(Number($(this).val()));
        });

        return data;
    }

}