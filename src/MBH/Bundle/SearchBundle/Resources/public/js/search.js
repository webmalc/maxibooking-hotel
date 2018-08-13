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
        this.writer.showStopSearch();
    };
    Searcher.prototype.drawResults = function (data) {
        var searchResults = data.results;
        this.writer.drawResults(searchResults);
    };
    Searcher.prototype.getSearchConditions = function () {
        return this.searchDataReceiver.getSearchConditionsData();
    };
    return Searcher;
}());
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    }
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
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
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
                        if (requestResults.status === 204) {
                            throw new Error('End of data');
                        }
                        this.drawResults(data);
                        return [3 /*break*/, 6];
                    case 5:
                        err_1 = _a.sent();
                        error = true;
                        console.log(err_1);
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
                        this.onStopSearch({ status: 'success' });
                        return [3 /*break*/, 4];
                    case 3:
                        e_2 = _a.sent();
                        this.onStopSearch(e_2);
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
        this.$childrenAges = [];
        this.$form = $("form[name=\"" + formName + "\"]");
        this.formName = formName;
        this.$children = $('input#search_conditions_children');
        this.$childrenAgeHolder = $('#search_conditions_childrenAges');
        this.agesTemplate = this.$childrenAgeHolder.data('prototype');
        this.bindHandlers();
    }
    FormDataReceiver.prototype.bindHandlers = function () {
        var _this = this;
        this.$children.on('change', function (e) {
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
var Writer = /** @class */ (function () {
    function Writer() {
        this.data = {};
        this.searchStatus = { state: false };
        this.init();
    }
    Writer.prototype.init = function () {
        this.searchVueInit();
        this.showSearchStatusInit();
        this.testVueJs();
    };
    Writer.prototype.testVueJs = function () {
        Vue.component('testing', {
            template: '<div><input  v-model="text" @input="$emit(\'reread\', \'alala\'); console($event)"><span>{{text}}</span></div>',
            data: function () {
                return {
                    text: 'newText'
                };
            },
            methods: {
                console: function (event) {
                    console.log(event);
                }
            }
        });
        new Vue({
            el: '#test',
            template: '<span>{{value}}<testing @reread="console($event); concatinate($event)"></testing></span>',
            data: {
                value: 'Value!'
            },
            methods: {
                console: function (event) {
                    console.log(event);
                },
                concatinate: function (event) {
                    this.value = this.value + event;
                }
            }
        });
    };
    Writer.prototype.showSearchStatusInit = function () {
        this.statusVue = new Vue({
            el: '#search-status',
            template: '<span v-if="status.state">Идет поиск...</span><span v-else>поиск не идет.</span>',
            data: {
                status: this.searchStatus
            }
        });
    };
    Writer.prototype.searchVueInit = function () {
        Vue.component('tariff', {
            props: ['tariff'],
            template: '<span>{{tariff.name}}. </span>'
        });
        Vue.component('prices', {
            props: ['prices', 'defaultPriceIndex'],
            template: "<span>\n                        <select v-model=\"selected\" @change=\"$emit('price-index-update', selected)\">\n                            <option v-for=\"(price, key) in prices\" :value=\"key\">{{price.searchAdults}}_{{price.searchChildren}} - {{rounded(price.total)}} </option>\n                        </select>\n                    </span>",
            methods: {
                rounded: function (price) {
                    return Number(price).toFixed(1);
                }
            },
            data: function () {
                return {
                    selected: this.defaultPriceIndex
                };
            }
        });
        Vue.component('package-link', {
            props: ['link'],
            template: '<a :href="link">Тыц на бронь</a>'
        });
        Vue.component('search-result', {
            props: ['result'],
            template: "<li>\n            <span>{{result.begin}}-{{result.end}}.  \u0422\u0430\u0440\u0438\u0444 - \n                <tariff :tariff=\"result.resultTariff\"></tariff>\n                <prices :prices=\"result.prices\" :defaultPriceIndex=\"currentPriceIndex\" @price-index-update=\"priceIndexUpdate($event)\"></prices>\n            </span>\n                <package-link :link=\"getLink()\"></package-link>\n            </li>",
            methods: {
                getLink: function () {
                    var begin = this.result.begin;
                    var end = this.result.end;
                    var tariff = this.result.resultTariff.id;
                    var roomType = this.result.resultRoomType.id;
                    var adults = this.result.prices[this.currentPriceIndex].adults;
                    var children = this.result.prices[this.currentPriceIndex].children;
                    var childrenAges = this.result.resultConditions.childrenAges;
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
                };
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
        Vue.component('room-type', {
            props: ['roomType', 'searchResults'],
            template: '<ul>{{roomType.name}}: {{roomType.hotelName}} - <li is="results-by-date" v-for="(results, key) in searchResults" :key="key" :results="results" :dates="key"></li></ul>',
        });
        this.rootApp = new Vue({
            el: '#vue_results',
            template: '<span><room-type v-for="(data, key) in rawData" :roomType="data.roomType" :searchResults="data.results" :key="key" ></room-type></span>',
            data: { rawData: this.data },
        });
    };
    Writer.prototype.showStartSearch = function () {
        console.log('Search started');
        this.data = {};
        this.rootApp.rawData = this.data;
        this.searchStatus.state = true;
    };
    Writer.prototype.showStopSearch = function () {
        console.log('Search stopped');
        this.searchStatus.state = false;
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
                var existsDates = this.data[newKey].results;
                var newDates = data[newKey].results;
                for (var newDatesKey in newDates) {
                    if (newDates.hasOwnProperty(newDatesKey) && existsDates.hasOwnProperty(newDatesKey)) {
                        for (var _i = 0, _a = newDates[newDatesKey]; _i < _a.length; _i++) {
                            var newDate = _a[_i];
                            this.data[newKey].results[newDatesKey].push(newDate);
                        }
                    }
                    else {
                        Vue.set(this.data[newKey].results, newDatesKey, newDates[newDatesKey]);
                    }
                }
                // this.data[newKey].results = this.data[newKey].results.concat(data[newKey].results);
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
var writer = new Writer();
var formDataReceiver = new FormDataReceiver('search_conditions');
new AsyncSearcher('async_search', writer, formDataReceiver);
new SyncSearcher('sync_search', writer, formDataReceiver);
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