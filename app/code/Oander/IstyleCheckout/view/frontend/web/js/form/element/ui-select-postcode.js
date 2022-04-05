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
      var key = 'billingAddressshared.postcode';
      var fieldElement = $('[name="' + key + '"] .form-control');

      validate.requiredHandler(fieldElement, key);
    },
  })
});
