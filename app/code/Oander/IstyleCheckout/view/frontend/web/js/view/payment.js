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
        if (store.steps.visible.indexOf('paymentMethod') < 0) {
          store.steps.visible.push('paymentMethod');
        }
      }

      if (currentLS.steps) {
        if (currentLS.steps.paymentMethod) {
          this.isPaymentMethodVisible(true);
          if (store.steps.visible.indexOf('paymentMethod') < 0) {
            store.steps.visible.push('paymentMethod');
          }
        }
      }

      store.steps.paymentMethod.subscribe(function (value) {
        if (value === true) {
          this.isPaymentMethodVisible(true);
          if (store.steps.visible.indexOf('paymentMethod') < 0) {
            store.steps.visible.push('paymentMethod');
          }
        }
      }, this);

      if (store.steps.active() === 'paymentMethod') {
          this.isPaymentMethodVisible(true);
          if (store.steps.visible.indexOf('paymentMethod') < 0) {
            store.steps.visible.push('paymentMethod');
          }
      }

      if (currentLS.steps) {
        if (currentLS.steps.active === 'paymentMethod') {
          this.isPaymentMethodVisible(true);
          if (store.steps.visible.indexOf('paymentMethod') < 0) {
            store.steps.visible.push('paymentMethod');
          }
        }
      }

      store.steps.active.subscribe(function (value) {
        if (value === 'paymentMethod') {
          this.isPaymentMethodVisible(true);
          if (store.steps.visible.indexOf('paymentMethod') < 0) {
            store.steps.visible.push('paymentMethod');
          }
        }
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

    /**
		 * Check if card edit should be visible
		 * @returns {Boolean}
		 */
    isCardEditVisible: function(param) {
			return ko.computed(function() {
        var currentLS = store.getLocalStorage(),
            activeStep,
            visibleSteps,
            visible = ko.observable(true);

        if (store.steps.active() !== '') {
          activeStep = store.steps.active()
        } else {
          if (currentLS !== false) {
            if (currentLS.hasOwnProperty('steps')) {
              if (currentLS.steps.hasOwnProperty('active')) {
                activeStep = currentLS.steps.active;
              } else {
                activeStep = 'auth';
              }
            } else {
              activeStep = 'auth';
            }
          } else {
            activeStep = 'auth';
          }
        }

        if (currentLS !== false) {
          if (currentLS.hasOwnProperty('steps')) {
            if (currentLS.steps.hasOwnProperty('visible')) {
              visibleSteps = currentLS.steps.visible;
            } else {
              visibleSteps = ['auth'];
            }
          } else {
            visibleSteps = ['auth'];
          }
        } else {
          visibleSteps = ['auth'];
        }

				if (visibleSteps.indexOf(param) > -1) {
					if (store.steps.order.indexOf(activeStep) < store.steps.order.indexOf(param)) {
						visible(false);
					}
				}

				return visible();
			});
		},
  };

  return function (target) {
    return target.extend(mixin);
  };
});
