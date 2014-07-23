/*global window */
$(document).ready(function() {
    'use strict';

    $('#s_begin').datepicker('setStartDate', new Date());

    //spinners
    $('#s_adults, #s_children').TouchSpin({
        min: 0,
        max: 20,
        step: 1,
        boostat: 2,
        maxboostedstep: 4
    });

    //search result prices
    (function() {
        var select = $('.search-food-select'),
                show = function(select) {

                    select.closest('tr').find('ul.package-search-prices').hide();
                    select.closest('tr').find('ul.package-search-prices li').hide();
                    select.closest('tr').find('ul.package-search-prices li.' + select.val() + '_food').show();
                    select.closest('tr').find('ul.package-search-prices').show();
                }
        ;
        select.click(function() {
            show($(this));
        });
        select.each(function() {
            show($(this));
        });
        
    }());

});

