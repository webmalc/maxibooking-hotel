/**
 * Created by mb on 23.04.15.
 */
(function($){

    var $documents = $(".alert.alert-warning.package-main-info");

    $(".remove-package-document").on("click", function(){
        var $this = $(this);
        $this.addClass("disabled").children("i.fa-trash-o").removeClass("fa-trash-o").addClass("fa-spinner fa-spin");

        $.ajax({
            url: Routing.generate('package_remove_document', {id: $this.data('package')}),
            data: {filename : $this.data('filename')},
            method: "post",
            success: function (data) {
                if(data.success){
                    $this.closest(".package-document").remove();
                    if($documents.find(".package-document").length == 0)
                        $documents.hide();
                }else{
                    console.log(data.error)//notification
                }
            },
            dataType: 'json'
        });
    })
})(window.jQuery)