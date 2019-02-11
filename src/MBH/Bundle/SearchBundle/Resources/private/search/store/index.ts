import Vue from 'vue';
import Vuex from 'vuex';
import createLogger from 'vuex/dist/logger';
import form from './modules/form';
import search from './modules/search';

Vue.use(Vuex);

const debug: boolean = process.env.NODE_ENV !== 'production';
let plugins = [];
if(debug) {
    plugins.push(createLogger())
}

function builder(initData) {
    const store =  new Vuex.Store({
        strict: debug,
        plugins: plugins,
        modules: {
            form,
            search
        }
    });

    // store.commit('form/setBegin', initData.begin);
    // store.commit('form/setRoomTypeSelections', initData.choices);

    return store;
}


export default builder;