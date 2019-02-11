/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'uiComponent',
    'applePay'
], function ($, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Oander_ApplePay/form/element/checkoutapplepay'
        },
        selectors: {
            applepayObject: 'applepay-object'
        },
        addButton: function () {
            if (document.getElementById('addcheckout-apple-pay-button').innerHTML === '') {
                $('#addcheckout-apple-pay').insertBefore($('#checkoutSteps').parent());
                var component = this;
                var applePayObject = document.getElementById(this.selectors.applepayObject);
                var applePay = $(applePayObject).data('mageApplepay');
                if(typeof applePay !== 'undefined') {
                    component.addCheckoutApplePayButton();
                }
                else {
                    applePayObject.addEventListener("init", function() {component.addCheckoutApplePayButton()});
                }
            }
            return false;
        },

        addCheckoutApplePayButton: function () {
            var applePayObject = document.getElementById(this.selectors.applepayObject);
            var applePay = $(applePayObject).data('mageApplepay');
            if (applePay.canUseApplePay('checkout')) {
                applePay.addCartButton(document.getElementById('addcheckout-apple-pay-button'));
            }
        }
    });
});
