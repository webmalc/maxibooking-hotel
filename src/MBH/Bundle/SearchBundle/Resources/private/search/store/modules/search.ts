declare let Routing: Routing;
const state = {
    isSearchStarted: false,
    lastSearchType: '',
    error: false,
    errorMessage: '',
    count: 0,
    asyncSearchStartUrl: 'search_start_async',
    asyncSearchUrl: 'search_async_results',
    syncSearchUrl: 'search_sync_start_json',
    specialRoute: 'search_specials',
    errorResults: [],
    forceSyncSearch: false
};


const mutations = {
    startSearch(state) {
        state.count++;
        state.error = false;
        state.errorMessage = '';
        state.isSearchStarted = true;
    },
    stopSearchWithError(state, errorMessage) {
        state.errorMessage = errorMessage;
        state.error = true;
        state.isSearchStarted = false;
    },
    stopSearchSuccess(state) {
        state.error = false;
        state.isSearchStarted = false;
    },
    setSearchSyncType(state) {
        state.lastSearchType = 'sync';
    },
    setSearchAsyncType(state) {
        state.lastSearchType = 'async';
    }
};

const request = (url: string, body: object = {}) => {
    return fetch(url,
        {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
        })
};

const getters = {
    getIsSearchStarted: state => {
        return state.isSearchStarted;
    }

};

const actions = {
    async specialSearch({state, rootGetters, commit}) {
        let conditions = rootGetters['form/getSearchConditions'];
        let url = Routing.generate(state.specialRoute);
        try {
            const response = await request(url, conditions);
            if (!response.ok) {
                const error = await response.json();
                // noinspection ExceptionCaughtLocallyJS
                throw new Error(error['error']);
            } else {
                let data = await response.text();
                commit('results/setSpecialsHtml', data, {root: true});
            }
        } catch (err) {
            console.log(err);
        }

    },
    async syncSearch({state, rootGetters, commit}) {
        commit('setSearchSyncType');
        commit('results/clearResults',null , {root: true});
        let conditions = rootGetters['form/getSearchConditions'];
        try {
            let url = Routing.generate(state.syncSearchUrl, {grouping: 'roomType'});
            console.log(url);
            const response = await request(url, conditions);
            if (!response.ok) {
                const error = await response.json();
                // noinspection ExceptionCaughtLocallyJS
                throw new Error(error['error']);
            } else {
                let data = await response.json();
                commit('stopSearchSuccess');
                commit('results/addResults', data, {root: true});
            }
        } catch (err) {
            commit('stopSearchWithError', err.message);
        }
    },

    async asyncSearch({state, rootGetters, commit}) {
        commit('setSearchAsyncType');
        commit('results/clearResults',null , {root: true});
        let conditions = rootGetters['form/getSearchConditions'];
        const asyncUrl = Routing.generate(state.asyncSearchStartUrl);
        try {
            const conditionResponse = await request(asyncUrl, conditions);
            if (!conditionResponse.ok) {
                const error = await conditionResponse.json();
                // noinspection ExceptionCaughtLocallyJS
                throw new Error(error['error']);
            }
            const data = await conditionResponse.json();
            const conditionsId = data.conditionsId;
            let url = Routing.generate(state.asyncSearchUrl, {id: conditionsId, grouping: 'roomType'});
            let requestThreshold: number = 30;
            let responseStatus: number;
            do {
                requestThreshold--;
                const response = await request(url);
                if (!response.ok) {
                    const error = await response.json();
                    // noinspection ExceptionCaughtLocallyJS
                    throw Error(error['error']);
                }
                responseStatus = response.status;
                if (responseStatus === 200) {
                    let data = await response.json();
                    commit('results/addResults', data, {root: true});
                }
                await new Promise((resolve) => {
                    setTimeout(() => {
                        resolve();
                    }, 1000)
                })
            } while (responseStatus !== 204 && requestThreshold > 0);

            if (responseStatus !== 204) {
                // noinspection ExceptionCaughtLocallyJS
                throw Error('Возмжно не все результаты были получены');
            }
            commit('stopSearchSuccess');

        } catch (err) {
            commit('stopSearchWithError', err.message);
        }
    }
};


export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions,

}