define(
    [
        "jquery",
        'mage/translate',
        'Oander_ApplePay/js/button'
    ],
    function (
        $,
        $t,
        button
    ) {

    'use strict';

    $.widget('mage.applepay', {
        options: {
            quoteURL: null,
            merchantId: null,
            countryCode: null,
            merchantCapabilities: [],
            supportedNetworks: [],
            clientToken: null,
            merchantName: null
        },
        request: {
            countryCode: 'CZ',
            currencyCode: 'CZK',
            merchantCapabilities: ['supports3DS'],
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
            supportedNetworks: ['visa', 'masterCard', 'amex', 'discover'],
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
            console.dir(this.options);

            $(document).trigger('applePayTrigger');

            //console.dir(this.options);
            var applePay = this;
            $(document).on("click", '.applepaybutton', function(event) {
                applePay.createPaymentRequest($(this), applePay);
            });
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

        requestQuoteDetails: function (productid) {
            var url = this.options['quoteDetailsURL'];
            var data = [];
            $.ajax({
                method: "POST",
                url: url,
                data: data
            }).done(function(response) {
                console.log(response);
            });
            $.ajax()
        },

        createPaymentRequest: function (element,widget) {
            button.init(
                element,//document.getElementById(id),
                widget
            );

            /**
            var paySession = new ApplePaySession(3, this.request);
            paySession.begin();

            paySession.onvalidatemerchant = function (event) {
                var validationData = { validationUrl: event.validationURL };
                console.dir(validationData);
                $.ajax({
                    url: '/applepay/ajax/payment',
                    method: "POST",
                    contentType: "application/json; charset=utf-8",
                    data: JSON.stringify(validationData)
                }).then(function (merchantSession) {
                    console.dir(merchantSession);
                    paySession.completeMerchantValidation(merchantSession);
                    alert("end = " + window.location.host);
                }, function (error) {
                    alert("merchant validation unsuccessful: " + JSON.stringify(error));
                    paySession.abort();
                });
            };*/

            // onvalidatemerchant: A callback function that is automatically called when the payment sheet is displayed.
         /*   paySession.onvalidatemerchant = function (event) {
                var promise = performValidation(event.validationURL);
                promise.then(function (merchantSession) {
                    paySession.completeMerchantValidation(merchantSession);
                });
            }

            paySession.onpaymentauthorized = function (event) {
                var promise = sendPaymentToken(event.payment.token, paymentRequest);
                promise.then(function (success) {
                    var status;
                    if (success){
                        applePayPane.hide();
                        successDiv.show();
                        status = ApplePaySession.STATUS_SUCCESS;
                        paySession.completePayment(status);
                        window.location = approvalURL;
                    }
                }, function (errorDecline) {
                    resultDiv.text(errorDecline);
                    resultDiv.show();
                    status = ApplePaySession.STATUS_FAILURE;
                    paySession.completePayment(status);
                });
            }*/
        },

        /**
         * Retrieve the client token
         * @returns null|string
         */
        getClientToken: function () {
            return this.options['clientToken'];
        },

        /**
         * Payment request data
         */
        getPaymentRequest: function () {
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
            return this.options['merchantName'];
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
