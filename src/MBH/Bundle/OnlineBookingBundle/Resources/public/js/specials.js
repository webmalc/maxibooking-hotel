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
        // console.log($(this).attr('action'));
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
            special: this.specialId,
            }
        },
        href = page + $.param(data);
    console.log(href);
    this.$form.attr("action", href);
};


$(function () {
    $.each($('.oneblockspec'), function () {
        var special = new Special($(this));
        special.init();
    });
});