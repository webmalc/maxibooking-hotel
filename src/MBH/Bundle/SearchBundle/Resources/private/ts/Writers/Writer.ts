
class Writer {

    private data: object = {};

    public searchStatus: {[index:string]: boolean} = {state: false};

    private statusVue;

    private rootApp;


    constructor() {
        this.init();
    }

    private init() {
        this.searchVueInit();
        this.showSearchStatusInit();
    }

    private showSearchStatusInit() {
        this.statusVue = new Vue({
            el: '#search-status',
            template: '<span v-if="status.state">Идет поиск...</span><span v-else>поиск не идет.</span>',
            data: {
                status: this.searchStatus
            }
        })
    }

    private searchVueInit(): void {
        Vue.component('price', {
            props: ['combination', 'price'],
            template: '<option>{{combination}} - {{rounded(price.total)}}</option>',
            methods: {
                rounded: function (price: number) {
                    return Number(price).toFixed(1);
                }
            }
        });
        Vue.component('prices', {
            props: ['prices'],
            template: '<span><select v-model="selected"><option is="price" v-for="(price, combination) in prices" :key="combination" :combination="combination" :price="price"></option></select> - {{ selected }}</span>'
        });
        Vue.component('tariff', {
            props: ['tariff'],
            template: '<span>{{tariff.name}}. </span>'
        });
        Vue.component('search-result', {
            props: ['result'],
            template: '<li><span>{{result.begin}}-{{result.end}}.  Тариф - <tariff :tariff="result.tariff"></tariff><prices v-for="(prices, key) in result.prices" :prices="prices" :key="key"></prices></span></li>'

        });
        Vue.component('room-type', {
            props: ['roomType', 'searchResults'],
            template: '<ul>{{roomType.name}}: {{roomType.hotelName}} - <li is="search-result" v-for="(result, key) in priceSorted" :key="key" :result="result"></li></ul>',
            computed: {
                priceSorted: function () {
                    this.searchResults.sort(function (resultA, resultB) {
                        let keyPriceA = Object.keys(resultA.prices[0])[0];
                        let keyPriceB = Object.keys(resultB.prices[0])[0];
                        let priceA = resultA.prices[0][keyPriceA].total;
                        let priceB = resultB.prices[0][keyPriceB].total;
                        if(priceA < priceB) {
                            return -1;
                        }
                        if(priceA > priceB) {
                            return 1;
                        }

                        return 0;
                    });

                    return this.searchResults;
                },

            }
        });
        this.rootApp = new Vue({
            el: '#vue_results',
            template: '<span><room-type v-for="(data, key) in rawData" :roomType="data.roomType" :searchResults="data.results" :key="key" ></room-type></span>',
            data: {rawData: this.data},
        });
    }

    public showStartSearch(): void {
        console.log('Search started');
        this.data = {};
        this.rootApp.rawData = this.data;
        this.searchStatus.state = true;
    }

    public showStopSearch(): void {
        console.log('Search stopped');
        this.searchStatus.state = false;
    }

    public drawResults(data): void {

        for (let newKey in data) {
            if (!this.data.hasOwnProperty(newKey)) {
                let tempData = {};
                tempData[newKey] = data[newKey];
                /**
                 * х.з почему тут именно так.
                 * @url https://ru.vuejs.org/v2/guide/reactivity.html */
                this.data = Object.assign({}, this.data, tempData);
                this.rootApp.rawData = this.data;
            } else {
                this.data[newKey].results = this.data[newKey].results.concat(data[newKey].results);
            }
        }
    }
}