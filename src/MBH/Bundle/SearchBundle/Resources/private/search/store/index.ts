import Vue from 'vue';
import Vuex from 'vuex';
import createLogger from 'vuex/dist/logger';
import form from './modules/form';
import search from './modules/search';
import results from './modules/results';

Vue.use(Vuex);

const debug: boolean = process.env.NODE_ENV !== 'production';
let plugins = [];
if(debug) {
    plugins.push(createLogger())
}

function builder(initData) {
    return  new Vuex.Store({
        strict: debug,
        plugins: plugins,
        modules: {
            results: results,
            form: form,
            search: search
        }
    });
}


export default builder;