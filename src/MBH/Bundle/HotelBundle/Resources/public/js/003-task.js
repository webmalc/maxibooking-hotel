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
});