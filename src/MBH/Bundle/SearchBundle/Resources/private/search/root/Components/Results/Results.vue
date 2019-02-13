<template>

    <tbody v-for="(roomTypeGroupedResult, roomTypeId) in roomTypeGroupedResults" :key="roomTypeId">

    <tr class="mbh-grid-header1 info">
        <td colspan="7">{{name(roomTypeGroupedResult)}}</td>
    </tr>

    <tr v-for="(dateGroupedResults, dateKey) in dateResults(roomTypeGroupedResult)" :key="dateKey">
        <td colspan="7">
            <table style="width: 100%;"
                   class="not-auto-datatable package-search-table table table-striped table-hover table-condensed table-icons table-actions">
                <thead>
                <tr>
                    <th class="td-xxs"></th>
                    <th class="td-lg">{{date(dateKey)}}</th>
                    <th></th>
                    <th class="td-sm"></th>
                    <th class="td-sm"></th>
                    <th class="td-md"></th>
                    <th class="td-md"></th>
                </tr>
                </thead>
                <tbody is="DateResults" :results="dateGroupedResults"></tbody>
            </table>
        </td>
    </tr>
    </tbody>
</template>

<script lang="ts">



    export default {
        name: "Results",
        methods: {
            name(result) {
                return `${result.roomType.categoryName}: ${result.roomType.hotelName}`;
            },
            dateResults(result) {
                return result.results;
            },
            date(dateKey) {
                const dates = dateKey.split("_");
                return `${moment(dates[0], 'DD.MM.YYYY').format('DD MMMM YYYY')} - ${moment(dates[1], 'DD.MM.YYYY').format('DD MMMM YYYY')}`
            }
        },
    }
</script>

<style scoped>

</style>