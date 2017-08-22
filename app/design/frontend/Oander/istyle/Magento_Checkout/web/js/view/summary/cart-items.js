/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* browser:true */
/* global define */
define(
  [
    'jquery',
    'ko',
    'Magento_Checkout/js/model/totals',
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/quote'
  ],
  function ($, ko, totals, Component) {
    'use strict';

    return Component.extend({
      defaults: {
        template: 'Magento_Checkout/summary/cart-items'
      },
      desktop: { openedState: 'active', active: true },
      mobile: { openedState: 'active', active: false },
      totals: totals.totals(),
      getItems: totals.getItems(),
      mediaQuery: undefined,
      initialize: function () {
        this._super();

        this.mediaQuery = window.matchMedia('(min-width: 992px)');
        this.mediaQuery.addListener(this._toggleSettings);

        return this;
      },
      getItemsQty: function () {
        return parseFloat(this.totals.items_qty);
      },
      getDefaultSettings: function () {
        return this.mediaQuery.matches ? this.desktop : this.mobile;
      },
      _toggleSettings: function (mediaQuery) {
        var $elements = $('.items-in-cart');

        if (mediaQuery.matches) {
          $elements.each(function () {
            $(this).collapsible('activate');
          });
        } else {
          $elements.each(function () {
            $(this).collapsible('deactivate');
          });
        }
      }
    });
  }
);
