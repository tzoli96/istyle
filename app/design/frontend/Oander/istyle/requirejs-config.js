var config = {

  // @Todo: FIX boostrap tether bug with requireJS

  // shim: {
  //   tether: {
  //     'deps': ['jquery']
  //   },
  //   bootstrap: {
  //     'deps': ['jquery', 'tether']
  //   }
  // },
  // paths: {
  //   'tether': 'js/vendor/tether.min',
  //   'bootstrap': 'js/vendor/bootstrap.min'
  // },

  map: {
    '*': {
      'oander.navigation': 'js/oander/navigation',
      'oander.accordion': 'js/oander/accordion',
      'oander.items-slider': 'js/oander/items-slider',
      'oander.slider-configuration': 'js/oander/slider-configuration',
      relatedProducts: 'js/module-catalog/related-products',
      upsellProducts: 'js/module-catalog/upsell-products',
      oanderWarehouseView: 'js/oander/warehouse-manager/product/view',
    }
  }
};