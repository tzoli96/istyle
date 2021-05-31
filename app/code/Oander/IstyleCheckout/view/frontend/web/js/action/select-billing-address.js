/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
  [
      'jquery',
      'Magento_Checkout/js/model/quote',
      'Magento_Checkout/js/checkout-data'
  ],
  function ($, quote, checkoutData) {
      'use strict';

      return function (billingAddress) {
          var address = null;

          if (quote.shippingAddress() && billingAddress.getCacheKey() == quote.shippingAddress().getCacheKey()) {
              address = $.extend({}, billingAddress);
              address.saveInAddressBook = null;
          } else {
              address = billingAddress;
          }
          quote.billingAddress(address);
          checkoutData.setBillingAddressFromData(address);
      };
  }
);
