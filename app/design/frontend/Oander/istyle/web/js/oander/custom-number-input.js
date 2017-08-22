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
     * Custom styled number input - the function should be called on <input /> element(s)
     * @param  {String} parentClass - class to be added to DOM element(s) on which the function is called
     */
  $.fn.addCustomNumberInput = function (parentClass) {
    $(this).each(function () {
      var $this = $(this);

      $this.wrap('<div class="' + parentClass + '"></div>');

      $('<div class="quantity-nav">' +
                '<div class="quantity-button quantity-up">+</div>' +
                '<div class="quantity-button quantity-down">-</div>' +
              '</div>').insertAfter($this);

      var spinner = $($this.parent());
      var input = spinner.find('input[type="number"]');
      var btnUp = spinner.find('.quantity-up');
      var btnDown = spinner.find('.quantity-down');
      var min = input.attr('min') !== undefined ? input.attr('min') : Number.MIN_VALUE;
      var max = input.attr('max') !== undefined ? input.attr('max') : Number.MAX_VALUE;

      btnUp.on('click', function () {
        var oldValue = parseFloat(input.val());

        if (oldValue < max) {
          input.val(oldValue + 1)
            .trigger('change');
        }
      });

      btnDown.on('click', function () {
        var oldValue = parseFloat(input.val());

        if (oldValue > min) {
          input.val(oldValue - 1)
            .trigger('change');
        }
      });
    });
  };
});
