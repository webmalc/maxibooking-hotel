import Vue from 'vue';

const state = {
    successes: {},
    errors: {},
    amount: 0
};

const mutations = {
    addResults(state, payload) {
        const success = payload.results.success;
        const errors = payload.results.errors;
        resultMerger(state.successes, success);
        resultMerger(state.errors, errors);
    },
    clearResults(state) {
        state.success = {};
        state.errors = {};
        state.amount = 0;
    }
};
const getters = {
    isResults: state => {
        return Boolean(Object.keys(state.successes).length/* || Object.keys(state.errors).length*/);
    },
    amount: state => {
        return Object.keys(state.errors).length;
    }
};

const resultMerger = (existsResultsData, newResultsData) => {
    for (let newResultRoomTypeKey in newResultsData) {
        if (!newResultsData.hasOwnProperty(newResultRoomTypeKey)) {
            continue;
        }

        if (!existsResultsData.hasOwnProperty(newResultRoomTypeKey)) {
            Vue.set(existsResultsData, newResultRoomTypeKey, newResultsData[newResultRoomTypeKey])
        } else {
            const existenceResultsByDate = existsResultsData[newResultRoomTypeKey].results;
            const newResultsByDate = newResultsData[newResultRoomTypeKey].results;
            for (let dateKey in newResultsByDate) {
                if (newResultsByDate.hasOwnProperty(dateKey) && existenceResultsByDate.hasOwnProperty(dateKey)) {
                    for (let resultInstance of newResultsByDate[dateKey]) {
                        existenceResultsByDate[dateKey].push(resultInstance);
                    }
                } else {
                    Vue.set(existenceResultsByDate, dateKey, newResultsByDate[dateKey]);
                }
            }
        }

    }
};

export default {
    namespaced: true,
    state,
    mutations,
    getters
}``