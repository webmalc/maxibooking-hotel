$(document).ready(function ($) {
    var packageMovingInfoId = $('#moving-info-id').val();
    $('.package-move-button').click(function () {
        var button = this;
        var movingPackageDataId = button.id;
        button.innerHTML = '<i class="fa fa-circle-o-notch fa-spin"></i>';
        $.ajax({
            url: Routing.generate('package_move', {
                'movingInfoId': packageMovingInfoId,
                'movingPackageId': movingPackageDataId
            }),
            method: "GET",
            success: function (result) {
                button.innerHTML = 'Перемещено';
                button.classList.add('disabled');
                $(this).unbind('click');
            },
            error: function (result) {
                console.log(result)
            },
            datatype: 'json'
        })
    });

    $('#package-moving-report-close-button').click(function () {
        var reportCloseModal = $('#report-close-modal');
        reportCloseModal.modal('show');
        reportCloseModal.find('#report-close-button').click(function () {
            location.href = Routing.generate('close_moving_report', {'movingInfoId' : packageMovingInfoId});
        });
    })
});