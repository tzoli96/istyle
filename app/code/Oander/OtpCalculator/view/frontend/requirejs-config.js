var config = {
  'config': {
    'mixins': {
      'Bigfishpaymentgateway_Pmgw/js/view/payment/method-renderer/bigfishpaymentgateway_pmgw': {
          'Oander_OtpCalculator/js/payment/bigfishpaymentgateway_pmgw-mixin': true
      }
    }
  },
  map: {
    '*': {
      'Bigfishpaymentgateway_Pmgw/template/payment/form.html':
        'Oander_OtpCalculator/template/payment/form.html'
    }
  }
};
