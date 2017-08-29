/**
 * @author Zoltan Belicza
 * @author Istvan Turupoli
 * @copyright OANDER Media Kft.
 * @project istyle
 * @date  2017.08.23
 */

define([
  'jquery'
], function ($) {
  'use strict';

  function elementLoaded(el, cb) {
    if ($(el).length) {
      cb($(el));
    } else {
      setTimeout(function() {
        elementLoaded(el, cb)
      }, 500);
    }
  }

  elementLoaded('.field.street .control .form-control', function(el) {
    var form_elem = document.querySelector('.field.street .control .form-control'),
        label = document.querySelector('.field legend span').innerHTML;
    form_elem.setAttribute('placeholder', label);
  });

  // Add placeholders to inputs on Account Create page
  var formGroups = $('.form-create-account .form-group .form-control');
  $(formGroups).each(function() {
    var formLabel = $(this).attr('title');
    $(this).attr('placeholder', formLabel);
  });

});
