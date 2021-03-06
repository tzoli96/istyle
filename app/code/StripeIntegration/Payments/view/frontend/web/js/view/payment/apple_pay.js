define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'StripeIntegration_Payments/js/view/payment/method-renderer/stripe_payments',
        'stripe_payments_express',
        'Magento_CheckoutAgreements/js/model/agreement-validator',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'mage/translate',
        'Magento_Ui/js/model/messageList'
    ],
    function (
        ko,
        $,
        Component,
        paymentMethod,
        stripeExpress,
        agreementValidator,
        selectPaymentMethod,
        checkoutData,
        quote,
        $t,
        globalMessageList
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                // template: 'StripeIntegration_Payments/payment/apple_pay_top',
                stripePaymentsShowApplePaySection: false,
                isPRAPIrendered: false,
                isTotalsCalculated: false
            },

            firstLoad: ko.observable(false),

            initObservable: function ()
            {
                this._super()
                    .observe([
                        'stripePaymentsStripeJsToken',
                        'stripePaymentsShowApplePaySection',
                        'isPaymentRequestAPISupported'
                    ]);

                var self = this;

                stripeExpress.onPaymentSupportedCallbacks.push(function()
                {
                    self.isPaymentRequestAPISupported(true);
                    self.stripePaymentsShowApplePaySection(true);
                });

                this.displayAtThisLocation = ko.computed(function()
                {
                    return false;
                }, this);

                var currentTotals = quote.totals();

                setTimeout(function () {
                    self.firstLoad(false);
                }, 2000);

                quote.totals.subscribe(function (totals)
                    {
                        if (JSON.stringify(totals.total_segments) == JSON.stringify(currentTotals.total_segments))
                            return;

                        currentTotals = totals;

                        if (!self.isPRAPIrendered)
                            return;

                        // Wait for Magento to commit the changes before re-initializing the PRAPI
                        setTimeout(function()
                        {
                            if (!self.firstLoad()) {
                                self.initPRAPI();
                                self.firstLoad(true);
                                self.isTotalsCalculated = true;
                            }
                        });
                    }
                    , this);

                quote.paymentMethod.subscribe(function(method)
                    {
                        if (method != null)
                        {
                            $(".stripe-payments.mobile").removeClass("_active");
                        }
                    }
                    , null, 'change');

                return this;
            },

            markPRAPIready: function()
            {
                this.isPRAPIrendered = true;

                if (this.isTotalsCalculated)
                    this.initPRAPI();
                else
                    return;
            },

            initPRAPI: function()
            {
                if (!this.config().isApplePayEnabled)
                    return;

                if (this.config().applePayLocation != 2)
                    return;

                var self = this;
                var params = self.config().initParams;
                stripeExpress.initStripeExpress('#payment-request-button', params, 'checkout', self.config().prapiButtonConfig,
                    function (paymentRequestButton, paymentRequest, params, prButton) {
                        stripeExpress.initCheckoutWidget(paymentRequestButton, paymentRequest, prButton, self.beginApplePay.bind(self));
                    }
                );
            },

            prapiTitle: function()
            {
                return this.config().prapiTitle;
            },

            showApplePaySection: function()
            {
                return this.isPaymentRequestAPISupported;
            },

            config: function()
            {
                return window.checkoutConfig.payment['stripe_payments'];
            },

            beginApplePay: function(ev)
            {
                this.makeActive();
                if (!this.validate())
                {
                    ev.preventDefault();
                }
            },

            makeActive: function()
            {
                if (!this.displayAtThisLocation())
                    return;

                // If there are any selected payment methods from a different section, make them inactive
                // This ensures that their form validations will not run
                try
                {
                    selectPaymentMethod(null);
                }
                catch (e) {}

                // We do want terms & conditions validation for Apple Pay, so activate that temporarily
                $(".stripe-payments.mobile").addClass("_active");
            },

            validate: function(region)
            {
                if (agreementValidator.validate())
                    return true;

                if (!agreementValidator.validate())
                    this.showError($t("Please agree to the terms and conditions before placing the order."));
                else
                    this.showError($t("Please complete all required fields before placing the order."));

                return false;
            },

            showError: function(message)
            {
                document.getElementById('checkout').scrollIntoView(true);
                globalMessageList.addErrorMessage({ "message": message });
            }
        });
    }
);
