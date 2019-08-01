var isMobileDevice = (function() {
  var isMobile = /Mobi/.test(navigator.userAgent);
  if (isMobile) {
    isMobile = !(/iPad|vivo/.test(navigator.userAgent));
  }
  return function() {
    return isMobile;
  }
})();

var isTabletDevice = (function() {
  var isTablet = window.matchMedia('only screen and (min-width: 700px) and (max-width: 1023px)').matches;
  return function() {
    return isTablet;
  }
})();
