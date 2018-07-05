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
        this.init();
    }
    Writer.prototype.init = function () {
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
            data: { rawData: this.data }
        });
    };
    Writer.prototype.showStartSearch = function () {
        console.log('Search started');
        this.data = {};
        this.rootApp.rawData = this.data;
    };
    Writer.prototype.showStopSearch = function () {
        console.log('Search stopped');
    };
    Writer.prototype.drawResults = function (data) {
        for (var newKey in data) {
            if (!this.data.hasOwnProperty(newKey)) {
                var tempData = {};
                tempData[newKey] = data[newKey];
                /**
                 * х.з почему тут именно так.
                 * @url https://ru.vuejs.org/v2/guide/reactivity.html */
                this.data = Object.assign({}, this.data, tempData);
                this.rootApp.rawData = this.data;
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic2VhcmNoLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vcHJpdmF0ZS90cy9TZWFyY2hlcnMvU2VhcmNoZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL1NlYXJjaGVycy9Bc3luY1NlYXJjaGVyLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9TZWFyY2hlcnMvU3luY1NlYXJjaGVyLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9EYXRhUmVjZWl2ZXJzL0Zvcm1EYXRhUmVjZWl2ZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL1dyaXRlcnMvV3JpdGVyLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9pbmRleC50cyIsIi4uLy4uL3ByaXZhdGUvdHMvUmVzdWx0L1Jlc3VsdC50cyIsIi4uLy4uL3ByaXZhdGUvdHMvUmVzdWx0L1Jvb21UeXBlLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9SZXN1bHQvUm9vbVR5cGVIb2xkZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL3RlbXAvSW5uZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL3RlbXAvV3JhcC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFFQTtJQUtJLGtCQUFzQixRQUFnQixFQUFFLE1BQWMsRUFBRSxZQUFtQztRQUN2RixJQUFJLENBQUMsTUFBTSxHQUFHLENBQUMsQ0FBQyxNQUFJLFFBQVUsQ0FBQyxDQUFDO1FBQ2hDLElBQUksQ0FBQyxNQUFNLEdBQUcsTUFBTSxDQUFDO1FBQ3JCLElBQUksQ0FBQyxrQkFBa0IsR0FBRyxZQUFZLENBQUM7UUFDdkMsSUFBSSxDQUFDLFlBQVksRUFBRSxDQUFDO0lBQ3hCLENBQUM7SUFFTywrQkFBWSxHQUFwQjtRQUFBLGlCQUtDO1FBSkcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxFQUFFLENBQUMsT0FBTyxFQUFFLFVBQUEsS0FBSztZQUN6QixLQUFLLENBQUMsY0FBYyxFQUFFLENBQUM7WUFDdkIsS0FBSSxDQUFDLFFBQVEsRUFBRSxDQUFDO1FBQ3BCLENBQUMsQ0FBQyxDQUFBO0lBQ04sQ0FBQztJQUlTLGdDQUFhLEdBQXZCO1FBQ0ksSUFBSSxDQUFDLE1BQU0sQ0FBQyxlQUFlLEVBQUUsQ0FBQztJQUNsQyxDQUFDO0lBRVMsK0JBQVksR0FBdEIsVUFBdUIsY0FBbUI7UUFDdEMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxjQUFjLENBQUMsQ0FBQztRQUM1QixJQUFJLENBQUMsTUFBTSxDQUFDLGNBQWMsRUFBRSxDQUFDO0lBQ2pDLENBQUM7SUFFUyw4QkFBVyxHQUFyQixVQUFzQixJQUFJO1FBQ3RCLElBQU0sYUFBYSxHQUF1QixJQUFJLENBQUMsT0FBTyxDQUFDO1FBQ3ZELElBQUksQ0FBQyxNQUFNLENBQUMsV0FBVyxDQUFDLGFBQWEsQ0FBQyxDQUFDO0lBQzNDLENBQUM7SUFDUyxzQ0FBbUIsR0FBN0I7UUFDSSxPQUFRLElBQUksQ0FBQyxrQkFBa0IsQ0FBQyx1QkFBdUIsRUFBRSxDQUFDO1FBRzFELEVBQUU7UUFDRiw0QkFBNEI7UUFDNUIsV0FBVztRQUNYLDJCQUEyQjtRQUMzQix5QkFBeUI7UUFDekIsZ0JBQWdCO1FBQ2hCLEtBQUs7UUFDTCxFQUFFO1FBQ0YsZUFBZTtJQUNuQixDQUFDO0lBRUwsZUFBQztBQUFELENBQUMsQUFqREQsSUFpREM7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7QUNuREQsa0NBQWtDO0FBQ2xDO0lBQTRCLGlDQUFRO0lBSWhDLHVCQUFtQixRQUFnQixFQUFFLE1BQWMsRUFBRSxZQUFtQztRQUF4RixZQUNJLGtCQUFNLFFBQVEsRUFBRSxNQUFNLEVBQUUsWUFBWSxDQUFDLFNBQ3hDO1FBSmdCLHNCQUFnQixHQUFXLEVBQUUsQ0FBQzs7SUFJL0MsQ0FBQztJQUVlLGdDQUFRLEdBQXhCOzs7Ozs7d0JBQ0ksSUFBSSxDQUFDLGFBQWEsRUFBRSxDQUFDO3dCQUNmLFdBQVcsR0FBRyxPQUFPLENBQUMsUUFBUSxDQUFDLG9CQUFvQixDQUFDLENBQUM7Ozs7d0JBR3ZELElBQUksR0FBRyxDQUFDLENBQUMsSUFBSSxDQUFDOzRCQUNWLEdBQUcsRUFBRSxXQUFXOzRCQUNoQixJQUFJLEVBQUUsTUFBTTs0QkFDWixRQUFRLEVBQUUsTUFBTTs0QkFDaEIsSUFBSSxFQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLG1CQUFtQixFQUFFLENBQUM7eUJBQ25ELENBQUMsQ0FBQzt3QkFDdUIscUJBQU0sSUFBSSxFQUFBOzt3QkFBOUIsaUJBQWlCLEdBQUcsU0FBVTt3QkFDaEMsS0FBSyxHQUFXLENBQUMsQ0FBQzt3QkFDbEIsY0FBYyxTQUFBLENBQUM7d0JBQ2YsS0FBSyxHQUFZLEtBQUssQ0FBQzt3QkFDckIsV0FBVyxHQUFHLE9BQU8sQ0FBQyxRQUFRLENBQUMsc0JBQXNCLEVBQUUsRUFBQyxFQUFFLEVBQUUsaUJBQWlCLENBQUMsWUFBWSxFQUFFLFFBQVEsRUFBRSxVQUFVLEVBQUMsQ0FBQyxDQUFDOzs7O3dCQUdqSCxjQUFjLEdBQUcsSUFBSSxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUM7NEJBQzNCLEdBQUcsRUFBRSxXQUFXOzRCQUNoQixJQUFJLEVBQUUsTUFBTTs0QkFDWixRQUFRLEVBQUUsTUFBTTs0QkFDaEIsSUFBSSxFQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsRUFBRSxDQUFDO3lCQUMzQixDQUFDLENBQUM7d0JBQ1EscUJBQU0sY0FBYyxFQUFBOzt3QkFBM0IsSUFBSSxHQUFHLFNBQW9CO3dCQUMvQixJQUFJLENBQUMsV0FBVyxDQUFDLElBQUksQ0FBQyxDQUFDOzs7O3dCQUV2QixLQUFLLEdBQUcsSUFBSSxDQUFDO3dCQUNiLElBQUksQ0FBQyxZQUFZLENBQUMsY0FBYyxDQUFDLENBQUM7Ozt3QkFFdEMsS0FBSyxFQUFFLENBQUM7d0JBQ1IscUJBQU0sSUFBSSxPQUFPLENBQUMsVUFBQyxPQUFPO2dDQUN0QixVQUFVLENBQUM7b0NBQ1AsT0FBTyxFQUFFLENBQUM7Z0NBQ2QsQ0FBQyxFQUFFLElBQUksQ0FBQyxDQUFBOzRCQUNaLENBQUMsQ0FBQyxFQUFBOzt3QkFKRixTQUlFLENBQUM7Ozs0QkFDRSxDQUFDLEtBQUssSUFBSSxLQUFLLEdBQUcsSUFBSSxDQUFDLGdCQUFnQjs7O3dCQUNoRCxJQUFJLENBQUMsS0FBSyxFQUFFOzRCQUNSLE9BQU8sQ0FBQyxHQUFHLENBQUMsa0NBQWtDLENBQUMsQ0FBQzt5QkFDbkQ7Ozs7d0JBRUQsSUFBSSxDQUFDLFlBQVksQ0FBQyxJQUFJLENBQUMsQ0FBQzs7Ozs7O0tBRS9CO0lBQ0wsb0JBQUM7QUFBRCxDQUFDLEFBcERELENBQTRCLFFBQVEsR0FvRG5DO0FDckREO0lBQTJCLGdDQUFRO0lBRS9CLHNCQUFtQixRQUFnQixFQUFFLE1BQWMsRUFBRSxZQUFtQztlQUNwRixrQkFBTSxRQUFRLEVBQUUsTUFBTSxFQUFFLFlBQVksQ0FBQztJQUN6QyxDQUFDO0lBRWUsK0JBQVEsR0FBeEI7Ozs7Ozt3QkFDSSxJQUFJLENBQUMsYUFBYSxFQUFFLENBQUM7d0JBRWYsV0FBVyxHQUFHLE9BQU8sQ0FBQyxRQUFRLENBQUMsd0JBQXdCLEVBQUUsRUFBQyxRQUFRLEVBQUUsVUFBVSxFQUFDLENBQUMsQ0FBQzs7Ozt3QkFFbkYsSUFBSSxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUM7NEJBQ1YsR0FBRyxFQUFFLFdBQVc7NEJBQ2hCLElBQUksRUFBRSxNQUFNOzRCQUNaLFFBQVEsRUFBRSxNQUFNOzRCQUNoQixJQUFJLEVBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsbUJBQW1CLEVBQUUsQ0FBQzt5QkFDbkQsQ0FBQyxDQUFDO3dCQUNVLHFCQUFNLElBQUksRUFBQTs7d0JBQWpCLElBQUksR0FBRyxTQUFVO3dCQUN2QixJQUFJLENBQUMsV0FBVyxDQUFDLElBQUksQ0FBQyxDQUFDOzs7O3dCQUV2QixJQUFJLENBQUMsWUFBWSxDQUFDLEdBQUMsQ0FBQyxDQUFDOzs7Ozs7S0FJNUI7SUFHTCxtQkFBQztBQUFELENBQUMsQUEzQkQsQ0FBMkIsUUFBUSxHQTJCbEM7QUMzQkQ7SUFRSSwwQkFBWSxRQUFnQjtRQUhwQixrQkFBYSxHQUFhLEVBQUUsQ0FBQztRQUlqQyxJQUFJLENBQUMsS0FBSyxHQUFHLENBQUMsQ0FBQyxpQkFBYyxRQUFRLFFBQUksQ0FBQyxDQUFDO1FBQzNDLElBQUksQ0FBQyxRQUFRLEdBQUcsUUFBUSxDQUFDO1FBQ3pCLElBQUksQ0FBQyxTQUFTLEdBQUcsQ0FBQyxDQUFDLGtDQUFrQyxDQUFDLENBQUM7UUFDdkQsSUFBSSxDQUFDLGtCQUFrQixHQUFHLENBQUMsQ0FBQyxpQ0FBaUMsQ0FBQyxDQUFDO1FBQy9ELElBQUksQ0FBQyxZQUFZLEdBQUcsSUFBSSxDQUFDLGtCQUFrQixDQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsQ0FBQztRQUM5RCxJQUFJLENBQUMsWUFBWSxFQUFFLENBQUM7SUFDeEIsQ0FBQztJQUVPLHVDQUFZLEdBQXBCO1FBQUEsaUJBSUM7UUFIRyxJQUFJLENBQUMsU0FBUyxDQUFDLEVBQUUsQ0FBQyxRQUFRLEVBQUUsVUFBQyxDQUFDO1lBQzFCLEtBQUksQ0FBQyxrQkFBa0IsRUFBRSxDQUFDO1FBQzlCLENBQUMsQ0FBQyxDQUFBO0lBQ04sQ0FBQztJQUVPLDZDQUFrQixHQUExQjtRQUNJLElBQU0sZ0JBQWdCLEdBQUcsSUFBSSxDQUFDLG9CQUFvQixFQUFFLENBQUM7UUFDckQsSUFBTSxvQkFBb0IsR0FBRyxJQUFJLENBQUMsZ0JBQWdCLEVBQUUsQ0FBQztRQUNyRCxJQUFJLGdCQUFnQixHQUFHLG9CQUFvQixFQUFFO1lBQ3pDLElBQUksQ0FBQyxVQUFVLENBQUMsb0JBQW9CLEVBQUUsZ0JBQWdCLENBQUMsQ0FBQztTQUMzRDtRQUNELElBQUksZ0JBQWdCLEdBQUcsb0JBQW9CLEVBQUU7WUFDekMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxvQkFBb0IsRUFBRSxnQkFBZ0IsQ0FBQyxDQUFDO1NBQ3hEO0lBQ0wsQ0FBQztJQUVPLHFDQUFVLEdBQWxCLFVBQW1CLFFBQWdCLEVBQUUsSUFBWTtRQUM3QyxLQUFLLElBQUksS0FBSyxHQUFHLElBQUksRUFBRSxLQUFLLEdBQUcsUUFBUSxFQUFHLEtBQUssRUFBRyxFQUFFO1lBQ2hELElBQU0sUUFBUSxHQUFHLDRDQUF5QyxLQUFLLEdBQUMsQ0FBQyxDQUFFLENBQUM7WUFDcEUsSUFBSSxTQUFTLEdBQUcsQ0FBQyxDQUFDLFFBQVEsQ0FBQyxDQUFDLE1BQU0sQ0FBQyxLQUFLLENBQUMsQ0FBQztZQUMxQyxTQUFTLENBQUMsTUFBTSxFQUFFLENBQUM7U0FDdEI7SUFDTCxDQUFDO0lBRU8sa0NBQU8sR0FBZixVQUFnQixRQUFnQixFQUFFLElBQVk7UUFDMUMsS0FBSyxJQUFJLEtBQUssR0FBRyxJQUFJLEVBQUUsS0FBSyxHQUFHLFFBQVEsRUFBRSxLQUFLLEVBQUUsRUFBRTtZQUM5QyxPQUFPLENBQUMsR0FBRyxDQUFDLE1BQU0sQ0FBQyxDQUFDO1lBQ3BCLElBQUksT0FBTyxHQUFHLElBQUksQ0FBQyxZQUFZLENBQUMsT0FBTyxDQUFDLFdBQVcsRUFBRSxNQUFNLENBQUMsS0FBSyxDQUFDLENBQUMsQ0FBQztZQUNwRSxJQUFJLENBQUMsa0JBQWtCLENBQUMsTUFBTSxDQUFDLE9BQU8sQ0FBQyxDQUFDO1NBQzNDO0lBQ0wsQ0FBQztJQUdPLCtDQUFvQixHQUE1QjtRQUNJLE9BQU8sSUFBSSxDQUFDLGtCQUFrQixDQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQyxNQUFNLENBQUM7SUFDekQsQ0FBQztJQUVPLDJDQUFnQixHQUF4QjtRQUNJLE9BQU8sTUFBTSxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsR0FBRyxFQUFFLENBQUMsQ0FBQztJQUN4QyxDQUFDO0lBRU0sa0RBQXVCLEdBQTlCO1FBQ0ksSUFBSSxJQUFvQixDQUFDO1FBQ3pCLElBQUksR0FBRztZQUNILEtBQUssRUFBRSxNQUFNLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxPQUFPLENBQUMsQ0FBQztZQUN6QyxHQUFHLEVBQUUsTUFBTSxDQUFDLElBQUksQ0FBQyxZQUFZLENBQUMsS0FBSyxDQUFDLENBQUM7WUFDckMsTUFBTSxFQUFFLE1BQU0sQ0FBQyxJQUFJLENBQUMsWUFBWSxDQUFDLFFBQVEsQ0FBQyxDQUFDO1lBQzNDLGVBQWUsRUFBRSxNQUFNLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxpQkFBaUIsQ0FBQyxDQUFDO1lBQzdELGFBQWEsRUFBRSxNQUFNLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxlQUFlLENBQUMsQ0FBQztZQUN6RCxPQUFPLEVBQUUsSUFBSSxDQUFDLFlBQVksQ0FBQyxTQUFTLENBQUM7WUFDckMsU0FBUyxFQUFFLElBQUksQ0FBQyxZQUFZLENBQUMsV0FBVyxDQUFDO1lBQ3pDLE1BQU0sRUFBRSxJQUFJLENBQUMsWUFBWSxDQUFDLFFBQVEsQ0FBQztZQUNuQyxRQUFRLEVBQUUsTUFBTSxDQUFDLElBQUksQ0FBQyxZQUFZLENBQUMsVUFBVSxDQUFDLENBQUM7WUFDL0MsWUFBWSxFQUFFLElBQUksQ0FBQyxlQUFlLEVBQUU7U0FFdkMsQ0FBQztRQUVGLE9BQU8sSUFBSSxDQUFDO0lBQ2hCLENBQUM7SUFFTyx1Q0FBWSxHQUFwQixVQUFxQixTQUFpQjtRQUNsQyxJQUFJLEtBQUssR0FBRyxJQUFJLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxNQUFJLElBQUksQ0FBQyxRQUFRLFNBQUksU0FBVyxDQUFDLENBQUM7UUFFOUQsT0FBTyxLQUFLLENBQUMsR0FBRyxFQUFFLENBQUM7SUFDdkIsQ0FBQztJQUVPLDBDQUFlLEdBQXZCO1FBQ0ksSUFBSSxJQUFJLEdBQVksRUFBRSxDQUFDO1FBQ3ZCLENBQUMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGtCQUFrQixDQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsRUFBRTtZQUMzQyxJQUFJLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLENBQUMsR0FBRyxFQUFFLENBQUMsQ0FBQyxDQUFDO1FBQ3JDLENBQUMsQ0FBQyxDQUFDO1FBRUgsT0FBTyxJQUFJLENBQUM7SUFDaEIsQ0FBQztJQUVMLHVCQUFDO0FBQUQsQ0FBQyxBQTdGRCxJQTZGQztBQzdGRDtJQU1JO1FBSlEsU0FBSSxHQUFVLEVBQUUsQ0FBQztRQUtyQixJQUFJLENBQUMsSUFBSSxFQUFFLENBQUM7SUFDaEIsQ0FBQztJQUVPLHFCQUFJLEdBQVo7UUFDSSxHQUFHLENBQUMsU0FBUyxDQUFDLE9BQU8sRUFBRTtZQUNuQixLQUFLLEVBQUUsQ0FBQyxhQUFhLEVBQUUsT0FBTyxDQUFDO1lBQy9CLFFBQVEsRUFBRSx1RUFBdUU7WUFDakYsT0FBTyxFQUFFO2dCQUNMLElBQUksRUFBRTtvQkFDRixPQUFPLElBQUksQ0FBQyxXQUFXLENBQUMsS0FBSyxDQUFDLEVBQUUsQ0FBQyxDQUFDLE9BQU8sRUFBRSxDQUFDLElBQUksQ0FBQyxFQUFFLENBQUMsQ0FBQztnQkFDekQsQ0FBQzthQUNKO1NBQ0osQ0FBQyxDQUFDO1FBQ0gsR0FBRyxDQUFDLFNBQVMsQ0FBQyxRQUFRLEVBQUU7WUFDcEIsS0FBSyxFQUFFLENBQUMsUUFBUSxDQUFDO1lBQ2pCLFFBQVEsRUFBRSxrSUFBa0k7U0FDL0ksQ0FBQyxDQUFDO1FBQ0gsR0FBRyxDQUFDLFNBQVMsQ0FBQyxRQUFRLEVBQUU7WUFDcEIsS0FBSyxFQUFFLENBQUMsUUFBUSxDQUFDO1lBQ2pCLFFBQVEsRUFBRSxnQ0FBZ0M7U0FDN0MsQ0FBQyxDQUFDO1FBQ0gsR0FBRyxDQUFDLFNBQVMsQ0FBQyxlQUFlLEVBQUU7WUFDM0IsS0FBSyxFQUFFLENBQUMsUUFBUSxDQUFDO1lBQ2pCLFFBQVEsRUFBRSwrTEFBK0w7U0FDNU0sQ0FBQyxDQUFDO1FBQ0gsR0FBRyxDQUFDLFNBQVMsQ0FBQyxXQUFXLEVBQUU7WUFDdkIsS0FBSyxFQUFFLENBQUMsVUFBVSxFQUFFLGVBQWUsQ0FBQztZQUNwQyxRQUFRLEVBQUUsc0pBQXNKO1NBQ25LLENBQUMsQ0FBQztRQUNILElBQUksQ0FBQyxPQUFPLEdBQUcsSUFBSSxHQUFHLENBQUM7WUFDbkIsRUFBRSxFQUFFLGNBQWM7WUFDbEIsUUFBUSxFQUFFLHlJQUF5STtZQUNuSixJQUFJLEVBQUUsRUFBQyxPQUFPLEVBQUUsSUFBSSxDQUFDLElBQUksRUFBQztTQUM3QixDQUFDLENBQUM7SUFDUCxDQUFDO0lBRU0sZ0NBQWUsR0FBdEI7UUFDSSxPQUFPLENBQUMsR0FBRyxDQUFDLGdCQUFnQixDQUFDLENBQUM7UUFDOUIsSUFBSSxDQUFDLElBQUksR0FBRyxFQUFFLENBQUM7UUFDZixJQUFJLENBQUMsT0FBTyxDQUFDLE9BQU8sR0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDO0lBQ3JDLENBQUM7SUFFTSwrQkFBYyxHQUFyQjtRQUNJLE9BQU8sQ0FBQyxHQUFHLENBQUMsZ0JBQWdCLENBQUMsQ0FBQztJQUNsQyxDQUFDO0lBRU0sNEJBQVcsR0FBbEIsVUFBbUIsSUFBSTtRQUVuQixLQUFLLElBQUksTUFBTSxJQUFJLElBQUksRUFBRTtZQUNyQixJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxjQUFjLENBQUMsTUFBTSxDQUFDLEVBQUU7Z0JBQ25DLElBQUksUUFBUSxHQUFHLEVBQUUsQ0FBQztnQkFDbEIsUUFBUSxDQUFDLE1BQU0sQ0FBQyxHQUFHLElBQUksQ0FBQyxNQUFNLENBQUMsQ0FBQztnQkFDaEM7O3dFQUV3RDtnQkFDeEQsSUFBSSxDQUFDLElBQUksR0FBRyxNQUFNLENBQUMsTUFBTSxDQUFDLEVBQUUsRUFBRSxJQUFJLENBQUMsSUFBSSxFQUFFLFFBQVEsQ0FBQyxDQUFDO2dCQUNuRCxJQUFJLENBQUMsT0FBTyxDQUFDLE9BQU8sR0FBRyxJQUFJLENBQUMsSUFBSSxDQUFDO2FBQ3BDO2lCQUFNO2dCQUNILElBQUksQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsT0FBTyxHQUFHLElBQUksQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsT0FBTyxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsT0FBTyxDQUFDLENBQUM7YUFDdEY7U0FDSjtJQUNMLENBQUM7SUFDTCxhQUFDO0FBQUQsQ0FBQyxBQXJFRCxJQXFFQztBQ3JFRCxpREFBaUQ7QUFDakQsZ0RBQWdEO0FBQ2hELHdEQUF3RDtBQUN4RCxpQ0FBaUM7QUFDakMsd0NBQXdDO0FBRXhDLElBQUksTUFBTSxHQUFHLElBQUksTUFBTSxFQUFFLENBQUM7QUFFMUIsSUFBTSxnQkFBZ0IsR0FBRyxJQUFJLGdCQUFnQixDQUFDLG1CQUFtQixDQUFDLENBQUM7QUFDbkUsSUFBSSxhQUFhLENBQUMsY0FBYyxFQUFFLE1BQU0sRUFBRSxnQkFBZ0IsQ0FBQyxDQUFDO0FBQzVELElBQUksWUFBWSxDQUFDLGFBQWEsRUFBRSxNQUFNLEVBQUUsZ0JBQWdCLENBQUMsQ0FBQztBQ1YxRDtJQUFBO0lBRUEsQ0FBQztJQUFELGFBQUM7QUFBRCxDQUFDLEFBRkQsSUFFQztBQ0ZEO0lBT0ksa0JBQVksTUFBd0I7UUFGNUIsWUFBTyxHQUFhLEVBQUUsQ0FBQztRQUczQixJQUFJLENBQUMsRUFBRSxHQUFHLE1BQU0sQ0FBQyxRQUFRLENBQUMsRUFBRSxDQUFDO0lBQ2pDLENBQUM7SUFFTSx3QkFBSyxHQUFaO1FBQ0ksT0FBTyxJQUFJLENBQUMsRUFBRSxDQUFDO0lBQ25CLENBQUM7SUFFTSx5QkFBTSxHQUFiLFVBQWMsTUFBd0I7SUFFdEMsQ0FBQztJQUNMLGVBQUM7QUFBRCxDQUFDLEFBbEJELElBa0JDO0FDbEJEO0lBQUE7UUFDWSxjQUFTLEdBQWUsRUFBRSxDQUFDO0lBd0J2QyxDQUFDO0lBdEJVLCtCQUFNLEdBQWIsVUFBYyxPQUFPO1FBQ2pCLEtBQUssSUFBSSxXQUFXLElBQUksT0FBTyxFQUFFO1lBQzdCLElBQUksQ0FBQyxJQUFJLENBQUMsZ0JBQWdCLENBQUMsV0FBVyxDQUFDLEVBQUU7Z0JBQ3JDLElBQUksUUFBUSxHQUFHLElBQUksUUFBUSxDQUFDLE9BQU8sQ0FBQyxXQUFXLENBQUMsQ0FBQyxDQUFDO2dCQUNsRCxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsQ0FBQzthQUNqQztTQUNKO0lBQ0wsQ0FBQztJQUVNLGdDQUFPLEdBQWQ7UUFDSSxPQUFPLElBQUksQ0FBQyxTQUFTLENBQUM7SUFDMUIsQ0FBQztJQUVPLHlDQUFnQixHQUF4QixVQUF5QixHQUFXO1FBQ2hDLEtBQXFCLFVBQWMsRUFBZCxLQUFBLElBQUksQ0FBQyxTQUFTLEVBQWQsY0FBYyxFQUFkLElBQWM7WUFBOUIsSUFBSSxRQUFRLFNBQUE7WUFDYixJQUFJLFFBQVEsQ0FBQyxLQUFLLEVBQUUsS0FBSyxHQUFHLEVBQUU7Z0JBQzFCLE9BQU8sSUFBSSxDQUFDO2FBQ2Y7U0FDSjtRQUVELE9BQU8sS0FBSyxDQUFDO0lBQ2pCLENBQUM7SUFDTCxxQkFBQztBQUFELENBQUMsQUF6QkQsSUF5QkM7QUN6QkQ7SUFNSSxlQUFZLElBQVksRUFBRSxNQUFjO1FBQ3BDLElBQUksQ0FBQyxJQUFJLEdBQUcsSUFBSSxDQUFDO1FBQ2pCLElBQUksQ0FBQyxNQUFNLEdBQUcsTUFBTSxDQUFDO0lBQ3pCLENBQUM7SUFFTSx1QkFBTyxHQUFkO1FBQ0ksT0FBTyxJQUFJLENBQUMsSUFBSSxDQUFDO0lBQ3JCLENBQUM7SUFFTSx5QkFBUyxHQUFoQjtRQUNJLE9BQU8sSUFBSSxDQUFDLE1BQU0sQ0FBQztJQUN2QixDQUFDO0lBQ0wsWUFBQztBQUFELENBQUMsQUFsQkQsSUFrQkM7QUNsQkQsK0JBQStCO0FBQy9CO0lBSUk7UUFIUSxXQUFNLEdBQVksRUFBRSxDQUFDO1FBSXpCLElBQUksQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDLElBQUksS0FBSyxDQUFDLGFBQWEsRUFBRSxDQUFDLENBQUUsQ0FBQyxDQUFDO1FBQy9DLElBQUksQ0FBQyxNQUFNLENBQUMsSUFBSSxDQUFDLElBQUksS0FBSyxDQUFDLGNBQWMsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQ25ELENBQUM7SUFFTSxzQkFBTyxHQUFkO1FBQ0ksT0FBTyxJQUFJLENBQUMsTUFBTSxDQUFDO0lBQ3ZCLENBQUM7SUFDTCxXQUFDO0FBQUQsQ0FBQyxBQVpELElBWUMifQ==