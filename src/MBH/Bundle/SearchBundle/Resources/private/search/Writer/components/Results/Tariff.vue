<template>
    <td>{{tariff.name}}<br><small><span class="package-search-book-count">Свободно номеров: {{freeRooms}}</span></small></td>
</template>

<script lang="ts">
    import Vue from "vue";
    export default Vue.extend({
        name: "Tariff",
        props: ['tariff', 'freeRooms']
    })
</script>

<style scoped>

</style>