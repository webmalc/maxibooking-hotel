/*global window */
$(document).ready(function () {
    'use strict';
    
    //Set active menu link
    /*(function () {
        $('#navbar-main ul li[class != "dropdown-toggle"] a').each(function () {
            if (window.location.pathname.toLowerCase().indexOf($(this).attr('href')) >= 0) {
                
            }
        });
    }());*/

    //Toggle menus
    (function () {
        var toggleLink = $('#menu-toggle-link a'),
            managementMenu = $('#management-menu'),
            mainMenu = $('#main-menu'),
            nav = $('#navbar-main-wrapper')
        ;
        
        if (!managementMenu.length) {
            return false;
        }

        if (window.location.pathname.indexOf("/management/") > -1 && mainMenu.is(":visible")) {
            mainMenu.hide();
            managementMenu.show();
            toggleLink.find('i').toggleClass('fa-gears fa-home');
            nav.toggleClass('navbar-default navbar-inverse');
            toggleLink.parent('li').attr('data-original-title', 'Назад к главному меню');
        }
        toggleLink.click(function(event){
            event.preventDefault();
            
            toggleLink.find('i').toggleClass('fa-gears fa-home');
            managementMenu.toggle('fast');
            mainMenu.toggle('fast', function () {
                if (mainMenu.is(":visible")) {
                    toggleLink.parent('li').attr('data-original-title', 'Перейти к настройкам');
                } else {
                    toggleLink.parent('li').attr('data-original-title', 'Назад к главному меню');
                }
            });
            nav.toggleClass('navbar-default navbar-inverse');
        });
    }());
});

