define([
  'jquery',
  'ko',
  'Magento_Checkout/js/model/quote',
  'Oander_IstyleCheckout/js/helpers',
  'Magento_Checkout/js/action/get-payment-information',
  'Oander_IstyleCheckout/js/model/store',
], function ($, ko, quote, helpers, getPaymentInformationAction, store) {
  'use strict';

  var mixin = {
    isPaymentMethodVisible: ko.observable(false),

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

      return title ? title : 'Please select payment method.';
    }),

    storePaymentData: function () {
      quote.paymentMethod.subscribe(function (value) {
        if (value) store.steps.paymentMethod(true);
      });
    },

    /**
     * Payment data
     * @returns {Void}
     */
    paymentData: function () {
      var currentLS = store.getLocalStorage();

      if (store.steps.paymentMethod() === true
        || currentLS.steps.paymentMethod) {
        this.isPaymentMethodVisible(true);
      }
      store.steps.paymentMethod.subscribe(function (value) {
        if (value === true) this.isPaymentMethodVisible(true);
      }, this);

      if (store.steps.active() === 'paymentMethod'
        || currentLS.steps.active === 'paymentMethod') {
          this.isPaymentMethodVisible(true);
      }
      store.steps.active.subscribe(function (value) {
        if (value === 'paymentMethod') this.isPaymentMethodVisible(true);
      }, this);

      this.storePaymentData();
    },

    /**
     * Is active
     * @param {String} step
     * @returns {Boolean}
     */
    isActive: function (step) {
      var currentLS = store.getLocalStorage();

      if (currentLS.steps && (currentLS.steps.active === step)) {
        var deferred = $.Deferred();
        getPaymentInformationAction(deferred);
        helpers.stepCounter($('[data-step="'+ step +'"]'));

        return true;
      }
      else {
        return false;
      }
    },
  };

  return function (target) {
    return target.extend(mixin);
  };
});
