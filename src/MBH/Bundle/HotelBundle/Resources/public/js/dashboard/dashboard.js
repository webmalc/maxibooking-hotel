/*global window, document, Routing */

$(document).ready(function () {
    if (document.getElementById('flow-widgets')) {
        $.ajax({
            url: Routing.generate('flow_progress'),
            dataType: 'json',
            method: "GET",
            success: function (response) {
                if (response.success === true) {
                    var progressData = response.data;
                    for (var flowType in progressData) {
                        if (progressData.hasOwnProperty(flowType)) {
                            var progressInPercents = progressData[flowType];
                            var $floWidget = $('#' + flowType);

                            var widgetColor;
                            if (progressInPercents === 0) {
                                widgetColor = 'red';
                            } else if (progressInPercents === 100) {
                                widgetColor = 'green';
                            } else {
                                widgetColor = 'yellow'
                            }

                            $floWidget.find('.small-box').addClass('bg-' + widgetColor);
                            $floWidget.find('.flow-progress').text(progressInPercents + '%');
                        }
                    }
                }
            }
        });
    }

    if (document.getElementById('not-confirmed-packages-box')) {
        $.ajax({
            url: Routing.generate('packages_list_api', {asHtml: 'true'}),
            dataType: 'json',
            data: {criteria: {isConfirmed: false}, limit: 7},
            method: "GET",
            success: function (response) {
                var $tableBody = $('#not-confirmed-packages-table tbody');
                response.data.forEach(function (packageData) {
                    var tableLine = document.createElement('tr');
                    packageData.forEach(function (cellHtml) {
                        var cell = document.createElement('td');
                        cell.innerHTML = cellHtml;
                        tableLine.appendChild(cell);
                    });

                    $tableBody.append(tableLine);
                });
            }
        });
    }

    if (document.getElementById('checkinout-widget')) {
        $.ajax({
            url: Routing.generate('current_packages'),
            dataType: 'json',
            method: "GET",
            success: function (response) {
                if (response.success === true) {
                    $('#number-of-check-out').text(response['data']['out'])
                    $('#number-of-check-in').text(response['data']['arrivals'])
                }
            }
        });
    }
});