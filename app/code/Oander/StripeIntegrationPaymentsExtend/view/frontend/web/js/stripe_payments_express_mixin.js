define([
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function (wrapper, quote) {
    'use strict';

    return function (stripePaymentsExpress) {
        stripePaymentsExpress.estimateShippingCart = wrapper.wrapSuper(stripePaymentsExpress.estimateShippingCart, function (address, callback) {
            address.shippingMethod = quote.shippingMethod();
            this._super(address, callback);
        });

        return stripePaymentsExpress;
    };
});