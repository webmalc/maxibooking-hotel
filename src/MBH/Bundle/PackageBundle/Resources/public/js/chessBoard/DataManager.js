///<reference path="ActionManager.ts"/>
///<reference path="ChessBoardManager.ts"/>
var DataManager = (function () {
    function DataManager(packages, leftRoomsData, noAccommodationCounts, chessBoardManager) {
        this._packages = packages;
        this._leftRoomCounts = leftRoomsData;
        this.chessBoardManager = chessBoardManager;
        this.noAccommodationCounts = noAccommodationCounts;
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
    DataManager.prototype.getPackages = function () {
        return this._packages;
    };
    DataManager.prototype.getLeftRoomCounts = function () {
        return this._leftRoomCounts;
    };
    DataManager.prototype.getNoAccommodationCounts = function () {
        return this.noAccommodationCounts;
    };
    DataManager.prototype.updatePackageRequest = function (packageId, data) {
        ActionManager.showLoadingIndicator();
        var self = this;
        $.ajax({
            url: Routing.generate('concise_package_update', { id: packageId }),
            data: data,
            type: "PUT",
            success: function (data) {
                self.handleResponse(data);
                ActionManager.hideLoadingIndicator();
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
            }
        });
    };
    DataManager.prototype.addPackageData = function (packageData) {
        packageData.begin = { 'date': moment(packageData.begin, "DD.MM.YYYY").toDate() };
        packageData.end = { 'date': moment(packageData.end, "DD.MM.YYYY").toDate() };
        packageData.payer = '';
        this._packages.push(packageData);
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
        this._packages.forEach(function (packageItem, index, packages) {
            if (packageItem.id === packageId) {
                packages.splice(index, 1);
            }
        });
        $.ajax({
            url: Routing.generate('chessboard_remove_package', { id: packageId }),
            type: "DELETE",
            success: function (data) {
                ActionManager.hideLoadingIndicator();
                self.handleResponse(data);
            }
        });
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
            dataType: 'html'
        });
    };
    DataManager.prototype.getPackageDataById = function (packageId) {
        return this.getPackages().find(function (packageData) {
            return packageData.id === packageId;
        });
    };
    DataManager.prototype.updatePackagesData = function () {
        ActionManager.showLoadingIndicator();
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
        this._packages = tableData.packages;
        this._leftRoomCounts = tableData.leftRoomCounts;
        this.noAccommodationCounts = tableData.noAccommodationCounts;
    };
    return DataManager;
}());
//# sourceMappingURL=DataManager.js.map