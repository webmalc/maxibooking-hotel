import Vue from 'vue';
import VueCurrencyFilter from 'vue-currency-filter';
import Specials from './components/Specials.vue';
import VueRouter from 'vue-router';

Vue.config.devtools = true;
Vue.use(VueCurrencyFilter, {
    thousandsSeparator: ' ',

});
Vue.use(VueRouter);

require('../../public/css/spec-slider.css');


const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path:'/',
            component: Specials
        }
    ]


});

new Vue({
    el: '#spec-filtered',
    template: '<Specials />',
    components: {
        Specials
    },
    router
});