import Vue from 'vue';
import * as _ from 'lodash';

const state = {
    successes:{},
    errors: {},
    amount: 0,
    specialsHtml: '',
    time: ''
};

const minPriceInDays = (dayResults) => {
    let resultPrice;
    for (let index in dayResults) {
        if (dayResults.hasOwnProperty(index)) {
            let minPrice = minPriceInDay(dayResults[index]);
            if (!resultPrice) {
                resultPrice = minPrice;
            }
            resultPrice = Math.min(resultPrice, minPrice);
        }

    }

    return resultPrice;
};
const minPriceInDay = (dayResult) => {
    return Math.min(...dayResult.map(function (data) {
        return data.prices[0].total;
    }));
};

const mutations = {
    setSpecialsHtml(state, payload) {
        state.specialsHtml = payload;
    },
    addResults(state, payload) {
        if (payload.results.hasOwnProperty('success')) {
            const successes = payload.results.success;
            resultMerger(state.successes, successes);
        }

        if (payload.results.hasOwnProperty('errors')) {
            const errors = payload.results.errors;
            resultMerger(state.errors, errors);
        }

        if (payload.results.hasOwnProperty('time')) {
            state.time = payload.results.time;
        }


    },
    clearResults(state) {
        state.successes = {};
        state.errors = {};
        state.amount = 0;
        state.specialsHtml = '';
        state.time = '';
    },
    sortAllPrices(state) {
        for (let resultId in state.successes) {
            if (state.successes.hasOwnProperty(resultId)) {
                let dayResults = state.successes[resultId].results;
                for (let dateKey in dayResults) {
                    if (dayResults.hasOwnProperty(dateKey)) {
                        dayResults[dateKey].sort(function (resA, resB) {
                            let priceA = resA.prices[0].total;
                            let priceB = resB.prices[0].total;

                            return priceA < priceB ? -1 : 1;
                        });
                    }
                }

            }
        }
    },
    shuffle(state){
        for (let resultId in state.successes) {
            if (state.successes.hasOwnProperty(resultId)) {
                let dayResults = state.successes[resultId].results;
                for (let dateKey in dayResults) {
                    if (dayResults.hasOwnProperty(dateKey)) {
                        dayResults[dateKey] = _.shuffle(dayResults[dateKey]);
                    }
                }

            }
        }
    },
    sortPricesInsideDay(state, payload) {
        let results =  state.successes[payload.roomTypeId].results[payload.dateKey];
        results.sort(function (resA, resB) {
            let priceA = resA.prices[0].total;
            let priceB = resB.prices[0].total;

            let result =  priceA < priceB ? -1 : 1;
            if (payload.direction) {
                result = -(result);
            }

            return result;
        });
    },
    bookingAction(state, {roomType, amount}) {
        const results = state.successes[roomType].results;
        for (let dateKey in results) {
            if (results.hasOwnProperty(dateKey)) {
                results[dateKey].map(function(result) {
                    let rest = result.minRoomsCount - amount;
                    if (rest < 0) {
                        rest = 0;
                    }
                    result.minRoomsCount = rest;
                })
            }
        }
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

const getters = {
    isResults: state => {
        return Boolean(Object.keys(state.successes).length/* || Object.keys(state.errors).length*/);
    },
    getDayResults: state => (roomTypeId, dateKey) => {
        return  state.successes[roomTypeId].results[dateKey];
    },
    getSortedDayResults: state => roomTypeId => {
        let dateResults = state.successes[roomTypeId].results;
        let sortable = [];

        for (let dateKey in dateResults) {
            if (dateResults.hasOwnProperty(dateKey)) {
                sortable.push(dateResults[dateKey]);
            }
        }
        sortable.sort(function (dayA, dayB) {
            let minPriceA = minPriceInDay(dayA);
            let minPriceB = minPriceInDay(dayB);

            if (minPriceA < minPriceB) {
                return -1;
            }
            if (minPriceA > minPriceB) {
                return 1
            }
        });

        return sortable;
    },
    getPrioritySortedResults: state => {
        const rawResults = state.successes;
        let sortable = [];
        for (let roomTypeId in rawResults) {
            if (rawResults.hasOwnProperty(roomTypeId)) {
                sortable.push(rawResults[roomTypeId]);
            }
        }

        sortable.sort(function (resultA, resultB) {
            if (resultA.roomType.priority < resultB.roomType.priority) {
                return -1;
            }
            if (resultA.roomType.priority > resultB.roomType.priority) {
                return 1;
            }

            if (resultA.roomType.priority === resultB.roomType.priority) {
                const daysA = resultA.results;
                const daysB = resultB.results;
                let minPriceA = minPriceInDays(daysA);
                let minPriceB = minPriceInDays(daysB);

                if (minPriceA < minPriceB) {
                    return -1
                }

                if (minPriceA > minPriceB) {
                    return 1;
                }

                return 0;
            }


        });

        return sortable;
    },
    getErrorsByType: state => type => {
        let results = [];
        const allErrors = state.errors;
        for (let roomTypeKey in allErrors) {
            if (allErrors.hasOwnProperty(roomTypeKey)) {
                let roomTypeResult = allErrors[roomTypeKey];
                let roomType = roomTypeResult.roomType;
                let dateResults = roomTypeResult.results;

            }
        }
    }
};

export default {
    namespaced: true,
    state,
    mutations,
    getters
}