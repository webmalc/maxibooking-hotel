/*global window */
$(document).ready(function () {
    'use strict';

    //Send delete form
    $('#entity-delete-button').click(function (event) {
        event.preventDefault();
        $('#entity-delete-form').submit();
    });
    
    //BootstrapSwitch configuration
    $('input[type="checkbox"]').not('.plain-html').bootstrapSwitch({
        'size': 'small',
        'onText': 'да',
        'offText': 'нет',
        'labelText': '<i class="fa fa-arrows-h" style="opacity: 0.6;"></i>',
    });

    //Select2 configuration
    $('select').not('.plain-html').select2({
        placeholder: "Сделайте выбор",
        allowClear: true,
        formatSelection: function(item, container) {
            var optgroup = $(item.element).parent('optgroup').attr('label');
            if (!optgroup) {
                return item.text;
            } else {
                return optgroup + ': ' + item.text.toLowerCase();
            }
        }
    });
    
    //
    $('.form-horizontal, .form-inline, .are-you-sure').areYouSure({'message': 'Внесенные изменения не сохранены!'});
    
    //Datepicker configuration
    $('.datepicker').datepicker({
        language: "ru",
        todayHighlight: true,
        autoclose: true
    });
    
    (function(){
        var begin = $('.begin-datepiker'),
            end =   $('.end-datepiker'),
            set = function () {
                begin.datepicker('setEndDate', end.datepicker('getDate'));
                end.datepicker('setStartDate', begin.datepicker('getDate'));
            };
            
            set();
            
            begin.change(function(){
                set();
                if (!end.val()) {
                    end.focus();
                }
            });
            end.change(function(){
                set();
                if (!begin.val()) {
                    begin.focus();
                }
            });
    }());

});

