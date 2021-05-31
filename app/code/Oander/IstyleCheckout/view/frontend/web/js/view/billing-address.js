define([
  'ko',
  'Magento_Checkout/js/model/quote',
], function (ko, quote) {
  'use strict';

  var mixin = {
    getBillingAddress: function () {
      var billingAddress = quote.billingAddress();
      var address = '';
      
      if (this.isAddressSameAsShipping()) {
        console.log('same as shipping');
      }
      else {
        console.log('not same as shipping');
      }

      if (billingAddress) {
        (billingAddress.postcode !== '*'
        && (billingAddress.postcode !== undefined && billingAddress.postcode !== '')
        && (billingAddress.city !== undefined && billingAddress.city !== '')
        && (billingAddress.street !== undefined && billingAddress.street !== '')
        && (billingAddress.firstname !== undefined && billingAddress.firstname !== '')
        && (billingAddress.lastname !== undefined && billingAddress.lastname !== ''))
        ? address = billingAddress.firstname + ' ' + billingAddress.lastname + ', ' + billingAddress.street[0] + ', ' + billingAddress.city + ', ' + billingAddress.postcode
        : address = false;
      }
      else {
        address = false;
      }

      return address;
    },
  };

  return function (target) {
    return target.extend(mixin);
  };
});
