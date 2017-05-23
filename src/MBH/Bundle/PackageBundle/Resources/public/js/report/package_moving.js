$(document).ready(function ($) {
    var packageMovingInfoId = $('#moving-info-id').val();
    onMoveButtonClickHandler(packageMovingInfoId);
    $('table.dataTable').on('draw.dt', function() {
        onMoveButtonClickHandler(packageMovingInfoId);
    });
    $('#package-moving-report-close-button').click(function () {
        var reportCloseModal = $('#report-close-modal');
        reportCloseModal.modal('show');
        reportCloseModal.find('#report-close-button').click(function () {
            location.href = Routing.generate('close_moving_report', {'movingInfoId': packageMovingInfoId});
        });
    });
    $('#package-moving-report-update-button').click(function () {
        showLoadingIndicator();
        var href = Routing.generate("update_report", {'movingInfoId': packageMovingInfoId});
        $.get(href, function (data) {
            hideLoadingIndicator();
            if (data.success === true) {
                document.location.reload();
            } else {
                handleErrorResponse("Произошла непредвиденная ошибка");
            }
        })
    });
    var reportStatus = document.getElementById('moving-info-status');
    if (reportStatus && reportStatus.value === 'preparing') {
        checkReportStatus(packageMovingInfoId);
    }
});

function checkReportStatus(packageMovingInfoId) {
    setTimeout(function () {
        var href = Routing.generate("is_report_ready", {'movingInfoId': packageMovingInfoId});
        $.get(href, function (data) {
            var isReportReady = data.ready;
            if (isReportReady === true) {
                document.location.reload();
            }
        });
        checkReportStatus(packageMovingInfoId);
    }, 15000);
}

function onMoveButtonClickHandler(packageMovingInfoId) {
    $('.package-move-button').click(function () {
        showLoadingIndicator();
        var button = this;
        button.classList.add('disabled');
        var movingPackageDataId = button.id;
        button.innerHTML = '<i style="font-size: 1.4em;" class="fa fa-circle-o-notch fa-spin"></i>';
        $.ajax({
            url: Routing.generate('package_move', {
                'movingInfoId': packageMovingInfoId,
                'movingPackageId': movingPackageDataId
            }),
            method: "GET",
            success: function (result) {
                hideLoadingIndicator();
                $(button).unbind('click');
                var buttonText;
                if (result.success) {
                    buttonText = 'Перемещено';

                } else {
                    buttonText = 'Не перемещено';
                    handleErrorResponse(result.error);
                }
                button.innerHTML = buttonText;
            },
            error: function () {
                $(button).unbind('click');
                hideLoadingIndicator();
                handleErrorResponse();
            },
            datatype: 'json'
        })
    });
}

function handleErrorResponse(message) {
    message = message || 'Произошла непредвиденная ошибка';
    var messageDiv = document.createElement('div');
    messageDiv.className = 'alert alert-dismissable autohide alert-danger';
    messageDiv.innerHTML = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
    messageDiv.innerHTML += message;
    messageDiv.style.width = getComputedStyle(document.getElementsByClassName('box')[0]).width;

    document.getElementById('messages').appendChild(messageDiv);
}
function showLoadingIndicator() {
    $('#dimmer').show();
    $('#loading-indicator').show();
}

function hideLoadingIndicator() {
    $('#dimmer').hide();
    $('#loading-indicator').hide();
}