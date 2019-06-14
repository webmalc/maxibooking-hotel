jQuery.fn.digits = function() {
    return this.each(function() {
        jQuery(this).text(MbhResultForm.prototype.priceSeparator(jQuery(this).text()));
    })
};
