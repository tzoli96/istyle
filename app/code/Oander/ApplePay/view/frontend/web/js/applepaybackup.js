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
                placeOrderURL: null,
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
            shippingMethod: null,
            ajaxResponse: null,
            baseRequest: {
                countryCode: null,
                currencyCode: null,
                merchantCapabilities: null,
                supportedNetworks: null,
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
                    widget.ajaxResponse = response;
                    widget.ajaxResponse.applepaydata.total.label = widget.getDisplayName();
                });
            },

            placeOrder: function (data, session) {
                var widget = this;
                var url = this.options.placeOrderURL;
                $.ajax({
                    method: "POST",
                    url: url,
                    data: data
                }).done(function(response) {
                    session.completePayment(session.STATUS_SUCCESS);
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
                var result = $.extend(this.baseRequest, this.ajaxResponse['applepaydata']);
                result.total = this._getOriginalTotal();
                // Add Selected Shipping method as Item and add shipping amount to total
                if('shippingMethods' in result)
                {
                    if(Array.isArray(result.shippingMethods)) {
                        if (result.shippingMethods.length>0) {
                            result.lineItems =
                                [
                                    {
                                        label: result.shippingMethods[0].label,
                                        amount: result.shippingMethods[0].amount,
                                        type: 'final'
                                    }
                                ];
                            result.total.amount = result.total.amount + result.shippingMethods[0].amount;
                        }
                    }
                }
                return result;
            },

            /**
             * change Total on ShippingSelection
             */
            onShippingMethodSelect: function (event, session) {
                var shippingMethod = event.shippingMethod;
                this.shippingMethod = shippingMethod.identifier;

                var newTotal = null;
                var update = {};

                update.newLineItems = [{
                    label: event.shippingMethod.label,
                    amount: event.shippingMethod.amount,
                    type: 'final'
                }];
                update.newTotal = this._getOriginalTotal();
                update.newTotal.amount = update.newTotal.amount + parseFloat(event.shippingMethod.amount);
                session.completeShippingMethodSelection(update);
            },

            /**
             * Apple pay place order method
             */
            startPlaceOrder: function (nonce, event, session) {
                event.quoteid = this.ajaxResponse.id;
                this.placeOrder(event,session);
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