var Searcher = /** @class */ (function () {
    function Searcher(buttonId, writer, dataReceiver) {
        this.button = $("#" + buttonId);
        this.writer = writer;
        this.searchDataReceiver = dataReceiver;
        this.bindHandlers();
    }
    Searcher.prototype.bindHandlers = function () {
        var _this = this;
        this.button.on('click', function (event) {
            event.preventDefault();
            _this.doSearch();
        });
    };
    Searcher.prototype.onStartSearch = function () {
        this.writer.showStartSearch();
    };
    Searcher.prototype.onStopSearch = function (requestResults) {
        console.log(requestResults);
        var message;
        if (requestResults.status === 'error') {
            message = 'error';
        }
        if (requestResults.status === 'success' && !Object.keys(requestResults.message.results).length) {
            message = 'noResults';
        }
        this.writer.showStopSearch(message);
    };
    Searcher.prototype.drawResults = function (data) {
        var searchResults = data.results;
        this.writer.drawResults(searchResults);
    };
    Searcher.prototype.getSearchConditions = function () {
        return this.searchDataReceiver.getSearchConditionsData();
        //
        // let data: SearchDataType;
        // data = {
        //     begin: '05.09.2018',
        //     end: '19.09.2018',
        //     adults: 2
        // };
        //
        // return data;
    };
    return Searcher;
}());
var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : new P(function (resolve) { resolve(result.value); }).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = y[op[0] & 2 ? "return" : op[0] ? "throw" : "next"]) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [0, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
///<reference path="Searcher.ts"/>
var AsyncSearcher = /** @class */ (function (_super) {
    __extends(AsyncSearcher, _super);
    function AsyncSearcher(buttonId, writer, dataReceiver) {
        var _this = _super.call(this, buttonId, writer, dataReceiver) || this;
        _this.requestThreshold = 25;
        return _this;
    }
    AsyncSearcher.prototype.doSearch = function () {
        return __awaiter(this, void 0, void 0, function () {
            var start_route, ajax, conditionsResults, count, requestResults, error, resultRoute, data, err_1, e_1;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        this.onStartSearch();
                        start_route = Routing.generate('search_start_async');
                        _a.label = 1;
                    case 1:
                        _a.trys.push([1, 10, , 11]);
                        ajax = $.ajax({
                            url: start_route,
                            type: "POST",
                            dataType: "json",
                            data: JSON.stringify(this.getSearchConditions())
                        });
                        return [4 /*yield*/, ajax];
                    case 2:
                        conditionsResults = _a.sent();
                        count = 0;
                        requestResults = void 0;
                        error = false;
                        resultRoute = Routing.generate('search_async_results', { id: conditionsResults.conditionsId, grouping: 'roomType' });
                        _a.label = 3;
                    case 3:
                        _a.trys.push([3, 5, , 6]);
                        requestResults = ajax = $.ajax({
                            url: resultRoute,
                            type: "POST",
                            dataType: "json",
                            data: JSON.stringify([])
                        });
                        return [4 /*yield*/, requestResults];
                    case 4:
                        data = _a.sent();
                        this.drawResults(data);
                        return [3 /*break*/, 6];
                    case 5:
                        err_1 = _a.sent();
                        error = true;
                        this.onStopSearch(requestResults);
                        return [3 /*break*/, 6];
                    case 6:
                        count++;
                        return [4 /*yield*/, new Promise(function (resolve) {
                                setTimeout(function () {
                                    resolve();
                                }, 1000);
                            })];
                    case 7:
                        _a.sent();
                        _a.label = 8;
                    case 8:
                        if (!error && count < this.requestThreshold) return [3 /*break*/, 3];
                        _a.label = 9;
                    case 9:
                        if (!error) {
                            console.log('Stop async receive by threshold.');
                            this.onStopSearch({ status: "error" });
                        }
                        return [3 /*break*/, 11];
                    case 10:
                        e_1 = _a.sent();
                        this.onStopSearch(ajax);
                        return [3 /*break*/, 11];
                    case 11: return [2 /*return*/];
                }
            });
        });
    };
    return AsyncSearcher;
}(Searcher));
var SyncSearcher = /** @class */ (function (_super) {
    __extends(SyncSearcher, _super);
    function SyncSearcher(buttonId, writer, dataReceiver) {
        return _super.call(this, buttonId, writer, dataReceiver) || this;
    }
    SyncSearcher.prototype.doSearch = function () {
        return __awaiter(this, void 0, void 0, function () {
            var ajax, start_route, data, e_2;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        this.onStartSearch();
                        start_route = Routing.generate('search_sync_start_json', { grouping: 'roomType' });
                        _a.label = 1;
                    case 1:
                        _a.trys.push([1, 3, , 4]);
                        ajax = $.ajax({
                            url: start_route,
                            type: "POST",
                            dataType: "json",
                            data: JSON.stringify(this.getSearchConditions())
                        });
                        return [4 /*yield*/, ajax];
                    case 2:
                        data = _a.sent();
                        this.drawResults(data);
                        this.onStopSearch({ status: 'success', message: data });
                        return [3 /*break*/, 4];
                    case 3:
                        e_2 = _a.sent();
                        this.onStopSearch({ status: 'error', message: e_2 });
                        return [3 /*break*/, 4];
                    case 4: return [2 /*return*/];
                }
            });
        });
    };
    return SyncSearcher;
}(Searcher));
var FormDataReceiver = /** @class */ (function () {
    function FormDataReceiver(formName) {
        this.$form = $("form[name=\"" + formName + "\"]");
        this.formName = formName;
        this.$children = $('input#search_conditions_children');
        this.$childrenAgeHolder = $('#search_conditions_childrenAges');
        this.agesTemplate = this.$childrenAgeHolder.data('prototype');
        this.bindHandlers();
    }
    FormDataReceiver.prototype.bindHandlers = function () {
        var _this = this;
        this.$children.on('change', function () {
            _this.updateChildrenAges();
        });
    };
    FormDataReceiver.prototype.updateChildrenAges = function () {
        var currentAgesCount = this.getChildrenAgesIndex();
        var currentChildrenCount = this.getChildrenCount();
        if (currentAgesCount > currentChildrenCount) {
            this.removeAges(currentChildrenCount, currentAgesCount);
        }
        if (currentAgesCount < currentChildrenCount) {
            this.addAges(currentChildrenCount, currentAgesCount);
        }
    };
    FormDataReceiver.prototype.removeAges = function (children, ages) {
        for (var index = ages; index > children; index--) {
            var selector = "select#search_conditions_childrenAges_" + (index - 1);
            var $ageInput = $(selector).parent('div');
            $ageInput.remove();
        }
    };
    FormDataReceiver.prototype.addAges = function (children, ages) {
        for (var index = ages; index < children; index++) {
            console.log('asdf');
            var ageHTML = this.agesTemplate.replace(/__name__/g, String(index));
            this.$childrenAgeHolder.append(ageHTML);
        }
    };
    FormDataReceiver.prototype.getChildrenAgesIndex = function () {
        return this.$childrenAgeHolder.find(':input').length;
    };
    FormDataReceiver.prototype.getChildrenCount = function () {
        return Number(this.$children.val());
    };
    FormDataReceiver.prototype.getSearchConditionsData = function () {
        var data;
        data = {
            begin: String(this.getFormField('begin')),
            end: String(this.getFormField('end')),
            adults: Number(this.getFormField('adults')),
            additionalBegin: Number(this.getFormField('additionalBegin')),
            additionalEnd: Number(this.getFormField('additionalEnd')),
            tariffs: this.getFormField('tariffs'),
            roomTypes: this.getFormField('roomTypes'),
            hotels: this.getFormField('hotels'),
            children: Number(this.getFormField('children')),
            childrenAges: this.getChildrenAges()
        };
        return data;
    };
    FormDataReceiver.prototype.getFormField = function (fieldName) {
        var field = this.$form.find("#" + this.formName + "_" + fieldName);
        return field.val();
    };
    FormDataReceiver.prototype.getChildrenAges = function () {
        var data = [];
        $.each(this.$childrenAgeHolder.find('select'), function () {
            data.push(Number($(this).val()));
        });
        return data;
    };
    return FormDataReceiver;
}());
///<reference path="../../../../../../../../node_modules/@types/accounting/index.d.ts"/>
///<reference path="../../../../../../../../node_modules/@types/bootstrap/index.d.ts"/>
var Writer = /** @class */ (function () {
    function Writer() {
        this.data = {};
        this.searchStatus = { state: 'new' };
        this.init();
    }
    Writer.prototype.init = function () {
        this.searchVueInit();
        this.showSearchStatusInit();
    };
    Writer.prototype.showSearchStatusInit = function () {
        this.statusVue = new Vue({
            el: '#package-searcher-results-wrapper',
            template: "<div  v-if=\"status.state === 'new' \" class=\"bg-gray color-palette alert\"> <i class=\"fa fa-search\"> </i> \u0412\u0432\u0435\u0434\u0438\u0442\u0435  \u0434\u0430\u043D\u043D\u044B\u0435 \u0434\u043B\u044F \u043F\u043E\u0438\u0441\u043A\u0430 </div>\n                       <div  v-else-if=\"status.state === 'noResults' \" class=\"alert alert-warning\"> <i class=\"fa fa-exclamation-circle\"></i> \u041F\u043E \u0432\u0430\u0448\u0435\u043C\u0443 \u0437\u0430\u043F\u0440\u043E\u0441\u0443 \u043D\u0438\u0447\u0435\u0433\u043E \u043D\u0435 \u043D\u0430\u0439\u0434\u0435\u043D\u043E</div>\n                       <div  v-else-if=\"status.state === 'error' \" class=\"alert alert-danger\"> <i class=\"fa fa-exclamation-circle\"></i> \u041F\u0440\u043E\u0438\u0437\u043E\u0448\u043B\u0430 \u043E\u0448\u0438\u0431\u043A\u0430 \u043F\u0440\u0438 \u0437\u0430\u043F\u0440\u043E\u0441\u0435 \u0432 \u0431\u0430\u0437\u0443 \u0434\u0430\u043D\u043D\u044B\u0445!</div>\n                       <div  v-else-if=\"status.state === 'process' \" class=\"alert alert-warning\"> <i class=\"fa fa-spinner fa-spin\"></i> \u041F\u043E\u0434\u043E\u0436\u0434\u0438\u0442\u0435...</div>",
            data: {
                status: this.searchStatus
            }
        });
    };
    Writer.prototype.searchVueInit = function () {
        Vue.component('tariff', {
            props: ['tariff', 'freeRooms'],
            template: '<td>{{tariff.name}}<br><small><span class="package-search-book-count">Свободно номеров: {{freeRooms}}</span></small></td>'
        });
        Vue.component('package-link', {
            props: ['link', 'roomsCount'],
            template: "<td class=\"text-center\">\n                            <a v-if=\"roomsCount > 0\" :href=\"link\" target=\"_blank\" class=\"btn btn-success btn-xs package-search-book\" :title=\"'\u0411\u0440\u043E\u043D\u0438\u0440\u043E\u0432\u0430\u0442\u044C \u043D\u043E\u043C\u0435\u0440. \u0412\u0441\u0435\u0433\u043E \u043D\u043E\u043C\u0435\u0440\u043E\u0432: ' + roomsCount\" >\n                            <i class=\"fa fa-book\"></i><span class=\"package-search-book-reservation-text\"> \u0411\u0440\u043E\u043D\u0438\u0440\u043E\u0432\u0430\u0442\u044C</span>\n                            </a>\n                        </td>"
        });
        Vue.component('count', {
            props: ['count'],
            template: "<td>\n                        <select class=\"form-control quantity-select input-xxs\">\n                            <option :value=\"count\">{{ count }}</option>\n                        </select>\n                       </td>"
        });
        Vue.component('prices', {
            props: ['prices', 'defaultPriceIndex'],
            template: "<td class=\"text-center\">\n                     <select v-model=\"selected\" @change=\"$emit('price-index-update', selected)\" class=\"form-control plain-html input-sm search-tourists-select\">\n                            <option v-for=\"(price, key) in prices\" :value=\"key\"><span>{{price.adults}} \u0432\u0437\u0440.</span><span v-if=\"price.children\">+{{price.children}} \u0440\u0435\u0431.</span></option>\n                        </select>\n                    </td>",
            data: function () {
                return {
                    selected: this.defaultPriceIndex
                };
            }
        });
        Vue.component('day-price', {
            props: ['dayPrices'],
            template: "<small>\n                            <i v-popover class=\"fa fa-question-circle\" data-container=\"body\" data-toggle=\"popover\"\n                                data-placement=\"left\" data-html=\"true\"\n                                :data-content=\"detail\"\n                                ></i>\n                        </small>",
            computed: {
                detail: function () {
                    var html = '';
                    for (var _i = 0, _a = this.dayPrices; _i < _a.length; _i++) {
                        var dayPrice = _a[_i];
                        html += dayPrice['day'] + " - " + dayPrice['price'] + " - <i class='fa fa-sliders'></i> " + dayPrice['tariff']['name'] + "<br>";
                    }
                    return "<small>" + html + "</small>";
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
            template: "<td class=\"text-right\"><ul class=\"package-search-prices\">\n                      <li>{{rounded(price.total)}}\n                        <small is=\"day-price\"  :dayPrices=\"price.dayPrices\"></small>\n                      </li>\n                    </ul>\n                    <small><i class=\"fa fa-sliders\"></i> {{tariffName}}</small>\n                    </td>",
            methods: {
                rounded: function (price) {
                    return accounting.formatMoney(price, "", 2, ",", ".");
                }
            }
        });
        Vue.component('result', {
            props: ['result'],
            template: "<tr>\n                    <td class=\"text-center table-icon\"><i class=\"fa fa-paper-plane-o\"></i></td>\n                    <td>{{begin}}-{{end}}<br><small>{{night}} \u043D\u043E\u0447\u0435\u0439</small></td>\n                    <td is=\"tariff\" :tariff=\"result.tariff\" :freeRooms=\"minRooms\"></td>\n                    <td is=\"count\" :count=\"minRooms\"></td>\n                    <td is=\"prices\" :prices=\"result.prices\" :defaultPriceIndex=\"currentPriceIndex\" @price-index-update=\"priceIndexUpdate($event)\"></td>\n                    <td is=\"total-price\" :price=\"result.prices[currentPriceIndex]\" :tariffName=\"result.tariff.name\"></td>\n                    <td is=\"package-link\" :link=\"getLink()\" :roomsCount=\"minRooms\" data-toggle=\"tooltip\" @click.native=\"$emit('booking')\"></td>\n            </tr>",
            computed: {
                begin: function () {
                    var begin = moment(this.result.begin, 'DD.MM.YYYY');
                    return begin.format('DD MMM');
                },
                end: function () {
                    var end = moment(this.result.end, 'DD.MM.YYYY');
                    return end.format('DD MMM');
                },
                night: function () {
                    var begin = moment.utc(this.result.begin, 'DD.MM.YYYY');
                    var end = moment.utc(this.result.end, 'DD.MM.YYYY');
                    return moment.duration(end.diff(begin)).days();
                },
                minRooms: function () {
                    return this.result.minRooms;
                }
            },
            methods: {
                getLink: function () {
                    var begin = this.result.begin;
                    var end = this.result.end;
                    var tariff = this.result.tariff.id;
                    var roomType = this.result.roomType.id;
                    var adults = this.result.prices[this.currentPriceIndex].adults;
                    var children = this.result.prices[this.currentPriceIndex].children;
                    var childrenAges = this.result.conditions.childrenAges;
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
                    currentPriceIndex: 0,
                };
            }
        });
        Vue.component('room-type', {
            props: ['roomType', 'results'],
            template: "<tbody>\n                           <tr class=\"mbh-grid-header1 info\"><td colspan=\"8\">{{roomType.name}}: {{roomType.hotelName}}</td></tr>\n                           <tr @booking=\"booking\" is=\"result\" v-for=\"(result, key) in sortedResultsByPrice\" :key=\"key\" :result=\"result\"></tr>\n                       </tbody>\n                        ",
            methods: {
                booking: function () {
                    for (var index in this.results) {
                        this.results[index].minRooms--;
                    }
                }
            },
            computed: {
                sortedResultsByPrice: function () {
                    this.results.sort(function (resultA, resultB) {
                        if (typeof resultA.prices[0] !== 'object' || typeof resultB.prices[0] !== 'object') {
                            return;
                        }
                        var keyPriceA = Object.keys(resultA.prices)[0];
                        var keyPriceB = Object.keys(resultB.prices)[0];
                        var priceA = resultA.prices[keyPriceA].total;
                        var priceB = resultB.prices[keyPriceB].total;
                        if (priceA < priceB) {
                            return -1;
                        }
                        if (priceA > priceB) {
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
            template: "<table v-if=\"Object.keys(rawData).length !== 0\" class=\"package-search-table table table-striped table-hover table-condensed table-icons table-actions\">\n                        <thead>\n                            <tr>\n                                <th class=\"td-xxs\"></th>\n                                <th class=\"td-md\">\u0414\u0430\u0442\u044B</th>\n                                <th>\u0422\u0430\u0440\u0438\u0444</th>\n                                <th class=\"td-sm\">\u041A\u043E\u043B\u0438\u0447\u0435\u0441\u0442\u0432\u043E</th>\n                                <th class=\"td-sm\">\u0413\u043E\u0441\u0442\u0438</th>\n                                <th class=\"td-md\">\u0426\u0435\u043D\u0430</th>\n                                <th class=\"td-md\"></th>\n                            </tr>\n                        </thead>\n                        <tbody is=\"room-type\" v-for=\"(data, key) in rawData\" :roomType=\"data.roomType\" :results=\"data.results\" :key=\"key\"></tbody>\n                        </table>\n",
            /*template: '<span><room-type v-for="(data, key) in rawData" :roomType="data.roomType" :searchResults="data.results" :key="key" ></room-type></span>',*/
            data: { rawData: this.data },
        });
    };
    Writer.prototype.showStartSearch = function () {
        console.log('Search started');
        this.data = {};
        this.rootApp.rawData = this.data;
        this.searchStatus.state = 'process';
    };
    Writer.prototype.showStopSearch = function (state) {
        console.log('Search stopped');
        this.searchStatus.state = state;
    };
    Writer.prototype.drawResults = function (data) {
        for (var newKey in data) {
            if (!data.hasOwnProperty(newKey)) {
                continue;
            }
            if (!this.data.hasOwnProperty(newKey)) {
                //  * @url https://ru.vuejs.org/v2/guide/reactivity.html */
                this.rootApp.$set(this.rootApp.rawData, newKey, data[newKey]);
            }
            else {
                this.data[newKey].results = this.data[newKey].results.concat(data[newKey].results);
            }
        }
    };
    return Writer;
}());
///<reference path="Searchers/AsyncSearcher.ts"/>
///<reference path="Searchers/SyncSearcher.ts"/>
///<reference path="DataReceivers/FormDataReceiver.ts"/>
///<reference path="vuejs.d.ts"/>
///<reference path="Writers/Writer.ts"/>
///<reference path="../../../../../../../node_modules/moment/moment.d.ts"/>
var writer = new Writer();
var formDataReceiver = new FormDataReceiver('search_conditions');
new AsyncSearcher('searcher-submit-button', writer, formDataReceiver);
new SyncSearcher('searcher-sync-submit-button', writer, formDataReceiver);
var Result = /** @class */ (function () {
    function Result() {
    }
    return Result;
}());
var RoomType = /** @class */ (function () {
    function RoomType(result) {
        this.results = [];
        this.id = result.roomType.id;
    }
    RoomType.prototype.getId = function () {
        return this.id;
    };
    RoomType.prototype.update = function (result) {
    };
    return RoomType;
}());
var RoomTypeHolder = /** @class */ (function () {
    function RoomTypeHolder() {
        this.roomTypes = [];
    }
    RoomTypeHolder.prototype.update = function (results) {
        for (var roomTypeKey in results) {
            if (!this.isRoomTypeExists(roomTypeKey)) {
                var roomType = new RoomType(results[roomTypeKey]);
                this.roomTypes.push(roomType);
            }
        }
    };
    RoomTypeHolder.prototype.getData = function () {
        return this.roomTypes;
    };
    RoomTypeHolder.prototype.isRoomTypeExists = function (key) {
        for (var _i = 0, _a = this.roomTypes; _i < _a.length; _i++) {
            var roomType = _a[_i];
            if (roomType.getId() === key) {
                return true;
            }
        }
        return false;
    };
    return RoomTypeHolder;
}());
var Inner = /** @class */ (function () {
    function Inner(type, number) {
        this.type = type;
        this.number = number;
    }
    Inner.prototype.getType = function () {
        return this.type;
    };
    Inner.prototype.getNumber = function () {
        return this.number;
    };
    return Inner;
}());
///<reference path="Inner.ts"/>
var Wrap = /** @class */ (function () {
    function Wrap() {
        this.inners = [];
        this.inners.push(new Inner('first inner', 1));
        this.inners.push(new Inner('second inner', 2));
    }
    Wrap.prototype.getData = function () {
        return this.inners;
    };
    return Wrap;
}());
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic2VhcmNoLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vcHJpdmF0ZS90cy9TZWFyY2hlcnMvU2VhcmNoZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL1NlYXJjaGVycy9Bc3luY1NlYXJjaGVyLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9TZWFyY2hlcnMvU3luY1NlYXJjaGVyLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9EYXRhUmVjZWl2ZXJzL0Zvcm1EYXRhUmVjZWl2ZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL1dyaXRlcnMvV3JpdGVyLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9pbmRleC50cyIsIi4uLy4uL3ByaXZhdGUvdHMvUmVzdWx0L1Jlc3VsdC50cyIsIi4uLy4uL3ByaXZhdGUvdHMvUmVzdWx0L1Jvb21UeXBlLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9SZXN1bHQvUm9vbVR5cGVIb2xkZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL3RlbXAvSW5uZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL3RlbXAvV3JhcC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFFQTtJQUtJLGtCQUFzQixRQUFnQixFQUFFLE1BQWMsRUFBRSxZQUFtQztRQUN2RixJQUFJLENBQUMsTUFBTSxHQUFHLENBQUMsQ0FBQyxNQUFJLFFBQVUsQ0FBQyxDQUFDO1FBQ2hDLElBQUksQ0FBQyxNQUFNLEdBQUcsTUFBTSxDQUFDO1FBQ3JCLElBQUksQ0FBQyxrQkFBa0IsR0FBRyxZQUFZLENBQUM7UUFDdkMsSUFBSSxDQUFDLFlBQVksRUFBRSxDQUFDO0lBQ3hCLENBQUM7SUFFTywrQkFBWSxHQUFwQjtRQUFBLGlCQUtDO1FBSkcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLFVBQUEsS0FBSztZQUN6QixLQUFLLENBQUMsY0FBYyxFQUFFLENBQUM7WUFDdkIsS0FBSSxDQUFDLFFBQVEsRUFBRSxDQUFDO1FBQ3BCLENBQUMsQ0FBQyxDQUFBO0lB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