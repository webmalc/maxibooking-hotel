/*global window, document */
if (typeof(mbh) !== 'undefined') {
    var config = mbh;
} else if (typeof(mbhForm) !== 'undefined') {
    var config = mbhForm;
}
function addLoadEvent(func) {
    var oldonload = window.onload;
    if (typeof window.onload !== 'function') {
        window.onload = func;
    } else {
        window.onload = function() {
            if (oldonload) {
                oldonload();
            }
            func();
        };
    }
}
addLoadEvent(function() {
    var getCoords = function(elem) {
        var box = elem.getBoundingClientRect();

        var body = document.body;
        var docEl = document.documentElement;

        var scrollTop = window.pageYOffset || docEl.scrollTop || body.scrollTop;
        var scrollLeft = window.pageXOffset || docEl.scrollLeft || body.scrollLeft;

        var clientTop = docEl.clientTop || body.clientTop || 0;
        var clientLeft = docEl.clientLeft || body.clientLeft || 0;

        var top  = box.top +  scrollTop - clientTop;
        var left = box.left + scrollLeft - clientLeft;

        return { top: Math.round(top), left: Math.round(left) };
    };
    
    var formWrapper = document.getElementById('mbh-form-wrapper');
    if (!formWrapper) {
        return;
    }
    var urlIndex = window.location.href.indexOf('?');
    var url = urlIndex !== -1 ? window.location.href.slice(urlIndex) : '';
    var iframeWidth = typeof(frameWidth) !== 'undefined' ? frameWidth : 300;
    var iframeHeight = typeof(frameHeight) !== 'undefined' ? frameHeight : 400;
    formWrapper.innerHTML = '<iframe id="mbh-form-iframe" scrolling="no" frameborder="0" width="'
        + iframeWidth + '" height="' + iframeHeight + '" src="' + config.form_url + url +'"></iframe>';
    document.body.innerHTML += '<iframe id="mbh-form-calendar" style="display: none; z-index: 10; position: absolute; top: 0px;" scrolling="no" frameborder="0" width="310" height="270" src="' + config.calendar_url + '"></iframe>';

    var formIframe = document.getElementById('mbh-form-iframe');
    var formCalendar = document.getElementById('mbh-form-calendar');
    var hideCalendar = function() {
        formCalendar.style.display = 'none';
    };
    var processMessage = function (e) {
        if (e.data.type !== 'mbh') {
            return;
        }
        var target = null;
        if (e.data.target === 'form') {
            target = formIframe; 
        }
        if (e.data.target === 'calendar') {
            target = formCalendar; 
        }
        if (target) {
            target.contentWindow.postMessage(e.data, '*');
        }
        if (e.data.action === 'showCalendar') {
            var c = getCoords(formIframe);
            formCalendar.style.display = 'block';
            formCalendar.style.top = (e.data.top + c.top - 10) + 'px';
            formCalendar.style.left = (e.data.left + c.left) + 'px';
            formCalendar.contentWindow.postMessage(e.data, '*');
        }
        if (e.data.action === 'hideCalendar') {
            hideCalendar();
        }
    };

    document.addEventListener("click", hideCalendar); 
    document.addEventListener("keyup", function(e) {
        if (e.keyCode == 27) {
            hideCalendar();
        }
    }); 

    if (window.addEventListener) {
	      window.addEventListener("message", processMessage, false);
    } else {
	      window.attachEvent("onmessage", processMessage);
    }
});
