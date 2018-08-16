<template>
    <tr :class="{success: isAdditionalDate}">
        <td class="text-center table-icon"><i class="fa fa-paper-plane-o"></i></td>
        <td>{{begin}}-{{end}}<br><small>{{night}} ночей</small></td>
        <td is="tariff" :tariff="result.resultTariff" :freeRooms="minRooms"></td>
        <td is="count" :count="minRooms" :quantity="quantity" @quantity="quantityUpdate($event)"></td>
        <td is="prices" :prices="result.prices" :defaultPriceIndex="currentPriceIndex" @price-index-update="priceIndexUpdate($event)"></td>
        <td is="total-price" :price="result.prices[currentPriceIndex]" :tariffName="result.resultTariff.name"></td>
        <td is="package-link" :link="getLink()" :roomsCount="minRooms" data-toggle="tooltip" @click.native="$emit('booking', quantity)"></td>
    </tr>
</template>

<script type="ts">
    import Vue from 'vue';
    import * as moment from "moment";

    export default Vue.extend({
        name: "Result",
        props: ['result'],
        computed: {
            begin: function () {
                let begin = moment(this.result.begin);

                return begin.format('DD MMM');
            },
            end: function () {
                let end = moment(this.result.end);

                return end.format('DD MMM');
            },
            night: function () {
                const begin = moment.utc(this.result.begin);
                const end = moment.utc(this.result.end);

                return moment.duration(end.diff(begin)).days();
            },
            isAdditionalDate: function () {
                let conditionBegin = this.result.resultConditions.begin;
                let begin = this.result.begin;

                let conditionEnd = this.result.resultConditions.end;
                let end = this.result.end;

                return (conditionBegin == begin) && (conditionEnd == end);
            },
            minRooms: function () {
                return this.result.minRoomsCount;
            }


        },
        methods: {
            getLink: function () {
                const begin = this.result.begin;
                const end = this.result.end;
                const tariff = this.result.resultTariff.id;
                const roomType = this.result.resultRoomType.id;
                const adults = this.result.prices[this.currentPriceIndex].adults;
                const children = this.result.prices[this.currentPriceIndex].children;
                const childrenAges = this.result.resultConditions.childrenAges;
                const order = this.result.resultConditions.order;
                const forceBooking = this.result.resultConditions.forceBooking;
                return Routing.generate('package_new', {
                    begin: begin,
                    end: end,
                    tariff: tariff,
                    roomType: roomType,
                    adults: adults,
                    children: children,
                    childrenAges: childrenAges,
                    quantity: this.quantity,
                    order: order,
                    forceBooking: forceBooking
                });
            },
            priceIndexUpdate: function (index) {
                this.currentPriceIndex = index;
            },
            quantityUpdate: function (num) {
                this.quantity = num;
            }
        },
        data: function () {
            return {
                currentPriceIndex: 0,
                quantity: 1
            }
        }
    });
</script>

<style scoped>

</style>