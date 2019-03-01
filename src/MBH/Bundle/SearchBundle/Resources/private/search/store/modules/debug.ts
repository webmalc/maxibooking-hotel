declare let Routing: Routing;
const state: StateInterface = {
    isDebug: true,
    compares: [],
    error: {
        status: false,
        errorList: []
    }
};

const mutations = {
    addToCompares(state, payload) {
        state.compares.push(payload);
    },
    clearCompares(state) {
        state.compares = [];
    },
    setError(state) {
        state.error.status = true
    },
    setErrorList(state, payload) {
        if (state.error.status == false) {
            state.error.status = true;
        }

        state.error.errorList = payload;
    },
    clearError(state) {
        state.error.status = false;
        state.error.errorList.splice(0, state.error.errorList.length);
    },
};

const actions = {

    async debugCheckPrices({state, commit}) {
        const data = state.compares;
        const url = Routing.generate('search_price_check');
        try {
            const response = await fetch(url, {
                method: 'post',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            if (!response.ok) {
                const error = await response.json();
                // noinspection ExceptionCaughtLocallyJS
                throw new Error(error['error']);
            } else {
                let data = await response.json();
                const errors = data['wrongPrice'];
                if (errors.length) {
                    commit('setErrorList', errors);
                }

            }
        } catch (err) {
            console.error(err.message);
        }

    }

};


// checkPricesByIterable({state, commit}) {
//     const links = state.links;
//     commit('clearLinks');
//     if (state.priceCheckerState) {
//         commit('cancelPriceCheckerState')
//     }
//     commit('addPriceCheckerState');
//     asyncCheckPrices(links, commit, state.priceCheckerState);
// },



// let asyncCheckPrices = async (links, commit, checkerState) => {
//     for (let index in links) {
//         if (!links.hasOwnProperty(index)) {
//             continue;
//         }
//         if (checkerState.cancel) {
//             console.log('пропускаю');
//             continue;
//         }
//         let result = links[index];
//         try {
//             let url = result.link;
//             const response = await fetch(url);
//             if (!response.ok) {
//                 const error = await response.json();
//                 // noinspection ExceptionCaughtLocallyJS
//                 throw new Error(error['error']);
//             } else {
//                 let data = await response.json();
//                 let packagePrice = data.price;
//                 let price = result.price;
//                 if (packagePrice !== price) {
//                     commit('addErrorInList', result.anchor)
//                 }
//             }
//         } catch (err) {
//             console.error(err.message);
//         } finally {
//             console.log('tik')
//         }
//     }
// };

export default {
    namespaced: true,
    state,
    mutations,
    actions
}