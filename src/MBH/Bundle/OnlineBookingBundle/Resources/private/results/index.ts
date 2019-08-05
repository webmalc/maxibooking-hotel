import Vue from 'vue';
import VueRouter from 'vue-router';
import VueLazyLoad from 'vue-lazyload';
import PrettyCheckbox from 'pretty-checkbox-vue';
import store from './store';
import * as queryString from 'query-string';
import routes from './routes';


Vue.config.devtools = process.env.NODE_ENV === 'development';

Vue.use(VueRouter);
Vue.use(PrettyCheckbox);
Vue.use(VueLazyLoad);
const router = new VueRouter({
    routes,
    scrollBehavior(to, from, savedPosition) {
        return {
            x: 0, y: 0
        }
    }
});

require('../../public/css/ssearch.less');

new Vue({
    el: '#azovsky-mb-results',
    template: '<router-view></router-view>',
    store,
    router,
    created() {
        const parsedUrl = queryString.parse(location.search, {arrayFormat: 'index'});
        this.$store.commit('form/setBegin', parsedUrl['search_form[begin]']);
        this.$store.commit('form/setEnd', parsedUrl['search_form[end]']);
        const hotel = parsedUrl['search_form[hotel]'];
        if (hotel) {
            this.$store.commit('form/setSelectedHotel', hotel);
        }

        this.$store.commit('form/setAdults', Number(parsedUrl['search_form[adults]']));
        this.$store.commit('form/setChildren', Number(parsedUrl['search_form[children]']) ||  Number(0));
        const childrenAges = parsedUrl['search_form[children_age]'];
        if (Array.isArray(childrenAges)) {
            for (let index in childrenAges) {
                this.$store.commit('form/setChildrenAges', {key: Number(index), value: Number(childrenAges[index])});
            }
        }
    }

});
