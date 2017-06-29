define([
  'jquery',
  'slick',
  'oander.slider-configuration'
], function ($, slick, config) {
  'use strict';

  /**
   *
   * @type {{options: {containerSelector: string, slider: boolean, sliderOptions: {mobileFirst: boolean, slidesToShow: number, slidesToScroll: number, responsive: [*]}}}}
   */
  var itemsSlider = {
    options: {
      containerSelector: '.products.list',
      slider: false,
      sliderOptions: config.settings
    }
  };

  /**
   * Init slider
   */
  $(function(){

    if ($('[data-items-slider]').length > 0) {

      var container = $('[data-items-slider]').find(itemsSlider.options.containerSelector);

      container.slick(itemsSlider.options.sliderOptions);
    }
  });

});