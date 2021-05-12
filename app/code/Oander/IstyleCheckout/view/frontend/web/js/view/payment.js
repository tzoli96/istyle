define([
  'ko',
  'Magento_Checkout/js/model/quote',
], function (ko, quote) {
  'use strict';

  var mixin = {
    /**
     * Get payment method
     * @returns {String}
     */
    getPaymentMethod: ko.computed(function () {
      var paymentMethod = quote.paymentMethod();
      var title = '';

      if (paymentMethod) (paymentMethod.title)
        ? title = paymentMethod.title
        : title = paymentMethod.method

      return title ? title : 'Please fill the fields.';
    }),
  };

  return function (target) {
    return target.extend(mixin);
  };
});
