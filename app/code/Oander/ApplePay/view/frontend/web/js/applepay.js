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
            defaults: {
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
            initialize: function () {
                this._super();
                if (!this.displayName) {
                    this.displayName = $t('Store');
                }

                var details = this.requestQuoteDetails({'type' : 'quote'});

                var api = new buttonApi();
                api.setGrandTotalAmount(parseFloat(this.grandTotalAmount).toFixed(2));
                api.setClientToken(this.clientToken);
                api.setDisplayName(this.displayName);
                api.setQuoteId(this.quoteId);
                api.setActionSuccess(this.actionSuccess);
                api.setIsLoggedIn(this.details.isLoggedIn);
                api.setStoreCode(this.storeCode);

                // Attach the button
                button.init(
                    document.getElementById(this.id),
                    api
                );

                return this;
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
                var url = this.quoteDetailsURL;
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
