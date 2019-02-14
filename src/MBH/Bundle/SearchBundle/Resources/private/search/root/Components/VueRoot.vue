<template>
    <div>

        <div class="box box-default box-solid" id="searcher">
            <div class="box-header">
                <h3 class="box-title">Фильтр
                    <small>поиск</small>
                </h3>
                <div class="box-tools pull-right">
                    <button class="btn btn-box-tool form-group-collapse" id="searcher-collapse"
                            data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="bg-gray-disabled color-palette box-body">
                <Form/>
            </div>
        </div>

        <Status/>
        <ResultsWrapper :results="prioritySortedResults" />


    </div>
</template>

<script lang="ts">
    import {mapState} from "vuex";
    import Form from "./Form/Form.vue";
    import Status from "./Status.vue";
    import ResultsWrapper from './Results/ResultsWrapper.vue';


    export default {
        name: "VueRoot",
        components: {
            Form,
            Status,
            ResultsWrapper
        },
        computed: {
            prioritySortedResults() {
                return this.$store.getters['results/getPrioritySortedResults'];
            },
            searchStatus() {
                return this.$store.getters['search/getIsSearchStarted'];
            },
            isAdditionalDates() {
                return Boolean(this.$store.state.form.additionalBegin || this.$store.state.form.additionalEnd);
            },
        },
        watch: {
            searchStatus: function (searchStarted, searchStopped) {
                if (searchStarted) {
                    (this.isAdditionalDates && !this.forceSyncSearch) ? this.asyncSearch() : this.syncSearch();
                }
                if (searchStopped) {
                    this.sortAllPrices();
                }
            }
        },
        ...mapState(
            'search',
            ['forceSyncSearch']
        ),
        beforeMount() {
            this.sortAllPrices();
        },
        methods: {
            syncSearch() {
                this.$store.dispatch('search/syncSearch');
            },
            asyncSearch() {
                this.$store.dispatch('search/asyncSearch');
            },
            sortAllPrices() {
                this.$store.commit('results/sortAllPrices');
            }

        }
    }

</script>

<style scoped>

</style>