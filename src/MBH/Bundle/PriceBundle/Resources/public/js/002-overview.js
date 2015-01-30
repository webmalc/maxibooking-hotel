/*global window */
$(document).ready(function() {
    'use strict';

    //Show table
    var showTable = function () {
        var wrapper = $('#prices-overview-table-wrapper'),
            begin = $('#prices-overview-filter-begin'),
            end = $('#prices-overview-filter-end'),
            data = {
                'begin': begin.val(),
                'end': end.val(),
                'roomTypes': $('#prices-overview-filter-roomType').val(),
                'tariffs': $('#prices-overview-filter-tariff').val()
            },
            inputs = function () {
                var input = $('.prices-overview-table-td-price input');

                input.keyup(function () {
                    this.value = this.value.replace(/[^0-9]+?/g,'');
                });

                input.change(function () {
                    if(!$(this).val()) {
                        $(this).siblings('small').show();
                        $(this).remove();
                    }

                    if (parseInt(this.value, 10) < 0) {
                        this.value = 0;
                    }
                });
            },
            links = function () {
                $('.prices-overview-table-td-price small.link').each(function(){
                    if ($(this).siblings('input').length) {
                        $(this).hide();
                    }
                })
                $('.prices-overview-table-td-price small.link').click(function() {
                    var val = parseInt($(this).text(), 10);
                    if (isNaN(val)) {
                        val = 0;
                    }
                    $(this).hide();
                    $(this).closest('td').append('<input name="prices'+ $(this).attr('data-input-name') +'" type="number" class="prices-overview-table-input form-control input-sm" value="' + val + '">');
                    $(this).siblings('input').focus();
                    inputs();
                });
            }
            ;

        if (wrapper.length === 0) {
            return false;
        }
        wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');

        $.ajax({
            url: Routing.generate('prices_overview_table'),
            data: data,
            success: function (data) {
                wrapper.html(data);
                begin.val($('#prices-overview-begin').val());
                end.val($('#prices-overview-end').val());
                links();
                inputs();

            },
            dataType: 'html'
        });

    }

    showTable();
    $('.prices-overview-filter').change(function () { showTable() });
});
