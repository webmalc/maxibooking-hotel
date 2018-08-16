<template>
    <td class="text-center">
        <a v-if="roomsCount > 0" :href="link" target="_blank" class="btn btn-success btn-xs package-search-book"
           :title="'Бронировать номер. Всего номеров: ' + roomsCount">
            <i class="fa fa-book"></i><span class="package-search-book-reservation-text"> Бронировать</span>
        </a>
    </td>
</template>

<script>
    export default {
        name: "PackageLink",
        props: ['link', 'roomsCount']
    }
</script>

<style scoped>

</style>