/**
 * Braintre Apple Pay mini cart payment method integration.
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
define(
    [
        'uiComponent',
        'Oander_ApplePay/js/button',
        'Oander_ApplePay/js/api',
        'Magento_Customer/js/model/customer',
        'mage/translate',
        'domReady!'
    ],
    function (
        Component,
        button,
        buttonApi,
        customer,
        $t
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

            /**
             * @returns {Object}
             */
            initialize: function () {
                this._super();
                if (!this.displayName) {
                    this.displayName = $t('Store');
                }

                var api = new buttonApi();
                api.setGrandTotalAmount(parseFloat(this.grandTotalAmount).toFixed(2));
                api.setClientToken(this.clientToken);
                api.setDisplayName(this.displayName);
                api.setQuoteId(this.quoteId);
                api.setActionSuccess(this.actionSuccess);
                api.setIsLoggedIn(customer.isLoggedIn);
                api.setStoreCode(this.storeCode);

                // Attach the button
                button.init(
                    document.getElementById(this.id),
                    api
                );

                return this;
            }
        });
    }
);
