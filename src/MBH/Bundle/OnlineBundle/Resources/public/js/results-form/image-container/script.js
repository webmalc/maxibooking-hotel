ImageContainer.prototype.init = function() {
    this.hideIframeOnPressEsc();
    this.openFancyboxOnPostMessage();
};

ImageContainer.prototype.hideIframe = function() {
    mbhSendParentPostMessage('hideImage', {roomTypeId: this._roomTypeId});
};

ImageContainer.prototype.hideIframeOnPressEsc = function () {
    var _this = this;
    window.addEventListener('keyup', function(ev) {
        if (ev.code === 'Escape') {
            _this.hideIframe();
        }
    });
};

ImageContainer.prototype.openFancyboxOnPostMessage = function () {
    var _this = this;
    window.addEventListener('message', function(ev) {
        jQuery.fancybox.open(
            _this._imageList,
            {
                loop: true,
                beforeClose: function() {
                    _this.hideIframe();
                },
                afterClose: function() {
                    jQuery.fancybox.destroy();
                }
            },
            _this._imageMapIdToIndex[ev.data]
        );
    });
};

window.addEventListener('load', function(ev) {
    var imageContainer = new ImageContainer();
    imageContainer.init();
});
