define(
  [
    'ko',
    'jquery',
    'StripeIntegration_Payments/js/view/payment/apple_pay',
    'StripeIntegration_Payments/js/view/payment/method-renderer/stripe_payments',
    'stripe_payments_express',
    'Magento_Checkout/js/model/quote',
  ],
  function (ko, $, stripeApplePay, paymentMethod, stripeExpress, quote) {
    'use strict';

    return stripeApplePay.extend({
      defaults: {
        template: 'StripeIntegration_Payments/checkout/summary/buttons',
      },

      initObservable: function () {
        this._super()
          .observe([
            'stripePaymentsStripeJsToken',
            'stripePaymentsShowApplePaySection',
            'isPaymentRequestAPISupported'
          ]);

        var self = this;

        stripeExpress.onPaymentSupportedCallbacks.push(function () {
          self.isPaymentRequestAPISupported(true);
          self.stripePaymentsShowApplePaySection(true);
        });

        this.displayAtThisLocation = ko.computed(function () {
          return paymentMethod.prototype.config().applePayLocation == 2;
        }, this);

        var currentTotals = quote.totals();

        quote.totals.subscribe(function (totals) {
          if (JSON.stringify(totals.total_segments) == JSON.stringify(currentTotals.total_segments))
            return;

          currentTotals = totals;

          if (!self.isPRAPIrendered)
            return;

          // Wait for Magento to commit the changes before re-initializing the PRAPI
          setTimeout(function () {
            self.isTotalsCalculated = true;
            self.initPRAPI();
          });
        }
          , this);

        quote.paymentMethod.subscribe(function (method) {
          if (method != null) {
            $(".stripe-payments.mobile").removeClass("_active");
          }
        }
          , null, 'change');

        return this;
      },
    });
  }
);
