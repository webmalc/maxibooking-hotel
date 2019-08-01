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
                <Form />
            </div>
        </div>

        <Status/>
        <ResultsWrapper :results="prioritySortedResults" />

        <div class="time-spend" v-if="searchTime"><small>Время поиска: {{searchTime}}</small></div>

        <Specials />
        <!--<SearchErrors />-->



    </div>
</template>

<script lang="ts">
    import {mapState} from "vuex";
    import Form from "./Form/Form.vue";
    import Status from "./Status.vue";
    import ResultsWrapper from './Results/ResultsWrapper.vue';
    import Specials from './Specials.vue';
    import SearchErrors from './Errors/SearchErrors.vue';


    export default {
        name: "VueRoot",
        components: {
            Form,
            Status,
            ResultsWrapper,
            Specials,
            SearchErrors
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
            isDebug() {
                return Boolean(this.$store.state.debug.isDebug);
            },
            searchTime() {
                return this.$store.state.results.time;
            }
        },
        watch: {
            searchStatus: function (searchStarted, searchStopped) {
                if (searchStarted) {
                    this.debugClearErrors();
                    (this.isAdditionalDates && !this.forceSyncSearch) ? this.asyncSearch() : this.syncSearch();
                    // this.specialSearch();
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
        updated() {
            if (!this.searchStatus && this.isDebug) {
                this.debugCheckPrices();
            }
        },
        methods: {
            syncSearch() {
                this.$store.dispatch('search/syncSearch');
            },
            asyncSearch() {
                this.$store.dispatch('search/asyncSearch');
            },
            specialSearch() {
                this.$store.dispatch('search/specialSearch');
            },
            sortAllPrices() {
                this.$store.commit('results/sortAllPrices');
            },
            debugCheckPrices() {
                this.$store.dispatch('debug/debugCheckPrices');
            },
            debugClearErrors() {
                this.$store.commit('debug/clearCompares');
                this.$store.commit('debug/clearError');
            }

        }
    }

</script>

<style scoped>
    @import "~animate.css";
    .time-spend {
        text-align: right;
    }
</style>