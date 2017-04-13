/**
 * @author Imre Toth
 * @copyright OANDER Media Kft.
 * @project istyle
 * @date 2017.04.11. 12:27
 */

define([
  'jquery',
  'mage/translate',
  'matchMedia'
], function ($) {
  'use strict';

  $.widget('oander.navigation', {

    options: {
      navigationMode: null,
      categoryAllLink: false,
      breakpoint: '(max-width: 992px)'
    },
    selectors: {
      navContainer: '.navigation.container',
      submenu: '.submenu',
      headerAccount: '.header-account',
      accountToggle: '#header-account-toggle'
    },


    /**
     * Init function
     * @private
     */
    _init: function () {

      var _ = this;

      _.navigationMode();
      _.generateNavigationIcons();
      _.initMobileNavigation();
      _.submenuPositionWatcher();

    },


    /**
     * Toggle options.navigationMode desktop/mobile
     */
    navigationMode: function() {

      var _ = this;
      var breakpoint = window.matchMedia(_.options.breakpoint);

      var handleBP = function (breakpoint) {
        if (breakpoint.matches) {
          _.options.navigationMode = 'mobile';
        } else {
          _.options.navigationMode = 'desktop';
          _.hideMobileNavigation();
        }
      };

      handleBP(breakpoint);
      breakpoint.addListener(handleBP);
    },


    /**
     * This function opens the mobile navigation
     */
    showMobileNavigation: function() {

      $('html').addClass('nav-open');
      $('body').append('<div class="modal-backdrop fade show" data-html-class="nav-open" data-action="modal-close" id="modal-backdrop"></div>');
    },


    /**
     * This function removes the mobile navigation and removes the css classes
     */
    hideMobileNavigation: function() {

      $('.navigation').find('.parent').removeClass('active');
      $('html').removeClass('nav-open');
      $('#modal-backdrop').remove();
    },


    /**
     * This function generates icons for the parent elements
     * @param iconClass | String
     */
    generateNavigationIcons: function(iconClass) {

      var _ = this;
      iconClass = iconClass || 'icon icon-chevron-down';

      $(_.selectors.navContainer).find('li').each(function() {

        if ($(this).hasClass('parent')) {
          $(this).children('a').append('<i class="' + iconClass + '"></i>');
        }
      });
    },


    /**
     * This function
     */
    initMobileNavigation: function() {

      var _ = this;
      var parent = $('.navigation .parent');

      // Open mobile navigation
      //$('.action.nav-toggle').on('click', this.showMobileNavigation);
      $('.action.nav-toggle').on('click', function() {

        if($('html').hasClass('nav-open')) {
          _.hideMobileNavigation();
        } else {
          _.showMobileNavigation();
        }

      });

      if(_.options.categoryAllLink === true) {
        _.generateCategoryLinks();
      }

      _.toggleMobileCategories();
    },


    /**
     * Click categories or categories' arrow on mobile.
     */
    toggleMobileCategories: function() {

      var _ = this;
      var link = $('.navigation a');

      link.on('click', function(e) {
        if(_.options.navigationMode === 'mobile') {

          var li = $(this).parent('li');

          if(li.hasClass('parent')) {
            if(_.options.categoryAllLink === true) {
              li.toggleClass('active');
              e.preventDefault();
            } else {
              if($(e.target).hasClass('icon')) {
                li.toggleClass('active');
                e.preventDefault();
              }
            }
          }
        }
      });
    },


    /**
     * This function generates "Shore all" links to the categories (in mobile view)
     */
    generateCategoryLinks: function () {

      var _ = this;

      $(_.selectors.navContainer).find('li').each(function() {

        var li = '<li class="hidden-lg-up"><a class="view-all"></a></li>';

        if($(this).hasClass('parent')) {

          var ul = '';
          var url = $(this).find('a').attr('href');
          var submenu = $(this).children(_.selectors.submenu);

          if(submenu.hasClass('block-added')) {
            ul = submenu.children('.sub-category').children('.inner');
          } else {
            ul = submenu;
          }

          ul.prepend(li);
          ul.find('a.view-all').attr('href', url).text($.mage.__('All'));
        }
      });
    },


    /**
     * This function watches the submenu position.
     */
    submenuPositionWatcher: function() {

      var _ = this;

      /**
       * This function adds or removes the classes from list elements.
       */
      function classManager() {

        var innerWidth = window.innerWidth;

        $(_.selectors.navContainer).find('li.level0').removeClass('inverse');
        $(_.selectors.navContainer).find('li.level0').each(function() {

          if($(this).hasClass('parent')) {

            var submenu = $(this).children('.submenu');
            var getBoundingClientRect = submenu[0].getBoundingClientRect();
            var width = submenu.outerWidth();
            var offsetLeft = getBoundingClientRect.left;
            var closest = $(this).closest('li.level0');

            if(innerWidth < (width + offsetLeft)) {
              closest.addClass('inverse');
            }
          }
        });
      }

      classManager();

      window.addEventListener('resize', classManager);

    }
  });

  return $.oander.navigation;

});