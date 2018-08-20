<template>
    <td class="text-center">
        <select v-model="selected" @change="$emit('price-index-update', selected)" class="form-control plain-html input-sm search-tourists-select">
            <option v-for="(price, key) in prices" :value="key"><span>{{price.searchAdults}} взр.</span><span v-if="price.searchChildren">+{{price.searchChildren}} реб.</span></option>
        </select>
    </td>
</template>

<script>
    import Vue from "vue";
    export default Vue.extend({
        name: "Prices",
        props: ['prices', 'defaultPriceIndex'],
        data: function () {
            return {
                selected: this.defaultPriceIndex
            }
        }
    })
</script>

<style scoped>

</style>