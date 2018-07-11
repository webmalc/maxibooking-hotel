///<reference path="ActionManager.ts"/>
///<reference path="ChessBoardManager.ts"/>
/*global $ */
var DataManager = /** @class */ (function () {
    function DataManager(accommodations, leftRoomsData, noAccommodationCounts, noAccommodationIntervals, chessBoardManager) {
        this._accommodations = accommodations;
        this._leftRoomCounts = leftRoomsData;
        this.chessBoardManager = chessBoardManager;
        this.noAccommodationCounts = noAccommodationCounts;
        this.noAccommodationIntervals = noAccommodationIntervals;
        this.actionManager = new ActionManager(this);
    }
    DataManager.prototype.handleResponse = function (jsonResponse) {
        var response = JSON.parse(jsonResponse);
        ActionManager.showResultMessages(response);
        this.updatePackagesData();
        if (response.data) {
            return response.data;
        }
    };
    DataManager.prototype.getAccommodations = function () {
        return this._accommodations;
    };
    DataManager.prototype.getNoAccommodationIntervals = function () {
        return this.noAccommodationIntervals;
    };
    DataManager.prototype.getLeftRoomCounts = function () {
        return this._leftRoomCounts;
    };
    DataManager.prototype.getNoAccommodationCounts = function () {
        return this.noAccommodationCounts;
    };
    DataManager.prototype.updatePackageRequest = function (packageId, data) {
        var self = this;
        $.ajax({
            url: Routing.generate('concise_package_update', { id: packageId }),
            data: data,
            type: "PUT",
            success: function (data) {
                self.handleResponse(data);
            },
            error: function () {
                self.handleError();
            },
            dataType: 'json'
        });
        ActionManager.hidePackageUpdateModal();
    };
    DataManager.prototype.relocateAccommodationRequest = function (accommodationId, newAccommodationData) {
        var self = this;
        $.ajax({
            url: Routing.generate('relocate_accommodation', { id: accommodationId }),
            data: newAccommodationData,
            type: "PUT",
            success: function (data) {
                self.handleResponse(data);
            },
            error: function () {
                self.handleError();
            },
            dataType: 'json'
        });
        ActionManager.hidePackageUpdateModal();
    };
    DataManager.prototype.createPackageRequest = function (newPackageCreateUrl, packageData) {
        ActionManager.showLoadingIndicator();
        if (packageData.accommodation) {
            this.addPackageData(packageData);
        }
        var self = this;
        $.ajax({
            url: newPackageCreateUrl,
            type: "GET",
            dataType: 'json',
            success: function (data) {
                ActionManager.hideLoadingIndicator();
                var packageId = self.handleResponse(data).packageId;
                if (packageId) {
                    self.getPackageDataRequest(packageId);
                }
            },
            error: function () {
                self.handleError();
            }
        });
    };
    DataManager.prototype.addPackageData = function (packageData) {
        this._accommodations[packageData.id] = packageData;
    };
    DataManager.prototype.updateLocalPackageData = function (packageData, isDivide) {
        ActionManager.hideLoadingIndicator();
        if (isDivide) {
            var dividedAccommodation = this._accommodations[packageData.accommodationId];
            var newAccommodationData = $.extend(true, {}, dividedAccommodation);
            dividedAccommodation.end = packageData.begin;
            if (dividedAccommodation.position == 'right') {
                dividedAccommodation.position = 'middle';
            }
            else if (dividedAccommodation.position == 'full') {
                newAccommodationData.position = 'right';
                dividedAccommodation.position = 'left';
            }
            else if (dividedAccommodation.position == 'left') {
                newAccommodationData.position = 'middle';
            }
            newAccommodationData.begin = packageData.begin;
            newAccommodationData.accommodation = packageData.roomId;
            newAccommodationData.id = 'newAccommodation';
            this._accommodations[packageData.id] = dividedAccommodation;
            this.addPackageData(newAccommodationData);
        }
        else {
            if (!packageData.accommodation) {
                delete this._accommodations[packageData.id];
            }
            else {
                this.updateAccommodationData(packageData);
            }
        }
    };
    DataManager.prototype.getNoAccommodationPackagesByDate = function (date, roomTypeId) {
        var noAccommodationPackages = [];
        for (var noAccommodationData in this.noAccommodationIntervals) {
            if (this.noAccommodationIntervals.hasOwnProperty(noAccommodationData)) {
                var noAccommodationIntervalData = this.noAccommodationIntervals[noAccommodationData];
                if (noAccommodationIntervalData.roomTypeId === roomTypeId) {
                    var packageBeginDate = ChessBoardManager.getMomentDate(noAccommodationIntervalData.begin);
                    var packageEndDate = ChessBoardManager.getMomentDate(noAccommodationIntervalData.end);
                    var beginAndCurrentDiff = date.diff(packageBeginDate, 'days');
                    var endAndCurrentDiff = packageEndDate.diff(date, 'days');
                    if (beginAndCurrentDiff >= 0 && endAndCurrentDiff > 0) {
                        noAccommodationPackages.push(noAccommodationIntervalData);
                    }
                }
            }
        }
        return noAccommodationPackages;
    };
    DataManager.prototype.updateAccommodationData = function (packageData) {
        var isAccommodation = false;
        if (this._accommodations[packageData.id]) {
            this._accommodations[packageData.id].begin = packageData.begin;
            this._accommodations[packageData.id].end = packageData.end;
            this._accommodations[packageData.id].accommodation = packageData.accommodation;
            this._accommodations[packageData.id].roomTypeId = packageData.roomTypeId;
        }
        else if (!isAccommodation && packageData.accommodation != '') {
            this.addPackageData(packageData);
        }
    };
    DataManager.prototype.getPackageOptionsRequest = function (searchData, packageData) {
        ActionManager.showLoadingIndicator();
        var self = this;
        $.ajax({
            url: Routing.generate('package_search_results'),
            data: searchData,
            type: "GET",
            success: function (data) {
                ActionManager.hideLoadingIndicator();
                self.actionManager.handleSearchOptionsModal(packageData, data);
            },
            error: function () {
                self.handleError();
            },
            dataType: 'html'
        });
    };
    DataManager.prototype.deletePackageRequest = function (packageId, $formContainer, $packageDeleteModal) {
        var self = this;
        this.deleteAccommodationsByPackageId(packageId);
        var data = $formContainer.find('form').serialize();
        $formContainer.html(mbh.loader.html);
        $.ajax({
            url: Routing.generate('package_delete', { id: packageId, from_chessboard: true }),
            type: "POST",
            data: data,
            success: function (response) {
                $formContainer.html(response);
            },
            error: function (response) {
                $packageDeleteModal.modal('hide');
                if (response.status === 302) {
                    self.updatePackagesData();
                    ActionManager.showMessage(true, Translator.trans('chessboard.package_remove.success'));
                }
                else {
                    self.handleError();
                }
            },
        });
    };
    DataManager.prototype.deleteAccommodationsByPackageId = function (packageId) {
        for (var accommodationId in this._accommodations) {
            if (this._accommodations.hasOwnProperty(accommodationId)
                && this._accommodations[accommodationId].packageId == packageId) {
                delete this._accommodations[accommodationId];
            }
        }
    };
    DataManager.prototype.getPackageDataRequest = function (packageId) {
        ActionManager.showLoadingIndicator();
        var self = this;
        $.ajax({
            url: Routing.generate('chessboard_get_package', { id: packageId }),
            type: "GET",
            success: function (data) {
                ActionManager.hideLoadingIndicator();
                self.actionManager.showPackageInfoModal(packageId, data);
            },
            error: function () {
                self.handleError();
            },
            dataType: 'html'
        });
    };
    DataManager.prototype.getNoAccommodationIntervalById = function (id) {
        return this.getNoAccommodationIntervals()[id];
    };
    DataManager.prototype.getPackageDataById = function (packageId) {
        var packageData;
        var self = this;
        Object.getOwnPropertyNames(this._accommodations).some(function (accommodationId) {
            var accommodationData = self._accommodations[accommodationId];
            if (accommodationData.packageId == packageId) {
                packageData = accommodationData;
                return true;
            }
            return false;
        });
        if (packageData) {
            return packageData;
        }
        for (var noAccommodationIntervalId in this.noAccommodationIntervals) {
            if (this.noAccommodationIntervals.hasOwnProperty(noAccommodationIntervalId)
                && noAccommodationIntervalId.startsWith(packageId)) {
                return this.noAccommodationIntervals[noAccommodationIntervalId];
            }
        }
    };
    DataManager.prototype.getPackageAccommodations = function (packageId) {
        var packageAccommodations = [];
        for (var accommodationId in this._accommodations) {
            if (this._accommodations.hasOwnProperty(accommodationId)) {
                var accommodationData = this._accommodations[accommodationId];
                if (accommodationData.packageId == packageId) {
                    packageAccommodations.push(accommodationData);
                }
            }
        }
        return packageAccommodations;
    };
    DataManager.prototype.getAccommodationIntervalById = function (id) {
        return this._accommodations[id];
    };
    DataManager.prototype.updatePackagesData = function () {
        var self = this;
        var filterData = ChessBoardManager.getFilterData($('#accommodation-report-filter'));
        $.ajax({
            url: Routing.generate('chessboard_packages'),
            data: filterData,
            type: "GET",
            success: function (data) {
                ActionManager.hideLoadingIndicator();
                self.updateTableData(data);
                self.chessBoardManager.updateTable();
            },
            error: function () {
                ActionManager.showInternalErrorMessage();
            }
        });
    };
    DataManager.prototype.handleError = function () {
        ActionManager.showInternalErrorMessage();
        this.updatePackagesData();
    };
    DataManager.prototype.updateTableData = function (data) {
        var tableData = JSON.parse(data);
        this._accommodations = tableData.accommodations;
        this.noAccommodationIntervals = tableData.noAccommodationIntervals;
        this._leftRoomCounts = tableData.leftRoomCounts;
        this.noAccommodationCounts = tableData.noAccommodationCounts;
    };
    DataManager.prototype.getAccommodationNeighbors = function (accommodationId) {
        var neighborsBySides = {};
        var accommodationData = this._accommodations[accommodationId];
        for (var id in this._accommodations) {
            var iteratedAccommodation = this._accommodations[id];
            if (this._accommodations.hasOwnProperty(id)
                && iteratedAccommodation.accommodation == accommodationData.accommodation) {
                if (iteratedAccommodation.end === accommodationData.begin) {
                    neighborsBySides['left'] = iteratedAccommodation;
                }
                else if (iteratedAccommodation.begin === accommodationData.end) {
                    neighborsBySides['right'] = iteratedAccommodation;
                }
            }
        }
        return neighborsBySides;
    };
    return DataManager;
}());
