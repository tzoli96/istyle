var config = {
  deps: ['Oander_AddressFieldsProperties/js/cleave.min'],
  config: {
    mixins: {
      'Oander_CoreBugFix/mage/validation': {
        'Oander_AddressFieldsProperties/js/mage-validation': true
      },
      'Magento_Ui/js/lib/validation/validator': {
        'Oander_AddressFieldsProperties/js/lib-validation': true
      },
      'Magento_Ui/js/form/element/abstract': {
        'Oander_AddressFieldsProperties/js/ui/form/element/abstract': true
      },
    }
  }
};
