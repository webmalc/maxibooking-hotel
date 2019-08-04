<template>
    <div id="all-results">
        <ResultInstance
                v-for="(dateResults, dateKey) in sortedDateResults(result.roomType)"
                :data="dateResults"
                :roomType="roomTypeInfo(result.roomType)"
                :key="`date-key-${result.roomType}-${dateKey}`"/>
    </div>
</template>

<script>
    import ResultInstance from './ResultInstance.vue'
    export default {
        name: "Results",
        props: ['result'],
        components: {
            ResultInstance
        },
        methods: {
            sortedDateResults(roomTypeId) {
                return this.$store.getters['results/getSortedDayResults'](roomTypeId);
            },
            roomTypeInfo(roomTypeId) {
                return this.$store.getters['results/getRoomTypeInfo'](roomTypeId)
            }
        }
    }
</script>

<style scoped>

</style>