define(
    [
        "jquery"
    ],
    function (
        $
    ) {

        'use strict';

        $.widget('mage.applepay', {
            options: {
                quoteURL: null,
                merchantId: null,
                countryCode: null,
                merchantCapabilities: [],
                supportedNetworks: [],
                clientToken: null,
                merchantName: null
            },
            request: {
                countryCode: 'CZ',
                currencyCode: 'CZK',
                merchantCapabilities: ['supports3DS'],
                /* shippingMethods: [
                     {
                         label: 'Free Standard Shipping',
                         amount: '0.00',
                         detail: 'Arrives in 5-7 days',
                         identifier: 'standardShipping'
                     },
                     {
                         label: 'Express Shipping',
                         amount: '1.00',
                         detail: 'Arrives in 2-3 days',
                         identifier: 'expressShipping'
                     }
                 ],*/
                //shippingType: ['shipping', 'storePickup'] ,
                supportedNetworks: ['visa', 'masterCard', 'amex', 'discover'],
                /* requiredBillingContactFields: [
                     'postalAddress',
                     'name',
                     'phoneticName'
                 ],
                 requiredShippingContactFields: [
                     'postalAddress',
                     'name',
                     'phone',
                     'email'
                 ],*/
                /*lineItems: [
                    {
                        label: 'Sales Tax',
                        amount: '0.00',
                        type: 'final'
                    },
                    {
                        label: 'Shipping',
                        amount: '1.99',
                        type: 'final'
                    }
                ],*/
                total: {
                    label: 'test',//this.options['merchantName'],
                    amount: '1.99',
                    type: 'final'
                }
            },

            /**
             * Widget initialization
             * @private
             */
            _create: function() {
                window.applePay = {};
                window.applePay.init = true;
                $(document).trigger('applePayTrigger');
            },

            canUseApplePay: function() {
                if (!window.ApplePaySession)
                {
                    console.log('AP - This device does not support Apple Pay');
                }
                else
                {
                    if (!ApplePaySession.canMakePayments()) {
                        console.log('AP - This device is not capable of making Apple Pay payments');
                    }
                    else
                    {
                        if(!ApplePaySession.canMakePaymentsWithActiveCard(this.options.merchantId))
                        {
                            console.log('AP - No active card in Wallet');
                        }
                        else
                        {
                            return true;
                        }
                    }
                }
                return false;
            },

            requestQuoteDetails: function (productid) {
                var url = this.options['quoteDetailsURL'];
                var data = [];
                $.ajax({
                    method: "POST",
                    url: url,
                    data: data
                }).done(function(response) {
                    console.log(response);
                });
                $.ajax()
            },

            /**
             * Retrieve the client token
             * @returns null|string
             */
            getClientToken: function () {
                return this.options['clientToken'];
            },

            /**
             * Payment request data
             */
            getPaymentRequest: function () {
                return this.request;
            },

            /**
             * Apple pay place order method
             */
            startPlaceOrder: function (nonce, event, session) {
                this.setPaymentMethodNonce(nonce);
                this.placeOrder();

                session.completePayment(ApplePaySession.STATUS_SUCCESS);
            },

            /**
             * Merchant display name
             */
            getDisplayName: function () {
                return this.options['merchantName'];
            },

            /**
             * Widget destroy functionality
             * @private
             */
            _destroy: function() {
                this._placeholder.remove();
                this._off($(window));
            }
        });

        return $.mage.applepay;
    });
