<template>
    <tbody>
        <tr is="ResultBar" v-for="(result, key) in results" :key="key" :result="result"></tr>
    </tbody>
</template>

<script lang="ts">
    import ResultBar from './ResultBar.vue';

    export default {
        name: "DateResults",
        props: ['dateKey', 'roomTypeKey'],
        computed: {
            results() {
                return this.$store.getters['results/getDayResults'](this.roomTypeKey, this.dateKey);
            }
        },
        components: {
            ResultBar
        }
    }
</script>

<style scoped>

</style>