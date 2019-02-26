const state: object = {
    cache: false,
    async_search: false,
};

const mutations = {
    setCache(state, payload) {
        state.cache = payload;
    },
    setAsyncSearch(state, payload) {
        state.async_search = payload;
    }
};

const getters = {};

const actions = {};

export default {
    namespaced: true,
    state,
    mutations,
    getters,
    actions,
};