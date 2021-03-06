/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'jquery',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/action/place-order',
        'Magento_Customer/js/customer-data',
    ],
    function (Component, ko, $, validators, url, quote, totals, priceUtils, placeOrderAction, customerData) {
        'use strict';

        return Component.extend({
            totals: quote.getTotals(),

            defaults: {
                template: "Oander_RaiffeisenPayment/payment/raiffeisen-payment-method",
            },

            initialize: function () {
                this._super();
                return this;
            },

            initObservable: function () {
                this._super();
                return this;
            },

            getCode: function () {
                return 'raiffeisen';
            },

            getData: function () {
                var data = {
                    'method': this.item.method,
                };
                return data;
            },
            getInstructions: function () {
                return window.checkoutConfig.payment.raiffeisen.instructions;
            },

            getEligibilityQuestions: function () {
                var questions = (window.checkoutConfig.payment.raiffeisen.eligibilityquestions) ? JSON.parse(window.checkoutConfig.payment.raiffeisen.eligibilityquestions) : "";
                var self = this;

                self.data = ko.observableArray([
                    questions
                ]);
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
             * @override
             */
            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }
                var self = this,
                    placeOrder = placeOrderAction(this.getData(), false);

                    $.when(placeOrder).fail(function () {
                    }).done(this.afterPlaceOrder.bind(this));
                    return true;

                return false;
            },
            afterPlaceOrder: function () {
                window.location.replace(url.build('raiffeisen/payment/redirect/'));
            },
        });
    }
);
