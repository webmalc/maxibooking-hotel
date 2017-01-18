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
                var tmp = fromIndex
                fromIndex = toIndex;
                toIndex = tmp;
            }

            return [fromIndex, toIndex];
        };
        $('.header-action-copy').click(function (event) {
            event.preventDefault();
            var from = $('.copy-from');
            var fromTd = $(this).closest('td');

            if ($(this).closest('td').hasClass('copy-from')) {
                clear();
            } else if (from.length) {
                clear();
                var tr = $(this).closest('tr');
                var fromInputs = $('table td.content:nth-child('+ (from.index() + 1) +') input'),
                    indexes = getIndexes(from, fromTd);
                for (var i = (indexes[0] + 1); i <= indexes[1] + 1; i++) {
                    $('table tr td.content:nth-child('+ i +') input').each(function (index) {
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
                console.log();
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