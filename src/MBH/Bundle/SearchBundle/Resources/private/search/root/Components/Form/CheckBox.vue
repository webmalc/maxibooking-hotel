<template>
    <input type="checkbox" class="plain-html" :id="labelId" :checked="checked" />
</template>

<script>
    export default {
        name: "CheckBox",
        model: {
            prop: 'checked',
            event: 'change'
        },
        props: {
            checked: Boolean,
            labelId: String
        },
        data() {
            return {
                uniqueId: null
            }
        },
        mounted() {
            this.uniqueId = this._uid;
            $(this.$el).bootstrapSwitch({
                'size': 'small',
                'onText': 'да',
                'offText': 'нет',
                'onColor': 'success',
            })
                .on('switchChange.bootstrapSwitch',  (event, state) => {
                    this.$emit('change', state);
                })
            ;
        },
        methods: {
            click() {
                this.$emit('change', {
                    checked: !this.checked
                });
            }
        }
    }
</script>

<style scoped>

</style>