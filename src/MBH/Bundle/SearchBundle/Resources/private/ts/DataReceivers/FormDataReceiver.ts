class FormDataReceiver implements DataReceiverInterface {
    private readonly $form: JQuery;
    private readonly formName: string;

    constructor(formName: string) {
        this.$form = $(`form[name="${formName}"]`);
        this.formName = formName;
    }

    public getSearchConditionsData(): SearchDataType {
        let data: SearchDataType;
        data = {
            begin: String(this.getFormField('begin')),
            end: String(this.getFormField('end')),
            adults: Number(this.getFormField('adults'))/*,
            children: this.getFormField('children'),
            childrenAges: this.getFormField('childrenAges'),
            additionalBegin: this.getFormField('additionalBegin'),
            additionalEnd: this.getFormField('additionalEnd'),
            roomTypes: this.getFormField('roomTypes'),*/
        };

        return data;
    }

    private getFormField(fieldName: string) {
        let field = this.$form.find(`#${this.formName}_${fieldName}`);
        return field.val();
    }

}