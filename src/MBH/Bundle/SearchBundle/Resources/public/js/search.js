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
        _this.requestThreshold = 10;
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
var Writer = /** @class */ (function () {
    function Writer(resultId) {
        this.template = "<p>{{roomType}}</p>";
        this.countTemplate = "<p>{{count}}</p>";
        this.$resultsContainer = $("#" + resultId);
    }
    Writer.prototype.showStartSearch = function () {
        console.log('Search started');
    };
    Writer.prototype.showStopSearch = function () {
        console.log('Search stopped');
    };
    Writer.prototype.drawResults = function (results) {
        var view = {
            count: results.length
        };
        var html = Mustache.render(this.countTemplate, view);
        this.$resultsContainer.append($(html));
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
        for (var _i = 0, results_1 = results; _i < results_1.length; _i++) {
            var result = results_1[_i];
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
///<reference path="Writer.ts"/>
///<reference path="AsyncSearcher.ts"/>
///<reference path="SyncSearcher.ts"/>
///<reference path="DataReceivers/FormDataReceiver.ts"/>
var writer = new Writer('results');
var formDataReceiver = new FormDataReceiver('search_conditions');
new AsyncSearcher('async_search', writer, formDataReceiver);
new SyncSearcher('sync_search', writer, formDataReceiver);
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic2VhcmNoLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vcHJpdmF0ZS90cy9TZWFyY2hlci50cyIsIi4uLy4uL3ByaXZhdGUvdHMvQXN5bmNTZWFyY2hlci50cyIsIi4uLy4uL3ByaXZhdGUvdHMvU3luY1NlYXJjaGVyLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9Xcml0ZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL0RhdGFSZWNlaXZlcnMvRm9ybURhdGFSZWNlaXZlci50cyIsIi4uLy4uL3ByaXZhdGUvdHMvaW5kZXgudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBRUE7SUFLSSxrQkFBc0IsUUFBZ0IsRUFBRSxNQUFjLEVBQUUsWUFBbUM7UUFDdkYsSUFBSSxDQUFDLE1BQU0sR0FBRyxDQUFDLENBQUMsTUFBSSxRQUFVLENBQUMsQ0FBQztRQUNoQyxJQUFJLENBQUMsTUFBTSxHQUFHLE1BQU0sQ0FBQztRQUNyQixJQUFJLENBQUMsa0JBQWtCLEdBQUcsWUFBWSxDQUFDO1FBQ3ZDLElBQUksQ0FBQyxZQUFZLEVBQUUsQ0FBQztJQUN4QixDQUFDO0lBRU8sK0JBQVksR0FBcEI7UUFBQSxpQkFLQztRQUpHLElBQUksQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDLE9BQU8sRUFBRSxVQUFBLEtBQUs7WUFDekIsS0FBSyxDQUFDLGNBQWMsRUFBRSxDQUFDO1lBQ3ZCLEtBQUksQ0FBQyxRQUFRLEVBQUUsQ0FBQztRQUNwQixDQUFDLENBQUMsQ0FBQTtJQUNOLENBQUM7SUFJUyxnQ0FBYSxHQUF2QjtRQUNJLElBQUksQ0FBQyxNQUFNLENBQUMsZUFBZSxFQUFFLENBQUM7SUFDbEMsQ0FBQztJQUVTLCtCQUFZLEdBQXRCLFVBQXVCLGNBQW1CO1FBQ3RDLE9BQU8sQ0FBQyxHQUFHLENBQUMsY0FBYyxDQUFDLENBQUM7UUFDNUIsSUFBSSxDQUFDLE1BQU0sQ0FBQyxjQUFjLEVBQUUsQ0FBQztJQUNqQyxDQUFDO0lBRVMsOEJBQVcsR0FBckIsVUFBc0Is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