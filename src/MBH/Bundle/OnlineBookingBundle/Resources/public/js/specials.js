/* global $ */

var Special = function ($row) {
    this.$row = $row;
    this.$newPrice = $(".spec_new_price", $row);
    this.$oldPrice = $(".spec_old_price", $row);
    this.$choice = $(".capacity_choice", $row);
    this.$form = $("form", $row);
    this.discount = $row.data('discount');
    this.roomTypeId = $row.data('roomtypeid');
    this.begin = $(".spec-date p.date", $row).data('begin');
    this.end = $(".spec-date p.date", $row).data('end');
    this.specialId = $row.data('specialid');
    this.hotelId = $row.data('hotelid');
    this.imageDiv = $row.find('.main-img');
    this.submit_button = $("form input[type='submit']", $row);
    this.activePrice = function () {
        return $("option:selected", this.$choice).data('price');
    };
    this.activeAdults = function () {
        return $("option:selected", this.$choice).data('adults');
    };
    this.activeChildren = function () {
        return $("option:selected", this.$choice).data('children');
    }
};

Special.prototype.init = function () {
    this.reNewPrices();
    this.reNewHref();
    this.bindHandlers();
};

Special.prototype.bindHandlers = function () {
    var that = this;
    this.$choice.on('change', function () {
        that.reNewPrices();
        that.reNewHref();
    });
    this.$form.on('submit', function (e) {
        e.preventDefault();
        window.location = $(this).attr('action');
    });
    this.submit_button.on('click', function (e) {
        e.preventDefault();
        window.location = $(this).closest('form').attr('action');
    });
    this.imageDiv.on('click', function (e) {
        $.fancybox($(this).data('image'));
    })

};

Special.prototype.recalculatePrice = function () {
    var price = this.activePrice();

    return {
        newPrice: price,
        oldPrice: Math.round(price / (1 - this.discount / 100))

    };
};

Special.prototype.reNewPrices = function () {
    var prices = this.recalculatePrice();
    this.$newPrice.text(this.numberWithCommas(prices.newPrice));
    this.$oldPrice.text(this.numberWithCommas(prices.oldPrice));
};

Special.prototype.reNewHref = function () {
    var page = '/mbresults/?',
        data = {
            step: 1,
            search_form: {
                hotel: this.hotelId,
                roomType: this.roomTypeId,
                begin: this.begin,
                end: this.end,
                adults: this.activeAdults(),
                children: this.activeChildren(),
                special: this.specialId
            }
        },
        href = page + $.param(data);
    this.$form.attr("action", href);
};

Special.prototype.show = function () {
    this.$row.removeClass('hide');
};
Special.prototype.hide = function () {
    this.$row.addClass('hide');
};
Special.prototype.showHotel = function () {
    this.$row.removeClass('hotel-hide');
};
Special.prototype.hideHotel = function () {
    this.$row.addClass('hotel-hide');
};
Special.prototype.removeLast = function () {
    if (this.$row.hasClass('spec-last')) {
        this.$row.removeClass('spec-last')
    }
};
Special.prototype.setLast = function () {
    if (!this.$row.hasClass('spec-last')) {
        this.$row.addClass('spec-last')
    }
};

Special.prototype.numberWithCommas = function (x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
};

var MonthSwitcher = function ($row, urlTool) {
    this.$row = $row;
    this.id = $row.attr('id');
    this.allSpecials = [];
    this.specials = [];
    this.isEnabled = false === $row.hasClass('disable-month');
    this.$neighbors = $row.parent().siblings().find('a');
    this.urlTool = urlTool;
};

MonthSwitcher.prototype.init = function (specials) {
    this.allSpecials = specials;
    var special;
    for (special in specials) {
        if (specials[special].$row.hasClass(this.id)) {
            this.specials.push(specials[special])
        }
    }
    this.bindHandlers();
};

MonthSwitcher.prototype.bindHandlers = function () {
    var that = this;
    this.$row.on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        if (that.isEnabled) {
            that.showClickedSpecials();
            that.urlTool.changeUrl(that.id);
        }
    });
};
MonthSwitcher.prototype.setActive = function () {
    this.$neighbors.removeClass('spec-active');
    this.$row.addClass('spec-active');
};
MonthSwitcher.prototype.showClickedSpecials = function () {
    var special;
    for (special in this.allSpecials) {
        this.allSpecials[special].hide();
    }
    for (special in this.specials) {
        this.specials[special].show();
        this.specials[special].removeLast();
    }
    //last element
    $("article div#block_spec_container div.oneblockspec:visible:last").addClass('spec-last');
    // this.specials[special].setLast();
    this.setActive()
};


