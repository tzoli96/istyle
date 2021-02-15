/**
 * Copyright ©2019 Itegration Ltd., Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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

            secureCode: ko.observable(''),

            initialize: function () {
                this._super();
                return this;
            },
            defaults: {
                template: "Oander_HelloBankPayment/payment/hellobank-payment-method"
            },

            getCode: function () {
                return 'hellobank';
            },

            getData: function () {
                var data = {
                    'method': this.item.method,
                    'additional_data': {}
                };
                return data;
            },

            getPaymentLogoSrc: function () {
               'asd';
            },

            getPaymentLogoLink: function () {
                'test';
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