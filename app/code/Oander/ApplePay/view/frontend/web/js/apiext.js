/**
 * Braintre Apple Pay mini cart payment method integration.
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
define(
    [
        'jquery',
        'Oander_ApplePay/js/api',
        'mage/storage'
    ],
    function (
        $,
        buttonApi,
        storage
    ) {

        'use strict';

        return buttonApi.extend({

            extdefaults: {
                countryCode: null,
                currencyCode: null,
                quoteDetailsURL: null,
                merchantCapabilities: null,
                supportedNetworks: null
            },

            initialize: function () {
                this._super(); //_super will call parent's `initialize` method here

                return this;
            },

            requestQuoteDetails: function (data) {
                var url = this.extdefaults.quoteDetailsURL;
                var result = null;
                $.ajax({
                    method: "POST",
                    url: url,
                    data: data,
                    async: false
                }).done(function(response) {
                    result = response;
                });
                return result;
            },

            getPaymentRequest: function (element) {
                //Only to add to Cart button
                if (element.hasAttribute("data-productid")) {
                    var response = this.requestQuoteDetails({'type' : 'product', 'product' : $(element).data("productid")});
                    this.setGrandTotalAmount(parseFloat(response.total).toFixed(2));
                    this.setQuoteId(response.id);
                    this.setIsLoggedIn(response.isLoggedIn);
                }
                var paymentRequest = this._super();
                paymentRequest.merchantCapabilities = this.getMerchantCapabilities();
                paymentRequest.supportedNetworks = this.getSupportedNetworks();
                paymentRequest.currencyCode = this.getCurrencyCode();
                //paymentRequest.supportedCountries = ['CZ'];
                return paymentRequest;
            },

            onShippingContactSelect: function (event, session) {
                var component = this;
                console.log(session);
                var address = event.shippingContact;
                if(address.countryCode !== this.extdefaults.countryCode.toLowerCase())
                {
                    var totalsPayload = {
                        "addressInformation": {
                            "address": {
                                "countryId": this.extdefaults.countryCode,
                                "region": this.shippingAddress.region,
                                "regionId": this.getRegionId(this.extdefaults.countryCode, this.shippingAddress.region),
                                "postcode": this.shippingAddress.postcode
                            },
                            "shipping_method_code": "",
                            "shipping_carrier_code": ""
                        }
                    };
                    storage.post(
                        this.getApiUrl("totals-information"),
                        JSON.stringify(totalsPayload)
                    ).done(function (r) {
                        component.setGrandTotalAmount(r.base_grand_total);
                        // Pass shipping methods back
                        session.completeShippingContactSelection(
                            ApplePaySession.STATUS_INVALID_SHIPPING_POSTAL_ADDRESS,
                            [],
                            {
                                label: component.getDisplayName(),
                                amount: parseFloat(component.getGrandTotalAmount())
                            },
                            []
                        );
                    });
                }
                else
                {
                    this._super();
                }
            },

            /**
             * API Urls for logged in / guest
             */
            getApiUrl: function (uri) {
                if (this.getIsLoggedIn() === true) {
                    return "rest/" + this.getStoreCode() + "/V1/carts/mine/" + uri + '?isApplePay=true';
                } else {
                    return "rest/" + this.getStoreCode() + "/V1/guest-carts/" + this.getQuoteId() + "/" + uri + '?isApplePay=true';
                }
            },

            /**
             * Set and get Country ID
             */
            setCountryCode: function (value) {
                this.extdefaults.countryCode = value;
            },
            getCountryCode: function () {
                return this.extdefaults.countryCode;
            },
            /**
             * Set and get Currency CODE
             */
            setCurrencyCode: function (value) {
                this.extdefaults.currencyCode = value;
            },
            getCurrencyCode: function () {
                return this.extdefaults.currencyCode;
            },
            /**
             * Set and get Quote Details URL
             */
            setQuoteDetailsURL: function (value) {
                this.extdefaults.quoteDetailsURL = value;
            },
            getQuoteDetailsURL: function () {
                return this.extdefaults.quoteDetailsURL;
            },
            /**
             * Set and get Merchant Capabilities
             */
            setMerchantCapabilities: function (value) {
                this.extdefaults.merchantCapabilities = value;
            },
            getMerchantCapabilities: function () {
                return this.extdefaults.merchantCapabilities;
            },
            /**
             * Set and get Supported Networks
             */
            setSupportedNetworks: function (value) {
                this.extdefaults.supportedNetworks = value;
            },
            getSupportedNetworks: function () {
                return this.extdefaults.supportedNetworks;
            }
        });

    });
