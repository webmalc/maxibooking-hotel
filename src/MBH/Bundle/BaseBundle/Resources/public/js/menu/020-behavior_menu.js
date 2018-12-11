if (typeof NodeList !== "undefined" && NodeList.prototype && !NodeList.prototype.forEach) {
    // Yes, there's really no need for `Object.defineProperty` here
    NodeList.prototype.forEach = Array.prototype.forEach;
}
var mbhBehaviorMenu = {
  key: 'mbhBehaviorMenu',
  divSidebarAccordion: null,
  getStatus          : function() {
    return additionalSettings.behavior_menu;
  },
  action             : function() {
    if (this.initSidebarAccordion()) {
      if (this.getStatus() === 'custom'){
        this.onLoad();
        this.addListener();
      }
    }
  },
  initSidebarAccordion: function () {
    if (this.getSidebarAccordion() === null) {
      return false;
    }

    return true;
  },
  getSidebarAccordion: function () {
    if (this.divSidebarAccordion === null) {
      var div = document.querySelector('aside[class="main-sidebar"]');
      if (div === null) {
        return null;
      }
      this.divSidebarAccordion = div.querySelector('.sidebar-accordion');
    }

    return this.divSidebarAccordion;
  },
  getTagUl           : function(){
    return this.getSidebarAccordion().
        querySelectorAll('ul.sidebar-menu');
  },
  getTafDivWithClassHeader: function(menuId) {
    return this.getSidebarAccordion().querySelector('div.header[data-target="#' + menuId + '"]');
  },
  addListener        : function() {
    this.getSidebarAccordion().querySelectorAll('div.header')
      .forEach(function(element) {
        element.addEventListener('click', function(evt) {
          mbhBehaviorMenu.writeInLocalStore();
        })
      });
  },
  changeMenu: function(data) {
    for (var key in data){
      document.getElementById(key).className = data[key].ul;
      this.getTafDivWithClassHeader(key).className = data[key].div;
    }
  },
  onLoad             : function(){
    var data = this.getFromLocalStore();
    if (data === false) {
      return;
    }

    this.changeMenu(data);
  },
  getFromLocalStore  : function() {
    var data = localStorage.getItem(this.key);
    if (data === null) {
      this.getTagUl().forEach(function(element) {
        element.classList.add('in');
        this.getTafDivWithClassHeader(element.id).classList.remove('collapsed');
      }, this);

      this.writeInLocalStore();
      return;
    }

    return JSON.parse(data);
  },
  writeInLocalStore  : function() {
    setTimeout(function (){localStorage.setItem(mbhBehaviorMenu.key, mbhBehaviorMenu.getDataJson())},500);
  },
  getDataJson        : function(){
    return JSON.stringify(this.getDataFromNode());
  },
  getDataFromNode    : function() {
    var data = {};
    this.getTagUl().forEach(function(element) {
      data[element.id] = {ul:element.className, div: this.getTafDivWithClassHeader(element.id).className};
    }, this);

    return data;
  }
};

mbhBehaviorMenu.action();