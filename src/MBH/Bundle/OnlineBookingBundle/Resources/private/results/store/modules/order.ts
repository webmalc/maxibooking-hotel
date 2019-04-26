import * as moment from 'moment';
declare let Routing: Routing;


const debug = {
    "begin": "2019-07-15T00:00:00+03:00",
    "end": "2019-07-22T00:00:00+03:00",
    "resultRoomType": {
        "id": "5703c37674eb53676e8b458b",
        "name": "Стандартные плюс 4-х местные",
        "categoryName": "Стандартный плюс",
        "hotelName": "Пансионат Азовский",
        "categoryId": "5703b8a874eb53d26c8b4572",
        "priority": 10,
        "images": [
            {
                "isMain": true,
                "src": "http://localhost:9090/upload/roomTypes/1490351147197.jpeg",
                "thumb": "http://localhost:9090/media/cache/thumb_275x210/upload/roomTypes/1490351147197.jpeg"
            },
            {
                "isMain": false,
                "src": "http://localhost:9090/upload/roomTypes/1521811948951.jpeg",
                "thumb": "http://localhost:9090/media/cache/thumb_275x210/upload/roomTypes/1521811948951.jpeg"
            },
            {
                "isMain": false,
                "src": "http://localhost:9090/upload/roomTypes/152181196866.jpeg",
                "thumb": "http://localhost:9090/media/cache/thumb_275x210/upload/roomTypes/152181196866.jpeg"
            },
            {
                "isMain": false,
                "src": "http://localhost:9090/upload/roomTypes/1521811991307.jpeg",
                "thumb": "http://localhost:9090/media/cache/thumb_275x210/upload/roomTypes/1521811991307.jpeg"
            }
        ]
    },
    "resultTariff": {
        "id": "5ba25291cd5722117d61bdad",
        "name": "СКИДКА 32% (50%)",
        "fullName": "Раннее бронирование 2019 в рассрочку  (32% скидка)"
    },
    "resultConditions": {
        "id": "5cb9a69155cc8a000f29efd3",
        "begin": "2019-07-15T00:00:00+03:00",
        "end": "2019-07-22T00:00:00+03:00",
        "adults": 1,
        "children": 1,
        "childrenAges": [
            3
        ],
        "searchHash": "az_5cb9a6918d32e3.49919074"
    },
    "prices": [
        {
            "searchAdults": 1,
            "searchChildren": 1,
            "total": 46572,
            "dayPrices": [
                {
                    "date": "2019-07-15T00:00:00+03:00",
                    "tariff": {
                        "id": "5ba25291cd5722117d61bdad",
                        "name": "СКИДКА 32% (50%)",
                        "fullName": "Раннее бронирование 2019 в рассрочку  (32% скидка)"
                    },
                    "price": 6653.12,
                    "adults": 1,
                    "children": 1,
                    "infants": 0
                },
                {
                    "date": "2019-07-16T00:00:00+03:00",
                    "tariff": {
                        "id": "5ba25291cd5722117d61bdad",
                        "name": "СКИДКА 32% (50%)",
                        "fullName": "Раннее бронирование 2019 в рассрочку  (32% скидка)"
                    },
                    "price": 6653.12,
                    "adults": 1,
                    "children": 1,
                    "infants": 0
                },
                {
                    "date": "2019-07-17T00:00:00+03:00",
                    "tariff": {
                        "id": "5ba25291cd5722117d61bdad",
                        "name": "СКИДКА 32% (50%)",
                        "fullName": "Раннее бронирование 2019 в рассрочку  (32% скидка)"
                    },
                    "price": 6653.12,
                    "adults": 1,
                    "children": 1,
                    "infants": 0
                },
                {
                    "date": "2019-07-18T00:00:00+03:00",
                    "tariff": {
                        "id": "5ba25291cd5722117d61bdad",
                        "name": "СКИДКА 32% (50%)",
                        "fullName": "Раннее бронирование 2019 в рассрочку  (32% скидка)"
                    },
                    "price": 6653.12,
                    "adults": 1,
                    "children": 1,
                    "infants": 0
                },
                {
                    "date": "2019-07-19T00:00:00+03:00",
                    "tariff": {
                        "id": "5ba25291cd5722117d61bdad",
                        "name": "СКИДКА 32% (50%)",
                        "fullName": "Раннее бронирование 2019 в рассрочку  (32% скидка)"
                    },
                    "price": 6653.12,
                    "adults": 1,
                    "children": 1,
                    "infants": 0
                },
                {
                    "date": "2019-07-20T00:00:00+03:00",
                    "tariff": {
                        "id": "5ba25291cd5722117d61bdad",
                        "name": "СКИДКА 32% (50%)",
                        "fullName": "Раннее бронирование 2019 в рассрочку  (32% скидка)"
                    },
                    "price": 6653.12,
                    "adults": 1,
                    "children": 1,
                    "infants": 0
                },
                {
                    "date": "2019-07-21T00:00:00+03:00",
                    "tariff": {
                        "id": "5ba25291cd5722117d61bdad",
                        "name": "СКИДКА 32% (50%)",
                        "fullName": "Раннее бронирование 2019 в рассрочку  (32% скидка)"
                    },
                    "price": 6653.12,
                    "adults": 1,
                    "children": 1,
                    "infants": 0
                }
            ],
            "discount": 32
        }
    ],
    "minRoomsCount": 2,
    "accommodationRooms": [],
    "virtualRoom": {
        "id": "5703c51474eb53f76e8b4576",
        "name": "29/2/33"
    },
    "status": "ok",
    "error": "",
    "id": "results_id5cb9a693a969c9.84911164",
    "cacheItemId": ""
};


const state = {
    currentOrder: debug,
    status: 'new',
    orderNum: null,
    orderResult : {},
};

const mutations = {
    selectOrder(state, payload) {
        state.currentOrder = payload;
    },
    setInProcess(state) {
        state.status = 'process';
    },
    setSuccess(state) {
        state.status = 'success';
    },
    setError(state) {
        state.status = 'error';
    },
    setResult(satate, payload) {
        state.orderResult = payload;
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


const routes = {
    online: 'az_create_order',
    reserve: 'az_create_reservation'
};

const actions = {
    async createOrder({commit, getters}, {personalData, type}) {
        commit('setInProcess');
        const routeName = routes[type];
        let orderData = getters.getOrderData;
        let data = {...orderData, ...personalData};
        try {
            let url = Routing.generate(routeName, {}, true);
            const response = await request(url, data);
            if (!response.ok) {
                const error = await response.json();
                // noinspection ExceptionCaughtLocallyJS
                throw new Error(error['error']);
            } else {
                let data = await response.json();
                if (data.status === 'error') {
                    throw new Error(data.message);
                }
                commit('setSuccess');
                commit('setResult', data)
            }
        } catch (err) {
            commit('setError');
            console.log(err.message);
        }

    }
};


const getters = {
    getOrderData: (state) => {
        const order = state.currentOrder;
        if (!Object.keys(order).length) {
            return {};
        }
        return {
            begin: moment(order.begin).format('DD.MM.YYYY'),
            end: moment(order.end).format('DD.MM.YYYY'),
            adults: order.resultConditions.adults,
            children: order.resultConditions.children,
            childrenAges: order.resultConditions.chilrenAges,
            roomType: order.resultRoomType.id,
            tariff: order.resultTariff.id,
            /*savedQueryId: order.resultConditions.id,*/
            paymentType: 'hundred'

        };
    }
};


export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
}