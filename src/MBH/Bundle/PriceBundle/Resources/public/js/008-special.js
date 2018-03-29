/*global window, $, document, mbh, Routing, deleteLink */
$(document).ready(function () {
    'use strict';
    $('#special-packages-list').readmore({
        collapsedHeight: 20,
        lessLink: '<a class="text-right" href="#">скрыть брони</a>',
        moreLink: '<a class="text-right" href="#">показать брони</a>'
    });

    var specialFilterForm = $('#special-filter-form'),
        specialTable = $('#special-table'),
        process = false,
        $isStrict = $('#isStrict'),
        $begin = $('input#begin'),
        $end = $('input#end');

    specialTable.dataTable({
        "processing": true,
        "serverSide": true,
        "ordering": false,
        "drawCallback": function() {
            process = false;
            deleteLink();
            $('.disabled-entry').closest('tr').addClass('danger');
            specialSelectorsRedraw();

        },
        "ajax": {
            "method": "POST",
            "url": Routing.generate('special'),
            "data": function (requestData) {
                process = true;
                requestData.form = specialFilterForm.serializeObject();
                return requestData;
            }
        }
    });

    var isStrictCheck = function () {
        var isBeginExist = $begin.datepicker("getDate");
        var isEndExist = $end.datepicker("getDate");
        if(!isBeginExist || !isEndExist) {
            $isStrict.bootstrapSwitch("state", false);
            $isStrict.bootstrapSwitch("disabled", true);
        } else {
            $isStrict.bootstrapSwitch("disabled", false)
        }
    };
    specialFilterForm.find('input, select').on('change switchChange.bootstrapSwitch', function () {
        if (!process) {
            specialTable.dataTable().fnDraw();
        }
    });
    $begin.add($end).on('clearDate changeDate ', function () {
        isStrictCheck();
    });
    isStrictCheck();

    var specialSelectors = function () {
        var $checkAll = $('#check-all-special');
        $checkAll.iCheck({
            checkboxClass: 'icheckbox_minimal-blue'
        });
        $checkAll.on('ifChecked', function () {
            $('input[type="checkbox"].promotion-apply').iCheck('check');
        });
        $checkAll.on('ifUnchecked', function () {
            $('input[type="checkbox"].promotion-apply').iCheck('uncheck');
        });

    };
    var specialSelectorsRedraw = function () {
        $('input[type="checkbox"].promotion-apply').iCheck({checkboxClass: 'icheckbox_minimal-green'});
        $('#check-all-special').iCheck('uncheck');
    };

    specialSelectors();

    var batchModal = function () {
        var $batchModal = $("#batch_modal");
        $batchModal.on("show.bs.modal", function (e){
            var link = e.relatedTarget.attr('href');
            var specialsIds = [];
            $(this).find('.modal-body').load(link, {}, function (data) {
                var $selected = getSelected();
                $.each($selected, function () {
                    specialsIds.push($(this).data('special'));
                });
                $('.modal-body').find('input.special-input').val(specialsIds.join(","));
            })
        });
        $batchModal.on("hidden.bs.modal", function (e) {
            $(this).removeData();
        });
        var $link = $("a.batch-link");
        $link.on('click', function (e) {
            e.preventDefault();
            $batchModal.modal('show', $(this));
        });
        var getSelected = function () {
            return $('input[type="checkbox"]:checked.promotion-apply');
        }
    };

    batchModal();

});