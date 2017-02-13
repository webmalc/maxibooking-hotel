/*global window, document, $, Routing, console, mbh */

$(document).ready(function ($) {
    'use strict';
    $('#dynamic-sales-filter-begin2').val('');
    $('#dynamic-sales-filter-begin3').val('');
    //Show table
    var pricesProcessing = false,
        showTable = function () {
            var wrapper = $('#dynamic-sales-table-wrapper'),
                begin = [],
                end = [];
            $.each( $('.dynamic-sales-filter'),function (i, val) {

                if($(this).val().length){
                    begin[i] = $(this).data('daterangepicker').startDate._d;
                    end[i] = $(this).data('daterangepicker').endDate._d;
                }

            });

            var data = {
                'begin': begin,
                'end': end,
                'roomTypes': $('#dynamic-sales-filter-roomType').val()
            };

            if (wrapper.length === 0) {
                return false;
            }
            wrapper.html('<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i> Подождите...</div>');
            if (!pricesProcessing) {
                $.ajax({
                    url: Routing.generate('dynamic_sales_table'),
                    data: data,
                    beforeSend: function () {
                        pricesProcessing = true;
                    },
                    success: function (data) {
                        wrapper.html(data);
                        pricesProcessing = false;
                    },
                    dataType: 'html'
                });
            }
        };

    $('#dynamic-sales-filter-begin').daterangepicker({
        "autoApply": true
    });

    $('#dynamic-sales-filter-begin2').daterangepicker({
        autoUpdateInput: false,
        "autoApply": true
    });

    $('#dynamic-sales-filter-begin3').daterangepicker({
        autoUpdateInput: false,
        "autoApply": true
    });

    $('#dynamic-sales-filter-begin2').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
    });

    $('#dynamic-sales-filter-begin2').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    $('#dynamic-sales-filter-begin3').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
    });

    $('#dynamic-sales-filter-begin3').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    showTable();

    $('#dynamic-sales-submit-button').click(function (event) {
        event.preventDefault();
        showTable();
    });

});