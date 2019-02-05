import Vue from 'vue';
import Vuex from 'vuex';
import createLogger from 'vuex/dist/logger';
import form from './modules/form'

Vue.use(Vuex);

const debug: boolean = process.env.NODE_ENV !== 'production';

export default new Vuex.Store({
    strict: debug,
    plugins: debug ? [createLogger()] : [],
    modules: {
        form
    }
})