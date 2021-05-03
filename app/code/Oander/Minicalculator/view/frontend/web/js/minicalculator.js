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
        switch (this.options.data.productType) {
          case "simple":
            this._calculator();
            break;
          case "bundle":
            this._bundle();
            break;
          case "configurable":
            this._configurable();
            break;
        }

        this._moveCalculatorBlock();
      }
    },

    /**
     * Configurable trigger calculator
     */
    _configurable: function () {
      var self = this;

      $(this.defaults.priceBox).on('updatePrice', function () {
        var productId = $('[data-role="swatch-options"]').data('mageOanderSwatchRenderer').getProduct();
        var priceBox = $(self.defaults.priceBox).data('mage-priceBox').cache.displayPrices;
        var finalPrice;

        if (!$.isEmptyObject(priceBox)) {
          if (priceBox.finalPrice.final != null) finalPrice = Math.round(priceBox.finalPrice.final);
          finalPrice = Math.round(priceBox.finalPrice.amount);
        }

        if (productId != null) {
          if (self.options.data.configurable.calculator[productId].type === null) self._clearCalculatorBlock();

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
          if (priceBox.finalPrice.final != null) finalPrice = Math.round(priceBox.finalPrice.final);
          finalPrice = Math.round(priceBox.finalPrice.amount);
        }

        if (finalPrice != null) self._calculator(finalPrice);
      });
    },

    /**
     * Calculator
     * @param {float} configPrice
     * @param {Number} configProductId
     */
    _calculator: function (configPrice, configProductId) {
      var self = this;
      var options = self.options.data;
      var price, barem, installment, type, minDownPayment = '';

      switch (options.productType) {
        case "configurable":
          price = Number(configPrice);
          barem = self._getBaremsDataById(options.configurable.calculator[configProductId].barem, configProductId);
          installment = options.configurable.calculator[configProductId].installment;
          type = options.configurable.calculator[configProductId].type;
          break;
        case "bundle":
          price = Number(configPrice);
          barem = self._getBaremsDataById(options.calculatorData.barem);
          installment = options.calculatorData.installment;
          type = options.calculatorData.type;
          break;
        default:
          price = Number(options.productPrice);
          barem = self._getBaremsDataById(options.calculatorData.barem);
          installment = options.calculatorData.installment;
          type = options.calculatorData.type;
      }

      minDownPayment = (barem.equity)
        ? Number(barem.min_price)
        : Number(price - barem.max_price);

      if (barem && (price >= barem.min_price) && self._checkMaxPrice(barem, price)) {
        if (type == 'hellobank') {
          if (xhr && xhr.readyState != null) xhr.abort();

          var xhr = $.ajax({
            url: this.defaults.urls.hellobank,
            type: 'GET',
            data: {
              kodProdejce: options.helloBankData.sellerId,
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
              self._renderBlock(type, barem.barem_id, installment, values.find('vyseSplatky').text());
            }
          });
        }
      }
      else {
        self._clearCalculatorBlock();
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
      var barems = (this.options.data.productType == "configurable")
        ? this.options.data.configurable.barems[productId]
        : this.options.data.barems;
      var data = {};

      barems.forEach(function (value) {
        if (value.id == id) {
          if (value.equity) data['equity'] = value.equity;

          data['min_price'] = value.min_price;
          data['max_price'] = value.max_price;
          data['barem_id'] = value.barem_id;
        }
      });

      return data;
    },

    /**
     * Render block
     * @param {Number} calculatorId
     * @param {Number} baremId
     * @param {Number} installment
     * @param {float} price
     */
    _renderBlock: function (calculatorId, baremId, installment, price) {
      var self = this;
      var priceBlock = $('.block.block--minicalculator').find('.block__calculate');

      priceBlock.find('.block__lead').html($t('or'));
      priceBlock.find('.block__content').html(installment + ' x ' + self._getFormattedPrice(price));
      priceBlock.find('.block__anchor')
        .html('<a href="#' + calculatorId + '">' + $t('Go to the installment calculator') + '</a>');

      scroller.callScroller(calculatorId, 140);

      priceBlock.find('.block__anchor').on('click', function () {
        self._changeActiveBarem(calculatorId, baremId, installment);
      });
    },

    /**
     * Change active barem
     * @param {string} type
     * @param {Number} baremId
     * @param {Number} installment
     */
     _changeActiveBarem: function (type, baremId, installment) {
      var self = this;

      if (type == 'hellobank') {
        var calculator = $('.hellobank-calculator');
        var barems = calculator.find('.tabs__titles').find('.tabs__title');

        barems.each(function (key, barem) {
          var currentBaremId = $(barem).attr('data-barem-id');

          if (baremId == currentBaremId) self._changeActiveInstallment(baremId, installment.trim());
        });
      }
    },

    /**
     * Change active installment
     * @param {Number} baremId
     * @param {Number} installment
     */
    _changeActiveInstallment: function (baremId, installment) {
      var calculator = $('.hellobank-calculator');
      var activeTitle = calculator.find('.tabs__title[data-barem-id="'+ baremId +'"]');
      var activeContent = calculator.find('.tabs__content[data-barem-id="'+ baremId +'"]');

      if (activeContent.find('.calculator-range').length) {
        var installmentIndex = 0;

        activeContent.find('.calculator-steps span').each(function (key, value) {
          if ($(value).attr('data-title') == installment) {
            $(value).addClass('active');
            installmentIndex = key;
          }
          else {
            $(value).removeClass('active');
          }
        });

        activeContent
          .find('.form__installment.months')
          .html(installment);

        activeContent
          .find('.slider')
          .attr('value', installmentIndex)
          .trigger('change');
      }

      activeTitle.trigger('click');
    },

    /**
     * Move calculator block under the price
     */
    _moveCalculatorBlock: function () {
      $('.product-price-addto').append($('.block.block--minicalculator'));
    },

    /**
     * Clear calculator block
     */
    _clearCalculatorBlock: function () {
      var priceBlock = $('.block.block--minicalculator').find('.block__calculate');

      priceBlock.find('.block__lead').html('');
      priceBlock.find('.block__content').html('');
      priceBlock.find('.block__anchor').html('');
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
