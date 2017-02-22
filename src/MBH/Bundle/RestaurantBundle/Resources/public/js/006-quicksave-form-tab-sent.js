/* global document, window, $, console */
/**
 * Created by zalex on 7/18/16.
 */

$(document).ready(function (event) {
    "use strict";
    var $form = $("form");
    if ($form.length) {
        $form.on('submit', function (event) {
            var tab = $('ul.nav').find('li.active').find('a').attr('href');
            $("#activetab").val(tab);
        });
    }
});