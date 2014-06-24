/*global window */
$(document).ready(function () {
    'use strict';

    //Tooltips configuration
    $('a[data-toggle="tooltip"], li[data-toggle="tooltip"]').tooltip();
    
    //BootstrapSwitch configuration
    $('input[type="checkbox"][class!="plain-html"]').bootstrapSwitch({
        'size': 'small',
        'onText': 'да',
        'offText': 'нет',
        'labelText': '<i class="fa fa-arrows-h" style="opacity: 0.6;"></i>'
    });

    //Select2 configuration
    $('select[class!="plain-html"]').select2({
        placeholder: "Сделайте выбор",
        allowClear: true,
        formatSelection: function (item, container) {
            var optgroup = $(item.element).parent('optgroup').attr('label');
            if(!optgroup) {
                return item.text;
            } else {
                return optgroup + ': ' + item.text.toLowerCase();
            }
        }
    });
});

