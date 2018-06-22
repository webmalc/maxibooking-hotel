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
        _this.requestThreshold = 2;
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
                        _a.trys.push([1, 11, , 12]);
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
                        requestResults = $.get(resultRoute);
                        _a.label = 4;
                    case 4:
                        _a.trys.push([4, 6, , 7]);
                        return [4 /*yield*/, requestResults];
                    case 5:
                        data = _a.sent();
                        this.drawResults(data);
                        return [3 /*break*/, 7];
                    case 6:
                        err_1 = _a.sent();
                        error = true;
                        this.onStopSearch(requestResults);
                        return [3 /*break*/, 7];
                    case 7:
                        count++;
                        return [4 /*yield*/, new Promise(function (resolve) {
                                setTimeout(function () {
                                    resolve();
                                }, 1000);
                            })];
                    case 8:
                        _a.sent();
                        _a.label = 9;
                    case 9:
                        if (!error && count < this.requestThreshold) return [3 /*break*/, 3];
                        _a.label = 10;
                    case 10:
                        if (!error) {
                            console.log('Stop async receive by threshold.');
                        }
                        return [3 /*break*/, 12];
                    case 11:
                        e_1 = _a.sent();
                        this.onStopSearch(ajax);
                        return [3 /*break*/, 12];
                    case 12: return [2 /*return*/];
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
        this.$resultsContainer = $("#" + resultId);
    }
    Writer.prototype.showStartSearch = function () {
        console.log('Search started');
    };
    Writer.prototype.showStopSearch = function () {
        console.log('Search stopped');
    };
    Writer.prototype.drawResults = function (results) {
        for (var _i = 0, results_1 = results; _i < results_1.length; _i++) {
            var result = results_1[_i];
            var html = this.render(result);
            this.$resultsContainer.append($(html));
        }
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
            adults: Number(this.getFormField('adults')) /*,
            children: this.getFormField('children'),
            childrenAges: this.getFormField('childrenAges'),
            additionalBegin: this.getFormField('additionalBegin'),
            additionalEnd: this.getFormField('additionalEnd'),
            roomTypes: this.getFormField('roomTypes'),*/
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic2VhcmNoLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vcHJpdmF0ZS90cy9TZWFyY2hlci50cyIsIi4uLy4uL3ByaXZhdGUvdHMvQXN5bmNTZWFyY2hlci50cyIsIi4uLy4uL3ByaXZhdGUvdHMvU3luY1NlYXJjaGVyLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9Xcml0ZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL0RhdGFSZWNlaXZlcnMvRm9ybURhdGFSZWNlaXZlci50cyIsIi4uLy4uL3ByaXZhdGUvdHMvaW5kZXgudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBRUE7SUFLSSxrQkFBc0IsUUFBZ0IsRUFBRSxNQUFjLEVBQUUsWUFBbUM7UUFDdkYsSUFBSSxDQUFDLE1BQU0sR0FBRyxDQUFDLENBQUMsTUFBSSxRQUFVLENBQUMsQ0FBQztRQUNoQyxJQUFJLENBQUMsTUFBTSxHQUFHLE1BQU0sQ0FBQztRQUNyQixJQUFJLENBQUMsa0JBQWtCLEdBQUcsWUFBWSxDQUFDO1FBQ3ZDLElBQUksQ0FBQyxZQUFZLEVBQUUsQ0FBQztJQUN4QixDQUFDO0lBRU8sK0JBQVksR0FBcEI7UUFBQSxpQkFLQztRQUpHLElBQUksQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDLE9BQU8sRUFBRSxVQUFBLEtBQUs7WUFDekIsS0FBSyxDQUFDLGNBQWMsRUFBRSxDQUFDO1lBQ3ZCLEtBQUksQ0FBQyxRQUFRLEVBQUUsQ0FBQztRQUNwQixDQUFDLENBQUMsQ0FBQTtJQUNOLENBQUM7SUFJUyxnQ0FBYSxHQUF2QjtRQUNJLElBQUksQ0FBQyxNQUFNLENBQUMsZUFBZSxFQUFFLENBQUM7SUFDbEMsQ0FBQztJQUVTLCtCQUFZLEdBQXRCLFVBQXVCLGNBQW1CO1FBQ3RDLE9BQU8sQ0FBQyxHQUFHLENBQUMsY0FBYyxDQUFDLENBQUM7UUFDNUIsSUFBSSxDQUFDLE1BQU0sQ0FBQyxjQUFjLEVBQUUsQ0FBQztJQUNqQyxDQUFDO0lBRVMsOEJBQVcsR0FBckIsVUFBc0IsSUFBSTtRQUN0QixJQUFNLGFBQWEsR0FBdUIsSUFBSSxDQUFDLE9BQU8sQ0FBQztRQUN2RCxJQUFJLENBQUMsTUFBTSxDQUFDLFdBQVcsQ0FBQyxhQUFhLENBQUMsQ0FBQztJQUMzQyxDQUFDO0lBRVMsc0NBQW1CLEdBQTdCO1FBQ0ksT0FBUSxJQUFJLENBQUMsa0JBQWtCLENBQUMsdUJBQXVCLEVBQUUsQ0FBQztRQUcxRCxFQUFFO1FBQ0YsNEJBQTRCO1FBQzVCLFdBQVc7UUFDWCwyQkFBMkI7UUFDM0IseUJBQXlCO1FBQ3pCLGdCQUFnQjtRQUNoQixLQUFLO1FBQ0wsRUFBRTtRQUNGLGVBQWU7SUFDbkIsQ0FBQztJQUVMLGVBQUM7QUFBRCxDQUFDLEFBbERELElBa0RDOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FDcERELGtDQUFrQztBQUNsQztJQUE0QixpQ0FBUTtJQUloQyx1QkFBbUIsUUFBZ0IsRUFBRSxNQUFjLEVBQUUsWUFBbUM7UUFBeEYsWUFDSSxrQkFBTSxRQUFRLEVBQUUsTUFBTSxFQUFFLFlBQVksQ0FBQyxTQUN4QztRQUpnQixzQkFBZ0IsR0FBVyxDQUFDLENBQUM7O0lBSTlDLENBQUM7SUFFZSxnQ0FBUSxHQUF4Qjs7Ozs7O3dCQUNJLElBQUksQ0FBQyxhQUFhLEVBQUUsQ0FBQzt3QkFDZixXQUFXLEdBQUcsT0FBTyxDQUFDLFFBQVEsQ0FBQyxvQkFBb0IsQ0FBQyxDQUFDOzs7O3dCQUd2RCxJQUFJLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FBQzs0QkFDVixHQUFHLEVBQUUsV0FBVzs0QkFDaEIsSUFBSSxFQUFFLE1BQU07NEJBQ1osUUFBUSxFQUFFLE1BQU07NEJBQ2hCLElBQUksRUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxtQkFBbUIsRUFBRSxDQUFDO3lCQUNuRCxDQUFDLENBQUM7d0JBQ3VCLHFCQUFNLElBQUksRUFBQTs7d0JBQTlCLGlCQUFpQixHQUFHLFNBQVU7d0JBQ2hDLEtBQUssR0FBVyxDQUFDLENBQUM7d0JBQ2xCLGNBQWMsU0FBQSxDQUFDO3dCQUNmLEtBQUssR0FBWSxLQUFLLENBQUM7d0JBQ3JCLFdBQVcsR0FBRyxPQUFPLENBQUMsUUFBUSxDQUFDLHNCQUFzQixFQUFFLEVBQUMsRUFBRSxFQUFFLGlCQUFpQixDQUFDLFlBQVksRUFBQyxDQUFDLENBQUM7Ozt3QkFFL0YsY0FBYyxHQUFHLENBQUMsQ0FBQyxHQUFHLENBQUMsV0FBVyxDQUFDLENBQUM7Ozs7d0JBRW5CLHFCQUFNLGNBQWMsRUFBQTs7d0JBQTNCLElBQUksR0FBRyxTQUFvQjt3QkFDakMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxJQUFJLENBQUMsQ0FBQzs7Ozt3QkFFdkIsS0FBSyxHQUFHLElBQUksQ0FBQzt3QkFDYixJQUFJLENBQUMsWUFBWSxDQUFDLGNBQWMsQ0FBQyxDQUFDOzs7d0JBRXRDLEtBQUssRUFBRSxDQUFDO3dCQUNSLHFCQUFNLElBQUksT0FBTyxDQUFDLFVBQUMsT0FBTztnQ0FDdEIsVUFBVSxDQUFDO29DQUNQLE9BQU8sRUFBRSxDQUFDO2dDQUNkLENBQUMsRUFBRSxJQUFJLENBQUMsQ0FBQTs0QkFDWixDQUFDLENBQUMsRUFBQTs7d0JBSkYsU0FJRSxDQUFDOzs7NEJBQ0UsQ0FBQyxLQUFLLElBQUksS0FBSyxHQUFHLElBQUksQ0FBQyxnQkFBZ0I7Ozt3QkFDaEQsSUFBSSxDQUFDLEtBQUssRUFBRTs0QkFDUixPQUFPLENBQUMsR0FBRyxDQUFDLGtDQUFrQyxDQUFDLENBQUM7eUJBQ25EOzs7O3dCQUVELElBQUksQ0FBQyxZQUFZLENBQUMsSUFBSSxDQUFDLENBQUM7Ozs7OztLQUUvQjtJQUNMLG9CQUFDO0FBQUQsQ0FBQyxBQS9DRCxDQUE0QixRQUFRLEdBK0NuQztBQ2hERDtJQUEyQixnQ0FBUTtJQUUvQixzQkFBbUIsUUFBZ0IsRUFBRSxNQUFjLEVBQUUsWUFBbUM7ZUFDcEYsa0JBQU0sUUFBUSxFQUFFLE1BQU0sRUFBRSxZQUFZLENBQUM7SUFDekMsQ0FBQztJQUVlLCtCQUFRLEdBQXhCOzs7Ozs7d0JBRVUsV0FBVyxHQUFHLE9BQU8sQ0FBQyxRQUFRLENBQUMsd0JBQXdCLENBQUMsQ0FBQzs7Ozt3QkFFM0QsSUFBSSxHQUFHLENBQUMsQ0FBQyxJQUFJLENBQUM7NEJBQ1YsR0FBRyxFQUFFLFdBQVc7NEJBQ2hCLElBQUksRUFBRSxNQUFNOzRCQUNaLFFBQVEsRUFBRSxNQUFNOzRCQUNoQixJQUFJLEVBQUUsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsbUJBQW1CLEVBQUUsQ0FBQzt5QkFDbkQsQ0FBQyxDQUFDO3dCQUNVLHFCQUFNLElBQUksRUFBQTs7d0JBQWpCLElBQUksR0FBRyxTQUFVO3dCQUN2QixJQUFJLENBQUMsV0FBVyxDQUFDLElBQUksQ0FBQyxDQUFDOzs7O3dCQUV2QixJQUFJLENBQUMsWUFBWSxDQUFDLEdBQUMsQ0FBQyxDQUFDOzs7Ozs7S0FJNUI7SUFHTCxtQkFBQztBQUFELENBQUMsQUExQkQsQ0FBMkIsUUFBUSxHQTBCbEM7QUN4QkQ7SUFHSSxnQkFBWSxRQUFnQjtRQURwQixhQUFRLEdBQVcscUJBQXFCLENBQUM7UUFFN0MsSUFBSSxDQUFDLGlCQUFpQixHQUFHLENBQUMsQ0FBQyxNQUFJLFFBQVUsQ0FBQyxDQUFDO0lBQy9DLENBQUM7SUFFTSxnQ0FBZSxHQUF0QjtRQUNJLE9BQU8sQ0FBQyxHQUFHLENBQUMsZ0JBQWdCLENBQUMsQ0FBQztJQUNsQyxDQUFDO0lBRU0sK0JBQWMsR0FBckI7UUFDSSxPQUFPLENBQUMsR0FBRyxDQUFDLGdCQUFnQixDQUFDLENBQUM7SUFDbEMsQ0FBQztJQUVNLDRCQUFXLEdBQWxCLFVBQW1CLE9BQTJCO1FBRTFDLEtBQW1CLFVBQU8sRUFBUCxtQkFBTyxFQUFQLHFCQUFPLEVBQVAsSUFBTztZQUFyQixJQUFJLE1BQU0sZ0JBQUE7WUFDWCxJQUFJLElBQUksR0FBRyxJQUFJLENBQUMsTUFBTSxDQUFDLE1BQU0sQ0FBQyxDQUFDO1lBQy9CLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUM7U0FDMUM7UUFDRCxpREFBaUQ7UUFDakQsaUNBQWlDO1FBQ2pDLHNDQUFzQztRQUN0QyxJQUFJO1FBQ0oseUJBQXlCO0lBQzdCLENBQUM7SUFFTyx1QkFBTSxHQUFkLFVBQWUsWUFBOEI7UUFDekMsSUFBSSxJQUFJLEdBQUc7WUFDUCxRQUFRLEVBQUUsWUFBWSxDQUFDLFFBQVE7U0FDbEMsQ0FBQztRQUVGLE9BQU8sUUFBUSxDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsUUFBUSxFQUFFLElBQUksQ0FBQyxDQUFDO0lBQ2hELENBQUM7SUFFTywyQkFBVSxHQUFsQixVQUFtQixLQUFhO1FBQzVCLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxNQUFNLENBQUMsS0FBSyxDQUFDLENBQUM7SUFDekMsQ0FBQztJQUVPLCtCQUFjLEdBQXRCLFVBQXVCLE9BQTJCO1FBQzlDLElBQUksUUFBUSxHQUE0QyxFQUFFLENBQUM7UUFDM0QsS0FBbUIsVUFBTyxFQUFQLG1CQUFPLEVBQVAscUJBQU8sRUFBUCxJQUFPO1lBQXJCLElBQUksTUFBTSxnQkFBQTtZQUNYLElBQUksQ0FBQyxRQUFRLENBQUMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxFQUFFO2dCQUM1QixRQUFRLENBQUMsTUFBTSxDQUFDLFFBQVEsQ0FBQyxHQUFHLEVBQUUsQ0FBQzthQUNsQztZQUNELFFBQVEsQ0FBQyxNQUFNLENBQUMsUUFBUSxDQUFDLENBQUMsSUFBSSxDQUFDLE1BQU0sQ0FBQyxDQUFFO1NBQzNDO1FBRUQsT0FBTyxRQUFRLENBQUM7SUFDcEIsQ0FBQztJQUFBLENBQUM7SUFDTixhQUFDO0FBQUQsQ0FBQyxBQW5ERCxJQW1EQztBQ3JERDtJQUlJLDBCQUFZLFFBQWdCO1FBQ3hCLElBQUksQ0FBQyxLQUFLLEdBQUcsQ0FBQyxDQUFDLGlCQUFjLFFBQVEsUUFBSSxDQUFDLENBQUM7UUFDM0MsSUFBSSxDQUFDLFFBQVEsR0FBRyxRQUFRLENBQUM7SUFDN0IsQ0FBQztJQUVNLGtEQUF1QixHQUE5QjtRQUNJLElBQUksSUFBb0IsQ0FBQztRQUN6QixJQUFJLEdBQUc7WUFDSCxLQUFLLEVBQUUsTUFBTSxDQUFDLElBQUksQ0FBQyxZQUFZLENBQUMsT0FBTyxDQUFDLENBQUM7WUFDekMsR0FBRyxFQUFFLE1BQU0sQ0FBQyxJQUFJLENBQUMsWUFBWSxDQUFDLEtBQUssQ0FBQyxDQUFDO1lBQ3JDLE1BQU0sRUFBRSxNQUFNLENBQUMsSUFBSSxDQUFDLFlBQVksQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFBOzs7Ozt3REFLQztTQUMvQyxDQUFDO1FBRUYsT0FBTyxJQUFJLENBQUM7SUFDaEIsQ0FBQztJQUVPLHVDQUFZLEdBQXBCLFVBQXFCLFNBQWlCO1FBQ2xDLElBQUksS0FBSyxHQUFHLElBQUksQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLE1BQUksSUFBSSxDQUFDLFFBQVEsU0FBSSxTQUFXLENBQUMsQ0FBQztRQUM5RCxPQUFPLEtBQUssQ0FBQyxHQUFHLEVBQUUsQ0FBQztJQUN2QixDQUFDO0lBRUwsdUJBQUM7QUFBRCxDQUFDLEFBOUJELElBOEJDO0FDOUJELGdDQUFnQztBQUNoQyx1Q0FBdUM7QUFDdkMsc0NBQXNDO0FBQ3RDLHdEQUF3RDtBQUd4RCxJQUFNLE1BQU0sR0FBRyxJQUFJLE1BQU0sQ0FBQyxTQUFTLENBQUMsQ0FBQztBQUNyQyxJQUFNLGdCQUFnQixHQUFHLElBQUksZ0JBQWdCLENBQUMsbUJBQW1CLENBQUMsQ0FBQztBQUNuRSxJQUFJLGFBQWEsQ0FBQyxjQUFjLEVBQUUsTUFBTSxFQUFFLGdCQUFnQixDQUFDLENBQUM7QUFDNUQsSUFBSSxZQUFZLENBQUMsYUFBYSxFQUFFLE1BQU0sRUFBRSxnQkFBZ0IsQ0FBQyxDQUFDIn0=