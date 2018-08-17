<template>
    <tr :class="{success: isAdditionalDate}">
        <td class="text-center table-icon"><i class="fa fa-paper-plane-o"></i></td>
        <td>{{begin}}-{{end}}<br>
            <small>{{night}} ночей</small>
        </td>
        <td is="Tariff" :tariff="result.resultTariff" :freeRooms="minRooms"></td>
        <td is="Count" :count="minRooms" :quantity="quantity" @quantity="quantityUpdate($event)"></td>
        <td is="Prices" :prices="result.prices" :defaultPriceIndex="currentPriceIndex"
            @price-index-update="priceIndexUpdate($event)"></td>
        <td is="TotalPrice" :price="result.prices[currentPriceIndex]" :tariffName="result.resultTariff.name"></td>
        <td is="PackageLink" :link="getLink()" :roomsCount="minRooms" data-toggle="tooltip"
            @click.native="$emit('booking', quantity)"></td>
    </tr>
</template>

<script lang="ts">
    import Vue from 'vue';
    import * as moment from "moment";
    import Tariff from "./Tariff";
    import Count from "./Count";
    import Prices from "./Prices";
    import TotalPrice from "./TotalPrice";
    import PackageLink from "./PackageLink";

    declare let Routing: any;

    export default Vue.extend({
        components: {
            Tariff,
            Count,
            Prices,
            TotalPrice,
            PackageLink
        },
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
            linkBegin: function () {
                let begin = moment(this.result.begin);

                return begin.format('DD.MM.YYYY');
            },
            linkEnd: function () {
                let end = moment(this.result.end);

                return end.format('DD.MM.YYYY');
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
                const begin = this.linkBegin;
                const end = this.linkEnd;
                const tariff = this.result.resultTariff.id;
                const roomType = this.result.resultRoomType.id;
                const adults = this.result.prices[this.currentPriceIndex].searchAdults;
                const children = this.result.prices[this.currentPriceIndex].searchChildren;
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