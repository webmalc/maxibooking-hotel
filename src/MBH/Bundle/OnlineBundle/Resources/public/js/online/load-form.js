/*global window, document */
if (typeof(mbh) !== 'undefined') {
    var config = mbh;
} else if (typeof(mbhForm) !== 'undefined') {
    var config = mbhForm;
}

var isMobileDevice = (function() {
    var isMobile = /Mobi/.test(navigator.userAgent);
    if (isMobile) {
        isMobile = !(/iPad|vivo/.test(navigator.userAgent));
    }
    return function() {
        return isMobile;
    }
})();

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

function OnLoadFormLoad() {
    this.formWrapper = null;
    this.spinner = null;
    this.formCalendar = null;
    this.formIframe = null;
    this.iframeWidth = typeof(frameWidth) !== 'undefined' ? frameWidth : 300;
    this.iframeHeight = typeof(frameHeight) !== 'undefined' ? frameHeight : 400;
    this.itIsFirstLoad = true;
}

OnLoadFormLoad.prototype.createIframeWithCalendar = function() {
    var calendarFrame = document.createElement('iframe');
    calendarFrame.id = 'mbh-form-calendar';
    calendarFrame.style.display = 'none';
    calendarFrame.style.zIndex = '1000';
    calendarFrame.style.position = 'absolute';
    calendarFrame.style.top = '0px';
    calendarFrame.setAttribute('scrolling', "no");
    calendarFrame.setAttribute('frameborder', 0);
    calendarFrame.setAttribute('width', 310);
    calendarFrame.setAttribute('height', 270);
    calendarFrame.setAttribute('src', config.calendar_url);
    calendarFrame.setAttribute('title', 'Support frame with calendar for search form.');

    document.body.appendChild(calendarFrame);

    return calendarFrame;
};

OnLoadFormLoad.prototype.waitingSpinner =  function() {
    var text = /\.ru/.test(window.location.hostname) ? 'Подождите...' : 'Please wait...',
        spinner = document.createElement('div');
    spinner.id = 'mbh-form-load-spinner';
    spinner.className = 'alert alert-info';
    spinner.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + text;

    this.formWrapper.appendChild(spinner);

    return spinner;
};

OnLoadFormLoad.prototype.getCoords = function () {
    var box = this.formIframe.getBoundingClientRect();

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

OnLoadFormLoad.prototype.hideCalendar = function () {
    this.formCalendar.style.display = 'none';
};

OnLoadFormLoad.prototype.processMessage = function (e) {
    if (e.data.type !== 'mbh') {
        return;
    }
    var target = null;
    if (e.data.target === 'form') {
        target = this.formIframe;
    }
    if (e.data.target === 'calendar') {
        target = this.formCalendar;
    }

    if (target) {
        target.contentWindow.postMessage(e.data, '*');
    }
    if (e.data.action === 'showCalendar') {
        var c = this.getCoords();
        this.formCalendar.style.display = 'block';
        this.formCalendar.style.top = (e.data.top + c.top - 10) + 'px';
        this.formCalendar.style.left = (isMobileDevice() ? e.data.left : (e.data.left + c.left)) + 'px';
        this.formCalendar.contentWindow.postMessage(e.data, '*');
    }
    if (e.data.action === 'hideCalendar') {
        this.hideCalendar();
    }
};

OnLoadFormLoad.prototype.resizeIframeWidth = function () {
    var width = window.outerWidth;
    if (this.formIframe && width) {
        this.formIframe.width = width < this.iframeWidth ? width : this.iframeWidth;
    }
};
OnLoadFormLoad.prototype.resizeIframeHeight = function (event) {
    if (event.data.type !== 'mbh') {
        return;
    }
    if (event.data.action === 'formResize') {
        var formIframe = document.getElementById("mbh-form-iframe");
        formIframe.height = event.data.formHeight + 5;
    }
};

OnLoadFormLoad.prototype.createIframeWithForm = function (locale) {
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

    var fullUrl = config.form_url + url;

    if (locale !== undefined) {
        fullUrl = fullUrl.replace(/(\?|&)locale=\w*/, '$1locale=' + locale);
    }

    this.formIframe = document.createElement('iframe');
    this.formIframe.id = 'mbh-form-iframe';
    this.formIframe.scrolling = 'no';
    this.formIframe.frameBorder = '0';
    this.formIframe.src = fullUrl;
    this.formIframe.hidden = true;
    this.formIframe.title = 'Frame with search form';
};

OnLoadFormLoad.prototype.exec = function (locale) {
    this.formWrapper = document.getElementById('mbh-form-wrapper');
    if (!this.formWrapper) {
        return;
    }

    if (this.itIsFirstLoad) {
        this.metric();
    }

    var self = this;

    this.formWrapper.innerText = '';

    this.spinner = this.waitingSpinner();

    this.createIframeWithForm(locale);
    this.formWrapper.appendChild(this.formIframe);

    this.formIframe.addEventListener('load', function() {
        self.hideWaitingSpinner(this);
    }, {once: true});

    self.resizeIframeWidth();

    if (this.itIsFirstLoad) {
        this.formCalendar = this.createIframeWithCalendar();

        document.addEventListener("click", function(ev) {
            self.hideCalendar();
        });
        document.addEventListener("keyup", function (e) {
            if (e.keyCode === 27) {
                self.hideCalendar();
            }
        });

        var eventDispatcher = null;

        window.addEventListener('resize', function(ev) {
            if (eventDispatcher) clearTimeout(eventDispatcher);
            eventDispatcher = setTimeout(function () {
                self.resizeIframeWidth();
            }, 200);
        });

        window.addEventListener('message', function(ev) {
            self.processMessage(ev);
        }, false);
        window.addEventListener("message", function(ev) {
            self.resizeIframeHeight(ev);
        }, false);
    }

    this.itIsFirstLoad = false;
};

OnLoadFormLoad.prototype.metric = function () {
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

    window.addEventListener("message", processMetricMessage, false);
};

OnLoadFormLoad.prototype.hideWaitingSpinner = function (_this) {
    this.spinner.className = '';
    this.spinner.innerHTML = '';
    _this.hidden = false;
};

var onLoadFormLoad = new OnLoadFormLoad();

addLoadEvent(onLoadFormLoad.exec());

