var $hotelSelect = $('#hotel');
var $roomTypeSelect = $('#roomType');

var roomTypeList = [];
var updateRoomList = function() {
    var $options = $roomTypeSelect.find('option');
    $options.each(function(){
        var roomType = {
            id: this.value,
            title: this.innerHTML,
            hotel: this.getAttribute('data-hotel')
        }
        if(!roomType.id || !roomType.hotel) {
            return;
        }
        roomTypeList.push(roomType);
    })
}

var updateRoomListView = function(roomTypes) {
    var html = '<option value=""></option>';
    roomTypes.forEach(function(roomType) {
        html += '<option value="'+roomType.id+'">'+roomType.title+'</option>';
    });
    $roomTypeSelect.html(html);
}

var getRoomTypesByHotel = function(hotelID)
{
    return roomTypeList.filter(function(roomType){
        return roomType.hotel == hotelID;
    })
}

var updateSelectView = function() {
    var hotelID = $hotelSelect.val();
    var roomTypes = [];
    if(hotelID) {
        roomTypes = getRoomTypesByHotel(hotelID);
    }

    updateRoomListView(roomTypes);
}

updateRoomList();
updateSelectView();

$hotelSelect.on('change', updateSelectView);
$('#dateBegin,#dateEnd').datepicker({language: 'ru'});

var $children = $('#children');
var $childrenIcon = $('#children-icon');
$childrenIcon.popover({
    html: true,
    content: ''
});
var childrenValues = [];

$childrenIcon.on('shown.bs.popover', function () {
    var popoverContent = $childrenIcon.next('div.popover').children('div.popover-content');
    popoverContent.find('input').on('change', function() {
        childrenValues = [];
        popoverContent.find('input').each(function(index, input){
            childrenValues.push(input.value);
        })
    });
});

var popoverShow = false;
$childrenIcon.on('hide.bs.popover', function () {
    popoverShow = false;
});

$children.on('change', function() {
    var value = $children.val();
    value = parseInt(value);
    if(isNaN(value)) {
        value = 0;
    }
    if(value) {
        var content = '';
        for(var i = 0; i < value; i++) {
            var inputValue = childrenValues[i];
            content = content + '<input name="children['+i+']" type="number" value="'+inputValue+'" min="1" max="18" class="form-control inline-block input-sm">';
        }
        if(content) {
            content = '<div class="form-inline">' + content + '</div>';
        }

        var popover = $childrenIcon.data('bs.popover');
        popover.options.content = content;
        var popoverContent = $childrenIcon.next('div.popover').children('div.popover-content');
        popoverContent.html(content);
        if(!popoverShow) {
            $childrenIcon.popover('show');
            popoverShow = true
        }
    } else {
        $childrenIcon.popover('hide');
    }
})