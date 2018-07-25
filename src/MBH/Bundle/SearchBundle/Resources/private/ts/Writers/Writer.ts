
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
            template: '<td>{{tariff.name}}</td>'
        });

        Vue.component('package-link', {
            props: ['link', 'roomsCount'],
            template: `<td class="text-center">
                            <a :href="link" class="btn btn-success btn-xs package-search-book" :title="'Бронировать номер. Всего номеров: ' + roomsCount" >
                            <span class="package-search-book-reservation-text">Бронировать</span>
                            </a>
                        </td>`
        });
        Vue.component('count', {
            props: ['count'],
            template: `<td>
                        <select class="form-control quantity-select input-xxs">
                            <option :value="count">{{ count }}</option>
                        </select>
                       </td>`
        });
        Vue.component('prices', {
            props: ['prices', 'defaultPriceIndex'],
            template: `<td class="text-center">
                     <select v-model="selected" @change="$emit('price-index-update', selected)" class="form-control plain-html input-sm search-tourists-select">
                            <option v-for="(price, key) in prices" :value="key"><span>{{price.adults}} взр.</span><span v-if="price.children">+{{price.children}} реб.</span></option>
                        </select>
                    </td>`
            ,
            data: function () {
                return {
                    selected: this.defaultPriceIndex
                }
            }
        });
        Vue.component('total-price', {
            props: ['prices'],
            template: `<td class="text-right"><ul class="package-search-prices"></ul></td>`
        });
        Vue.component('result', {
            props: ['result'],
            template: `<tr>
                    <td class="text-center table-icon"><i class="fa fa-paper-plane-o"></i></td>
                    <td>{{result.begin}}-{{result.end}}<br><small>x ночей</small></td>
                    <td is="tariff" :tariff="result.tariff"><br><small>Свободно номеров</small></td>
                    <td is="count" :count="3"></td>
                    <td is="prices" :prices="result.prices" :defaultPriceIndex="currentPriceIndex" @price-index-update="priceIndexUpdate($event)"></td>
                    <td is="total-price" :prices="result.prices[currentPriceIndex]"></td>
                    <td is="package-link" :link="getLink()" :roomsCount="3" data-toggle="tooltip"></td>
            </tr>`,
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
                },
                rounded: function (price: number) {
                    return Number(price).toFixed(1);
                }
            },
            data: function () {
                return {
                    currentPriceIndex: 0
                }
            }

        });
        Vue.component('room-type', {
            props: ['roomType', 'results'],
            template: `<tbody>
                           <tr class="mbh-grid-header1 info"><td colspan="8">{{roomType.name}}: {{roomType.hotelName}}</td></tr>
                           <tr is="result" v-for="(result, key) in sortedResultsByPrice" :key="key" :result="result"></tr>
                       </tbody>
                        `,
            computed: {
                sortedResultsByPrice: function () {
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
        this.rootApp = new Vue({
            el: '#search_results',
            template: `<table v-if="Object.keys(rawData).length !== 0" class="package-search-table table table-striped table-hover table-condensed table-icons table-actions">
                        <thead>
                            <tr>
                                <th class="td-xxs"></th>
                                <th class="td-md">Даты</th>
                                <th>Тариф</th>
                                <th class="td-sm">Количество</th>
                                <th class="td-sm">Гости</th>
                                <th class="td-md">Цена</th>
                                <th class="td-md"></th>
                            </tr>
                        </thead>
                        <tbody is="room-type" v-for="(data, key) in rawData" :roomType="data.roomType" :results="data.results" :key="key"></tbody>
                        </table>
`,
            /*template: '<span><room-type v-for="(data, key) in rawData" :roomType="data.roomType" :searchResults="data.results" :key="key" ></room-type></span>',*/
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