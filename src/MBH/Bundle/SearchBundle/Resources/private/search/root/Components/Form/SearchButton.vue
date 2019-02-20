<template>
    <button class="btn btn-primary" @click="doSearch" :disabled="isSearchStarted"><i class="fa fa-search"></i> Найти</button>
</template>

<script>
    export default {
        name: "SearchButton",
        computed: {
            isSearchStarted: {
                get() {
                    return this.$store.getters['search/getIsSearchStarted'];
                },
                set() {
                    this.$store.commit('search/startSearch');
                }
            }
        },
        methods: {
            doSearch() {
                this.isSearchStarted = true;
            }
        }

    }
</script>

<style scoped>

</style>