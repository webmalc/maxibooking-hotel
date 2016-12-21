///<reference path="ActionManager.ts"/>
///<reference path="ChessBoardManager.ts"/>

declare var $:any;
declare var Routing:any;
declare var packages;

class DataManager {

    private _accommodations;
    private _leftRoomCounts;
    private chessBoardManager;
    private actionManager;
    private noAccommodationCounts;
    private noAccommodationIntervals;

    constructor(accommodations, leftRoomsData, noAccommodationCounts, noAccommodationIntervals, chessBoardManager) {
        this._accommodations = accommodations;
        this._leftRoomCounts = leftRoomsData;
        this.chessBoardManager = chessBoardManager;
        this.noAccommodationCounts = noAccommodationCounts;
        this.noAccommodationIntervals = noAccommodationIntervals;
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

    public getAccommodations() {
        return this._accommodations;
    }

    public getNoAccommodationIntervals() {
        return this.noAccommodationIntervals;
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

    public relocateAccommodationRequest(accommodationId, newAccommodationData) {
        let self = this;
        ActionManager.showLoadingIndicator();
        $.ajax({
            url: Routing.generate('relocate_accommodation', {id : accommodationId}),
            data: newAccommodationData,
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
                    self.getPackageDataRequest(packageId);
                }
            }
        });
    }

    private addPackageData(packageData) {
        packageData.begin = { 'date' : DataManager.getPackageDate(packageData.begin) };
        packageData.end = { 'date'  : DataManager.getPackageDate(packageData.end) };
        packageData.payer = '';
        this._accommodations.push(packageData);
    }

    public static getPackageDate(packageDataDate) {
        return moment(packageDataDate, "DD.MM.YYYY").toDate();
    }

    public updateLocalPackageData(packageData) {
        let self = this;
        if (!packageData.accommodation) {
            this._accommodations.forEach(function (packageItem, index, packages) {
                if (packageItem.id === packageData.id) {
                    packages.splice(index, 1);
                }
            });
        } else {
            let isAccommodation = false;
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
                self.addPackageData(packageData);
            }
        }
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
        this._accommodations.forEach(function (packageItem, index, packages) {
            if (packageItem.packageId === packageId) {
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

    public getPackageDataRequest(accommodationId) {
        ActionManager.showLoadingIndicator();
        let self = this;
        $.ajax({
            url: Routing.generate('chessboard_get_package', {id: accommodationId}),
            type: "GET",
            success: function (data) {
                ActionManager.hideLoadingIndicator();
                self.actionManager.showPackageInfoModal(accommodationId, data);
            },
            dataType: 'html'
        });
    }

    public getNoAccommodationIntervalById(id) {
        return this.getNoAccommodationIntervals().find(function(packageData) {
            return packageData.id === id;
        })
    }

    public getAccommodationIntervalById(id) {
        return this.getAccommodations().find(function (accommodationData) {
            return accommodationData.id === id;
        });
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
        this._accommodations = tableData.accommodations;
        this.noAccommodationIntervals = tableData.noAccommodationIntervals;
        this._leftRoomCounts = tableData.leftRoomCounts;
        this.noAccommodationCounts = tableData.noAccommodationCounts;
    }
}