var mychart, myprices, myrows = [];

//BuyOnline
var RoomTypeRow = function ($row) {
    this.$row = $row;
    this.$bookingButton = $row.find('.btn-booking');
    this.$bookingButtonReservation = $row.find('.btn-booking-reservation');
    this.$bookingButtonAll = $row.find('.btn-booking-all');
    this.$showAllSpecialButton = $row.find('.showspecial');
    this.tariffRows = [];
    this.selectedTariffRow = null;
    this.chartContainer = this.$row.find("div[id^=chart]").attr('id');
    this.roomTypeId = this.$row.data('roomtype');
};

RoomTypeRow.prototype.init = function () {
    this.initTariffs();
    this.bindEventHandlers();
    this.fancyInit()
};

RoomTypeRow.prototype.initTariffs = function () {
    var that = this;
    this.$row.find('.tariff-row').each(function () {
        var tariffRow = new TariffRow($(this));
        tariffRow.init(that);
        that.tariffRows.push(tariffRow);
    });
};

RoomTypeRow.prototype.bindEventHandlers = function () {
    var that = this;
    this.$bookingButtonAll.on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        /*that.allTariffAction();*/
        $(this).off('click').find('.btmthree').removeClass('btmthree').addClass('btmthreeoff');
    });
    this.$showAllSpecialButton.on('click', function (e) {
        e.preventDefault();
        window.open('http://azovsky.ru/specpredlojenia-dnia/', '_blank');
    });

};

RoomTypeRow.prototype.selectActiveTariff = function (tariffRow) {
    this.selectedTariffRow = tariffRow;
    this.reBuildHref();
};

RoomTypeRow.prototype.allTariffAction = function () {
    //TODO: Сделать затемнение?
    var that = this,
        $spinner = this.$row.find('.tariff-spinner'),
        formData = $("form#search-form").serialize();

    formData = formData +
        '&' + encodeURIComponent('search_form[roomType]') + '=' + this.roomTypeId +
        '&getalltariff=true';
    $spinner.toggle();
    var ajax = $.get({

            url: Routing.generate('online_booking', {}, true),
            data: formData
        }
    );

    ajax.done(function (data) {
        $spinner.toggle();
        that.removeTariffs();
        that.addTariffs(data);
    });
};

RoomTypeRow.prototype.addTariffs = function (html) {
    this.$row.find('.tariff-container').append(html);
    this.initTariffs();
};

RoomTypeRow.prototype.removeTariffs = function () {
    while (this.tariffRows.length) {
        var tariffRow = this.tariffRows.pop();
        tariffRow.selfRemove();
    }
    if (this.selectedTariffRow) {
        this.selectedTariffRow = null;
    }
};
RoomTypeRow.prototype.reBuildHref = function () {
    var href = this.selectedTariffRow.bookingHref;
    var form_href = '&' + $("form#search-form").serialize();
    href = href + form_href;

    this.$bookingButton.attr('href', href);
    this.$bookingButtonReservation.attr('href', href + '&reservation=true');
};
RoomTypeRow.prototype.handleChartData = function (data) {
    var chartData = data;
    $.each(this.tariffRows, function (index) {
        this.chartData = chartData[index];
    });

};

RoomTypeRow.prototype.fancyInit = function () {
    // Because jquery v.1.12 azovsky.ru
    var fancies = this.$row
        .find('.fancybox')
        .fancybox()
        .each(function () {
            $(this).on('click', function (e) {
                e.stopPropagation();
            });
        });

    this.$row
        .find(".imghotel")
        .on('click', function () {
            $(fancies[0]).click();
        });

    // this.$row
    //     .find('.fancybox')
    //     .each(function () {
    //         $(this).fancybox()
    //             .on('click', function (e) {
    //                 e.stopPropagation();
    //             });
    //     });
    //
    // this.$row
    //     .find(".imghotel")
    //     .on('click', function (e) {
    //         $.fancybox.open($('.fancybox', $(this)));
    //     });
};

RoomTypeRow.prototype.getChartOptions = function () {
    return {
        chart: {
            type: 'column',
            plotBorderWith: 1,
            height: 250,
            zoomType: false
        },
        title: {
            text: 'Календарь цен',
            align: 'center',
            floating: false
        },
        subtitle: {
            text: 'subtitle',
            floating: true
        },
        xAxis: {
            gridLineWidth: 0,
            type: 'category',
            labels: {
                rotation: -75,
                style: {
                    fontSize: '10px',
                    fontFamily: 'Verdana, sans-serif'
                }
            }
        },
        yAxis: {
            gridLineWidth: 1,
            title: {
                enabled: false,
                text: 'Цена'
            },
            allowDecimail: false,
            labels: {
                enabled: false,
                format: '{value:,.0f} р'
            },
            tickInterval: 2000
        },
        scrollbar: {
            enabled: true,
            liveRedraw: true
        },
        legend: {
            enabled: false
        },
        plotOptions: {
            series: {
                color: '#6894ee',
                align: 'right',
                allowPointSelect: false,
                cursor: 'pointer',
                dataLabels: {
                    format: '{point.y:,.0f} р',
                    enabled: true,
                    rotation: -90,
                    y: 30,
                    color: '#FFFFFF',
                    style: {
                        "fontSize": '10px',
                        "textShadow": false,
                        "textWeight": 100,
                        "stroke-width": "0px"
                    }
                }
            },
            line: {
                dataLabels: {
                    enabled: false
                }
            }
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.85)',
            style: {
                color: '#F0F0F0'
            },
            headerFormat: '<span style="font-size: 10px; text-align: center;">{point.key}</span><br/>',
            pointFormat: '<b>{point.y} руб.</b>'
        }
    };
};

