var config = {
  map: {
    '*': {
      select2: 'Oander_CustomerExtend/js/select2.min',
      roRegionCity: 'Oander_CustomerExtend/js/ro-region-city'
    }
  },
  config: {
    mixins: {
      'Magento_Checkout/js/model/shipping-rates-validator': {
        'Oander_CustomerExtend/js/model/shipping-rates-validator-mixin': true
      }
    }
  }
};
