/*global $, window, document, $, Translator */

var toggler = function(id) {
    $("#" + id).toggle();
};

var closePopovers = function() {
    'use strict';
    $('body').on('click', function(e) {
        //only buttons
        if ($(e.target).data('toggle') !== 'popover' && $(e.target).parents('.popover.in').length === 0) {
            $('[data-toggle="popover"]').popover('hide');
        }
    });
};

var getUrlVars = function() {
    'use strict';
    var vars = [],
        hash,
        hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
};

var getHashVars = function() {
    'use strict';
    var vars = [],
        hash,
        hashes = window.location.hash.slice(window.location.hash.indexOf('#') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = decodeURIComponent(hashes[i]).split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
};

var dangerTr = function() {
    'use strict';
    $('span.danger-tr').closest('tr').addClass('danger');
};

mbh.loader = {
    html: '<div class="alert alert-warning"><i class="fa fa-spinner fa-spin"></i>'+ Translator.trans("package.processing") +'...</div>',
    acceptTo: function($container) {
        $container.html(this.html);
    }
};

mbh.error = {
    html: '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>'+ Translator.trans("010-app.error_occured") +'.</div>',
    acceptTo: function($container) {
        $container.html(this.html);
    }
};

mbh.alert = {
    $alert: $('#entity-delete-confirmation'),
    show: function(href, header, text, buttonText, buttonIcon, buttonClass, action, $this, alertType) {
        $("#entity-delete-button").off('click').on('click', function(e) {
            e.preventDefault();
            if (action) {
                var actionType = typeof action;
                switch (actionType) {
                    case 'function':
                        action.call($this);
                        break;
                    case 'string':
                        mbh.utils.executeFunctionByName(action, window, $this); //eval(action + '($this)');
                        break;
                }
            } else if (href) {
                window.location.href = href;
            } else {
                throw new Error('...');
            }
        });

        $('#entity-delete-modal-header').html(header);
        $('#entity-delete-modal-text').html(text);
        $('#entity-delete-button-text').html(buttonText);
        $('#entity-delete-button-icon').attr('class', 'fa ' + buttonIcon);
        $('#entity-delete-button').attr('class', 'btn btn-' + buttonClass);
        this.$alert.addClass('modal-' + alertType);

        this.$alert.modal('show');
    },
    hide: function() {
        this.$alert.modal('hide');
    }
};

mbh.datatablesOptions = {
    dom: "12<'row'<'col-sm-6'Bl><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
    buttons: [{
            extend: 'excel',
            text: '<i class="fa fa-table" title="Excel" data-toggle="tooltip" data-placement="bottom"></i>',
            className: 'btn btn-default btn-sm',
            exportOptions: {
                stripNewlines: false
            }
        },
        {
            extend: 'pdf',
            text: '<i class="fa fa-file-pdf-o" title="PDF" data-toggle="tooltip" data-placement="bottom"></i>',
            className: 'btn btn-default btn-sm'
        }
    ]
};

mbh.highchartsOptions = {
    lang: {
        shortMonths: [
            Translator.trans("analytics.months.jan_abbr"),
            Translator.trans("analytics.months.feb_abbr"),
            Translator.trans("analytics.months.mar_abbr"),
            Translator.trans("analytics.months.apr_abbr"),
            Translator.trans("analytics.months.may_abbr"),
            Translator.trans("analytics.months.jun_abbr"),
            Translator.trans("analytics.months.jul_abbr"),
            Translator.trans("analytics.months.aug_abbr"),
            Translator.trans("analytics.months.sep_abbr"),
            Translator.trans("analytics.months.okt_abbr"),
            Translator.trans("analytics.months.nov_abbr"),
            Translator.trans("analytics.months.dec_abbr")
        ],
        months: [
            Translator.trans("analytics.months.jan"),
            Translator.trans("analytics.months.feb"),
            Translator.trans("analytics.months.mar"),
            Translator.trans("analytics.months.apr"),
            Translator.trans("analytics.months.may"),
            Translator.trans("analytics.months.jun"),
            Translator.trans("analytics.months.jul"),
            Translator.trans("analytics.months.aug"),
            Translator.trans("analytics.months.sep"),
            Translator.trans("analytics.months.okt"),
            Translator.trans("analytics.months.nov"),
            Translator.trans("analytics.months.dec")
        ],
        weekdays: [
            Translator.trans("analytics.days_of_week.sun"),
            Translator.trans("analytics.days_of_week.mon"),
            Translator.trans("analytics.days_of_week.tue"),
            Translator.trans("analytics.days_of_week.wed"),
            Translator.trans("analytics.days_of_week.thu"),
            Translator.trans("analytics.days_of_week.fri"),
            Translator.trans("analytics.days_of_week.sat")
        ],
        downloadJPEG: Translator.trans("analytics.downloadJPEG"),
        downloadPNG: Translator.trans("analytics.downloadPNG"),
        downloadPDF: Translator.trans("analytics.downloadPDF"),
        downloadSVG: Translator.trans("analytics.downloadSVG"),
        drillUpText: "",
        loading: Translator.trans("analytics.loading"),
        printChart: Translator.trans("analytics.printChart"),
        resetZoom: Translator.trans("analytics.resetZoom"),
        resetZoomTitle: Translator.trans("analytics.resetZoomTitle")
    }
};

mbh.bootstrapSwitchConfig = {
    'size': 'small',
    'onText': Translator.trans('app.bootstrap_switch_config.yes'),
    'offText': Translator.trans('app.bootstrap_switch_config.no'),
    'onColor': 'success'
};

$('#work-shift-lock').on('click', function(e) {
    e.preventDefault();
    var $this = $(this);
    var header = Translator.trans("010-app.shift_lock");
    var text = Translator.trans("010-app.sure_block_shift");
    var buttonText = Translator.trans("010-app.block");
    var buttonIcon = 'danger';
    var buttonClass = 'info';
    mbh.alert.show($this.attr('href'), header, text, buttonText, buttonIcon, buttonClass);
});

function isMobileDevice() {
    return /Mobi/.test(navigator.userAgent);
}

function isLowWidthDevice() {
    return document.documentElement.clientWidth < 768;
}

var deleteLink = function() {
    'use strict';
    $('.delete-link').on('click', function(event) {
        event.preventDefault();

        var $this = $(this);
        var href = ($this.attr('href')) ? $this.attr('href') : $this.attr('data-href');
        var action = $this.attr('data-action');

        var header = $this.attr('data-header') || $('#entity-delete-modal-header').attr('data-default');
        var text = $this.attr('data-text') || $('#entity-delete-modal-text').attr('data-default');
        var buttonText = $this.attr('data-button') || $('#entity-delete-button-text').attr('data-default');
        var buttonIcon = $this.attr('data-button-icon') || $('#entity-delete-button-icon').attr('data-default');
        var buttonClass = $this.attr('data-button-class') || $('#entity-delete-button').attr('data-default');
        var alertType = $this.attr('data-alert-type') || $('#entity-delete-confirmation').data('alert-type');
        mbh.alert.show(href, header, text, buttonText, buttonIcon, buttonClass, action, $this, alertType);

        $('.datepicker').datepicker({
            language: "ru",
            todayHighlight: true,
            autoclose: true
        });
    });
};
/*
var deleteLink = function () {
    'use strict';
    $('.delete-link').click(function (event) {
        event.preventDefault();

        var $this = $(this);
        var href = ($this.attr('href')) ? $this.attr('href') : $this.attr('data-href');
        var action = $this.attr('data-action');

        $("#entity-delete-button").unbind("click");

        $('#entity-delete-button').click(function (e) {
            e.preventDefault();
            if (action) {
                eval(action + '($this)');
            } else {
                window.location.href = href;
            }
        });

        if ($this.attr('data-header')) {
            $('#entity-delete-modal-header').html($this.attr('data-header'));
        } else {
            $('#entity-delete-modal-header').html($('#entity-delete-modal-header').attr('data-default'));
        }
        if ($this.attr('data-text')) {
            $('#entity-delete-modal-text').html($this.attr('data-text'));
        } else {
            $('#entity-delete-modal-text').html($('#entity-delete-modal-text').attr('data-default'));
        }
        if ($this.attr('data-button')) {
            $('#entity-delete-button-text').html($this.attr('data-button'));
        } else {
            $('#entity-delete-button-text').html($('#entity-delete-button-text').attr('data-default'));
        }

        if ($this.attr('data-button-icon')) {
            $('#entity-delete-button-icon').attr('class', 'fa ' + $this.attr('data-button-icon'));
        } else {
            $('#entity-delete-button-icon').attr('class', 'fa ' + $('#entity-delete-button-icon').attr('data-default'));
        }

        if ($this.attr('data-button-class')) {
            $('#entity-delete-button').attr('class', 'btn btn-' + $this.attr('data-button-class'));
        } else {
            $('#entity-delete-button').attr('class', 'btn btn-' + $('#entity-delete-button').attr('data-default'));
        }

        $('.datepicker').datepicker({
            language: "ru",
            todayHighlight: true,
            autoclose: true
        });

        $('#entity-delete-confirmation').modal();
    });
};*/


$(document).ready(function() {
    'use strict';
    if (isLowWidthDevice()) {
        var $logoBlock = $('header.main-header > .logo');
        var logoBlockHeight = parseInt($logoBlock.css('height'), 10);
        $(window).scroll(function () {
            var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
            if (scrollTop > logoBlockHeight) {
                $logoBlock.hide();
            } else {
                $logoBlock.show();
            }
        });
    }

    var workShiftMenu = $('#work-shift-menu');
    if (workShiftMenu.length == 1) {
        $('#logout-btn').on('click', function(e) {
            e.preventDefault();
            mbh.alert.show(this.href,
                Translator.trans("010-app.work_shift_not_closed"),
                Translator.trans("010-app.work_shift_not_closed"),
                Translator.trans("010-app.go_out"),
                'fa-sign-out',
                'danger');
        })
    }

    //scrolling height
    (function() {
        if (!$('.scrolling').length || isLowWidthDevice()) {
            return null;
        }
        var height = function() {
            var isActionsPanelExists = $('#actions').length > 0;
            if (!isActionsPanelExists) {
                document.body.style.paddingBottom = 0;
            }
            var bottomOffset = isActionsPanelExists ? 85 : 45;
            $('.scrolling').height(function() {
                return $(window).height() - $(this).offset().top - bottomOffset;
            });
        };
        height();
        setInterval(height, 500);
    }());

    //Tooltips configuration
    $('[data-toggle="tooltip"]').tooltip();

    //delete link
    deleteLink();

    //autohide messages
    window.setTimeout(function() {
        $(".autohide").fadeTo(400, 0).slideUp(400, function() {
            $(this).remove();
        });
    }, 5000);

    //fancybox
    $('.fancybox').fancybox({
        'type': 'image'
    });
    $('.image-fancybox').fancybox({
        'type': 'image'
    });

    //popovers
    $('[data-toggle="popover"]').popover();
    closePopovers();

    //sidebar
    (function() {
        'use strict';

        $('.sidebar-toggle').click(function() {
            if ($('body').hasClass('sidebar-collapse')) {
                localStorage.setItem('sidebar-collapse', 'open');
            } else {
                localStorage.setItem('sidebar-collapse', 'close');
            }
        });
    }());

    //dashboard
    (function() {
        'use strict';
        $('.dashboard-confirm-button').click(function() {
            var that = $(this);
            var hideMessage = function (response) {
                that.parent('div.alert').alert('close');
                var num = $('.dashboard-confirm-button').length;
                var conuter = $('#dashboard-counter');
                if (conuter) {
                    conuter.html(num);
                    if (!num) {
                        $('#dashboard-notifications').hide();
                    }
                }
            };
            $.ajax({
                url: Routing.generate('dashboard_confirm', {id: that.attr('data-id')}),
                beforeSend: function () {
                    that.html('<i class="fa fa-spinner fa-spin"></i>');
                },
                success: hideMessage,
                error: hideMessage
            });
        });

        if (!mbh.justLogin) {
            return;
        }
        var link = $('#dashboard-link');
        if (link.length) {
            link.trigger('click');
        }
    }());
    initSupportModal();
});

var $taskCounter = $('#task-counter');
var updateTaskCounter = function() {
    $.ajax({
        url: Routing.generate('task_ajax_total_my_open'),
        dataType: 'json',
        success: function(response) {
            if (response.total == 0) {
                $taskCounter.html('');
            } else {
                $taskCounter.html(response.total);
            }
        }
    });
};

var delay = 1000 * 60 * 5; //5 minutes
setInterval(function() {
    updateTaskCounter();
}, delay);

function initSupportModal() {
    var allowedGuides = mbh['allowed_guides'];
    $('#support-link').click(function () {
        $('#support-info-modal').modal('show');
        var $modalGuidesList = $('#modal-guides-list');
        if (isMobileDevice() && allowedGuides.length > 0) {
            $modalGuidesList.closest('li').remove();
        } else if ($modalGuidesList.find('li').length === 0) {
            for (var guideId in GUIDES_BY_NAMES) {
                var guideListItem = document.createElement('li');
                var linkElement = document.createElement('a');

                linkElement.innerHTML = GUIDES_BY_NAMES[guideId].name;
                linkElement.setAttribute('data-guide-id', guideId);
                guideListItem.appendChild(linkElement);
                $modalGuidesList.append(guideListItem);

                linkElement.onclick = function () {
                    $('#support-info-modal').modal('hide');
                    var guideId = this.getAttribute('data-guide-id');
                    var guides = GUIDES_BY_NAMES[guideId].guides;
                    runGuides(guides);
                };
            }
        }
    });
}