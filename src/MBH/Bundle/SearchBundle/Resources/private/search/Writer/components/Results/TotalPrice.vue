<template>
    <td class="text-right">
        <ul class="package-search-prices">
            <li>{{rounded(price.total)}}
                <small is="DayPrice" :dayPrices="price.dayPrices"></small>
            </li>
        </ul>
        <small><i class="fa fa-sliders"></i> {{tariffName}}</small>
    </td>
</template>

<script lang="ts">
    import Vue from "vue";
    import accounting from "accounting";
    import DayPrice from "./DayPrice";

    export default Vue.extend({
        name: "TotalPrice",
        props: ['price', 'tariffName'],
        methods: {
            rounded: function (price: number) {
                return accounting.formatMoney(price, "", 2, ",", ".");
            }
        },
        components: {
            DayPrice
        }
    });
</script>

<style scoped>

</style>