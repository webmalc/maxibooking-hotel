<!--Заполняем форму-->
function formFill() {
    var search_form_name = 'search_form';
    var hashParams = window.location.search.substr(1).split('&');
    for (var i = 0; i < hashParams.length; i++) {
        var p = hashParams[i].split('=');
        var element_name = decodeURIComponent(p[0]);
        if (element_name.split(['['])[0] == search_form_name) {
            var element = document.getElementsByName(element_name)[0];
            if (element && element.type !== 'submit') {
                element.value = decodeURIComponent(p[1]);
                if (element.id === 'search_form_children') {
                    $(element).trigger('change');
                }
            }
        }
    }
}


$(function () {

    // Block Children
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
    var drawChildrenAge = function (index) {
            var prototype = $ageHolder.data("prototype"),
                $newAge = $(prototype.replace(/__name__/g, index));
            $ageHolder.append($newAge.hide().fadeIn(300));
            // $('select.dropdown',$newAge).easyDropDown();

        },
        deleteChildrenAge = function (index) {
            var prototype = $ageHolder.data("prototype"),
                newAge = prototype.replace(/__name__/g, index);
            $.each($ageHolder.children(), function (id, value) {
                if ($(newAge).find('select').attr('id') == $(this).find('select').attr('id')) {
                    $(this).fadeOut(300, function () {
                        $(this).remove();
                    });
                }
            })
        },
        countChildrenAge = function (value) {
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
        };

    $children.on('change', function (e) {
        var target = e.target;
        var value = parseInt($(target).val()) || 0,
            min = parseInt($(target).attr('min')),
            max = parseInt($(target).attr('max'));
        if (value != index) {
            if (value >= min && value <= max) {
                countChildrenAge(value)
            }
        }
        checkShowAgeLabel();
    });



    //Burn in HELL easyDropDown

    formFill();
    $('select.dropdown').easyDropDown();

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
                        if (!result) result = val;
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
                    var result = false;
                    $.each(allHotels, function (key, id) {
                        var dates = mbh.restrictions[prefix + id];
                        if (dates) {
                            dates = $.map(dates, function (val) {
                                return val;
                            });
                            result = result === false ? dates : result;
                            result = $.map(dates, function (val) {
                                return $.inArray(val, result) < 0 ? null : val;
                            });
                        } else {
                            result = [];
                            return;
                        }
                    });
                    return result;

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
            /* Убрал disabled из за заполнения формы скриптом в mbresults.php */
            /*if (!roomTypes.length) {
             $roomTypeSelect.prop("disabled", true);
             } else {
             $roomTypeSelect.prop("disabled", false);
             }*/
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

    $hotelSelect.on("change", function () {
        updateSelectView();
        updateRestrictions();
        $search_form_begin.datepicker("update");
        $search_form_end.datepicker("update");

    });
    $roomTypeSelect.on("change", updateRestrictions);

//////////////////////////////////////////////

    $("form#search-form").on('submit', function (e) {
        $(".spinn").css('display', 'block');
        if('undefined' !== typeof yaCounter10885255) {
            yaCounter10885255.reachGoal('mb_bron_price');
        }
        if('undefined' !== typeof ga) {
            ga('send', 'event', 'Бронирование МБ', 'Узнать цены МБ', $(e.target).find("#search_form_hotel option:selected").text());
        }
    });

    var $search_form_begin = $("#search_form_begin"),
        $search_form_end = $("#search_form_end");
    $search_form_begin.mask('99.99.9999');
    $search_form_end.mask('99.99.9999');

    moment.locale("ru");

    var dateBeginDefaults = function () {
            var defaultMinDate = '24.04.2019',
                minDate = moment(defaultMinDate, "DD.MM.YYYY", true),
                now = moment(),
                startDate = moment(Math.max(minDate, now)),
                endDate = startDate.clone().add(2, 'year');

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
                orientation: "auto",
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
                        if (diff < 0) {
                            $search_form_end.datepicker("setDate", date.toDate());
                        } else {
                            setTimeout(drawNights(), 300);
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
                nights = parseInt(nightsCalculate(begin, end));
            if (nights) {
                if ($("#nights_amount_container").hasClass('hidden')) {
                    $("#nights_amount_container").removeClass('hidden');
                }
                $("#nights_amount").html(nights);
            }
        };

    //init datepicker
    //Учитываем если дата уже выбрана

    updateRestrictions();
    $search_form_begin.datepicker(dateBeginDefaults());

    if (!$search_form_begin.val().length) {
        $search_form_begin.datepicker("update", $search_form_begin.datepicker("getStartDate"));
    } else {
        var currentStartDate = moment($search_form_begin.val(), "DD.MM.YYYY", true),
            setDate = moment(Math.max(currentStartDate, $search_form_begin.datepicker("getStartDate")));
        $search_form_begin.datepicker("update", setDate.toDate());
    }

    var date_end_defaults = dateEndDefaults();
    $search_form_end.datepicker(date_end_defaults);

    if (!$search_form_end.val().length) {
        $search_form_end.datepicker("update", $search_form_end.datepicker("getStartDate"));
        //First update endPicker
        updateEndPicker(moment($search_form_begin.datepicker('getDate')).unix());
    } else {
        var currentEndDate = moment($search_form_end.val(), "DD.MM.YYYY", true),
            setEndDate = moment(Math.max(currentEndDate, $search_form_end.datepicker("getStartDate")));
        $search_form_end.datepicker("update", setEndDate.toDate());
    }

    $search_form_begin.datepicker().on("changeDate", function (e) {
        updateEndPicker(moment(e.date).unix());

    });


    $search_form_end.datepicker().on("changeDate", function (e) {
        drawNights();
    });





});