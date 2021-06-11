define([
  'ko',
  'Magento_Checkout/js/model/quote',
], function (ko, quote) {
  'use strict';

  return {
    /**
     * Get shipping address
     * @returns {String}
     */
     getShippingAddress: ko.computed(function () {
      var shippingAddress = quote.shippingAddress();
      var address = '';

      if (shippingAddress) {
        (shippingAddress.postcode !== '*'
        && (shippingAddress.postcode !== undefined && shippingAddress.postcode !== '')
        && (shippingAddress.city !== undefined && shippingAddress.city !== '')
        && (shippingAddress.street !== undefined && shippingAddress.street !== '')
        && (shippingAddress.firstname !== undefined && shippingAddress.firstname !== '')
        && (shippingAddress.lastname !== undefined && shippingAddress.lastname !== ''))
        ? address = shippingAddress.firstname + ' ' + shippingAddress.lastname + ', ' + shippingAddress.street[0] + ', ' + shippingAddress.city + ', ' + shippingAddress.postcode
        : address = false;
      }
      else {
        address = false;
      }

      return address;
    }),

    /**
     * Is filled
     * @param {Object<String> | String} value
     * @returns String
     */
    isFilled: function (value) {
      if (value) return 'filled';
      return '';
    }
  }
});
