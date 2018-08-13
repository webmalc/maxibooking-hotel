/**
 * Created by danya on 26.06.17.
 */
/*global window, $, document, deleteLink, mbh */
$(document).ready(function () {
    var sliders = {
        saturation: {
            maxLeft: 300,
            maxTop: 300,
            callLeft: 'setSaturation',
            callTop: 'setBrightness'
        },
        hue: {
            maxLeft: 0,
            maxTop: 300,
            callLeft: false,
            callTop: 'setHue'
        },
        alpha: {
            maxLeft: 0,
            maxTop: 300,
            callLeft: false,
            callTop: 'setAlpha'
        }
    };
    $('.color-picker').colorpicker({'sliders': sliders});
    deleteLink();
});
