var config = {
  map: {
    '*': {
      oanderIstyleCheckout: 'Oander_IstyleCheckout/js/checkout',
      'Magento_Checkout/template/onepage.html':
        'Oander_IstyleCheckout/template/onepage.html',
      'Magento_Checkout/template/progress-bar.html':
        'Oander_IstyleCheckout/template/progress-bar.html',
      'Magento_Checkout/template/shipping.html':
        'Oander_IstyleCheckout/template/shipping.html',
      'Magento_Checkout/template/form/element/email.html':
        'Oander_IstyleCheckout/template/form/element/email.html',
      'Magento_Checkout/template/payment.html':
        'Oander_IstyleCheckout/template/payment.html',
      'Magento_Checkout/template/sidebar.html':
        'Oander_IstyleCheckout/template/sidebar.html',
      'Magento_Checkout/js/view/form/element/email':
        'Oander_IstyleCheckout/js/view/form/element/email',
    }
  },
  config: {
    mixins: {
      'Oander_CoreBugFix/js/view/shipping': {
        'Oander_IstyleCheckout/js/view/shipping': true
      },
      'Magento_Checkout/js/view/payment': {
        'Oander_IstyleCheckout/js/view/payment': true
      }
    }
  }
};
