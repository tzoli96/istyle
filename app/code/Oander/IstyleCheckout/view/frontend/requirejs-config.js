var config = {
  map: {
    '*': {
      oanderIstyleCheckout: 'Oander_IstyleCheckout/js/checkout',
      leaflet: 'Oander_IstyleCheckout/js/leaflet',
      'Magento_Checkout/template/progress-bar.html':
        'Oander_IstyleCheckout/template/progress-bar.html',
      'Magento_Checkout/template/registration.html':
        'Oander_IstyleCheckout/template/registration.html',
      'Magento_Checkout/template/shipping.html':
        'Oander_IstyleCheckout/template/shipping.html',
      'Magento_Checkout/template/billing-address.html':
        'Oander_IstyleCheckout/template/billing-address.html',
      'Magento_Checkout/template/form/element/email.html':
        'Oander_IstyleCheckout/template/form/element/email.html',
      'Magento_Checkout/template/payment.html':
        'Oander_IstyleCheckout/template/payment.html',
      'Magento_Checkout/template/sidebar.html':
        'Oander_IstyleCheckout/template/sidebar.html',
      'Magento_Checkout/template/cart/totals.html':
        'Oander_IstyleCheckout/template/checkout/cart/totals.html',
      'Magento_Tax/template/checkout/cart/totals/grand-total.html':
        'Oander_IstyleCheckout/template/checkout/cart/totals/grand-total.html',
      'Magento_SalesRule/template/cart/totals/discount.html':
        'Oander_IstyleCheckout/template/checkout/cart/totals/discount.html',
      'Magento_Checkout/template/shipping-address/address-renderer/default.html':
        'Oander_IstyleCheckout/template/shipping-address/address-renderer/default.html',
      'Magento_Checkout/template/shipping-address/list.html':
        'Oander_IstyleCheckout/template/shipping-address/list.html',
      'Magento_Checkout/js/view/form/element/email':
        'Oander_IstyleCheckout/js/view/form/element/email',
      'Magento_Checkout/template/billing-address/form':
        'Oander_IstyleCheckout/template/billing-address/form',
      'Magento_Checkout/template/billing-address/list':
        'Oander_IstyleCheckout/template/billing-address/list',
      'Magento_Checkout/js/view/shipping-address/address-renderer/default':
        'Oander_IstyleCheckout/js/view/shipping-address/address-renderer/default',
      'Magento_Customer/js/action/check-email-availability':
        'Oander_IstyleCheckout/js/action/check-email-availability',
      'Magento_Customer/js/action/login':
        'Oander_IstyleCheckout/js/action/login',
      'Magento_Checkout/js/model/shipping-save-processor/default':
        'Oander_IstyleCheckout/js/model/shipping-save-processor/default',
      'Magento_Checkout/js/action/set-billing-address':
        'Oander_IstyleCheckout/js/action/set-billing-address'
    }
  },
  config: {
    mixins: {
      'Oander_CoreBugFix/js/view/shipping': {
        'Oander_IstyleCheckout/js/view/shipping': true
      },
      'Magento_Checkout/js/view/payment': {
        'Oander_IstyleCheckout/js/view/payment': true
      },
      'Magento_Checkout/js/view/billing-address': {
        'Oander_IstyleCheckout/js/view/billing-address': true
      },
      'Oander_Ui/js/form/element/ui-select-ajax': {
        'Oander_IstyleCheckout/js/form/element/ui-select-city': true
      }
    }
  }
};
