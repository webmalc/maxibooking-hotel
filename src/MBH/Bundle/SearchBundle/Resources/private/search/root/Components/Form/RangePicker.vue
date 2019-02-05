<template>
    <input name="dates"/>
</template>


<script>
    import {mapState} from 'vuex';

    const options = {
        'dateLimit': 365,
        'showDropdowns': true,
        'autoApply': true,
        'autoUpdateInput': true,
        "locale": {
            "format": "ll",
            "separator": " - ",
            "daysOfWeek": [
                "Вс",
                "Пн",
                "Вт",
                "Ср",
                "Чт",
                "Пт",
                "Сб"
            ],
            "monthNames": [
                "Январь",
                "Февраль",
                "Март",
                "Апрель",
                "Май",
                "Июнь",
                "Июль",
                "Август",
                "Сентябрь",
                "Октябрь",
                "Ноябрь",
                "Декабрь"
            ],
            "firstDay": 1,
            "startDate": this.begin,
            "endDate": this.end
        }
    };
    export default {
        name: "RangePicker",
        computed: mapState({
            begin: state => state.form.begin,
            end: state => state.form.end,
        }),
        mounted() {
            options.startDate = this.begin;
            options.endDate = this.end;
            $('input[name="dates"]')
                .daterangepicker(options)
                .on('apply.daterangepicker', (ev,picker) => {
                    this.$store.commit('form/setBegin', picker.startDate.format('DD.MM.YYYY'));
                    this.$store.commit('form/setEnd', picker.endDate.format('DD.MM.YYYY'));
                });
        }

    }
</script>

<style scoped>
    input[name="dates"] {
        width: 210px;
    }
</style>