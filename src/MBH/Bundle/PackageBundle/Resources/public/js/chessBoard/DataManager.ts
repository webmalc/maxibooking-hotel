///<reference path="ActionManager.ts"/>
///<reference path="ChessBoardManager.ts"/>

declare var $:any;
declare var Routing:any;
declare var packages;

class DataManager {

    private _packages;
    private _leftRoomCounts;
    private chessBoardManager;
    private actionManager;
    private noAccommodationCounts;

    constructor(packages, leftRoomsData, noAccommodationCounts, chessBoardManager) {
        this._packages = packages;
        this._leftRoomCounts = leftRoomsData;
        this.chessBoardManager = chessBoardManager;
        this.noAccommodationCounts = noAccommodationCounts;
        this.actionManager = new ActionManager(this);
    }

    private handleResponse(jsonResponse) {
        var response = JSON.parse(jsonResponse);

        ActionManager.showResultMessages(response);
        this.updatePackagesData();

        if(response.data) {
            return response.data;
        }
    }

    public getPackages() {
        return this._packages;
    }

    public getLeftRoomCounts() {
        return this._leftRoomCounts;
    }

    public getNoAccommodationCounts() {
        return this.noAccommodationCounts;
    }

    public updatePackageRequest(packageId, data) {
        ActionManager.showLoadingIndicator();
        let self = this;
        $.ajax({
            url: Routing.generate('concise_package_update', {id: packageId}),
            data: data,
            type: "PUT",
            success: function (data) {
                self.handleResponse(data);
                ActionManager.hideLoadingIndicator();
            },
            dataType: 'json'
        });
        ActionManager.hidePackageUpdateModal();
    }

    public createPackageRequest(newPackageCreateUrl, packageData) {
        ActionManager.showLoadingIndicator();
        this.addPackageData(packageData);
        let self = this;
        $.ajax({
            url: newPackageCreateUrl,
            type: "GET",
            dataType: 'json',
            success: function (data) {
                ActionManager.hideLoadingIndicator();
                let packageId = self.handleResponse(data).packageId;
                if (packageId) {
                    // self.getPackageDataRequest(packageId);
                }
            }
        });
    }

    private addPackageData(packageData) {
        packageData.begin = {'date' : DataManager.getPackageDate(packageData.begin) };
        packageData.end = { 'date'  : DataManager.getPackageDate(packageData.end) };
        packageData.payer = '';

        this._packages.push(packageData);
    }

    private static getPackageDate(packageDataDate) {
        return moment(packageDataDate, "DD.MM.YYYY").toDate();
    }

    public updateLocalPackageData(packageData) {

        this._packages.forEach(function (packageDataItem) {
            if (packageDataItem.id === packageData.id) {
                packageDataItem.begin.date = DataManager.getPackageDate(packageData.begin);
                console.log(packageDataItem);
                packageDataItem.end.date = DataManager.getPackageDate(packageData.end);
                packageDataItem.accommodation = packageData.accommodation;
                packageDataItem.roomTypeId = packageData.roomTypeId;
            }
        });
    }

    public getPackageOptionsRequest(searchData, packageData) {
        ActionManager.showLoadingIndicator();
        let self = this;
        $.ajax({
            url: Routing.generate('package_search_results'),
            data: searchData,
            type: "GET",
            success: function (data) {
                ActionManager.hideLoadingIndicator();
                self.actionManager.handleSearchOptionsModal(packageData, data)
            },
            dataType: 'html'
        });
    }

    public deletePackageRequest(packageId) {
        ActionManager.showLoadingIndicator();
        let self = this;
        this._packages.forEach(function (packageItem, index, packages) {
            if (packageItem.id === packageId) {
                packages.splice(index, 1);
            }
        });

        $.ajax({
            url: Routing.generate('chessboard_remove_package', {id: packageId}),
            type: "DELETE",
            success: function (data) {
                ActionManager.hideLoadingIndicator();
                self.handleResponse(data);
            }
        });
    }

    public getPackageDataRequest(packageId) {
        ActionManager.showLoadingIndicator();
        let self = this;
        $.ajax({
            url: Routing.generate('chessboard_get_package', {id: packageId}),
            type: "GET",
            success: function (data) {
                ActionManager.hideLoadingIndicator();
                self.actionManager.showPackageInfoModal(packageId, data);
            },
            dataType: 'html'
        });
    }

    public getPackageDataById(packageId) {
        return this.getPackages().find(function(packageData) {
            return packageData.id === packageId;
        })
    }

    private updatePackagesData() {
        ActionManager.showLoadingIndicator();
        let self = this;
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
    }

    private updateTableData(data) {
        var tableData = JSON.parse(data);
        this._packages = tableData.packages;
        this._leftRoomCounts = tableData.leftRoomCounts;
        this.noAccommodationCounts = tableData.noAccommodationCounts;
    }
}