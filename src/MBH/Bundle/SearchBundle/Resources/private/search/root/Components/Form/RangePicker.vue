<template>
    <input name="dates"/>
</template>


<script>
    import {mapState} from 'vuex';
    import moment from 'moment';

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
            // "startDate": this.begin,
            // "minDate": this.begin,
            // "endDate": this.end
        }
    };
    export default {
        name: "RangePicker",
        computed: mapState(
            'form',
            ['begin', 'end']
        ),
        mounted() {
            options.startDate = moment(this.begin, 'DD.MM.YYYY');
            options.endDate = moment(this.end, 'DD.MM.YYYY');
            options.minDate = moment(this.begin, 'DD.MM.YYYY');
            $('input[name="dates"]')
                .daterangepicker(options)
                .on('apply.daterangepicker', (ev, picker) => {
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