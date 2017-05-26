/*global window, $, services, document, firstTableDate, packages, leftRoomsData, noAccommodationCounts, noAccommodationIntervals*/
var DATE_ELEMENT_WIDTH = 47;

$(document).ready(function () {
    'use strict';
    var chessBoardManager = new ChessBoardManager(packages, leftRoomsData, noAccommodationCounts, noAccommodationIntervals);
    chessBoardManager.hangHandlers();
});
