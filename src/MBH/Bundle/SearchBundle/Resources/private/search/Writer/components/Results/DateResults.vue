<template>
    <tbody>
    <tr @booking="booking($event)" is="Result" v-for="(result, key) in sortedResults" :key="key" :result="result" :isForceBooking="isForceBooking"></tr>
    </tbody>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Result from './Result';

    export default Vue.extend({
        name: "DateResults",
        props: ['results', 'isForceBooking'],
        methods: {
            booking: function (count: number) {
                for (let index in this.results) {
                    this.results[index].minRoomsCount = this.results[index].minRoomsCount - count;
                }
            }
        },
        computed: {
            sortedResults: function () {
                this.results.sort(function (resultA, resultB) {
                    if (typeof resultA.prices[0] !== 'object' || typeof resultB.prices[0] !== 'object') {
                        return;
                    }
                    let keyPriceA = Object.keys(resultA.prices)[0];
                    let keyPriceB = Object.keys(resultB.prices)[0];
                    let priceA = resultA.prices[keyPriceA].total;
                    let priceB = resultB.prices[keyPriceB].total;
                    if (priceA < priceB) {
                        return -1;
                    }
                    if (priceA > priceB) {
                        return 1;
                    }

                    return 0;
                });

                return this.results;
            },

        },
        components: {
            Result
        }
    })
</script>

<style scoped>

</style>