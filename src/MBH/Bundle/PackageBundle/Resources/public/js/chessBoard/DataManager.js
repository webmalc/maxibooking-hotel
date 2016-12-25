///<reference path="ActionManager.ts"/>
///<reference path="ChessBoardManager.ts"/>
var DataManager = (function () {
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
            dataType: 'json'
        });
        ActionManager.hidePackageUpdateModal();
    };
    DataManager.prototype.createPackageRequest = function (newPackageCreateUrl, packageData) {
        ActionManager.showLoadingIndicator();
        this.addPackageData(packageData);
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
            }
        });
    };
    DataManager.prototype.addPackageData = function (packageData) {
        packageData.begin = { 'date': DataManager.getPackageDate(packageData.begin) };
        packageData.end = { 'date': DataManager.getPackageDate(packageData.end) };
        packageData.payer = '';
        this._accommodations.push(packageData);
    };
    DataManager.getPackageDate = function (packageDataDate) {
        return moment(packageDataDate, "DD.MM.YYYY").toDate();
    };
    DataManager.prototype.updateLocalPackageData = function (packageData, isDivide) {
        var self = this;
        ActionManager.hideLoadingIndicator();
        if (isDivide) {
            var dividedAccommodation_1;
            this._accommodations.some(function (accommodationData) {
                if (accommodationData.id == packageData.accommodationId) {
                    dividedAccommodation_1 = accommodationData;
                    return true;
                }
                return false;
            });
            var newAccommodationData = $.extend(true, {}, dividedAccommodation_1);
            dividedAccommodation_1.end = { 'date': DataManager.getPackageDate(packageData.begin) };
            newAccommodationData.begin = { 'date': DataManager.getPackageDate(packageData.begin) };
            newAccommodationData.accommodation = packageData.roomId;
            this._accommodations.forEach(function (packageDataItem) {
                if (packageDataItem.id === packageData.id) {
                    packageDataItem = dividedAccommodation_1;
                }
            });
            this._accommodations.push(newAccommodationData);
        }
        else {
            if (!packageData.accommodation) {
                this._accommodations.forEach(function (packageItem, index, packages) {
                    if (packageItem.id === packageItem.id) {
                        packages.splice(index, 1);
                    }
                });
            }
            else {
                this.updateAccommodationData(packageData);
            }
        }
    };
    DataManager.prototype.updateAccommodationData = function (packageData) {
        var isAccommodation = false;
        this._accommodations.forEach(function (packageDataItem) {
            if (packageDataItem.id === packageData.id) {
                isAccommodation = true;
                packageDataItem.begin.date = DataManager.getPackageDate(packageData.begin);
                packageDataItem.end.date = DataManager.getPackageDate(packageData.end);
                packageDataItem.accommodation = packageData.accommodation;
                packageDataItem.roomTypeId = packageData.roomTypeId;
            }
        });
        if (!isAccommodation) {
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
            dataType: 'html'
        });
    };
    DataManager.prototype.deletePackageRequest = function (packageId) {
        ActionManager.showLoadingIndicator();
        var self = this;
        var index = this._accommodations.length - 1;
        while (index >= 0) {
            if (this._accommodations[index].packageId === packageId) {
                this._accommodations.splice(index, 1);
            }
            index -= 1;
        }
        $.ajax({
            url: Routing.generate('chessboard_remove_package', { id: packageId }),
            type: "DELETE",
            success: function (data) {
                ActionManager.hideLoadingIndicator();
                self.handleResponse(data);
            }
        });
    };
    DataManager.prototype.getPackageDataRequest = function (accommodationId) {
        ActionManager.showLoadingIndicator();
        var self = this;
        $.ajax({
            url: Routing.generate('chessboard_get_package', { id: accommodationId }),
            type: "GET",
            success: function (data) {
                ActionManager.hideLoadingIndicator();
                self.actionManager.showPackageInfoModal(accommodationId, data);
            },
            dataType: 'html'
        });
    };
    DataManager.prototype.getNoAccommodationIntervalById = function (id) {
        return this.getNoAccommodationIntervals().find(function (packageData) {
            return packageData.id === id;
        });
    };
    DataManager.prototype.getAccommodationIntervalById = function (id) {
        return this.getAccommodations().find(function (accommodationData) {
            return accommodationData.id === id;
        });
    };
    DataManager.prototype.updatePackagesData = function () {
        var self = this;
        var filterData = $('#accommodation-report-filter').serialize();
        $.ajax({
            url: Routing.generate('chessboard_packages'),
            data: filterData,
            type: "GET",
            success: function (data) {
                ActionManager.hideLoadingIndicator();
                self.updateTableData(data);
                self.chessBoardManager.updateTable();
            }
        });
    };
    DataManager.prototype.updateTableData = function (data) {
        var tableData = JSON.parse(data);
        this._accommodations = tableData.accommodations;
        this.noAccommodationIntervals = tableData.noAccommodationIntervals;
        this._leftRoomCounts = tableData.leftRoomCounts;
        this.noAccommodationCounts = tableData.noAccommodationCounts;
    };
    return DataManager;
}());
//# sourceMappingURL=DataManager.js.map