<template>
    <div id="search_results">
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

            <tr v-for="(dateGroupedResults, dateKey) in roomTypeGroupedResult.results" :key="dateKey">
                <td colspan="7">
                    <table style="width: 100%;"
                           class="not-auto-datatable package-search-table table table-striped table-hover table-condensed table-icons table-actions">
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
                        <tbody is="DateResults" :dateKey="dateKey" :roomTypeKey="roomTypeGroupedResult.roomType.id"></tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script lang="ts">
    import moment from 'moment';
    import DateResults from './DateResults.vue';


    moment.locale('ru');
    export default {
        name: "ResultsWrapper",
        props: ['results'],
        data() {
            return {
                sortType: {
                    price: true
                }
            }
        },
        methods: {
            name(result) {
                return `${result.roomType.name}: ${result.roomType.hotelName}`;
            },
            datedName(firstResultInArray) {
                return `${moment(firstResultInArray.begin).format('DD MMMM YYYY')} - ${moment(firstResultInArray.end).format('DD MMMM YYYY')}`
            },
        },
        components: {
            DateResults
        }
    }
</script>

<style scoped>

</style>