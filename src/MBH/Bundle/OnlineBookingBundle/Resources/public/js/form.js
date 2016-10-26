$(function () {
    var restrictions,
        updateRestrictions = function () {
            restrictions = getRestrictions();
        };

    var $hotelSelect = $("#search_form_hotel");
    var $roomTypeSelect = $("#search_form_roomType");
    var roomTypeList = [];


    var getMinStay = function (minstay) {
        var currentRoomType = $("#search_form_roomType").find("option:selected").val(),
            currentHotel = $("#search_form_hotel").find("option:selected").val(),
            allHotels = getAllOptionsValues($("#search_form_hotel")),
            result;
        if (!$.isEmptyObject(currentRoomType)) {
            result =  minstay['hotel_' + currentHotel]['category_' + currentRoomType];
        } else {
            if (!$.isEmptyObject(currentHotel)) {
                $.each(minstay['hotel_' + currentHotel], function (id, val) {
                    result = Math.min(result, val);
                });
            } else {
                $.each(minstay, function (id, category) {
                    $.each(category, function (id, val) {
                        if(!result) {
                            //Первая итерация иначе result будет всегда 0
                            result = val;
                        }
                        result = Math.min(result, val);
                    })
                })
            }
        }

        return result;
    };
    var getRestrictions = function () {
            var prefix, id,
                allHotels = getAllOptionsValues($("#search_form_hotel")),
                currentRoomType = $("#search_form_roomType").find("option:selected").val(),
                currentHotel = $("#search_form_hotel").find("option:selected").val();

            if (!$.isEmptyObject(currentRoomType)) {
                prefix = 'category_';
                id = currentRoomType;
            } else {
                if (!$.isEmptyObject(currentHotel)) {
                    prefix = 'allrooms_';
                    id = currentHotel;
                } else {
                    prefix = 'allrooms_';
                    var data = [];
                    $.each(allHotels, function (index, id) {
                        $.merge(data, $.map(mbh.restrictions[prefix + id], function (val) {
                            return val;
                        }));
                    });
                    return data;

                }
            }

            return $.map(mbh.restrictions[prefix + id], function (val) {
                return val;
            });

        },

        getAllOptionsValues = function ($name) {
            return $.map($name.find("option"), function (el, index) {
                return $(el).val() || null
            });
        };
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
        if (!roomTypes.length) {
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
    $hotelSelect.on("change", updateRestrictions());
    $roomTypeSelect.on("change", updateRestrictions());
//////////////////////////////////////////////
    var $search_form_begin = $("#search_form_begin"),
        $search_form_end = $("#search_form_end");
    $search_form_begin.mask('99.99.9999');
    $search_form_end.mask('99.99.9999');

    moment.locale("ru");
    var dateBeginDefaults = function () {
            var defaultMinDate = '28.04.2017',
                minDate = moment(defaultMinDate, "DD.MM.YYYY", true),
                now = moment(),
                startDate = moment(Math.max(minDate, now)),
                endDate = startDate.clone().add(1, 'year');
                while($.inArray(startDate.format("DD.MM.YYYY"), restrictions) != -1) {
                    startDate.add(1, "day");
                }
            return {
                format: 'dd.mm.yyyy',
                language: 'ru',
                autoclose: true,
                startDate: startDate.format('DD.MM.YYYY'),
                endDate: endDate.format('DD.MM.YYYY'),
                todayHighlight: true,
                orientation: "bottom right",
                beforeShowDay: function (date) {
                    if($.inArray(moment(date.valueOf()).format("DD.MM.YYYY"), restrictions) != -1) {
                        return false;
                    }
                }
            }
        },
        dateEndDefaults = function () {
            var defaults = dateBeginDefaults(),
                startDate = $search_form_begin.datepicker("getDate"),
                minPeriodDays = 1;
            if(minPeriodDays) {
                startDate = moment(startDate.valueOf()).add(minPeriodDays, 'days');
            }
            while($.inArray(startDate.format("DD.MM.YYYY"), restrictions) != -1) {
                startDate.add(1, "day");
            }
            defaults.startDate = startDate.toDate();

            return defaults;
        },

        updateEndPicker = function(timestamp) {
            var minstay, result, date = moment.unix(timestamp);
            var url = Routing.generate('online_booking_min_stay', {'timestamp': timestamp}, true);
            var jqxhr = $.get(url)
                .done(function (data) {
                    minstay = $.parseJSON(data)['minstay'];
                    result = getMinStay(minstay)||1;
                    if(result) {
                        date.add(result, 'day');
                        while($.inArray(date.format("DD.MM.YYYY"), restrictions) != -1) {
                            date.add(1, "day");
                        }
                        $search_form_end.datepicker("setDate", date.toDate());
                        $search_form_end.datepicker("setStartDate", date.toDate());
                    }
                })
                .fail(function (data) {
                    console.error(data);
                });
        };


    $search_form_begin.val(dateBeginDefaults()["startDate"]).datepicker(dateBeginDefaults());
    date_end_defaults = dateEndDefaults();
    $search_form_end.val(moment(date_end_defaults["startDate"].valueOf()).format("DD.MM.YYYY")).datepicker(date_end_defaults);

    $search_form_begin.datepicker().on("changeDate", function (e) {
        updateEndPicker(moment(e.date).unix());

    });

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

    function getQueryVariable(variable) {
        var query = window.location.search.substring(1);
        var vars = query.split('&');
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split('=');
            if (decodeURIComponent(pair[0]) == 'search_form[' + variable + ']') {
                return decodeURIComponent(pair[1]);
            }
        }
        console.log('Query variable %s not found', variable);
    }

    function formatToRusType(string) {
        if (string) {
            var arr = string.split(".");
            return arr[1] + '.' + arr[0] + '.' + arr[2];
        }

    }
});

