/*global window */

var setSearchDatepickers = function(date){
    (typeof(date) === 'undefined') ? date = new Date() : date = new Date(date);
    $('#s_begin').datepicker('setStartDate', date);
};

$(document).ready(function() {
    'use strict';

    //spinners
    $('#s_adults, #s_children').TouchSpin({
        min: 0,
        max: 20,
        step: 1,
        boostat: 2,
        maxboostedstep: 4
    });

    //ajax request
    (function() {

        var send = function (query) {
            var wrapper = $('#package-search-results-wrapper');
            $.ajax({
                url: Routing.generate('package_search_results'),
                data: query,
                success: function (data) {
                    wrapper.html(data);

                    $('table.table-striped').dataTable();

                    // select2
                    (function () {
                        var format = function (icon) {
                            var arr = icon.id.split('_'),
                                text = '';
                            ;
                            for (var i = 1; i <= arr[0]; i++) {
                                text += '<i class="fa fa-male"></i> ';
                            }
                            for (var i = 1; i <= arr[1]; i++) {
                                text += '<small><i class="fa fa-child"></i></small>';
                            }
                            return text;

                        };

                        $('.search-tourists-select').each(function () {
                            $(this).select2({
                                placeholder: '',
                                allowClear: false,
                                width: 'element',
                                minimumResultsForSearch: -1,
                                formatResult: format,
                                formatSelection: format
                            });
                        });
                        $('.search-food-select').each(function () {
                            $(this).select2({
                                placeholder: '',
                                allowClear: false,
                                minimumResultsForSearch: -1,
                                width: 'element'
                            });
                        });
                    }());

                    //search result prices
                    (function() {
                        var show = function(tr) {
                                var tourist = tr.find('.search-tourists-select'),
                                    select  = tr.find('.search-food-select'),
                                    selectVal = select.select2('data').id,
                                    touristVal = tourist.select2('data').id,
                                    touristArr = touristVal.split('_')
                                    ;
                                tr.find('ul.package-search-prices').hide();
                                tr.find('ul.package-search-prices li').hide();
                                tr.find('ul.package-search-prices li.' + touristVal + '_' + selectVal + '_food').show();
                                tr.find('ul.package-search-prices').show();
                                var bookLink = tr.find('a.package-search-book'),
                                    oldHref = bookLink.prop('href')
                                        .replace(/&food=.*?(?=(&|$))/, '')
                                        .replace(/&adults=.*?(?=(&|$))/, '')
                                        .replace(/&children=.*?(?=(&|$))/, '')
                                    ;

                                bookLink.prop('href', oldHref + '&food=' + selectVal + '&adults=' + touristArr[0] + '&children=' + touristArr[1]);
                            }
                            ;
                        $('.search-food-select, .search-tourists-select').click(function() {
                            show($(this).closest('tr'));
                        });
                        $('.search-food-select, .search-tourists-select').each(function() {
                            show($(this).closest('tr'));
                        });
                    }());

                    //tariff chooser
                    (function() {
                        var links  = $('#package-search-tariffs li a'),
                            select = $('#s_tariff'),
                            form   = $('form[name="s"]')
                            ;
                        links.click(function (e) {
                            e.preventDefault();
                            select.val($(this).attr('data-id'));
                            window.location.hash = form.serialize();
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
                }
            });
        }

        if (window.location.hash) {
            send(window.location.hash.replace('#', ''));
        }

        $('.search-form').submit(function(e){
            e.preventDefault();
            var wrapper = $('#package-search-results-wrapper');
            window.location.hash = $(this).serialize();
            wrapper.html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');
            send($(this).serialize())

        });
    }());

});

