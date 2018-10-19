$(document).ready(function () {
    var $addImageInput = $('#mbhhotel_bundle_hotel_image_type_imageFile, #image_imageFile');
    $addImageInput.change(function () {
        $addImageInput.closest('form').submit();
    });

    handleCreationOfNewRoomType();
    handleCreationOfNewHotel();
    drawLinesBetweenFlowTabs();
    setInterval(drawLinesBetweenFlowTabs, 1500);

    $('#go-to-site_with-save').click(function () {
        $.ajax({
            method: "GET",
            url: Routing.generate('change_color_theme', {colorTheme: $('#mb_site_flow_colorTheme').val()})
        });
    });

    initKeyWordsForm();
});

function drawLinesBetweenFlowTabs() {
    $('.line-between-flow-tabs').remove();
    var $stepTabs = $('.flow-step-tab');
    var numberOfSteps = $stepTabs.length;
    var $activeTab = $stepTabs.filter('.active');
    var numberOfActiveStep = $stepTabs.index($activeTab);

    var firstTab = $stepTabs.get(0);
    var lineWidth = 10;
    var tabWidth = parseFloat(getComputedStyle(firstTab).width);
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

function handleCreationOfNewRoomType() {
    $('#add-roomtype-button').click(function () {
        var route = Routing.generate('room_type_short_create', {hotelId: $('#flow-hotel-id').val()});
        var initFormFunc = function(response) {
            $('#modal-with-form-body').html(response.data.form);
        };
        var handleSuccessFunc = function(result) {
            return handleSuccessNewFlowItemWithProgressRate(result, $('#mbhhotel_bundle_room_type_flow_roomType'));
        };

        onOpenModalWithFormButtonClick(route, handleSuccessFunc, initFormFunc);
    });
}

function handleCreationOfNewHotel() {
    $('#add-hotel-button').click(function () {
        var route = Routing.generate('hotel_short_create');
        var handleSuccessFunc = function(result) {
            var newRoomData = result['data'];
            var $hotelSelect = $('#mbhhotel_bundle_room_type_flow_hotel');
            if ($hotelSelect.length === 1) {
                addAndSetSelect2Option($('#mbhhotel_bundle_room_type_flow_hotel'), newRoomData['id'], newRoomData['name']);
            } else {
                var $hotelRadioInputs = $('#mbhhotel_bundle_hotel_flow_hotel');
                handleSuccessNewFlowItemWithProgressRate(result, $hotelRadioInputs);
            }

        };
        var initFormFunc = function(response) {
            $('#modal-with-form-body').html(response.data.form);
        };

        onOpenModalWithFormButtonClick(route, handleSuccessFunc, initFormFunc);
    });
}

var handleSuccessNewFlowItemWithProgressRate = function(result, $radioField) {
    var data = result['data'];
    var $lastChoice = $radioField.find('.row:last');
    var $newChoice;

    if ($lastChoice.parent().css('display') === 'none') {
        $lastChoice.parent().show();
        $newChoice = $lastChoice;
        $('.alert.alert-warning').remove();
    } else {
        $newChoice = $lastChoice.clone();
        $newChoice.insertAfter($lastChoice);
    }
    var $newChoiceRateBlock = $newChoice.find('.alert');

    $newChoice
        .find('input')
        .val(data['id'])
        .attr('checked', true);
    $newChoice
        .find('span')
        .html(data['name']);
    $newChoiceRateBlock.html('0%');

    if (!$newChoiceRateBlock.hasClass('alert-danger')) {
        $newChoiceRateBlock
            .removeClass('alert-success')
            .removeClass('alert-warning')
            .addClass('alert-danger');
    }
};