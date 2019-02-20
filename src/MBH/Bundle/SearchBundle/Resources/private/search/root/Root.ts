declare let Routing: Routing;
import Vue from 'vue';
import store from '../store';

import VueRoot from './Components/VueRoot'

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
                this.$store.commit('form/setOrderId', order);
                this.$store.commit('form/setBegin', begin);
                this.$store.commit('form/setEnd', end);
                this.$store.commit('form/setRoomTypeSelections', roomTypes)
            }
        })
    }
}