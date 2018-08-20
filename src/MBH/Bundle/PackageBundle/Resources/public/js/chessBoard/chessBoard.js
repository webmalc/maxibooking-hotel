/*global window, $, services, document, firstTableDate, packages, leftRoomsData, noAccommodationCounts, noAccommodationIntervals*/

$(document).ready(function () {
    'use strict';
    var chessBoardManager = new ChessBoardManager(packages, leftRoomsData, noAccommodationCounts, noAccommodationIntervals);
    chessBoardManager.hangHandlers();

    // var wrapperTableResult = $('#package-new-results');
    //   wrapperTableResult.readmore({
    //     moreLink: '<div class="more-link"><a href="#">'+wrapperTableResult.attr('data-more') +' <i class="fa fa-caret-right"></i></a></div>',
    //     lessLink: '<div class="less-link"><a href="#">'+wrapperTableResult.attr('data-less') +' <i class="fa fa-caret-up"></i></a></div>',
    //     collapsedHeight: 0
    //   });
});
