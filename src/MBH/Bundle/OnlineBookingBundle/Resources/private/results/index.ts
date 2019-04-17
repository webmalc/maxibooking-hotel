import Vue from 'vue';
import VueRouter from 'vue-router';
import PrettyCheckbox from 'pretty-checkbox-vue';
import store from './store';
import * as queryString from 'query-string';
import routes from './routes';


Vue.config.devtools = true;
Vue.use(VueRouter);
Vue.use(PrettyCheckbox);
const router = new VueRouter({routes});

new Vue({
    el: '#azovsky-mb-results',
    template: '<router-view></router-view>',
    store,
    router,
    created() {
        const parsedUrl = queryString.parse(location.search, {arrayFormat: 'index'});
        this.$store.commit('form/setBegin', parsedUrl['search_form[begin]']);
        this.$store.commit('form/setEnd', parsedUrl['search_form[end]']);
        this.$store.commit('form/setSelectedHotel', parsedUrl['search_form[hotel]']);
        this.$store.commit('form/setAdults', Number(parsedUrl['search_form[adults]']));
        this.$store.commit('form/setChildren', Number(parsedUrl['search_form[children]']));
        const childrenAges = parsedUrl['search_form[children_age]'];
        if (Array.isArray(childrenAges)) {
            for (let index in childrenAges) {
                this.$store.commit('form/setChildrenAges', {key: Number(index), value: Number(childrenAges[index])});
            }
        }
    }

});
