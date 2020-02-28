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
                    if (typeof shippingAddress.customAttributes['dob'] === 'string') {
                        shippingAddress['extension_attributes']['dob'] = shippingAddress.customAttributes['dob'];
                    } else if (typeof shippingAddress.customAttributes['dob'].value !== 'undefined') {
                        shippingAddress['extension_attributes']['dob'] = shippingAddress.customAttributes['dob'].value;
                    }
                }


                if (typeof shippingAddress.customAttributes['pfpj_reg_no'] !== 'undefined') {
                    if (typeof shippingAddress.customAttributes['pfpj_reg_no'] === 'string') {
                        shippingAddress['extension_attributes']['pfpj_reg_no'] = shippingAddress.customAttributes['pfpj_reg_no'];
                    } else if (typeof shippingAddress.customAttributes['pfpj_reg_no'].value !== 'undefined') {
                        shippingAddress['extension_attributes']['pfpj_reg_no'] = shippingAddress.customAttributes['pfpj_reg_no'].value;
                    }
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