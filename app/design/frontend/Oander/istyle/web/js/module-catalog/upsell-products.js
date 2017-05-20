/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
  'jquery',
  'jquery/ui',
  'slick',
  'oander.slider-configuration'
], function($, ui, slick, config){
  "use strict";

  $.widget('mage.upsellProducts', {
    options: {
      elementsSelector: ".item.product",
      containerSelector: '.products.list',
      slider: false,
      sliderOptions: config.settings
    },

    /**
     * Bind events to the appropriate handlers.
     * @private
     */
    _create: function() {

      this._showUpsellProducts(
        this.element.find(this.options.elementsSelector),
        this.element.data('limit'),
        this.element.data('shuffle')
      );
      this._initSlider();
    },

    /**
     * Show upsell products according to limit. Shuffle if needed.
     * @param elements
     * @param limit
     * @param shuffle
     * @private
     */
    _showUpsellProducts: function(elements, limit, shuffle) {
      if (shuffle) {
        this._shuffle(elements);
      }
      if (limit === 0) {
        limit = elements.length;
      }
      for (var index = 0; index < limit; index++) {
        $(this.element).find(elements[index]).show();
      }
    },

    /**
     * Shuffle an array
     * @param o
     * @returns {*}
     */
    _shuffle: function shuffle(o){ //v1.0
      for (var j, x, i = o.length; i; j = Math.floor(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
      return o;
    },

    /**
     * Init slider
     * @private
     */
    _initSlider: function() {
      // Check is options is true
      if (this.options.slider) {
        this.element.find(this.options.containerSelector).slick(this.options.sliderOptions);
      }
    }
  });

  return $.mage.upsellProducts;
});