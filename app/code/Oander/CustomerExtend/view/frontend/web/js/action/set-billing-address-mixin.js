define([
  'jquery',
  'mage/utils/wrapper',
  'Magento_Checkout/js/model/quote'
], function ($, wrapper,quote) {
  'use strict';

  return function (setBillingAddressAction) {
    return wrapper.wrap(setBillingAddressAction, function (originalAction, messageContainer) {

      var billingAddress = quote.billingAddress();

      if(typeof billingAddress !== 'undefined' && billingAddress) {

        if (typeof billingAddress['extension_attributes'] === 'undefined') {
          billingAddress['extension_attributes'] = {};
        }

        if (typeof billingAddress.customAttributes !== 'undefined') {
          $.each(billingAddress.customAttributes, function (key, value) {

            if($.isPlainObject(value)){
              value = value['value'];
            }

            billingAddress['extension_attributes'][key] = value;
          });
        }

      }

      return originalAction(messageContainer);
    });
  };
});