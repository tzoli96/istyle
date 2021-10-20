define([
  'jquery',
  'ko',
  'Magento_Checkout/js/model/quote',
  'Oander_IstyleCheckout/js/helpers',
  'Magento_Checkout/js/action/get-payment-information',
  'Oander_IstyleCheckout/js/model/store',
  'Magento_Checkout/js/model/payment/method-list',
  'mage/translate'
], function ($, ko, quote, helpers, getPaymentInformationAction, store, methodList, $t) {
  'use strict';

  var paymentMethodList = [];

  var mixin = {
    isPaymentMethodVisible: ko.observable(false),

    /**
     * Get payment method
     * @returns {String}
     */
    getPaymentMethod: ko.computed(function () {
      var paymentMethod = quote.paymentMethod(),
          title = '';

      if (paymentMethodList.length === 0) {
        paymentMethodList = methodList();
      }

      if (paymentMethod) {
        if (paymentMethod.title) {
          title = paymentMethod.title;
        } else {
          for (var i = 0; i < paymentMethodList.length; i++) {
            if (paymentMethodList[i].method === paymentMethod.method) {
              title = paymentMethodList[i].title;
            }
          }
        }
      }

      return title ? title : $t('Please select payment method.');
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

      if (store.steps.paymentMethod() === true) {
        this.isPaymentMethodVisible(true);
      }

      if (currentLS.steps) {
        if (currentLS.steps.paymentMethod) {
          this.isPaymentMethodVisible(true);
        }
      }

      store.steps.paymentMethod.subscribe(function (value) {
        if (value === true) this.isPaymentMethodVisible(true);
      }, this);

      if (store.steps.active() === 'paymentMethod') {
          this.isPaymentMethodVisible(true);
      }

      if (currentLS.steps) {
        if (currentLS.steps.active === 'paymentMethod') {
          this.isPaymentMethodVisible(true);
        }
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
