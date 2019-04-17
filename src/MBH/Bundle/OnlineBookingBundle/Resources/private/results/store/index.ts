import Vue from 'vue';
import Vuex from 'vuex';
import createLogger from 'vuex/dist/logger';
import search from "./modules/search";
import form from "./modules/form"
import results from "./modules/results"
// import results from "../../../../../SearchBundle/Resources/private/search/store/modules/results";
// import form from "../../../../../SearchBundle/Resources/private/search/store/modules/form";
// import access from "../../../../../SearchBundle/Resources/private/search/store/modules/access";

Vue.use(Vuex);
const debug: boolean = process.env.NODE_ENV !== 'production';
let plugins = [];
if(debug) {
    plugins.push(createLogger())
}

export default new Vuex.Store({
    strict: debug,
    plugins,
    modules: {
        search,
        form,
        results
        // access: access,
    }
});
