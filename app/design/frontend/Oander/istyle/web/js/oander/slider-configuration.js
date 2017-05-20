define(function () {
  'use strict';

  return {
    settings: {
      mobileFirst: true,
      prevArrow: '<div class="slick-prev slick-arrow"><i class="icon icon-chevron-left"></i></div>',
      nextArrow: '<div class="slick-next slick-arrow"><i class="icon icon-chevron-right"></i></div>',
      dots: true,
      arrows: false,
      slidesToShow: 2,
      slidesToScroll: 2,
      responsive: [
        {
          breakpoint: 768,
          settings: {
            arrows: true,
            slidesToShow: 3,
            slidesToScroll: 3
          }
        },
        {
          breakpoint: 992,
          settings: {
            arrows: true,
            slidesToShow: 4,
            slidesToScroll: 4,
            swipe: false
          }
        }
      ]
    }
  };

});