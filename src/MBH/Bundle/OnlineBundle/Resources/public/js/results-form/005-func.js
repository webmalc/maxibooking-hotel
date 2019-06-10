MbhResultForm.prototype.priceSeparator = function (amount) {
    amount = String(amount);
    if (amount.length <= 3) {
        return amount;
    } else if (amount.length <= 6) {
        return amount.replace(/(\d{3}$)/, " $1");
    } else if (amount.length <= 9) {
        return (amount.replace(/(\d{3})(\d{3}$)/, " $1 $2"));
    } else if (amount.length <= 12) {
        return (amount.replace(/(\d{3})(\d{3})(\d{3}$)/, " $1 $2 $3"));
    } else {
        return (amount.replace(/(\d)(\d{3})(\d{3})(\d{3}$)/, "$1 $2 $3 $4"));
    }
};

MbhResultForm.prototype.scrollToTopIframe = function () {
    if (isMobileDevice) {
        window.parent.postMessage({
            type: 'mbh',
            action: 'scrollToTopIframe'
        }, "*");
    }
};

MbhResultForm.prototype.waiting = function() {
    this.wrapper.html('<div class="mbh-results-info alert alert-info"><i class="fa fa-spinner fa-spin"></i> ' + this._waitingText + '</div>');
};

MbhResultForm.prototype.setSelect2 = function() {
    jQuery('select.select2').select2({
        placeholder: '',
        allowClear: false,
        minimumResultsForSearch: -1,
        width: 'resolve'
    });
};

MbhResultForm.prototype.getLocale = function() {
    return jQuery('#mbh-form-locale').val();
};

MbhResultForm.prototype.searchDataInit = function() {
    this.searchData = {
        url: '',
        init: function () {
            var urlData = window.location.search.replace('?', '');
            if (urlData === ''){
                urlData = window.sessionStorage.getItem('MBHSearchData');
            }
            this.url = urlData == null ? '': urlData;
        }
    };

    this.searchData.init();
};

MbhResultForm.prototype.resize = function() {
    window.parent.postMessage({
        type: 'mbh',
        action: 'resize',
        height: jQuery('body').height()
    }, "*");
};

MbhResultForm.prototype.addEventReloadPage = function(selector) {
    jQuery(selector).click(function() {
        window.location.reload();
    });
};
