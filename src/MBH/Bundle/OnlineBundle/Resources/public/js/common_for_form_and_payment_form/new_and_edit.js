if ($("#mbh_bundle_onlinebundle_form_type_formTemplate").length) {
  elements.push(
      {
        elementId: 'mbh_bundle_onlinebundle_form_type_formTemplate',
        type: 'xml'
      }
  )
}

var applyCodeMirror = function (element, mode) {
  CodeMirror.fromTextArea(element, {
    lineNumbers: true,
    mode: mode,
    extraKeys: {
      "F11": function (cm) {
        cm.setOption("fullScreen", !cm.getOption("fullScreen"));
      },
      "Esc": function (cm) {
        if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
      }
    }
  });
};

window.addEventListener('load', function() {
  for (var i in elements) {
    var element = elements[i];
    applyCodeMirror(document.getElementById(element.elementId), element.type);
  }
});
