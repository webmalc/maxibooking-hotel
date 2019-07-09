const state: object = {
    cache: false,
    async_search: false,
    positiveMaxAddDates: 7,
    negativeMaxAddDates: 7
};

const mutations = {
    setCache(state, payload) {
        state.cache = payload;
    },
    setAsyncSearch(state, payload) {
        state.async_search = payload;
    },
    setPositiveMaxAddDates(state, payload) {
        state.positiveMaxAddDates = Number(payload);
    },
    setNegativeMaxAddDates(state, payload) {
        state.negativeMaxAddDates = Number(payload);
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