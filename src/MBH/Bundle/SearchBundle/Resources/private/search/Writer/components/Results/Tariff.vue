<template>
    <td>{{tariff.name}}<br><small><span class="package-search-book-count">Свободно номеров: {{freeRooms}}</span></small></td>
</template>

<script>
    export default {
        name: "Tariff",
        props: ['tariff', 'freeRooms']
    }
</script>

<style scoped>

</style>