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

  // Shipping methods tabs
  $('body').on('click', '.switch--delivery', function(e) {
    e.preventDefault();
    var clickedTab = $(this).attr('href').substring(1);
    
    $('.delivery-content').addClass('d-none');
    $('.data.item.title').removeClass('active')
    $(this).closest('.data.item.title').addClass('active');
    $('#' + clickedTab).removeClass('d-none');
  });

  var mixin = {
    /**
     * Is logged in
     * @returns {Boolean}
     */
    isLoggedIn: function () {
      return customer.isLoggedIn() ? false : true;
    },

    /**
     * Are tabs needed
     * @returns {Object}
     */
    areTabsNeeded: function() {
      var ratesArray = this.rates(),
          firstItem = '',
          areNeeded = false,
          firstArray = [],
          secondArray = [];

      for (var i = 0; i < ratesArray.length; i++) {
        if (i === 0) {
          firstItem = ratesArray[0].carrier_code
          firstArray.push(ratesArray[0]);
        } else {
          if (ratesArray[i].carrier_code.charAt(0) !== firstItem.charAt(0)) {
            areNeeded = true;
            secondArray.push(ratesArray[i]);
          } else {
            firstArray.push(ratesArray[i]);
          }
        }
      }

      return {
        needed: areNeeded,
        firstArray: firstArray,
        secondArray: secondArray
      };
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
        email: helpers.isFilled((customer.isLoggedIn()) ? window.checkoutConfig.customerData.email : checkoutData.getInputFieldEmailValue()),
        shippingMethod: helpers.isFilled(checkoutData.getSelectedShippingRate()),
        shippingAddress: helpers.isFilled(helpers.getShippingAddress()),
        billingAddress: helpers.isFilled(checkoutData.getBillingAddressFromData()),
        paymentMethod: helpers.isFilled(checkoutData.getSelectedPaymentMethod()),
      };

      var checkShippingMethod = function () {
        if (helpers.isFilled(checkoutData.getSelectedShippingRate())) {
          if (quote.shippingMethod()) return true;
          return false;
        }
        else {
          return true;
        }
      }

      if (!pageLoaded && checkShippingMethod()) {
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
