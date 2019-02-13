/**
 * Braintre Apple Pay mini cart payment method integration.
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
define(
    [
        'Oander_ApplePay/js/button',
        'Oander_ApplePay/js/apiext',
        'mage/translate',
        'jquery',
        'domReady!',
        'loader'
    ],
    function (
        button,
        buttonApi,
        $t,
        $
    ) {

        'use strict';

        $.widget('mage.applepay', {
            options: {
                quoteDetailsURL: null,
                clientToken: null,
                displayName: null,
                actionSuccess: null,
                storeCode: "default",
                countryCode: null,
                currencyCode: null,
                merchantCapabilities: null,
                supportedNetworks: null,
                displayIn: []
            },

            selectors: {
                minicart: '[data-block="minicart"]',
                checkoutbutton: '#top-cart-btn-checkout',
                applepayObject: '#applepay-object',
                originallink: '#apple-pay-original-link'
            },

            _create: function() {
                var event = document.createEvent('Event');
                event.initEvent('init', true, true);
                document.getElementById('applepay-object').dispatchEvent(event);
            },

            /**
             * @returns {Object}
             */
            _init: function () {
                this._super();
                if (!this.options.displayName) {
                    this.options.displayName = $t('Store');
                }
                return this;
            },

            addProductCartButton: function (element) {
                var api = new buttonApi();
                api.setQuoteDetailsURL(this.options.quoteDetailsURL);
                api.setClientToken(this.options.clientToken);
                api.setDisplayName(this.options.displayName);
                api.setActionSuccess(this.options.actionSuccess+'?clearQuote=false');
                api.setStoreCode(this.options.storeCode);
                api.setCountryCode(this.options.countryCode);
                api.setCurrencyCode(this.options.currencyCode);
                api.setSupportedNetworks(this.options.supportedNetworks);
                api.setMerchantCapabilities(this.options.merchantCapabilities);

                // Attach the button
                button.init(
                    element,
                    api
                );
            },

            addCartButton: function (element) {
                var api = new buttonApi();
                api.setQuoteDetailsURL(this.options.quoteDetailsURL);
                var response = api.requestQuoteDetails({'type' : 'quote'});
                api.setGrandTotalAmount(parseFloat(response.total).toFixed(2));
                api.setClientToken(this.options.clientToken);
                api.setDisplayName(this.options.displayName);
                api.setQuoteId(response.id);
                api.setActionSuccess(this.options.actionSuccess);
                api.setIsLoggedIn(response.isLoggedIn);
                api.setStoreCode(this.options.storeCode);
                api.setCountryCode(this.options.countryCode);
                api.setCurrencyCode(this.options.currencyCode);
                api.setSupportedNetworks(this.options.supportedNetworks);
                api.setMerchantCapabilities(this.options.merchantCapabilities);
                api.setShippingAddress(response.shipping_address);
                api.setBillingAddress(response.billing_address);

                // Attach the button
                button.init(
                    element,
                    api
                );
            },

            canUseApplePay: function(type) {
                if(this.options.displayIn.indexOf(type) > -1) {
                    if (!window.ApplePaySession) {
                        console.log('AP - This device does not support Apple Pay');
                    }
                    else {
                        if (!ApplePaySession.canMakePayments()) {
                            console.log('AP - This device is not capable of making Apple Pay payments');
                        }
                        else {
                            if (!ApplePaySession.canMakePaymentsWithActiveCard(this.options.merchant_id)) {
                                console.log('AP - No active card in Wallet');
                            }
                            else {
                                return true;
                            }
                        }
                    }
                }
                return false;
            }
        });

        return $.mage.applepay;
    });
