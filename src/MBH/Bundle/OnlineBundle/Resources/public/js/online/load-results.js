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
        window.onload = function() {
            if (oldonload) {
                oldonload();
            }
            func();
        };
    }
}
addLoadEvent(function() {
    var yaCounterObjName = 'yaCounter' + yaCounterId;
    <!-- Yandex.Metrika counter -->
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w[yaCounterObjName] = new Ya.Metrika({
                    id: yaCounterId,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    webvisor:true
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
    <!-- /Yandex.Metrika counter -->

    var resultsWrapper = document.getElementById('mbh-results-wrapper');
    if (!resultsWrapper) {
        return;
    }
    var urlIndex = window.location.href.indexOf('?');
    var url = urlIndex !== -1 ? window.location.href.slice(urlIndex) : '';
    resultsWrapper.innerHTML = '<iframe id="mbh-results-iframe" scrolling="no" frameborder="0" width="100%" height="300" src="' + configResults.results_url + url +'"></iframe>';
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
            resultsIframe.style.height =  e.data.height > 300 ? e.data.height + 'px' : '300px';
        }
    };
    var processMetricMessage = function (e) {
        if (e.data.type === 'form-event') {
            var purposeType = e.data.purpose;
            window[yaCounterObjName].reachGoal(purposeType);
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
        var frameTopOffset = resultsIframe.getBoundingClientRect().top;
        resultsIframe.contentWindow.postMessage({type: 'onScroll', frameTopOffset: frameTopOffset}, '*')
    }
});
