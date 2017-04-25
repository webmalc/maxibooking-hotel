/* global $, window, Routing */

var Special = function($row) {
    this.id = $("#special", $row).data('special');
    this.begin = $("#dates", $row).data('begin');
    this.end = $("#dates", $row).data('end');
    this.tariffId = $("#tariff", $row).data('tariff');
    this.roomTypeId = $("#roomType", $row).data('roomtypeid');
    this.$select = $("#capacity", $row);
    this.activeOption = function () {
        return $("option:selected", this.$select);
    };
    this.$siteSelect = $("#sitecapacity", $row);
    this.$price = $("span#price", $row);
    this.$sitePrice = $("span#sitePrice", $row);
    this.$bookingButton = $("div#booking", $row);
    this.$closeButton = $("div#closeSpec", $row);
    this.$editButton = $("div#edit", $row);
};
Special.prototype.init = function () {
    this.reNewPrices();
    this.reNewSitePrices();
    this.priceLabels();
    this.bindHandlers();
};
Special.prototype.bindHandlers = function () {
    var that = this;
    this.$select.on('change', function () {
        that.reNewPrices();
        that.reNewSitePrices();
        that.priceLabels();
    });
    this.$bookingButton.on('click', function (e) {
        that.booking(e);
    });
    this.$closeButton.on('click', function (e) {
        that.closeSpecial(e);
    });
    this.$editButton.on('click', function (e) {
        that.editSpecial(e);
    });
};

Special.prototype.priceLabels = function () {
    var price = parseInt(this.$price.text());
    var sitePrice = parseInt(this.$sitePrice.text());
    if (price !== sitePrice) {
        this.labelWarning(this.$price);
        this.labelWarning(this.$sitePrice);
    } else {
        this.labelSuccess(this.$price);
        this.labelSuccess(this.$sitePrice);
    }
};
Special.prototype.labelWarning = function ($span) {
    $span.removeClass('label-success');
    $span.addClass('label-warning');
};
Special.prototype.labelSuccess = function ($span) {
    $span.removeClass('label-warning');
    $span.addClass('label-success');
};

Special.prototype.reNewPrices = function () {
    var price = this.activeOption().data('price');
    this.$price.text(price);

};
Special.prototype.reNewSitePrices = function () {
    var activeIndex = this.activeOption().index(),
        price = $(this.$siteSelect.find('option')[activeIndex]).data('price');
    this.$sitePrice.text(price);
};
Special.prototype.getCurrentAges = function () {
    var $activeSelect = $("option:selected", this.$select);

    return {
        adults: $activeSelect.data('adults'),
        children: $activeSelect.data('children')
    };

};

Special.prototype.booking = function (e) {
    e.preventDefault();
    var data = {
        'begin': this.begin,
        'end': this.end,
        'roomType': this.roomTypeId,
        'tariff': this.tariffId,
        'special': this.id,
        'adults': this.getCurrentAges().adults,
        'children': this.getCurrentAges().children

    };
    window.location.href = Routing.generate('package_new', data);
};

Special.prototype.closeSpecial = function(e) {
    e.preventDefault();
    window.location.href = Routing.generate('special_close', {id: this.id});
};
Special.prototype.editSpecial = function (e) {
    e.preventDefault();
    window.location.href = Routing.generate('special_edit', {id: this.id});
};


$(document).ready(function () {
    var special = new Special($("table#spec"));
    special.init();
});