var HotelSwitcher = function ($row) {
    this.$row = $row;
    this.id = $row.data('hotelid');
    this.allSpecials = [];
    this.specials = [];
};
HotelSwitcher.prototype.init = function (specials) {
    this.allSpecials = specials;
    var special;
    for (special in specials) {
        if (specials[special].$row.data('hotelid') === (this.id)) {
            this.specials.push(specials[special])
        }
    }
    this.bindHandlers();
};
HotelSwitcher.prototype.bindHandlers = function () {
    var that = this;
    this.$row.on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        that.showClickedSpecials();
        that.setActive(this);
    });
};
HotelSwitcher.prototype.setActive = function(link) {
    $('.hotel-switcher').removeClass('activespc');
    $(link).closest('.hotel-switcher').addClass('activespc');
};
HotelSwitcher.prototype.showClickedSpecials = function () {
    var special;
    for (special in this.allSpecials) {
        this.allSpecials[special].hideHotel();
    }
    for (special in this.specials) {
        this.specials[special].showHotel();
    }
    this.specials[special].setLast();
};

var callMonthSlider = function (monthSwitcherContainer) {
    var page = monthSwitcherContainer.getActivePage(),
        perPage = monthSwitcherContainer.perPage;
    $('.bxslider').bxSlider({

        minSlides: perPage,
        maxSlides: 4,
        slideWidth: 360,
        slideMargin: 10,
        infiniteLoop: false,
        pager: false,
        startSlide: page-1

    });
};

var UrlTool = function () {
    this.url = window.location.href;
    this.search = window.location.search;
    this.pathName = window.location.pathname;
};

UrlTool.prototype.getDefaultMonth = function () {
    try {
        var month = window.location.search.replace("=","_").replace('/','').replace('?','');
    } catch (err) {
        month = null;
    }

    return month || 'month_05';
};
UrlTool.prototype.changeUrl = function (month) {
    var state = this.pathName + '?' + month;
    window.history.pushState('', '', state);

};

var MonthSwitcherContainer = function() {
    this.switchers = [];
    this.defaultSwitcher = null;
    this.perPage = 3;
};
MonthSwitcherContainer.prototype.addSwitcher = function (switcher) {
    this.switchers.push(switcher);
};


MonthSwitcherContainer.prototype.defaultSwitcherDetermine = function(defaultMonth) {
    var switcher, enabledSwitcher;
    for (switcher in this.switchers) {
        if (this.switchers[switcher].isEnabled) {
            enabledSwitcher = this.switchers[switcher];
            if (enabledSwitcher.id === defaultMonth) {
                this.defaultSwitcher = enabledSwitcher;
                break
            }
        }
    }
    if(!this.defaultSwitcher) {
        //Последний в списке
        this.defaultSwitcher = enabledSwitcher;
    }
};
MonthSwitcherContainer.prototype.showFirstEnabledSwitcher = function (defaultMonth) {
    if(!this.defaultSwitcher) {
        this.defaultSwitcherDetermine(defaultMonth);
    }
    if(this.defaultSwitcher) {
        this.defaultSwitcher.showClickedSpecials();
    }
};
MonthSwitcherContainer.prototype.getActivePage = function() {
    if(!this.defaultSwitcher) {
        return 1;
    }
    var perPage = this.perPage,
        numsOfSwitchers = this.switchers.length,
        pages = Math.ceil(numsOfSwitchers / perPage),
        switcherIndex = this.switchers.indexOf(this.defaultSwitcher)+1;

    return Math.ceil((switcherIndex) / pages);
};

$(function () {
    var urlTool = new UrlTool();
    var defaultMonth = urlTool.getDefaultMonth();
    var specials = [],
        hotelSwitchers = [],
        monthSwitcherContainer = new MonthSwitcherContainer();
    $.each($('.oneblockspec'), function () {
        var special = new Special($(this));
        special.init();
        specials.push(special);
    });
    $.each($('.month-switcher>a'), function () {
        var switcher = new MonthSwitcher($(this), urlTool);
        switcher.init(specials);
        monthSwitcherContainer.addSwitcher(switcher);
    });
    $.each($('.hotel-switcher'), function () {
        var hotelSwitcher = new HotelSwitcher($(this));
        hotelSwitcher.init(specials);
        hotelSwitchers.push(hotelSwitcher);
    });
    monthSwitcherContainer.showFirstEnabledSwitcher(defaultMonth);
    /*callMonthSlider(monthSwitcherContainer);*/

});
