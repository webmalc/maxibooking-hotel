(function () {
    var configResults;
    if (typeof(mbh) !== 'undefined') {
        configResults = mbh;
    } else if (typeof(mbhResults) !== 'undefined') {
        configResults = mbhResults;
    }

    this.resultsWrapper = document.getElementById('mbh-results-wrapper');
    if (this.resultsWrapper === null) {
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

    var fullUrl = configResults.results_url + url;

    function redirect(self) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', fullUrl);

        xhr.send();

        xhr.onreadystatechange = function() {
            if (xhr.readyState !== 4) {
                return;
            }

            if (xhr.status !== 200) {
                console.error( xhr.status + ': ' + xhr.statusText );
            } else {
                var script = document.createElement('script');
                script.innerText = xhr.responseText;

                self.resultsWrapper.appendChild(script);

                onLoadResultsLoad();
            }
        };
    }

    redirect(this);
})();
