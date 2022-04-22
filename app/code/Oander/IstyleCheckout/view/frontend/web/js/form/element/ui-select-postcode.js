define([
  'jquery',
  'ko',
  "uiRegistry",
  'Magento_Ui/js/form/element/ui-select',
  'Oander_IstyleCheckout/js/model/store',
  'Oander_IstyleCheckout/js/view/billing-address/validate',
], function ($, ko, registry, uiSelect, store, validate) {
  'use strict';

  return uiSelect.extend({
    defaults: {
      billingKey: 'billingAddressshared.postcode',
    },

    initObservable: function () {
      this._super();

      this.value.equalityComparer = function (a, b) {
        return (!a && !b) || (a == b);
      };

      return this;
    },

    toggleOptionSelected: function () {
      this._super();
      var self = this;
      var currentLS = store.getLocalStorage();

      store.steps.active.subscribe(function (value) {
        if (value === 'billingAddress') self.validateHandler();
      }, this);

      if (currentLS.steps) {
        if (currentLS.steps.active === 'billingAddress') self.validateHandler();
      }

      return this;
    },

    validateHandler: function () {
      var key = this.billingKey;
      var fieldElement = $('[name="' + key + '"] .form-control');

      validate.requiredHandler(fieldElement, key);
    },

    /**
      * Toggle list visibility
      *
      * @returns {Object} Chainable
      */
    toggleListVisible: function (data, event) {
      var currentLS = store.getLocalStorage();

      this.listVisible(!this.listVisible());

      store.steps.active.subscribe(function (value) {
        if (value === 'shippingAddress') {
          $(this.cacheUiSelect).find('.oander-ui-control-text').focus();
        }

        if (value === 'billingAddress') {
          $('[name="' + this.billingKey +'"]').find('.oander-ui-control-text').focus();
        }
      }, this);

      if (currentLS.steps) {
        if (currentLS.steps.active === 'shippingAddress') {
          $(this.cacheUiSelect).find('.oander-ui-control-text').focus();
        }

        if (currentLS.steps.active === 'billingAddress') {
          $('[name="' + this.billingKey +'"]').find('.oander-ui-control-text').focus();
        }
      }

      return this;
    },
  })
});
