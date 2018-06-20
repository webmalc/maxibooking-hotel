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
        this.$routeName = 'search_async_results';
        this.$resultsContainer = $("#" + containerId);
    }
    Receiver.prototype.receive = function (conditionsId) {
        var _this = this;
        var route = Routing.generate(this.$routeName, { id: conditionsId });
        var func = function () { return __awaiter(_this, void 0, void 0, function () {
            var ajax, data, e_1;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        _a.trys.push([0, 2, , 3]);
                        ajax = $.get(route);
                        return [4 /*yield*/, ajax];
                    case 1:
                        data = _a.sent();
                        console.log(data);
                        return [2 /*return*/, ajax.status];
                    case 2:
                        e_1 = _a.sent();
                        console.error(e_1);
                        return [3 /*break*/, 3];
                    case 3: return [2 /*return*/];
                }
            });
        }); };
        var n = 10;
        var _loop_1 = function () {
            var statusCode;
            (function () { return __awaiter(_this, void 0, void 0, function () {
                return __generator(this, function (_a) {
                    switch (_a.label) {
                        case 0: return [4 /*yield*/, func()];
                        case 1:
                            statusCode = _a.sent();
                            console.log('statuscode');
                            return [2 /*return*/];
                    }
                });
            }); })();
            n--;
            console.log('problem');
        };
        do {
            _loop_1();
        } while (n >= 0);
    };
    Receiver.prototype.writeResults = function () {
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
            var data, e_2;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        _a.trys.push([0, 2, , 3]);
                        return [4 /*yield*/, this.sendSearchData()];
                    case 1:
                        data = _a.sent();
                        this.asyncReceiver.receive(data.conditionsId);
                        return [3 /*break*/, 3];
                    case 2:
                        e_2 = _a.sent();
                        console.log(e_2);
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic2VhcmNoLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vcHJpdmF0ZS90cy9SZWNlaXZlci50cyIsIi4uLy4uL3ByaXZhdGUvdHMvU2VhcmNoZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL2luZGV4LnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0FBQUE7SUFPSSxrQkFBWSxXQUFtQjtRQUx2QixlQUFVLEdBQVcsc0JBQXNCLENBQUM7UUFNaEQsSUFBSSxDQUFDLGlCQUFpQixHQUFHLENBQUMsQ0FBQyxNQUFJLFdBQWEsQ0FBQyxDQUFDO0lBQ2xELENBQUM7SUFDTSwwQkFBTyxHQUFkLFVBQWUsWUFBb0I7UUFBbkMsaUJBeUJDO1FBeEJHLElBQU0sS0FBSyxHQUFHLE9BQU8sQ0FBQyxRQUFRLENBQUMsSUFBSSxDQUFDLFVBQVUsRUFBRSxFQUFDLEVBQUUsRUFBRSxZQUFZLEVBQUMsQ0FBQyxDQUFDO1FBRXBFLElBQUksSUFBSSxHQUFHOzs7Ozs7d0JBRUMsSUFBSSxHQUFHLENBQUMsQ0FBQyxHQUFHLENBQUMsS0FBSyxDQUFDLENBQUM7d0JBQ2IscUJBQU0sSUFBSSxFQUFBOzt3QkFBakIsSUFBSSxHQUFHLFNBQVU7d0JBQ3JCLE9BQU8sQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLENBQUM7d0JBQ2xCLHNCQUFPLElBQUksQ0FBQyxNQUFNLEVBQUM7Ozt3QkFFbkIsT0FBTyxDQUFDLEtBQUssQ0FBQyxHQUFDLENBQUMsQ0FBQTs7Ozs7YUFFdkIsQ0FBQztRQUNGLElBQUksQ0FBQyxHQUFVLEVBQUUsQ0FBQzs7WUFHZCxJQUFJLFVBQWtCLENBQUM7WUFDdkIsQ0FBQzs7O2dDQUNnQixxQkFBTSxJQUFJLEVBQUUsRUFBQTs7NEJBQXpCLFVBQVUsR0FBRyxTQUFZLENBQUM7NEJBQzFCLE9BQU8sQ0FBQyxHQUFHLENBQUMsWUFBWSxDQUFDLENBQUM7Ozs7aUJBQzdCLENBQUMsRUFBRSxDQUFDO1lBQ0wsQ0FBQyxFQUFFLENBQUM7WUFDSixPQUFPLENBQUMsR0FBRyxDQUFDLFNBQVMsQ0FBQyxDQUFDO1FBQzNCLENBQUM7UUFSRDs7aUJBUVMsQ0FBQyxJQUFJLENBQUMsRUFBQztJQUVwQixDQUFDO0lBRU8sK0JBQVksR0FBcEI7SUFFQSxDQUFDO0lBQ0wsZUFBQztBQUFELENBQUMsQUF4Q0QsSUF3Q0M7QUN0Q0Q7SUFLSSxrQkFBWSxRQUFnQjtRQUpwQixVQUFLLEdBQVcsT0FBTyxDQUFDLFFBQVEsQ0FBQyxtQkFBbUIsQ0FBQyxDQUFDO1FBSzFELElBQUksQ0FBQyxNQUFNLEdBQUcsQ0FBQyxDQUFDLE1BQUksUUFBVSxDQUFDLENBQUM7UUFDaEMsSUFBSSxDQUFDLGFBQWEsR0FBRyxJQUFJLFFBQVEsQ0FBQyxTQUFTLENBQUMsQ0FBQztRQUM3QyxJQUFJLENBQUMsSUFBSSxFQUFFLENBQUM7SUFDaEIsQ0FBQztJQUVPLHVCQUFJLEdBQVo7UUFDSSxJQUFJLENBQUMsWUFBWSxFQUFFLENBQUM7SUFDeEIsQ0FBQztJQUVPLCtCQUFZLEdBQXBCO1FBQUEsaUJBS0M7UUFKRyxJQUFJLENBQUMsTUFBTSxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsVUFBQSxLQUFLO1lBQ3pCLEtBQUssQ0FBQyxjQUFjLEVBQUUsQ0FBQztZQUN2QixLQUFJLENBQUMsV0FBVyxFQUFFLENBQUM7UUFDdkIsQ0FBQyxDQUFDLENBQUE7SUFDTixDQUFDO0lBS08sOEJBQVcsR0FBbkI7UUFBQSxpQkFTQztRQVJHLENBQUM7Ozs7Ozt3QkFFb0IscUJBQU0sSUFBSSxDQUFDLGNBQWMsRUFBRSxFQUFBOzt3QkFBbEMsSUFBSSxHQUFHLFNBQTJCO3dCQUN4QyxJQUFJLENBQUMsYUFBYSxDQUFDLE9BQU8sQ0FBQyxJQUFJLENBQUMsWUFBWSxDQUFDLENBQUM7Ozs7d0JBRTlDLE9BQU8sQ0FBQyxHQUFHLENBQUMsR0FBQyxDQUFDLENBQUM7Ozs7O2FBRXRCLENBQUMsRUFBRSxDQUFDO0lBQ1QsQ0FBQztJQUNhLGlDQUFjLEdBQTVCOzs7Z0JBQ0ksc0JBQU8sQ0FBQyxDQUFDLElBQUksQ0FBQzt3QkFDVixHQUFHLEVBQUUsSUFBSSxDQUFDLEtBQUs7d0JBQ2YsSUFBSSxFQUFFLE1BQU07d0JBQ1osUUFBUSxFQUFFLE1BQU07d0JBQ2hCLElBQUksRUFBRSxJQUFJLENBQUMsU0FBUyxDQUFDLElBQUksQ0FBQyxPQUFPLEVBQUUsQ0FBQztxQkFDdkMsQ0FBQyxFQUFDOzs7S0FDTjtJQUVPLDBCQUFPLEdBQWY7UUFDSSxJQUFJLElBQXlCLENBQUM7UUFDOUIsSUFBSSxHQUFHO1lBQ0gsS0FBSyxFQUFFLFlBQVk7WUFDbkIsR0FBRyxFQUFFLFlBQVk7WUFDakIsTUFBTSxFQUFFLENBQUM7U0FDWixDQUFDO1FBRUYsT0FBTyxJQUFJLENBQUM7SUFDaEIsQ0FBQztJQUVMLGVBQUM7QUFBRCxDQUFDLEFBdkRELElBdURDO0FDekRELGlGQUFpRjtBQUNqRixrQ0FBa0M7QUFDbEMsSUFBSSxRQUFRLENBQUMsUUFBUSxDQUFDLENBQUMifQ==