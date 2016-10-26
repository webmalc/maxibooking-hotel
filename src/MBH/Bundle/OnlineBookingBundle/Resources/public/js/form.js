$(function () {
    var restrictions,
        updateRestrictions = function () {
            restrictions = getRestrictions();
        },
        $hotelSelect = $("#search_form_hotel"),
        $roomTypeSelect = $("#search_form_roomType"),
        roomTypeList = [],

        getMinStay = function (minstay) {
            var currentRoomType = $("#search_form_roomType").find("option:selected").val(),
                currentHotel = $("#search_form_hotel").find("option:selected").val(),
                allHotels = getAllOptionsValues($("#search_form_hotel")),
                result;
            if (!$.isEmptyObject(currentRoomType)) {
                result = minstay['hotel_' + currentHotel]['category_' + currentRoomType];
            } else {
                if (!$.isEmptyObject(currentHotel)) {
                    $.each(minstay['hotel_' + currentHotel], function (id, val) {
                        result = Math.min(result, val);
                    });
                } else {
                    $.each(minstay, function (id, category) {
                        $.each(category, function (id, val) {
                            if (!result) {
                                //Первая итерация иначе result будет всегда 0
                                result = val;
                            }
                            result = Math.min(result, val);
                        })
                    })
                }
            }

            return result;
        },

        getRestrictions = function () {
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
//////////////////////////////////////////////////////
        getAllOptionsValues = function ($name) {
            return $.map($name.find("option"), function (el, index) {
                return $(el).val() || null
            });
        },
        updateRoomList = function () {
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
        },

        updateRoomListView = function (roomTypes) {
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
        },

        getRoomTypesByHotel = function (hotelID) {
            return roomTypeList.filter(function (roomType) {
                return roomType.hotel == hotelID;
            })
        },

        updateSelectView = function () {
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
            while ($.inArray(startDate.format("DD.MM.YYYY"), restrictions) != -1) {
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
                    if ($.inArray(moment(date.valueOf()).format("DD.MM.YYYY"), restrictions) != -1) {
                        return false;
                    }
                }
            }
        },
        dateEndDefaults = function () {
            var defaults = dateBeginDefaults(),
                startDate = $search_form_begin.datepicker("getDate"),
                minPeriodDays = 1;
            if (minPeriodDays) {
                startDate = moment(startDate.valueOf()).add(minPeriodDays, 'days');
            }
            while ($.inArray(startDate.format("DD.MM.YYYY"), restrictions) != -1) {
                startDate.add(1, "day");
            }
            defaults.startDate = startDate.toDate();

            return defaults;
        },

        updateEndPicker = function (timestamp) {
            var minstay, result, date = moment.unix(timestamp);
            var url = Routing.generate('online_booking_min_stay', {'timestamp': timestamp}, true);
            var jqxhr = $.get(url)
                .done(function (data) {
                    minstay = $.parseJSON(data)['minstay'];
                    result = getMinStay(minstay) || 1;
                    if (result) {
                        date.add(result, 'day');
                        while ($.inArray(date.format("DD.MM.YYYY"), restrictions) != -1) {
                            date.add(1, "day");
                        }
                        var endDate = moment($search_form_end.datepicker('getDate').valueOf());
                        $search_form_end.datepicker("setStartDate", date.toDate());
                        var diff = endDate.diff(date);
                        if(diff < 0) {
                            $search_form_end.datepicker("setDate", date.toDate());
                        }
                    }
                })
                .fail(function (data) {
                    console.error(data);
                });
        },
        nightsCalculate = function (begin, end) {
            return Math.abs(moment.duration(moment(begin).diff(moment(end))).asDays());
        },
        drawNights = function () {
            var begin = $search_form_begin.datepicker('getDate').valueOf(),
                end = $search_form_end.datepicker('getDate').valueOf(),
                nights = nightsCalculate(begin, end);
            if(nights) {
                if($("#nights_amount_container").hasClass('hidden')) {
                    $("#nights_amount_container").removeClass('hidden');
                }
                console.log(nights)
                $("#nights_amount").html(nights);
            }
        };


    $search_form_begin.val(dateBeginDefaults()["startDate"]).datepicker(dateBeginDefaults());
    var date_end_defaults = dateEndDefaults();
    $search_form_end.val(moment(date_end_defaults["startDate"].valueOf()).format("DD.MM.YYYY")).datepicker(date_end_defaults);

    $search_form_begin.datepicker().on("changeDate", function (e) {
        updateEndPicker(moment(e.date).unix());
        drawNights();
    });
    $search_form_end.datepicker().on("changeDate", function (e) {
        drawNights();
    });
/////////////////////////////Чилдрены//////////////////////
//     var $children = $("#search_form_children");
//     var $childrenIcon = $("#children-icon");
//     $childrenIcon.popover({
//         html: true,
//         content: ""
//
//     });
//     var childrenValues = $childrenIcon.data("value");
//     if (!childrenValues) {
//         childrenValues = [];
//     }
//     $childrenIcon.on("shown.bs.popover", function () {
//         var popoverContent = $childrenIcon.next('div.popover').children('div.popover-content');
//         popoverContent.find('input').on('change', function () {
//             childrenValues = [];
//             popoverContent.find('input').each(function (index, input) {
//                 childrenValues.push(input.value);
//             })
//         });
//     });
//
//
//     var popoverShow = false;
//     $childrenIcon.on('hide.bs.popover', function () {
//         popoverShow = false;
//     });
//
//     $children.on('keyup mouseup', function (e) {
//         var value = parseInt($children.val());
//         if (value && Math.min(value, 5) != value) {
//             value = 5;
//             $children.val(value);
//         }
//
//         if (isNaN(value)) {
//             value = 0;
//         }
//         if (value) {
//             var content = "";
//             for (var i = 0; i < value; i++) {
//                 var inputValue = childrenValues[i];
//                 if (undefined === inputValue) {
//                     inputValue = 0;
//                 }
//                 content = content + '<input name="search_form[children_age][' + i + ']" type="number" value="' + inputValue + '" min="1" max="18" class="form-control input-sm" style="display: inline-block">';
//             }
//             if (content) {
//                 content = '<div>' + content + '</div>';
//             }
//
//             var popover = $childrenIcon.data('bs.popover');
//             popover.options.content = content;
//             var popoverContent = $childrenIcon.next('div.popover').children('div.popover-content');
//             popoverContent.html(content);
//             if (!popoverShow) {
//                 $childrenIcon.popover('show');
//                 popoverShow = true
//             }
//         } else {
//             $childrenIcon.popover('hide');
//         }
//     });
//
//     if (childrenValues) {
//         setTimeout(function () {
//             $children.trigger('change');
//         }, 1000)
//     }
    var $children = $("#search_form_children"),
        $ageHolder = $("#search_form_children_age"),
        index;
    index = $ageHolder.find(':input').length;

    var checkShowAgeLabel = function () {
        var $label = $(".children_age_label");
        if (index == 0) {
            $label.fadeOut(300);
        } else if (index && $label.is(':hidden')) {
            $label.removeClass('hidden').hide().fadeIn(300);
        }
    };

    checkShowAgeLabel();
    $ageHolder.data('index', index);
    var countChildrenAge = function (value) {
            if (value > index) {
                var current, i;
                for (i = index; i < value; i++) {
                    current = parseInt(i) + 1;
                    drawChildrenAge(current);
                }
            } else if (value < index) {
                for (i = value; i < index; i++) {
                    current = parseInt(i) + 1;
                    deleteChildrenAge(current)
                }
            }
            index = value;
        },
        drawChildrenAge = function (index) {
            var prototype = $ageHolder.data("prototype"),
                newAge = prototype.replace(/__name__/g, index);
            $ageHolder.append($(newAge).hide().fadeIn(300));
        };

    deleteChildrenAge = function (index) {
        var prototype = $ageHolder.data("prototype"),
            newAge = prototype.replace(/__name__/g, index);
        $.each($ageHolder.children(), function (id, value) {
            if ($(newAge).find('select').attr('id') == $(this).find('select').attr('id')) {
                $(this).fadeOut(300, function () {
                    $(this).remove();
                });
            }
        });

    };

    $children.on('keyup mouseup', function (e) {
        var target = e.target;
        var value = $(target).val(),
            min = $(target).attr('min'),
            max = $(target).attr('max');

        if (value && value != index) {
            if (value >= min && value <= max) {
                countChildrenAge(value)
            }
        }
        checkShowAgeLabel();
    });



});