import * as moment from 'moment';
import Vue from 'vue';
moment.locale('ru');

const state: object = {
    begin: '',
    end: '',
    adults: 1,
    children: 0,
    childrenAges: [],
    additionalBegin: 7,
    additionalEnd: 7,
    selectedRoomTypes: [],
    selectedHotels: [],
    orderId: 0,
    isForceBooking: false,
    isSpecialStrict: false,
    isUseCache: false,
    errorLevel: 0,
    isOnline: true,
    roomTypeSelections: [],

};

const mutations = {
    setBegin(state, begin) {
        state.begin = begin;
    },
    setEnd(state, end) {
        state.end = end;
    },
    setAdditionalBegin(state, value) {
        state.additionalBegin = value;
    },
    setAdditionalEnd(state, value) {
        state.additionalEnd = value
    },
    setAdults(state, value) {
        state.adults = value;
    },
    setChildren(state, value) {
        state.children = value;
    },
    setChildrenAges(state, payload) {
        Vue.set(state.childrenAges, payload.key, payload.value);
    },
    deleteChildrenAge(state, payload) {
        state.childrenAges.splice(payload, 1)
    },
    addToSelectedRoomTypes(state, payload) {
        state.selectedRoomTypes.push(payload);
    },
    removeFromSelectedRoomTypes(state, payload) {
        let index = state.selectedRoomTypes.indexOf(payload);
        if (index > -1) {
            state.selectedRoomTypes.splice(index, 1);
        }
    },
    setOrderId(state, payload) {
        state.orderId = payload;
    },
    setIsForceBooking(state, payload) {
        state.isForceBooking = payload;
    },
    setIsSpecialStrict(state, payload) {
        state.isSpecialStrict = payload;
    },
    setRoomTypeSelections(state, payload) {
        state.roomTypeSelections = payload;
    },
    setSelectedHotel(state, payload) {
        state.selectedHotels.push(payload);
    },
    setIsUseCache(state, payload) {
        state.isUseCache = payload;
    }

};

const getters = {
    getSearchConditions: (state) => {
        return {
            begin: state.begin,
            end: state.end,
            adults: state.adults,
            children: state.children,
            childrenAges: state.childrenAges,
            roomTypes: state.selectedRoomTypes,
            hotels:state.selectedHotels,
            isForceBooking: state.isForceBooking,
            isSpecialStrict: state.isSpecialStrict,
            order: state.orderId,
            additionalBegin: state.additionalBegin,
            additionalEnd: state.additionalEnd,
            isUseCache: state.isUseCache,
            errorLevel: state.errorLevel,
            isOnline: state.isOnline
        }
    }
};

const actions = {

};


export default {
    namespaced: true,
    state,
    mutations,
    getters,
    actions,
}