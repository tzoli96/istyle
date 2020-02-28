define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();
            if (typeof shippingAddress['extension_attributes'] === 'undefined') {
                shippingAddress['extension_attributes'] = {};
            }

            if (typeof shippingAddress.customAttributes !== 'undefined') {
                if (typeof shippingAddress.customAttributes['dob'] !== 'undefined') {
                    shippingAddress['extension_attributes']['dob'] = shippingAddress.customAttributes['dob'];
                }
                if (typeof shippingAddress.customAttributes['pfpj_reg_no'] !== 'undefined') {
                    shippingAddress['extension_attributes']['pfpj_reg_no'] = shippingAddress.customAttributes['pfpj_reg_no'];
                }
            }

            return originalAction();
        });
    };
});