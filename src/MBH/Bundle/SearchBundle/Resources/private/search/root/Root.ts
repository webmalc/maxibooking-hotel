declare let Routing: Routing;
import Vue from 'vue';
import store from '../store';

import VueRoot from './Components/VueRoot.vue'

Vue.config.devtools = true;

// Store clever init https://stackoverflow.com/a/45899653/3725361
export class Root {

    public startApplication(): void {
        new Vue({
            el: '#vue-searcher',
            template: '<VueRoot />',
            render: h => h(VueRoot),
            store: store({}),
            beforeMount() {
                const order = this.$el.getAttribute('data-order');
                const begin = this.$el.getAttribute('data-begin');
                const end = this.$el.getAttribute('data-end');
                const roomTypes = JSON.parse(this.$el.getAttribute('data-room-types'));
                const accessCache = (this.$el.getAttribute('data-access-cache') === 'true');
                const accessAsyncSearch = (this.$el.getAttribute('data-access-async-search') === 'true');
                const positiveMaxAdditionalDays = this.$el.getAttribute('data-positive-max-add-days');
                const negativeMaxAdditionalDays = this.$el.getAttribute('data-negative-max-add-days');
                this.$store.commit('form/setOrderId', order);
                this.$store.commit('form/setBegin', begin);
                this.$store.commit('form/setEnd', end);
                this.$store.commit('form/setRoomTypeSelections', roomTypes);
                this.$store.commit('access/setCache', accessCache);
                this.$store.commit('access/setAsyncSearch', accessAsyncSearch);
                this.$store.commit('access/setPositiveMaxAddDates', positiveMaxAdditionalDays);
                this.$store.commit('access/setNegativeMaxAddDates', negativeMaxAdditionalDays);
            }
        })
    }
}