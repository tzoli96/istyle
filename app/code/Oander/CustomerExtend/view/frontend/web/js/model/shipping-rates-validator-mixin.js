define([
  'mage/utils/wrapper'
], function (wrapper) {
  'use strict';

  var postcodeElementName = 'postcode';

  return function (shippingRatesValidator) {
    shippingRatesValidator.doElementBinding = wrapper.wrapSuper(shippingRatesValidator.doElementBinding, function (element, force, delay) {
      if (window.checkoutConfig.disablePostcodeWarning && element.index === postcodeElementName) {
        return;
      }

      this._super(element, force, delay);
    });

    return shippingRatesValidator;
  };
});
