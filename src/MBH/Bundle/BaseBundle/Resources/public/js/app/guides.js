var LS_CURRENT_GUIDE_WITH_STAGE = 'current-guide-stage';

var GUIDES_BY_PATH = {
    any: ['first-guide-1', 'start-guide-1', 'start1-guide-1'],
    "/warehouse/record": ['first-guide-2'],
    '/package': ['first-guide-1'],
    '/warehouse/record/new': ['first-guide-3'],
    '/price/room_cache': ['start-guide-2'],
    '/price/price_cache': ['start1-guide-1'],
    '/price/room_cache/generator': ['start-guide-3'],
    '/price/price_cache/generator': ['start1-guide-3']
};

var EXCLUDED_PATHS_BEGINS = ['/user'];

var GUIDES = {
    'first-guide-1': {
        steps: [
            {
                selector: '#main-menu li[icon="fa fa-usd"]',
                event: 'click',
                description: 'Click on this btn'
            },
            {
                timeout: 500,
                selector: '#main-menu li[icon="fa fa-book"]',
                event: 'click',
                description: 'Click on this btn'
            }
        ],
        next: 'first-guide-2'
    },
    'first-guide-2': {
        steps: [
            {
                timeout: 500,
                selector: '#actions button',
                event: 'click',
                description: 'Click on this btn'
            }
        ],
        next: 'first-guide-3'
    },
    'first-guide-3': {
        steps: [
            {
                'next #mbh_bundle_warehousebundle_recordtype_qtty': 'Заполните поле'
            },
            {
                'next #mbh_bundle_warehousebundle_recordtype_price': 'Заполните поле'
            },
            {
                selector: '#mbh_bundle_warehousebundle_recordtype .form-group:nth-child(3) span.select2',
                event_type: 'next',
                description: 'Поле с селектом'
            },
            {
                'next #mbh_bundle_warehousebundle_recordtype_operation': 'Поле toggle'
            },
            {
                'next #mbh_bundle_warehousebundle_recordtype_recordDate': 'Поле с датой'
            },
            {
                selector: '#mbh_bundle_warehousebundle_recordtype .form-group:nth-child(8) .bootstrap-touchspin',
                event: 'next',
                description: 'Полное поле, с тачспином по бокам'
            },
            {
                selector: '#actions button[name="save_close"]',
                event_type: 'click',
                description: 'Кнопка'
            }
        ]
    },
    'start-guide-1': {
        steps: [
            {
                selector: '#main-menu li[icon="fa fa-usd"]',
                event: 'click',
                description: 'Здравствуйте. Для начала нажмите сюда'
            },
            {
                timeout: 500,
                selector: '#main-menu li[icon="fa fa-bed"]:not(.first)',
                event: 'click',
                description: 'Далее сюда'
            }
        ],
        next: 'start-guide-2'
    },
    'start-guide-2': {
        steps: [
            {
                selector: '#actions li:nth-child(2) button',
                event: 'click',
                description: 'Для генерации номеров в продаже нажмите сюда'
            }
        ],
        next: 'start-guide-3'
    },
    'start-guide-3': {
        steps: [
            {
                'next #mbh_bundle_pricebundle_room_cache_generator_type_begin' : 'Укажите начало периода'
            },
            {
                'next #mbh_bundle_pricebundle_room_cache_generator_type_end' : 'Укажите конец периода'
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
    },
    'start1-guide-1': {
        steps: [
            {
                selector: '#main-menu li[icon="fa fa-usd"]',
                event: 'click',
                description: 'Здравствуйте. Для начала нажмите сюда'
            },
            {
                timeout: 500,
                selector: '#main-menu ul.menu-open li[icon="fa fa-usd"]',
                event: 'click',
                description: 'Далее сюда'
            }
        ],
        next: 'start1-guide-2'
    },
    'start1-guide-2': {
        steps: [
            {
                selector: '#actions li:nth-child(2) button',
                event: 'click',
                description: 'Для генерации цен нажмите сюда'
            }
        ],
        next: 'start1-guide-3'
    },
    'start1-guide-3': {
        steps: [
            {
                'next #mbh_price_bundle_price_cache_generator_begin' : 'Укажите начало периода'
            },
            {
                'next #mbh_price_bundle_price_cache_generator_end' : 'Укажите конец периода'
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

    if (guideName && (GUIDES_BY_PATH[currentPath] || GUIDES_BY_PATH.any.indexOf(guideName) > -1) && !isPathExcluded(currentPath)) {
        var guideData = GUIDES[guideName];
        var enjoyhint_instance = new EnjoyHint({
            onEnd: function () {
                clearGuidesLSData();
                if (guideData.next) {
                    writeGuidesLSData(guideData.next);
                }
            }, onStart: function () {
                writeGuidesLSData(guideName);
                $('.enjoyhint_close_btn,.enjoyhint_skip_btn').click(clearGuidesLSData);
            }
        });

        var steps = guideData.steps;
        steps['onBeforeStart'] = function () {
            setTimeout(function () {
                $('.enjoyhint_close_btn').css('top', 55);
            }, 500);
        };

        enjoyhint_instance.set(guideData.steps);
        enjoyhint_instance.run();
    } else {
        clearGuidesLSData();
    }
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
