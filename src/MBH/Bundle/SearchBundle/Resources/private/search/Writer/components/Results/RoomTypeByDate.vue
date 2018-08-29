<template>
    <tr>
        <td colspan="7">
            <table style="width: 100%;" class="package-search-table table table-striped table-hover table-condensed table-icons table-actions">
                <thead>
                <tr>
                    <th class="td-xxs"></th>
                    <th class="td-md">{{date}}</th>
                    <th></th>
                    <th class="td-sm"></th>
                    <th class="td-sm"></th>
                    <th class="td-md"></th>
                    <th class="td-md"></th>
                </tr>
                </thead>
                <tbody is="DateResults" :results="dateResults" :isForceBooking="isForceBooking"></tbody>
            </table>
        </td>
    </tr>

</template>

<script>
    import Vue from 'vue';
    import DateResults from './DateResults';
    export default Vue.extend({
        name: "RoomTypeByDate",
        props: ['date', 'dateResults', 'isForceBooking'],
        components: {
            DateResults
        }
    });

</script>

<style scoped>

</style>