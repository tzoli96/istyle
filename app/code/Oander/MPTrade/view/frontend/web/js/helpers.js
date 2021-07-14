define([
  'mage/translate',
  'Magento_Catalog/js/price-utils',
  'jquery',
  'mage/calendar'
], function ($t, priceUtils, $) {
  return {
    getFormattedPrice(price) {
      var priceFormat = {
        decimalSymbol: '.',
        groupLength: 3,
        groupSymbol: ",",
        integerRequired: false,
        pattern: "$%s",
        precision: 2,
        requiredPrecision: 2
      };

      return priceUtils.formatPrice(price, priceFormat);
    },

    translateString(string) {
      return $t(string);
    },

    scrollTo(block, topMinus) {
      var watch = setInterval(function () {
        if ($(block).length) {
          clearInterval(watch);

          $('html, body').animate({
            scrollTop: $(block).offset().top - topMinus,
          }, 500);
        }
      }, 500);
    },

    toggleInfo: function () {
      $(document).on('click', '.form-info-icon', function (e) {
        var info = $(this).closest('.form-label').find('.form-info-content');
        $('.form-info-content.is-active').not(info).removeClass('is-active');

        info.toggleClass('is-active');
      });

      $(document).mouseup(function (e) {
        var info = $('.form-info-content');
        var infoIcon = $('.form-info-icon');

        if ((!info.is(e.target) && info.has(e.target).length === 0)
          && !infoIcon.is(e.target) && infoIcon.has(e.target).length === 0) {
          info.removeClass('is-active');
        }
      });
    },
  };
});
