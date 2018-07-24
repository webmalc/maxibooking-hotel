
class Writer {

    private data: object = {};

    public searchStatus: {[index:string]: string} = {state: 'new'};

    private statusVue;

    private rootApp;


    constructor() {
        this.init();
    }

    private init() {
        this.searchVueInit();
        this.showSearchStatusInit();
    }

    private showSearchStatusInit(): void {
        this.statusVue = new Vue({
            el: '#package-searcher-results-wrapper',
            template: `<div  v-if="status.state === 'new' " class="bg-gray color-palette alert"> <i class="fa fa-search"> </i> Введите  данные для поиска </div>
                       <div  v-else-if="status.state === 'noResults' " class="alert alert-warning"> <i class="fa fa-exclamation-circle"></i> По вашему запросу ничего не найдено</div>
                       <div  v-else-if="status.state === 'error' " class="alert alert-danger"> <i class="fa fa-exclamation-circle"></i> Произошла ошибка при запросе в базу данных!</div>
                       <div  v-else-if="status.state === 'process' " class="alert alert-warning"> <i class="fa fa-spinner fa-spin"></i> Подождите...</div>`,
            data: {
                status: this.searchStatus
            }
        })
    }

    private searchVueInit(): void {
        Vue.component('tariff', {
            props: ['tariff'],
            template: '<span>{{tariff.name}}. </span>'
        });

        Vue.component('prices', {
            props: ['prices', 'defaultPriceIndex'],
            template: `<span>
                        <select v-model="selected" @change="$emit('price-index-update', selected)">
                            <option v-for="(price, key) in prices" :value="key">{{price.adults}}_{{price.children}} - {{rounded(price.total)}} </option>
                        </select>
                    </span>`,
            methods: {
                rounded: function (price: number) {
                    return Number(price).toFixed(1);
                }
            },
            data: function () {
                return {
                    selected: this.defaultPriceIndex
                }
            }
        });
        Vue.component('package-link', {
            props: ['link'],
            template: '<a :href="link">Тыц на бронь</a>'
        });

        Vue.component('search-result', {
            props: ['result'],
            template: `<li>
            <span>{{result.begin}}-{{result.end}}.  Тариф - 
                <tariff :tariff="result.tariff"></tariff>
                <prices :prices="result.prices" :defaultPriceIndex="currentPriceIndex" @price-index-update="priceIndexUpdate($event)"></prices>
            </span>
                <package-link :link="getLink()"></package-link>
            </li>`,
            methods:  {
                getLink: function () {
                    const begin: string = this.result.begin;
                    const end: string = this.result.end;
                    const tariff: string = this.result.tariff.id;
                    const roomType: string = this.result.roomType.id;
                    const adults: number = this.result.prices[this.currentPriceIndex].adults;
                    const children: number = this.result.prices[this.currentPriceIndex].children;
                    const childrenAges = this.result.conditions.childrenAges;
                    return Routing.generate('package_new', {
                        begin: begin,
                        end: end,
                        tariff: tariff,
                        roomType: roomType,
                        adults: adults,
                        children: children,
                        childrenAges: childrenAges,
                    });
                },
                priceIndexUpdate: function (index) {
                    this.currentPriceIndex = index;
                }
            },
            data: function () {
                return {
                    currentPriceIndex: 0
                }
            }

        });
        Vue.component('results-by-date', {
            props: ['dates', 'results'],
            template: '<li>{{dates}}<ul><li is="search-result" v-for="(result, key) in sortedByPrice" :key="key" :result="result"></li></ul></li>',
            computed: {
                sortedByPrice: function () {
                    this.results.sort(function (resultA, resultB) {
                        if (typeof resultA.prices[0] !== 'object' || typeof resultB.prices[0] !== 'object') {
                            return;
                        }

                        let keyPriceA = Object.keys(resultA.prices)[0];
                        let keyPriceB = Object.keys(resultB.prices)[0];
                        let priceA = resultA.prices[keyPriceA].total;
                        let priceB = resultB.prices[keyPriceB].total;
                        if(priceA < priceB) {
                            return -1;
                        }
                        if(priceA > priceB) {
                            return 1;
                        }

                        return 0;
                    });

                    return this.results;
                },

            }
        });
        Vue.component('room-type', {
            props: ['roomType', 'searchResults'],
            template: '<ul>{{roomType.name}}: {{roomType.hotelName}} - <li is="results-by-date" v-for="(results, key) in searchResults" :key="key" :results="results" :dates="key"></li></ul>',

        });
        this.rootApp = new Vue({
            el: '#search_results',
            template: '<span><room-type v-for="(data, key) in rawData" :roomType="data.roomType" :searchResults="data.results" :key="key" ></room-type></span>',
            data: {rawData: this.data},
        });
    }

    public showStartSearch(): void {
        console.log('Search started');
        this.data = {};
        this.rootApp.rawData = this.data;
        this.searchStatus.state = 'process';
    }

    public showStopSearch(state: string): void {
        console.log('Search stopped');
        this.searchStatus.state = state;
    }

    public drawResults(data): void {
        for (let newKey in data) {
            if (!data.hasOwnProperty(newKey)) {
                continue;
            }
            if (!this.data.hasOwnProperty(newKey)) {
                //  * @url https://ru.vuejs.org/v2/guide/reactivity.html */
                this.rootApp.$set(this.rootApp.rawData, newKey, data[newKey]);
            } else {
                this.data[newKey].results = this.data[newKey].results.concat(data[newKey].results);
            }
        }
    }
}