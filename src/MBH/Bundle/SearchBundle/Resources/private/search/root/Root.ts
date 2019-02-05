import Vue from 'vue';
import store from '../store';

import VueRoot from './Components/VueRoot'
// Store clever init https://stackoverflow.com/a/45899653/3725361
export class Root {
    public startApplication(): void {
        new Vue({
            el: '#vue-searcher',
            template: '<VueRoot />',
            render: h => h(VueRoot),
            store: store
        })
    }
}