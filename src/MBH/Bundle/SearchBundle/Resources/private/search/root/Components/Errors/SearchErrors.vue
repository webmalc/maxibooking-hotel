<template>
    <div id="errors">
        Errors

    </div>
</template>

<script>
    export default {
        name: "SearchErrors",
        computed: {
            windowError() {
                return this.$store.getters['results/getErrorsByType'](1);
            }
        }
    }
</script>

<style scoped>

</style>