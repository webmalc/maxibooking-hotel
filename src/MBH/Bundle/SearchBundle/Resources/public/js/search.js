var Writer = /** @class */ (function () {
    function Writer(resultId) {
        this.template = "<span>{{roomType}}</span>";
        this.countTemplate = "<span>{{count}}</span>";
        this.resultTemplate = "<p><span>{{begin}}</span>-<span>{{end}}</span>-<span> Тип Комнаты - {{roomType.name}}. Тариф {{tariff.name}}.</span>" +
            "Цены: {{#prices}} {{#showPrices}}Price {{key}} Total {{value}}{{/showPrices}} {{/prices}}" +
            "</p>";
        this.$resultsContainer = $("#" + resultId);
    }
    Writer.prototype.showStartSearch = function () {
        console.log('Search started');
    };
    Writer.prototype.showStopSearch = function () {
        console.log('Search stopped');
    };
    Writer.prototype.drawResults = function (results) {
        var _loop_1 = function (result) {
            var data = [];
            result['showPrices'] = function () {
                for (var index in this) {
                    var price = this[index];
                    data.push({
                        'key': index,
                        'value': price.total
                    });
                }
                return data;
            };
            var html = Mustache.render(this_1.resultTemplate, result);
            this_1.$resultsContainer.append($(html));
        };
        var this_1 = this;
        for (var _i = 0, results_1 = results; _i < results_1.length; _i++) {
            var result = results_1[_i];
            _loop_1(result);
        }
        // for (let result of results) {
        //     let html = this.render(result);
        //     this.$resultsContainer.append($(html));
        // }
        // const drawData = this.sortByRoomType(results);
        // for (let result of drawData) {
        //     let html = this.render(result);
        // }
        // console.log(drawData);
    };
    Writer.prototype.render = function (searchResult) {
        var view = {
            roomType: searchResult.roomType
        };
        return Mustache.render(this.template, view);
    };
    Writer.prototype.viewResult = function ($line) {
        this.$resultsContainer.append($line);
    };
    Writer.prototype.sortByRoomType = function (results) {
        var drawData = {};
        for (var _i = 0, results_2 = results; _i < results_2.length; _i++) {
            var result = results_2[_i];
            if (!drawData[result.roomType]) {
                drawData[result.roomType] = [];
            }
            drawData[result.roomType].push(result);
        }
        return drawData;
    };
    ;
    return Writer;
}());
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
        if (searchResults.length) {
            this.writer.drawResults(searchResults);
        }
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
                        resultRoute = Routing.generate('search_async_results', { id: conditionsResults.conditionsId });
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
                        start_route = Routing.generate('search_sync_start_json');
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
        this.$form = $("form[name=\"" + formName + "\"]");
        this.formName = formName;
    }
    FormDataReceiver.prototype.getSearchConditionsData = function () {
        var data;
        data = {
            begin: String(this.getFormField('begin')),
            end: String(this.getFormField('end')),
            adults: Number(this.getFormField('adults')),
            additionalBegin: Number(this.getFormField('additionalBegin')),
            additionalEnd: Number(this.getFormField('additionalEnd')),
        };
        return data;
    };
    FormDataReceiver.prototype.getFormField = function (fieldName) {
        var field = this.$form.find("#" + this.formName + "_" + fieldName);
        return field.val();
    };
    return FormDataReceiver;
}());
///<reference path="Writers/Writer.ts"/>
///<reference path="Searchers/AsyncSearcher.ts"/>
///<reference path="Searchers/SyncSearcher.ts"/>
///<reference path="DataReceivers/FormDataReceiver.ts"/>
var writer = new Writer('results');
var formDataReceiver = new FormDataReceiver('search_conditions');
new AsyncSearcher('async_search', writer, formDataReceiver);
new SyncSearcher('sync_search', writer, formDataReceiver);
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic2VhcmNoLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vcHJpdmF0ZS90cy9Xcml0ZXJzL1dyaXRlci50cyIsIi4uLy4uL3ByaXZhdGUvdHMvU2VhcmNoZXJzL1NlYXJjaGVyLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9TZWFyY2hlcnMvQXN5bmNTZWFyY2hlci50cyIsIi4uLy4uL3ByaXZhdGUvdHMvU2VhcmNoZXJzL1N5bmNTZWFyY2hlci50cyIsIi4uLy4uL3ByaXZhdGUvdHMvRGF0YVJlY2VpdmVycy9Gb3JtRGF0YVJlY2VpdmVyLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9pbmRleC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFFQTtJQU9JLGdCQUFZLFFBQWdCO1FBTHBCLGFBQVEsR0FBVywyQkFBMkIsQ0FBQztRQUMvQyxrQkFBYSxHQUFXLHdCQUF3QixDQUFDO1FBQ2pELG1CQUFjLEdBQVcsc0hBQXNIO1lBQ25KLDJGQUEyRjtZQUMzRixNQUFNLENBQUM7UUFFUCxJQUFJLENBQUMsaUJBQWlCLEdBQUcsQ0FBQyxDQUFDLE1BQUksUUFBVSxDQUFDLENBQUM7SUFDL0MsQ0FBQztJQUVNLGdDQUFlLEdBQXRCO1FBQ0ksT0FBTyxDQUFDLEdBQUcsQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDO0lBQ2xDLENBQUM7SUFFTSwrQkFBYyxHQUFyQjtRQUNJLE9BQU8sQ0FBQyxHQUFHLENBQUMsZ0JBQWdCLENBQUMsQ0FBQztJQUNsQyxDQUFDO0lBRU0sNEJBQVcsR0FBbEIsVUFBbUIsT0FBMkI7Z0NBRWxDLE1BQU07WUFDVixJQUFJLElBQUksR0FBRyxFQUFFLENBQUM7WUFDZCxNQUFNLENBQUMsWUFBWSxDQUFDLEdBQUc7Z0JBQ25CLEtBQUssSUFBSSxLQUFLLElBQUksSUFBSSxFQUFFO29CQUNwQixJQUFJLEtBQUssR0FBRyxJQUFJLENBQUMsS0FBSyxDQUFDLENBQUM7b0JBQ3hCLElBQUksQ0FBQyxJQUFJLENBQUM7d0JBQ04sS0FBSyxFQUFF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