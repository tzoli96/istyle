define([
  'jquery',
  'Magento_Catalog/js/price-utils',
  'mage/translate',
  'oander.scroll-to'
], function ($, priceUtils, $t, scroller) {
  'use strict';

  $.widget('oander.oanderMiniCalculator', {
    defaults: {
      priceBox: '.product-info-price [data-role=priceBox]',
      urls: {
        hellobank: 'https://www.cetelem.cz/webkalkulator.php',
      }
    },

    /**
     * Init
     */
    _init: function () {
      if (this.options.data.helloBankData.isActive) {
        if (this.options.data.productType == "simple") {
          this._calculator();
        }
        else if (this.options.data.productType == "bundle") {
          this._bundle();
        }
        else if (this.options.data.productType == "configurable") {
          this._configurable();
        }

        $('.product-price-addto').append($('.block.block--minicalculator'));
      }
    },

    /**
     * Configurable trigger calculator
     */
    _configurable: function () {
      var self = this;
      var priceBlock = $('.block.block--minicalculator').find('.block__calculate');

      $(this.defaults.priceBox).on('updatePrice', function () {
        var productId = $('[data-role="swatch-options"]').data('mageOanderSwatchRenderer').getProduct();
        var priceBox = $(self.defaults.priceBox).data('mage-priceBox').cache.displayPrices;
        var finalPrice;

        if (!$.isEmptyObject(priceBox)) {
          if (priceBox.finalPrice.final != null) {
            finalPrice = Math.round(priceBox.finalPrice.final);
          } else {
            finalPrice = Math.round(priceBox.finalPrice.amount);
          }
        }

        if (productId != null) {
          if (self.options.data.configurable.calculator[productId].type === null) {
            priceBlock.find('.block__lead').html('');
            priceBlock.find('.block__content').html('');
            priceBlock.find('.block__anchor').html('');
          }

          self._calculator(finalPrice, productId);
        }
      });
    },

    /**
     * Bundle trigger calculator
     */
    _bundle: function () {
      var self = this;

      $(this.defaults.priceBox).on('updatePrice', function () {
        var priceBox = $(self.defaults.priceBox).data('mage-priceBox').cache.displayPrices;
        var finalPrice;

        if (!$.isEmptyObject(priceBox)) {
          if (priceBox.finalPrice.final != null) {
            finalPrice = Math.round(priceBox.finalPrice.final);
          } else {
            finalPrice = Math.round(priceBox.finalPrice.amount);
          }
        }

        if (finalPrice != null) {
          self._calculator(finalPrice);
        }
      });
    },

    /**
     * Calculator
     * @param {float} configPrice
     * @param {Number} configProductId
     */
    _calculator: function (configPrice, configProductId) {
      var self = this;
      var priceBlock = $('.block.block--minicalculator').find('.block__calculate');
      var price = '';
      var barem = '';
      var installment = '';
      var type = '';

      if (this.options.data.productType == 'configurable') {
        price = Number(configPrice);
        barem = self._getBaremsDataById(this.options.data.configurable.calculator[configProductId].barem, configProductId);
        installment = self.options.data.configurable.calculator[configProductId].installment;
        type = self.options.data.configurable.calculator[configProductId].type;
      }
      else if (this.options.data.productType == 'bundle') {
        price = Number(configPrice);
        barem = self._getBaremsDataById(this.options.data.calculatorData.barem);
        installment = self.options.data.calculatorData.installment;
        type = self.options.data.calculatorData.type;
      } else {
        price = Number(self.options.data.productPrice);
        barem = self._getBaremsDataById(this.options.data.calculatorData.barem);
        installment = self.options.data.calculatorData.installment;
        type = self.options.data.calculatorData.type;
      }

      var minDownPayment = (barem.equity) ? Number(barem.min_price) : Number(price - barem.max_price);

      if (barem && (price >= barem.min_price) && self._checkMaxPrice(barem, price)) {
        if (type == 'hellobank') {
          if (xhr && xhr.readyState != null) xhr.abort();

          var xhr = $.ajax({
            url: this.defaults.urls.hellobank,
            type: 'GET',
            data: {
              kodProdejce: self.options.data.helloBankData.sellerId,
              kodBaremu: barem.barem_id,
              kodPojisteni: 'S0',
              cenaZbozi: price,
              vyseUveru: (minDownPayment) ? Number(price - minDownPayment) : price,
              primaPlatba: (minDownPayment) ? minDownPayment : 0,
              pocetSplatek: installment,
            },
            dataType: 'xml'
          }).done(function (res) {
            var status = $(res).find('status').text();
            
            if (status == 'ok') {
              var values = $(res).find('vysledek');
              self._renderBlock(type, installment, values.find('vyseSplatky').text());
            }
          });
        }
      }
      else {
        priceBlock.find('.block__lead').html('');
        priceBlock.find('.block__content').html('');
        priceBlock.find('.block__anchor').html('');
      }
    },

    /**
     * Check max price
     * @param {Array} barem
     * @param {float} price
     * @returns
     */
    _checkMaxPrice: function (barem, price) {
      if (barem.equity) {
        if (price > barem.max_price) return false;
        return true;
      }
      else {
        return true;
      }
    },

    /**
     * Get barems data by id
     * @param {Number} id
     * @param {Number} productId
     */
    _getBaremsDataById: function (id, productId) {
      var barems = (this.options.data.productType == "configurable") ? this.options.data.configurable.barems : this.options.data.barems;
      var data = {};

      if (this.options.data.productType == "configurable") {
        barems[productId].forEach(function (value) {
          if (value.id == id) {
            if (value.equity) data['equity'] = value.equity;

            data['min_price'] = value.min_price;
            data['max_price'] = value.max_price;
            data['barem_id'] = value.barem_id;
          }
        });
      }
      else {
        barems.forEach(function (value) {
          if (value.id == id) {
            if (value.equity) data['equity'] = value.equity;

            data['min_price'] = value.min_price;
            data['max_price'] = value.max_price;
            data['barem_id'] = value.barem_id;
          }
        });
      }

      return data;
    },

    /**
     * Render block
     * @param {Number} calculatorId
     * @param {Number} installment
     * @param {float} price
     */
    _renderBlock: function (calculatorId, installment, price) {
      var self = this;
      var priceBlock = $('.block.block--minicalculator').find('.block__calculate');

      priceBlock.find('.block__lead').html($t('or'));
      priceBlock.find('.block__content').html(installment + ' x ' + self._getFormattedPrice(price));
      priceBlock.find('.block__anchor').html('<a href="#' + calculatorId + '">' + $t('Go to the installment calculator') + '</a>');

      scroller.callScroller(calculatorId, 140);
    },

    /**
     * Get formatted price
     * @param {float} price
     * @returns float
     */
     _getFormattedPrice: function (price) {
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
  });

  return $.oander.oanderMiniCalculator;
});
