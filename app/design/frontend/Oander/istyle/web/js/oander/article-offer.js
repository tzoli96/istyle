/**
 * @author Tamas Laszlo
 * @copyright OANDER Media Kft.
 * @project istyle
 * @date  2017.08.30
 */

define([
  'jquery'
], function ($) {
  'use strict';

  /**
   * Change links for iMagazin article offers
   */
  $(function () {
    $('.widget-block .link[href*="imagazin"]')
      .html('<i class="icon icon-imagazin-logo"></i>Read our test on iMagazin')
      .addClass('imagazin-link');
  });
});
