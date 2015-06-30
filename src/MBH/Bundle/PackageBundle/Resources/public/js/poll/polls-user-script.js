/*global document, window, $, alert */
$(document).ready(function () {
    'use strict';
    var $hints = [
            '1 - Отвратительно', '2 - Ужасно', '3 - Плохо', '4 - Достаточно плохо', '5 - Удовлетворительно',
            '6 - Приемлемо', '7 - Достаточно хорошо', '8 - Хорошо', '9 - Очень хорошо', '10 - Превосходно!',
        ],
        cScore = 0;


    $('input.mb-rank-result').val('');

    $('.mb-rank').each(function () {
        var $id = $(this).attr('rel');
        $(this).raty({
            starOn: '/bundles/mbhpackage/images/star1.png',
            starOff: '/bundles/mbhpackage/images/star_empty.png',
            starHalf: '/bundles/mbhpackage/images/star_half.png',
            cancelOn: '/bundles/mbhpackage/images/trash_on.png',
            cancelOff: '/bundles/mbhpackage/images/trash.png',
            half: false,
            cancel: true,
            cancelPlace: 'left',
            cancelHint: 'Сбросить оценку',
            cancelClass: 'mb-rate-cancel',
            number: 10,
            target: '.mb-rank-result-screen[rel="' + $id + '"]',
            hints: $hints,
            targetKeep: true,
            targetText: '0 - Без ответа',
            targetFormat: '{score}',
            click: function (score, evt) {
                cScore = Math.ceil(score / 2);
                $(this).raty('set', {'starOn': '/bundles/mbhpackage/images/star' + cScore + '.png'});
                $('.mb-rank-result-screen[rel="' + $(this).attr('rel') + '"]').html('****').removeClass().addClass('mb-rank-result-screen mb-rank-result-screen-' + cScore + '');
                $('#mb-rank-result-' + $(this).attr('rel')).val(score);
            }
        });
    });

    $('.mb-rank-submit').click(function () {
        var cnt = $('.mb-rank-result[value!=""]').length;
        if (cnt < 6) {
            alert('Пожалуйста, выставите вашу оценку по крайней мере по 5 пунктам! \nБольшое спасибо!');
            return;
        }
        $('#mb-rank-form').submit();
    });

});