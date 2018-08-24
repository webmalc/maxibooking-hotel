<template>
    <tbody>
    <tr class="mbh-grid-header1 info">
        <td colspan="8">{{roomType.name}}: {{roomType.hotelName}}</td>
    </tr>
    <tr is="RoomTypeByDate" v-for="(dateResults, key) in results" :date="key" :dateResults="dateResults"></tr>
    </tbody>
</template>

<script>
    import Vue from 'vue';
    import RoomTypeByDate from './RoomTypeByDate';

    export default Vue.extend({
        name: "RoomTypeResults",
        props: ['roomType', 'results'],
        components: {
            RoomTypeByDate
        }
    });
</script>

<style scoped>

</style>