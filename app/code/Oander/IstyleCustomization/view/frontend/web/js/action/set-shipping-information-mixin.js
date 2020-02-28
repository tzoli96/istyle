define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer'
], function ($, wrapper, quote,customer) {
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

            if (typeof shippingAddress['extension_attributes']['dob'] == 'undefined'
                && typeof customer.customerData !== 'undefined'
                && typeof customer.customerData.dob !== 'undefined'
            ) {
                shippingAddress['extension_attributes']['dob'] = customer.customerData.dob;
            }

            return originalAction();
        });
    };
});