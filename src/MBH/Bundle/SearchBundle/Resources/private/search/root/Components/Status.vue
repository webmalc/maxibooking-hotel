<template>
    <div v-if="state === 'new'" class="bg-gray color-palette alert">
        <i class="fa fa-search"></i> {{message.new}}
    </div>
    <div v-else-if="state === 'process'" class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i>
        {{message.process}}
    </div>
    <div v-else-if="state === 'noResults'" class="alert alert-warning"><i class="fa fa-exclamation-circle"></i>
        {{message.noResults}}
    </div>
    <div v-else-if="state === 'error'" class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>
        {{message.error}} {{errorMessage}}.
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
                    error: 'Произошла ошибка при запросе в базу данных!'
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

                return state;
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