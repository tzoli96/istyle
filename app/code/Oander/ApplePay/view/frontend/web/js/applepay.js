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
                merchant_name: null,
                merchant_id: null,
                merchant_capabilities: [],
                supported_networks: []
            },
            ajaxResponse: null,
            baseRequest: {
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
                supportedNetworks: null, //['visa', 'masterCard', 'amex', 'discover'],
                requiredBillingContactFields: [
                    'phone',
                    'email',
                    'name',
                    'postalAddress'
                ],
                requiredShippingContactFields: [
                    'phone',
                    'email',
                    'name',
                    'postalAddress'
                ]
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
                 ],
                 total: {
                 label: 'test',//this.options['merchantName'],
                 amount: '1.99',
                 type: 'final'
                 }*/
            },

            /**
             * Widget initialization
             * @private
             */
            _create: function() {
                window.applePay = {};
                window.applePay.init = true;
                $(document).trigger('applePayTrigger');
                this.baseRequest.countryCode = this.options.countryCode;
                this.baseRequest.currencyCode = this.options.currencyCode;
                this.baseRequest.merchantCapabilities = this.options.merchant_capabilities;
                this.baseRequest.supportedNetworks = this.options.supported_networks;
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
                        if(!ApplePaySession.canMakePaymentsWithActiveCard(this.options.merchant_id))
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
                    console.log(response);
                    widget.ajaxResponse = response;
                    widget.ajaxResponse.total.label = widget.options.merchant_name;
                    widget._updateTotal();
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
                this.ajaxResponse = {};
                if (element.hasAttribute("data-productid")) {
                    this.requestQuoteDetails({'type' : 'product', 'product' : $(element).data("productid")})
                }
                else
                {
                    this.requestQuoteDetails({'type' : 'quote'})
                }
                return $.extend(this.baseRequest, this.ajaxResponse['applepaydata']);
            },

            /**
             * change Total on ShippingSelection
             */
            onShippingMethodSelect: function (event, session) {
                console.log(event);
                var newTotal = null;
                var update = {};

                update.newLineItems = [{
                    label: event.shippingMethod.label,
                    amount: event.shippingMethod.amount,
                    type: 'final'
                }];
                console.log(this._getOriginalTotal());
                update.newTotal = this._getOriginalTotal();
                update.newTotal.amount = update.newTotal.amount + parseFloat(event.shippingMethod.amount);
                console.log(update);
                session.completeShippingMethodSelection(update);
            },

            /**
             * Apple pay place order method
             */
            startPlaceOrder: function (nonce, event, session) {
                this.setPaymentMethodNonce(nonce);
                this.placeOrder();

                session.completePayment(session.STATUS_SUCCESS);
            },

            /**
             * Merchant display name
             */
            getDisplayName: function () {
                return this.options.merchant_name;
            },

            /**
             * Widget destroy functionality
             * @private
             */
            _destroy: function() {
                this._placeholder.remove();
                this._off($(window));
            },

            /**
             * Internal get original total with modified label
             */
            _getOriginalTotal: function() {
                return $.extend({},this.ajaxResponse.applepaydata.total);
            }
        });

        return $.mage.applepay;
    });