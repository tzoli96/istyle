/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'uiComponent',
    'mage/translate',
    'applePay'
], function ($, Component, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Oander_ApplePay/form/element/checkoutapplepay'
        },
        selectors: {
            applepayObject: 'applepay-object'
        },
        divs: '<div id="addcheckout-apple-pay">' +
        '<div class="opc-wrapper">' +
        '<h2 class="step-title" data-role="title">' + $t('Pay wih one click') + '</h2>' +
        '<div id="addcheckout-apple-pay-button"></div>' +
        '<div data-role="title">' + $t('Applepay Checkout Description from Translation') + '</div>' +
        '</div>' +
        '<div class="separator opc-wrapper" data-role="title">' + $t('Or') + '</div>' +
        '</div>',

        addButton: function () {
            var component = this;
            var applePayObject = document.getElementById(this.selectors.applepayObject);
            var applePay = $(applePayObject).data('mageApplepay');
            if(typeof applePay !== 'undefined') {
                component.addCheckoutApplePayButton();
            }
            else {
                applePayObject.addEventListener("init", function() {component.addCheckoutApplePayButton()});
            }
            return false;
        },

        addCheckoutApplePayButton: function () {

            var applePayObject = document.getElementById(this.selectors.applepayObject);
            var applePay = $(applePayObject).data('mageApplepay');
            if (applePay.canUseApplePay('checkout')) {
                var outerdiv = $('#addcheckout-apple-pay');
                $('#checkoutSteps').parent().before($(this.divs));
                //outerdiv.insertBefore($('#checkoutSteps').parent());
                applePay.addCartButton(document.getElementById('addcheckout-apple-pay-button'));
            }
        }
    });
});
