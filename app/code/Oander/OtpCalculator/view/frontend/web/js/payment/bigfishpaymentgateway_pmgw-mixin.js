define([], function () {
  'use strict';

  return function (BigfishPaymentGateway) {
    return BigfishPaymentGateway.extend({

      isCalculatorEnabled: function () {
        return this.getProviderObj().calculator_enabled;
      },

      getIframeURL: function () {
        var url = 'https://aruhitel.otpbank.hu/webshop/webshop-calculator.html';
        var grandTotal = Math.round(window.checkoutConfig.quoteData.base_grand_total);
        var provider = this.getProviderObj();
        // "bigfishpaymentgateway_pmgw_otparuhitel"

        //https://aruhitel.otpbank.hu/webshop/webshop-calculator.html?purchasePrice=389900&constructionGroup=1000071&retailerId=1003692

        return url += '' +
          '?purchasePrice=' + grandTotal + '' +
          '&constructionGroup=' + provider.construction_group + '' +
          '&retailerId=' + provider.retailer_id + '' +
          '&term=' + provider.term;
      },

      getProviderObj: function () {
        var providers = window.checkoutConfig.payment.bigfishpaymentgateway_pmgw.providers;
        for (var i = 0; i < providers.length; i++) {
          if (providers[i].name === 'bigfishpaymentgateway_pmgw_otparuhitel') {
            return providers[i];
          }
        }
      }
    });
  }
});
