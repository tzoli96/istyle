define([
  'jquery',
  'ko',
  'Magento_Customer/js/model/customer',
  'Magento_Checkout/js/model/quote',
  'Magento_Checkout/js/checkout-data',
  'Oander_IstyleCheckout/js/helpers',
  'domReady!'
], function ($, ko, customer, quote, checkoutData, helpers) {
  'use strict';

  var pageLoaded = false;

  // @todo: remove settimeout
  var triggerPaymentLoad = function () {
    setTimeout(function () {
      $('.block--checkout-step[data-step="shippingAddress"] .action.next-step').trigger('click');
    }, 2000);
  };

  var mixin = {
    /**
     * Is logged in
     * @returns {Boolean}
     */
    isLoggedIn: function () {
      return customer.isLoggedIn() ? false : true;
    },

    /**
     * Get shipping method
     * @returns {String}
     */
    getShippingMethod: ko.computed(function () {
      return checkoutData.getSelectedShippingRate()
        ? (quote.shippingMethod() ? quote.shippingMethod().method_title : '')
        : false;
    }),

    getShippingAddress: ko.computed(function () {
      return helpers.getShippingAddress();
    }),

    // @todo: refact: watch quote instead of checkoutdata
    checkStepContent: ko.computed(function () {
      var steps = {
        email: helpers.isFilled(checkoutData.getInputFieldEmailValue()),
        shippingMethod: helpers.isFilled(checkoutData.getSelectedShippingRate()),
        shippingAddress: helpers.isFilled(helpers.getShippingAddress()),
        billingAddress: helpers.isFilled(checkoutData.getBillingAddressFromData()),
        paymentMethod: helpers.isFilled(checkoutData.getSelectedPaymentMethod()),
      };

      if (!pageLoaded) {
        pageLoaded = true;

        for (var i in steps) {
          if (steps[i] == '') {
            var findElement = setInterval(function () {
              var element = $('.block--checkout-step[data-step="'+ i +'"]');

              if (element) {
                setTimeout(function () {
                  $('.block--checkout-step[data-step="'+ i +'"] .card__action').trigger('click');
                }, 1000);
                clearInterval(findElement);
              }
            }, 500);

            break;
          }
        }
      }
    }),

    isFilled: function (value) {
      return helpers.isFilled(value);
    },
  };

  return function (target) {
    return target.extend(mixin);
  };
});
