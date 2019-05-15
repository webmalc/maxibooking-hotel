import Vue from 'vue';
import VueCurrencyFilter from 'vue-currency-filter';
import Specials from './components/Specials.vue';

Vue.config.devtools = true;
Vue.use(VueCurrencyFilter, {
    thousandsSeparator: ' ',

});
require('../../public/css/spec-slider.css');

new Vue({
    el: '#spec-filtered',
    template: '<Specials />',
    components: {
        Specials
    }
});