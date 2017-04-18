/* global $ */

var Special = function ($row) {
    this.$row = $row;
    this.$newPrice = $(".spec_new_price", $row);
    this.$oldPrice = $(".spec_old_price", $row);
    this.$choice = $(".capacity_choice", $row);
    this.$form = $("form", $row);
    this.discount = $row.data('discount');
    this.roomCategoryId = $row.data('roomcategoryid');
    this.roomTypeId = $row.data('roomtypeid');
    this.begin = $(".spec-date p.date", $row).data('begin');
    this.end = $(".spec-date p.date", $row).data('end');
    this.specialId = $row.data('specialid');
    this.hotelId = $row.data('hotelid');
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
    this.$form.on('submit', function(e) {
        e.preventDefault();
        window.location = $(this).attr('action');
    })

};

Special.prototype.recalculatePrice = function () {
    var price = this.activePrice();

    return {
        newPrice: price,
        oldPrice: Math.round(price/(1-this.discount/100))

    };
};

Special.prototype.reNewPrices = function () {
    var prices = this.recalculatePrice();
    this.$newPrice.text(prices.newPrice);
    this.$oldPrice.text(prices.oldPrice);
};

Special.prototype.reNewHref = function () {
    var page = '/mbresults.php?',
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
Special.prototype.hide = function() {
    this.$row.addClass('hide');
};
Special.prototype.setLast = function () {
    if(!this.$row.hasClass('spec-last')) {
        this.$row.addClass('spec-last')
    }

};

var MonthSwitcher = function ($row) {
    this.$row = $row;
    this.id = $row.attr('id');
    this.allSpecials = [];
    this.specials = [];
    this.isActive = false === $row.hasClass('disable-month');
    this.$neighbors = $row.parent().siblings().find('a');
};

MonthSwitcher.prototype.init = function(specials) {
    this.allSpecials = specials;
    var special;
    for (special in specials) {
        if(specials[special].$row.hasClass(this.id)) {
            this.specials.push(specials[special])
        }
    }
    this.bindHandlers();
};

MonthSwitcher.prototype.bindHandlers = function() {
        var that = this;
        this.$row.on('click', function (event) {
            event.preventDefault();
            if (that.isActive) {
                that.showClickedSpecials();
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
    }
    //last element
    this.specials[special].setLast();
    this.setActive()
};

var callMonthSlider = function (month) {
    $('.bxslider').bxSlider({

        minSlides: 3,
        maxSlides: 3,
        slideWidth: 360,
        slideMargin: 10,
        infiniteLoop:false,
        pager: false

    });
};
var showFirstActiveSpecials = function (monthSwitchers) {
    var switcher;
    for (switcher in monthSwitchers) {
        if(monthSwitchers[switcher].isActive) {
            monthSwitchers[switcher].showClickedSpecials();
            break;
        }
    }
};

$(function () {
    var specials = [],
        monthSwitchers = [];
    $.each($('.oneblockspec'), function () {
        var special = new Special($(this));
        special.init();
        specials.push(special);
    });
    $.each($('.month-switcher>a'), function () {
        var switcher = new MonthSwitcher($(this));
        switcher.init(specials);
        monthSwitchers.push(switcher);
    });
    callMonthSlider();
    showFirstActiveSpecials(monthSwitchers);
});