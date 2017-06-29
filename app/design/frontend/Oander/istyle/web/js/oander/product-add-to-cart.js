/**
 * @author Imre Toth
 * @copyright OANDER Media Kft.
 * @project istyle
 * @date  2017.06.29 10:51
 */

define([
  'jquery'
], function ($) {
  'use strict';

  var selectors = {
    form: '#product_addtocart_form',
    button: '#oander-product-addtocart-button'
  };

  /**
   * Posting form
   */
  $(selectors.button).on('click', function (e) {
    $(selectors.form).submit();
    e.preventDefault();
  });

});