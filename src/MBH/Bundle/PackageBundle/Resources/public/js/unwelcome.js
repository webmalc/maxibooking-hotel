/*global Translator */
$(document).ready(function() {
    'use strict';
    var $unwelcomeForm = $('#mbh_package_bundle_unwelcome');
    var levels = [
        '',
        Translator.trans('unwelcome.levels.minor'),
        Translator.trans('unwelcome.levels.low'),
        Translator.trans('unwelcome.levels.middle'),
        Translator.trans('unwelcome.levels.high'),
        Translator.trans('unwelcome.levels.very_high')
    ];

    var levelColors = [
        '',
        '#34A42D',
        '#A4952D',
        '#A45B2D',
        '#A42D2D',
        '#F73636'
    ];

    var $radioInputs = $unwelcomeForm.find('input[type=radio]');
    $radioInputs.on('change', function () {
        updateRadioLabel($(this));
    });

    var updateRadioLabel = function ($inputs) {
        $inputs.each(function() {
            var $this = $(this);
            var $formGroup = $this.closest('.col-sm-6');//$this.closest('.form-group');
            var value = $this.val();
            var text = levels[value];
            var color = levelColors[value];
            var labelId = $this.attr('id') + '_label';
            var $label = $formGroup.find('#' + labelId);
            if($label.length == 0) {
                $label = $('<strong style="margin-left:7px" id="'+ labelId +'">'+text+'</strong>');
                $formGroup.append($label);
            } else {
                $label.html(text);
            }
            $label.css('color', color);
        })
    };

    updateRadioLabel($radioInputs.filter(function () {
        return $(this).prop('checked');
    }));
});