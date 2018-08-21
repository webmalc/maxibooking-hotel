$(document).ready(function () {
    var $addImageInput = $('#mbhhotel_bundle_hotel_image_type_imageFile');
    $addImageInput.change(function () {
        $addImageInput.closest('form').submit();
    });
    drawLinesBetweenFlowTabs();
    setInterval(drawLinesBetweenFlowTabs, 500);
});

function drawLinesBetweenFlowTabs() {
    $('.line-between-flow-tabs').remove()
    var $stepTabs = $('.flow-step-tab');
    var numberOfSteps = $stepTabs.length;
    var numberOfActiveStep = $stepTabs.index($('.active'));

    var firstTab = $stepTabs.get(0);
    var lineWidth = 10;
    var tabWidth = parseInt(getComputedStyle(firstTab).width, 10);
    var tabHeight = parseInt(getComputedStyle(firstTab).height, 10);
    var tabWidthPlusLineWidth = tabWidth + lineWidth;

    var top = Math.ceil(tabHeight / 2);
    var container = document.getElementById('tabs-container');
    var containerWidth = parseInt(getComputedStyle(container).width, 10);
    var numberOfLinesOnLine = Math.floor(containerWidth / tabWidthPlusLineWidth);

    function getTemplateLine(lineIndex) {
        var line = document.createElement('div');
        line.classList.add('line-between-flow-tabs');
        if (lineIndex > numberOfActiveStep) {
            line.classList.add('line-to-not-passed');
        }

        return line;
    }

    for (var lineIndex = 0; lineIndex < numberOfSteps; lineIndex++) {
        var line = getTemplateLine(lineIndex);
        var left = (lineIndex % numberOfLinesOnLine) * tabWidthPlusLineWidth;
        line.style.top = top + 'px';
        line.style.left = left + 'px';
        container.appendChild(line);

        if (left + 2 * tabWidthPlusLineWidth > containerWidth) {
            var clonedLine = line.cloneNode();
            clonedLine.style.left = left + tabWidthPlusLineWidth + 'px';
            container.appendChild(clonedLine);
            top += tabHeight + 5;
        }
    }
}