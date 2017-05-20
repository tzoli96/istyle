define(function () {
  'use strict';

  return {
    settings: {
      prevArrow: '<div class="slick-prev slick-arrow"><i class="icon icon-chevron-left"></i></div>',
      nextArrow: '<div class="slick-next slick-arrow"><i class="icon icon-chevron-right"></i></div>',
      mobileFirst: true,
      slidesToShow: 2,
      slidesToScroll: 2,
      dots: true,
      responsive: [
        {
          breakpoint: 576,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 2
          }
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 3,
            slidesToScroll: 3
          }
        },
        {
          breakpoint: 992,
          settings: {
            slidesToShow: 4,
            slidesToScroll: 4,
            dots: false,
            swipe: false
          }
        }
      ]
    }
  };

});