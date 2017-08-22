/**
 * @author Tamas Laszlo
 * @copyright OANDER Media Kft.
 * @project istyle
 * @date  2017.08.03
 */

define([
  'jquery',
  'mage/bootstrap'
], function ($) {
  'use strict';

  /**
     * Enable Bootstrap-tooltip for the whole webpage
     */
  $(function () {
    $('[data-toggle="tooltip"]').tooltip({
      position: { my: 'center+25 bottom', at: 'center top-10' }
    });
  });
});
