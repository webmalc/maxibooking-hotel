/*global window, $, services, document, firstTableDate, packages, leftRoomsData, noAccommodationCounts, moment, Routing */
var PACKAGE_ELEMENT_HEIGHT = 41;
var DATE_ELEMENT_WIDTH = 47;
var PACKAGE_TO_MIDDAY_OFFSET = 20;

//TODO: Как обрабатывать ошибки ajax?
//TODO: Мб скрывать боковое меню при переходе на вкладку шахматки?
//TODO: Если не находятся возможности бронирования, мб указывать что-то другое, а не "по вашему запросу ничего не найдено"?
//TODO: Разобраться с дейтпикером
//TODO: Мб переименовать размещение в шахматку?

$(document).ready(function () {
    'use strict';
    var chessBoardManager = new ChessBoardManager(packages, leftRoomsData, noAccommodationCounts);
    chessBoardManager.hangHandlers();
});
