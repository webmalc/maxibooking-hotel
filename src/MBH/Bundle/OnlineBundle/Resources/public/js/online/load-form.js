if (typeof(mbh) !== 'undefined') {
    var config = mbh;
} else if (typeof(mbhForm) !== 'undefined') {
    var config = mbhForm;
}

function PreOnLoadFormLoad() {
    this.formWrapper = null;
}

PreOnLoadFormLoad.prototype.waitingSpinner =  function() {
    var text = /\.ru/.test(window.location.hostname) ? 'Подождите...' : 'Please wait...',
        spinner = document.createElement('div');
    spinner.id = 'mbh-form-load-spinner';
    spinner.className = 'alert alert-info';
    spinner.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ' + text;

    this.formWrapper.appendChild(spinner);

    return spinner;
};

PreOnLoadFormLoad.prototype.createIframeWithForm = function (locale) {
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

    console.log(fullUrl);

    function redirect(self) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', fullUrl);

        xhr.send();

        xhr.onreadystatechange = function() { // (3)
            if (xhr.readyState !== 4) {
                return;
            }

            if (xhr.status !== 200) {
                console.error( xhr.status + ': ' + xhr.statusText );
            } else {
                var script = document.createElement('script');
                script.defer = true;
                script.innerText = xhr.responseText;

                self.formWrapper.appendChild(script);
            }
        };
    }

    redirect(this);
};

PreOnLoadFormLoad.prototype.exec = function (locale) {
    this.formWrapper = document.getElementById('mbh-form-wrapper');
    if (!this.formWrapper) {
        return;
    }

    this.formWrapper.innerText = '';

    this.spinner = this.waitingSpinner();

    this.createIframeWithForm(locale);
};

var preOnLoadFormLoad = new PreOnLoadFormLoad();
preOnLoadFormLoad.exec();
