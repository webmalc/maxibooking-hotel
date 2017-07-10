//Default Prices
var DefaultPrice = function() {
    this.$container = {};
    this.select2Config = {
        placeholder: 'Показывать цену по-умолчанию для...'
    };
    this.$form = {};
};
DefaultPrice.prototype.init = function(container) {
    this.$container = container;
    this.$container.select2(this.select2Config);
    this.form = this.$container.closest('form');
    this.update();
};

DefaultPrice.prototype.update = function () {
    console.log(this.form);
};




(function () {
    var $virtualRoom = $("#mbh_bundle_pricebundle_special_type_virtualRoom");
    var defaultPriceId = '#mbh_bundle_pricebundle_special_type_defaultPrice';
    $(defaultPriceId).select2();
    $virtualRoom.change(function (event) {
        var $form = $(this).closest('form');
        var data = {};
        data[$virtualRoom.attr('name')] = $virtualRoom.val();
        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: data,
            success: function (html) {
                $(defaultPriceId).select2("destroy");
                $(defaultPriceId).replaceWith(
                    $(html).find(defaultPriceId)
                );
                $(defaultPriceId).select2();

            }
        })
    });

})();