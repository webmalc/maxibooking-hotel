<template>
    <div>
    <div v-if="!isError">
        Ваша заявка принята. Спасибо за использование нашего сервиса.
    </div>
    <div v-if="isError">
        Произошла непредвиденая ошибка. Пожалуйста, позвоните нам.
    </div>
    </div>
</template>

<script>
    export default {
        name: "ReserveResult",
        computed: {
            isError() {
                return this.$store.state.order.orderResult.status !== 'success';
            }
        }
    }
</script>

<style scoped>

</style>