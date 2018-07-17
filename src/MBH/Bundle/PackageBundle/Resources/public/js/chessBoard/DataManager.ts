///<reference path="ActionManager.ts"/>
///<reference path="ChessBoardManager.ts"/>
/*global $ */

declare let Routing: any;
declare let packages;

class DataManager {

    private _accommodations;
    private _leftRoomCounts;
    public chessBoardManager;
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
        let response = JSON.parse(jsonResponse);
        ActionManager.showResultMessages(response);
        this.updatePackagesData();

        if (response.data) {
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
            url: Routing.generate('relocate_accommodation', {id: accommodationId}),
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
        this._accommodations[packageData.id] = packageData;
    }

    public updateLocalPackageData(packageData, isDivide) {
        ActionManager.hideLoadingIndicator();
        if (isDivide) {
            let dividedAccommodation = this._accommodations[packageData.accommodationId];

            let newAccommodationData = $.extend(true, {}, dividedAccommodation);
            dividedAccommodation.end = packageData.begin;

            if (dividedAccommodation.position == 'right') {
                dividedAccommodation.position = 'middle';
            } else if (dividedAccommodation.position == 'full') {
                newAccommodationData.position = 'right';
                dividedAccommodation.position = 'left';
            } else if (dividedAccommodation.position == 'left') {
                newAccommodationData.position = 'middle';
            }

            newAccommodationData.begin = packageData.begin;
            newAccommodationData.accommodation = packageData.roomId;
            newAccommodationData.id = 'newAccommodation';

            this._accommodations[packageData.id] = dividedAccommodation;
            this.addPackageData(newAccommodationData);
        } else {
            if (!packageData.accommodation) {
                delete this._accommodations[packageData.id];
            } else {
                this.updateAccommodationData(packageData)
            }
        }
    }

    public getNoAccommodationPackagesByDate(date, roomTypeId) {
        let noAccommodationPackages = [];
        for (let noAccommodationData in this.noAccommodationIntervals) {
            if (this.noAccommodationIntervals.hasOwnProperty(noAccommodationData)) {

                let noAccommodationIntervalData = this.noAccommodationIntervals[noAccommodationData];
                if (noAccommodationIntervalData.roomTypeId === roomTypeId) {

                    let packageBeginDate = ChessBoardManager.getMomentDate(noAccommodationIntervalData.begin);
                    let packageEndDate = ChessBoardManager.getMomentDate(noAccommodationIntervalData.end);

                    let beginAndCurrentDiff = date.diff(packageBeginDate, 'days');
                    let endAndCurrentDiff = packageEndDate.diff(date, 'days');

                    if (beginAndCurrentDiff >= 0 && endAndCurrentDiff > 0) {
                        noAccommodationPackages.push(noAccommodationIntervalData);
                    }
                }
            }
        }

        return noAccommodationPackages;
    }

    private updateAccommodationData(packageData) {
        let isAccommodation = false;
        if (this._accommodations[packageData.id]) {
            this._accommodations[packageData.id].begin = packageData.begin;
            this._accommodations[packageData.id].end = packageData.end;
            this._accommodations[packageData.id].accommodation = packageData.accommodation;
            this._accommodations[packageData.id].roomTypeId = packageData.roomTypeId;
        } else if (!isAccommodation && packageData.accommodation != '') {
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

    public deletePackageRequest(packageId, $formContainer, $packageDeleteModal) {
        let self = this;
        this.deleteAccommodationsByPackageId(packageId);
        const data = $formContainer.find('form').serialize();
        $formContainer.html(mbh.loader.html);

        $.ajax({
            url: Routing.generate('package_delete', {id: packageId, from_chessboard: true}),
            type: "POST",
            data: data,
            success: function (response) {
                $formContainer.html(response)
            },
            error: function (response) {
                $packageDeleteModal.modal('hide');
                if (response.status === 302) {
                    self.updatePackagesData();
                    ActionManager.showMessage(true, Translator.trans('chessboard.package_remove.success'));
                } else {
                    self.handleError();
                }
            },
        });
    }

    public deleteAccommodationsByPackageId(packageId) {
        for (let accommodationId in this._accommodations) {
            if (this._accommodations.hasOwnProperty(accommodationId)
                && this._accommodations[accommodationId].packageId == packageId) {
                delete this._accommodations[accommodationId];
            }
        }
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
            error: function () {
                self.handleError();
            },
            dataType: 'html'
        });
    }

    public getNoAccommodationIntervalById(id) {
        return this.getNoAccommodationIntervals()[id];
    }

    public getPackageDataById(packageId) {
        let packageData;
        let self = this;
        Object.getOwnPropertyNames(this._accommodations).some(function (accommodationId) {
            let accommodationData = self._accommodations[accommodationId];
            if (accommodationData.packageId == packageId) {
                packageData = accommodationData;
                return true;
            }
            return false;
        });
        if (packageData) {
            return packageData
        }

        for (let noAccommodationIntervalId in this.noAccommodationIntervals) {
            if (this.noAccommodationIntervals.hasOwnProperty(noAccommodationIntervalId)
                && noAccommodationIntervalId.startsWith(packageId)) {
                return this.noAccommodationIntervals[noAccommodationIntervalId];
            }
        }
    }
    
    public getPackageAccommodations(packageId) {
        let packageAccommodations = [];
        for (let accommodationId in this._accommodations) {
            if (this._accommodations.hasOwnProperty(accommodationId)) {
                let accommodationData = this._accommodations[accommodationId];
                if (accommodationData.packageId == packageId) {
                    packageAccommodations.push(accommodationData);
                }
            }
        }

        return packageAccommodations;
    }

    public getAccommodationIntervalById(id) {
        return this._accommodations[id]
    }

    public updatePackagesData() {
        let self = this;
        let filterData = ChessBoardManager.getFilterData($('#accommodation-report-filter'));
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
    }

    private handleError() {
        ActionManager.showInternalErrorMessage();
        this.updatePackagesData();
    }

    private updateTableData(data) {
        let tableData = JSON.parse(data);
        this._accommodations = tableData.accommodations;
        this.noAccommodationIntervals = tableData.noAccommodationIntervals;
        this._leftRoomCounts = tableData.leftRoomCounts;
        this.noAccommodationCounts = tableData.noAccommodationCounts;
    }

    public getAccommodationNeighbors(accommodationId) {
        let neighborsBySides = {};
        let accommodationData = this._accommodations[accommodationId];
        for (let id in this._accommodations) {
            let iteratedAccommodation = this._accommodations[id];
            if (this._accommodations.hasOwnProperty(id)
                && iteratedAccommodation.accommodation == accommodationData.accommodation) {
                if (iteratedAccommodation.end === accommodationData.begin) {
                    neighborsBySides['left'] = iteratedAccommodation;
                } else if (iteratedAccommodation.begin === accommodationData.end) {
                    neighborsBySides['right'] = iteratedAccommodation;
                }
            }
        }

        return neighborsBySides;
    }
}