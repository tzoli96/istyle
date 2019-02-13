/**
 * Braintre Apple Pay mini cart payment method integration.
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
define(
    [
        'jquery',
        'Oander_ApplePay/js/api',
        'mage/storage',
        'mage/url',
        'mage/translate'
    ],
    function (
        $,
        buttonApi,
        storage,
        urlBuilder,
        $t
    ) {

        'use strict';

        return buttonApi.extend({

            extdefaults: {
                countryCode: null,
                currencyCode: null,
                quoteDetailsURL: null,
                merchantCapabilities: null,
                supportedNetworks: null,
                shippingAddress: null,
                billingAddress: null
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
                    this.setShippingAddress(response.shipping_address);
                    this.setBillingAddress(response.billing_address);
                }
                var paymentRequest = this._super();
                paymentRequest.merchantCapabilities = this.getMerchantCapabilities();
                paymentRequest.supportedNetworks = this.getSupportedNetworks();
                paymentRequest.currencyCode = this.getCurrencyCode();
                paymentRequest.countryCode = this.getCountryCode();
                var shippingAddress = this.getShippingAddress();
                if(shippingAddress)
                {
                    paymentRequest.shippingContact = this.transformAddress(shippingAddress);
                    var magentoShippingMethodsResponse = this.getShippingMethodsFromServer(shippingAddress);
                    var applePayShippingMethods = [];
                    var magentoShippingMethods = {};
                    if (magentoShippingMethodsResponse.length !== 0) {
                        var method = {};
                        // Format shipping methods array
                        for (var i = 0; i < magentoShippingMethodsResponse.length; i++) {
                            if (typeof magentoShippingMethodsResponse[i].method_code !== 'string') {
                                continue;
                            }
                            method = {
                                identifier: magentoShippingMethodsResponse[i].method_code,
                                label: magentoShippingMethodsResponse[i].method_title,
                                detail: magentoShippingMethodsResponse[i].carrier_title ? magentoShippingMethodsResponse[i].carrier_title : "",
                                amount: parseFloat(magentoShippingMethodsResponse[i].amount).toFixed(2)
                            };

                            applePayShippingMethods.push(method);
                            magentoShippingMethods[ magentoShippingMethodsResponse[i].method_code ] = magentoShippingMethodsResponse[i];
                        }

                        paymentRequest.shippingMethods = applePayShippingMethods;
                        this.setShippingMethods(magentoShippingMethods);
                        this.setShippingMethod(applePayShippingMethods[0].identifier);

                        paymentRequest.total.amount = parseFloat(this.getGrandTotalAmount() + parseFloat(applePayShippingMethods[0].amount)).toFixed(2);
                        paymentRequest.lineItems =  [{
                            type: 'final',
                            label: $t('Shipping'),
                            amount: applePayShippingMethods[0].amount
                        }];
                    }
                }
                var billingAddress = this.getBillingAddress();
                if(billingAddress)
                {
                    paymentRequest.billingContact = this.transformAddress(billingAddress);
                }
                //paymentRequest.supportedCountries = ['CZ'];

                return paymentRequest;
            },

            onShippingContactSelect: function (event, session) {
                var component = this;
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
               /* if (this.getIsLoggedIn() === true) {
                    return "rest/" + this.getStoreCode() + "/V1/carts/mine/" + uri + '?isApplePay=true&forcedActive=true';
                } else {*/
                    return "rest/" + this.getStoreCode() + "/V1/guest-carts/" + this.getQuoteId() + "/" + uri + '?isApplePay=true&forcedActive=true';
              //  }
            },

            transformAddress: function (magentoAddress) {
                return {"emailAddress": magentoAddress.email,
                    "phoneNumber": magentoAddress.telephone,
                    "givenName": magentoAddress.firstname,
                    "familyName": magentoAddress.lastname,
                    "addressLines": magentoAddress.street,
                    "locality": magentoAddress.city,
                    "administrativeArea": magentoAddress.region,
                    "countryCode": magentoAddress.country_id.toLowerCase(),
                    "postalCode": magentoAddress.postcode};
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
            },
            /**
             * Set and get Supported Networks
             */
            setShippingAddress: function (value) {
                this.extdefaults.shippingAddress = value;
            },
            getShippingAddress: function () {
                return this.extdefaults.shippingAddress;
            },
            /**
             * Set and get Supported Networks
             */
            setBillingAddress: function (value) {
                this.extdefaults.billingAddress = value;
            },
            getBillingAddress: function () {
                return this.extdefaults.billingAddress;
            },

            getShippingMethodsFromServer: function (shippingAddress) {
                var shippingMethods = [];
                var payload = {
                    address: {
                        city: null,//shippingAddress.city,
                        region: null,//shippingAddress.region,
                        country_id: shippingAddress.country_id,
                        postcode: null,//shippingAddress.postcode,
                        save_in_address_book :0
                    }
                };

                // Retrieve shipping methods
                $.ajax({
                    type: "POST",
                    url: urlBuilder.build(this.getApiUrl("estimate-shipping-methods")),
                    data: JSON.stringify(payload),
                    async: false,
                    global: true,
                    contentType: 'application/json'
                }).done(function (response) {
                    shippingMethods = response;
                });

                return shippingMethods;
            }
        });

    });
