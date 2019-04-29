<template>
    <div id="online-booking-search">
        <p v-if="searchStatus && !isResultsFounded">
            Идет поиск, пожалуйста подождите...
        </p>

        <p v-if="!searchStatus && !isResultsFounded">
            Поиск закончен, однако по заданным датам все уже раскуплено. Пожалуйста попробуйте другие даты или позвоните
            нам, а наши прекрасные менеджеры для Вас что то обязательно подберут.
        </p>

        <p v-if="isSearchError">
            Произошла непредвиденная ошибка. Позвоните нам пожалуйста. {{errorMessage}}
        </p>



        <template v-if="isResultsFounded">
            <p class="restitle">Курортная сеть "Азовский": варианты размещения в выбранные объекты</p>
            <div class="infoIncludeN"> Что входит в стоимость путевки:
                <a href="https://azovsky.ru/azovsky/azovskiy-putevka/" target="_blank">Азовский</a>,
                <a href="http://azovsky.ru/azovland/azovland-putevka/" style="" target="_blank">АзовЛенд</a>
            </div>
        </template>


        <transition-group
                tag="div"
                name="result-list"
                enter-active-class="animated fadeIn"
                :duration="{enter:800}"
                appear
                style="display: inline;"
        >
        <Results v-for="(result, key) in prioritySortedResults" :key="`result${key}`" :result="result"/>
        </transition-group>


        <SpecialInstance v-for="(special, key) in specials" :key="`special${key}`" :special="special"/>


        <template v-if="isResultsFounded">
            <div class="infoIncludeN">Что входит в стоимость путевки:
                <a href="https://azovsky.ru/azovsky/azovskiy-putevka/" target="_blank">Азовский</a>,
                <a href="http://azovsky.ru/azovland/azovland-putevka/" target="_blank">АзовЛенд</a>
            </div>

            <div id="online-booking-additional" style="display: none"></div>
            <p><br></p>
            <p>
                <a href="https://maxi-booking.ru/" target="_blank">Бронирование предоставляется системой
                    Maxibooking</a>
            </p>
        </template>
    </div>

</template>

<script lang="ts">
    import Results from './Results.vue';
    import SpecialInstance from './SpecialInstance.vue';

    export default {
        name: "ResultsWrapper",
        components: {
            Results,
            SpecialInstance
        },
        data() {
            return {
                specials: []
            }
        },
        mounted() {
            console.log('mounted');
            this.$store.commit('search/startSearch');
        },
        computed: {
            prioritySortedResults() {
                return this.$store.getters['results/getPrioritySortedResults'];
            },
            searchStatus() {
                return this.$store.getters['search/getIsSearchStarted'];
            },
            isResultsFounded() {
                return this.$store.getters['results/isResults'];
            },
            isAdditionalDates() {
                return Boolean(this.$store.state.form.additionalBegin || this.$store.state.form.additionalEnd);
            },
            isSearchError() {
                return this.$store.state.search.error;
            },
            errorMessage() {
                return this.$store.state.search.errorMessage;
            }
        },
        watch: {
            searchStatus: function (searchStarted, searchStopped) {
                if (searchStarted) {
                    (this.isAdditionalDates && !this.forceSyncSearch) ? this.asyncSearch() : this.syncSearch();
                    // this.specialSearch();
                }
                if (searchStopped) {
                    this.sortAllPrices();
                }
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
            }

        }
    }
</script>

<style scoped>
    @import "~animate.css";
    .result-list-move {
        transition: transform 1s;
    }
</style>
