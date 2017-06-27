/**
 * Created by danya on 26.06.17.
 */
/*global window, $, document, deleteLink, mbh */
$(document).ready(function () {
    var CPSize = 300;
    var colorPickerTemplate = '<div class="colorpicker dropdown-menu">'+
    '<div class="colorpicker-saturation" style="width:'+CPSize+'px;height:'+CPSize+'px"><i><b></b></i></div>'+
    '<div class="colorpicker-hue" style="height:'+CPSize+'px"><i></i></div>'+
    '<div class="colorpicker-alpha" style="height:'+CPSize+'px"><i></i></div>'+
    '<div class="colorpicker-color"><div /></div>'+
    '</div>';
    $('.color-picker').colorpicker({template: colorPickerTemplate});
    deleteLink();
});