// function datePicker()
// {
//     var allDates = getAllDates();
//
//     $('#dateBegin').datepicker({
//         format: 'dd.mm.yyyy',
//         language: 'ru',
//         autoclose: true,
//         startDate: allDates['minInDate'],
//         endDate: allDates['maxInDate'],
//         beforeShowDay: function(date)
//         {
//             if($.inArray(date.valueOf(), allDates['inDates']) < 0){
//                 return 'disabled';
//             }else{
//                 return '';
//             }
//         }
//     });
//
//     $('#dateEnd').datepicker({
//         format: 'dd.mm.yyyy',
//         language: 'ru',
//         autoclose: true,
//         weekStart: 1,
//         startDate: allDates['minOutDate'],
//         endDate: allDates['maxOutDate'],
//         beforeShowDay: function(date)
//         {
//             if($.inArray(date.valueOf(), allDates['outDates']) < 0){
//                 return 'disabled';
//             }else{
//                 return '';
//             }
//         }
//     });
//
//     // $('#dateBegin').datepicker('update', allDates['minInDate']);
//     $('#dateBegin').datepicker('update', (postDateBegin != ''?postDateBegin:allDates['minInDate']) );
//     // $('#dateEnd').datepicker('update', allDates['minOutDate']);
//     $('#dateEnd').datepicker('update', (postDateEnd != ''?postDateEnd:allDates['minOutDate']) );
//
//     //$('#dateBegin').datepicker('update', '');
//     //$('#dateEnd').datepicker('update', '');
//
//     $('#dateBegin').datepicker().on('changeDate', function(e){
//
//         var dateBeginDateTmp = $('#dateBegin').datepicker('getDate');
//         var newDateForEnd = false;
//
//         $('#dateEnd').datepicker('startDate', dateBeginDateTmp);
//
//         $.each(allDates['outDates'], function(key, val){
//             var tmpCheckdate = new Date(val);
//
//             if(tmpCheckdate > dateBeginDateTmp){
//                 newDateForEnd = tmpCheckdate;
//
//                 return false;
//             }
//         });
//
//         if(newDateForEnd !== false){
//             $('#dateEnd').datepicker('update', newDateForEnd);
//         }
//
//         $('#dateEnd').datepicker('show');
//
//     });
//
// }






