/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'jquery',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url'
    ],
    function (Component, ko, $, redirectOnSuccessAction, validators, url) {
        'use strict';

        return Component.extend({

            initialize: function () {
                this._super();
                return this;
            },
            defaults: {
                template: "Oander_HelloBankPayment/payment/hellobank-payment-method",
                response: ''
            },
            initObservable: function () {

                this._super()
                    .observe([
                        'response'
                    ]);
                return this;
            },

            getCode: function () {
                return 'hellobank';
            },

            getData: function () {
                var data = {
                    'method': this.item.method,
                    'additional_data': {
                        'response': this.response()
                    }
                };
                return data;
            },

            getPaymentLogoSrc: function () {
                return window.checkoutConfig.payment.hellobank.logoSrc;
            },

            validate: function () {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },

            checkForm: function () {
                if (this.validate() && validators.validate()) {
                    this.placeOrder();
                }
            },
            getResponse: function() {
                return _.map(window.checkoutConfig.payment.hellobank.response, function(value, key) {
                    return {
                        'value': key,
                        'response': value
                    }
                });
            },
            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                redirectOnSuccessAction.redirectUrl = url.build('hellobank/payment/redirect/');
                this.redirectAfterPlaceOrder = true;
            }
        });
    }
);