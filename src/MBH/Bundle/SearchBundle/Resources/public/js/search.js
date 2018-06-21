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
var Receiver = /** @class */ (function () {
    function Receiver(containerId) {
        this.requestThreshold = 20;
        this.$routeName = 'search_async_results';
        this.$resultsContainer = $("#" + containerId);
    }
    Receiver.prototype.receive = function (conditionsId) {
        return __awaiter(this, void 0, void 0, function () {
            var count, request, error, route, data, err_1;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        count = 0;
                        error = false;
                        route = Routing.generate(this.$routeName, { id: conditionsId });
                        this.startReceive();
                        _a.label = 1;
                    case 1:
                        request = $.get(route);
                        _a.label = 2;
                    case 2:
                        _a.trys.push([2, 4, , 5]);
                        return [4 /*yield*/, request];
                    case 3:
                        data = _a.sent();
                        this.writeResults(data.results);
                        return [3 /*break*/, 5];
                    case 4:
                        err_1 = _a.sent();
                        error = true;
                        this.stopReceive(request);
                        return [3 /*break*/, 5];
                    case 5:
                        count++;
                        return [4 /*yield*/, new Promise(function (resolve) {
                                setTimeout(function () {
                                    resolve();
                                }, 1000);
                            })];
                    case 6:
                        _a.sent();
                        _a.label = 7;
                    case 7:
                        if (!error && count < this.requestThreshold) return [3 /*break*/, 1];
                        _a.label = 8;
                    case 8:
                        if (!error) {
                            console.log('stop receive by timeout');
                        }
                        return [2 /*return*/];
                }
            });
        });
    };
    Receiver.prototype.startReceive = function () {
        console.log('start receive');
    };
    Receiver.prototype.stopReceive = function (ajax) {
        console.log('receive stop with code');
        console.log(ajax.status);
    };
    Receiver.prototype.writeResults = function (data) {
        console.log('write the results');
        console.log(data);
    };
    return Receiver;
}());
var Searcher = /** @class */ (function () {
    function Searcher(buttonId) {
        this.route = Routing.generate('search_start_json');
        this.button = $("#" + buttonId);
        this.asyncReceiver = new Receiver('results');
        this.init();
    }
    Searcher.prototype.init = function () {
        this.bindHandlers();
    };
    Searcher.prototype.bindHandlers = function () {
        var _this = this;
        this.button.on('click', function (event) {
            event.preventDefault();
            _this.startSearch();
        });
    };
    Searcher.prototype.startSearch = function () {
        var _this = this;
        (function () { return __awaiter(_this, void 0, void 0, function () {
            var data, e_1;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        _a.trys.push([0, 2, , 3]);
                        return [4 /*yield*/, this.sendSearchData()];
                    case 1:
                        data = _a.sent();
                        console.log(data);
                        this.asyncReceiver.receive(data.conditionsId);
                        return [3 /*break*/, 3];
                    case 2:
                        e_1 = _a.sent();
                        console.log(e_1);
                        return [3 /*break*/, 3];
                    case 3: return [2 /*return*/];
                }
            });
        }); })();
    };
    Searcher.prototype.sendSearchData = function () {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                return [2 /*return*/, $.ajax({
                        url: this.route,
                        type: "POST",
                        dataType: "json",
                        data: JSON.stringify(this.getData())
                    })];
            });
        });
    };
    Searcher.prototype.getData = function () {
        var data;
        data = {
            begin: '05.08.2018',
            end: '12.08.2018',
            adults: 2
        };
        return data;
    };
    return Searcher;
}());
///<reference path="../../../../../../../node_modules/@types/jquery/index.d.ts"/>
///<reference path="Searcher.ts"/>
new Searcher('search');
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic2VhcmNoLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vcHJpdmF0ZS90cy9SZWNlaXZlci50cyIsIi4uLy4uL3ByaXZhdGUvdHMvU2VhcmNoZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL2luZGV4LnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FBQUE7SUFRSSxrQkFBWSxXQUFtQjtRQVBkLHFCQUFnQixHQUFXLEVBQUUsQ0FBQztRQUV2QyxlQUFVLEdBQVcsc0JBQXNCLENBQUM7UUFNaEQsSUFBSSxDQUFDLGlCQUFpQixHQUFHLENBQUMsQ0FBQyxNQUFJLFdBQWEsQ0FBQyxDQUFDO0lBQ2xELENBQUM7SUFFWSwwQkFBTyxHQUFwQixVQUFxQixZQUFvQjs7Ozs7O3dCQUNqQyxLQUFLLEdBQVcsQ0FBQyxDQUFDO3dCQUVsQixLQUFLLEdBQVksS0FBSyxDQUFDO3dCQUNyQixLQUFLLEdBQUcsT0FBTyxDQUFDLFFBQVEsQ0FBQyxJQUFJLENBQUMsVUFBVSxFQUFFLEVBQUMsRUFBRSxFQUFFLFlBQVksRUFBQyxDQUFDLENBQUM7d0JBQ3BFLElBQUksQ0FBQyxZQUFZLEVBQUUsQ0FBQzs7O3dCQUVoQixPQUFPLEdBQUcsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxLQUFLLENBQUMsQ0FBQzs7Ozt3QkFFUixxQkFBTSxPQUFPLEVBQUE7O3dCQUFwQixJQUFJLEdBQUcsU0FBYTt3QkFDeEIsSUFBSSxDQUFDLFlBQVksQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLENBQUM7Ozs7d0JBRWhDLEtBQUssR0FBRyxJQUFJLENBQUM7d0JBQ2IsSUFBSSxDQUFDLFdBQVcsQ0FBQyxPQUFPLENBQUMsQ0FBQzs7O3dCQUU5QixLQUFLLEVBQUUsQ0FBQzt3QkFDUixxQkFBTSxJQUFJLE9BQU8sQ0FBQyxVQUFDLE9BQU87Z0NBQ3RCLFVBQVUsQ0FBQztvQ0FDUCxPQUFPLEVBQUUsQ0FBQztnQ0FDZCxDQUFDLEVBQUUsSUFBSSxDQUFDLENBQUE7NEJBQ1osQ0FBQyxDQUFDLEVBQUE7O3dCQUpGLFNBSUUsQ0FBQzs7OzRCQUNFLENBQUMsS0FBSyxJQUFJLEtBQUssR0FBRyxJQUFJLENBQUMsZ0JBQWdCOzs7d0JBRWhELElBQUksQ0FBQyxLQUFLLEVBQUU7NEJBQ1IsT0FBTyxDQUFDLEdBQUcsQ0FBQyx5QkFBeUIsQ0FBQyxDQUFDO3lCQUMxQzs7Ozs7S0FFSjtJQUVPLCtCQUFZLEdBQXBCO1FBQ0ksT0FBTyxDQUFDLEdBQUcsQ0FBQyxlQUFlLENBQUMsQ0FBQztJQUNqQyxDQUFDO0lBRU8sOEJBQVcsR0FBbkIsVUFBb0IsSUFBSTtRQUNwQixPQUFPLENBQUMsR0FBRyxDQUFDLHdCQUF3QixDQUFDLENBQUM7UUFDdEMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUM7SUFDN0IsQ0FBQztJQUVPLCtCQUFZLEdBQXBCLFVBQXFCLElBQXFCO1FBQ3RDLE9BQU8sQ0FBQyxHQUFHLENBQUMsbUJBQW1CLENBQUMsQ0FBQztRQUNqQyxPQUFPLENBQUMsR0FBRyxDQUFDLElBQUksQ0FBQyxDQUFDO0lBQ3RCLENBQUM7SUFDTCxlQUFDO0FBQUQsQ0FBQyxBQXRERCxJQXNEQztBQ3BERDtJQUtJLGtCQUFZLFFBQWdCO1FBSnBCLFVBQUssR0FBVyxPQUFPLENBQUMsUUFBUSxDQUFDLG1CQUFtQixDQUFDLENBQUM7UUFLMUQsSUFBSSxDQUFDLE1BQU0sR0FBRyxDQUFDLENBQUMsTUFBSSxRQUFVLENBQUMsQ0FBQztRQUNoQyxJQUFJLENBQUMsYUFBYSxHQUFHLElBQUksUUFBUSxDQUFDLFNBQVMsQ0FBQyxDQUFDO1FBQzdDLElBQUksQ0FBQyxJQUFJLEVBQUUsQ0FBQztJQUNoQixDQUFDO0lBRU8sdUJBQUksR0FBWjtRQUNJLElBQUksQ0FBQyxZQUFZLEVBQUUsQ0FBQztJQUN4QixDQUFDO0lBRU8sK0JBQVksR0FBcEI7UUFBQSxpQkFLQztRQUpHLElBQUksQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDLE9BQU8sRUFBRSxVQUFBLEtBQUs7WUFDekIsS0FBSyxDQUFDLGNBQWMsRUFBRSxDQUFDO1lBQ3ZCLEtBQUksQ0FBQyxXQUFXLEVBQUUsQ0FBQztRQUN2QixDQUFDLENBQUMsQ0FBQTtJQUNOLENBQUM7SUFLTyw4QkFBVyxHQUFuQjtRQUFBLGlCQVVDO1FBVEcsQ0FBQzs7Ozs7O3dCQUVvQixxQkFBTSxJQUFJLENBQUMsY0FBYyxFQUFFLEVBQUE7O3dCQUFsQyxJQUFJLEdBQUcsU0FBMkI7d0JBQ3hDLE9BQU8sQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLENBQUM7d0JBQ2xCLElBQUksQ0FBQyxhQUFhLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxZQUFZLENBQUMsQ0FBQzs7Ozt3QkFFOUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxHQUFDLENBQUMsQ0FBQzs7Ozs7YUFFdEIsQ0FBQyxFQUFFLENBQUM7SUFDVCxDQUFDO0lBQ2EsaUNBQWMsR0FBNUI7OztnQkFDSSxzQkFBTyxDQUFDLENBQUMsSUFBSSxDQUFDO3dCQUNWLEdBQUcsRUFBRSxJQUFJLENBQUMsS0FBSzt3QkFDZixJQUFJLEVBQUUsTUFBTTt3QkFDWixRQUFRLEVBQUUsTUFBTTt3QkFDaEIsSUFBSSxFQUFFLElBQUksQ0FBQyxTQUFTLENBQUMsSUFBSSxDQUFDLE9BQU8sRUFBRSxDQUFDO3FCQUN2QyxDQUFDLEVBQUM7OztLQUNOO0lBRU8sMEJBQU8sR0FBZjtRQUNJLElBQUksSUFBeUIsQ0FBQztRQUM5QixJQUFJLEdBQUc7WUFDSCxLQUFLLEVBQUUsWUFBWTtZQUNuQixHQUFHLEVBQUUsWUFBWTtZQUNqQixNQUFNLEVBQUUsQ0FBQztTQUNaLENBQUM7UUFFRixPQUFPLElBQUksQ0FBQztJQUNoQixDQUFDO0lBRUwsZUFBQztBQUFELENBQUMsQUF4REQsSUF3REM7QUMxREQsaUZBQWlGO0FBQ2pGLGtDQUFrQztBQUNsQyxJQUFJLFFBQVEsQ0FBQyxRQUFRLENBQUMsQ0FBQyJ9