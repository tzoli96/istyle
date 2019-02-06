/**
 * Braintre Apple Pay mini cart payment method integration.
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
define(
    [
        'Oander_ApplePay/js/button',
        'Oander_ApplePay/js/api',
        'mage/translate',
        'jquery',
        'domReady!'
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
                storeCode: "default"
            },
            details: null,

            /**
             * @returns {Object}
             */
            _init: function () {
                this._super();
                if (!this.options.displayName) {
                    this.options.displayName = $t('Store');
                }

                var details = this.requestQuoteDetails({'type' : 'quote'});

                return this;
            },

            addMiniCartButton: function (element) {
                console.log(this.details.id);
                var api = new buttonApi();
                api.setGrandTotalAmount(parseFloat(this.details.total).toFixed(2));
                api.setClientToken(this.options.clientToken);
                api.setDisplayName(this.options.displayName);
                api.setQuoteId(this.details.id);
                api.setActionSuccess(this.options.actionSuccess);
                api.setIsLoggedIn(this.details.isLoggedIn);
                api.setStoreCode(this.options.storeCode);

                // Attach the button
                button.init(
                    element,
                    api
                );
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
                    widget.details = response;
                });
            }
        });

        return $.mage.applepay;
    });
