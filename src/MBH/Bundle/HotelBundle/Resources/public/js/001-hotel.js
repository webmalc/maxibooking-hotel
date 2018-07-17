/*global window, $, document, Routing, console, select2Text */
$(document).ready(function () {
    'use strict';

    //spinners
    $('#mbh_bundle_hotelbundle_hoteltype_saleDays').TouchSpin({
        min: 1,
        max: 365,
        step: 1,
        boostat: 5,
        maxboostedstep: 10
    });
    $('.spinner').TouchSpin({
        min: 0,
        max: 9007199254740992,
        step: 1,
        boostat: 5,
        maxboostedstep: 10
    });

    $("#mbh_bundle_hotelbundle_hotel_extended_type_rating").TouchSpin({
        min: 1,
        max: 5,
        step: 1,
        boostat: 1,
        maxboostedstep: 1
    });

    drawLinesBetweenFlowTabs();
});

function drawLinesBetweenFlowTabs() {
    var $linesBetweenFlowTabs = $('.line-between-flow-tabs');
    if ($linesBetweenFlowTabs.length === 0) {
        return;
    }
    var top;
    var tabWidthPlusLineWidth = 60 + 10;
    var containerWidth = parseInt(getComputedStyle($linesBetweenFlowTabs.get(0).parentNode).width, 10);
    var numberOfLinesOnLine = Math.floor(containerWidth / tabWidthPlusLineWidth);

    $linesBetweenFlowTabs.each(function (lineNumber, line) {
        if (typeof top === "undefined") {
            top = parseInt(getComputedStyle(line).top, 10);
        }

        var left = (lineNumber % numberOfLinesOnLine) * tabWidthPlusLineWidth;
        line.style.top = top + 'px';
        line.style.left = left + 'px';

        if (left + 2 * tabWidthPlusLineWidth > containerWidth) {
            var clonedLine = line.cloneNode();
            clonedLine.style.top = top + 'px';
            clonedLine.style.left = left + tabWidthPlusLineWidth + 'px';
            $linesBetweenFlowTabs.parent().append(clonedLine);
            top += 61;
        }
    });
}