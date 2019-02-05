import * as moment from 'moment';
import Vue from 'vue';
moment.locale('ru');

const defaultBegin = moment('24.04.2019', 'DD.MM.YYYY');
const defaultEnd = defaultBegin.clone().add(1, 'days');
const state: object = {
    begin: defaultBegin,
    end: defaultEnd,
    adults: 1,
    children: 0,
    childrenAges: [],
    additionalBegin: 0,
    additionalEnd: 0
};

const mutations = {
    setBegin(state, begin) {
        state.begin = moment(begin, 'DD.MM.YYYY');
    },
    setEnd(state, end) {
        state.end = moment(end, 'DD.MM.YYYY');
    },
    setAdditionalBegin(state, value) {
        state.additionalBegin = value;
    },
    setAdditionalEnd(state, value) {
        state.additionalEnd = value
    },
    setAdults(state, value) {
        state.adults = Number(value);
    },
    setChildren(state, value) {
        state.children = Number(value);
    },
    setChildrenAges(state, payload) {
        Vue.set(state.childrenAges, payload.key, Number(payload.value));
    },
    createChildrenAge(state, payload) {

    },
    deleteChildrenAge(state, payload) {
        state.childrenAges.splice(payload, 1)
    }

};


export default {
    namespaced: true,
    state,
    mutations
}