var isMobileDevice = (function() {
  var isMobile = /Mobi/.test(navigator.userAgent);
  if (isMobile) {
    isMobile = !(/iPad|vivo/.test(navigator.userAgent));
  }
  return function() {
    return isMobile;
  }
})();