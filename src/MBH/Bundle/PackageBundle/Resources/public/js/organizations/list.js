(function($){
    var $organizationTable = $('#organizations-table');
    var organizationType = $organizationTable.data('type');

    var langOptions =

    $organizationTable.dataTable({
        //paging: false,
        pageLength    : mbh.datatablesOptions.pageLength,
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
            "sProcessing": Translator.trans('list.sProcessing') + "...",
            "sLengthMenu": Translator.trans('list.sLengthMenu', {"menu": "_MENU_"}),
            "sZeroRecords": Translator.trans("list.sZeroRecords"),
            "sInfo": Translator.trans("list.sInfo", {'start' : '_START_', 'end' : '_END_', 'total' : "_TOTAL_"}),
            "sInfoEmpty": Translator.trans("list.sInfoEmpty"),
            "sInfoFiltered": "(" + Translator.trans("list.sInfoFiltered", {"max" : "_MAX_"}) + ")",
            "sEmptyTable": Translator.trans("list.sEmptyTable"),
            "sInfoPostFix": "",
            "sSearch": Translator.trans("list.sSearch") + " ",
            "sUrl": "",
            "oPaginate": {
                "sFirst": Translator.trans("list.sFirst"),
                "sPrevious": isMobileDevice()? mbh.datatablesOptions.language.paginate.previous :Translator.trans("list.sPrevious"),
                "sNext": isMobileDevice()? mbh.datatablesOptions.language.paginate.next :Translator.trans("list.sNext"),
                "sLast": Translator.trans("list.sLast")
            },
            "oAria": {
                "sSortAscending": ": " + Translator.trans("list.sSortAscending"),
                "sSortDescending": ": " + Translator.trans("list.sSortDescending")
            }
        }
    });
})(jQuery);