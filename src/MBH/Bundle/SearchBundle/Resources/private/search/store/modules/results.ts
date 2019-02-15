import Vue from 'vue';


const state = {
    successes:{} /*{
                "5703c7eb74eb53976f8b4569": {
                    "roomType": {
                        "id": "5703c7eb74eb53976f8b4569",
                        "name": "Комфорт двухкомнатный",
                        "categoryName": "Комфорт двухкомнатный",
                        "hotelName": "Пансионат Азовский",
                        "categoryId": "5704bf2074eb533a108b456c"
                    },
                    "results": {
                        "13.05.2019_20.05.2019": [
                            {
                                "begin": "2019-05-13T00:00:00+03:00",
                                "end": "2019-05-20T00:00:00+03:00",
                                "resultRoomType": {
                                    "id": "5703c7eb74eb53976f8b4569",
                                    "name": "Комфорт двухкомнатный",
                                    "categoryName": "Комфорт двухкомнатный",
                                    "hotelName": "Пансионат Азовский",
                                    "categoryId": "5704bf2074eb533a108b456c"
                                },
                                "resultTariff": {
                                    "id": "56fbd22274eb5383728b45ad",
                                    "name": "Основной тариф"
                                },
                                "resultConditions": {
                                    "id": "5c62a25932e88e0d2f40b350",
                                    "begin": "2019-05-13T00:00:00+03:00",
                                    "end": "2019-05-20T00:00:00+03:00",
                                    "adults": 1,
                                    "children": 1,
                                    "childrenAges": [
                                        3
                                    ],
                                    "searchHash": "az_5c62a259e70921.47006444"
                                },
                                "prices": [
                                    {
                                        "searchAdults": 1,
                                        "searchChildren": 1,
                                        "total": 70994,
                                        "dayPrices": [
                                            {
                                                "date": "2019-05-13T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "56fbd22274eb5383728b45ad",
                                                    "name": "Основной тариф"
                                                },
                                                "price": 10142,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-14T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "56fbd22274eb5383728b45ad",
                                                    "name": "Основной тариф"
                                                },
                                                "price": 10142,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-15T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "56fbd22274eb5383728b45ad",
                                                    "name": "Основной тариф"
                                                },
                                                "price": 10142,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-16T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "56fbd22274eb5383728b45ad",
                                                    "name": "Основной тариф"
                                                },
                                                "price": 10142,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-17T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "56fbd22274eb5383728b45ad",
                                                    "name": "Основной тариф"
                                                },
                                                "price": 10142,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-18T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "56fbd22274eb5383728b45ad",
                                                    "name": "Основной тариф"
                                                },
                                                "price": 10142,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-19T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "56fbd22274eb5383728b45ad",
                                                    "name": "Основной тариф"
                                                },
                                                "price": 10142,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            }
                                        ]
                                    }
                                ],
                                "minRoomsCount": 5,
                                "accommodationRooms": [],
                                "virtualRoom": {
                                    "id": "5703c82574eb539d6f8b456a",
                                    "name": "24/1/2"
                                },
                                "status": "ok",
                                "error": "",
                                "id": "results_id5c62a25a4d77d7.99430370",
                                "cacheItemId": ""
                            },
                            {
                                "begin": "2019-05-13T00:00:00+03:00",
                                "end": "2019-05-20T00:00:00+03:00",
                                "resultRoomType": {
                                    "id": "5703c7eb74eb53976f8b4569",
                                    "name": "Комфорт двухкомнатный",
                                    "categoryName": "Комфорт двухкомнатный",
                                    "hotelName": "Пансионат Азовский",
                                    "categoryId": "5704bf2074eb533a108b456c"
                                },
                                "resultTariff": {
                                    "id": "5ba24e16cd57226c32174742",
                                    "name": "СКИДКА 35% (100%)"
                                },
                                "resultConditions": {
                                    "id": "5c62a25932e88e0d2f40b350",
                                    "begin": "2019-05-13T00:00:00+03:00",
                                    "end": "2019-05-20T00:00:00+03:00",
                                    "adults": 1,
                                    "children": 1,
                                    "childrenAges": [
                                        3
                                    ],
                                    "searchHash": "az_5c62a259e70921.47006444"
                                },
                                "prices": [
                                    {
                                        "searchAdults": 1,
                                        "searchChildren": 1,
                                        "total": 46146,
                                        "dayPrices": [
                                            {
                                                "date": "2019-05-13T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba24e16cd57226c32174742",
                                                    "name": "СКИДКА 35% (100%)"
                                                },
                                                "price": 6592.3,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-14T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba24e16cd57226c32174742",
                                                    "name": "СКИДКА 35% (100%)"
                                                },
                                                "price": 6592.3,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-15T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba24e16cd57226c32174742",
                                                    "name": "СКИДКА 35% (100%)"
                                                },
                                                "price": 6592.3,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-16T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba24e16cd57226c32174742",
                                                    "name": "СКИДКА 35% (100%)"
                                                },
                                                "price": 6592.3,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-17T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba24e16cd57226c32174742",
                                                    "name": "СКИДКА 35% (100%)"
                                                },
                                                "price": 6592.3,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-18T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba24e16cd57226c32174742",
                                                    "name": "СКИДКА 35% (100%)"
                                                },
                                                "price": 6592.3,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-19T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba24e16cd57226c32174742",
                                                    "name": "СКИДКА 35% (100%)"
                                                },
                                                "price": 6592.3,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            }
                                        ]
                                    }
                                ],
                                "minRoomsCount": 3,
                                "accommodationRooms": [],
                                "virtualRoom": {
                                    "id": "5703c82574eb539d6f8b456a",
                                    "name": "24/1/2"
                                },
                                "status": "ok",
                                "error": "",
                                "id": "results_id5c62a25a585b47.76115790",
                                "cacheItemId": ""
                            },
                            {
                                "begin": "2019-05-13T00:00:00+03:00",
                                "end": "2019-05-20T00:00:00+03:00",
                                "resultRoomType": {
                                    "id": "5703c7eb74eb53976f8b4569",
                                    "name": "Комфорт двухкомнатный",
                                    "categoryName": "Комфорт двухкомнатный",
                                    "hotelName": "Пансионат Азовский",
                                    "categoryId": "5704bf2074eb533a108b456c"
                                },
                                "resultTariff": {
                                    "id": "5ba25291cd5722117d61bdad",
                                    "name": "СКИДКА 32% (50%)"
                                },
                                "resultConditions": {
                                    "id": "5c62a25932e88e0d2f40b350",
                                    "begin": "2019-05-13T00:00:00+03:00",
                                    "end": "2019-05-20T00:00:00+03:00",
                                    "adults": 1,
                                    "children": 1,
                                    "childrenAges": [
                                        3
                                    ],
                                    "searchHash": "az_5c62a259e70921.47006444"
                                },
                                "prices": [
                                    {
                                        "searchAdults": 1,
                                        "searchChildren": 1,
                                        "total": 48276,
                                        "dayPrices": [
                                            {
                                                "date": "2019-05-13T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25291cd5722117d61bdad",
                                                    "name": "СКИДКА 32% (50%)"
                                                },
                                                "price": 6896.5599999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-14T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25291cd5722117d61bdad",
                                                    "name": "СКИДКА 32% (50%)"
                                                },
                                                "price": 6896.5599999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-15T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25291cd5722117d61bdad",
                                                    "name": "СКИДКА 32% (50%)"
                                                },
                                                "price": 6896.5599999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-16T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25291cd5722117d61bdad",
                                                    "name": "СКИДКА 32% (50%)"
                                                },
                                                "price": 6896.5599999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-17T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25291cd5722117d61bdad",
                                                    "name": "СКИДКА 32% (50%)"
                                                },
                                                "price": 6896.5599999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-18T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25291cd5722117d61bdad",
                                                    "name": "СКИДКА 32% (50%)"
                                                },
                                                "price": 6896.5599999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-19T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25291cd5722117d61bdad",
                                                    "name": "СКИДКА 32% (50%)"
                                                },
                                                "price": 6896.5599999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            }
                                        ]
                                    }
                                ],
                                "minRoomsCount": 3,
                                "accommodationRooms": [],
                                "virtualRoom": {
                                    "id": "5703c82574eb539d6f8b456a",
                                    "name": "24/1/2"
                                },
                                "status": "ok",
                                "error": "",
                                "id": "results_id5c62a25a5c8ea0.57547732",
                                "cacheItemId": ""
                            },
                            {
                                "begin": "2019-05-13T00:00:00+03:00",
                                "end": "2019-05-20T00:00:00+03:00",
                                "resultRoomType": {
                                    "id": "5703c7eb74eb53976f8b4569",
                                    "name": "Комфорт двухкомнатный",
                                    "categoryName": "Комфорт двухкомнатный",
                                    "hotelName": "Пансионат Азовский",
                                    "categoryId": "5704bf2074eb533a108b456c"
                                },
                                "resultTariff": {
                                    "id": "5ba25508cd57225011774adc",
                                    "name": "СКИДКА 35+5% (100%)"
                                },
                                "resultConditions": {
                                    "id": "5c62a25932e88e0d2f40b350",
                                    "begin": "2019-05-13T00:00:00+03:00",
                                    "end": "2019-05-20T00:00:00+03:00",
                                    "adults": 1,
                                    "children": 1,
                                    "childrenAges": [
                                        3
                                    ],
                                    "searchHash": "az_5c62a259e70921.47006444"
                                },
                                "prices": [
                                    {
                                        "searchAdults": 1,
                                        "searchChildren": 1,
                                        "total": 43839,
                                        "dayPrices": [
                                            {
                                                "date": "2019-05-13T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25508cd57225011774adc",
                                                    "name": "СКИДКА 35+5% (100%)"
                                                },
                                                "price": 6262.6849999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-14T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25508cd57225011774adc",
                                                    "name": "СКИДКА 35+5% (100%)"
                                                },
                                                "price": 6262.6849999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-15T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25508cd57225011774adc",
                                                    "name": "СКИДКА 35+5% (100%)"
                                                },
                                                "price": 6262.6849999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-16T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25508cd57225011774adc",
                                                    "name": "СКИДКА 35+5% (100%)"
                                                },
                                                "price": 6262.6849999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-17T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25508cd57225011774adc",
                                                    "name": "СКИДКА 35+5% (100%)"
                                                },
                                                "price": 6262.6849999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-18T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25508cd57225011774adc",
                                                    "name": "СКИДКА 35+5% (100%)"
                                                },
                                                "price": 6262.6849999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-19T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25508cd57225011774adc",
                                                    "name": "СКИДКА 35+5% (100%)"
                                                },
                                                "price": 6262.6849999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            }
                                        ]
                                    }
                                ],
                                "minRoomsCount": 3,
                                "accommodationRooms": [],
                                "virtualRoom": {
                                    "id": "5703c82574eb539d6f8b456a",
                                    "name": "24/1/2"
                                },
                                "status": "ok",
                                "error": "",
                                "id": "results_id5c62a25a60c624.51915671",
                                "cacheItemId": ""
                            },
                            {
                                "begin": "2019-05-13T00:00:00+03:00",
                                "end": "2019-05-20T00:00:00+03:00",
                                "resultRoomType": {
                                    "id": "5703c7eb74eb53976f8b4569",
                                    "name": "Комфорт двухкомнатный",
                                    "categoryName": "Комфорт двухкомнатный",
                                    "hotelName": "Пансионат Азовский",
                                    "categoryId": "5704bf2074eb533a108b456c"
                                },
                                "resultTariff": {
                                    "id": "5ba259a8cd5722117d61beb0",
                                    "name": "СКИДКА 32+5% (50%)"
                                },
                                "resultConditions": {
                                    "id": "5c62a25932e88e0d2f40b350",
                                    "begin": "2019-05-13T00:00:00+03:00",
                                    "end": "2019-05-20T00:00:00+03:00",
                                    "adults": 1,
                                    "children": 1,
                                    "childrenAges": [
                                        3
                                    ],
                                    "searchHash": "az_5c62a259e70921.47006444"
                                },
                                "prices": [
                                    {
                                        "searchAdults": 1,
                                        "searchChildren": 1,
                                        "total": 45862,
                                        "dayPrices": [
                                            {
                                                "date": "2019-05-13T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba259a8cd5722117d61beb0",
                                                    "name": "СКИДКА 32+5% (50%)"
                                                },
                                                "price": 6551.732,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-14T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba259a8cd5722117d61beb0",
                                                    "name": "СКИДКА 32+5% (50%)"
                                                },
                                                "price": 6551.732,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-15T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba259a8cd5722117d61beb0",
                                                    "name": "СКИДКА 32+5% (50%)"
                                                },
                                                "price": 6551.732,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-16T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba259a8cd5722117d61beb0",
                                                    "name": "СКИДКА 32+5% (50%)"
                                                },
                                                "price": 6551.732,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-17T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba259a8cd5722117d61beb0",
                                                    "name": "СКИДКА 32+5% (50%)"
                                                },
                                                "price": 6551.732,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-18T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba259a8cd5722117d61beb0",
                                                    "name": "СКИДКА 32+5% (50%)"
                                                },
                                                "price": 6551.732,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-19T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba259a8cd5722117d61beb0",
                                                    "name": "СКИДКА 32+5% (50%)"
                                                },
                                                "price": 6551.732,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            }
                                        ]
                                    }
                                ],
                                "minRoomsCount": 3,
                                "accommodationRooms": [],
                                "virtualRoom": {
                                    "id": "5703c82574eb539d6f8b456a",
                                    "name": "24/1/2"
                                },
                                "status": "ok",
                                "error": "",
                                "id": "results_id5c62a25a6504f2.72658511",
                                "cacheItemId": ""
                            }
                        ],
                        "14.05.2019_20.05.2019": [
                            {
                                "begin": "2019-05-14T00:00:00+03:00",
                                "end": "2019-05-20T00:00:00+03:00",
                                "resultRoomType": {
                                    "id": "5703c7eb74eb53976f8b4569",
                                    "name": "Комфорт двухкомнатный",
                                    "categoryName": "Комфорт двухкомнатный",
                                    "hotelName": "Пансионат Азовский",
                                    "categoryId": "5704bf2074eb533a108b456c"
                                },
                                "resultTariff": {
                                    "id": "56fbd22274eb5383728b45ad",
                                    "name": "Основной тариф"
                                },
                                "resultConditions": {
                                    "id": "5c62a25932e88e0d2f40b350",
                                    "begin": "2019-05-14T00:00:00+03:00",
                                    "end": "2019-05-20T00:00:00+03:00",
                                    "adults": 1,
                                    "children": 1,
                                    "childrenAges": [
                                        3
                                    ],
                                    "searchHash": "az_5c62a259e70921.47006444"
                                },
                                "prices": [
                                    {
                                        "searchAdults": 1,
                                        "searchChildren": 1,
                                        "total": 70994,
                                        "dayPrices": [
                                            {
                                                "date": "2019-05-14T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "56fbd22274eb5383728b45ad",
                                                    "name": "Основной тариф"
                                                },
                                                "price": 10142,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-14T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "56fbd22274eb5383728b45ad",
                                                    "name": "Основной тариф"
                                                },
                                                "price": 10142,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-15T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "56fbd22274eb5383728b45ad",
                                                    "name": "Основной тариф"
                                                },
                                                "price": 10142,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-16T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "56fbd22274eb5383728b45ad",
                                                    "name": "Основной тариф"
                                                },
                                                "price": 10142,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-17T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "56fbd22274eb5383728b45ad",
                                                    "name": "Основной тариф"
                                                },
                                                "price": 10142,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-18T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "56fbd22274eb5383728b45ad",
                                                    "name": "Основной тариф"
                                                },
                                                "price": 10142,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-19T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "56fbd22274eb5383728b45ad",
                                                    "name": "Основной тариф"
                                                },
                                                "price": 10142,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            }
                                        ]
                                    }
                                ],
                                "minRoomsCount": 3,
                                "accommodationRooms": [],
                                "virtualRoom": {
                                    "id": "5703c82574eb539d6f8b456a",
                                    "name": "24/1/2"
                                },
                                "status": "ok",
                                "error": "",
                                "id": "results_id5c62a25a4d77d7.99430370",
                                "cacheItemId": ""
                            },
                            {
                                "begin": "2019-05-14T00:00:00+03:00",
                                "end": "2019-05-20T00:00:00+03:00",
                                "resultRoomType": {
                                    "id": "5703c7eb74eb53976f8b4569",
                                    "name": "Комфорт двухкомнатный",
                                    "categoryName": "Комфорт двухкомнатный",
                                    "hotelName": "Пансионат Азовский",
                                    "categoryId": "5704bf2074eb533a108b456c"
                                },
                                "resultTariff": {
                                    "id": "5ba24e16cd57226c32174742",
                                    "name": "СКИДКА 35% (100%)"
                                },
                                "resultConditions": {
                                    "id": "5c62a25932e88e0d2f40b350",
                                    "begin": "2019-05-14T00:00:00+03:00",
                                    "end": "2019-05-20T00:00:00+03:00",
                                    "adults": 1,
                                    "children": 1,
                                    "childrenAges": [
                                        3
                                    ],
                                    "searchHash": "az_5c62a259e70921.47006444"
                                },
                                "prices": [
                                    {
                                        "searchAdults": 1,
                                        "searchChildren": 1,
                                        "total": 46146,
                                        "dayPrices": [
                                            {
                                                "date": "2019-05-14T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba24e16cd57226c32174742",
                                                    "name": "СКИДКА 35% (100%)"
                                                },
                                                "price": 6592.3,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-14T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba24e16cd57226c32174742",
                                                    "name": "СКИДКА 35% (100%)"
                                                },
                                                "price": 6592.3,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-15T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba24e16cd57226c32174742",
                                                    "name": "СКИДКА 35% (100%)"
                                                },
                                                "price": 6592.3,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-16T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba24e16cd57226c32174742",
                                                    "name": "СКИДКА 35% (100%)"
                                                },
                                                "price": 6592.3,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-17T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba24e16cd57226c32174742",
                                                    "name": "СКИДКА 35% (100%)"
                                                },
                                                "price": 6592.3,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-18T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba24e16cd57226c32174742",
                                                    "name": "СКИДКА 35% (100%)"
                                                },
                                                "price": 6592.3,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-19T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba24e16cd57226c32174742",
                                                    "name": "СКИДКА 35% (100%)"
                                                },
                                                "price": 6592.3,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            }
                                        ]
                                    }
                                ],
                                "minRoomsCount": 3,
                                "accommodationRooms": [],
                                "virtualRoom": {
                                    "id": "5703c82574eb539d6f8b456a",
                                    "name": "24/1/2"
                                },
                                "status": "ok",
                                "error": "",
                                "id": "results_id5c62a25a585b47.76115790",
                                "cacheItemId": ""
                            },
                            {
                                "begin": "2019-05-14T00:00:00+03:00",
                                "end": "2019-05-20T00:00:00+03:00",
                                "resultRoomType": {
                                    "id": "5703c7eb74eb53976f8b4569",
                                    "name": "Комфорт двухкомнатный",
                                    "categoryName": "Комфорт двухкомнатный",
                                    "hotelName": "Пансионат Азовский",
                                    "categoryId": "5704bf2074eb533a108b456c"
                                },
                                "resultTariff": {
                                    "id": "5ba25291cd5722117d61bdad",
                                    "name": "СКИДКА 32% (50%)"
                                },
                                "resultConditions": {
                                    "id": "5c62a25932e88e0d2f40b350",
                                    "begin": "2019-05-14T00:00:00+03:00",
                                    "end": "2019-05-20T00:00:00+03:00",
                                    "adults": 1,
                                    "children": 1,
                                    "childrenAges": [
                                        3
                                    ],
                                    "searchHash": "az_5c62a259e70921.47006444"
                                },
                                "prices": [
                                    {
                                        "searchAdults": 1,
                                        "searchChildren": 1,
                                        "total": 33333,
                                        "dayPrices": [
                                            {
                                                "date": "2019-05-14T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25291cd5722117d61bdad",
                                                    "name": "СКИДКА 32% (50%)"
                                                },
                                                "price": 6896.5599999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-14T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25291cd5722117d61bdad",
                                                    "name": "СКИДКА 32% (50%)"
                                                },
                                                "price": 6896.5599999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-15T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25291cd5722117d61bdad",
                                                    "name": "СКИДКА 32% (50%)"
                                                },
                                                "price": 6896.5599999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-16T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25291cd5722117d61bdad",
                                                    "name": "СКИДКА 32% (50%)"
                                                },
                                                "price": 6896.5599999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-17T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25291cd5722117d61bdad",
                                                    "name": "СКИДКА 32% (50%)"
                                                },
                                                "price": 6896.5599999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-18T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25291cd5722117d61bdad",
                                                    "name": "СКИДКА 32% (50%)"
                                                },
                                                "price": 6896.5599999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-19T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25291cd5722117d61bdad",
                                                    "name": "СКИДКА 32% (50%)"
                                                },
                                                "price": 6896.5599999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            }
                                        ]
                                    }
                                ],
                                "minRoomsCount": 3,
                                "accommodationRooms": [],
                                "virtualRoom": {
                                    "id": "5703c82574eb539d6f8b456a",
                                    "name": "24/1/2"
                                },
                                "status": "ok",
                                "error": "",
                                "id": "results_id5c62a25a5c8ea0.57547732",
                                "cacheItemId": ""
                            },
                            {
                                "begin": "2019-05-14T00:00:00+03:00",
                                "end": "2019-05-20T00:00:00+03:00",
                                "resultRoomType": {
                                    "id": "5703c7eb74eb53976f8b4569",
                                    "name": "Комфорт двухкомнатный",
                                    "categoryName": "Комфорт двухкомнатный",
                                    "hotelName": "Пансионат Азовский",
                                    "categoryId": "5704bf2074eb533a108b456c"
                                },
                                "resultTariff": {
                                    "id": "5ba25508cd57225011774adc",
                                    "name": "СКИДКА 35+5% (100%)"
                                },
                                "resultConditions": {
                                    "id": "5c62a25932e88e0d2f40b350",
                                    "begin": "2019-05-14T00:00:00+03:00",
                                    "end": "2019-05-20T00:00:00+03:00",
                                    "adults": 1,
                                    "children": 1,
                                    "childrenAges": [
                                        3
                                    ],
                                    "searchHash": "az_5c62a259e70921.47006444"
                                },
                                "prices": [
                                    {
                                        "searchAdults": 1,
                                        "searchChildren": 1,
                                        "total": 43839,
                                        "dayPrices": [
                                            {
                                                "date": "2019-05-14T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25508cd57225011774adc",
                                                    "name": "СКИДКА 35+5% (100%)"
                                                },
                                                "price": 6262.6849999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-14T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25508cd57225011774adc",
                                                    "name": "СКИДКА 35+5% (100%)"
                                                },
                                                "price": 6262.6849999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-15T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25508cd57225011774adc",
                                                    "name": "СКИДКА 35+5% (100%)"
                                                },
                                                "price": 6262.6849999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-16T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25508cd57225011774adc",
                                                    "name": "СКИДКА 35+5% (100%)"
                                                },
                                                "price": 6262.6849999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-17T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25508cd57225011774adc",
                                                    "name": "СКИДКА 35+5% (100%)"
                                                },
                                                "price": 6262.6849999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-18T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25508cd57225011774adc",
                                                    "name": "СКИДКА 35+5% (100%)"
                                                },
                                                "price": 6262.6849999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-19T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba25508cd57225011774adc",
                                                    "name": "СКИДКА 35+5% (100%)"
                                                },
                                                "price": 6262.6849999999995,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            }
                                        ]
                                    }
                                ],
                                "minRoomsCount": 3,
                                "accommodationRooms": [],
                                "virtualRoom": {
                                    "id": "5703c82574eb539d6f8b456a",
                                    "name": "24/1/2"
                                },
                                "status": "ok",
                                "error": "",
                                "id": "results_id5c62a25a60c624.51915671",
                                "cacheItemId": ""
                            },
                            {
                                "begin": "2019-05-14T00:00:00+03:00",
                                "end": "2019-05-20T00:00:00+03:00",
                                "resultRoomType": {
                                    "id": "5703c7eb74eb53976f8b4569",
                                    "name": "Комфорт двухкомнатный",
                                    "categoryName": "Комфорт двухкомнатный",
                                    "hotelName": "Пансионат Азовский",
                                    "categoryId": "5704bf2074eb533a108b456c"
                                },
                                "resultTariff": {
                                    "id": "5ba259a8cd5722117d61beb0",
                                    "name": "СКИДКА 32+5% (50%)"
                                },
                                "resultConditions": {
                                    "id": "5c62a25932e88e0d2f40b350",
                                    "begin": "2019-05-14T00:00:00+03:00",
                                    "end": "2019-05-20T00:00:00+03:00",
                                    "adults": 1,
                                    "children": 1,
                                    "childrenAges": [
                                        3
                                    ],
                                    "searchHash": "az_5c62a259e70921.47006444"
                                },
                                "prices": [
                                    {
                                        "searchAdults": 1,
                                        "searchChildren": 1,
                                        "total": 45862,
                                        "dayPrices": [
                                            {
                                                "date": "2019-05-14T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba259a8cd5722117d61beb0",
                                                    "name": "СКИДКА 32+5% (50%)"
                                                },
                                                "price": 6551.732,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-14T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba259a8cd5722117d61beb0",
                                                    "name": "СКИДКА 32+5% (50%)"
                                                },
                                                "price": 6551.732,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-15T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba259a8cd5722117d61beb0",
                                                    "name": "СКИДКА 32+5% (50%)"
                                                },
                                                "price": 6551.732,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-16T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba259a8cd5722117d61beb0",
                                                    "name": "СКИДКА 32+5% (50%)"
                                                },
                                                "price": 6551.732,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-17T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba259a8cd5722117d61beb0",
                                                    "name": "СКИДКА 32+5% (50%)"
                                                },
                                                "price": 6551.732,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-18T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba259a8cd5722117d61beb0",
                                                    "name": "СКИДКА 32+5% (50%)"
                                                },
                                                "price": 6551.732,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            },
                                            {
                                                "date": "2019-05-19T00:00:00+03:00",
                                                "tariff": {
                                                    "id": "5ba259a8cd5722117d61beb0",
                                                    "name": "СКИДКА 32+5% (50%)"
                                                },
                                                "price": 6551.732,
                                                "adults": 1,
                                                "children": 1,
                                                "infants": 0
                                            }
                                        ]
                                    }
                                ],
                                "minRoomsCount": 3,
                                "accommodationRooms": [],
                                "virtualRoom": {
                                    "id": "5703c82574eb539d6f8b456a",
                                    "name": "24/1/2"
                                },
                                "status": "ok",
                                "error": "",
                                "id": "results_id5c62a25a6504f2.72658511",
                                "cacheItemId": ""
                            }
                        ]
                    }
                }
    }*/,
    errors: {},
    amount: 0,
    specialsHtml: ''
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


    },
    clearResults(state) {
        state.successes = {};
        state.errors = {};
        state.amount = 0;
        state.specialsHtml = '';
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
    }
    ,
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
};

export default {
    namespaced: true,
    state,
    mutations,
    getters
}