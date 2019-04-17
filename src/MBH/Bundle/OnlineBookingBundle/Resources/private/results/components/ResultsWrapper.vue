<template>
    <div id="online-booking-search">
        <p class="restitle">Курортная сеть "Азовский": варианты размещения в выбранные объекты</p>
        <div class="infoIncludeN"> Что входит в стоимость путевки:
            <a href="https://azovsky.ru/azovsky/azovskiy-putevka/" target="_blank">Азовский</a>,
            <a href="http://azovsky.ru/azovland/azovland-putevka/" style="" target="_blank">АзовЛенд</a>
        </div>

        <Results v-for="(result, key) in prioritySortedResults" :key="`result${key}`" :result="result"/>
        <SpecialInstance v-for="(special, key) in specials" :key="`special${key}`" :special="special"/>

        <div class="infoIncludeN">Что входит в стоимость путевки:
            <a href="https://azovsky.ru/azovsky/azovskiy-putevka/" target="_blank">Азовский</a>,
            <a href="http://azovsky.ru/azovland/azovland-putevka/" target="_blank">АзовЛенд</a>
        </div>

        <div class="addDates">
            <a id="addDatesLink"
               href="index.html?search_form[hotel]=&amp;search_form[begin]=08.07.2019&amp;search_form[end]=15.07.2019&amp;search_form[adults]=1&amp;search_form[children]=2&amp;search_form[children_age][1]=3&amp;search_form[children_age][2]=12&amp;submit=Узнат#">Посмотреть
                еще варианты
            </a>
        </div>

        <div id="online-booking-additional" style="display: none"></div>
        <p><br></p>
        <p>
            <a href="https://maxi-booking.ru/" target="_blank">Бронирование предоставляется системой
                Maxibooking</a>
        </p>

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
            isAdditionalDates() {
                return Boolean(this.$store.state.form.additionalBegin || this.$store.state.form.additionalEnd);
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

</style>