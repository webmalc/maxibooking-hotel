$(function () {
    var $modal = $("#modal-default"),
        $form = $modal.find('form'),
        $priorityCell = $form.find("#image_priority_form_priority");
    $form.on('submit', function (event) {
        if(!$priorityCell.length) {
            event.preventDefault();
        }
    });

    $modal.on('show.bs.modal', function(event){
        var $currentLink = $(event.relatedTarget),
            imageId = $currentLink.data('image'),
            roomTypeId = $currentLink.data('roomtype'),
            action = Routing.generate('room_type_image_edit_priority', {
            'id': roomTypeId,
            'imageId': imageId
        });
        $priorityCell.val($currentLink.text());
        $form.attr('action', action);
    });

});