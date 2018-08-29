<template>
    <div v-if="status.state === 'new' " class="bg-gray color-palette alert"><i class="fa fa-search"> </i> Введите данные
        для поиска
    </div>
    <div v-else-if="status.state === 'noResults' " class="alert alert-warning"><i class="fa fa-exclamation-circle"></i>
        Поиск выполнен.
    </div>
    <div v-else-if="status.state === 'error' " class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>
        Произошла ошибка при запросе в базу данных!
    </div>
    <div v-else-if="status.state === 'process' " class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i>
        Подождите...
    </div>
</template>

<script lang="ts">
    import Vue from 'vue';

    export default Vue.extend({
        name: "status",
        props: ['status']
    })

</script>

<style scoped>

</style>