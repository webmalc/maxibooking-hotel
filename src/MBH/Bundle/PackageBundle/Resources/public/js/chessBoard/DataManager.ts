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
        console.log(response);
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
        let self = this;
        $.ajax({
            url: Routing.generate('concise_package_update', {id: packageId}),
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
    }

    public relocateAccommodationRequest(accommodationId, newAccommodationData) {
        let self = this;
        $.ajax({
            url: Routing.generate('relocate_accommodation', {id : accommodationId}),
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
    }

    public createPackageRequest(newPackageCreateUrl, packageData) {
        ActionManager.showLoadingIndicator();
        if (packageData.accommodation) {
            this.addPackageData(packageData);
        }
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
            },
            error: function () {
                self.handleError();
            }
        });
    }

    private addPackageData(packageData) {
        packageData.payer = '';
        this._accommodations.push(packageData);
    }

    public updateLocalPackageData(packageData, isDivide) {
        let self = this;
        ActionManager.hideLoadingIndicator();
        if (isDivide) {
            let dividedAccommodation;
            this._accommodations.some(function (accommodationData) {
                if (accommodationData.id == packageData.accommodationId) {
                    dividedAccommodation = accommodationData;
                    return true;
                }
                return false;
            });
            let newAccommodationData = $.extend(true, {}, dividedAccommodation);
            dividedAccommodation.end = packageData.begin;
            newAccommodationData.begin = packageData.begin;
            newAccommodationData.accommodation = packageData.roomId;
            this._accommodations.forEach(function (packageDataItem) {
                if (packageDataItem.id === packageData.id) {
                    packageDataItem = dividedAccommodation;
                }
            });
            this._accommodations.push(newAccommodationData);
        } else {
            if (!packageData.accommodation) {
                this._accommodations.forEach(function (packageItem, index, packages) {
                    if (packageItem.id === packageItem.id) {
                        packages.splice(index, 1);
                    }
                });
            } else {
                this.updateAccommodationData(packageData)
            }
        }
    }

    public getNoAccommodationPackagesByDate(date, roomTypeId) {
        return this.noAccommodationIntervals.filter(function (noAccommodationInterval) {
            if (noAccommodationInterval.roomTypeId === roomTypeId) {
                let packageBeginDate = ChessBoardManager.getMomentDate(noAccommodationInterval.begin);
                let packageEndDate = ChessBoardManager.getMomentDate(noAccommodationInterval.end);

                let beginAndCurrentDiff = date.diff(packageBeginDate, 'days');
                let endAndCurrentDiff = packageEndDate.diff(date, 'days');

                return beginAndCurrentDiff >= 0 && endAndCurrentDiff > 0;
            }

            return false;
        })
    }

    private updateAccommodationData(packageData) {
        let isAccommodation = false;
        this._accommodations.forEach(function (packageDataItem) {
            if (packageDataItem.id === packageData.id) {
                isAccommodation = true;
                packageDataItem.begin = packageData.begin;
                packageDataItem.end = packageData.end;
                packageDataItem.accommodation = packageData.accommodation;
                packageDataItem.roomTypeId = packageData.roomTypeId;
            }
        });
        if (!isAccommodation && packageData.accommodation != '') {
            this.addPackageData(packageData);
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
            error: function () {
                self.handleError();
            },
            dataType: 'html'
        });
    }

    public deletePackageRequest(packageId) {
        ActionManager.showLoadingIndicator();
        let self = this;

        let index = this._accommodations.length - 1;

        while (index >= 0) {
            if (this._accommodations[index].packageId === packageId) {
                this._accommodations.splice(index, 1);
            }

            index -= 1;
        }

        $.ajax({
            url: Routing.generate('chessboard_remove_package', {id: packageId}),
            type: "DELETE",
            success: function (data) {
                ActionManager.hideLoadingIndicator();
                self.handleResponse(data);
            },
            error: function () {
                self.handleError();
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
            error: function () {
                self.handleError();
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
            },
            error: function () {
                ActionManager.showHappenedUnWaitedMessage();
            }
        });
    }

    private handleError() {
        ActionManager.showHappenedUnWaitedMessage();
        this.updatePackagesData();
    }

    private updateTableData(data) {
        var tableData = JSON.parse(data);
        this._accommodations = tableData.accommodations;
        this.noAccommodationIntervals = tableData.noAccommodationIntervals;
        this._leftRoomCounts = tableData.leftRoomCounts;
        this.noAccommodationCounts = tableData.noAccommodationCounts;
    }
}