var TariffRow = function ($row) {
    this.$row = $row;
    this.$radiobutton = this.$row.find('input[type=radio]');
    this.bookingHref = this.$row.data('url');
    this.roomTypeRow = null;
    this.chartData = {};
};

TariffRow.prototype.init = function (roomTypeRow) {
    var that = this;
    this.roomTypeRow = roomTypeRow;
    this.$radiobutton.on('ifChecked', function (e) {
        that.roomTypeRow.selectActiveTariff(that);
        that.$row.addClass('alerting').siblings().removeClass("alerting");
    });
    //Проблема в том что при инициализации (если checked) не генерится событие ifChecked, т.к.формально статус не изменяется
    //костыль в проверке и генерации события "руками"
    this.$radiobutton.on('ifCreated', function (e) {
        if ($(this).is(':checked')) {
            $(this).trigger('ifChecked');
        }
    });
    this.$radiobutton.iCheck({
        checkboxClass: 'icheckbox_square-green',
        radioClass: 'iradio_square-green',
        increaseArea: '20%' // optional
    });
    this.$row.on('click', function (e) {
        that.$radiobutton.iCheck('check');
    });
};

TariffRow.prototype.selfRemove = function () {
    this.$row.remove();
};

TariffRow.prototype.showChart = function () {
    if (!this.roomTypeRow.chart) {
        var options = this.roomTypeRow.getChartOptions();
        options.xAxis.min = this.chartData['showBegin'];
        options.xAxis.max = this.chartData['showEnd'];
        options.subtitle.text = this.chartData['roomTypeName'];
        options.yAxis.max = this.chartData['yMax'];
        options.yAxis.min = this.chartData['yMin'];

        this.roomTypeRow.chart = new Highcharts.Chart(this.roomTypeRow.chartContainer, options);
    }

    //Алярма!, массив с данными передается по ссылке  - по-умолчанию по сему
    //тут копия, которую и передаем. -3чч
    var prices = JSON.parse(JSON.stringify(this.chartData['prices']));

    if ('undefined' === typeof (this.roomTypeRow.chart.series[0])) {
        this.roomTypeRow.chart.addSeries({data: prices});
    } else {
        this.roomTypeRow.chart.series[0].setData(prices);
    }
};

TariffRow.prototype.drawChart = function () {
    var data = this.chartData,
        that = this;
    if ($.isEmptyObject(data)) {
        //Для одного тарифа
        //data = $.getJSON(getChartUrl(this))
        //Для всех тарифов
        data = $.getJSON(getChartRoomUrl(this));

        $.when(data).then(function (data) {
            //Закидываем данные в каждый tariffRow через roomType
            that.roomTypeRow.handleChartData(data);
            that.showChart();
        });
    } else {
        this.showChart();
    }

};

var getChartRoomUrl = function (tariffRow) {
    var data = [];
    var RoomTypeRow = tariffRow.roomTypeRow;
    RoomTypeRow.tariffRows.forEach(function (tariffRow) {
        var $row = tariffRow.$row;
        data.push({
            roomType: $row.data('roomtype'),
            tariff: $row.data('tariff'),
            adults: $row.data('adults'),
            children: $row.data('children'),
            begin: $row.data('begin'),
            end: $row.data('end')
        });
    });

    return Routing.generate('online_booking_calculationRoom',
        {
            data: JSON.stringify(data)

        }
        , true);
};


var init = function ($block) {
    $.each($('.room-type-row', $block), function () {
        var roomTypeRow = new RoomTypeRow($(this));
        roomTypeRow.init();
        //Графики - нужно будет перенести в методы
        //roomTypeRow.tariffRows.forEach(function (tariffRow) {
        // tariffRow.$row.on('click', function () {
        //     if ( useCharts?'true':'false' ) {
        //         tariffRow.drawChart();
        //         }
        //     //tariffRow.selected();
        //     //start_bron(this);
        //     });
        // //})
    });
};

var AddingDates = function ($link) {
    this.$link = $link;
    this.$resultBlock = $("#online-booking-additional");
    this.$loading = $(".loading");
    this.$noResult = $("div#noResult");
};
AddingDates.prototype.isLoading = function () {
    return this.$loading.hasClass('loading-v');
};
AddingDates.prototype.startLoading = function () {
    this.$loading.addClass('loading-v');
};
AddingDates.prototype.stopLoading = function () {
    this.$loading.removeClass('loading-v');
};
AddingDates.prototype.init = function () {
    this.bindHandlers();
};
AddingDates.prototype.final = function () {
    this.$noResult.remove();
    this.$link.remove();
};
AddingDates.prototype.bindHandlers = function () {
    var that = this,
        formData = $("form#search-form").serialize();
    formData = formData +
        '&' + encodeURIComponent('search_form[addDates]') + '=' + 'true';
    this.$link.on('click', function (e) {
        e.preventDefault();
        if (that.isLoading()) {
            return false;
        }
        //TODO: Сделать затемнение?
        var ajax = $.get({
                url: Routing.generate('online_booking', {}, true),
                data: formData,
                beforeSend: function () {
                    that.startLoading();
                }
            }
        );
        ajax.done(function (data) {
            that.stopLoading();
            that.final();
            that.addDates(data);
        });

    });
};
AddingDates.prototype.addDates = function (html) {
    this.$resultBlock.append(html).fadeIn('slow');
    init(this.$resultBlock);
};

$(function () {
    init($('div#online-booking-search'));
    var $link = $("#addDatesLink");
    var addDates = new AddingDates($link);
    addDates.init();
});
