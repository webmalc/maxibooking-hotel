(function () {
  if (isMobileDevice()) {
    var body = document.querySelector('body');

    window.addEventListener('scroll', function (e) {
      var logoIsHidden = body.classList.contains('hidden-logo');
      var offSet = window.pageYOffset;
      if (offSet >= 49 && !logoIsHidden) {
        body.classList.add('hidden-logo');
      } else if (offSet < 49 && logoIsHidden) {
        body.classList.remove('hidden-logo');
      }
    });
  }

})();