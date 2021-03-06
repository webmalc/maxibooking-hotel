/**
 * Для всех новых клиентов актуально loadResult.js.twig
 */
/*global window, document */
if (typeof(mbh) !== 'undefined') {
    var configResults = mbh;
} else if (typeof(mbhResults) !== 'undefined') {
    var configResults = mbhResults;
}

function addLoadEvent(func) {
    var oldonload = window.onload;
    if (typeof window.onload != 'function') {
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

function onLoadResultsLoad() {
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
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

        ga('create', googleCounterId, 'auto');
        ga('send', 'pageview');
    }

    var resultsWrapper = document.getElementById('mbh-results-wrapper');
    if (!resultsWrapper) {
        return;
    }

    var urlIndex = window.location.href.indexOf('?');
    var url = '';
    if (urlIndex !== -1) {
        url = window.location.href.slice(urlIndex);
    }
    if (configResults.results_url.indexOf('?') > -1) {
        url = url.replace('?', '&');
    }

    resultsWrapper.innerHTML = '<iframe id="mbh-results-iframe" title="Frame with result" scrolling="no" frameborder="0" width="100%" height="300" src="' + configResults.results_url + url + '"></iframe>';
    var resultsIframe = document.getElementById('mbh-results-iframe');
    var resize = function () {
        resultsIframe.style.height = resultsIframe.contentWindow.document.body.scrollHeight + 'px';
    };
    var processMessage = function (e) {
        if (e.data.type !== 'mbh') {
            return;
        }
        if (e.data.action === 'resize') {
            resultsIframe = document.getElementById('mbh-results-iframe');
            resultsIframe.style.height = e.data.height > 300 ? e.data.height + 'px' : '300px';
        }
    };
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
        window.addEventListener("message", processMetricMessage, false);
    } else {
        window.attachEvent("onmessage", processMessage);
        window.attachEvent("onmessage", processMetricMessage);
    }
    window.onscroll = function () {
        if (resultsIframe.contentWindow) {
            var frameTopOffset = resultsIframe.getBoundingClientRect().top;
            resultsIframe.contentWindow.postMessage({type: 'onScroll', frameTopOffset: frameTopOffset}, '*')
        }
    }
}
addLoadEvent(onLoadResultsLoad);