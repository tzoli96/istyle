define([
  'jquery',
], function ($) {
  'use strict';

  var base = {
    /**
     * Scroll to form
     * @param {HTMLElement} formElement
     * @returns {Void}
     */
    scrollToForm: function (formElement) {
      if (formElement.length) {
        $('html, body').animate({
          scrollTop: formElement.offset().top - 100,
        }, 500);
      }
    },
  };

  return base;
});
