/*global window, $, document, Translator, searchProcess */

var LS_CURRENT_GUIDE_WITH_STAGE = 'current-guide-stage';

var GUIDES_BY_PATH = {
    any: ['first-guide-1', 'room-cache-1', 'price-cache-1', 'search-guide-1'],
    "/warehouse/record": ['first-guide-2'],
    '/package': ['first-guide-1'],
    '/warehouse/record/new': ['first-guide-3'],
    '/price/room_cache': ['room-cache-2'],
    '/price/price_cache': ['price-cache-2'],
    '/price/room_cache/generator': ['room-cache-3'],
    '/price/price_cache/generator': ['price-cache-3'],
    '/package/search': ['search-guide-2', 'search-guide-3-v1', 'search-guide-3-v2']
};

var INCLUDED_PATHS_BEGINS = ['/package/order'];
var EXCLUDED_PATHS_BEGINS = ['/user'];

var GUIDES = {
    'room-cache-1': {
        getSteps: function () {
            return [
                {
                    selector: '#main-menu li.dropdown:eq(1)',
                    event: 'click',
                    description: 'Для начала нажмите сюда'
                },
                {
                    timeout: 500,
                    selector: '#main-menu li.dropdown:eq(1) li:eq(2)',
                    event: 'click',
                    description: 'Далее сюда'
                }
            ]
        },
        next: 'room-cache-2'
    },
    'room-cache-2': {
        getSteps: function () {
            return [
                {
                    selector: '#actions li:nth-child(2) button',
                    event: 'click',
                    description: 'Для генерации номеров в продаже нажмите сюда'
                }
            ]
        },
        next: 'room-cache-3'
    },
    'room-cache-3': {
        getSteps: function () {
            return [
                {
                    'next #mbh_bundle_pricebundle_room_cache_generator_type_begin': 'Укажите начало периода'
                },
                {
                    'next #mbh_bundle_pricebundle_room_cache_generator_type_end': 'Укажите конец периода'
                },
                {
                    selector: '#mbh_bundle_pricebundle_room_cache_generator_type .form-group:nth-child(3) span.select2',
                    event_type: 'next',
                    description: 'Можно указать определенные дни недели'
                },
                {
                    selector: '#mbh_bundle_pricebundle_room_cache_generator_type .form-group:nth-child(4) span.select2',
                    event_type: 'next',
                    description: 'Укажите типы номеров'
                },
                {
                    selector: '#mbh_bundle_pricebundle_room_cache_generator_type .form-group:nth-child(7) .bootstrap-touchspin',
                    event_type: 'next',
                    description: 'Укажите количество номеров'
                },
                {
                    selector: '#actions li:first-child button',
                    event: 'click',
                    description: 'Нажмите кнопку'
                }
            ]
        }
    },
    'price-cache-1': {
        getSteps: function () {
            return [
                {
                    selector: '#main-menu li.dropdown:eq(1)',
                    event: 'click',
                    description: 'Для начала нажмите сюда'
                },
                {
                    timeout: 500,
                    selector: '#main-menu li.dropdown:eq(1) li:eq(3)',
                    event: 'click',
                    description: 'Далее сюда'
                }
            ]
        },
        next: 'price-cache-2'
    },
    'price-cache-2': {
        getSteps: function () {
            return [
                {
                    selector: '#actions li:nth-child(2) button',
                    event: 'click',
                    description: 'Для генерации цен нажмите сюда'
                }
            ]
        },
        next: 'price-cache-3'
    },
    'price-cache-3': {
        getSteps: function () {
            return [
                {
                    'next #mbh_price_bundle_price_cache_generator_begin': 'Укажите начало периода'
                },
                {
                    'next #mbh_price_bundle_price_cache_generator_end': 'Укажите конец периода'
                },
                {
                    selector: '#mbh_price_bundle_price_cache_generator .form-group:nth-child(4) span.select2',
                    event_type: 'next',
                    description: 'Укажите типы номеров'
                },
                {
                    selector: '#mbh_price_bundle_price_cache_generator .form-group:nth-child(5) span.select2',
                    event_type: 'next',
                    description: 'Укажите тарифы'
                },
                {
                    selector: '#mbh_price_bundle_price_cache_generator .form-group:nth-child(1) .bootstrap-touchspin',
                    event_type: 'next',
                    description: 'Укажите цену'
                },
                {
                    selector: '#actions li:first-child button',
                    event: 'click',
                    description: 'Нажмите кнопку'
                }
            ]
        }
    },
    'search-guide-1': {
        getSteps: function () {
            return [
                {
                    selector: '#main-menu li[icon="fa fa-search"]',
                    event: 'click',
                    description: 'Забронировать номер для клиента можно здесь'
                }
            ]
        },
        next: 'search-guide-2'
    },
    'search-guide-2': {
        getSteps: function () {
            var steps = [
                {
                    selector: '#package-search-form .input:eq(0) input.daterangepicker-input',
                    event_type: 'next',
                    description: 'Укажите даты брони'
                },
                {
                    selector: '#s_adults',
                    event_type: 'next',
                    description: 'Укажите количество взрослых'
                },
                {
                    selector: '#s_children',
                    event_type: 'next',
                    description: 'и детей'
                },
                {
                    selector: '#package-search-form .input:eq(5) span.select2-container',
                    event_type: 'next',
                    description: 'Здесь можно выбрать тип номера'
                }
            ];

            if ($('#search-submit-button').length) {
                steps.push({
                    selector: '#search-submit-button',
                    event: 'click',
                    description: 'Нажмите для поиска вариантов бронирования'
                });
            }

            return steps;
        },
        onEnd: function () {
            var checkForCompletenessAndRunGuide = function () {
                setTimeout(function () {
                    if (searchProcess) {
                        checkForCompletenessAndRunGuide()
                    } else {
                        if ($('.package-search-book').length > 0) {
                            runGuide('search-guide-3-v1')
                        } else {
                            runGuide('search-guide-3-v2')
                        }
                    }
                }, 650);
            };
            checkForCompletenessAndRunGuide();
        }
    },
    'search-guide-3-v1': {
        getSteps: function () {
            return [
                {
                    selector: '.package-search-book',
                    event: 'click',
                    description: 'Нажмите, чтобы забронировать'
                }
            ]
        },
        next: 'package-payer'
    },
    'search-guide-3-v2': {
        getSteps: function () {
            return [
                {
                    selector: '#package-search-results-wrapper .alert-warning',
                    event_type: 'next',
                    //TODO: Тут текст нужен
                    description: 'Не найдены варианты бронирования.'
                }
            ]
        }
    },
    'package-payer': {
        getSteps: function () {
            return [
                {
                    selector: '#mbh_bundle_packagebundle_package_order_tourist_type_lastName',
                    event_type: 'next',
                    description: 'Укажите фамилию плательщика'
                },
                {
                    selector: '#mbh_bundle_packagebundle_package_order_tourist_type_firstName',
                    event_type: 'next',
                    description: 'Укажите имя плательщика'
                },
                {
                    selector: '#actions button[name="save_close"]',
                    event: 'click',
                    description: 'Сохраните'
                }
            ]
        }
    }
};

