/* global $, Routing, url */

var $form = $("#payRestForm");
console.log(url);
$form.on('submit', function (e) {
    e.preventDefault();
    $.ajax({
        url: url,
        data: $form.serialize(),
        method: "POST"
    })
    .done(function (data) {
        data = $.parseJSON(data);
        console.log(data);
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        console.log('fail');
    })

});
