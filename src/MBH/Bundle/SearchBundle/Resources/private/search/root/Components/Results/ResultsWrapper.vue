<template>
    <div v-if="isResults" id="search_results">
        <!--<button @click="shuffle">button</button>-->
        <table class="not-auto-datatable package-search-table table table-striped table-hover table-condensed table-icons table-actions">
            <thead>
            <tr>
                <th class="td-xxs"></th>
                <th class="td-lg">Даты</th>
                <th>Тариф</th>
                <th class="td-sm">Количество</th>
                <th class="td-sm">Гости</th>
                <th class="td-md">Цена</th>
                <th class="td-md"></th>
            </tr>
            </thead>

            <tbody v-for="(roomTypeGroupedResult, key) in results" :key="key">

            <tr class="mbh-grid-header1 info">
                <td colspan="7">{{name(roomTypeGroupedResult)}}</td>
            </tr>


            <tr>
                <transition-group
                        colspan="7" tag="td"
                        enter-active-class="animated fadeIn"
                        leave-active-class="animated fadeOut"
                        :duration="{enter:200, leave: 200}"
                        type="transition"
                        appear
                >
                    <table style="width: 100%;"
                           class="not-auto-datatable package-search-table table table-striped table-hover table-condensed table-icons table-actions"
                           v-for="(dateGroupedResults, dateKey) in sortedDateResults(roomTypeGroupedResult.roomType)" :key="`date-key-${dateKey}`"
                    >
                        <thead>
                        <tr>
                            <th class="td-xxs"></th>
                            <th class="td-lg">{{datedName(dateGroupedResults[0])}}</th>
                            <th></th>
                            <th class="td-sm"></th>
                            <th class="td-sm"></th>
                            <th class="td-md"></th>
                            <th class="td-md"></th>
                        </tr>
                        </thead>
                        <transition-group
                            tag="tbody"
                            enter-active-class="animated fadeIn"
                            :duration="{enter:800}"
                            name="result-bar"
                            appear
                        >
                        <tr is="ResultBar" v-for="(barResult, groupedKey) in dateGroupedResults" :key="`bar-key-${groupedKey}`" :result="barResult"></tr>
                        </transition-group>
                    </table>
                </transition-group>
            </tr>

            </tbody>
        </table>
    </div>
</template>

<script lang="ts">
    import * as moment from 'moment';
    import ResultBar from './ResultBar.vue'


    moment.locale('ru');

    export default {
        name: "ResultsWrapper",
        components: {
            ResultBar
        },
        props: {
            results: Array
        },
        data() {
            return {
                sortType: {
                    price: true
                }
            }
        },
        computed: {
            isResults() {
                return this.$store.getters['results/isResults'];
            }
        },
        methods: {
            name(result) {
                const roomTypeId = result.roomType;
                const roomType = this.roomTypeInfo(roomTypeId);
                const hotelId = roomType.hotel.$id.$id;
                const hotel = this.hotelInfo(hotelId);

                return `${roomType.fullTitle}: ${hotel.fullTitle}`;
            },
            datedName(firstResultInArray) {
                return `${moment(firstResultInArray.begin, 'DD.MM.YYYY').format('DD MMMM YYYY')} - ${moment(firstResultInArray.end, 'DD.MM.YYYY').format('DD MMMM YYYY')}`
            },
            sortedDateResults(roomTypeId) {
                return this.$store.getters['results/getSortedDayResults'](roomTypeId);
            },
            shuffle() {
                this.$store.commit('results/shuffle');
            },
            roomTypeInfo(roomTypeId) {
                return this.$store.getters['results/getRoomTypeInfo'](roomTypeId);
            },
            hotelInfo(hotelId) {
                return this.$store.getters['results/getHotelInfo'](hotelId);
            }
        }
    }
</script>

<style scoped>
</style>