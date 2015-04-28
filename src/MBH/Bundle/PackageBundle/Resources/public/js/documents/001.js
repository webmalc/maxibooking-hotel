/**
 * Created by mb on 23.04.15.
 */
(function($){
    /*$(".remove-package-document").on("click", function(){
        var $this = $(this);
        $this.addClass("disabled").children("i.fa-trash-o").removeClass("fa-trash-o").addClass("fa-spinner fa-spin");

        $.ajax({
            url: Routing.generate('package_remove_document', {id: $this.data('package')}),
            data: {docname : $this.data('docname')},
            method: "post",
            success: function (data) {
                if(data.success){
                    $this.closest("tr").remove();
                }else{
                    console.log(data.error)//notification
                }
            },
            dataType: 'json'
        });
    })*/

    $('#documents-table').dataTable({
        //paging: false,
        pageLength: 10,
        order: [[4, 'desc']],
        info: true,
        stateSave: true,


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

    $('.fancybox').fancybox();
})(window.jQuery)