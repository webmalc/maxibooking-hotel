/*global document, window, $, alert */
$(document).ready(function () {
    'use strict';
    var $hints = [
            '1 -' + Translator.trans("polls_user_script.disgusting"),
            '2 -' + Translator.trans("polls_user_script.terribble"),
            '3 -' + Translator.trans("polls_user_script.bad"),
            '4 -' + Translator.trans("polls_user_script.bad_enogh"),
            '5 -' + Translator.trans("polls_user_script.passably"),
            '6 -' + Translator.trans("polls_user_script.acceptable"),
            '7 -' + Translator.trans("polls_user_script.good_enough"),
            '8 -' + Translator.trans("polls_user_script.good"),
            '9 -' + Translator.trans("polls_user_script.very_good"),
            '10 -' + Translator.trans("polls_user_script.excellent") + '!'
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
            cancelHint: Translator.trans("polls_user_script.reset_rating"),
            cancelClass: 'mb-rate-cancel',
            number: 10,
            target: '.mb-rank-result-screen[rel="' + $id + '"]',
            hints: $hints,
            targetKeep: true,
            targetText: '0 - ' + Translator.trans("polls_user_script.no_answer"),
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
            alert(Translator.trans("polls_user_script.please_set_rating") + ' \n' + Translator.trans("polls_user_script.many_thanks") + '!');
            return;
        }
        $('#mb-rank-form').submit();
    });

});