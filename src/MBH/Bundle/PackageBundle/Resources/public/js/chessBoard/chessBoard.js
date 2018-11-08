/*global window, $, services, document, firstTableDate, packages, leftRoomsData, noAccommodationCounts, noAccommodationIntervals*/

$(document).ready(function () {
    'use strict';
    var chessBoardManager = new ChessBoardManager();
    chessBoardManager.hangHandlers();
});
