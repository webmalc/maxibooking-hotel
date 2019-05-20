<template>
    <div class="maxi" itemprop="articleBody">
        <transition name="fade">
            <div v-if="status !== 'new'" class="selectblock">

                <div class="row row-no-indent">
                    <div class="col-md-4 az-filter-hotel">
                        <select class="azselect azselect-right" name="object" v-model="selectedFilters.hotel">
                            <option v-for="hotelFilter in filters.hotels" :value="hotelFilter.value"
                                    :key="hotelFilter.value">
                                {{ hotelFilter.text }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4 az-filter-room-type">
                        <select class="azselect azselect-right" name="room" v-model="selectedFilters.roomType">
                            <option v-for="roomTypeFilter in filters.roomType" :value="roomTypeFilter.value"
                                    :key="roomTypeFilter.value"
                                    :disabled="checkRoomTypeDisabled(roomTypeFilter.value)"
                            >
                                {{ roomTypeFilter.text }}

                            </option>
                        </select>
                    </div>
                    <div class="col-md-4 az-filter-month">
                        <select class="azselect azselect-right" name="room" v-model="selectedFilters.month">
                            <option v-for="monthFilter in getMonthFilters" :value="monthFilter.value"
                                    :key="monthFilter.value"
                            >
                                {{ monthFilter.text }}
                            </option>
                        </select>
                    </div>
                    <!--                <div class="col-md- az-filter-amount">-->
                    <!--                    <select class="azselect azselect-right" name="room" v-model="selectedFilters.viewAmount">-->
                    <!--                        <option v-for="amountFilter in filters.viewAmount" :value="amountFilter.value"-->
                    <!--                                :key="amountFilter.value">-->
                    <!--                            {{ amountFilter.text }}-->
                    <!--                        </option>-->
                    <!--                    </select>-->
                    <!--                </div>-->


                </div>

                <div class="row row-no-indent spec-sort">
                    <div class="col-md-12">
                        <div class="spec-sort-wrap">

                            <div class="spec-sort-cont"><span class="sortby">Сортировать по:</span>

                                <div :class="['curspoint', 'mr20', sorting.currentSorting === 'price' ? 'sort-active' : 'sort-selector' ]"
                                     @click="togglePriceSorting()">
                                    <p>Цене</p> <img :src="priceArrow">
                                </div>

                                <div :class="['curspoint', 'sort-selector', sorting.currentSorting === 'date' ? 'sort-active' : 'sort-selector']"
                                     @click="toggleDateSorting()">
                                    <p>Дате</p> <img :src="dateArrow">
                                </div>

                            </div>

                            <div :class="['spec-pokaz-cont']"><span class="sortby">Показать:</span>
                                <a v-for="(amountFilter, key) in filters.viewAmount"
                                   href="#"
                                   :class="['pokazlink', {mr10: true}, amountActive(amountFilter.value)]"
                                   :key="`amount${key}`"
                                   @click.prevent="toggleShowAmount(amountFilter.value)"
                                >
                                    {{amountFilter.text}}
                                </a>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </transition>

        <div v-if="status === 'new'">Идет поиск</div>
        <div id="block_spec_containers">
            <transition-group
                    tag="div"
                    name="spec-list"
                    enter-active-class="animated fadeIn"
                    :duration="{enter:500}"
                    appear
                    style="display: inline;"
            >
                <SpecItem :class="{'last-spec' : key === sortedSpecs.length - 1}" v-for="(data, key) in sortedSpecs"
                          :key="`${data.special.id}${data.roomType.id}`"
                          :data="data">

                </SpecItem>
            </transition-group>
        </div>
        <div id="az-show-more" v-if="!isAllDisplayed">
            <button type="button" @click="showMore" class="btn btn-primary">Показать еще
                {{selectedFilters.viewAmount}}
            </button>
        </div>
    </div>
</template>

<script lang="ts">
    import SpecItem from './SpecItem.vue';
    import moment from 'moment';
    import arrows from './../images/arrows';
    import URLSearchParams from '@ungap/url-search-params'

    declare const Routing: any;
    moment.locale('ru');
    const params = new URLSearchParams(location.search);

    export default {
        name: "Specials",
        components: {
            SpecItem
        },
        data() {
            return {
                status: 'new',
                specials: [],
                filters: {
                    hotels: [
                        {text: 'Все отели', value: 'all'},
                    ],
                    month: [
                        {text: 'все месяцы', value: 0},
                        {text: 'апрель', value: 4},
                        {text: 'май', value: 5},
                        {text: 'июнь', value: 6},
                        {text: 'июль', value: 7},
                        {text: 'август', value: 8},
                        {text: 'сентябрь', value: 9},
                    ],
                    roomType: [
                        {text: 'Все типы номеров', value: 'all'}
                    ],
                    viewAmount: [
                        {text: '20', value: 20},
                        {text: '40', value: 40},
                    ]
                },
                sorting: {
                    price: true,
                    date: true,
                    currentSorting: 'price'
                },
                selectedFilters: {
                    hotel: '',
                    roomType: '',
                    month: '',
                    viewAmount: 20,
                    page: 1
                },
                categories: []
            }
        },
        created() {
            (async () => {
                const data = await this.getSpecials();
                this.specials = data.data.results;
                const filters = data.data.filters;
                const categories = data.data.categories;
                this.fillFilters(filters);
                this.fillCategories(categories);
                this.setDefaultFiltersValue();
                // this.updateHistory();

            })();
        },
        updated() {
            //** TODO: Проблема что updated вызывается на все.
            this.updateHistory();
        },
        computed: {
            filteredSpecs() {
                const month = this.selectedFilters.month;
                const hotel = this.selectedFilters.hotel;
                const roomType = this.selectedFilters.roomType;
                return this.specials.filter(spec => {
                    const monthFiltered = (
                        moment(spec.dates.begin, 'DD.MM.YY').format('MM') == month ||
                        moment(spec.dates.end, 'DD.MM.YY').format('MM') == month ||
                        month === 0
                    );
                    const hotelFiltered = spec.hotel.id === hotel || hotel === 'all';
                    const categoryFiltered = spec.roomType.categoryId === roomType || roomType === 'all';

                    return monthFiltered && hotelFiltered && categoryFiltered;

                })
            },
            roomTypeFilteredSpecs() {
                const roomType = this.selectedFilters.roomType;

                return this.specials.filter(spec => {
                    return spec.roomType.categoryId === roomType || roomType === 'all';
                })
            },
            sortedSpecs() {
                let specs = this.filteredSpecs;
                if (this.sorting.currentSorting === 'price') {
                    specs.sort((a, b) => {
                        const priceA = a.prices.prices[a.prices.defaultPrice];
                        const priceB = b.prices.prices[b.prices.defaultPrice];

                        let compareResult;
                        if (this.sorting.price) {
                            compareResult = priceA < priceB ? -1 : 1;
                        } else {
                            compareResult = priceA > priceB ? -1 : 1;
                        }

                        return compareResult;

                    });
                }

                if (this.sorting.currentSorting === 'date') {
                    specs.sort((a, b) => {
                        let dateA = moment(a.dates.begin, "DD.MM.YY");
                        let dateB = moment(b.dates.begin, "DD.MM.YY");
                        let compareResult;
                        if (this.sorting.date) {
                            compareResult = dateA < dateB ? -1 : 1;
                        } else {
                            compareResult = dateA > dateB ? -1 : 1;
                        }
                        if (dateA.isSame(dateB)) {
                            const priceA = a.prices.prices[a.prices.defaultPrice];
                            const priceB = b.prices.prices[b.prices.defaultPrice];
                            compareResult = priceA < priceB ? -1 : 1;
                        }

                        return compareResult;
                    });
                }
                const viewAmount = this.selectedFilters.viewAmount;
                const page = this.selectedFilters.page;
                return specs.slice(0, viewAmount * page);
            },
            priceArrow() {
                return this.sorting.price ? arrows.arrowUp : arrows.arrowDown
            },
            dateArrow() {
                return this.sorting.date ? arrows.arrowUp : arrows.arrowDown;
            },
            isAllDisplayed() {
                return this.filteredSpecs.length <= this.selectedFilters.viewAmount * this.selectedFilters.page;
            },
            getMonthFilters() {
                return this.filters.month.filter(month => {
                    const filterMonth = month.value;
                    const currentMonthFilter = filterMonth >= parseInt(moment().format('M'));

                    const selectedRoomTypeMonthFilter = (this.roomTypeFilteredSpecs.filter(spec => {

                        return parseInt(moment(spec.dates.begin, 'DD.MM.YY').format('M')) == filterMonth
                            || parseInt(moment(spec.dates.end, 'DD.MM.YY').format('M')) == filterMonth
                            || filterMonth === 0;

                    })).length;

                    return (currentMonthFilter && selectedRoomTypeMonthFilter) || filterMonth === 0;
                });
            }
        },
        watch: {
            'selectedFilters.hotel'(newHotelId) {
                const roomTypeId = this.selectedFilters.roomType;
                if (newHotelId && newHotelId !== 'all' && roomTypeId && roomTypeId !== 'all') {
                    let category = this.categories.find(category => {
                        return category.categoryId === roomTypeId;
                    });
                    if (category.hotelId !== newHotelId) {
                        this.selectedFilters.roomType = 'all';
                    }

                }
                console.log('changed hotel');
            },
            'selectedFilters.roomType'() {
                const currentMonth = this.selectedFilters.month;
                const isMonthExistsForRoomType = (this.getMonthFilters.filter(month => {
                    return month.value === currentMonth;
                })).length;
                if (!isMonthExistsForRoomType) {
                    this.selectedFilters.month = 0;
                }
                console.log('changed roomType');
            },
            'selectedFilters.month'() {
                console.log('changed month')
            },
            '$route'() {
            }
        },
        methods: {
            async getSpecials() {
                const url = Routing.generate('az_specials_search', {}, true);
                try {
                    const response = await fetch(url);
                    if (!response.ok) {
                        const error = await response.json();
                        // noinspection ExceptionCaughtLocallyJS
                        throw new Error(error['message']);
                    } else {
                        const data = (await response.json());
                        this.status = 'received';
                        return data;
                    }
                } catch (err) {
                    this.status = 'error';
                    console.error(err);
                }
            },
            fillFilters(filters) {
                const hotels = filters['hotels'];
                const roomType = filters['roomType'];
                const month = filters['month'];
                hotels.forEach(filter => {
                    this.filters.hotels.push(filter);
                });
                roomType.forEach(filter => {
                    this.filters.roomType.push(filter);
                })
                // month.forEach(filter => {
                // this.filters.month.push(filter);
                // })
            },
            fillCategories(categories) {
                this.categories = categories;
            },
            setDefaultFiltersValue() {
                let hotel = params.get('hotel');
                let category = params.get('category');
                let month = params.get('month');
                if (!hotel) {
                    hotel = 'all';
                } else {
                    console.log(hotel);
                    hotel = hotel.replace(/\//, '');
                    console.log(hotel);

                }
                if (!category) {
                    category = 'all';
                } else {
                    category = category.replace(/\//, '');
                }
                if (!month && parseInt(month) !== 0) {
                    month = moment().format('M');
                } else {
                    month = month.replace(/\//, '');
                }


                const april = 4;
                const september = 9;
                const computedMonth = Math.min(Math.max(april, parseInt(month)), september);

                this.$set(this.selectedFilters, 'hotel', hotel);
                this.$set(this.selectedFilters, 'roomType', category);
                this.$set(this.selectedFilters, 'month', computedMonth);
            },
            updateHistory() {
                const hotel = this.selectedFilters.hotel;
                const category = this.selectedFilters.roomType;
                const month = this.selectedFilters.month;

                if (hotel) {
                    if (hotel === 'all') {
                        params.delete('hotel');
                    } else {
                        params.set('hotel', hotel);
                    }
                }
                //
                if (category) {
                    if (category === 'all') {
                        params.delete('category');
                    } else {
                        params.set('category', category);
                    }

                }
                if (month) {
                    params.set('month', month);
                } else if (parseInt(month) === 0) {
                    params.delete('month');
                }

                // const query = params.toString().length ? `?${params.toString()}` : '/';
                // this.$router.push(query);
                const prefix = '/specpredlojenia-dnia/';
                const query = params.toString().length ? `${prefix}?${params.toString()}` : prefix;
                window.history.replaceState({},'', `${query}`);


            },
            togglePriceSorting() {
                if (this.sorting.currentSorting === 'price') {
                    this.$set(this.sorting, 'price', !this.sorting.price);
                }
                this.$set(this.sorting, 'currentSorting', 'price');


            },
            toggleDateSorting() {
                if (this.sorting.currentSorting === 'date') {
                    this.$set(this.sorting, 'date', !this.sorting.date);
                }
                this.$set(this.sorting, 'currentSorting', 'date');
            },
            toggleShowAmount(amount) {
                this.selectedFilters.viewAmount = amount;
            },
            showMore() {
                this.selectedFilters.page++;
            },
            checkRoomTypeDisabled(categoryId) {
                const currentHotel = this.selectedFilters.hotel;
                if (currentHotel === 'all' || categoryId === 'all') {
                    return false;
                }
                let category = this.categories.find(category => {
                    return category.categoryId === categoryId;
                });

                if (category) {
                    return category.hotelId !== currentHotel;
                }

                return true;
            },
            amountActive(amount) {
                return {
                    amountActive: amount === this.selectedFilters.viewAmount,
                    amountLink: true
                }
            },

        }
    }
</script>

<style scoped>
    @import "~animate.css";

    .sort-selector {
        opacity: 0.5;
    }

    .sort-active {
        opacity: 1;
    }

    .last-spec {
        border-bottom: 1px solid #969696 !important;
        padding-bottom: 15px;
    }

    .fade-enter-active, .fade-leave-active {
        transition: opacity .5s;
    }

    .fade-enter, .fade-leave-to {
        opacity: 0;
    }

    .spec-list-move {
        transition: transform 1s;
    }

    .azselect option:disabled {
        opacity: 0.5;
        display: none;
    }

    .amountLink {
        opacity: 0.5;
    }

    .amountActive {
        font-weight: bold;
        opacity: 1;
    }


    .spec-sort {
        text-align: right;
        margin-top: 20px;
    }

    .spec-sort .sortby {
        display: inline-block;
        margin-right: 10px;
    }

    .spec-sort .curspoint {
        cursor: pointer;
        display: inline-block;
    }

    .spec-sort .curspoint.mr20 {
        margin-right: 20px;
    }

    .spec-sort .curspoint p {
        margin: 0;
        padding: 0 10px 0 0;
        float: left;
    }

    .spec-sort .curspoint img {
        float: left;
        margin-top: 7px;
        margin-bottom: 0;
    }

    .spec-sort .curspoint:hover {
        color: #233e5e;
    }

    .spec-sort .spec-sort-wrap {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .spec-sort .spec-sort-cont {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        margin-right: 40px;
    }

    .spec-sort .spec-pokaz-cont {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .spec-sort .spec-pokaz-cont .pokazlink {
        text-decoration: underline;
    }

    .spec-sort .spec-pokaz-cont .pokazlink:hover {
        text-decoration: none;
    }

    .spec-sort .spec-pokaz-cont .pokazlink.mr10 {
        margin-right: 10px;
    }

</style>