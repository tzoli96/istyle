/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'underscore',
    'mage/utils/wrapper'
], function ($, _, wrapper) {
    'use strict';

    return function (newCustomerAddress) {
        return wrapper.wrap(newCustomerAddress, function (originalAction, addressData) {
            var address = originalAction(addressData),
                customAttributes = {}
            ;

            // address.dob = addressData.dob;
            customAttributes.dob = addressData.dob;
            customAttributes.pfpj_reg_no = addressData.pfpj_reg_no;

            // for quote save handler
            address.extensionAttributes = $.extend(true, addressData.extensionAttributes || {}, customAttributes);
            // for frontend template to render
            address.customAttributes = $.extend(true, addressData.customAttributes || {}, customAttributes);

            return address;
        });
    };
});
