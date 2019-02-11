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
    </div>
</template>

<script lang="ts">
    import Form from "./Form/Form.vue";
    import Status from "./Status.vue";
    import {mapState} from "vuex";

    export default {
        name: "VueRoot",
        components: {
            Form,
            Status
        },
        computed: {
            searchStatus() {
                return this.$store.getters['search/getIsSearchStarted'];
            },
            isAdditionalDates() {
                return Boolean(this.$store.state.form.additionalBegin || this.$store.state.form.additionalEnd);
            }
        },
        methods: {
            syncSearch() {
                this.$store.dispatch('search/syncSearch');
            },
            asyncSearch() {
                this.$store.dispatch('search/asyncSearch')
            }
        },
        ...mapState(
            'search',
            ['forceSyncSearch']
        ),
        watch: {
            searchStatus: function (searchStarted) {
                if (searchStarted) {
                    (this.isAdditionalDates && !this.forceSyncSearch) ? this.asyncSearch() : this.syncSearch();
                }
            }
        }
    }

</script>

<style scoped>

</style>