(function () {
  if (isMobileDevice()) {
    var body = document.querySelector('body');

    window.addEventListener('scroll', function (e) {
      // console.dir(e);
      // console.dir(e.srcElement.activeElement);
      // console.dir(e.srcElement.activeElement.localName);
      // console.log(body.classList.contains('hidden-logo'));
      // if (e.srcElement.activeElement.localName === 'body') {
      var logoIsHidden = body.classList.contains('hidden-logo');
      var offSet = window.pageYOffset;
      if (offSet >= 49 && !logoIsHidden) {
        // console.log('hidden');
        body.classList.add('hidden-logo');
      } else if (offSet < 49 && logoIsHidden) {
        // console.log('visible');
        body.classList.remove('hidden-logo');
      }
      // }

    });
  }

})();