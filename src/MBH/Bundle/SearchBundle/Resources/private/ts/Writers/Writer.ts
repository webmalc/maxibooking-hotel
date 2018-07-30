///<reference path="../../../../../../../../node_modules/@types/accounting/index.d.ts"/>
///<reference path="../../../../../../../../node_modules/@types/bootstrap/index.d.ts"/>

declare let moment: any;

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
            props: ['tariff', 'freeRooms'],
            template: '<td>{{tariff.name}}<br><small><span class="package-search-book-count">Свободно номеров: {{freeRooms}}</span></small></td>'
        });

        Vue.component('package-link', {
            props: ['link', 'roomsCount'],
            template: `<td class="text-center">
                            <a v-if="roomsCount > 0" :href="link" target="_blank" class="btn btn-success btn-xs package-search-book" :title="'Бронировать номер. Всего номеров: ' + roomsCount" >
                            <i class="fa fa-book"></i><span class="package-search-book-reservation-text"> Бронировать</span>
                            </a>
                        </td>`
        });
        Vue.component('count', {
            props: ['count', 'quantity'],
            template: `<td>
                        <select v-model="selected" @change="$emit('quantity', selected)" class="form-control quantity-select input-xxs">
                            <option v-for="value in (1, count)" :value="value">{{ value }}</option>
                        </select>
                       </td>`
            ,
            mounted: function () {
                this.$emit('quantity', this.selected)
            },
            data: function () {
                return {
                    selected: this.quantity
                }
            }
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

        Vue.component('day-price', {
            props: ['dayPrices'],
            template: `<small>
                            <i v-popover class="fa fa-question-circle" data-container="body" data-toggle="popover"
                                data-placement="left" data-html="true"
                                :data-content="detail"
                                ></i>
                        </small>`
            ,
            computed: {
                detail: function  () {
                    let html: string = '';
                    for (let dayPrice of this.dayPrices) {
                        html += `${dayPrice['day']} - ${dayPrice['price']} - <i class='fa fa-sliders'></i> ${dayPrice['tariff']['name']}<br>`;
                    }

                    return `<small>${html}</small>`;
                }
            },
            directives: {
                popover: {
                    inserted: function (el) {
                        $(el).popover();
                    }
                }
            }
        });
        Vue.component('total-price', {
            props: ['price', 'tariffName'],
            template: `<td class="text-right"><ul class="package-search-prices">
                      <li>{{rounded(price.total)}}
                        <small is="day-price"  :dayPrices="price.dayPrices"></small>
                      </li>
                    </ul>
                    <small><i class="fa fa-sliders"></i> {{tariffName}}</small>
                    </td>`,
            methods: {
                rounded: function (price: number) {
                    return accounting.formatMoney(price, "", 2, ",", ".");
                }
            }
        });
        Vue.component('result', {
            props: ['result'],
            template: `<tr :class="{success: isAdditionalDate}">
                    <td class="text-center table-icon"><i class="fa fa-paper-plane-o"></i></td>
                    <td>{{begin}}-{{end}}<br><small>{{night}} ночей</small></td>
                    <td is="tariff" :tariff="result.tariff" :freeRooms="minRooms"></td>
                    <td is="count" :count="minRooms" :quantity="quantity" @quantity="quantityUpdate($event)"></td>
                    <td is="prices" :prices="result.prices" :defaultPriceIndex="currentPriceIndex" @price-index-update="priceIndexUpdate($event)"></td>
                    <td is="total-price" :price="result.prices[currentPriceIndex]" :tariffName="result.tariff.name"></td>
                    <td is="package-link" :link="getLink()" :roomsCount="minRooms" data-toggle="tooltip" @click.native="$emit('booking', quantity)"></td>
            </tr>`,
            computed: {
                begin: function () {
                    let begin = moment(this.result.begin, 'DD.MM.YYYY');

                    return begin.format('DD MMM');
                },
                end: function () {
                    let end = moment(this.result.end, 'DD.MM.YYYY');

                    return end.format('DD MMM');
                },
                night: function () {
                    const begin = moment.utc(this.result.begin, 'DD.MM.YYYY');
                    const end = moment.utc(this.result.end, 'DD.MM.YYYY');

                    return moment.duration(end.diff(begin)).days();
                },
                isAdditionalDate: function () {
                    let conditionBegin = this.result.conditions.begin;
                    let begin = this.result.begin;

                    let conditionEnd = this.result.conditions.end;
                    let end = this.result.end;

                    return (conditionBegin == begin) && (conditionEnd == end);
                },
                minRooms: function ()  {
                    return this.result.minRooms;
                }


            },
            methods:  {
                getLink: function () {
                    const begin: string = this.result.begin;
                    const end: string = this.result.end;
                    const tariff: string = this.result.tariff.id;
                    const roomType: string = this.result.roomType.id;
                    const adults: number = this.result.prices[this.currentPriceIndex].adults;
                    const children: number = this.result.prices[this.currentPriceIndex].children;
                    const childrenAges = this.result.conditions.childrenAges;
                    const order = this.result.conditions.order;
                    const forceBooking = this.result.conditions.forceBooking;
                    return Routing.generate('package_new', {
                        begin: begin,
                        end: end,
                        tariff: tariff,
                        roomType: roomType,
                        adults: adults,
                        children: children,
                        childrenAges: childrenAges,
                        quantity: this.quantity,
                        order: order,
                        forceBooking: forceBooking,
                    });
                },
                priceIndexUpdate: function (index) {
                    this.currentPriceIndex = index;
                },
                quantityUpdate: function (num) {
                    this.quantity = num;
                }
            },
            data: function () {
                return {
                    currentPriceIndex: 0,
                    quantity: 1
                }
            }

        });
        Vue.component('room-type', {
            props: ['roomType', 'results'],
            template: `<tbody>
                           <tr class="mbh-grid-header1 info"><td colspan="8">{{roomType.name}}: {{roomType.hotelName}}</td></tr>
                           <tr @booking="booking($event)" is="result" v-for="(result, key) in sortedResults" :key="key" :result="result"></tr>
                       </tbody>
                        `,
            methods: {
                booking: function  (count) {
                    for(let index in this.results) {
                        this.results[index].minRooms = this.results[index].minRooms - count;
                    }
                }
            },
            computed: {
                sortedResults: function () {
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