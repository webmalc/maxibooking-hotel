<template>
    <div id="all-results">
        <ResultInstance
                v-for="(dateResults, dateKey) in sortedDateResults(result.roomType.id)"
                :data="dateResults"
                :roomType="result.roomType"
                :key="`date-key-${result.roomType.id}-${dateKey}`"/>
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
            }
        }
    }
</script>

<style scoped>

</style>