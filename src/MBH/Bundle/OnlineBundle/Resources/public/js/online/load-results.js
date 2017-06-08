/*global window, document */
window.onload = function() {
    var resultsWrapper = document.getElementById('mbh-results-wrapper');
    if (!resultsWrapper) {
        return;
    }
    var urlIndex = window.location.href.indexOf('?');
    var url = urlIndex !== -1 ? window.location.href.slice(urlIndex) : '';
    resultsWrapper.innerHTML = '<iframe id="mbh-results-iframe" scrolling="no" frameborder="0" width="100%" height="300" src="' + mbh.results_url + url +'"></iframe>';
    var resultsIframe = document.getElementById('mbh-results-iframe');
    var resize = function () {
        resultsIframe.style.height = resultsIframe.contentWindow.document.body.scrollHeight + 'px'; 
    };
    var processMessage = function (e) {
        if (e.data.type !== 'mbh') {
            return;
        }
        if (e.data.action == 'resize') {
            resultsIframe.style.height =  e.data.height > 300 ? e.data.height + 100 + 'px': '300px';
        }
    };
    if (window.addEventListener) {
	      window.addEventListener("message", processMessage, false);
    } else {
	      window.attachEvent("onmessage", processMessage);
    }
};
