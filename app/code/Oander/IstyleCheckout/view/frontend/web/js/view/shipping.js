define([
  'ko',
  'Magento_Checkout/js/model/quote',
  'Magento_Customer/js/model/customer'
], function (ko, quote, customer) {
  'use strict';

  var mixin = {
    /**
     * Is logged in
     * @returns {Boolean}
     */
    isLoggedIn: function () {
      return customer.isLoggedIn()
        ? false
        : true;
    },

    /**
     * Get shipping method
     * @returns {String}
     */
    getShippingMethod: ko.computed(function () {
      return quote.shippingMethod()
        ? quote.shippingMethod().method_title
        : 'Please fill the fields.';
    }),

    /**
     * Get shipping address
     * @returns {String}
     */
    getShippingAddress: ko.computed(function () {
      var shippingAddress = quote.shippingAddress();
      var address = '';

      if (shippingAddress) {
        (typeof shippingAddress.city !== 'undefined')
        ? address = shippingAddress.firstname + ' ' + shippingAddress.lastname + ', ' + ((typeof shippingAddress.street == 'Array') ? shippingAddress.street[0] : shippingAddress.street) + ', ' + shippingAddress.city + ', ' + shippingAddress.postcode
        : address = 'Please fill the fields.';
      }
      else {
        address = 'Please fill the fields.';
      }

      return address;
    }),
  };

  return function (target) {
    return target.extend(mixin);
  };
});
