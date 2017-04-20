/**
 * @author Imre Toth
 * @copyright OANDER Media Kft.
 * @project istyle
 * @date  2017.04.20 16:19
 */

define([
  'jquery',
  'mage/translate',
  'matchMedia'
], function ($) {
  'use strict';

  $('.js-accordion-box h2').on('click', function(e) {
    $(this).closest('.js-accordion-box').toggleClass('active');
  });


});
