<template>
    <small>
        <i v-popover class="fa fa-question-circle" data-container="body" data-toggle="popover"
           data-placement="left" data-html="true"
           :data-content="detail"
        ></i>
    </small>
</template>

<script lang="ts">
    declare let $: any;
    import * as moment from 'moment';
    import * as accounting from 'accounting';


    export default {
        name: "DayPrice",
        directives: {
            popover: {
                inserted: function (el) {
                    $(el).popover();
                }
            }
        },
        props: {
            dayPrices: Array
        },
        computed: {
            detail: function () {
                let html: string = '';
                for (let dayPrice of this.dayPrices) {
                    let day = moment(dayPrice['date'], 'DD.MM.YYYY').format('DD MMM');
                    let price = accounting.formatMoney(dayPrice['total'], "", 2, ",", ".");
                    const tariffName = this.tariffInfo(dayPrice['tariff']).fullTitle;

                    html += `${day} - ${price} - <i class='fa fa-sliders'></i> ${tariffName}`;
                    if (dayPrice['promotion']) {
                        const promotionName = this.promotionInfo(dayPrice['promotion']).fullTitle;
                        html += `&nbsp;<i class='fa fa-bookmark'></i>${promotionName}`;
                    }

                    html += `<br>`;
                }

                return `<small>${html}</small>`;
            },

        },
        methods: {
            tariffInfo: function(tariffId) {
                return this.$store.getters['results/getTariffInfo'](tariffId);
            },
            promotionInfo: function(promotionId) {
                return this.$store.getters['results/getPromotionInfo'](promotionId);
            }
        }
    }
</script>

<style scoped>

</style>