function runGuide(guideName) {
    var devAddressStr = '/app_dev.php';
    guideName = guideName || localStorage.getItem(LS_CURRENT_GUIDE_WITH_STAGE);
    var currentPath = location.pathname.indexOf(devAddressStr) > -1 ?
        location.pathname.substr(devAddressStr.length)
        : location.pathname;

    if ((currentPath.length - 1) === currentPath.lastIndexOf('/')) {
        currentPath = currentPath.substring(0, currentPath.length - 1)
    }
    if (guideName &&
        ((GUIDES_BY_PATH[currentPath] && GUIDES_BY_PATH[currentPath].indexOf(guideName) > -1) || GUIDES_BY_PATH.any.indexOf(guideName) > -1 || isPathBeginsFromIncluded(currentPath))
        && !isPathExcluded(currentPath)
    ) {
        var guideData = GUIDES[guideName];
        var enjoyhint_instance = new EnjoyHint({
            onEnd: function () {
                clearGuidesLSData();
                if (guideData.next) {
                    writeGuidesLSData(guideData.next);
                }
                if (guideData.onEnd) {
                    guideData.onEnd();
                }
            }, onStart: function () {
                writeGuidesLSData(guideName);
                $('.enjoyhint_close_btn,.enjoyhint_skip_btn').click(clearGuidesLSData);
            }
        });
        var steps = guideData.getSteps();
        if (steps[0].selector && steps[0].selector.indexOf('#main-menu') === 0
            && $(steps[0].selector).hasClass('active')
            && $(steps[0].selector).hasClass('dropdown')
        ) {
            steps.splice(0, 1);
        }

        steps.forEach(function (stepData) {
            stepData['nextButton'] = {text: Translator.trans('guides.next_button.title')};
            stepData['skipButton'] = {text: Translator.trans('guides.skip_button.title')};
        });

        steps[0]['onBeforeStart'] = function () {
            if (localStorage.getItem('sidebar-collapse') === 'close') {
                $('.sidebar-toggle').trigger('click');
            }
            setTimeout(function () {
                $('.enjoyhint_close_btn').css('top', 55);
            }, 500);
        };

        enjoyhint_instance.set(steps);
        enjoyhint_instance.run();
    } else {
        clearGuidesLSData();
    }
}

function isPathBeginsFromIncluded(currentPath) {
    var isBegins = false;
    INCLUDED_PATHS_BEGINS.forEach(function (pathBegin) {
        if (currentPath.indexOf(pathBegin) === 0) {
            isBegins = true;
        }
    });

    return isBegins;
}

function isPathExcluded(path) {
    var isExcluded = false;
    EXCLUDED_PATHS_BEGINS.forEach(function (excludedPath) {
        if (path.indexOf(excludedPath) === 0) {
            isExcluded = true;
        }
    });

    return isExcluded;
}

function clearGuidesLSData() {
    localStorage.removeItem(LS_CURRENT_GUIDE_WITH_STAGE);
}

function writeGuidesLSData(guideWithStage) {
    localStorage.setItem(LS_CURRENT_GUIDE_WITH_STAGE, guideWithStage);
}
