<template>
    <small>
        <i v-popover class="fa fa-question-circle" data-container="body" data-toggle="popover"
           data-placement="left" data-html="true"
           :data-content="detail"
        ></i>
    </small>
</template>



<script lang="ts">

    import Vue from 'vue';
    import * as moment from 'moment';
    import accounting from "accounting";
    moment.locale('ru');
    export default Vue.extend({
        name: "DayPrice",
        props: ['dayPrices'],
        computed: {
            detail: function () {
                let html: string = '';
                for (let dayPrice of this.dayPrices) {
                    let day = moment(dayPrice['date']).format('DD MMM');
                    let price = accounting.formatMoney(dayPrice['price'], "", 2, ",", ".");
                    html += `${day} - ${price} - <i class='fa fa-sliders'></i> ${dayPrice['tariff']['name']}<br>`;
                }

                return `<small>${html}</small>`;
            }
        },
        directives: {
            popover: {
                inserted: function (el) {
                    $(el).popover();
                }
            }
        }
    });
</script>

<style scoped>

</style>