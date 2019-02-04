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
                //generalconfig
                quoteDetailsURL: null,
                version: null,
                countryCode: null,
                languageCode: null,
                currencyCode: null,
                clientToken: null,
                //Paymentconfigs
                merchantName: null,
                merchantId: null,
                merchantCapabilities: [],
                supportedNetworks: []
            },
            request: {
                countryCode: null,
                currencyCode: null,
                merchantCapabilities: null,//['supports3DS'],
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
                supportedNetworks: null,//['visa', 'masterCard', 'amex', 'discover'],
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
                this.request.merchantCapabilities = this.options.merchantCapabilities;
                this.request.supportedNetworks = this.options.supportedNetworks;
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

            requestQuoteDetails: function (data) {
                var widget = this;
                var url = this.options.quoteDetailsURL;
                $.ajax({
                    method: "POST",
                    url: url,
                    data: data,
                    async: false
                }).done(function(response) {
                    widget.request.countryCode = widget.options.countryCode;
                    widget.request.currencyCode = widget.options.currencyCode;
                    widget.request.merchantCapabilities = widget.options.merchantCapabilities;
                    widget.request.supportedNetworks = widget.options.supportedNetworks;
                    widget.request['total'] = response['total'];
                });
            },

            /**
             * Retrieve the client token
             * @returns null|string
             */
            getClientToken: function () {
                return this.options.clientToken;
            },

            /**
             * Payment request data
             */
            getPaymentRequest: function (element) {
                if (element.hasAttribute("data-productid")) {
                    this.requestQuoteDetails({'type' : 'product', 'product' : $(element).data("productid")})
                }
                console.log(this.request);
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
                return this.options.merchantName;
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