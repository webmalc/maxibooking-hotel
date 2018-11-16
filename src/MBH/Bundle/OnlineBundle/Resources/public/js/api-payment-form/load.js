MbhIframe.prototype.addFormPayment = function(data) {
    var html = '<form id="' + this.idFormPayment + '" method="post" action="' + this.actionPayment + '">' +
    '<div class="form-group form-group-sm"> ' +
        '<label for="total" class="col-form-label col-form-label-sm">' + this.form.label + '</label>' +
        '<div class="form-field">' +
        '<input type="number" class="form-control input-sm" name="total" min="1" step="0.01" max="' + data.total + '" value="' + data.total + '">' +
        '</div></div>' +
        '<input type="hidden" name="orderId" value="' + data.orderId + '">' +
        '<div class="form-group"' + (this.onlyOneSystem ? ' style="display: none;"' : '') + '>' +
        '<select class="form-control input-sm" name="paymentSystemName">' + this.form.options + '</select> ' +
        '</div>' +
        '<div class="form-group">' +
        '<input type="submit" value="' + this.form.submitBtn + '" class="btn btn-success btn-block">' +
        '</div>';
    html += '</form>';

    return html;
};

MbhIframe.prototype.updateResultDiv = function(type, data) {
    var divResult = document.querySelector('#' + this.idDivResultSearch);
    divResult.className = '';
    divResult.classList.add('panel-body');
    divResult.classList.add('panel-' + type);

    if (type !== 'info') {
        divResult.classList.add('bg-' + type);
    }

    divResult.innerHTML = data;
};

MbhIframe.prototype.clearDiv = function(id) {
    document.querySelector('#' + id).innerHTML = '';
};

MbhIframe.prototype.accordion = function() {
    if (this.useAccordion === true) {
        this.accordionFunc()
    }
};

MbhIframe.prototype.accordionFunc = function() {
    (function() {
        $("#mbh-form-payment-accordion").accordion({
            header: ".panel-heading",
            heightStyle: "content"
        });
    })();

    document.addEventListener('mbh-loaded-result-search', function(evt) {
        $("#mbh-form-payment-accordion").accordion({
            active: 1
        });
    });

    document.addEventListener('mbh-loaded-btn-for-pay', function(evt) {
        $("#mbh-form-payment-accordion").accordion({
            active: 2
        });
    });
};

window.addEventListener('load', function(ev) {
    var mbhIframe = new MbhIframe(),
        formSearch = document.querySelector('#' + mbhIframe.idFormSearch),
        formResult = document.querySelector('#' + mbhIframe.idDivResultSearch);

    formSearch.addEventListener('submit', function(e) {
        e.preventDefault();
        mbhIframe.clearDiv(mbhIframe.idDivResultSearch);
        mbhIframe.clearDiv('payment-btn');
        $.post(this.action, $(this).serialize())
        .done(function(data) {
            if (typeof data.error !== 'undefined') {
                mbhIframe.updateResultDiv('danger', data.error);
            } else {
                if (data.needIsPaid) {
                    mbhIframe.updateResultDiv('info', mbhIframe.addFormPayment(data.data));
                } else {
                    mbhIframe.updateResultDiv('success', data.data);
                }
            }
            document.dispatchEvent(new CustomEvent('mbh-loaded-result-search'));
        }).error(function(error) {
            console.error(error);
        });
    });

    formResult.addEventListener('submit', function(e) {
        e.preventDefault();
        mbhIframe.clearDiv('payment-btn');
        var form = this.querySelector('form');
        $.post(form.action, $(form).serialize())
        .done(function(data) {
            if (typeof data.error !== 'undefined') {
                mbhIframe.updateResultDiv('danger', data.error);
            } else {
                document.querySelector('#payment-btn').innerHTML = data;
                document.dispatchEvent(new CustomEvent('mbh-loaded-btn-for-pay'));
            }
        }).error(function(error) {
            console.error(error);
        });
    });



    mbhIframe.accordion();
});