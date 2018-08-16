<template>
    <td class="text-right">
        <ul class="package-search-prices">
            <li>{{rounded(price.total)}}
                <small is="day-price" :dayPrices="price.dayPrices"></small>
            </li>
        </ul>
        <small><i class="fa fa-sliders"></i> {{tariffName}}</small>
    </td>
</template>

<script type="ts">
    export default {
        name: "TotalPrice",
        props: ['price', 'tariffName'],
        methods: {
            rounded: function (price: number) {
                return accounting.formatMoney(price, "", 2, ",", ".");
            }
        }
    }
</script>

<style scoped>

</style>