var isMobileDevice = (function() {
  var isMobile = /Mobi/.test(navigator.userAgent);
  return function() {
    return isMobile;
  }
})();