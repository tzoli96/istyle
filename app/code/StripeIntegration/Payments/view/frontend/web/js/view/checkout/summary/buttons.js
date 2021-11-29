define(
  [
    'StripeIntegration_Payments/js/view/payment/apple_pay',
  ],
  function (stripeApplePay) {
    'use strict';

    return stripeApplePay.extend({
      defaults: {
        template: 'StripeIntegration_Payments/checkout/summary/buttons',
      },
    });
  }
);
