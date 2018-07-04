class Writer {

    private data:object = {};

    private rootApp;

    constructor() {
        this.init();
    }

    private init() {
        Vue.component('price', {
            props: ['combination', 'price'],
            template: '<span><b>{{combination}} - {{price.total}} - th {{test()}}</b></span>',
            methods: {
                test: function () {
                    return this.combination.split('').reverse().join('');
                }
            }
        });
        Vue.component('prices', {
            props: ['prices'],
            template: '<span><price v-for="(price, combination) in prices" :key="combination" :combination="combination" :price="price"></price></span>'
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
            template: '<ul>{{roomType.name}}: {{roomType.hotelName}}<search-result v-for="(result, key) in searchResults" :key="key" :result="result"></search-result></ul>',
        });
        this.rootApp = new Vue({
            el: '#vue_results',
            template: '<span><room-type v-for="(data, key) in rawData" :roomType="data.roomType" :searchResults="data.results" :key="key" ></room-type></span>',
            data: {rawData: this.data}
        });
    }

    public showStartSearch(): void {
        console.log('Search started');
        this.data = {};
        this.rootApp.rawData = this.data;
    }

    public showStopSearch(): void {
        console.log('Search stopped');
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