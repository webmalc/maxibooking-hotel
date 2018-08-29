<template>
    <table v-if="Object.keys(rawData).length !== 0"
           class="package-search-table table table-striped table-hover table-condensed table-icons table-actions">
        <thead>
        <tr>
            <th class="td-xxs"></th>
            <th class="td-md">Даты</th>
            <th>Тариф</th>
            <th class="td-sm">Количество</th>
            <th class="td-sm">Гости</th>
            <th class="td-md">Цена</th>
            <th class="td-md"></th>
        </tr>
        </thead>
        <tbody is="RoomTypeResults" v-for="(data, key) in rawData" :roomType="data.roomType" :results="data.results"
               :key="key" :isForceBooking="isForceBooking">
        </tbody>
    </table>
</template>

<script lang="ts">
    import Vue from 'vue';
    import RoomTypeResults from './RoomTypeResults'

    export default Vue.extend({
        name: 'SearchResults',
        props: ['rawData'],
        components: {
            RoomTypeResults
        },

        created: function () {
            let switcher = $('#search_conditions_isForceBooking');
            let that = this;
            switcher.on('switchChange.bootstrapSwitch', function (event, state) {
                that.isForceBooking = state;
                console.log('yaya');
            });
        },
        data: function () {
            return {
                isForceBooking: false
            }
        }
    })
</script>

<style scoped>

</style>