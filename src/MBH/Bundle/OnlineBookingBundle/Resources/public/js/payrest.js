/* global $, Routing, url */

var $form = $("#payRestForm");
$form.on('submit', function (e) {
    e.preventDefault();
    var that = this;
    $.ajax({
        url: $(that).attr('action'),
        method: $(that).attr('method'),
        data: $(that).serialize()
    })
        .done(function (data) {
            // console.log(data);
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.log(textStatus);
        });
});
// $form.on('submit', function (e) {
//     e.preventDefault();
//     $.ajax({
//         url: url,
//         data: $form.serialize(),
//         method: "POST"
//     })
//     .done(function (data) {
//         data = $.parseJSON(data);
//         console.log(data);
//     })
//     .fail(function(jqXHR, textStatus, errorThrown) {
//         console.log('fail');
//     })
//
// });
