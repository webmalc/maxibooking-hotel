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
            _this.doSpecialSearch();
            _this.doSearch();
        });
    };
    Searcher.prototype.doSpecialSearch = function () {
        return __awaiter(this, void 0, void 0, function () {
            var ajax, special_route, data, e_1;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        special_route = Routing.generate('search_specials');
                        _a.label = 1;
                    case 1:
                        _a.trys.push([1, 3, , 4]);
                        ajax = $.ajax({
                            url: special_route,
                            type: "html",
                            data: JSON.stringify(this.getSearchConditions())
                        });
                        return [4 /*yield*/, ajax];
                    case 2:
                        data = _a.sent();
                        this.drawSpecialResults(data);
                        return [3 /*break*/, 4];
                    case 3:
                        e_1 = _a.sent();
                        console.error('Ошибка получеия спец предложений однако');
                        console.log(e_1);
                        return [3 /*break*/, 4];
                    case 4: return [2 /*return*/];
                }
            });
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
    };
    Searcher.prototype.drawSpecialResults = function (data) {
        this.writer.drawSpecialResults(data);
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
            var start_route, ajax, conditionsResults, count, requestResults, error, resultRoute, data, err_1, e_2;
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
                        e_2 = _a.sent();
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
            var ajax, start_route, data, e_3;
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
                        e_3 = _a.sent();
                        this.onStopSearch({ status: 'error', message: e_3 });
                        return [3 /*break*/, 4];
                    case 4: return [2 /*return*/];
                }
            });
        });
    };
    return SyncSearcher;
}(Searcher));
///<reference path="../../../../../../../../node_modules/@types/bootstrap-switch/index.d.ts"/>
var FormDataReceiver = /** @class */ (function () {
    function FormDataReceiver(formName) {
        this.$form = $("form[name=\"" + formName + "\"]");
        this.formName = formName;
        this.$children = $('input#search_conditions_children');
        this.$childrenAgeHolder = $('#search_conditions_childrenAges');
        this.agesTemplate = this.$childrenAgeHolder.data('prototype');
        this.$addTouristButton = $('#add-tourist');
        this.bindHandlers();
        this.checkChildrenAges();
    }
    FormDataReceiver.prototype.bindHandlers = function () {
        var _this = this;
        this.$children.on('input', function (e) {
            _this.checkMaxValue($(e.target));
            _this.updateChildrenAges();
            _this.checkChildrenAges();
        });
        //Выключаем туристов, нужно их выносить во VueJs
        // this.$addTouristButton.on('click', (e) => {
        //     this.initGuestModal(e);
        // });
    };
    FormDataReceiver.prototype.initGuestModal = function (e) {
        var guestModal = $('#add-guest-modal'), form = guestModal.find('form'), button = $('#add-guest-modal-submit'), errors = $('#add-guest-modal-errors');
        e.preventDefault();
        guestModal.modal('show');
        button.click(function () {
            errors.hide();
            $.post(form.prop('action'), form.serialize(), function (data) {
                if (data.error) {
                    errors.html(data.text).show();
                }
                else {
                    $('.findGuest').append($("<option/>", {
                        value: data.id,
                        text: data.text
                    })).val(data.id).trigger('change');
                    form.trigger('reset');
                    //form.find('select').select2('data', null);
                    guestModal.modal('hide');
                    form.find('select').select2('data');
                    //form.find('input').select2('data', null);
                    return 1;
                }
            });
        });
    };
    FormDataReceiver.prototype.checkMaxValue = function ($childrenField) {
        var currentValue = $childrenField.val();
        var maxValue = Number($childrenField.attr('max'));
        var minValue = Number($childrenField.attr('min'));
        if (currentValue > maxValue) {
            $childrenField.val(maxValue).trigger('input');
        }
        if (currentValue < minValue) {
            $childrenField.val(minValue).trigger('input');
        }
    };
    FormDataReceiver.prototype.checkChildrenAges = function () {
        if (this.getChildrenCount()) {
            $('.children_age_holder').fadeIn();
        }
        else {
            $('.children_age_holder').fadeOut();
        }
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
            begin: String(this.getFormFieldValue('begin')),
            end: String(this.getFormFieldValue('end')),
            adults: Number(this.getFormFieldValue('adults')),
            additionalBegin: Number(this.getFormFieldValue('additionalBegin')),
            additionalEnd: Number(this.getFormFieldValue('additionalEnd')),
            tariffs: this.getFormFieldValue('tariffs'),
            roomTypes: this.getFormFieldValue('roomTypes'),
            hotels: this.getFormFieldValue('hotels'),
            children: Number(this.getFormFieldValue('children')),
            childrenAges: this.getChildrenAges(),
            order: Number(this.getFormFieldValue('order')),
            isForceBooking: this.getFormField('isForceBooking').bootstrapSwitch('state'),
            isSpecialStrict: this.getFormField('isSpecialStrict').bootstrapSwitch('state'),
        };
        return data;
    };
    FormDataReceiver.prototype.getFormField = function (fieldName) {
        return this.$form.find("#" + this.formName + "_" + fieldName);
    };
    FormDataReceiver.prototype.getFormFieldValue = function (fieldName) {
        var field = this.getFormField(fieldName);
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
            props: ['count', 'quantity'],
            template: "<td>\n                        <select v-model=\"selected\" @change=\"$emit('quantity', selected)\" class=\"form-control quantity-select input-xxs\">\n                            <option v-for=\"value in (1, count)\" :value=\"value\">{{ value }}</option>\n                        </select>\n                       </td>",
            mounted: function () {
                this.$emit('quantity', this.selected);
            },
            data: function () {
                return {
                    selected: this.quantity
                };
            }
        });
        Vue.component('prices', {
            props: ['prices', 'defaultPriceIndex'],
            template: "<td class=\"text-center\">\n                     <select v-model=\"selected\" @change=\"$emit('price-index-update', selected)\" class=\"form-control plain-html input-sm search-tourists-select\">\n                            <option v-for=\"(price, key) in prices\" :value=\"key\"><span>{{price.searchAdults}} \u0432\u0437\u0440.</span><span v-if=\"price.searchChildren\">+{{price.searchChildren}} \u0440\u0435\u0431.</span></option>\n                        </select>\n                    </td>",
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
            template: "<tr :class=\"{success: isAdditionalDate}\">\n                    <td class=\"text-center table-icon\"><i class=\"fa fa-paper-plane-o\"></i></td>\n                    <td>{{begin}}-{{end}}<br><small>{{night}} \u043D\u043E\u0447\u0435\u0439</small></td>\n                    <td is=\"tariff\" :tariff=\"result.resultTariff\" :freeRooms=\"minRooms\"></td>\n                    <td is=\"count\" :count=\"minRooms\" :quantity=\"quantity\" @quantity=\"quantityUpdate($event)\"></td>\n                    <td is=\"prices\" :prices=\"result.prices\" :defaultPriceIndex=\"currentPriceIndex\" @price-index-update=\"priceIndexUpdate($event)\"></td>\n                    <td is=\"total-price\" :price=\"result.prices[currentPriceIndex]\" :tariffName=\"result.resultTariff.name\"></td>\n                    <td is=\"package-link\" :link=\"getLink()\" :roomsCount=\"minRooms\" data-toggle=\"tooltip\" @click.native=\"$emit('booking', quantity)\"></td>\n            </tr>",
            computed: {
                begin: function () {
                    var begin = moment(this.result.begin);
                    return begin.format('DD MMM');
                },
                end: function () {
                    var end = moment(this.result.end);
                    return end.format('DD MMM');
                },
                night: function () {
                    var begin = moment.utc(this.result.begin);
                    var end = moment.utc(this.result.end);
                    return moment.duration(end.diff(begin)).days();
                },
                isAdditionalDate: function () {
                    var conditionBegin = this.result.resultConditions.begin;
                    var begin = this.result.begin;
                    var conditionEnd = this.result.resultConditions.end;
                    var end = this.result.end;
                    return (conditionBegin == begin) && (conditionEnd == end);
                },
                minRooms: function () {
                    return this.result.minRoomsCount;
                }
            },
            methods: {
                getLink: function () {
                    var begin = this.result.begin;
                    var end = this.result.end;
                    var tariff = this.result.resultTariff.id;
                    var roomType = this.result.resultRoomType.id;
                    var adults = this.result.prices[this.currentPriceIndex].adults;
                    var children = this.result.prices[this.currentPriceIndex].children;
                    var childrenAges = this.result.resultConditions.childrenAges;
                    var order = this.result.resultConditions.order;
                    var forceBooking = this.result.resultConditions.forceBooking;
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
                        forceBooking: forceBooking
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
                };
            }
        });
        Vue.component('room-type', {
            props: ['roomType', 'results'],
            template: "<tbody>\n                           <tr class=\"mbh-grid-header1 info\"><td colspan=\"8\">{{roomType.name}}: {{roomType.hotelName}}</td></tr>\n                           <tr @booking=\"booking($event)\" is=\"result\" v-for=\"(result, key) in sortedResults\" :key=\"key\" :result=\"result\"></tr>\n                       </tbody>\n                        ",
            methods: {
                booking: function (count) {
                    for (var index in this.results) {
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
            }
        }
    };
    Writer.prototype.drawSpecialResults = function (data) {
        var $holder = $('#specials');
        $holder.empty();
        $holder.append($(data));
        // let $specialWrapper = $holder.find('#package-new-search-special-wrapper');
        // $specialWrapper.readmore({
        //     moreLink: '<div class="more-link"><a href="#">'+$specialWrapper.attr('data-more') +' <i class="fa fa-caret-right"></i></a></div>',
        //     lessLink: '<div class="less-link"><a href="#">'+$specialWrapper.attr('data-less') +' <i class="fa fa-caret-up"></i></a></div>',
        //     collapsedHeight: 230
        // });
        var $specialTouristSelect = $holder.find('.search-special-tourist-select');
        var $specialPrice = $holder.find('.special-price');
        var $specialLinks = $holder.find('a.booking-special-apply');
        $specialTouristSelect.select2({
            placeholder: '',
            allowClear: false,
            width: 'element'
        }).on('change.select2', function () {
            $(this).closest('td').siblings('td').find('span.special-price').html($(this).val());
        });
        $.each($specialPrice, function () {
            $(this).html($(this).closest('td').siblings('td').find('select.search-special-tourist-select').val());
        });
        $specialLinks.on('click', function (event) {
            event.preventDefault();
            var relatedSelect = $(this).closest('td').siblings('td').find('select.search-special-tourist-select option:selected');
            var linkAdults = relatedSelect.data('adults');
            var linkChildren = relatedSelect.data('children');
            var bookingUrl = Routing.generate('special_booking', {
                'id': $(this).data('id'),
                'adults': linkAdults,
                'children': linkChildren
            });
            window.open(bookingUrl);
        });
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoic2VhcmNoLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vcHJpdmF0ZS90cy9TZWFyY2hlcnMvU2VhcmNoZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL1NlYXJjaGVycy9Bc3luY1NlYXJjaGVyLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9TZWFyY2hlcnMvU3luY1NlYXJjaGVyLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9EYXRhUmVjZWl2ZXJzL0Zvcm1EYXRhUmVjZWl2ZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL1dyaXRlcnMvV3JpdGVyLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9pbmRleC50cyIsIi4uLy4uL3ByaXZhdGUvdHMvUmVzdWx0L1Jlc3VsdC50cyIsIi4uLy4uL3ByaXZhdGUvdHMvUmVzdWx0L1Jvb21UeXBlLnRzIiwiLi4vLi4vcHJpdmF0ZS90cy9SZXN1bHQvUm9vbVR5cGVIb2xkZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL1NlYXJjaGVycy9TcGVjaWFsU2VhcmNoZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL3RlbXAvSW5uZXIudHMiLCIuLi8uLi9wcml2YXRlL3RzL3RlbXAvV3JhcC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQUVBO0lBS0ksa0JBQXNCLFFBQWdCLEVBQUUsTUFBYyxFQUFFLFlBQW1DO1FBQ3ZGLElBQUksQ0FBQyxNQUFNLEdBQUcsQ0FBQyxDQUFDLE1BQUksUUFBVSxDQUFDLENBQUM7UUFDaEMsSUFBSSxDQUFDLE1BQU0sR0FBRyxNQUFNLENBQUM7UUFDckIsSUFBSSxDQUFDLGtCQUFrQixHQUFHLFlBQVksQ0FBQztRQUN2QyxJQUFJLENBQUMsWUFBWSxFQUFFLENBQUM7SUFDeEIsQ0FBQztJQUVPLCtCQUFZLEdBQXBCO1FBQUEsaUJBTUM7UUFMRyxJQUFJLENBQUMsTUFBTSxDQUFDLEVBQUUsQ0FBQyxPQUFPLEVBQUUsVUFBQSxLQUFLO1lB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