/**
 * Braintre Apple Pay mini cart payment method integration.
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
define(
    [
        'uiComponent',
        'Oander_ApplePay/js/button',
        'Oander_ApplePay/js/api',
        'mage/translate',
        'jquery',
        'domReady!'
    ],
    function (
        Component,
        button,
        buttonApi,
        $t,
        $
    ) {
        'use strict';

        return Component.extend({

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
    }
);
