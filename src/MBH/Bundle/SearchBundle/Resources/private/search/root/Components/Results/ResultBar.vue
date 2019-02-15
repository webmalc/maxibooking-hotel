<template>
    <tr :class="{success: exactDay}">
        <td class="text-center table-icon">
            <Icon :isCached="result.cached" :cacheItemId="result.cacheItemId"></Icon>
        </td>
        <td>{{titleBegin}}-{{titleEnd}} {{exactDay ? '': ' (доп. даты)'}}<br>
            <small>{{night}} ночей</small>
        </td>
        <td>{{tariff.name}}<br>
            <small><span class="package-search-book-count">Свободно номеров: {{minRooms}}</span></small>
        </td>

        <td>
            <select v-if="minRooms > 0" v-model="quantity" class="form-control quantity-select input-xxs plain-html">
                <option v-for="value in (1, Math.min(minRooms,5))" :value="value">{{ value }}</option>
            </select>
        </td>

        <td class="text-center">
            <select disabled v-model="priceIndex" readonly="readonly"
                    class="form-control plain-html input-sm search-tourists-select">
                <option v-for="(price, key) in result.prices" :value="key"><span>{{price.searchAdults}} взр.</span><span
                        v-if="price.searchChildren">+{{price.searchChildren}} реб.</span></option>
            </select>
        </td>

        <td class="text-right">
            <ul class="package-search-prices">
                <li>{{rounded(totalPrice)}}
                    <small is="DayPrice" :dayPrices="selectedPrice.dayPrices"></small>
                </li>
            </ul>
            <small><i class="fa fa-sliders"></i> {{tariff.name}}</small>
        </td>

        <td class="text-center">
            <a v-if="minRooms > 0" :href="bookingLink" target="_blank"
               class="btn btn-success btn-xs package-search-book"
               :title="'Бронировать номер. Всего номеров: ' + minRooms" @click="decreaseRoomAvailability">
                <i class="fa fa-book"></i><span class="package-search-book-reservation-text"> Бронировать</span>
            </a>
        </td>

    </tr>
</template>

<script lang="ts">
    import Icon from './ResultBarComponents/Icon.vue';
    import DayPrice from './ResultBarComponents/DayPrice.vue';
    import accounting from 'accounting';
    import moment from 'moment';

    declare let Routing: Routing;


    export default {
        name: "ResultBar",
        components: {
            Icon,
            DayPrice
        },
        props: {
            result: Object
        },
        data() {
            return {
                quantity: 1,
                priceIndex: 0
            }
        },
        computed: {
            exactDay() {
                return this.result.begin === this.conditions.begin && this.result.end === this.conditions.end;
            },
            titleBegin() {
                return moment(this.result.begin).format('DD MMM');
            },
            titleEnd() {
                return moment(this.result.end).format('DD MMM');
            },
            night() {
                const begin = moment.utc(this.result.begin);
                const end = moment.utc(this.result.end);
                return moment.duration(end.diff(begin)).asDays();
            },
            tariff() {
                return this.result.resultTariff;
            },
            roomType() {
                return this.result.resultRoomType;
            },
            minRooms() {
                return this.result.minRoomsCount;
            },
            totalPrice() {
                return this.selectedPrice.total;
            },
            selectedPrice() {
                return this.result.prices[this.priceIndex];
            },
            conditions() {
                return this.result.resultConditions;
            },
            bookingLink() {
                const begin = moment(this.result.begin).format('DD.MM.YYYY');
                const end = moment(this.result.end).format('DD.MM.YYYY');
                const tariff = this.tariff.id;
                const roomType = this.roomType.id;
                const adults = this.conditions.adults;
                const children = this.conditions.children;
                const childrenAges = this.conditions.childrenAges;
                const order = this.$store.state.form.orderId;
                const forceBooking = this.$store.state.form.isForceBooking === false ? 0 : 1;

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
                    forceBooking: forceBooking,
                    isUseCache: false
                });
            }
        },
        methods: {
            rounded: function (price: number) {
                return accounting.formatMoney(price, "", 2, ",", ".")
            },
            decreaseRoomAvailability() {
                const result = this.result;
                this.$store.commit('results/bookingAction', {
                    roomType: result.resultRoomType.id,
                    amount: this.quantity
                });
                this.quantity = Math.min(this.quantity, this.minRooms);
            }
        }
    }
</script>

<style scoped>
    .fade-enter-active{
        transition: opacity .2s;
    }
    .fade-leave-active {
        transition: opacity .2s;
    }
    .fade-enter, .fade-leave-to {
        opacity: 0;
    }
</style>