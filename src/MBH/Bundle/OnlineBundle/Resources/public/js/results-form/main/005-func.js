MbhResultForm.prototype.priceSeparator = function (amount) {
    return mbhFuncPriceSeparator(amount);
};

MbhResultForm.prototype.sendPostMessage = function (action, data, target) {
    window.parent.postMessage({
        type: 'mbh',
        action: action,
        data: data || null,
        target: target || null
    }, "*");
};

MbhResultForm.prototype.scrollToTopIframe = function () {
    if (isMobileDevice) {
        this.sendPostMessage('scrollToTopIframe');
    }
};

MbhResultForm.prototype.waiting = function() {
    this.wrapper.html('<div class="mbh-results-info-load alert alert-info"><i class="fa fa-spinner fa-spin"></i> ' + this._waitingText + '</div>');
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
    this.sendPostMessage('resize', {height: jQuery('body').height()});
};

MbhResultForm.prototype.addEventReloadPage = function(selector) {
    jQuery(selector).click(function() {
        window.location.reload();
    });
};
