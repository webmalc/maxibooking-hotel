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
    
    //tariff choser
    (function() {
        var links  = $('#package-search-tariffs li a'),
            select = $('#s_tariff'),
            form   = $('form[name="s"]')
        ;
        links.click(function (e) {
            e.preventDefault();
            select.val($(this).attr('data-id'));
            form.submit();
        });
    }());
    
    //book button
    $('.package-search-book').click( function(e) {
        var numWrappper = $(this).find('span');
        var num = parseInt(numWrappper.text()) - 1;
        (num <= 0) ? num = 0 : num;
        numWrappper.text(num);
    });

});

