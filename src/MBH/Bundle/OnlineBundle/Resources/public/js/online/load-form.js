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
        window.onload = function () {
            if (oldonload) {
                oldonload();
            }
            func();
        };
    }
}

function onLoadFormLoad() {
    var getCoords = function (elem) {
        var box = elem.getBoundingClientRect();

        var body = document.body;
        var docEl = document.documentElement;

        var scrollTop = window.pageYOffset || docEl.scrollTop || body.scrollTop;
        var scrollLeft = window.pageXOffset || docEl.scrollLeft || body.scrollLeft;

        var clientTop = docEl.clientTop || body.clientTop || 0;
        var clientLeft = docEl.clientLeft || body.clientLeft || 0;

        var top = box.top + scrollTop - clientTop;
        var left = box.left + scrollLeft - clientLeft;

        return {top: Math.round(top), left: Math.round(left)};
    };

    var useYaMetrics = typeof yaCounterId !== 'undefined';
    if (useYaMetrics) {
        var yaCounterObjName = 'yaCounter' + yaCounterId;
        <!-- Yandex.Metrika counter -->
        (function (d, w, c) {
            (w[c] = w[c] || []).push(function () {
                try {
                    w[yaCounterObjName] = new Ya.Metrika({
                        id: yaCounterId,
                        clickmap: true,
                        trackLinks: true,
                        accurateTrackBounce: true,
                        webvisor: true
                    });
                } catch (e) {
                }
            });

            var n = d.getElementsByTagName("script")[0],
                s = d.createElement("script"),
                f = function () {
                    n.parentNode.insertBefore(s, n);
                };
            s.type = "text/javascript";
            s.async = true;
            s.src = "https://mc.yandex.ru/metrika/watch.js";

            if (w.opera == "[object Opera]") {
                d.addEventListener("DOMContentLoaded", f, false);
            } else {
                f();
            }
        })(document, window, "yandex_metrika_callbacks");
        <!-- /Yandex.Metrika counter -->
    }

    var useGoogleMetrics = typeof googleCounterId !== 'undefined';
    if (useGoogleMetrics) {
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', googleCounterId, 'auto');
        ga('send', 'pageview');
    }

    var formWrapper = document.getElementById('mbh-form-wrapper');
    if (!formWrapper) {
        return;
    }

    var urlIndex = window.location.href.indexOf('?');
    var url;
    if (urlIndex !== -1) {
        url = window.location.href.slice(urlIndex);
    } else {
        url = '?url=' + window.location.pathname;
    }
    if (config.form_url.indexOf('?') > -1) {
        url = url.replace('?', '&');
    }

    var iframeWidth = typeof(frameWidth) !== 'undefined' ? frameWidth : 300;
    var iframeHeight = typeof(frameHeight) !== 'undefined' ? frameHeight : 400;

    formWrapper.innerHTML = '<iframe id="mbh-form-iframe" scrolling="no" frameborder="0" width="'
        + "auto" + '" height="' + "auto" + '" src="' + config.form_url + url + '"></iframe>';

    var calendarFrame = document.createElement('iframe');
    calendarFrame.id = 'mbh-form-calendar';
    calendarFrame.style = 'display: none; z-index: 1000; position: absolute; top: 0px;';
    calendarFrame.setAttribute('scrolling', "no");
    calendarFrame.setAttribute('frameborder', 0);
    calendarFrame.setAttribute('width', 310);
    calendarFrame.setAttribute('height', 270);
    calendarFrame.setAttribute('src', config.calendar_url);

    document.body.appendChild(calendarFrame);

    var formIframe = document.getElementById('mbh-form-iframe');
    var formCalendar = document.getElementById('mbh-form-calendar');
    var hideCalendar = function () {
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
    document.addEventListener("keyup", function (e) {
        if (e.keyCode === 27) {
            hideCalendar();
        }
    });

    var resizeIframeWidth = function () {
            var width = window.outerWidth;
            if (formIframe && width) {
                formIframe.width = width < iframeWidth ? width : iframeWidth;
            }
        },
        resizeIframeHeight = function (event) {
            if (event.data.type !== 'mbh') {
                return;
            }
            if (event.data.action === 'formResize') {
                var formIframe = document.getElementById("mbh-form-iframe");
                formIframe.height = event.data.formHeight + 5;
            }
        };
    setInterval(function () {
        resizeIframeWidth();
    }, 300);
    var processMetricMessage = function (e) {
        if (e.data.type === 'form-event') {
            var purposeType = e.data.purpose;
            if (useYaMetrics) {
                window[yaCounterObjName].reachGoal(purposeType);
            }
            if (useGoogleMetrics) {
                ga('send', 'event', purposeType, 'click');
            }
        }
    };

    if (window.addEventListener) {
        window.addEventListener("message", processMessage, false);
        window.addEventListener("message", resizeIframeHeight, false);
        window.addEventListener("message", processMetricMessage, false);
    } else {
        window.attachEvent("onmessage", processMessage);
        window.attachEvent("onmessage", resizeIframeHeight);
        window.attachEvent("onmessage", processMetricMessage);
    }
}

addLoadEvent(onLoadFormLoad);

