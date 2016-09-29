var $hotelSelect = $("#search_form_hotel");
var $roomTypeSelect = $("#search_form_roomType");

var roomTypeList = [];
var updateRoomList = function () {
    var $options = $roomTypeSelect.find("option");
    $options.each(function () {
        var roomType = {
            id: this.value,
            title: this.innerHTML,
            hotel: this.getAttribute('data-hotel')
        };
        if (!roomType.id || !roomType.hotel) {
            return;
        }
        roomTypeList.push(roomType);
    });
};

var updateRoomListView = function (roomTypes) {
    if(!roomTypes.length) {
        $roomTypeSelect.prop("disabled", true);
    } else {
        $roomTypeSelect.prop("disabled", false);
    }
    var html = '<option value="">Все типы номеров</option>';
    roomTypes.forEach(function (roomType) {
        html += '<option value="' + roomType.id + '">' + roomType.title + '</option>';
    });
    $roomTypeSelect.html(html);
};

var getRoomTypesByHotel = function (hotelID) {
    return roomTypeList.filter(function (roomType) {
        return roomType.hotel == hotelID;
    })
};

var updateSelectView = function () {
    var hotelID = $hotelSelect.val();
    var roomTypes = [];
    if (hotelID) {
        roomTypes = getRoomTypesByHotel(hotelID);
    }

    updateRoomListView(roomTypes);
};

updateRoomList();
updateSelectView();

if ($roomTypeSelect.data('value') && !$roomTypeSelect.val()) {
    $roomTypeSelect.val($roomTypeSelect.data('value'));
}

$hotelSelect.on("change", updateSelectView);

var $beginInput = $('#search_form_begin');
var $endInput = $('#search_form_end');
var $rangeInput = $('#search_form_range');
$rangeInput.daterangepicker({
    locale: {
        applyLabel: 'Принять',
        cancelLabel: 'Отмена',
        format: 'DD.MM.YYYY'
    },
    autoApply: true,
    language: "ru"
});

// $("#form_begin, #form_end").datepicker({language: "ru"});

$rangeInput.on("apply.daterangepicker", function (ev, picker) {
    $beginInput.val(picker.startDate.format('DD.MM.YYYY'));
    $endInput.val(picker.endDate.format('DD.MM.YYYY'));
});

$beginInput.val($rangeInput.data('daterangepicker').startDate.format('DD.MM.YYYY'));
$endInput.val($rangeInput.data('daterangepicker').endDate.format('DD.MM.YYYY'));

$rangeInput.on('cancel.daterangepicker', function (ev, picker) {
    $beginInput.val("");
    $endInput.val("");
});

var dateRangePicker = $rangeInput.data("daterangepicker");
if ($beginInput.val()) {
    dateRangePicker.setStartDate($beginInput.val());
}
if ($endInput.val()) {
    dateRangePicker.setEndDate($endInput.val());
}

var $children = $("#search_form_children");
var $childrenIcon = $("#children-icon");
$childrenIcon.popover({
    html: true,
    content: ""

});
var childrenValues = $childrenIcon.data("value");
if (!childrenValues) {
    childrenValues = [];
}
$childrenIcon.on("shown.bs.popover", function () {
    var popoverContent = $childrenIcon.next('div.popover').children('div.popover-content');
    popoverContent.find('input').on('change', function () {
        childrenValues = [];
        popoverContent.find('input').each(function (index, input) {
            childrenValues.push(input.value);
        })
    });
});

var popoverShow = false;
$childrenIcon.on('hide.bs.popover', function () {
    popoverShow = false;
});

$children.on('keyup mouseup', function (e) {
    var value = parseInt($children.val());
    if (value && Math.min(value, 5) != value) {
        value = 5;
        $children.val(value);
    }

    if (isNaN(value)) {
        value = 0;
    }
    if (value) {
        var content = "";
        for (var i = 0; i < value; i++) {
            var inputValue = childrenValues[i];
            if (undefined === inputValue) {
                inputValue = 0;
            }
            content = content + '<input name="search_form[children_age][' + i + ']" type="number" value="' + inputValue + '" min="1" max="18" class="form-control input-sm" style="display: inline-block">';
        }
        if (content) {
            content = '<div>' + content + '</div>';
        }

        var popover = $childrenIcon.data('bs.popover');
        popover.options.content = content;
        var popoverContent = $childrenIcon.next('div.popover').children('div.popover-content');
        popoverContent.html(content);
        if (!popoverShow) {
            $childrenIcon.popover('show');
            popoverShow = true
        }
    } else {
        $childrenIcon.popover('hide');
    }
});

if (childrenValues) {
    setTimeout(function () {
        $children.trigger('change');
    }, 1000)
}
