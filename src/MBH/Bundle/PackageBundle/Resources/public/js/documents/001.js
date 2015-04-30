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


        /*"sPaginationType": "full_numbers",
        "bFilter": false,
        "bSearchable":false,
        "bInfo":false,*/
        "bLengthChange": false,
        //"bPaginate": false,
    });

    $('.fancybox').fancybox();
})(window.jQuery)