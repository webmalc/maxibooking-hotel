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
        "drawCallback": function () {
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
        if (!isBeginExist || !isEndExist) {
            $isStrict.bootstrapSwitch("state", false);
            $isStrict.bootstrapSwitch("disabled", true);
        } else {
            $isStrict.bootstrapSwitch("disabled", false)
        }
    };
    specialFilterForm.find('input, select').on('change switchChange.bootstrapSwitch', function () {
        refreshDataTable();
    });

    var refreshDataTable = function () {
        if (!process) {
            specialTable.dataTable().fnDraw();
        }
    };


    $begin.add($end).on('clearDate changeDate ', function () {
        isStrictCheck();
    });
    isStrictCheck();

    var $alertSuccess = $('div#alert-success');
    var $alertWarning = $('div#alert-warning');
    var $alertDanger = $('div#alert-danger');

    var alertInit = function () {
        $('.alert-close').on('click', function () {
            $(this).parent().hide();
        })
    };

    var hideWarningAlertIfVisible = function () {
        if ($alertWarning.is(":visible")) {
            $alertWarning.hide();
        }
    };

    var hideAllWarnings = function () {
        $('.alert').hide();
    };

    alertInit();

    var specialSelectors = function () {
        var $checkAll = $('#check-all-special');
        $checkAll.iCheck({
            checkboxClass: 'icheckbox_minimal-blue'
        });
        $checkAll.on('ifChecked', function () {
            hideWarningAlertIfVisible();
            $('input[type="checkbox"].promotion-apply').iCheck('check');
        });
        $checkAll.on('ifUnchecked', function () {
            $('input[type="checkbox"].promotion-apply').iCheck('uncheck');
        });

    };
    var specialSelectorsRedraw = function () {
        var $specCheckboxes = $('input[type="checkbox"].promotion-apply');
        $specCheckboxes.iCheck({checkboxClass: 'icheckbox_minimal-green'});
        $('#check-all-special').iCheck('uncheck');
        //# Висит на строке двойной щелчек. Надо будет убрать. Костыли ниже не помогают.
        //# Костыль
        $specCheckboxes.on('ifChecked', function (e) {
            e.stopPropagation();
        });
        $specCheckboxes.on('ifUnchecked', function (e) {
            e.stopPropagation();
        });
        $('.icheckbox_minimal-green').on('click', function (e) {
            e.stopPropagation();
        })
        //# /Костыль

    };

    specialSelectors();


    var batchModal = function () {
        var $batchModal = $("#batch_modal");
        var $submitButton = $batchModal.find('#submit-button');
        $batchModal.on("show.bs.modal", function (e) {
            var link = e.relatedTarget.attr('href');
            var title = e.relatedTarget.attr('title');
            var specialsIds = [];
            var $modal = $(this);
            $modal.find('#ModalLongTitle').text(title);
            $modal.find('.modal-body').load(link, {}, function (data) {
                var $selected = getSelected();
                $.each($selected, function () {
                    specialsIds.push($(this).data('special'));
                });
                var $modalBody = $('.modal-body');
                $modalBody.find('input.special-input').val(specialsIds.join(","));
                var $form = $modalBody.find('form');
                $form.on('submit', function (e) {
                    e.preventDefault();
                    hideAllWarnings();
                    mbh.loader.acceptTo($('.modal-span'));
                    var $sendForm = $(this);
                    $.ajax({
                        type: $sendForm.attr('method'),
                        url: $sendForm.attr('action'),
                        data: $sendForm.serialize()
                    })
                        .done(function () {
                            $alertSuccess.show();
                        })
                        .fail(function () {
                            $alertDanger.show();
                        })
                        .always(function () {
                            $modal.modal('hide');
                            $('.modal-span').empty();
                        })
                    ;
                });
                $submitButton.on('click', function () {
                    $form.submit();
                })

            })
        });
        $batchModal.on("hidden.bs.modal", function (e) {
            $(this).removeData();
            $submitButton.off('click');
            refreshDataTable();
        });
        var $link = $("a.batch-link");
        $link.on('click', function (e) {
            e.preventDefault();
            if (getSelected().length) {
                hideWarningAlertIfVisible();
                $batchModal.modal('show', $(this));
            } else {
                $alertWarning.show();
            }
        });
        var getSelected = function () {
            return $('input[type="checkbox"]:checked.promotion-apply');
        }
    };

    batchModal();

});