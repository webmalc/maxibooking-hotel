StepOneButton.prototype.textForPackageInfo = function (amount, entity) {
    if (amount === 1) {
        return this._text.packageInfo[entity].one;
    } else if (amount > 1 && amount <= 4) {
        return this._text.packageInfo[entity].many;
    }

    return this._text.packageInfo[entity].other;
};

StepOneButton.prototype.dataRoomAndGuestAfterCalc = function () {
    this._packageInfoContainerRoom.innerHTML = this._amountRoom;
    this._packageInfoContainerRoomText.innerHTML = this.textForPackageInfo(this._amountRoom, 'room');

    this._packageInfoContainerGuest.innerHTML = this._amountGuest;
    this._packageInfoContainerGuestText.innerHTML = this.textForPackageInfo(this._amountGuest, 'guest');
};

StepOneButton.prototype.processMessage = function(e) {
    if (e.data.type !== 'mbh') {
        return;
    }

    console.log(e.data);

    if (e.data.action === 'selectPackage') {
        this.changeVariables(e.data.data);

        this._packageNextButton.disabled = !this._totalPackage > 0;

        this._packageTotalSum.innerHTML = mbhFuncPriceSeparator(this._totalPackage);
        this.dataRoomAndGuestAfterCalc();
    }

    if (e.data.action === 'packageDate') {
        this.packageDate(e.data);
    }
};

StepOneButton.prototype.changeVariables = function (data) {
    this._totalPackage = data.totalPackage || 0;
    this._amountRoom = data.amountRoom || 0;
    this._amountGuest = data.amountGuest || 0;
};

StepOneButton.prototype.initFields = function () {
    var packageInfoContainer = document.querySelector('#mbh-results-package-info-container');

    this._packageInfoContainerRoom = packageInfoContainer.querySelector('.room-amount');
    this._packageInfoContainerRoomText = packageInfoContainer.querySelector('.room-text');
    this._packageInfoContainerGuest = packageInfoContainer.querySelector('.guest-amount');
    this._packageInfoContainerGuestText = packageInfoContainer.querySelector('.guest-text');

    this._packageTotalSum = document.querySelector('#mbh-results-total-sum');
    this._packageNextButton = document.querySelector('#mbh-results-next')
};

StepOneButton.prototype.packageDate = function (data) {
    if (data.data === undefined) {
        return;
    }

    document.querySelector('.mbh-package-info-date .begin').innerHTML = data.data.begin;
    document.querySelector('.mbh-package-info-date .end').innerHTML = data.data.end;
};

StepOneButton.prototype.exec = function() {
    var _this = this;

    this.initFields();

    window.addEventListener('message', function(e) {
        _this.processMessage(e);
    });

    this._packageNextButton.addEventListener('click', function(e) {
        window.parent.postMessage({
            type: 'mbh',
            target: 'stepOneParent',
            action: 'clickNextButton'
        },'*');
    });
};

var stepOne = new StepOneButton();
stepOne.exec();
