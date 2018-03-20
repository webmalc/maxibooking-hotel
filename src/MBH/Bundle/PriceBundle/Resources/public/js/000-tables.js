/*global window, $, document, mbh */

var mbhGridCopy = function () {
    'use strict';
    //th copy function
    (function () {
        var clear = function () {
            $('.copy-from').removeClass('copy-from');
            $('.header-action-generator').removeClass('hidden');
            $('.header-td').removeClass('header-selected-td warning');
        };
        var getIndexes = function (fromObject, toObject) {
            var fromIndex = fromObject.index();
            var toIndex = toObject.index();
            if (toIndex < fromIndex) {
                var tmp = fromIndex;
                fromIndex = toIndex;
                toIndex = tmp;
            }

            return [fromIndex, toIndex];
        };
        $('.header-action-generator').click(onCopyToGeneratorButtonClick);

        $('.header-action-copy').click(function (event) {
            event.preventDefault();
            var from = $('.copy-from');
            var fromTd = $(this).closest('td');

            if ($(this).closest('td').hasClass('copy-from')) {
                clear();
            } else if (from.length) {
                clear();
                var tr = $(this).closest('tr');
                var fromInputs = $('table tr[data-copy-row-id="' + tr.attr('data-copy-row-id') + '"] td.content:nth-child('+ (from.index() + 1) +') input'),
                    indexes = getIndexes(from, fromTd);
                for (var i = (indexes[0] + 1); i <= indexes[1] + 1; i++) {
                    $('table tr[data-copy-row-id="' + tr.attr('data-copy-row-id') + '"] td.content:nth-child('+ i +') input').each(function (index) {
                        var fromInput = fromInputs.eq(index);
                        if (fromInput.length && fromInput.hasClass('mbh-grid-input')) {
                            $(this).val(fromInput.val());
                        }
                        if (fromInput.length && fromInput.hasClass('mbh-grid-checkbox')) {
                            $(this).prop("checked", fromInput.prop('checked') );
                        }
                        $(this).prop('disabled', false);
                    });
                }
            } else {
                $('.header-action-generator').addClass('hidden');
                fromTd.addClass('copy-from');
            }
        });

        $('.header-td').hover(function () {
            $('.header-td').removeClass('header-selected-td warning');
            var from = $('.copy-from');
            var tr = $('.copy-from').closest('tr');
            if (!from.length) {
                return;
            }
            var indexes = getIndexes(from, $(this));
            $('tr:nth-child('+ (tr.index() + 1) +') .header-td').slice(indexes[0] - 1, indexes[1]).addClass('header-selected-td warning');
        });

        $(document).keyup(function(e) {
            if (e.keyCode === 27) {
                clear();
            }
        });
    }());
};

function onCopyToGeneratorButtonClick() {
    var $tdElement = $(this).closest('td');
    var columnNumber = $tdElement.index();
    var $table = $tdElement.closest('table');
    var $rows = $table.find('tr');
    var data = {};
    var rowOffset = 3;
    var generatorTypeName;
    var numberOfDataRows;
    switch ($table.attr('id')) {
        case 'price-cache-overview-table':
            generatorTypeName = 'price-generator';
            numberOfDataRows = 5;
            break;
        case 'restriction-overview-table':
            generatorTypeName = 'restriction-generator';
            numberOfDataRows = 11;
            break;
        case 'room-cache-overview-table':
            generatorTypeName = 'rooms-generator';
            numberOfDataRows = 1;
            break;
    }
    for (var i = rowOffset; i < numberOfDataRows + rowOffset; i++) {
        var $priceDataInput = $rows.eq(i).find('td').eq(columnNumber).find('input');
        var inputName = $priceDataInput.attr('name');
        var inputVal = $priceDataInput.attr('type') === 'text' ? $priceDataInput.val() : $priceDataInput.prop('checked');
        data[inputName.substr(inputName.lastIndexOf('['))] = inputVal;
    }

    localStorage.setItem(generatorTypeName, JSON.stringify(data));
}

function setGeneratorData() {
    var generatorTypeName;
    var formName = $('form').attr('name');
    if (formName) {
        switch (formName) {
            case 'mbh_price_bundle_price_cache_generator':
                generatorTypeName = 'price-generator';
                break;
            case 'mbh_bundle_pricebundle_room_cache_generator_type':
                generatorTypeName = 'rooms-generator';
                break;
            case 'mbh_bundle_pricebundle_restriction_generator_type':
                generatorTypeName = 'restriction-generator';
                break;
        }
        if (!generatorTypeName) {
            return;
        }
        var data = JSON.parse(localStorage.getItem(generatorTypeName));
        for (var fieldName in data) {
            var $input = $('input[name="' + formName + fieldName + '"]');
            if ($input.length === 1) {
                switch ($input.attr('type')) {
                    case 'hidden':
                        $('input[name="' + formName + fieldName.replace(']', 'Fake]"]')).val(data[fieldName]);
                    case 'text':
                        $input.val(data[fieldName]);
                        break;
                    case 'checkbox':
                        $input.bootstrapSwitch('state', data[fieldName]);
                        break;
                }
            }
        }
        localStorage.removeItem(generatorTypeName);
    }
}