jQuery.fn.digits = function() {
    return this.each(function() {
        jQuery(this).text(priceSeparator(jQuery(this).text()));

    })
};

function priceSeparator(amount) {
    if (amount.length <= 3) {
        return amount;
    } else if (amount.length <= 6) {
        return amount.replace(/(\d{3}$)/, ",$1");
    } else if (amount.length <= 9) {
        return (amount.replace(/(\d{3})(\d{3}$)/, ",$1,$2")).replace(/(^(,))/, "");
    } else if (amount.length <= 12) {
        return (amount.replace(/(\d{3})(\d{3})(\d{3}$)/, ",$1,$2,$3")).replace(/(^(,))/, "");
    } else {
        return (argument.replace(/(\d)(\d{3})(\d{3})(\d{3}$)/, "$1,$2,$3,$4")).replace(/(^(,))/, "");
    }
}
