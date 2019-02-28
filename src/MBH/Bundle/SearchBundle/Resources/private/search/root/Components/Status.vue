<template>
    <div class="search-block">
        <div v-if="priceError" class="alert alert-danger" :key="'priceError'"><i
                class="fa fa-exclamation-circle"></i>
            Внимание! Возможная ошибка цены в поиске!
        </div>

        <transition
                name="animated-css"
                enter-active-class="animated fadeIn"
                leave-active-class="animated fadeOut"
                mode="out-in"
                class="animated-status"
                :duration="{enter:animation.enter, leave: animation.leave}"
                appear
        >

            <div v-if="state === 'new'" class="bg-gray color-palette alert" :key="state">
                <i class="fa fa-search"></i> {{message.new}}
            </div>
            <div v-else-if="state === 'process'" class="alert alert-warning" :key="state"><i
                    class="fa fa-spinner fa-spin"></i>
                {{message.process}}
            </div>
            <div v-else-if="state === 'noResults'" class="alert alert-success" :key="state"><i
                    class="fa fa-exclamation-circle"></i>
                {{message.noResults}}
            </div>
            <div v-else-if="state === 'success'" class="alert alert-success" :key="state"><i
                    class="fa fa-exclamation-circle"></i>
                {{message.success}}
            </div>
            <div v-else-if="state === 'error'" class="alert alert-danger" :key="state"><i
                    class="fa fa-exclamation-circle"></i>
                {{message.error}} {{errorMessage}}.
            </div>
        </transition>
    </div>
</template>

<script lang="ts">
    import {mapState, mapGetters} from 'vuex';

    export default {
        name: "Status",
        data() {
            return {
                message: {
                    new: 'Введите данные для поиска.',
                    process: 'Подождите...',
                    noResults: 'Поиск выполнен.',
                    error: 'Произошла ошибка при запросе в базу данных!',
                    success: 'Результаты поиска:'
                },
                animation: {
                    enter: 120,
                    leave: 120
                }
            }
        },
        computed: {
            state() {
                let state: string;
                if (false === this.status && 0 == this.count) {
                    state = 'new';
                }
                if (true === this.status) {
                    state = 'process';
                }
                if (false === this.status && true === this.error) {
                    state = 'error';
                }
                if (false === this.status && !this.isResults && this.count > 0 && false === this.error) {
                    state = 'noResults';
                }
                if (false === this.status && this.isResults && this.count > 0 && false === this.error) {
                    state = 'success';
                }

                return state;
            },
            priceError() {
                return this.$store.state.debug.error.status;
            },
            ...mapGetters('search', {
                status: 'getIsSearchStarted'
            }),
            ...mapGetters('results', [
                'isResults'
            ]),
            ...mapState('search', ['error', 'errorMessage', 'count'])
        },
    }
</script>

<style scoped>

</style>