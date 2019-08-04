<template>

    <tr :class="color" :id="anchor">
        <td class="text-center table-icon">
            <Icon :isCached="result.cached" :cacheItemId="result.cacheItemId"></Icon>
        </td>
        <td>{{titleBegin}}-{{titleEnd}} {{exactDay ? '': ' (доп. даты)'}}<br>
            <small>{{night}} ночей</small>
        </td>
        <td>{{tariffInfo.title || tariffInfo.fullTitle }}<br>
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
                <option v-for="(price, key) in result.prices" :value="key"><span>{{price.adults}} взр.</span><span
                        v-if="price.children">+{{price.children}} реб.</span></option>
            </select>
        </td>

        <td class="text-right">
            <ul class="package-search-prices">
                <li>{{rounded(totalPrice)}}
                    <small is="DayPrice" :dayPrices="selectedPrice.priceByDay"></small>
                </li>
            </ul>
            <small><i class="fa fa-sliders"></i> {{tariffInfo.title || tariffInfo.fullTitle}}</small>
        </td>

        <td class="text-center">
            <a v-if="minRooms > 0 && !priceError" :href="bookingLink" target="_blank"
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
    import * as accounting from 'accounting';
    import * as moment from 'moment';

    declare let Routing: Routing;

    const createDate = function (date) {
        return moment(date, 'DD.MM.YYYY');
    };

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
            priceError() {
                return this.$store.state.debug.error.errorList.includes(this.anchor);
            },
            color() {
                return {
                    success: this.exactDay && !this.priceError,
                    danger: this.priceError
                }
            },
            exactDay() {
                return this.result.begin === this.searchBegin && this.result.end === this.searchEnd;
            },
            titleBegin() {
                return createDate(this.result.begin).format('DD MMM');
            },
            titleEnd() {
                return createDate(this.result.end).format('DD MMM');
            },
            night() {
                const begin = moment.utc(this.result.begin, 'DD.MM.YYYY');
                const end = moment.utc(this.result.end, 'DD.MM.YYYY');
                return moment.duration(end.diff(begin)).asDays();
            },
            tariff() {
                return this.result.tariff;
            },
            tariffInfo() {
                const tariffId = this.tariff;

                return this.$store.getters['results/getTariffInfo'](tariffId);
            },
            roomType() {
                return this.result.roomType;
            },
            minRooms() {
                return this.result.roomAvailableAmount;
            },
            anchor(): string {
                return `#${this.roomType}${this.tariff}${createDate(this.result.begin).format('YYYYMD')}${createDate(this.result.end).format('YYYYMD')}`;
            },
            totalPrice(): number {
                return this.selectedPrice.total;
            },
            selectedPrice() {
                return this.result.prices[this.priceIndex];
            },
            searchBegin() {
                return createDate(this.$store.state.results.currentConditions.begin).format('DD.MM.YYYY');
            },
            searchEnd() {
                return createDate(this.$store.state.results.currentConditions.end).format('DD.MM.YYYY');
            },

            bookingData(): object {
                const begin = createDate(this.result.begin).format('DD.MM.YYYY');
                const end = createDate(this.result.end).format('DD.MM.YYYY');
                const tariff = this.tariff;
                const roomType = this.roomType;
                const adults = this.result.adults;
                const children = this.result.children;
                const childrenAges = this.result.childrenAges;
                const order = this.$store.state.form.orderId;
                const forceBooking = this.$store.state.form.isForceBooking === false ? 0 : 1;

                return {
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
                };

            },
            bookingLink() {
                return Routing.generate('package_new', this.bookingData);
            },
            isDebug() {
                return this.$store.state.debug.isDebug;
            }
        },
        created() {
            if (this.isDebug) {
                let compareData: CompareInterface;
                compareData = {
                    price: this.totalPrice,
                    hash: this.anchor,
                    query: this.bookingData
                };
                this.$store.commit('debug/addToCompares', compareData );
            }

        },
        methods: {
            rounded: function (price: number) {
                return accounting.formatMoney(price, "", 2, ",", ".")
            },
            decreaseRoomAvailability() {
                const result = this.result;
                this.$store.commit('results/bookingAction', {
                    roomType: result.roomType,
                    amount: this.quantity
                });
                this.quantity = Math.min(this.quantity, this.minRooms);
            }
        }
    }
</script>

<style scoped>
    .fade-enter-active {
        transition: opacity .2s;
    }

    .fade-leave-active {
        transition: opacity .2s;
    }

    .fade-enter, .fade-leave-to {
        opacity: 0;
    }
</style>