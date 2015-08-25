(function($){
    var $organizationTable = $('#organizations-table');
    var organizationType = $organizationTable.data('type');
    $organizationTable.dataTable({
        //paging: false,
        pageLength: 10,
        //iDisplayLength : 2,
        //order: [[4, 'desc']],
        info: true,
        stateSave: true,

        aoColumnDefs: [
            { 'bSortable': false, 'aTargets': [ 3 ] }
        ],

        serverSide: true,
        ajax: {
            url: Routing.generate('organization_json'),
            data: function (d) {
                d.type = organizationType;
            }
        },
        drawCallback: function (settings, json) {
            deleteLink();
        },

        /*columnDefs: [
         { "type": "de_date", targets: 4 }
         ],*/

        /*"sPaginationType": "full_numbers",
         "bFilter": false,
         "bSearchable":false,
         "bInfo":false,*/
        "bLengthChange": false,
        //"bPaginate": false,

        language: {
            "sProcessing": "Подождите...",
            "sLengthMenu": "Показать _MENU_ записей",
            "sZeroRecords": "Записи отсутствуют.",
            "sInfo": "Записи с _START_ до _END_ из _TOTAL_ записей",
            "sInfoEmpty": "Записи с 0 до 0 из 0 записей",
            "sInfoFiltered": "(отфильтровано из _MAX_ записей)",
            "sEmptyTable": "Ничего не найдено",
            "sInfoPostFix": "",
            "sSearch": "Поиск ",
            "sUrl": "",
            "oPaginate": {
                "sFirst": "Первая",
                "sPrevious": "Назад",
                "sNext": "Вперед",
                "sLast": "Последняя"
            },
            "oAria": {
                "sSortAscending": ": активировать для сортировки столбца по возрастанию",
                "sSortDescending": ": активировать для сортировки столбцов по убыванию"
            }
        },
    });
})(jQuery);