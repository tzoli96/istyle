var config = {
  map: {
    '*': {
      select2: 'Oander_CustomerExtend/js/select2.min',
      roRegionCity: 'Oander_CustomerExtend/js/ro-region-city',
      addressCompanyExtend: 'Oander_CustomerExtend/js/address-extend'
    }
  },
  config: {
    mixins: {
      'Magento_Checkout/js/model/shipping-rates-validator': {
        'Oander_CustomerExtend/js/model/shipping-rates-validator-mixin': true
      },
      'Magento_Checkout/js/action/set-billing-address': {
        'Oander_CustomerExtend/js/action/set-billing-address-mixin': true
      },
      'Magento_Checkout/js/action/set-shipping-information': {
        'Oander_CustomerExtend/js/action/set-shipping-information-mixin': true
      },
      'Magento_Checkout/js/action/create-shipping-address': {
        'Oander_CustomerExtend/js/action/create-shipping-address-mixin': true
      },
      'Magento_Checkout/js/action/place-order': {
        'Oander_CustomerExtend/js/action/set-billing-address-mixin': true
      },
      'Magento_Checkout/js/action/create-billing-address': {
        'Oander_CustomerExtend/js/action/set-billing-address-mixin': true
      }
    }
  }
};
