/*global window, $ */
$(document).ready(function(){
    $('#task-table').dataTable({
        "processing": true,
        "serverSide": true,
        "ordering": false,
        "searching": false,
        "autoWidth": false,
        "ajax": {
            "url": Routing.generate('task_json')
        }
    });

    var $taskDate = $('#mbh_bundle_hotelbundle_task_date');
    if ($taskDate)
        $taskDate.datepicker({
            language: "ru",
            autoclose: true,
            startView: 2,
            format: 'dd.mm.yyyy'
        });
});